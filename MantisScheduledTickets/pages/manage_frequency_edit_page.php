<?php

# MantisScheduledTickets - a MantisBT (http://www.mantisbt.org) plugin

# MantisScheduledTickets is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# MantisScheduledTickets is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MantisScheduledTickets.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Mantis Scheduled Tickets
 *
 * @package MantisScheduledTickets
 * @filesource
 * @copyright Copyright (C) 2015-2020 MantisScheduledTickets Team <mantisbt.mst@gmail.com>
 * @link https://github.com/mantisbt-mst/MantisScheduledTickets
 */

    access_ensure_global_level( plugin_config_get( 'manage_threshold' ) );

    $t_page_title = plugin_lang_get( 'title_edit_frequency' );
    $t_action_page = plugin_page( 'manage_frequency_edit' );
    $t_delete_page = plugin_page( 'manage_frequency_delete_page' );
    $t_frequency_id = gpc_get_int( 'id' );
    $t_frequency = frequency_get_row( $t_frequency_id );

    layout_page_header( $t_page_title );
    layout_page_begin();
    print_manage_menu();
    mst_core_print_scheduled_tickets_menu( MST_MANAGE_FREQUENCY_PAGE );

?>

<div class="col-md-12 col xs-12">
    <div class="space-10"></div>
    <div class="form-container">
        <form name="edit_frequency" id="edit_frequency" class="mst_form" method="post" action="<?php echo $t_action_page; ?>">
            <?php
                echo form_security_field( 'manage_frequency_edit' );
            ?>
            <input type="hidden" name="frequency_id" value="<?php echo $t_frequency_id; ?>" />
            <input type="hidden" name="old_name" value="<?php echo htmlspecialchars( $t_frequency['name'] ); ?>" />
            <input type="hidden" name="old_enabled" value="<?php echo $t_frequency['enabled']; ?>" />
            <input type="hidden" name="old_minute" value="<?php echo $t_frequency['minute']; ?>" />
            <input type="hidden" name="old_hour" value="<?php echo $t_frequency['hour']; ?>" />
            <input type="hidden" name="old_day_of_month" value="<?php echo $t_frequency['day_of_month']; ?>" />
            <input type="hidden" name="old_month" value="<?php echo $t_frequency['month']; ?>" />
            <input type="hidden" name="old_day_of_week" value="<?php echo $t_frequency['day_of_week']; ?>" />

            <div class="widget-box widget-color-blue2">
                <div class="widget-header widget-header-small">
                    <h4>
                        <i class="ace-icon fa fa-calendar-o"></i>
                        <?php echo $t_page_title; ?>
                    </h4>
                </div>
                <div class="widget-body">
                    <div class="widget-main no-padding">
                        <table class="table table-bordered table-condensed">
                            <tbody>
                                <!-- name -->
                                <tr>
                                    <td class="category">
                                        <span class="required">*</span><?php echo plugin_lang_get( 'frequency_name' ); ?>
                                    </td>
                                    <td colspan="4">
                                        <input <?php echo helper_get_tab_index(); ?> type="text" name="name" id="name" size="60" maxlength="128" value="<?php echo htmlspecialchars( $t_frequency['name'] ); ?>" />
                                    </td>
                                </tr>

                                <!-- enabled -->
                                <tr <?php echo helper_alternate_class();?>>
                                    <td class="category">
                                        <span class="required">*</span><?php echo plugin_lang_get( 'frequency_enabled' ); ?>
                                    </td>
                                    <td colspan="4">
                                        <input <?php echo helper_get_tab_index(); ?> type="checkbox" name="enabled" id="enabled" <?php echo $t_frequency['enabled'] ? 'checked' : '' ?> />
                                    </td>
                                </tr>

                                <tr>
                                    <td class="category">
                                        <span class="required">*</span><?php echo plugin_lang_get( 'frequency_day_of_week' ); ?>
                                    </td>
                                    <td class="category">
                                        <span class="required">*</span><?php echo plugin_lang_get( 'frequency_month' ); ?>
                                    </td>
                                    <td class="category">
                                        <span class="required">*</span><?php echo plugin_lang_get( 'frequency_day_of_month' ); ?>
                                    </td>
                                    <td class="category">
                                        <span class="required">*</span><?php echo plugin_lang_get( 'frequency_hour' ); ?>
                                    </td>
                                    <td class="category">
                                        <span class="required">*</span><?php echo plugin_lang_get( 'frequency_minute' ); ?>
                                    </td>
                                </tr>

                                <tr>
                                    <!-- day of week -->
                                    <td>
                                        <?php mst_helper_render_day_of_week_options( $t_frequency['day_of_week'] ); ?>
                                    </td>

                                    <!-- month -->
                                    <td>
                                        <?php mst_helper_render_month_options( $t_frequency['month'] ); ?>
                                    </td>

                                    <!-- day of month -->
                                    <td>
                                        <?php mst_helper_render_day_of_month_options( $t_frequency['day_of_month'] ); ?>
                                    </td>

                                    <!-- hour -->
                                    <td>
                                        <?php mst_helper_render_hour_options( $t_frequency['hour'] ); ?>
                                    </td>

                                    <!-- minute -->
                                    <td>
                                        <?php mst_helper_render_minute_options( $t_frequency['minute'] ); ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="widget-toolbox padding-8 clearfix">
                        <span class="required pull-right"> * <?php echo lang_get( 'required' ); ?></span>
                        <input type="submit" class="btn btn-primary btn-white btn-round" value="<?php echo plugin_lang_get( 'frequency_update' ); ?>" />
                    </div>
                </div>
            </div>
        </form>
    </div>

    <?php
        if( ( 0 == $t_frequency['template_count'] ) && ( 0 == $t_frequency['bug_count'] ) ) {
    ?>
        <!-- delete -->
        <div class="space-10"></div>
        <div class="col-md-6 col-xs-12 no-padding">
            <div class="space-8"></div>
            <div class="btn-group">
                <form name="delete_frequency" id="delete_frequency" method="post" action="<?php echo $t_delete_page; ?>">
                    <?php
                        echo form_security_field( 'manage_frequency_delete' );
                    ?>
                    <input type="hidden" name="id" value="<?php echo $t_frequency_id; ?>" />
                    <input type="submit" class="btn btn-primary btn-white btn-round" value="<?php echo plugin_lang_get( 'frequency_delete' ); ?>" />
                </form>
            </div>
        </div>
    <?php
        }
    ?>
</div>

<!-- History -->
<?php
    $t_history = frequency_get_history( $t_frequency_id );
    $t_normal_date_format = config_get( 'normal_date_format' );
    $t_collapse_block = gpc_get_bool( 'history', config_get( 'history_default_visible' ) );
    $t_block_css = $t_collapse_block ? '' : 'collapsed';
    $t_block_icon = $t_collapse_block ? 'fa-chevron-up' : 'fa-chevron-down';
?>
<div class="col-md-12 col-xs-12">
    <a name="history" id="history"></a>
    <div class="space-10"></div>
    <div class="widget-box widget-color-blue2 <?php echo $t_block_css ?>">
        <div class="widget-header widget-header-small">
            <h4 class="widget-title">
                <i class="ace-icon fa fa-history"></i>
                <?php echo plugin_lang_get( 'frequency_history' ); ?>
            </h4>
            <div class="widget-toolbar">
                <a data-action="collapse" href="#">
                    <i class="1 ace-icon fa <?php echo $t_block_icon ?> bigger-125"></i>
                </a>
            </div>
        </div>
        <div class="widget-body">
            <div class="widget-main no-padding">
                <div class="table-responsive" id="history_open">
                    <table class="table table-bordered table-condensed table-hover table-striped">
                        <thead>
                            <tr>
                                <th class="small-caption">
                                    <?php echo lang_get( 'date_modified' ) ?>
                                </th>
                                <th class="small-caption">
                                    <?php echo lang_get( 'username' ) ?>
                                </th>
                                <th class="small-caption">
                                    <?php echo lang_get( 'field' ) ?>
                                </th>
                                <th class="small-caption">
                                    <?php echo lang_get( 'change' ) ?>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                if( is_array( $t_history ) ) {
                                    foreach( $t_history as $t_item ) {
                            ?>
                                        <tr <?php echo helper_alternate_class(); ?>>
                                            <td class="small-caption" nowrap="nowrap">
                                                <?php echo date( $t_normal_date_format, $t_item['date_modified'] ); ?>
                                            </td>
                                            <td class="small-caption">
                                                <?php print_user( $t_item['user_id'] ); ?>
                                            </td>
                                            <td class="small-caption">
                                                <?php
                                                    if( MST_FREQUENCY_CHANGED == $t_item['type'] ) {
                                                        echo plugin_lang_get( 'frequency_' . $t_item['field_name'] );
                                                    }
                                                ?>
                                            </td>
                                            <td class="small-caption">
                                                <?php
                                                    switch( $t_item['type'] ) {
                                                        case MST_FREQUENCY_ADDED:
                                                            echo plugin_lang_get( 'frequency_added' );
                                                            break;
                                                        case MST_FREQUENCY_ENABLED:
                                                            echo plugin_lang_get( 'frequency_enabled' );
                                                            break;
                                                        case MST_FREQUENCY_DISABLED:
                                                            echo plugin_lang_get( 'frequency_disabled' );
                                                            break;
                                                        case MST_FREQUENCY_CHANGED:
                                                            switch( $t_item['field_name'] ) {
                                                                case 'name':
                                                                case 'enabled':
                                                                    echo htmlspecialchars( $t_item['old_value'] ) . ' => ' . htmlspecialchars( $t_item['new_value'] );
                                                                    break;
                                                                case 'minute':
                                                                    echo mst_helper_minute_column( $t_item['old_value'] ) . ' => ' . mst_helper_minute_column( $t_item['new_value'] );
                                                                    break;
                                                                case 'hour':
                                                                    echo mst_helper_hour_column( $t_item['old_value'] ) . ' => ' . mst_helper_hour_column( $t_item['new_value'] );
                                                                    break;
                                                                case 'day_of_month':
                                                                    echo mst_helper_day_of_month_column( $t_item['old_value'] ) . ' => ' . mst_helper_day_of_month_column( $t_item['new_value'] );
                                                                    break;
                                                                case 'month':
                                                                    echo mst_helper_month_column( $t_item['old_value'] ) . ' => ' . mst_helper_month_column( $t_item['new_value'] );
                                                                    break;
                                                                case 'day_of_week':
                                                                    echo mst_helper_day_of_week_column( $t_item['old_value'] ) . ' => ' . mst_helper_day_of_week_column( $t_item['new_value'] );
                                                                    break;
                                                            }
                                                            break;
                                                        case MST_FREQUENCY_DELETED:
                                                            # we should never really get here... if the frequency was deleted, we wouldn't be rendering this page...
                                                            echo plugin_lang_get( 'frequency_deleted' );
                                                            break;
                                                    }
                                                ?>
                                            </td>
                                        </tr>
                            <?php
                                    } # end for loop
                                }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
    layout_page_end();
