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

/**
 * Add a bug history record
 *
 * @param int $p_bug_id Bug idate
 * @param int $p_frequency_id Frequency id
 * @param int $p_template_id Template id
 * @param int $p_project_id Project id
 * @param int $p_category_id Category id
 * @param int $p_user_id User id
 * @param int $p_template_category_id Template category id
 * @param int $p_status_code Status code
 * @param int $p_command_output_note_id Note id corresponding to the command output note
 * @param int $p_diff_output_note_id Note id corresponding to the diff output note
 * @return void
 */
function mst_bug_history_add( $p_bug_id,
    $p_frequency_id,
    $p_template_id,
    $p_project_id,
    $p_category_id,
    $p_user_id,
    $p_template_category_id,
    $p_status_code,
    $p_command_output_note_id,
    $p_diff_output_note_id ) {

    $t_bug_history_table = plugin_table( 'bug_history' );

    $c_bug_id = db_prepare_int( $p_bug_id );
    $c_frequency_id = db_prepare_int( $p_frequency_id );
    $c_template_id = db_prepare_int( $p_template_id );
    $c_project_id = db_prepare_int( $p_project_id );
    $c_category_id = db_prepare_int( $p_category_id );
    $c_user_id = db_prepare_int( $p_user_id );
    $c_template_category_id = db_prepare_int( $p_template_category_id );
    $c_status_code = db_prepare_int( $p_status_code );
    $c_command_output_note_id = db_prepare_int( $p_command_output_note_id );
    $c_diff_output_note_id = db_prepare_int( $p_diff_output_note_id );

    $query = "INSERT INTO $t_bug_history_table
            (
                date_submitted,
                bug_id,
                frequency_id,
                template_id,
                project_id,
                category_id,
                user_id,
                template_category_id,
                status_code,
                command_output_note_id,
                diff_output_note_id
            )
            VALUES
            (" .
                db_param() . ", " .
                db_param() . ", " .
                db_param() . ", " .
                db_param() . ", " .
                db_param() . ", " .
                db_param() . ", " .
                db_param() . ", " .
                db_param() . ", " .
                db_param() . ", " .
                db_param() . ", " .
                db_param() .
            ");";
    db_query_bound(
        $query,
        array(
            db_now(),
            $c_bug_id,
            $c_frequency_id,
            $c_template_id,
            $c_project_id,
            $c_category_id,
            $c_user_id,
            $c_template_category_id,
            $c_status_code,
            $c_command_output_note_id,
            $c_diff_output_note_id
        )
    );
}

/**
 * Process output file
 *
 * Process the specified output file and take the appropriate actions
 *
 * @param int $p_bug_id Bug id
 * @param string $p_output_file Output file
 * @param bool $p_diff_flag Diff flag
 * @param bool $p_previous_note_id The id of the note containing the previous execution's command output
 * @return mixed Array of values to return (skip_assign, command_output_note_id etc)
 */
function mst_bug_process_output_file( $p_bug_id, $p_output_file, $p_diff_flag = false, $p_previous_note_id = null ) {
    $t_output_file_contents = @file_get_contents( $p_output_file );

    if( '' == trim( $t_output_file_contents ) ) {
        return null;
    }

    libxml_use_internal_errors( true );
    $t_output_file_xml = simplexml_load_string( $t_output_file_contents );

    if( false == $t_output_file_xml ) {
        foreach( libxml_get_errors() as $t_error ) {
            $t_errors[] = trim( $t_error->message );
        }
    } else {
        list( $t_return, $t_errors, $t_diff_output_found ) = mst_bug_process_actions(
            $p_bug_id,
            $p_previous_note_id,
            $t_output_file_xml
        );
    }

    if( $p_diff_flag && ( false == $t_diff_output_found ) ) {
        if( $p_previous_note_id ) {
            bugnote_add(
                $p_bug_id,
                sprintf( plugin_lang_get( 'output_file_diffs_no_diff_in_output_file' ),
                $p_previous_note_id )
            );
        } else {
            bugnote_add( $p_bug_id, plugin_lang_get( 'output_file_diffs_no_previous_note' ) );
        }
    }

    # record errors as a separate note
    if( $t_errors ) {
        bugnote_add(
            $p_bug_id,
            plugin_lang_get( 'output_file_invalid_actions' ) .
            PHP_EOL . PHP_EOL . '- ' .
            join( PHP_EOL . '- ', $t_errors )
        );
    }

    return $t_return;
}

/**
 * Process XML output file
 *
 * @param int $p_bug_id Bug id
 * @param int $p_previous_note_id Previous command output note id
 * @param string $p_output_file_xml XML output file
 * @return mixed Array containing processing results, errors and a flag that indicates whether a diff was found
 */
function mst_bug_process_actions( $p_bug_id, $p_previous_note_id, $p_output_file_xml ) {
    $t_return = null;
    $t_errors = null;
    $t_diff_output_found = false;

    foreach( $p_output_file_xml->children() as $t_action_node => $t_action_node_value ) {
        if( 'actions' == $t_action_node ) {
            foreach( $t_action_node_value->children() as $t_action_name => $t_value ) {
                switch( strtolower( $t_action_name ) ) {
                    case 'assign_to':
                        $t_return['skip_assign'] = mst_bug_process_assign_to( $p_bug_id, (string)$t_value );

                        if( false == $t_return['skip_assign'] ) {
                            $t_errors[] = sprintf( plugin_lang_get( 'output_file_invalid_user' ), $t_action_name, (string)$t_value );
                        }

                        break;
                    case 'command_output':
                        $t_return['command_output_note_id'] = bugnote_add( $p_bug_id, (string)$t_value );
                        break;
                    case 'diff_output':
                        $t_return['diff_output_note_id'] = mst_bug_process_diff_output( $p_bug_id, $p_previous_note_id, (string)$t_value );
                        $t_diff_output_found = true;
                        break;
                    case 'add_monitor':
                        if( false == mst_bug_process_add_monitor( $p_bug_id, (string)$t_value ) ) {
                            $t_errors[] = sprintf( plugin_lang_get( 'output_file_invalid_user' ), $t_action_name, (string)$t_value );
                        }
                        break;
                    case 'note':
                        bugnote_add( $p_bug_id, (string)$t_value );
                        break;
                    case 'skip_assign':
                        $t_return['skip_assign'] = true;
                        break;
                    case 'status':
                        if( false === mst_bug_change_status( $p_bug_id, (string)$t_value ) ) {
                            $t_errors[] = sprintf( plugin_lang_get( 'output_file_invalid_status' ), $t_action_name, (string)$t_value );
                        }
                        break;
                    default:
                        $t_errors[] = sprintf( plugin_lang_get( 'output_file_invalid_action' ), $t_action_name );
                        break;
                }
            }
        } else {
            $t_errors[] = sprintf( plugin_lang_get( 'output_file_unknown_node' ), $t_action_node );
        }
    }

    return array( $t_return, $t_errors, $t_diff_output_found );
}

/**
 * Process 'assign_to' output file action
 *
 * @param int $p_bug_id Bug id
 * @param string $p_action_name Action name, EXACTLY as specified in the file
 * @param string $p_username Username specified in the output file
 * @return bool True if $p_username is a valid username, false otherwise
 */
function mst_bug_process_assign_to( $p_bug_id, $p_username ) {
    $t_user_id = user_get_id_by_name( $p_username );

    if( false === $t_user_id ) {
        return false;
    }

    bug_assign( $p_bug_id, $t_user_id );

    return true;
}

/**
 * Process 'diff_output' output file action
 *
 * @param int $p_bug_id Bug id
 * @param int $p_previous_note_id Previous commmand output note id
 * @param string $p_diff_output The contents of the 'diff_output' XML node in the output file
 * @return int Diff output note id if one was recorded, NULL otherwise
 */
function mst_bug_process_diff_output( $p_bug_id, $p_previous_note_id, $p_diff_output ) {
    if( $p_previous_note_id ) {
        if( '' != $p_diff_output ) {
            return bugnote_add( $p_bug_id,
                sprintf( plugin_lang_get( 'output_file_diffs_previous_note' ),
                $p_previous_note_id,
                $p_diff_output )
            );
        } else {
            bugnote_add(
                $p_bug_id,
                sprintf( plugin_lang_get( 'output_file_diffs_no_diffs' ),
                $p_previous_note_id )
            );
        }
    } else {
        bugnote_add( $p_bug_id, plugin_lang_get( 'output_file_diffs_no_previous_note' ) );
    }

    return null;
}

/**
 * Process 'add_monitor' output file action
 *
 * @param int $p_bug_id Bug id
 * @param string $p_username Username specified in the output file
 * @return bool True if $p_username is a valid username, false otherwise
 */
function mst_bug_process_add_monitor( $p_bug_id, $p_username ) {
    $t_user_id = user_get_id_by_name( $p_username );

    if( false === $t_user_id ) {
        return false;
    } else {
        bug_monitor( $p_bug_id, $t_user_id );
    }

    return true;
}

/**
 * Update bug status
 *
 * Change bug status to the given status
 *
 * @param int $p_bug_id Bug idate
 * @param string $p_status New status
 * @return bool True if $p_status is valid, false otherwise
 */
function mst_bug_change_status( $p_bug_id, $p_status ) {
    $t_status = config_get( "bug_{$p_status}_status_threshold", -1 );

    if( -1 == $t_status ) {
        return false;
    }

    $t_bug_data = bug_get( $p_bug_id );
    $t_bug_data->status = $t_status;
    $t_bug_data->update();

    return true;
}
