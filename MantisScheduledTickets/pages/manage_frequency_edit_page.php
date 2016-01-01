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
 * @copyright Copyright (C) 2015-2016 MantisScheduledTickets Team <support@mantis-scheduled-tickets.net>
 * @link http://www.mantis-scheduled-tickets.net
 */

    access_ensure_global_level( plugin_config_get( 'manage_threshold' ) );
    $g_MantisScheduledTickets_context = true;

    $t_page_title = plugin_lang_get( 'title_edit_frequency' );
    $t_edit_action = plugin_page( 'manage_frequency_edit' );
    $t_delete_page = plugin_page( 'manage_frequency_delete_page' );
    $t_frequency_id = gpc_get_int( 'id' );
    $t_frequency = frequency_get_row( $t_frequency_id );

    html_page_top( $t_page_title );
    print_manage_menu();
    print_scheduled_tickets_menu();

?>

<div align="center">
    <form name="edit_frequency" method="post" action="<?php echo $t_edit_action; ?>">
        <?php
            echo form_security_field( 'manage_frequency_edit' );
        ?>

        <input type="hidden" name="frequency_id" value="<?php echo $t_frequency_id; ?>" />
        <input type="hidden" name="old_name" value="<?php echo string_html_specialchars( $t_frequency['name'] ); ?>" />
        <input type="hidden" name="old_enabled" value="<?php echo $t_frequency['enabled']; ?>" />
        <input type="hidden" name="old_minute" value="<?php echo $t_frequency['minute']; ?>" />
        <input type="hidden" name="old_hour" value="<?php echo $t_frequency['hour']; ?>" />
        <input type="hidden" name="old_day_of_month" value="<?php echo $t_frequency['day_of_month']; ?>" />
        <input type="hidden" name="old_month" value="<?php echo $t_frequency['month']; ?>" />
        <input type="hidden" name="old_day_of_week" value="<?php echo $t_frequency['day_of_week']; ?>" />

        <table class="width75" cellspacing="1">
            <tr>
                <td class="form-title" colspan="5">
                    <?php echo $t_page_title; ?>
                </td>
            </tr>

            <!-- Name -->
            <tr <?php echo helper_alternate_class();?>>
                <td class="category" width="20%">
                    <span class="required">*</span><?php echo plugin_lang_get( 'frequency_name' ); ?>
                </td>
                <td width="80%" colspan="4">
                    <input <?php echo helper_get_tab_index(); ?> type="text" name="name" size="60" maxlength="128" value="<?php echo $t_frequency['name']; ?>" />
                </td>
            </tr>

            <!-- Name -->
            <tr <?php echo helper_alternate_class();?>>
                <td class="category" width="20%">
                    <span class="required">*</span><?php echo plugin_lang_get( 'frequency_enabled' ); ?>
                </td>
                <td width="80%" colspan="4">
                    <input <?php echo helper_get_tab_index(); ?> type="checkbox" name="enabled" <?php echo $t_frequency['enabled'] ? 'checked' : '' ?> />
                </td>
            </tr>

            <tr <?php echo helper_alternate_class(); ?>>
                <td class="category" width="20%">
                    <span class="required">*</span><?php echo plugin_lang_get( 'frequency_day_of_week' ); ?>
                </td>
                <td class="category" width="20%">
                    <span class="required">*</span><?php echo plugin_lang_get( 'frequency_month' ); ?>
                </td>
                <td class="category" width="20%">
                    <span class="required">*</span><?php echo plugin_lang_get( 'frequency_day_of_month' ); ?>
                </td>
                <td class="category" width="20%">
                    <span class="required">*</span><?php echo plugin_lang_get( 'frequency_hour' ); ?>
                </td>
                <td class="category" width="20%">
                    <span class="required">*</span><?php echo plugin_lang_get( 'frequency_minute' ); ?>
                </td>
            </tr>

            <tr <?php echo helper_alternate_class(); ?>>
                <!-- day of week -->
                <td width="20%">
                    <?php frequency_helper_render_day_of_week_options( $t_frequency['day_of_week'] ); ?>
                </td>

                <!-- month -->
                <td width="20%">
                    <?php frequency_helper_render_month_options( $t_frequency['month'] ); ?>
                </td>

                <!-- day of month -->
                <td width="20%">
                    <?php frequency_helper_render_day_of_month_options( $t_frequency['day_of_month'] ); ?>
                </td>

                <!-- hour -->
                <td width="20%">
                    <?php frequency_helper_render_hour_options( $t_frequency['hour'] ); ?>
                </td>

                <!-- minute -->
                <td width="20%">
                    <?php frequency_helper_render_minute_options( $t_frequency['minute'] ); ?>
                </td>
            </tr>

            <!-- buttons -->
            <tr>
                <td colspan="5">
                    <input type="submit" class="button" value="<?php echo plugin_lang_get( 'frequency_update' ); ?>" />
                </td>
            </tr>
        </table>
    </form>
</div>
<br />

<?php
    if( ( 0 == $t_frequency['template_count'] ) && ( 0 == $t_frequency['bug_count'] ) ) {
?>
    <!-- delete -->
    <div class="border center">
        <form name="delete_frequency" method="post" action="<?php echo $t_delete_page; ?>">
            <input type="hidden" name="id" value="<?php echo $t_frequency_id; ?>" />
            <input type="submit" class="button" value="<?php echo plugin_lang_get( 'frequency_delete' ); ?>" />
        </form>
    </div>
    <br />
<?php
    }
?>

<!-- History -->
<a name="history" id="history" />
<?php
    collapse_open( 'history' );
    $t_history = frequency_get_history( $t_frequency_id );
    $t_normal_date_format = config_get( 'normal_date_format' );
?>
<table class="width100" cellspacing="0">
    <tr>
        <td class="form-title" colspan="4">
            <?php
                collapse_icon( 'history' );
                echo plugin_lang_get( 'frequency_history' );
            ?>
        </td>
    </tr>
    <tr class="row-category-history">
        <td class="small-caption">
            <?php echo lang_get( 'date_modified' ); ?>
        </td>
        <td class="small-caption">
            <?php echo lang_get( 'username' ); ?>
        </td>
        <td class="small-caption">
            <?php echo lang_get( 'field' ); ?>
        </td>
        <td class="small-caption">
            <?php echo lang_get( 'change' ); ?>
        </td>
    </tr>
    <?php
        foreach ( $t_history as $t_item ) {
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
                if( FREQUENCY_CHANGED == $t_item['type'] ) {
                    echo plugin_lang_get( 'frequency_' . $t_item['field_name'] );
                }
            ?>
        </td>
        <td class="small-caption">
            <?php
                switch( $t_item['type'] ) {
                    case FREQUENCY_ADDED:
                        echo plugin_lang_get( 'frequency_added' );
                        break;
                    case FREQUENCY_ENABLED:
                        echo plugin_lang_get( 'frequency_enabled' );
                        break;
                    case FREQUENCY_DISABLED:
                        echo plugin_lang_get( 'frequency_disabled' );
                        break;
                    case FREQUENCY_CHANGED:
                        switch( $t_item['field_name'] ) {
                            case 'name':
                            case 'enabled':
                                echo $t_item['old_value'] . ' => ' . $t_item['new_value'];
                                break;
                            case 'minute':
                                echo frequency_helper_minute_column( $t_item['old_value'] ) . ' => ' . frequency_helper_minute_column( $t_item['new_value'] );
                                break;
                            case 'hour':
                                echo frequency_helper_hour_column( $t_item['old_value'] ) . ' => ' . frequency_helper_hour_column( $t_item['new_value'] );
                                break;
                            case 'day_of_month':
                                echo frequency_helper_day_of_month_column( $t_item['old_value'] ) . ' => ' . frequency_helper_day_of_month_column( $t_item['new_value'] );
                                break;
                            case 'month':
                                echo frequency_helper_month_column( $t_item['old_value'] ) . ' => ' . frequency_helper_month_column( $t_item['new_value'] );
                                break;
                            case 'day_of_week':
                                echo frequency_helper_day_of_week_column( $t_item['old_value'] ) . ' => ' . frequency_helper_day_of_week_column( $t_item['new_value'] );
                                break;
                        }
                        break;
                    case FREQUENCY_DELETED:
                        # we should never really get here... if the frequency was deleted, we wouldn't be rendering this page...
                        echo plugin_lang_get( 'frequency_deleted' );
                        break;
                }
            ?>
        </td>
    </tr>
    <?php
        } # end for loop
    ?>
</table>
<?php
	collapse_closed( 'history' );
?>
<table class="width100" cellspacing="0">
<tr>
	<td class="form-title" colspan="4">
        <?php
            collapse_icon( 'history' );
            echo plugin_lang_get( 'frequency_history' );
        ?>
	</td>
</tr>
</table>

<?php
	collapse_end( 'history' );
    html_page_bottom();
?>

<script type="text/javascript">
    focus_on_first_element();
</script>
