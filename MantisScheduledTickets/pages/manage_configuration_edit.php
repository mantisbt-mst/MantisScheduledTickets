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

    form_security_validate( 'manage_configuration' );

    $f_manage_threshold = gpc_get_int( 'manage_threshold' );
    $f_email_reports_to = gpc_get_string( 'email_reports_to' );
    $f_send_email_on_success = gpc_get_bool( 'send_email_on_success' );
    $f_auto_reporter_username = gpc_get_string( 'auto_reporter_username' );
    $f_crontab_base_url = gpc_get_string( 'crontab_base_url' );
    $f_enable_commands = gpc_get_bool( 'enable_commands' );
    $f_check_for_updates = gpc_get_bool( 'check_for_updates' );

    if( '' == $f_email_reports_to ) {
        error_parameters( plugin_lang_get( 'config_email_reports_to' ) );
        trigger_error( ERROR_EMPTY_FIELD, ERROR );
        die;
    }

    if( '' == $f_auto_reporter_username ) {
        error_parameters( plugin_lang_get( 'config_auto_reporter_username' ) );
        trigger_error( ERROR_EMPTY_FIELD, ERROR );
        die;
    }

    if( plugin_config_get( 'manage_threshold' ) != $f_manage_threshold ) {
        plugin_config_set( 'manage_threshold', $f_manage_threshold );
    }

    if( plugin_config_get( 'email_reports_to' ) != $f_email_reports_to ) {
        plugin_config_set( 'email_reports_to', $f_email_reports_to );
    }

    if( plugin_config_get( 'send_email_on_success' ) != $f_send_email_on_success ) {
        plugin_config_set( 'send_email_on_success', $f_send_email_on_success );
    }

    if( plugin_config_get( 'auto_reporter_username' ) != $f_auto_reporter_username ) {
        plugin_config_set( 'auto_reporter_username', $f_auto_reporter_username );
    }

    if( plugin_config_get( 'crontab_base_url' ) != $f_crontab_base_url ) {
        plugin_config_set( 'crontab_base_url', $f_crontab_base_url );
    }

    if( plugin_config_get( 'enable_commands' ) != $f_enable_commands ) {
        plugin_config_set( 'enable_commands', $f_enable_commands );

        $t_templates = template_get_all();

        if( is_array( $t_templates ) ) {
            foreach( $t_templates as $t_template ) {
                template_log_event_special( $t_template['id'], MST_TEMPLATE_CONFIG_CHANGED, $f_enable_commands );
            }
        }
    }

    if( plugin_config_get( 'check_for_updates' ) != $f_check_for_updates ) {
        plugin_config_set( 'check_for_updates', $f_check_for_updates );
    }

    form_security_purge( 'manage_configuration' );

    $t_redirect_url = plugin_page( 'manage_configuration_edit_page', true );

    html_page_top( null, $t_redirect_url );

?>

<br />
<div align="center">
<?php
	echo lang_get( 'operation_successful' ) . '<br />';
	print_bracket_link( $t_redirect_url, lang_get( 'proceed' ) );
?>
</div>

<?php
	html_page_bottom();
