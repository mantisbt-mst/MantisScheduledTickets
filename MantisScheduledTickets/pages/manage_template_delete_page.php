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

    form_security_validate( 'delete_template' );

    $t_template_id = gpc_get_int( 'id' );
    $t_manage_template_page = plugin_page( 'manage_template_page', true );

    helper_ensure_confirmed( plugin_lang_get( 'template_delete_sure_msg' ), plugin_lang_get( 'template_delete' ) );

    $t_template = template_get_row( $t_template_id );

    if( 0 != $t_template['bug_count'] ) {
        plugin_error( plugin_lang_get( 'error_template_cannot_be_deleted' ), ERROR );
    }

    template_delete( $t_template_id );
    template_log_event_special( $t_template_id, MST_TEMPLATE_DELETED );

    form_security_purge( 'delete_template' );

    html_page_top( null, $t_manage_template_page );

?>

<br />
<div align="center">
<?php
	echo lang_get( 'operation_successful' ) . '<br />';
	print_bracket_link( $t_manage_template_page, lang_get( 'proceed' ) );
?>
</div>

<?php
	html_page_bottom();
