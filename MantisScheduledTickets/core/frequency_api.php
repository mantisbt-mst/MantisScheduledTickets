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

define( 'ERROR_FREQUENCY_NAME_NOT_UNIQUE', 12151 );
define( 'ERROR_FREQUENCY_NOT_UNIQUE', 12152 );

define( 'FREQUENCY_ADDED', 1 );
define( 'FREQUENCY_ENABLED', 2 );
define( 'FREQUENCY_DISABLED', 3 );
define( 'FREQUENCY_CHANGED', 4 );
define( 'FREQUENCY_DELETED', 5 );

define( 'MAX_DAY_OF_WEEK', 6 );
define( 'MAX_MONTH', 12 );
define( 'MAX_DAY_OF_MONTH', 31 );
define( 'MAX_HOUR', 23 );
define( 'MAX_MINUTE', 59 );

/**
 * Frequency class
 */
class Frequency {
    /**
     * Frequency name
     */
    protected $name = '';

    /**
     * Flag which indicates whether template is enabled or not
     */
    protected $enabled = 1;

    /**
     * Minute component
     */
    protected $minute = '*';

    /**
     * Hour component
     */
    protected $hour = '*';

    /**
     * Day of month component
     */
    protected $day_of_month = '*';

    /**
     * Month component
     */
    protected $month = '*';

    /**
     * Day of week component
     */
    protected $day_of_week = '*';

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
    if( $p_filter ) {
        foreach( $p_filter as $t_column => $t_values ) {
            $t_where[] = "$t_column = " . db_param();
            $t_params[] = $t_values;
        }

        $query .= ' WHERE ' . implode( ' AND ', $t_where );
    }

    $query .= " ORDER BY F.name;";

    # run the query
    $result = db_query_bound( $query, $t_params );

    if( 0 == db_num_rows( $result ) ) {
        return null;
    }

    $t_row_count = db_num_rows( $result );
    $t_frequencies = array();

    for( $i = 0; $i < $t_row_count; $i++ ) {
        array_push( $t_frequencies, db_fetch_array( $result ) );
    }

    return $t_frequencies;
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
    $t_filter = array( 'enabled' => 1 );
    $t_frequencies = frequency_get_all( $t_filter );
    $t_crontab_frequencies = array();

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

    return $t_crontab_frequencies;
}

/**
 * Get a single frequency record by the specified id
 *
 * @param int $p_frequency_id Frequency id
 * @return array Associative array representing a frequency record
 */
function frequency_get_row( $p_frequency_id ) {
    $p_filter = array( 'id' => $p_frequency_id );
    $t_frequencies = frequency_get_all( $p_filter );

    if( !is_array( $t_frequencies[0] ) ) {
        plugin_error( plugin_lang_get( 'error_frequency_not_found' ), ERROR );
    } else {
        return $t_frequencies[0];
    }
}

/**
 * Check whether the given frequency name is unique
 *
 * @param string $p_name Frequency name
 * @param int $p_id Frequency id to exclude
 * @return bool True if the given frequency name is unique, false otherwise
 */
function frequency_name_is_unique( $p_name, $p_id = null ) {
    $t_frequency_table = plugin_table( 'frequency' );

    $query = "SELECT COUNT(*)
                FROM $t_frequency_table AS F
                WHERE F.name = " . db_param();

    if( $p_id ) {
        $query .= " AND F.id <> " . db_param();
    }
    $result = db_query_bound( $query, array( $p_name, $p_id ) );

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
 * @param int $p_id Frequency id
 * @return bool True if the given frequency (the collection of individual components) is unique, false otherwise
 */
function frequency_is_unique( $p_minute, $p_hour, $p_day_of_month, $p_month, $p_day_of_week, $p_id = null ) {
    $t_frequency_table = plugin_table( 'frequency' );

    $query = "SELECT COUNT(*)
                FROM $t_frequency_table AS F
                WHERE F.minute = " . db_param() . "
                  AND F.hour = " . db_param() . "
                  AND F.day_of_month = " . db_param() . "
                  AND F.month = " . db_param() . "
                  AND F.day_of_week = " . db_param();

    if( $p_id ) {
        $query .= " AND F.id <> " . db_param();
    }
    $result = db_query_bound( $query, array( $p_minute, $p_hour, $p_day_of_month, $p_month, $p_day_of_week, $p_id ) );

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
 * @param int $p_id Frequency id
 * @return void
 */
function frequency_ensure_unique( $p_name, $p_minute, $p_hour, $p_day_of_month, $p_month, $p_day_of_week, $p_id = null ) {
    if( !frequency_name_is_unique( $p_name, $p_id ) ) {
        /* @todo
        error_parameters( plugin_lang_get( 'error_frequency_name_not_unique' ) );
        trigger_error( ERROR_PLUGIN_GENERIC, ERROR );
        */

        plugin_error( plugin_lang_get( 'error_frequency_name_not_unique' ), ERROR );
    }

    if( !frequency_is_unique( $p_minute, $p_hour, $p_day_of_month, $p_month, $p_day_of_week, $p_id ) ) {
        /* @todo
        error_parameters( plugin_lang_get( 'error_frequency_not_unique' ) );
        trigger_error( ERROR_PLUGIN_GENERIC, ERROR );
        */

        plugin_error( plugin_lang_get( 'error_frequency_not_unique' ), ERROR );
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

    frequency_ensure_unique( $p_name, $p_minute, $p_hour, $p_day_of_month, $p_month, $p_day_of_week );

    $t_frequency_table = plugin_table( 'frequency' );

    $query = "INSERT INTO $t_frequency_table
                (name, enabled, minute, hour, day_of_month, month, day_of_week)
                VALUES
                (" . db_param() . ", " . db_param() . ", " . db_param() . ", ". db_param() . ", ". db_param() . ", ". db_param() . ", ". db_param() . ");";
    db_query_bound( $query, array( $p_name, $p_enabled, $p_minute, $p_hour, $p_day_of_month, $p_month, $p_day_of_week ) );

    return db_insert_id( $t_frequency_table );
}

/**
 * Update frequency record
 *
 * @param int $p_id Frequency record id
 * @param string $p_name Frequency name
 * @param bool $p_enabled Boolean flag which indicates whether frequency is enabled or not
 * @param string $p_minute Minute component
 * @param string $p_hour Hour component
 * @param string $p_day_of_month Day of month component
 * @param string $p_month Month component
 * @param string $p_day_of_week Day of week component
 * @return void
 */
function frequency_update( $p_id, $p_name, $p_enabled, $p_minute, $p_hour, $p_day_of_month, $p_month, $p_day_of_week ) {
    if( '' == $p_name ) {
        error_parameters( plugin_lang_get( 'frequency_name' ) );
        trigger_error( ERROR_EMPTY_FIELD, ERROR );
    }

    frequency_ensure_unique( $p_name, $p_minute, $p_hour, $p_day_of_month, $p_month, $p_day_of_week, $p_id );

    $t_frequency_table = plugin_table( 'frequency' );

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
    db_query_bound( $query, array( $p_name, $p_enabled, $p_minute, $p_hour, $p_day_of_month, $p_month, $p_day_of_week, $p_id ) );
}

/**
 * Delete frequency record
 *
 * @param int $p_id Frequency record id
 * @return void
 */
function frequency_delete( $p_id ) {
    $t_frequency_table = plugin_table( 'frequency' );

    $query = "DELETE FROM $t_frequency_table WHERE id = " . db_param();
    db_query_bound( $query, array( $p_id ) );

    return true;
}

/**
 * Render 'day of week' column
 *
 * @param string $p_day_of_week Day of week component
 * @return string Comma-separated list of days of the week
 */
function frequency_helper_day_of_week_column( $p_day_of_week ) {
    if( '*' == $p_day_of_week ) {
        return plugin_lang_get( 'frequency_day_of_week_all' );
    }

    $t_day_of_week = split( ',', $p_day_of_week );
    $t_day_of_week_names = array();

    foreach( $t_day_of_week as $day_of_week ) {
        $t_day_of_week_names[] = plugin_lang_get( 'frequency_day_of_week_' . $day_of_week );
    }

    return join( ', ', $t_day_of_week_names );
}

/**
 * Render 'month' column
 *
 * @param string $p_month Month component
 * @return string Comma-separated list of month names
 */
function frequency_helper_month_column( $p_month ) {
    if( '*' == $p_month ) {
        return plugin_lang_get( 'frequency_month_all' );
    }

    $t_month = split( ',', $p_month );
    $t_month_names = array();

    foreach( $t_month as $month ) {
        $t_month_names[] = plugin_lang_get( 'frequency_month_' . $month );
    }

    return join( ', ', $t_month_names );
}

/**
 * Render 'day of month' column
 *
 * @param string $p_day_of_month Day of month component
 * @return string Comma-separated list of days of the month
 */
function frequency_helper_day_of_month_column( $p_day_of_month ) {
    if( '*' == $p_day_of_month ) {
        return plugin_lang_get( 'frequency_day_of_month_all' );
    }

    $t_day_of_month = split( ',', $p_day_of_month );
    $t_day_of_month_names = array();

    foreach( $t_day_of_month as $day_of_month ) {
        $t_day_of_month_names[] = plugin_lang_get( 'frequency_day_of_month_' . $day_of_month );
    }

    return join( ', ', $t_day_of_month_names );
}

/**
 * Render 'hour' column
 *
 * @param string $p_hour Hour component
 * @return string Comma-separated list of hours
 */
function frequency_helper_hour_column( $p_hour ) {
    if( '*' == $p_hour ) {
        return plugin_lang_get( 'frequency_hour_all' );
    }

    $t_hour = split( ',', $p_hour );
    $t_hour_names = array();

    foreach( $t_hour as $hour ) {
        $t_hour_names[] = plugin_lang_get( 'frequency_hour_' . $hour );
    }

    return join( ', ', $t_hour_names );
}

/**
 * Render 'minute' column
 *
 * @param string $p_minute Minute component
 * @return string Comma-separated list of minutes
 */
function frequency_helper_minute_column( $p_minute ) {
    if( '*' == $p_minute ) {
        return plugin_lang_get( 'frequency_minute_all' );
    }

    $t_minute = split( ',', $p_minute );
    $t_minute_names = array();

    foreach( $t_minute as $minute ) {
        $t_minute_names[] = plugin_lang_get( 'frequency_minute_' . $minute );
    }

    return join( ', ', $t_minute_names );
}

/**
 * Render 'day of week' select list
 *
 * @param string $p_day_of_week Day of week component
 * @return void
 */
function frequency_helper_render_day_of_week_options( $p_day_of_week = '*' ) {
    if( '*' != $p_day_of_week ) {
        $t_day_of_week = split( ',', $p_day_of_week );
    } else {
        $t_day_of_week = array();
    }
    $t_all_checked = ( '*' == $p_day_of_week );

    echo '<input type="radio" name="day_of_week" id="day_of_week" value="all"' . ( $t_all_checked ? ' checked="checked"' : '' ) . ' onclick="javascript:enable_disable(\'day_of_week_select\', true);">' . plugin_lang_get( 'frequency_day_of_week_all' ) . '<br />';
    echo '<input type="radio" name="day_of_week" id="day_of_week" value="select"' . ( !$t_all_checked ? ' checked="checked"' : '' ) . ' onclick="javascript:enable_disable(\'day_of_week_select\', false);" />' . plugin_lang_get( 'frequency_day_of_week_choose' ) . ' ';
    echo '<select name="day_of_week_select[]" id="day_of_week_select" size="7" style="vertical-align:top;" multiple' . ( $t_all_checked ? ' disabled>' : '>' );

    for( $i = 0; $i <= MAX_DAY_OF_WEEK; $i++ ) {
        $selected = in_array( $i, $t_day_of_week ) ? ' selected' : '';
        echo "<option value=\"$i\"$selected>" . plugin_lang_get( 'frequency_day_of_week_' . $i ) . '</option>';
    }

    echo '</select>';
}

/**
 * Render 'month' select list
 *
 * @param string $p_month Month component
 * @return void
 */
function frequency_helper_render_month_options( $p_month = '*' ) {
    if( '*' != $p_month ) {
        $t_month = split( ',', $p_month );
    } else {
        $t_month = array();
    }
    $t_all_checked = ( '*' == $p_month );

    echo '<input type="radio" name="month" id="month" value="all"' . ( $t_all_checked ? ' checked="checked"' : '' ) . ' onclick="javascript:enable_disable(\'month_select\', true);">' . plugin_lang_get( 'frequency_month_all' ) . '<br />';
    echo '<input type="radio" name="month" id="month" value="select"' . ( !$t_all_checked ? ' checked="checked"' : '' ) . ' onclick="javascript:enable_disable(\'month_select\', false);" />' . plugin_lang_get( 'frequency_month_choose' ) . ' ';
    echo '<select name="month_select[]" id="month_select" size="7" style="vertical-align:top;" multiple' . ( $t_all_checked ? ' disabled>' : '>' );

    for( $i = 1; $i <= MAX_MONTH; $i++ ) {
        $selected = in_array( $i, $t_month ) ? ' selected' : '';
        echo "<option value=\"$i\"$selected>" . plugin_lang_get( 'frequency_month_' . $i ) . '</option>';
    }

    echo '</select>';
}

/**
 * Render 'day of month' select list
 *
 * @param string $p_day_of_month Day of month component
 * @return void
 */
function frequency_helper_render_day_of_month_options( $p_day_of_month = '*' ) {
    if( '*' != $p_day_of_month ) {
        $t_day_of_month = split( ',', $p_day_of_month );
    } else {
        $t_day_of_month = array();
    }
    $t_all_checked = ( '*' == $p_day_of_month );

    echo '<input type="radio" name="day_of_month" id="day_of_month" value="all"' . ( $t_all_checked ? ' checked="checked"' : '' ) . ' onclick="javascript:enable_disable(\'day_of_month_select\', true);">' . plugin_lang_get( 'frequency_day_of_month_all' ) . '<br />';
    echo '<input type="radio" name="day_of_month" id="day_of_month" value="select"' . ( !$t_all_checked ? ' checked="checked"' : '' ) . ' onclick="javascript:enable_disable(\'day_of_month_select\', false);" />' . plugin_lang_get( 'frequency_day_of_month_choose' ) . ' ';
    echo '<select name="day_of_month_select[]" id="day_of_month_select" size="7" style="vertical-align:top;" multiple' . ( $t_all_checked ? ' disabled>' : '>' );

    for( $i = 1; $i <= MAX_DAY_OF_MONTH; $i++ ) {
        $selected = in_array( $i, $t_day_of_month ) ? ' selected' : '';
        echo "<option value=\"$i\"$selected>" . plugin_lang_get( 'frequency_day_of_month_' . $i ) . '</option>';
    }

    echo '</select>';
}

/**
 * Render 'hour' select list
 *
 * @param string $p_hour Hour component
 * @return void
 */
function frequency_helper_render_hour_options( $p_hour = '*' ) {
    if( '*' != $p_hour ) {
        $t_hour = split( ',', $p_hour );
    } else {
        $t_hour = array();
    }
    $t_all_checked = ( '*' == $p_hour );

    echo '<input type="radio" name="hour" id="hour" value="all"' . ( $t_all_checked ? ' checked="checked"' : '' ) . ' onclick="javascript:enable_disable(\'hour_select\', true);">' . plugin_lang_get( 'frequency_hour_all' ) . '<br />';
    echo '<input type="radio" name="hour" id="hour" value="select"' . ( !$t_all_checked ? ' checked="checked"' : '' ) . ' onclick="javascript:enable_disable(\'hour_select\', false);" />' . plugin_lang_get( 'frequency_hour_choose' ) . ' ';
    echo '<select name="hour_select[]" id="hour_select" size="7" style="vertical-align:top;" multiple' . ( $t_all_checked ? ' disabled>' : '>' );

    for( $i = 0; $i <= MAX_HOUR; $i++ ) {
        $selected = in_array( $i, $t_hour ) ? ' selected' : '';
        echo "<option value=\"$i\"$selected>" . plugin_lang_get( 'frequency_hour_' . $i ) . '</option>';
    }

    echo '</select>';
}

/**
 * Render 'minute' select list
 *
 * @param string $p_minute Minute component
 * @return void
 */
function frequency_helper_render_minute_options( $p_minute = '*' ) {
    if( '*' != $p_minute ) {
        $t_minute = split( ',', $p_minute );
    } else {
        $t_minute = array();
    }
    $t_all_checked = ( '*' == $p_minute );

    echo '<input type="radio" name="minute" id="minute" value="all"' . ( $t_all_checked ? ' checked="checked"' : '' ) . ' onclick="javascript:enable_disable(\'minute_select\', true);">' . plugin_lang_get( 'frequency_minute_all' ) . '<br />';
    echo '<input type="radio" name="minute" id="minute" value="select"' . ( !$t_all_checked ? ' checked="checked"' : '' ) . ' onclick="javascript:enable_disable(\'minute_select\', false);" />' . plugin_lang_get( 'frequency_minute_choose' ) . ' ';
    echo '<select name="minute_select[]" id="minute_select" size="7" style="vertical-align:top;" multiple' . ( $t_all_checked ? ' disabled>' : '>' );

    for( $i = 0; $i <= MAX_MINUTE; $i++ ) {
        $selected = in_array( $i, $t_minute ) ? ' selected' : '';
        echo "<option value=\"$i\"$selected>" . plugin_lang_get( 'frequency_minute_' . $i ) . '</option>';
    }

    echo '</select>';
}

/**
 * Log frequency event (add, delete)
 *
 * @param int $p_id Frequency id
 * @param int $p_event_type Event type
 * @return void
 */
function frequency_log_event_special( $p_id, $p_event_type ) {
    $t_frequency_history_table = plugin_table( 'frequency_history' );
    $p_user_id = auth_get_current_user_id();

    $query = "INSERT $t_frequency_history_table (user_id, frequency_id, date_modified, type)
                VALUES (" . db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . db_param() . ');';
    db_query_bound( $query, array( $p_user_id, $p_id, db_now(), $p_event_type ) );
}

/**
 * Log frequency change event
 *
 * Record the actual changes made (old/new values)
 *
 * @param int $p_id Frequency record id
 * @param string $p_field_name Field name
 * @param mixed $p_old_value Old field value
 * @param mixed $p_new_value New field value
 * @return void
 */
function frequency_log_event( $p_id, $p_field_name , $p_old_value, $p_new_value ) {
    $t_frequency_history_table = plugin_table( 'frequency_history' );
    $p_user_id = auth_get_current_user_id();

    $query = "INSERT $t_frequency_history_table (user_id, frequency_id, date_modified, type, field_name, old_value, new_value)
                VALUES (" . db_param() . ', ' . db_param() . ', ' . db_param() . ', ' .  db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . db_param() . ');';
    db_query_bound( $query, array( $p_user_id, $p_id, db_now(), FREQUENCY_CHANGED, $p_field_name , $p_old_value, $p_new_value ) );
}

/**
 * Determine differences between two given frequency records and log them
 *
 * @param int $p_id Frequency record id
 * @param mixed $p_old_record Frequency object representing the old record
 * @param mixed $p_new_record Frequency object representing the new record
 * @return void
 */
function frequency_log_changes( $p_id, $p_old_record, $p_new_record ) {
    if( $p_old_record->name != $p_new_record->name ) {
        frequency_log_event( $p_id, 'name', $p_old_record->name, $p_new_record->name );
    }

    if( $p_old_record->enabled != $p_new_record->enabled ) {
        frequency_log_event_special( $p_id, $p_new_record->enabled ? FREQUENCY_ENABLED : FREQUENCY_DISABLED );
    }

    if( $p_old_record->minute != $p_new_record->minute ) {
        frequency_log_event( $p_id, 'minute', $p_old_record->minute, $p_new_record->minute );
    }

    if( $p_old_record->hour != $p_new_record->hour ) {
        frequency_log_event( $p_id, 'hour', $p_old_record->hour, $p_new_record->hour );
    }

    if( $p_old_record->day_of_month != $p_new_record->day_of_month ) {
        frequency_log_event( $p_id, 'day_of_month', $p_old_record->day_of_month, $p_new_record->day_of_month );
    }

    if( $p_old_record->month != $p_new_record->month ) {
        frequency_log_event( $p_id, 'month', $p_old_record->month, $p_new_record->month );
    }

    if( $p_old_record->day_of_week != $p_new_record->day_of_week ) {
        frequency_log_event( $p_id, 'day_of_week', $p_old_record->day_of_week, $p_new_record->day_of_week );
    }
}

/**
 * Get frequency history
 *
 * @param int $p_id Frequency record id
 * @return mixed Array of frequency history records
 */
function frequency_get_history( $p_id ) {
    $t_frequency_history_table = plugin_table( 'frequency_history' );

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
    $result = db_query_bound( $query, array( $p_id ) );

     if( 0 == db_num_rows( $result ) ) {
        return null;
    }

    $t_row_count = db_num_rows( $result );
    $t_history = array();

    for( $i = 0; $i < $t_row_count; $i++ ) {
        array_push( $t_history, db_fetch_array( $result ) );
    }

    return $t_history;
}
