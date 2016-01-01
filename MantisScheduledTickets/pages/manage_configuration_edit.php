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

    form_security_validate( 'manage_configuration' );

    $f_manage_threshold = gpc_get_int( 'manage_threshold' );
    $f_email_reports_to = gpc_get_string( 'email_reports_to' );
    $f_send_email_on_successful_auto_report = gpc_get_bool( 'send_email_on_successful_auto_report' );
    $f_auto_reporter_username = gpc_get_string( 'auto_reporter_username' );

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

    if( plugin_config_get( 'send_email_on_successful_auto_report' ) != $f_send_email_on_successful_auto_report ) {
        plugin_config_set( 'send_email_on_successful_auto_report', $f_send_email_on_successful_auto_report );
    }

    if( plugin_config_get( 'auto_reporter_username' ) != $f_auto_reporter_username ) {
        plugin_config_set( 'auto_reporter_username', $f_auto_reporter_username );
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
