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

    form_security_validate( 'manage_frequency_delete' );

    $t_frequency_id = gpc_get_int( 'id' );

    helper_ensure_confirmed( plugin_lang_get( 'frequency_delete_sure_msg' ), plugin_lang_get( 'frequency_delete' ) );

    $t_frequency = frequency_get_row( $t_frequency_id );

    if( ( 0 != $t_frequency['template_count'] ) || ( 0 != $t_frequency['bug_count'] ) ) {
        error_parameters( plugin_lang_get( 'error_frequency_cannot_be_deleted' ), plugin_lang_get( 'title' ) );
        trigger_error( ERROR_PLUGIN_GENERIC, ERROR );
    }

    frequency_delete( $t_frequency_id );
    frequency_log_event_special( $t_frequency_id, MST_FREQUENCY_DELETED );
    cron_regenerate_crontab_file();
    cron_validate_crontab_file();

    form_security_purge( 'manage_frequency_delete' );

    $t_redirect_url = plugin_page( 'manage_frequency_page', true );

    layout_page_header( null, $t_redirect_url );
    layout_page_begin();
    html_operation_successful( $t_redirect_url );
    layout_page_end();
