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

    global $g_path;

    $t_auto_reporter_username = plugin_config_get( 'auto_reporter_username' );

    $t_auto_reporter_user_id = user_get_id_by_name( $t_auto_reporter_username );

    if ( $_SERVER["REMOTE_ADDR"] != $_SERVER["SERVER_ADDR"] ) {
        plugin_error( plugin_lang_get( 'error_host_access_denied' ), ERROR );
        exit;
    }

    if ( false == auth_attempt_script_login( $t_auto_reporter_username ) ) {
        plugin_error( plugin_lang_get( 'error_user_access_denied' ), ERROR );
        exit;
    }

    $f_frequency_id = gpc_get_int( 'frequency_id' );

    $t_template_categories = template_category_get_all(
        array(
            'frequency_id' => db_prepare_int( $f_frequency_id ),
            'T.enabled' => 1,
            'F.enabled' => 1
        )
    );

    $t_tickets = array();
    $t_invalid_template_categories = array();

    $t_enable_commands = plugin_config_get( 'enable_commands' );

    if( is_array( $t_template_categories ) ) {
        foreach( $t_template_categories as $t_template_category ) {
            $t_bug_id = null;
            $t_status_code = MST_STATUS_CODE_OK;

            if( $t_template_category['invalid_project'] ||
                $t_template_category['invalid_category'] ||
                $t_template_category['invalid_user'] ) {
                if( false == in_array( $t_template_category['summary'], $t_invalid_template_categories ) ) {
                    $t_invalid_template_categories[] = array(
                        'id' => $t_template_category['template_id'],
                        'summary' => $t_template_category['summary']
                    );
                }

                if( $t_template_category['invalid_project'] ) {
                    $t_status_code |= MST_STATUS_CODE_INVALID_PROJECT;
                }

                if( $t_template_category['invalid_category'] ) {
                    $t_status_code |= MST_STATUS_CODE_INVALID_CATEGORY;
                }

                if( $t_template_category['invalid_user'] ) {
                    $t_status_code |= MST_STATUS_CODE_INVALID_USER;
                }
            } else {
                $t_skip_assign = false;
                $t_command_output_note_id = null;
                $t_diff_output_note_id = null;
                $t_command = '';
                $t_command_output = '';

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
                $t_bug_data->additional_information = sprintf(
                    plugin_lang_get( 'bug_template_category_id' ),
                    $t_template_category['template_category_id']
                );
                $t_bug_data->summary                = $t_template_category['summary'];
                $t_bug_data->description            = $t_template_category['description'];
                $t_bug_data->reporter_id            = $t_auto_reporter_user_id;
                $t_bug_data->due_date               = 1;

                # Allow plugins to pre-process bug data
                $t_bug_data = event_signal( 'EVENT_REPORT_BUG_DATA', $t_bug_data );

                # create the bug and send the appropriate email notifications
                $t_bug_id = $t_bug_data->create();

                $t_tickets[] = $t_bug_id;

                # Allow plugins to post-process bug data with the new bug ID
                event_signal( 'EVENT_REPORT_BUG', array( $t_bug_data, $t_bug_id ) );

                email_new_bug( $t_bug_id );

                # execute command specified at the template level
                if( $t_enable_commands ) {
                    if( '' != $t_template_category['command'] ) {
                        $t_diff_flag = $t_template_category['diff_flag'];

                        # assemble the command line
                        $t_command_arguments = command_arguments_format(
                            command_argument_get_all( $t_template_category['template_category_id'] ),
                            MST_ESCAPE_FOR_COMMAND_LINE
                        );

                        $t_command = './plugins/' . plugin_get_current() . '/scripts/' .
                            $t_template_category['command'] . ' ' .
                            $t_command_arguments;

                        if( $t_diff_flag ) {
                            # create temp files
                            $t_diff_left_side = trim( shell_exec( 'tempfile' ) );

                            # dump the previous note into the left side tempfile
                            $t_previous_note = template_category_get_previous_bugnote( $t_template_category['template_category_id'] );

                            # ensure that we have a "previous" note
                            if( $t_previous_note ) {
                                # save the previous note
                                file_put_contents( $t_diff_left_side, $t_previous_note['note_text'] );

                                # adjust the command line appropriately
                                $t_command .= " --diff-left-side=\"$t_diff_left_side\"";
                            }
                        }

                        $t_output_file = trim( shell_exec( 'tempfile' ) );
                        $t_command .= " --output-file=\"$t_output_file\" 2>&1";

                        bugnote_add( $t_bug_id, $t_command );

                        # execute command and record output
                        $t_command_output = shell_exec( $t_command );

                        if( '' != trim( $t_command_output ) ) {
                            bugnote_add( $t_bug_id, $t_command_output );
                        }

                        # process output file
                        $t_return = mst_bug_process_output_file(
                            $t_bug_id,
                            $t_output_file,
                            $t_diff_flag,
                            $t_previous_note['note_id']
                        );

                        # get values from the returned array
                        if( is_array( $t_return ) && isset( $t_return['skip_assign'] ) ) {
                            $t_skip_assign = $t_return['skip_assign'];
                        }

                        if( is_array( $t_return ) && isset( $t_return['command_output_note_id'] ) ) {
                            $t_command_output_note_id = $t_return['command_output_note_id'];
                        }

                        if( is_array( $t_return ) && isset( $t_return['diff_output_note_id'] ) ) {
                            $t_diff_output_note_id = $t_return['diff_output_note_id'];
                        }

                        # clean up
                        $t_command_output = shell_exec( "rm $t_output_file" );

                        if( $t_diff_flag ) {
                            shell_exec( "rm $t_diff_left_side" );
                        }
                    } else {
                        $t_command_output_note_id = null;
                    }

                    # assign the ticket, if so specified, and NOT overridden by the output file
                    if( ( 0 != $t_template_category['user_id'] ) && ( false == $t_skip_assign ) ) {
                        bug_assign( $t_bug_id, $t_template_category['user_id'] );
                    }
                } else {
                    # assign the ticket
                    if( ( 0 != $t_template_category['user_id'] ) ) {
                        bug_assign( $t_bug_id, $t_template_category['user_id'] );
                    }
                }
            }

            # create bug history record
            mst_bug_history_add(
                $t_bug_id,
                $f_frequency_id,
                $t_template_category['template_id'],
                $t_template_category['project_id'],
                $t_template_category['category_id'],
                $t_template_category['user_id'],
                $t_template_category['template_category_id'],
                $t_status_code,
                $t_command_output_note_id,
                $t_diff_output_note_id
            );
        }
    }

    if( 0 < count( $t_invalid_template_categories ) ) {
        $t_manage_template_edit_page = $g_path . plugin_page( 'manage_template_edit_page', true );
        $t_body = plugin_lang_get( 'email_invalid_templates' ) . PHP_EOL . PHP_EOL;

        foreach( $t_invalid_template_categories as $t_invalid_template_category ) {
            $t_body .= '<a href="' . $t_manage_template_edit_page . '&id=' . $t_invalid_template_category['id'] .
                        '">' . $t_invalid_template_category['summary'] . '</a>' . PHP_EOL;
        }

        mst_core_email_send( plugin_lang_get( 'email_subject_auto_report_error' ), $t_body );
    }

    if( plugin_config_get( 'send_email_on_success' ) ) {
        if( 0 < count( $t_tickets ) ) {
            $t_body = plugin_lang_get( 'email_tickets_successfully_created' ) . PHP_EOL . PHP_EOL;

            if( is_array( $t_tickets ) ) {
                foreach( $t_tickets as $t_ticket ) {
                    $t_body .= string_get_bug_view_link( $t_ticket, null, true, true ) . PHP_EOL;
                }
            }

            mst_core_email_send( plugin_lang_get( 'email_subject_auto_report_success' ), $t_body );
        }
    }
