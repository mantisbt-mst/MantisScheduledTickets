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

    html_page_top( plugin_lang_get( 'title_configuration' ) );
    print_manage_menu();
    print_scheduled_tickets_menu( MST_CONFIGURATION_PAGE );

    $t_edit_action = plugin_page( 'manage_configuration_edit' );
    $t_auto_reporter_username = plugin_config_get( 'auto_reporter_username' );
    $t_auto_reporter_id = user_get_id_by_name( $t_auto_reporter_username );

    exec( 'wget -V', $t_wget_output, $t_return_code_wget );
    exec( 'crontab -l', $t_crontab_output, $t_return_code_crontab );
?>

<br/>
<div align="center">
    <form name="edit_frequency" method="post" action="<?php echo $t_edit_action; ?>">
        <?php
            echo form_security_field( 'manage_configuration' );
        ?>
        <table class="width75" cellspacing="1">
            <tr>
                <td class="form-title" colspan="2">
                    <?php echo $t_page_title; ?>
                </td>
            </tr>

            <tr <?php echo helper_alternate_class();?>>
                <td class="category" width="30%">
                    <?php echo plugin_lang_get( 'config_wget_command_available' ); ?>
                </td>
                <td width="80%" class="<?php echo ( 0 == $t_return_code_wget ) ? 'config_ok' : 'config_not_ok'; ?>">
                    <?php echo ( 0 == $t_return_code_wget ) ? plugin_lang_get( 'config_ok' ) : plugin_lang_get( 'config_not_ok' ); ?>
                </td>
            </tr>

            <tr <?php echo helper_alternate_class();?>>
                <td class="category" width="30%">
                    <?php echo plugin_lang_get( 'config_crontab_command_available' ); ?>
                </td>
                <td width="80%" class="<?php echo ( 0 == $t_return_code_crontab ) ? 'config_ok' : 'config_not_ok'; ?>">
                    <?php echo ( 0 == $t_return_code_crontab ) ? plugin_lang_get( 'config_ok' ) : plugin_lang_get( 'config_not_ok' ); ?>
                </td>
            </tr>

            <tr <?php echo helper_alternate_class();?>>
                <td class="category" width="30%">
                    <?php echo plugin_lang_get( 'config_manage_threshold' ); ?>
                </td>
                <td width="80%">
                    <select name="manage_threshold">
                        <?php print_enum_string_option_list( 'access_levels', plugin_config_get( 'manage_threshold' ) ); ?>
                    </select>
                </td>
            </tr>

            <tr <?php echo helper_alternate_class();?>>
                <td class="category" width="30%">
                    <span class="required">*</span><?php echo plugin_lang_get( 'config_email_reports_to' ); ?>
                </td>
                <td width="80%">
                    <input <?php echo helper_get_tab_index(); ?> type="text" name="email_reports_to" size="60" maxlength="128" value="<?php echo plugin_config_get( 'email_reports_to' ); ?>" />
                </td>
            </tr>

            <tr <?php echo helper_alternate_class();?>>
                <td class="category" width="30%">
                    <?php echo plugin_lang_get( 'config_send_email_on_successful_auto_report' ); ?>
                </td>
                <td width="80%">
                    <input <?php echo helper_get_tab_index(); ?> type="checkbox" name="send_email_on_successful_auto_report"<?php echo ( 1 == plugin_config_get( 'send_email_on_successful_auto_report' ) ) ? ' checked' : ''; ?> />
                </td>
            </tr>

            <tr <?php echo helper_alternate_class();?>>
                <td class="category" width="30%">
                    <span class="required">*</span><?php echo plugin_lang_get( 'config_auto_reporter_username' ); ?>
                </td>
                <td width="80%" class="<?php echo ( false === $t_auto_reporter_id ) ? 'config_not_ok' : 'config_ok'; ?>">
                    <input <?php echo helper_get_tab_index(); ?> type="text" name="auto_reporter_username" size="60" maxlength="128" value="<?php echo $t_auto_reporter_username; ?>" />
                </td>
            </tr>

            <!-- buttons -->
            <tr>
                <td colspan="5">
                    <input type="submit" class="button" value="<?php echo plugin_lang_get( 'config_save' ); ?>" />
                </td>
            </tr>
        </table>
    </form>
</div>

<?php
    html_page_bottom();
