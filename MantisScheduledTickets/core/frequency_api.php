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

define( 'MST_FREQUENCY_ADDED', 1 );
define( 'MST_FREQUENCY_ENABLED', 2 );
define( 'MST_FREQUENCY_DISABLED', 3 );
define( 'MST_FREQUENCY_CHANGED', 4 );
define( 'MST_FREQUENCY_DELETED', 5 );

define( 'MST_MAX_DAY_OF_WEEK', 6 );
define( 'MST_MAX_MONTH', 12 );
define( 'MST_MAX_DAY_OF_MONTH', 31 );
define( 'MST_MAX_HOUR', 23 );
define( 'MST_MAX_MINUTE', 59 );

define( 'MST_CRONTAB_STATUS_OK', 0 );
define( 'MST_CRONTAB_STATUS_DISABLED', 1 );
define( 'MST_CRONTAB_STATUS_NOT_OK', 2 );

/**
 * Frequency class
 */
class Frequency {
    /**
     * Frequency name
     */
    protected $name = null;

    /**
     * Flag which indicates whether template is enabled or not
     */
    protected $enabled = null;

    /**
     * Minute component
     */
    protected $minute = null;

    /**
     * Hour component
     */
    protected $hour = null;

    /**
     * Day of month component
     */
    protected $day_of_month = null;

    /**
     * Month component
     */
    protected $month = null;

    /**
     * Day of week component
     */
    protected $day_of_week = null;

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
     * @param string $name Property name
     * @return mixed Property value
     */
    public function __get( $name ) {
        return $this->$name;
    }
}

/**
 * Get all frequency records
 *
 * Get an array of all frequency records that match the given filter
 *
 * NOTE: When calling this function, the values provided via $p_filter
 * MUST have already been cleansed (i.e. the value in the array should
 * be the result of the appropriate db_prepare_* function).
 *
 * @param mixed $p_filter Filter array
 * @return mixed Array of frequency records
 */
function frequency_get_all( $p_filter = null ) {
    $t_frequency_table = plugin_table( 'frequency' );
    $t_template_category_table = plugin_table( 'template_category' );
    $t_bug_history_table = plugin_table( 'bug_history' );

    $t_where = array();
    $t_params = array();

    $query = "SELECT
                F.id,
                F.name,
                F.enabled,
                F.minute,
                F.hour,
                F.day_of_month,
                F.month,
                F.day_of_week,
                (
                  SELECT
                    COUNT(*)
                  FROM $t_template_category_table AS TP
                  WHERE TP.frequency_id = F.id
                ) AS template_count,
                (
                  SELECT
                    COUNT(*)
                  FROM $t_bug_history_table AS BH
                  WHERE BH.frequency_id = F.id
                ) AS bug_count
            FROM $t_frequency_table AS F";

    # add WHERE clause(s) corresponding to the given filters, if any
    if( is_array( $p_filter ) ) {
        foreach( $p_filter as $t_column => $t_values ) {
            $t_where[] = "$t_column = " . db_param();
            $t_params[] = $t_values;
        }

        $query .= ' WHERE ' . implode( ' AND ', $t_where );
    }

    $query .= " ORDER BY F.name;";

    # run the query
    $result = db_query_bound( $query, $t_params );

    $t_row_count = db_num_rows( $result );
    $t_frequencies = array();

    for( $i = 0; $i < $t_row_count; $i++ ) {
        array_push( $t_frequencies, db_fetch_array( $result ) );
    }

    return $t_frequencies;
}

/**
 * Get active frequency records
 *
 * @return mixed Array of active frequency records
 */
function frequency_get_all_active() {
    $t_filter = array( 'enabled' => 1 );
    return frequency_get_all( $t_filter );
}

/**
 * Get enabled frequency records for crontab file operations
 *
 * Get enabled frequency records in a format that makes crontab file operations
 * (validation / regeneration) a little easier
 *
 * @return mixed Array of frequency records
 */
function frequency_get_for_crontab() {
    $t_frequencies = frequency_get_all_active();
    $t_crontab_frequencies = array();

    if( is_array( $t_frequencies ) ) {
        foreach( $t_frequencies as $t_frequency ) {
            array_push( $t_crontab_frequencies,
                array(
                    'id' => $t_frequency['id'],
                    'schedule' => $t_frequency['minute'] . ' '.
                        $t_frequency['hour'] . ' ' .
                        $t_frequency['day_of_month'] . ' ' .
                        $t_frequency['month'] . ' ' .
                        $t_frequency['day_of_week'],
                    'matched' => 0
                )
            );
        }
    }

    return $t_crontab_frequencies;
}

/**
 * Get a single frequency record by the specified id
 *
 * @param int $p_frequency_id Frequency id
 * @return array Associative array representing a frequency record
 */
function frequency_get_row( $p_frequency_id ) {
    $t_filter = array( 'id' => db_prepare_int( $p_frequency_id ) );
    $t_frequencies = frequency_get_all( $t_filter );

    if( false == is_array( $t_frequencies[0] ) ) {
        plugin_error( plugin_lang_get( 'error_frequency_not_found' ), ERROR );
    }

    return $t_frequencies[0];
}

/**
 * Get the name of the specified frequency record
 *
 * @param int $p_frequency_id Frequency id
 * @return string Frequency name
 */
function frequency_get_name( $p_frequency_id ) {
    $t_frequency = frequency_get_row( $p_frequency_id );

    return $t_frequency['name'];
}

/**
 * Check whether the given frequency name is unique
 *
 * @param string $p_name Frequency name
 * @param int $p_frequency_id Frequency id to exclude
 * @return bool True if the given frequency name is unique, false otherwise
 */
function frequency_name_is_unique( $p_name, $p_frequency_id = null ) {
    $t_frequency_table = plugin_table( 'frequency' );
    $c_frequency_id = db_prepare_int( $p_frequency_id );

    $query = "SELECT
                COUNT(*)
            FROM $t_frequency_table AS F
            WHERE F.name = " . db_param();

    if( $p_frequency_id ) {
        $query .= " AND F.id <> " . db_param();
    }

    $result = db_query_bound(
        $query,
        $p_frequency_id ?
            array( $p_name, $c_frequency_id ) :
            array( $p_name )
    );

    if( 0 < db_result( $result ) ) {
        return false;
    }

    return true;
}

/**
 * Check whether the given frequency is unique
 *
 * @param string $p_minute Minute component
 * @param string $p_hour Hour component
 * @param string $p_day_of_month Day of month component
 * @param string $p_month Month component
 * @param string $p_day_of_week Day of week component
 * @param int $p_frequency_id Frequency id
 * @return bool True if the given frequency (the collection of individual components) is unique, false otherwise
 */
function frequency_is_unique( $p_minute, $p_hour, $p_day_of_month, $p_month, $p_day_of_week, $p_frequency_id = null ) {
    $t_frequency_table = plugin_table( 'frequency' );
    $c_frequency_id = db_prepare_int( $p_frequency_id );

    $query = "SELECT COUNT(*)
            FROM $t_frequency_table AS F
            WHERE F.minute = " . db_param() . "
              AND F.hour = " . db_param() . "
              AND F.day_of_month = " . db_param() . "
              AND F.month = " . db_param() . "
              AND F.day_of_week = " . db_param();

    if( $p_frequency_id ) {
        $query .= " AND F.id <> " . db_param();
    }

    $result = db_query_bound(
        $query,
        $p_frequency_id ?
            array( $p_minute, $p_hour, $p_day_of_month, $p_month, $p_day_of_week, $c_frequency_id ) :
            array( $p_minute, $p_hour, $p_day_of_month, $p_month, $p_day_of_week )
    );

    if( 0 < db_result( $result ) ) {
        return false;
    }

    return true;
}

/**
 * Ensure that the given frequency is unique
 *
 * @todo plugin error reporting appears to be broken in Mantis 1.2.19; revisit in the future
 *
 * @param string $p_name Frequency name
 * @param string $p_minute Minute component
 * @param string $p_hour Hour component
 * @param string $p_day_of_month Day of month component
 * @param string $p_month Month component
 * @param string $p_day_of_week Day of week component
 * @param int $p_frequency_id Frequency id
 * @return void
 */
function frequency_ensure_unique( $p_name, $p_minute, $p_hour, $p_day_of_month, $p_month, $p_day_of_week, $p_frequency_id = null ) {
    if( false == frequency_name_is_unique( $p_name, $p_frequency_id ) ) {
        /* @todo
        error_parameters( plugin_lang_get( 'error_frequency_name_not_unique' ) );
        trigger_error( ERROR_PLUGIN_GENERIC, ERROR );
        */

        plugin_error( plugin_lang_get( 'error_frequency_name_not_unique' ), ERROR );
    }

    if( false == frequency_is_unique( $p_minute, $p_hour, $p_day_of_month, $p_month, $p_day_of_week, $p_frequency_id ) ) {
        /* @todo
        error_parameters( plugin_lang_get( 'error_frequency_not_unique' ) );
        trigger_error( ERROR_PLUGIN_GENERIC, ERROR );
        */

        plugin_error( plugin_lang_get( 'error_frequency_not_unique' ), ERROR );
    }
}

/**
 * Ensure that the given value(s) are valid for a given crontab field
 *
 * $p_value should be a comma-separated list of integers within the range specified by $p_min_value and $p_max_value.
 * The error corresponding to $p_error_string is thrown if validation fails.
 *
 * @param mixed $p_value Comma-separated list of values to check
 * @param int $p_min_value Lower end of valid range
 * @param int $p_max_value Upper end of valid range
 * @param string $p_error_string Error to throw if/when validation fails.
 */
function frequency_ensure_valid_field_values( $p_value, $p_min_value, $p_max_value, $p_error_string ) {
    # check the trivial cases first
    if( '*' == $p_value ) {
        return;
    }

    if( '' == trim( $p_value ) ) {
        plugin_error( plugin_lang_get( $p_error_string ), ERROR );
    }

    # is there anything in the given string that's NOT a space, a digit or a comma?
    preg_match( '/[^\s\d,]/', $p_value, $t_matches );

    if( $t_matches ) {
        plugin_error( plugin_lang_get( $p_error_string ), ERROR );
    }

    # validate that each value is within the given range
    $t_values = explode( ',', $p_value );

    foreach( $t_values as $t_value ) {
        if( ( $p_min_value > (int)trim( $t_value ) ) || ( $p_max_value < (int)trim( $t_value ) ) ) {
            plugin_error( plugin_lang_get( $p_error_string ), ERROR );
        }
    }
}

/**
 * Create frequency record
 *
 * @param string $p_name Frequency name
 * @param bool $p_enabled Flag which indicates whether frequency is enabled or not
 * @param string $p_minute Minute component
 * @param string $p_hour Hour component
 * @param string $p_day_of_month Day of month component
 * @param string $p_month Month component
 * @param string $p_day_of_week Day of week component
 * @return int Frequency record id
 */
function frequency_add( $p_name, $p_enabled, $p_minute, $p_hour, $p_day_of_month, $p_month, $p_day_of_week ) {
    if( '' == $p_name ) {
        error_parameters( plugin_lang_get( 'frequency_name' ) );
        trigger_error( ERROR_EMPTY_FIELD, ERROR );
    }

    frequency_ensure_valid_field_values( $p_minute, 0, 59, 'error_frequency_invalid_minute_value' );
    frequency_ensure_valid_field_values( $p_hour, 0, 23, 'error_frequency_invalid_hour_value' );
    frequency_ensure_valid_field_values( $p_day_of_month, 1, 31, 'error_frequency_invalid_day_of_month_value' );
    frequency_ensure_valid_field_values( $p_month, 1, 12, 'error_frequency_invalid_month_value' );
    frequency_ensure_valid_field_values( $p_day_of_week, 0, 6, 'error_frequency_invalid_day_of_week_value' );

    frequency_ensure_unique( $p_name, $p_minute, $p_hour, $p_day_of_month, $p_month, $p_day_of_week );

    $t_frequency_table = plugin_table( 'frequency' );
    $c_enabled = db_prepare_bool( $p_enabled );

    $query = "INSERT INTO $t_frequency_table
                (
                    name,
                    enabled,
                    minute,
                    hour,
                    day_of_month,
                    month,
                    day_of_week
                )
            VALUES
                (" .
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
        array( $p_name, $c_enabled, $p_minute, $p_hour, $p_day_of_month, $p_month, $p_day_of_week )
    );

    return db_insert_id( $t_frequency_table );
}

/**
 * Update frequency record
 *
 * @param int $p_frequency_id Frequency record id
 * @param string $p_name Frequency name
 * @param bool $p_enabled Boolean flag which indicates whether frequency is enabled or not
 * @param string $p_minute Minute component
 * @param string $p_hour Hour component
 * @param string $p_day_of_month Day of month component
 * @param string $p_month Month component
 * @param string $p_day_of_week Day of week component
 * @return void
 */
function frequency_update( $p_frequency_id, $p_name, $p_enabled, $p_minute, $p_hour, $p_day_of_month, $p_month, $p_day_of_week ) {
    if( '' == $p_name ) {
        error_parameters( plugin_lang_get( 'frequency_name' ) );
        trigger_error( ERROR_EMPTY_FIELD, ERROR );
    }

    frequency_ensure_unique( $p_name, $p_minute, $p_hour, $p_day_of_month, $p_month, $p_day_of_week, $p_frequency_id );

    $t_frequency_table = plugin_table( 'frequency' );
    $c_frequency_id = db_prepare_int( $p_frequency_id );
    $c_enabled = db_prepare_bool( $p_enabled );

    $query = "UPDATE $t_frequency_table
                SET
                    name = " . db_param() . ",
                    enabled = " . db_param() . ",
                    minute = " . db_param() . ",
                    hour = " . db_param() . ",
                    day_of_month = " . db_param() . ",
                    month = " . db_param() . ",
                    day_of_week = " . db_param() . "
                WHERE id = " . db_param() . ";";
    db_query_bound(
        $query,
        array( $p_name, $c_enabled, $p_minute, $p_hour, $p_day_of_month, $p_month, $p_day_of_week, $c_frequency_id )
    );
}

/**
 * Delete frequency record
 *
 * @param int $p_frequency_id Frequency record id
 * @return void
 */
function frequency_delete( $p_frequency_id ) {
    $t_frequency_table = plugin_table( 'frequency' );

    $c_frequency_id = db_prepare_int( $p_frequency_id );

    $query = "DELETE FROM $t_frequency_table WHERE id = " . db_param();
    db_query_bound( $query, array( $c_frequency_id ) );

    return true;
}

/**
 * Log frequency event (add, delete)
 *
 * @param int $p_frequency_id Frequency id
 * @param int $p_event_type Event type
 * @return void
 */
function frequency_log_event_special( $p_frequency_id, $p_event_type ) {
    $t_frequency_history_table = plugin_table( 'frequency_history' );
    $t_user_id = auth_get_current_user_id();

    $c_frequency_id = db_prepare_int( $p_frequency_id );
    $c_event_type = db_prepare_int( $p_event_type );

    $query = "INSERT $t_frequency_history_table (user_id, frequency_id, date_modified, type)
                VALUES (" . db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . db_param() . ');';
    db_query_bound( $query, array( $t_user_id, $c_frequency_id, db_now(), $c_event_type ) );
}

/**
 * Log frequency change event
 *
 * Record the actual changes made (old/new values)
 *
 * @param int $p_frequency_id Frequency record id
 * @param string $p_field_name Field name
 * @param mixed $p_old_value Old field value
 * @param mixed $p_new_value New field value
 * @return void
 */
function frequency_log_event( $p_frequency_id, $p_field_name , $p_old_value, $p_new_value ) {
    $t_frequency_history_table = plugin_table( 'frequency_history' );
    $t_user_id = auth_get_current_user_id();

    $c_frequency_id = db_prepare_int( $p_frequency_id );

    $query = "INSERT
                $t_frequency_history_table
            (
                user_id,
                frequency_id,
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
        array( $t_user_id, $c_frequency_id, db_now(), MST_FREQUENCY_CHANGED, $p_field_name , $p_old_value, $p_new_value )
    );
}

/**
 * Determine differences between two given frequency records and log them
 *
 * @param int $p_frequency_id Frequency record id
 * @param mixed $p_old_record Frequency object representing the old record
 * @param mixed $p_new_record Frequency object representing the new record
 * @return void
 */
function frequency_log_changes( $p_frequency_id, $p_old_record, $p_new_record ) {
    if( $p_old_record->name != $p_new_record->name ) {
        frequency_log_event( $p_frequency_id, 'name', $p_old_record->name, $p_new_record->name );
    }

    if( $p_old_record->enabled != $p_new_record->enabled ) {
        frequency_log_event_special( $p_frequency_id, $p_new_record->enabled ? MST_FREQUENCY_ENABLED : MST_FREQUENCY_DISABLED );
    }

    if( $p_old_record->minute != $p_new_record->minute ) {
        frequency_log_event( $p_frequency_id, 'minute', $p_old_record->minute, $p_new_record->minute );
    }

    if( $p_old_record->hour != $p_new_record->hour ) {
        frequency_log_event( $p_frequency_id, 'hour', $p_old_record->hour, $p_new_record->hour );
    }

    if( $p_old_record->day_of_month != $p_new_record->day_of_month ) {
        frequency_log_event( $p_frequency_id, 'day_of_month', $p_old_record->day_of_month, $p_new_record->day_of_month );
    }

    if( $p_old_record->month != $p_new_record->month ) {
        frequency_log_event( $p_frequency_id, 'month', $p_old_record->month, $p_new_record->month );
    }

    if( $p_old_record->day_of_week != $p_new_record->day_of_week ) {
        frequency_log_event( $p_frequency_id, 'day_of_week', $p_old_record->day_of_week, $p_new_record->day_of_week );
    }
}

/**
 * Get frequency history
 *
 * @param int $p_frequency_id Frequency record id
 * @return mixed Array of frequency history records
 */
function frequency_get_history( $p_frequency_id ) {
    $t_frequency_history_table = plugin_table( 'frequency_history' );

    $c_frequency_id = db_prepare_int( $p_frequency_id );

    $query = "SELECT
                H.date_modified,
                H.user_id,
                H.type,
                H.field_name,
                H.old_value,
                H.new_value
            FROM $t_frequency_history_table AS H
            WHERE H.frequency_id = " . db_param() . "
            ORDER BY
              H.date_modified;";
    $result = db_query_bound( $query, array( $c_frequency_id ) );

    $t_row_count = db_num_rows( $result );
    $t_history = array();

    for( $i = 0; $i < $t_row_count; $i++ ) {
        array_push( $t_history, db_fetch_array( $result ) );
    }

    return $t_history;
}
