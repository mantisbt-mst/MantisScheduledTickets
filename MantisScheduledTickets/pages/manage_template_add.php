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

    form_security_validate( 'add_template' );

    $f_summary = gpc_get_string( 'summary' );
    $f_description = gpc_get_string( 'description' );
    $f_enabled = gpc_get_bool( 'enabled', false );
    $f_command = gpc_get_string( 'command', null );
    $f_diff_flag = gpc_get_bool( 'diff_flag', false );

    $t_template_id = template_add( $f_summary, $f_description, $f_enabled, $f_command, $f_diff_flag );
    template_log_event_special( $t_template_id, MST_TEMPLATE_ADDED );

    if( false === $f_enabled ) {
        template_log_event_special( $t_template_id, MST_TEMPLATE_DISABLED );
    }

    form_security_purge( 'add_template' );

    $t_redirect_url = plugin_page( 'manage_template_page', true );

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
