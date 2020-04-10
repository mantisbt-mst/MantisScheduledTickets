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

    form_security_validate( 'manage_template_delete' );

    $t_template_id = gpc_get_int( 'id' );

    helper_ensure_confirmed( plugin_lang_get( 'template_delete_sure_msg' ), plugin_lang_get( 'template_delete' ) );

    $t_template = template_get_row( $t_template_id );

    if( 0 != $t_template['bug_count'] ) {
        error_parameters( plugin_lang_get( 'error_template_cannot_be_deleted' ), plugin_lang_get( 'title' ) );
        trigger_error( ERORR_PLUGIN_GENERIC, ERROR );
    }

    template_delete( $t_template_id );
    template_log_event_special( $t_template_id, MST_TEMPLATE_DELETED );

    form_security_purge( 'manage_template_delete' );

    $t_redirect_url = plugin_page( 'manage_template_page', true );

    layout_page_header( null, $t_redirect_url );
    layout_page_begin();
    html_operation_successful( $t_redirect_url );
    layout_page_end();
