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
 * @copyright Copyright (C) 2015-2017 MantisScheduledTickets Team <support@mantis-scheduled-tickets.net>
 * @link http://www.mantis-scheduled-tickets.net
 */

    access_ensure_global_level( plugin_config_get( 'manage_threshold' ) );

    $t_page_title = plugin_lang_get( 'title_edit_template_category' );
    $t_edit_action = plugin_page( 'manage_template_category_edit' );
    $t_delete_argument_action = plugin_page( 'manage_command_argument_delete_page' );
    $t_add_edit_command_argument_action = plugin_page( 'manage_command_argument_add_edit' );

    $f_template_category_id = gpc_get_int( 'id' );

    $t_template_category = template_category_get_row( $f_template_category_id );
    $t_template_id = $t_template_category['template_id'];
    $t_project_id = $t_template_category['project_id'];
    $t_category_id = $t_template_category['category_id'];
    $t_frequency_id = $t_template_category['frequency_id'];
    $t_user_id = $t_template_category['user_id'];
    $t_has_command = $t_template_category['has_command'];
    $t_enable_commands = plugin_config_get( 'enable_commands' );
    $t_frequency_class = ( 0 == $t_template_category['frequency_enabled'] ) ? ' class="frequency_crontab_disabled"' : '';

    $t_command_arguments = command_argument_get_all( $f_template_category_id );

    layout_page_header( $t_page_title );
    layout_page_begin();
    print_manage_menu();
    mst_core_print_scheduled_tickets_menu( MST_MANAGE_TEMPLATE_PAGE );

?>

<div class="col-md-12 col xs-12">
    <div class="space-10"></div>
    <div class="form-container">
        <form name="edit_template_category" id="edit_template_category" method="post" action="<?php echo $t_edit_action; ?>">
            <?php
                echo form_security_field( 'manage_template_category_edit' );
            ?>
            <input type="hidden" name="id" value="<?php echo $f_template_category_id; ?>" />
            <input type="hidden" name="template_id" value="<?php echo $t_template_id; ?>" />
            <input type="hidden" name="old_project_id" value="<?php echo $t_project_id; ?>">
            <input type="hidden" name="old_category_id" value="<?php echo $t_category_id; ?>">
            <input type="hidden" name="old_frequency_id" value="<?php echo $t_frequency_id; ?>" />
            <input type="hidden" name="old_user_id" value="<?php echo $t_user_id; ?>" />

            <div class="widget-box widget-color-blue2">
                <div class="widget-header widget-header-small">
                    <h4>
                        <i class="ace-icon fa fa-newspaper-o"></i>
                        <?php echo $t_page_title; ?>
                    </h4>
                </div>
                <div class="widget-body">
                    <div class="widget-main no-padding">
                        <table class="table table-bordered table-condensed">
                            <tbody>
                                <tr>
                                    <td class="category">
                                        <?php echo lang_get( 'category' ); ?>
                                    </td>
                                    <td>
                                        <?php mst_helper_available_categories( $t_project_id, $t_category_id ); ?>
                                    </td>
                                </tr>

                                <?php
                                    if( $t_enable_commands ) {
                                ?>
                                    <tr>
                                        <td class="category">
                                            <?php echo plugin_lang_get( 'template_command' ); ?>
                                        </td>
                                        <td>
                                            <input <?php echo helper_get_tab_index(); ?> type="text" name="command" id="command" size="50" value="<?php echo $t_template_category['command']; ?>" disabled="disabled" />
                                        </td>
                                    </tr>
                                <?php
                                    }
                                ?>

                                <tr>
                                    <td class="category">
                                        <?php echo plugin_lang_get( 'frequency' ); ?>
                                    </td>
                                    <td <?php echo $t_frequency_class; ?>>
                                        <?php mst_helper_frequencies( $t_frequency_id, $t_template_category['frequency_name'] ); ?>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="category">
                                        <?php echo lang_get( 'assign_to' ); ?>
                                    </td>
                                    <td>
                                        <select name="user_id" id="user_id">
                                            <option value="0"><?php echo plugin_lang_get( 'not_assigned' ); ?></option>
                                            <?php print_assign_to_option_list( (int)$t_user_id ); ?>
                                        </select>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="widget-toolbox padding-8 clearfix">
                        <span class="required pull-right"> * <?php echo lang_get( 'required' ); ?></span>
                        <input type="submit" class="btn btn-primary btn-white btn-round" value="<?php echo plugin_lang_get( 'template_update' ); ?>" />
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<?php
    if( $t_has_command &&  $t_enable_commands ) {
?>
    <div class="col-md-12 col xs-12">
        <div class="space-10"></div>
        <div class="widget-box widget-color-blue2">
            <div class="widget-header widget-header-small">
                <h4>
                    <i class="ace-icon fa fa-sitemap"></i>
                    <?php echo lang_get( 'categories' ); ?>
                </h4>
            </div>
            <div class="widget-body">
                <div class="widget-main no-padding">
                    <table class="table table-bordered table-condensed table-hover table-striped" id="command_arguments">
                        <thead>
                            <tr>
                                <td>
                                    <?php echo plugin_lang_get( 'template_command_argument_name' ); ?>
                                </td>
                                <td>
                                    <?php echo plugin_lang_get( 'template_command_argument_value' ); ?>
                                </td>
                                <td>
                                    <?php echo lang_get( 'actions' ); ?>
                                </td>
                            </thead>
                            <tbody>
                                <?php
                                    if( is_array( $t_command_arguments ) ) {
                                        foreach( $t_command_arguments as $t_command_argument ) {
                                            $t_argument_name = $t_command_argument['argument_name'];
                                            $t_argument_value = $t_command_argument['argument_value'];
                                            $t_escaped_argument_value = command_arguments_format( $t_argument_value );
                                            $t_id = $t_command_argument['id']
                                ?>
                                            <tr id="argument_<?php echo $t_id; ?>">
                                                <td id="name_<?php echo $t_id; ?>"><?php echo $t_argument_name; ?></td>
                                                <td id="value<?php echo $t_id; ?>"><?php echo $t_argument_value; ?></td>
                                                <td class="center">
                                                    <div class="btn-group inline">
                                                        <div class="pull-left">
                                                            <input type="button" class="btn btn-sm btn-primary btn-white btn-round mst_edit_command_argument" data-argument-name="<?php echo $t_argument_name; ?>" data-argument-value="<?php echo command_argument_format( $t_argument_value, MST_ESCAPE_FOR_JS ); ?>" value="<?php echo lang_get( 'edit_link' ); ?>" id="edit_<?php echo $t_id; ?>" />
                                                        </div>
                                                    </div>
                                                    <div class="btn-group inline">
                                                        <div class="pull-left">
                                                            <form name="delete_command_argument" method="post" action="<?php echo $t_delete_argument_action; ?>">
                                                                <?php
                                                                    echo form_security_field( 'delete_command_argument' );
                                                                ?>
                                                                <input type="hidden" name="id" value="<?php echo $t_id; ?>" />
                                                                <input type="hidden" name="template_category_id" value="<?php echo $f_template_category_id; ?>" />
                                                                <input type="hidden" name="template_id" value="<?php echo $t_template_id; ?>" />
                                                                <input type="submit" class="btn btn-sm btn-primary btn-white btn-round" value="<?php echo lang_get( 'delete_link' ); ?>" id="delete_<?php echo $t_id; ?>" />
                                                            </form>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                <?php
                                        }
                                    }
                                ?>
                            </tbody>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-12 col xs-12">
        <div class="space-10"></div>
        <div class="form-container">
            <form name="add_edit_command_argument" id="add_edit_command_argument" method="post" action="<?php echo $t_add_edit_command_argument_action; ?>">
                <?php
                    echo form_security_field( 'add_edit_command_argument' );
                ?>
                <input type="hidden" name="template_category_id" value="<?php echo $f_template_category_id; ?>" />
                <input type="hidden" name="template_id" value="<?php echo $t_template_id; ?>" />
                <input type="hidden" id="command_argument_id" name="command_argument_id" />

                <div class="widget-box widget-color-blue2">
                    <div class="widget-header widget-header-small">
                        <h4>
                            <i class="ace-icon fa fa-newspaper-o"></i>
                            <?php echo $t_page_title; ?>
                        </h4>
                    </div>
                    <div class="widget-body">
                        <div class="widget-main no-padding">
                            <table class="table table-bordered table-condensed">
                                <tbody>
                                    <tr>
                                        <td class="category">
                                            <?php echo plugin_lang_get( 'template_command_argument_name' ); ?>
                                        </td>
                                        <td>
                                            <input <?php echo helper_get_tab_index(); ?> type="text" name="argument_name" id="argument_name" size="50" />
                                        </td>
                                    </tr>

                                    <tr>
                                        <td class="category">
                                            <?php echo plugin_lang_get( 'template_command_argument_value' ); ?>
                                        </td>
                                        <td>
                                            <input <?php echo helper_get_tab_index(); ?> type="text" name="argument_value" id="argument_value" size="50" />
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="widget-toolbox padding-8 clearfix">
                            <input type="submit" class="btn btn-primary btn-white btn-round" id="submit_button" value="<?php echo plugin_lang_get( 'template_command_argument_button_add' ); ?>" />
                            <input type="reset" class="btn btn-primary btn-white btn-round mst_form_reset_button" id="form_reset_button" value="<?php echo plugin_lang_get( 'template_command_argument_button_reset' ); ?>" />
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
<?php
    }  # if( $t_has_command ... )
?>

<!-- History -->
<?php
    $t_history = template_category_get_history( $f_template_category_id );
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
                <?php echo plugin_lang_get( 'template_history' ); ?>
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
                                    <?php echo lang_get( 'date_modified' ); ?>
                                </th>
                                <th class="small-caption">
                                    <?php echo lang_get( 'username' ); ?>
                                </th>
                                <th class="small-caption">
                                    <?php echo lang_get( 'field' ); ?>
                                </th>
                                <th class="small-caption">
                                    <?php echo lang_get( 'change' ); ?>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                if( is_array( $t_history ) ) {
                                    foreach ( $t_history as $t_item ) {
                            ?>
                                        <tr>
                                            <td class="small-caption" nowrap="nowrap">
                                                <?php echo date( $t_normal_date_format, $t_item['date_modified'] ); ?>
                                            </td>
                                            <td class="small-caption">
                                                <?php print_user( $t_item['user_id'] ); ?>
                                            </td>
                                            <td class="small-caption">
                                                <?php
                                                    switch( $t_item['type'] ) {
                                                        case MST_TEMPLATE_CHANGED:
                                                            echo plugin_lang_get( 'template_' . $t_item['field_name'] );
                                                            break;
                                                        case MST_TEMPLATE_CATEGORY_CHANGED:
                                                            switch( $t_item['field_name'] ) {
                                                                case 'project':
                                                                    echo lang_get( 'project_name' );
                                                                    break;
                                                                case 'category':
                                                                    echo lang_get( 'category' );
                                                                    break;
                                                                case 'frequency':
                                                                    echo plugin_lang_get( 'frequency' );
                                                                    break;
                                                                case 'assigned_to':
                                                                    echo lang_get( 'assigned_to' );
                                                                    break;
                                                                case 'template_command_arguments':
                                                                    echo plugin_lang_get( 'template_command_arguments' );
                                                                    break;
                                                            }
                                                            break;
                                                        case MST_TEMPLATE_CATEGORY_ADDED:
                                                            echo plugin_lang_get( 'template_category_added' );
                                                            break;
                                                        case MST_TEMPLATE_CATEGORY_DELETED:
                                                            echo plugin_lang_get( 'template_category_deleted' );
                                                            break;
                                                        case MST_TEMPLATE_CONFIG_CHANGED:
                                                            echo ( $t_item['new_value'] ) ?
                                                                plugin_lang_get( 'config_commands_enabled' ) :
                                                                plugin_lang_get( 'config_commands_disabled' );
                                                            break;
                                                    }
                                                ?>
                                            </td>
                                            <td class="small-caption">
                                                <?php
                                                    switch( $t_item['type'] ) {
                                                        case MST_TEMPLATE_ADDED:
                                                            echo plugin_lang_get( 'template_added' );
                                                            break;
                                                        case MST_TEMPLATE_ENABLED:
                                                            echo plugin_lang_get( 'template_enabled' );
                                                            break;
                                                        case MST_TEMPLATE_DISABLED:
                                                            echo plugin_lang_get( 'template_disabled' );
                                                            break;
                                                        case MST_TEMPLATE_CHANGED:
                                                            switch( $t_item['field_name'] ) {
                                                                case 'summary':
                                                                case 'description':
                                                                case 'enabled':
                                                                case 'command':
                                                                    echo $t_item['old_value'] . ' => ' . $t_item['new_value'];
                                                                    break;
                                                                case 'diff_flag':
                                                                    echo ( $t_item['old_value'] ? $t_yes : $t_no ) . ' => ' . ( $t_item['new_value'] ? $t_yes : $t_no );
                                                                    break;
                                                            }
                                                            break;
                                                        case MST_TEMPLATE_DELETED:
                                                            # we should never really get here... if the template was deleted, we wouldn't be rendering this page...
                                                            echo plugin_lang_get( 'template_deleted' );
                                                            break;
                                                        case MST_TEMPLATE_CATEGORY_ADDED:
                                                            break;
                                                        case MST_TEMPLATE_CATEGORY_CHANGED:
                                                            switch( $t_item['field_name'] ) {
                                                                case 'project':
                                                                case 'category':
                                                                case 'frequency':
                                                                case 'assigned_to':
                                                                case 'template_command_arguments':
                                                                    echo  $t_item['old_value'] . ' => ' .  $t_item['new_value'];
                                                                    break;
                                                            }
                                                            break;
                                                        case MST_TEMPLATE_CATEGORY_DELETED:
                                                            # we should never get to this point
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
