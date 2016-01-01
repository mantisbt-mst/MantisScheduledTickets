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

define( 'MST_CONFIGURATION_PAGE', 1 );
define( 'MST_MANAGE_FREQUENCY_PAGE', 2 );
define( 'MST_MANAGE_TEMPLATE_PAGE', 3 );

define( 'STATUS_CODE_OK', 0 );
define( 'STATUS_CODE_INVALID_PROJECT', 1 );
define( 'STATUS_CODE_INVALID_CATEGORY', 2 );
define( 'STATUS_CODE_INVALID_USER', 4 );

/**
 * Print a custom menu, specific to the ScheduledTickets plugin
 *
 * @param int $p_page Current page. If this value is provided, the corresponding menu option is NOT a link
 * @return void
 */
function print_scheduled_tickets_menu( $p_page = null ) {
    echo '<div align="center">';

    print_bracket_link( ( MST_CONFIGURATION_PAGE === $p_page ) ? '' : plugin_page( 'manage_configuration_edit_page' ), plugin_lang_get( 'manage_configuration_link' ) );
    print_bracket_link( ( MST_MANAGE_FREQUENCY_PAGE === $p_page ) ? '' : plugin_page( 'manage_frequency_page' ), plugin_lang_get( 'manage_frequency_link' ) );
    print_bracket_link( ( MST_MANAGE_TEMPLATE_PAGE === $p_page ) ? '' : plugin_page( 'manage_template_page' ), plugin_lang_get( 'manage_template_link' ) );

    echo '</div>';
}

/**
 * Add a bug history record
 *
 * @param int $p_bug_id Bug idate
 * @param int $p_frequency_id Frequency id
 * @param int $p_template_id Template id
 * @param int $p_status_code Status code
 * @return void
 */
function bug_history_add( $p_bug_id, $p_frequency_id, $p_template_id, $p_status_code ) {
    $t_bug_history_table = plugin_table( 'bug_history' );

    $query = "INSERT INTO $t_bug_history_table
                (date_submitted, bug_id, frequency_id, template_id, status_code)
                VALUES
                (" . db_param() . ", " . db_param() . ", " . db_param() . ", ". db_param() . ", ". db_param() . ");";
    db_query_bound( $query, array( db_now(), $p_bug_id, $p_frequency_id, $p_template_id, $p_status_code ) );
}

/**
 * Generate and send an email with the given subject/body
 *
 * @param string $p_subject Email subject
 * @param string $p_body Email body
 * @return void
 */
function mst_email_send( $p_subject, $p_body ) {
    $t_email_data = new EmailData;

    $t_email_data->email = plugin_config_get( 'email_reports_to' );
    $t_email_data->subject = $p_subject;
    $t_email_data->body = $p_body;
    $t_email_data->metadata = array();
    $t_email_data->metadata['headers'] = $p_headers === null ? array() : $p_headers;
    $t_email_data->metadata['priority'] = config_get( 'mail_priority' );
    $t_email_data->metadata['charset'] = 'utf-8';

    $t_hostname = '';
    $t_server = isset( $_SERVER ) ? $_SERVER : $HTTP_SERVER_VARS;
    if( isset( $t_server['SERVER_NAME'] ) ) {
        $t_hostname = $t_server['SERVER_NAME'];
    } else {
        $t_address = explode( '@', config_get( 'from_email' ) );
        if( isset( $t_address[1] ) ) {
            $t_hostname = $t_address[1];
        }
    }
    $t_email_data->metadata['hostname'] = $t_hostname;

    $t_email_id = email_queue_add( $t_email_data );
}

/**
 * Determine whether plugin tables already exist
 *
 * Attempt to determine whether the plugin tables exist or not. This is a *hack*,
 * things would be much simpler if the Mantis plugin architecture would offer a
 * post-install hook/event/overrideable method/callback, to perform more actions
 * once the "schema()" method executes. For now, this will have to do...
 *
 * @return bool True if plugin tables exist, false otherwise
 */
function st_tables_exist() {
    global $g_database_name;

    $t_frequency_table = plugin_table( 'frequency' );
    $t_template_category_table = plugin_table( 'template_category' );
    $t_bug_history_table = plugin_table( 'bug_history' );

    $query = "SELECT
                COUNT(*) AS table_count
            FROM information_schema.tables
            WHERE table_schema = '$g_database_name'
                AND table_name IN
                    (
                        '$t_frequency_table',
                        '$t_template_category_table',
                        '$t_bug_history_table'
                    );";
    $result = db_query_bound( $query );

    $row = db_fetch_array( $result );

    if( 3 != $row['table_count'] ) {
        return false;
    }

    return true;
}
