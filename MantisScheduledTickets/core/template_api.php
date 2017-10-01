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

define( 'MST_TEMPLATE_ADDED', 1 );
define( 'MST_TEMPLATE_ENABLED', 2 );
define( 'MST_TEMPLATE_DISABLED', 3 );
define( 'MST_TEMPLATE_CHANGED', 4 );
define( 'MST_TEMPLATE_DELETED', 5 );

define( 'MST_TEMPLATE_CONFIG_CHANGED', 6 );

define( 'MST_TEMPLATE_CATEGORY_ADDED', 11 );
define( 'MST_TEMPLATE_CATEGORY_CHANGED', 12 );
define( 'MST_TEMPLATE_CATEGORY_DELETED', 13 );

/**
 * Template class
 */
class Template {
    /**
     * Template summary
     */
    protected $summary = null;

    /**
     * Template description
     */
    protected $description = null;

    /**
     * Flag which indicates whether template is enabled or not
     */
    protected $enabled = null;

    /**
     * Command to execute after creating tickets.
     *
     * The output of the command will be added as a note to the newly created ticket(s).
     */
    protected $command = null;

    /**
     * Flag that indicates whether to perform a diff operation.
     *
     * If this flag is set, an additional command line argument (--diff-left-side) will be passed to the specified
     * command. This represents a file that contains the previous execution's command output. The command can then
     * perform a diff against the previous execution and communicate that information back to the MantisScheduledTickets
     * plugin using the output file. Please see the documentation for more details.
     */
    protected $diff_flag = null;

    /**
     * Setter
     *
     * Set the value of a (protected) property
     *
     * @param string $name Property name
     * @param mixed $value New property value
     * @return void
     */
    public function __set( $name, $value ) {
        switch( $name ) {
            case 'enabled':
                $value = (bool)$value;
                break;
        }

        $this->$name = $value;
    }

    /**
     * Getter
     *
     * Get the value of a (protected) property
     *
     * @param string $name Parameter name
     * @return mixed Property value
     */
    public function __get( $name ) {
        return $this->$name;
    }
}

/**
 * Get all template records
 *
 * Get template records for the given filter. $p_filter is an associative array
 * that contains key/value pairs (<field_name> => <value_to_filter_on>)
 *
 * NOTE: When calling this function, the values provided via $p_filter
 * MUST have already been cleansed (i.e. the value in the array should
 * be the result of the appropriate db_prepare_* function).
 *
 * @param mixed $p_filter (Optional) Filter array.
 * @return mixed Array of template records
 */
function template_get_all( $p_filter = null ) {
    $t_template_table = plugin_table( 'template' );
    $t_template_category_table = plugin_table( 'template_category' );
    $t_bug_history_table = plugin_table( 'bug_history' );
    $t_category_table = db_get_table( 'mantis_category_table' );
    $t_user_table = db_get_table( 'mantis_user_table' );

    $t_where_clause = array();
    $t_params = array();

    $query = "SELECT
                T.id,
                T.summary,
                T.description,
                T.enabled,
                T.command,
                T.diff_flag,
                (
                    SELECT
                        COUNT(*)
                    FROM $t_template_category_table AS TC
                    WHERE TC.template_id = T.id
                ) AS category_count,
                (
                    SELECT
                        COUNT(*)
                    FROM $t_template_category_table AS TC1
                    LEFT JOIN $t_category_table AS C1
                        ON C1.id = TC1.category_id
                    WHERE TC1.template_id = T.id
                        AND C1.id IS NULL
                ) AS deleted_category_count,
                (
                    SELECT
                        COUNT(*)
                    FROM $t_template_category_table AS TC2
                    LEFT JOIN $t_user_table AS U2
                        ON U2.id = TC2.user_id
                    WHERE TC2.template_id = T.id
                        AND TC2.user_id != 0
                        AND U2.id IS NULL
                ) AS deleted_user_count,
                (
                    SELECT
                        COUNT(*)
                    FROM $t_bug_history_table AS BH
                    WHERE BH.template_id = T.id
                ) AS bug_count
            FROM $t_template_table AS T";

    # add WHERE clause(s) corresponding to the given filters, if any
    if( is_array( $p_filter ) ) {
        foreach( $p_filter as $t_column => $t_values ) {
            $t_where_clause[] = "$t_column = " . db_param();
            $t_params[] = $t_values;
        }

        $query .= ' WHERE ' . implode( ' AND ', $t_where_clause );
    }

    $query .= " ORDER BY T.summary;";

    # run the query
    $result = db_query_bound( $query, $t_params );

    $t_row_count = db_num_rows( $result );
    $t_templates = array();

    for( $i = 0; $i < $t_row_count; $i++ ) {
        array_push( $t_templates, db_fetch_array( $result ) );
    }

    return $t_templates;
}

/**
 * Get a single template record by the specified id
 *
 * @param int $p_template_id Template record id
 * @return mixed Associative array containing a single template record
 */
function template_get_row( $p_template_id ) {
    $t_filter = array( 'id' => db_prepare_int( $p_template_id ) );
    $t_templates = template_get_all( $t_filter );

    if( false == is_array( $t_templates[0] ) ) {
        error_parameters( plugin_lang_get( 'error_template_not_found' ), plugin_lang_get( 'title' ) );
        trigger_error( ERROR_PLUGIN_GENERIC, ERROR );
    }

    return $t_templates[0];
}

/**
 * Check whether the given template summary is unique
 *
 * @param string $p_summary Template summary
 * @param int $p_template_id Template record id
 * @return bool True if template summary is unique, false otherwise
 */
function template_summary_is_unique( $p_summary, $p_template_id = null ) {
    $t_template_table = plugin_table( 'template' );
    $c_template_id = db_prepare_int( $p_template_id );

    $query = "SELECT
                COUNT(*)
            FROM $t_template_table AS T
            WHERE T.summary = " . db_param();

    if( $p_template_id ) {
        $query .= " AND T.id <> " . db_param();
    }
    $result = db_query_bound(
        $query,
        $p_template_id ?
            array( $p_summary, $c_template_id ) :
            array( $p_summary )
    );

    if( 0 < db_result( $result ) ) {
        return false;
    }

    return true;
}

/**
 * Ensure that the given template is unique
 *
 * @param string $p_summary Template summary
 * @param int $p_template_id Template record id
 * @return void
 */
function template_ensure_unique( $p_summary, $p_template_id = null ) {
    if( false == template_summary_is_unique( $p_summary, $p_template_id ) ) {
        error_parameters( plugin_lang_get( 'error_template_summary_not_unique' ), plugin_lang_get( 'title' ) );
        trigger_error( ERROR_PLUGIN_GENERIC, ERROR );
    }
}

/**
 * Create template record
 *
 * @param string $p_summary Template summary
 * @param string $p_description Template description
 * @param bool $p_enabled Flag which indicates whether template is enabled or not
 * @param string $p_command Command to execute after creating tickets
 * @param bool $p_diff_flag Flag which indicates whether to perform a diff operation
 * @return int Template record id
 */
function template_add( $p_summary, $p_description, $p_enabled, $p_command, $p_diff_flag ) {
    if( '' == $p_summary ) {
        error_parameters( plugin_lang_get( 'template_summary' ) );
        trigger_error( ERROR_EMPTY_FIELD, ERROR );
    }

    if( '' == $p_description ) {
        error_parameters( plugin_lang_get( 'template_description' ) );
        trigger_error( ERROR_EMPTY_FIELD, ERROR );
    }

    template_ensure_unique( $p_summary );

    $t_template_table = plugin_table( 'template' );
    $c_enabled = db_prepare_bool( $p_enabled );
    $c_diff_flag = db_prepare_bool( $p_diff_flag );

    $query = "INSERT INTO $t_template_table
                (summary, description, enabled, command, diff_flag)
            VALUES
                (" . db_param() . ", " . db_param() . ", " . db_param() . ", " . db_param() . ", " . db_param() . ");";
    db_query_bound( $query, array( $p_summary, $p_description, $c_enabled, $p_command, $c_diff_flag ) );

    return db_insert_id( $t_template_table );
}

/**
 * Update template record
 *
 * @param int $p_template_id Template record id
 * @param string $p_summary Template summary
 * @param string $p_description Template description
 * @param bool $p_enabled Flag which indicates whether template is enabled or not
 * @param string $p_command Command to execute after creating tickets
 * @param bool $p_diff_flag Flag which indicates whether to perform a diff operation
 * @return void
 */
function template_update( $p_template_id, $p_summary, $p_description, $p_enabled, $p_command, $p_diff_flag ) {
    if( '' == $p_summary ) {
        error_parameters( plugin_lang_get( 'template_summary' ) );
        trigger_error( ERROR_EMPTY_FIELD, ERROR );
    }

    if( '' == $p_description ) {
        error_parameters( plugin_lang_get( 'template_description' ) );
        trigger_error( ERROR_EMPTY_FIELD, ERROR );
    }

    template_ensure_unique( $p_summary, $p_template_id );

    $t_template_table = plugin_table( 'template' );

    $c_template_id = db_prepare_int( $p_template_id );
    $c_enabled = db_prepare_bool( $p_enabled );
    $c_diff_flag = db_prepare_bool( $p_diff_flag );

    $query = "UPDATE $t_template_table
            SET
                summary = " . db_param() . ",
                description = " . db_param() . ",
                enabled = " . db_param() . ",
                command = " . db_param() . ",
                diff_flag = " . db_param() . "
            WHERE id = " . db_param() . ";";
    db_query_bound( $query, array( $p_summary, $p_description, $c_enabled, $p_command, $c_diff_flag, $c_template_id ) );
}

/**
 * Delete template record
 *
 * @param int $p_template_id Template record id
 * @return void
 */
function template_delete( $p_template_id ) {
    $t_command_argument_table = plugin_table( 'command_argument' );
    $t_template_category_table = plugin_table( 'template_category' );
    $t_template_table = plugin_table( 'template' );

    $c_template_id = db_prepare_int( $p_template_id );

    $query = "DELETE FROM $t_command_argument_table
            WHERE template_category_id IN
                (
                    SELECT
                        TC.id
                    FROM $t_template_category_table AS TC
                    WHERE TC.template_id = " . db_param() . "
                );";
    db_query_bound( $query, array( $c_template_id ) );

    $query = "DELETE FROM $t_template_category_table WHERE template_id = " . db_param();
    db_query_bound( $query, array( $c_template_id ) );

    $query = "DELETE FROM $t_template_table WHERE id = " . db_param();
    db_query_bound( $query, array( $c_template_id ) );

    return true;
}

/**
 * Delete (blank out) all the command arguments for the given template
 *
 * @param int $p_template_id Template record id
 * @return void
 */
function template_delete_all_command_arguments( $p_template_id ) {
    $t_template_categories = template_category_get_all( array( 'template_id' => db_prepare_int( $p_template_id ) ) );

    if( is_array( $t_template_categories ) ) {
        foreach( $t_template_categories as $t_template_category ) {
            $t_command_arguments = command_arguments_format(
                command_argument_get_all( $t_template_category['template_category_id'] ),
                MST_ESCAPE_FOR_COMMAND_LINE
            );

            if( '' != $t_command_arguments ) {
                command_argument_delete(
                    array( 'template_category_id' => db_prepare_int( $t_template_category['template_category_id'] ) )
                );
                template_category_log_event(
                    $t_template_category['template_id'],
                    $t_template_category['template_category_id'],
                    'template_command_arguments',
                    $t_command_arguments,
                    ''
                );
            }
        }
    }
}

/**
 * Log template event (add, delete)
 *
 * @param int $p_template_id Template record id
 * @param int $p_event_type Event type
 * @param string $p_new_value Optionally, new value to record
 * @return void
 */
function template_log_event_special( $p_template_id, $p_event_type, $p_new_value = null ) {
    $t_template_history_table = plugin_table( 'template_history' );
    $t_user_id = auth_get_current_user_id();

    $c_template_id = db_prepare_int( $p_template_id );
    $c_event_type = db_prepare_int( $p_event_type );

    $query = "INSERT $t_template_history_table
            (
                user_id,
                template_id,
                new_value,
                date_modified,
                type
            )
            VALUES
            (" .
                db_param() . ', ' .
                db_param() . ', ' .
                db_param() . ', ' .
                db_param() . ', ' .
                db_param() .
            ');';
    db_query_bound( $query, array( $t_user_id, $c_template_id, $p_new_value, db_now(), $c_event_type ) );
}

/**
 * Log template change event
 *
 * Record the actual changes made (old/new values)
 *
 * @param int $p_template_id Template record id
 * @param string $p_field_name Field name
 * @param string $p_old_value Old field value
 * @param string $p_new_value New field value
 * @return void
 */
function template_log_event( $p_template_id, $p_field_name , $p_old_value, $p_new_value ) {
    $t_template_history_table = plugin_table( 'template_history' );
    $t_user_id = auth_get_current_user_id();
    $c_template_id = db_prepare_int( $p_template_id );

    $query = "INSERT $t_template_history_table
            (
                user_id,
                template_id,
                date_modified,
                type,
                field_name,
                old_value,
                new_value
            )
            VALUES
            (" .
                db_param() . ', ' .
                db_param() . ', ' .
                db_param() . ', ' .
                db_param() . ', ' .
                db_param() . ', ' .
                db_param() . ', ' .
                db_param() .
            ');';
    db_query_bound(
        $query,
        array( $t_user_id, $c_template_id, db_now(), MST_TEMPLATE_CHANGED, $p_field_name , $p_old_value, $p_new_value )
    );
}

/**
 * Determine differences between two given template records and log them
 *
 * @param int $p_template_id Template record id
 * @param mixed $p_old_record Template object representing the old record
 * @param mixed $p_new_record Template object representing the new record
 * @return void
 */
function template_log_changes( $p_template_id, $p_old_record, $p_new_record ) {
    if( $p_old_record->summary != $p_new_record->summary ) {
        template_log_event( $p_template_id, 'summary', $p_old_record->summary, $p_new_record->summary );
    }

    if( $p_old_record->description != $p_new_record->description ) {
        template_log_event( $p_template_id, 'description', $p_old_record->description, $p_new_record->description );
    }

    if( $p_old_record->enabled != $p_new_record->enabled ) {
        template_log_event_special( $p_template_id, $p_new_record->enabled ? MST_TEMPLATE_ENABLED : MST_TEMPLATE_DISABLED );
    }

    if( $p_old_record->command != $p_new_record->command ) {
        template_log_event( $p_template_id, 'command', $p_old_record->command, $p_new_record->command );
    }

    if( $p_old_record->diff_flag != $p_new_record->diff_flag ) {
        template_log_event( $p_template_id, 'diff_flag', $p_old_record->diff_flag, $p_new_record->diff_flag );
    }
}

/**
 * Get template history
 *
 * @param int $p_template_id Template record id
 * @return mixed Array of template history records
 */
function template_get_history( $p_template_id ) {
    $t_template_history_table = plugin_table( 'template_history' );
    $t_template_category_history_table = plugin_table( 'tmplt_cat_hist' );

    $c_template_id = db_prepare_int( $p_template_id );

    $query = "SELECT
                TH.date_modified,
                TH.user_id,
                TH.type,
                TH.field_name,
                TH.old_value,
                TH.new_value,
                NULL AS template_category_id
            FROM $t_template_history_table AS TH
            WHERE TH.template_id = " . db_param() . "

            UNION ALL

            SELECT
                TCH.date_modified,
                TCH.user_id,
                TCH.type,
                TCH.field_name,
                TCH.old_value,
                TCH.new_value,
                TCH.template_category_id
            FROM $t_template_category_history_table AS TCH
            WHERE TCH.template_id = " . db_param() . "
            ORDER BY
                date_modified,
                type;";
    $result = db_query_bound( $query, array( $c_template_id, $c_template_id ) );

    $t_row_count = db_num_rows( $result );
    $t_history = array();

    for( $i = 0; $i < $t_row_count; $i++ ) {
        array_push( $t_history, db_fetch_array( $result ) );
    }

    return $t_history;
}
