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

    global $g_path;

    $t_auto_reporter_username = plugin_config_get( 'auto_reporter_username' );

    $t_auto_reporter_user_id = user_get_id_by_name( $t_auto_reporter_username );

    if ( $_SERVER["REMOTE_ADDR"] != $_SERVER["SERVER_ADDR"] ) {
        plugin_error( plugin_lang_get( 'error_host_access_denied' ), ERROR );
        exit;
    }

    if ( !auth_attempt_script_login( $t_auto_reporter_username ) ) {
        plugin_error( plugin_lang_get( 'error_user_access_denied' ), ERROR );
        exit;
    }

    $f_frequency_id = gpc_get_int( 'frequency_id' );

    $t_template_categories = template_categories_get_by_frequency_id( $f_frequency_id );

    $t_tickets = array();
    $t_invalid_template_categories = array();

    foreach( $t_template_categories as $t_template_category ) {
        $t_bug_id = null;
        $t_status_code = STATUS_CODE_OK;

        echo "<pre>";
        echo 'invalid project = '; print_r( $t_template_category['invalid_project'] );
        echo 'invalid category = '; print_r( $t_template_category['invalid_category'] );
        echo 'invalid user = '; print_r( $t_template_category['invalid_user'] );
        echo "</pre>";

        if( ( true == $t_template_category['invalid_project'] ) ||
            ( true == $t_template_category['invalid_category'] ) ||
            ( true == $t_template_category['invalid_user'] ) ) {
            if( !in_array( $t_template_category['summary'], $t_invalid_template_categories ) ) {
                $t_invalid_template_categories[] = array( 'id' => $t_template_category['template_id'], 'summary' => $t_template_category['summary'] );
            }

            if( true == $t_template_category['invalid_project'] ) {
                $t_status_code |= STATUS_CODE_INVALID_PROJECT;
            }

            if( true == $t_template_category['invalid_category'] ) {
                $t_status_code |= STATUS_CODE_INVALID_CATEGORY;
            }

            if( true == $t_template_category['invalid_user'] ) {
                $t_status_code |= STATUS_CODE_INVALID_USER;
            }
        } else {

            # for the current template, fill in the BugData structure
            $t_bug_data = new BugData;
            $t_bug_data->m_id                   = 0;
            $t_bug_data->project_id             = $t_template_category['project_id'];
            $t_bug_data->category_id            = $t_template_category['category_id'];
            $t_bug_data->profile_id             = 0;
            $t_bug_data->handler_id             = 0;
            $t_bug_data->view_state             = config_get( 'default_bug_view_status' );
            $t_bug_data->reproducibility        = config_get( 'default_bug_reproducibility' );
            $t_bug_data->severity               = config_get( 'default_bug_severity' );
            $t_bug_data->priority               = config_get( 'default_bug_priority' );
            $t_bug_data->projection             = config_get( 'default_bug_projection' );
            $t_bug_data->eta                    = config_get( 'default_bug_eta' );
            $t_bug_data->resolution             = config_get( 'default_bug_resolution' );
            $t_bug_data->status                 = config_get( 'bug_submit_status' );
            $t_bug_data->steps_to_reproduce     = config_get( 'default_bug_steps_to_reproduce' );
            $t_bug_data->additional_information = config_get ( 'default_bug_additional_info' );
            $t_bug_data->summary                = $t_template_category['summary'];
            $t_bug_data->description            = $t_template_category['description'];
            $t_bug_data->reporter_id            = $t_auto_reporter_user_id;

            # Allow plugins to pre-process bug data
            $t_bug_data = event_signal( 'EVENT_REPORT_BUG_DATA', $t_bug_data );

            # create the bug and send the appropriate email notifications
            $t_bug_id = $t_bug_data->create();

            $t_tickets[] = $t_bug_id;

            # Allow plugins to post-process bug data with the new bug ID
            event_signal( 'EVENT_REPORT_BUG', array( $t_bug_data, $t_bug_id ) );

            email_new_bug( $t_bug_id );

            if( 0 != $t_template_category['user_id'] ) {
                # get the bug record (including any behind-the-scenes that Mantis may perform in between the time
                # the bug object was initialized above and the time it was actually saved to the database)
                $t_bug_data = bug_get( $t_bug_id );

                # this logic stolen from bug_assign.php
                $g_project_override = $t_bug_data->project_id;
                $t_handler_id = $t_template_category['user_id'];

                # check that new handler has rights to handle the issue
                access_ensure_bug_level( config_get( 'handle_bug_threshold' ), $t_bug_id, $t_handler_id );

                # Update handler and status
                $t_bug_data->handler_id = $t_handler_id;
                if( ( ON == config_get( 'auto_set_status_to_assigned' ) ) && ( NO_USER != $t_handler_id ) ) {
                    $t_bug_data->status = config_get( 'bug_assigned_status' );
                }

                # Plugin support
                $t_new_bug = event_signal( 'EVENT_UPDATE_BUG', $t_bug_data, $t_bug_id );
                if ( !is_null( $t_new_bug ) ) {
                    $t_bug_data = $t_new_bug;
                }

                # Update bug and send notifications
                $t_bug_data->update();
            }
        }

        echo "<pre>status code = "; print_r( $t_status_code ); echo "</pre>";

        # create bug history record
        bug_history_add( $t_bug_id, $f_frequency_id, $t_template_category['template_id'], $t_status_code );
    }

    if( 0 < count( $t_invalid_template_categories ) ) {
        $t_manage_template_edit_page = $g_path . plugin_page( 'manage_template_edit_page', true );
        $t_body = plugin_lang_get( 'email_invalid_templates' ) . PHP_EOL . PHP_EOL;
        foreach( $t_invalid_template_categories as $t_invalid_template_category ) {
            $t_body .= '<a href="' . $t_manage_template_edit_page . '&id=' . $t_invalid_template_category['id'] .
                        '">' . $t_invalid_template_category['summary'] . '</a>' . PHP_EOL;
        }

        mst_email_send( plugin_lang_get( 'email_subject_auto_report_error' ), $t_body );
    }

    if( plugin_config_get( 'send_email_on_successful_auto_report' ) ) {
        if( 0 < count( $t_tickets ) ) {
            $t_body = plugin_lang_get( 'email_tickets_successfully_created' ) . PHP_EOL . PHP_EOL;
            foreach( $t_tickets as $t_ticket ) {
                $t_body .= string_get_bug_view_link( $t_ticket, null, true, true ) . PHP_EOL;
            }

            mst_email_send( plugin_lang_get( 'email_subject_auto_report_success' ), $t_body );
        }
    }
