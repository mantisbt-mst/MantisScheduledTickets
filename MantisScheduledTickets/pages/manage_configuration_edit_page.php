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
    $g_MantisScheduledTickets_context = true;

    global $g_plugin_cache;

    html_page_top( plugin_lang_get( 'title_configuration' ) );
    print_manage_menu();
    mst_core_print_scheduled_tickets_menu( MST_CONFIGURATION_PAGE );

    $t_edit_action = plugin_page( 'manage_configuration_edit' );
    $t_auto_reporter_username = plugin_config_get( 'auto_reporter_username' );
    $t_auto_reporter_id = user_get_id_by_name( $t_auto_reporter_username );
    $t_crontab_base_url = plugin_config_get( 'crontab_base_url' );

    $t_check_for_updates = plugin_config_get( 'check_for_updates' );

    exec( 'wget -V', $t_wget_output, $t_return_code_wget );
    exec( 'crontab -l', $t_crontab_output, $t_return_code_crontab );
?>

<br/>
<div align="center">
    <form name="edit_config" id="edit_config" method="post" action="<?php echo $t_edit_action; ?>">
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
                    <select name="manage_threshold" id="manage_threshold">
                        <?php print_enum_string_option_list( 'access_levels', plugin_config_get( 'manage_threshold' ) ); ?>
                    </select>
                </td>
            </tr>

            <tr <?php echo helper_alternate_class();?>>
                <td class="category" width="30%">
                    <span class="required">*</span><?php echo plugin_lang_get( 'config_email_reports_to' ); ?>
                </td>
                <td width="80%">
                    <input <?php echo helper_get_tab_index(); ?> type="text" name="email_reports_to" id="email_reports_to" size="60" maxlength="128" value="<?php echo plugin_config_get( 'email_reports_to' ); ?>" />
                </td>
            </tr>

            <tr <?php echo helper_alternate_class();?>>
                <td class="category" width="30%">
                    <?php echo plugin_lang_get( 'config_send_email_on_success' ); ?>
                </td>
                <td width="80%">
                    <input <?php echo helper_get_tab_index(); ?> type="checkbox" name="send_email_on_success" id="send_email_on_success"<?php echo ( 1 == plugin_config_get( 'send_email_on_success' ) ) ? ' checked="checked"' : ''; ?> />
                </td>
            </tr>

            <tr <?php echo helper_alternate_class();?>>
                <td class="category" width="30%">
                    <span class="required">*</span><?php echo plugin_lang_get( 'config_auto_reporter_username' ); ?>
                </td>
                <td width="80%" class="<?php echo ( false === $t_auto_reporter_id ) ? 'config_not_ok' : 'config_ok'; ?>">
                    <input <?php echo helper_get_tab_index(); ?> type="text" name="auto_reporter_username" id="auto_reporter_username" size="60" maxlength="128" value="<?php echo $t_auto_reporter_username; ?>" />
                </td>
            </tr>

            <tr <?php echo helper_alternate_class();?>>
                <td class="category" width="30%">
                    <span class="required">*</span><?php echo plugin_lang_get( 'config_crontab_base_url' ); ?>
                </td>
                <td width="80%">
                    <input <?php echo helper_get_tab_index(); ?> type="text" name="crontab_base_url" id="crontab_base_url" size="60" maxlength="128" value="<?php echo $t_crontab_base_url; ?>" />
                </td>
            </tr>

            <tr <?php echo helper_alternate_class();?>>
                <td class="category" width="30%">
                    <?php echo plugin_lang_get( 'config_enable_commands' ); ?>
                </td>
                <td width="80%">
                    <input <?php echo helper_get_tab_index(); ?> type="checkbox" name="enable_commands" id="enable_commands"<?php echo ( 1 == plugin_config_get( 'enable_commands' ) ) ? ' checked="checked"' : ''; ?> />
                </td>
            </tr>

            <tr <?php echo helper_alternate_class();?>>
                <td class="category" width="30%">
                    <?php echo plugin_lang_get( 'config_check_for_updates' ); ?>
                </td>
                <td width="80%">
                    <input <?php echo helper_get_tab_index(); ?> type="checkbox" name="check_for_updates" id="check_for_updates"<?php echo ( 1 == $t_check_for_updates ) ? ' checked="checked"' : ''; ?> />
                </td>
            </tr>

            <?php
            if( $t_check_for_updates )
            {
                $t_installed_version = $g_plugin_cache[plugin_get_current()]->version;
                list( $t_latest_version, $t_tested ) = mst_core_get_plugin_version_info( $t_installed_version );
                $t_is_latest = $t_installed_version == $t_latest_version;

                $t_installed_version_class = ( $t_latest_version == $t_installed_version ) ? 'config_ok' : 'config_warn';
                $t_latest_version_class = ( null == $t_latest_version ) ? 'config_not_ok' : 'config_ok';
                $t_latest_version = ( null == $t_latest_version ) ?                                             plugin_lang_get( 'config_unable_to_check_for_updates' ) : $t_latest_version;

                if ( null === $t_tested )
                {
                    $t_tested_class = 'config_not_ok';
                }
                else
                {
                    $t_tested_class = $t_tested ? 'config_ok' : 'config_warn';
                }
            ?>
                <tr <?php echo helper_alternate_class();?>>
                    <td class="category" width="30%" id="installed_version">
                        <?php echo plugin_lang_get( 'config_installed_version' ); ?>
                    </td>
                    <td width="80%" class="<?php echo $t_installed_version_class; ?>">
                        <?php echo $t_installed_version . ' (' . lang_get( 'mantis_version' ) . ': ' . MANTIS_VERSION . ')'; ?>
                    </td>
                </tr>

                <tr <?php echo helper_alternate_class();?>>
                    <td class="category" width="30%" id="latest_version">
                        <?php echo plugin_lang_get( 'config_latest_version' ); ?>
                    </td>
                    <td width="80%" class="<?php echo $t_latest_version_class; ?>">
                        <?php echo $t_latest_version; ?>
                    </td>
                </tr>

                <tr>
                    <td class="category" width="30%" id="tested">
                        <?php echo plugin_lang_get( 'tested' ); ?>
                    </td>
                    <td width="80%" class="<?php echo $t_tested_class; ?>">
                        <?php
                            if ( null !== $t_tested )
                            {
                                echo $t_tested ? plugin_lang_get( 'config_ok' ) : plugin_lang_get( 'config_update_info_unknown' );
                            }
                        ?>
                    </td>
                </tr>
            <?php
            }
            ?>

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
