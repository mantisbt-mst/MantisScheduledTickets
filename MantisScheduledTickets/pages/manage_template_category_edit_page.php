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

    $t_page_title = plugin_lang_get( 'title_edit_template_category' );
    $t_edit_action = plugin_page( 'manage_template_category_edit' );

    $t_id = gpc_get_int( 'id' );
    $t_template_id = gpc_get_int( 'template_id' );
    $t_category = template_category_get( $t_id );

    html_page_top( $t_page_title );
    print_manage_menu();
    print_scheduled_tickets_menu();

?>

<div align="center">
    <form name="edit_template_category" method="post" action="<?php echo $t_edit_action; ?>">
        <?php
            echo form_security_field( 'manage_template_category_edit' );
        ?>

        <input type="hidden" name="id" value="<?php echo $t_id; ?>" />
        <input type="hidden" name="template_id" value="<?php echo $t_template_id; ?>" />
        <input type="hidden" name="category_id" value="<?php echo $t_category['category_id']; ?>">
        <input type="hidden" name="old_frequency_id" value="<?php echo $t_category['frequency_id']; ?>" />
        <input type="hidden" name="old_user_id" value="<?php echo $t_category['user_id']; ?>" />

        <table class="width75" cellspacing="1">
            <tr>
                <td class="form-title" colspan="2">
                    <?php echo $t_page_title; ?>
                </td>
            </tr>

            <tr <?php echo helper_alternate_class();?>>
                <td class="category" width="20%">
                    <?php echo lang_get( 'category' ); ?>
                </td>
                <td width="80%">
                    <input <?php echo helper_get_tab_index(); ?> type="text" name="category" size="50" value="<?php echo string_display( category_full_name( $t_category['category_id'] , true, 0 ) ); ?>" disabled />
                </td>
            </tr>

            <tr <?php echo helper_alternate_class();?>>
                <td class="category" width="20%">
                    <?php echo plugin_lang_get( 'frequency' ); ?>
                </td>
                <td width="80%">
                    <?php template_helper_frequencies( $t_category['frequency_id'] ); ?>
                </td>
            </tr>

            <tr <?php echo helper_alternate_class();?>>
                <td class="category" width="20%">
                    <?php echo lang_get( 'assign_to' ); ?>
                </td>
                <td width="80%">
                    <select name="user_id">
                        <option value="0"><?php echo plugin_lang_get('not_assigned'); ?></option>
                        <?php print_assign_to_option_list( $t_category['user_id'] ); ?>
                    </select>
                </td>
            </tr>

            <tr>
                <td>
                    <input <?php echo helper_get_tab_index(); ?> type="submit" class="button" value="<?php echo plugin_lang_get( 'template_update' ); ?>" />
                </td>
            </tr>
        </table>
    </form>

<?php
    html_page_bottom();
