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
    $g_MantisScheduledTickets_context = true;

    $t_page_title = plugin_lang_get( 'title_add_frequency' );

    html_page_top( $t_page_title );
    print_manage_menu();
    mst_core_print_scheduled_tickets_menu();

    $t_action_page = plugin_page( 'manage_frequency_add' );

?>

<div align="center">
    <form name="add_frequency" id="add_frequency" method="post" action="<?php echo $t_action_page; ?>">
        <?php
            echo form_security_field( 'add_frequency' );
        ?>

        <table class="width75" cellspacing="1">
            <tr>
                <td class="form-title" colspan="5">
                    <?php echo $t_page_title; ?>
                </td>
            </tr>

            <tr <?php echo helper_alternate_class() ?>>
                <td class="category" width="20%">
                    <span class="required">*</span><?php echo plugin_lang_get( 'frequency_name' ); ?>
                </td>
                <td width="80%" colspan="4">
                    <input <?php echo helper_get_tab_index() ?> type="text" name="name" id="name" size="60" maxlength="128" />
                </td>
            </tr>

            <tr <?php echo helper_alternate_class() ?>>
                <td class="category" width="20%">
                    <span class="required">*</span><?php echo plugin_lang_get( 'frequency_enabled' ); ?>
                </td>
                <td width="80%" colspan="4">
                    <input <?php echo helper_get_tab_index() ?> type="checkbox" name="enabled" id="enabled" checked="checked" />
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
                    <?php mst_helper_render_day_of_week_options(); ?>
                </td>

                <!-- month -->
                <td width="20%">
                    <?php mst_helper_render_month_options(); ?>
                </td>

                <!-- day of month -->
                <td width="20%">
                    <?php mst_helper_render_day_of_month_options(); ?>
                </td>

                <!-- hour -->
                <td width="20%">
                    <?php mst_helper_render_hour_options(); ?>
                </td>

                <!-- minute -->
                <td width="20%">
                    <?php mst_helper_render_minute_options(); ?>
                </td>
            </tr>

            <!-- buttons -->
            <tr>
                <td colspan="5">
                    <input <?php echo helper_get_tab_index() ?> type="submit" class="button" value="<?php echo plugin_lang_get( 'frequency_add' ); ?>" />
                </td>
            </tr>
        </table>
    </form>
</div>

<?php
    html_page_bottom();
?>

<script type="text/javascript">
    focus_on_first_element();
</script>
