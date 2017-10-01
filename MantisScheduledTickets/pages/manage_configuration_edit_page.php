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

    global $g_plugin_cache;

    layout_page_header( plugin_lang_get( 'title_configuration' ) );
    layout_page_begin();
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

<div class="col-md-12 col-xs-12">
    <div class="space-10"></div>
    <div class="form-container">
        <form name="edit_config" id="edit_config" method="post" action="<?php echo $t_edit_action; ?>">
            <?php
                echo form_security_field( 'manage_configuration' );
            ?>

            <div class="widget-box widget-color-blue2">
                <div class="widget-header widget-header-small">
                    <h4>
                        <i class="ace-icon fa fa-newspaper-o"></i>
                        <?php echo plugin_lang_get( 'title_configuration' ); ?>
                    </h4>
                </div>
                <div class="widget-body">
                    <div class="widget-main no-padding">
                        <div class="table-responsive">
                            <table id="templates" class="table table-bordered table-condensed table-hover table-striped">
                                <tbody>
                                    <tr>
                                        <td class="category">
                                            <?php echo plugin_lang_get( 'config_wget_command_available' ); ?>
                                        </td>
                                        <td>
                                            <i class="fa fa-square-o fa-xlg <?php echo ( 0 == $t_return_code_wget ) ? 'config_ok' : 'config_not_ok'; ?>"></i>
                                            <?php echo ( 0 == $t_return_code_wget ) ? plugin_lang_get( 'config_ok' ) : plugin_lang_get( 'config_not_ok' ); ?>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td class="category">
                                            <?php echo plugin_lang_get( 'config_crontab_command_available' ); ?>
                                        </td>
                                        <td>
                                            <i class="fa fa-square-o fa-xlg <?php echo ( 0 == $t_return_code_crontab ) ? 'config_ok' : 'config_not_ok'; ?>"></i>
                                            <?php echo ( 0 == $t_return_code_crontab ) ? plugin_lang_get( 'config_ok' ) : plugin_lang_get( 'config_not_ok' ); ?>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td class="category">
                                            <?php echo plugin_lang_get( 'config_manage_threshold' ); ?>
                                        </td>
                                        <td>
                                            <select name="manage_threshold" id="manage_threshold">
                                                <?php print_enum_string_option_list( 'access_levels', plugin_config_get( 'manage_threshold' ) ); ?>
                                            </select>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td class="category">
                                            <span class="required">*</span><?php echo plugin_lang_get( 'config_email_reports_to' ); ?>
                                        </td>
                                        <td>
                                            <input <?php echo helper_get_tab_index(); ?> type="text" name="email_reports_to" id="email_reports_to" size="60" maxlength="128" value="<?php echo plugin_config_get( 'email_reports_to' ); ?>" />
                                        </td>
                                    </tr>

                                    <tr>
                                        <td class="category">
                                            <?php echo plugin_lang_get( 'config_send_email_on_success' ); ?>
                                        </td>
                                        <td>
                                            <input <?php echo helper_get_tab_index(); ?> type="checkbox" name="send_email_on_success" id="send_email_on_success"<?php echo ( 1 == plugin_config_get( 'send_email_on_success' ) ) ? ' checked="checked"' : ''; ?> />
                                        </td>
                                    </tr>

                                    <tr>
                                        <td class="category">
                                            <span class="required">*</span><?php echo plugin_lang_get( 'config_auto_reporter_username' ); ?>
                                        </td>
                                        <td>
                                            <i class="fa fa-square-o fa-xlg <?php echo ( false === $t_auto_reporter_id ) ? 'config_not_ok' : 'config_ok'; ?>"></i>
                                            <input <?php echo helper_get_tab_index(); ?> type="text" name="auto_reporter_username" size="60" maxlength="128" value="<?php echo $t_auto_reporter_username; ?>" />
                                        </td>
                                    </tr>

                                    <tr>
                                        <td class="category">
                                            <span class="required">*</span><?php echo plugin_lang_get( 'config_crontab_base_url' ); ?>
                                        </td>
                                        <td>
                                            <input <?php echo helper_get_tab_index(); ?> type="text" name="crontab_base_url" id="crontab_base_url" size="60" maxlength="128" value="<?php echo $t_crontab_base_url; ?>" />
                                        </td>
                                    </tr>

                                    <tr>
                                        <td class="category">
                                            <?php echo plugin_lang_get( 'config_enable_commands' ); ?>
                                        </td>
                                        <td>
                                            <input <?php echo helper_get_tab_index(); ?> type="checkbox" name="enable_commands" id="enable_commands"<?php echo ( 1 == plugin_config_get( 'enable_commands' ) ) ? ' checked="checked"' : ''; ?> />
                                        </td>
                                    </tr>

                                    <tr>
                                        <td class="category">
                                            <?php echo plugin_lang_get( 'config_check_for_updates' ); ?>
                                        </td>
                                        <td>
                                            <input <?php echo helper_get_tab_index(); ?> type="checkbox" name="check_for_updates" id="check_for_updates"<?php echo ( 1 == $t_check_for_updates ) ? ' checked="checked"' : ''; ?> />
                                        </td>
                                    </tr>

                                    <?php
                                    if( $t_check_for_updates )
                                    {
                                        $t_installed_version = $g_plugin_cache[plugin_get_current()]->version;
                                        $t_latest_version = mst_core_get_latest_plugin_version();
                                        $t_is_latest = mst_core_is_latest_plugin_version( $t_installed_version, $t_latest_version );

                                        $t_installed_version_class = ( 'N/A' == $t_latest_version ) ? 'config_warn' : ( $t_is_latest ? 'config_ok' : 'config_not_ok' );

                                        switch ( $t_latest_version )
                                        {
                                            case '':
                                                $t_latest_version_class = 'config_not_ok';
                                                break;
                                            case 'N/A':
                                                $t_latest_version_class = 'config_warn';
                                                break;
                                            default:
                                                $t_latest_version_class = 'config_ok';
                                                break;
                                        }
                                        $t_latest_version = ( '' == $t_latest_version ) ?
                                            plugin_lang_get( 'config_unable_to_check_for_updates' ) :
                                            ( 'N/A' == $t_latest_version ) ? plugin_lang_get( 'config_update_info_unknown' ) : $t_latest_version;
                                    ?>
                                        <tr>
                                            <td class="category" id="installed_version">
                                                <?php echo plugin_lang_get( 'config_installed_version' ); ?>
                                            </td>
                                            <td>
                                                <i class="fa fa-square-o fa-xlg <?php echo $t_installed_version_class; ?>"></i>
                                                <?php echo $t_installed_version . ' (' . lang_get( 'mantis_version' ) . ': ' . MANTIS_VERSION . ')'; ?>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td class="category" id="latest_version">
                                                <?php echo plugin_lang_get( 'config_latest_version' ); ?>
                                            </td>
                                            <td>
                                                <i class="fa fa-square-o fa-xlg <?php echo $t_latest_version_class; ?>"></i>
                                                <?php echo $t_latest_version; ?>
                                            </td>
                                        </tr>
                                    <?php
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="widget-toolbox padding-8 clearfix">
                            <span class="required pull-right"> * <?php echo lang_get( 'required' ); ?></span>
                            <input type="submit" class="btn btn-primary btn-white btn-round" value="<?php echo plugin_lang_get( 'config_save' ); ?>" />
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<?php
    layout_page_end();
