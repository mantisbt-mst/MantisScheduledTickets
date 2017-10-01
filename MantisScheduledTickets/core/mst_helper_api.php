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
 * Prompt the user to confirm the action.
 *
 * This function performs a similar role to the Mantis helper_ensure_confirmed,
 * but allows for more than one button / action.
 *
 * @param string $p_message Message to display
 * @param mixed $p_button_labels Button labels
 * @return bool True if the user confirmed (clicked one of the buttons)
 */
function mst_helper_ensure_confirmed( $p_message, $p_button_labels ) {
    if( gpc_get_bool( '_confirmed' ) ) {
        return true;
    }

    html_page_top();

    echo '<br /><div align="center">';
    print_hr();
    echo $p_message;

    echo '<form method="post" action="' . string_attribute( form_action_self() ) . '">';
    # CSRF protection not required here - user needs to confirm action
    # before the form is accepted.
    print_hidden_inputs( gpc_strip_slashes( $_POST ) );
    print_hidden_inputs( gpc_strip_slashes( $_GET ) );

    echo '<input type="hidden" name="_confirmed" value="1" /><br /><br />';

    if( is_array( $p_button_labels ) ) {
        foreach( $p_button_labels as $t_button_label ) {
            echo '<input type="submit" class="button" name="action" value="' . $t_button_label . '" />&nbsp;';
        }
    }

    echo '</form>';

    print_hr();
    echo '</div>';
    html_page_bottom();
    exit;
}

/**
 * Determine whether the given command is still valid or not.
 *
 * A command is valid when an executable file with the given name exists under the plugin's scripts/ directory
 *
 * @param string $p_command Command to check
 * @return bool True if command is valid, false otherwise
 */
function mst_helper_command_is_valid( $p_command ) {
    $t_commands = mst_helper_get_valid_commands();

    return in_array( $p_command, $t_commands );
}

/**
 * Get a list of valid commands
 *
 * Valid commands are any entries under the plugin's scripts directory with a type of "file" that are also marked as
 * executable. All other entries (directories, symlinks, non-executable files etc) are ignored.
 *
 * @return array Array containing valid commands
 */
function mst_helper_get_valid_commands() {
    return explode(
        ',',
        shell_exec( 'find ./plugins/MantisScheduledTickets/scripts -type f -executable -printf ",%f"' )
    );
}

/**
 * Generate command dropdown
 *
 * Get list of executable files in the scripts/ directory
 *
 * @param string $p_command Currently-selected command
 */
function mst_helper_commands( $p_command ) {
    $t_commands = mst_helper_get_valid_commands();
    $t_command_found = false;

    $t_select = '<select name="command" id="command" onchange="javascript:enable_disable_diff(\'command\', \'diff_flag\');">';

    if( is_array( $t_commands ) ) {
        foreach( $t_commands as $t_command ) {
            $t_select .= '<option value="' .
                $t_command . '"' .
                ( ( $t_command == $p_command ) ? ' selected="selected">' : '>' ) .
                $t_command .
                '</option>';

            if( $p_command == $t_command ) {
                $t_command_found = true;
            }
        }
    }

    if( false == $t_command_found ) {
        $t_select .= '<option value="' . $p_command . '" selected="selected">' . $p_command . '</option>';
    }

    $t_select .= '</select>';

    echo $t_select;
}

/**
 * Render 'day of week' column
 *
 * @param string $p_day_of_week Day of week component
 * @return string Comma-separated list of days of the week
 */
function mst_helper_day_of_week_column( $p_day_of_week ) {
    if( '*' == $p_day_of_week ) {
        return plugin_lang_get( 'frequency_day_of_week_all' );
    }

    $t_day_of_week = explode( ',', $p_day_of_week );
    $t_day_of_week_names = array();

    if( is_array( $t_day_of_week ) ) {
        foreach( $t_day_of_week as $day_of_week ) {
            $t_day_of_week_names[] = plugin_lang_get( 'frequency_day_of_week_' . $day_of_week );
        }
    }

    return join( ', ', $t_day_of_week_names );
}

/**
 * Render 'month' column
 *
 * @param string $p_month Month component
 * @return string Comma-separated list of month names
 */
function mst_helper_month_column( $p_month ) {
    if( '*' == $p_month ) {
        return plugin_lang_get( 'frequency_month_all' );
    }

    $t_month = explode( ',', $p_month );
    $t_month_names = array();

    if( is_array( $t_month ) ) {
        foreach( $t_month as $month ) {
            $t_month_names[] = plugin_lang_get( 'frequency_month_' . $month );
        }
    }

    return join( ', ', $t_month_names );
}

/**
 * Render 'day of month' column
 *
 * @param string $p_day_of_month Day of month component
 * @return string Comma-separated list of days of the month
 */
function mst_helper_day_of_month_column( $p_day_of_month ) {
    if( '*' == $p_day_of_month ) {
        return plugin_lang_get( 'frequency_day_of_month_all' );
    }

    $t_day_of_month = explode( ',', $p_day_of_month );
    $t_day_of_month_names = array();

    if( is_array( $t_day_of_month ) ) {
        foreach( $t_day_of_month as $day_of_month ) {
            $t_day_of_month_names[] = plugin_lang_get( 'frequency_day_of_month_' . $day_of_month );
        }
    }

    return join( ', ', $t_day_of_month_names );
}

/**
 * Render 'hour' column
 *
 * @param string $p_hour Hour component
 * @return string Comma-separated list of hours
 */
function mst_helper_hour_column( $p_hour ) {
    if( '*' == $p_hour ) {
        return plugin_lang_get( 'frequency_hour_all' );
    }

    $t_hour = explode( ',', $p_hour );
    $t_hour_names = array();

    if( is_array( $t_hour ) ) {
        foreach( $t_hour as $hour ) {
            $t_hour_names[] = plugin_lang_get( 'frequency_hour_' . $hour );
        }
    }

    return join( ', ', $t_hour_names );
}

/**
 * Render 'minute' column
 *
 * @param string $p_minute Minute component
 * @return string Comma-separated list of minutes
 */
function mst_helper_minute_column( $p_minute ) {
    if( '*' == $p_minute ) {
        return plugin_lang_get( 'frequency_minute_all' );
    }

    $t_minute = explode( ',', $p_minute );
    $t_minute_names = array();

    if( is_array( $t_minute ) ) {
        foreach( $t_minute as $minute ) {
            $t_minute_names[] = plugin_lang_get( 'frequency_minute_' . $minute );
        }
    }

    return join( ', ', $t_minute_names );
}

/**
 * Render 'day of week' select list
 *
 * @param string $p_day_of_week Day of week component
 * @return void
 */
function mst_helper_render_day_of_week_options( $p_day_of_week = '*' ) {
    if( '*' != $p_day_of_week ) {
        $t_day_of_week = explode( ',', $p_day_of_week );
    } else {
        $t_day_of_week = array();
    }
    $t_all_checked = ( '*' == $p_day_of_week );

    echo '<input type="radio" name="day_of_week" id="day_of_week" value="all"' .
        ( $t_all_checked ? ' checked="checked"' : '' ) .
        ' onclick="javascript:enable_disable(\'day_of_week_select\', true);">' .
        plugin_lang_get( 'frequency_day_of_week_all' ) . '<br />';
    echo '<input type="radio" name="day_of_week" id="day_of_week" value="select"' .
        ( ( false == $t_all_checked ) ? ' checked="checked"' : '' ) .
        ' onclick="javascript:enable_disable(\'day_of_week_select\', false);" />' .
        plugin_lang_get( 'frequency_day_of_week_choose' ) . ' ';
    echo '<select name="day_of_week_select[]" id="day_of_week_select" size="7" style="vertical-align:top;" multiple' .
        ( $t_all_checked ? ' disabled="disabled">' : '>' );

    for( $i = 0; $i <= MST_MAX_DAY_OF_WEEK; $i++ ) {
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
function mst_helper_render_month_options( $p_month = '*' ) {
    if( '*' != $p_month ) {
        $t_month = explode( ',', $p_month );
    } else {
        $t_month = array();
    }
    $t_all_checked = ( '*' == $p_month );

    echo '<input type="radio" name="month" id="month" value="all"' .
        ( $t_all_checked ? ' checked="checked"' : '' ) .
        ' onclick="javascript:enable_disable(\'month_select\', true);">' .
        plugin_lang_get( 'frequency_month_all' ) . '<br />';
    echo '<input type="radio" name="month" id="month" value="select"' .
        ( ( false == $t_all_checked ) ? ' checked="checked"' : '' ) .
        ' onclick="javascript:enable_disable(\'month_select\', false);" />' .
        plugin_lang_get( 'frequency_month_choose' ) . ' ';
    echo '<select name="month_select[]" id="month_select" size="7" style="vertical-align:top;" multiple' .
        ( $t_all_checked ? ' disabled="disabled">' : '>' );

    for( $i = 1; $i <= MST_MAX_MONTH; $i++ ) {
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
function mst_helper_render_day_of_month_options( $p_day_of_month = '*' ) {
    if( '*' != $p_day_of_month ) {
        $t_day_of_month = explode( ',', $p_day_of_month );
    } else {
        $t_day_of_month = array();
    }
    $t_all_checked = ( '*' == $p_day_of_month );

    echo '<input type="radio" name="day_of_month" id="day_of_month" value="all"' .
        ( $t_all_checked ? ' checked="checked"' : '' ) .
        ' onclick="javascript:enable_disable(\'day_of_month_select\', true);">' .
        plugin_lang_get( 'frequency_day_of_month_all' ) . '<br />';
    echo '<input type="radio" name="day_of_month" id="day_of_month" value="select"' .
        ( ( false == $t_all_checked ) ? ' checked="checked"' : '' ) .
        ' onclick="javascript:enable_disable(\'day_of_month_select\', false);" />' .
        plugin_lang_get( 'frequency_day_of_month_choose' ) . ' ';
    echo '<select name="day_of_month_select[]" id="day_of_month_select" size="7" style="vertical-align:top;" multiple' .
        ( $t_all_checked ? ' disabled="disabled">' : '>' );

    for( $i = 1; $i <= MST_MAX_DAY_OF_MONTH; $i++ ) {
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
function mst_helper_render_hour_options( $p_hour = '*' ) {
    if( '*' != $p_hour ) {
        $t_hour = explode( ',', $p_hour );
    } else {
        $t_hour = array();
    }
    $t_all_checked = ( '*' == $p_hour );

    echo '<input type="radio" name="hour" id="hour" value="all"' .
        ( $t_all_checked ? ' checked="checked"' : '' ) .
        ' onclick="javascript:enable_disable(\'hour_select\', true);">' .
        plugin_lang_get( 'frequency_hour_all' ) . '<br />';
    echo '<input type="radio" name="hour" id="hour" value="select"' .
        ( ( false == $t_all_checked ) ? ' checked="checked"' : '' ) .
        ' onclick="javascript:enable_disable(\'hour_select\', false);" />' .
        plugin_lang_get( 'frequency_hour_choose' ) . ' ';
    echo '<select name="hour_select[]" id="hour_select" size="7" style="vertical-align:top;" multiple' .
        ( $t_all_checked ? ' disabled="disabled">' : '>' );

    for( $i = 0; $i <= MST_MAX_HOUR; $i++ ) {
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
function mst_helper_render_minute_options( $p_minute = '*' ) {
    if( '*' != $p_minute ) {
        $t_minute = explode( ',', $p_minute );
    } else {
        $t_minute = array();
    }
    $t_all_checked = ( '*' == $p_minute );

    echo '<input type="radio" name="minute" id="minute" value="all"' .
        ( $t_all_checked ? ' checked="checked"' : '' ) .
        ' onclick="javascript:enable_disable(\'minute_select\', true);">' .
        plugin_lang_get( 'frequency_minute_all' ) . '<br />';
    echo '<input type="radio" name="minute" id="minute" value="select"' .
        ( ( false == $t_all_checked ) ? ' checked="checked"' : '' ) .
        ' onclick="javascript:enable_disable(\'minute_select\', false);" />' .
        plugin_lang_get( 'frequency_minute_choose' ) . ' ';
    echo '<select name="minute_select[]" id="minute_select" size="7" style="vertical-align:top;" multiple' .
        ( $t_all_checked ? ' disabled="disabled">' : '>' );

    for( $i = 0; $i <= MST_MAX_MINUTE; $i++ ) {
        $selected = in_array( $i, $t_minute ) ? ' selected' : '';
        echo "<option value=\"$i\"$selected>" . plugin_lang_get( 'frequency_minute_' . $i ) . '</option>';
    }

    echo '</select>';
}

/**
 * Get all enabled projects/categories
 *
 * @param int $p_project_id Project record id
 * @param int $p_category_id Category record id
 * @return void
 */
function mst_helper_available_categories( $p_project_id = null, $p_category_id = null ) {
    $t_category_table = db_get_table( 'mantis_category_table' );
    $t_project_table = db_get_table( 'mantis_project_table' );

    $query = "SELECT
                P.id AS project_id,
                P.name AS project_name,
                C.id AS category_id,
                C.name AS category_name
            FROM $t_project_table AS P
            JOIN $t_category_table AS C
                ON C.project_id = P.id
            WHERE P.enabled = 1

            UNION ALL

            SELECT
                P.id AS project_id,
                P.name AS project_name,
                C.id AS category_id,
                C.name AS category_name
            FROM $t_project_table AS P
            JOIN $t_category_table AS C
            WHERE P.enabled = 1
                AND C.project_id = 0
            ORDER BY
                project_name,
                category_name;";
    $result = db_query( $query );

    $t_row_count = db_num_rows( $result );

    $t_select = '<select name="category_id" id="category_id">';
    for( $i = 0; $i < $t_row_count; $i++ ) {
        $t_category = db_fetch_array( $result );
        $t_selected =
            ( ( $p_project_id == $t_category['project_id'] ) && ( $p_category_id == $t_category['category_id'] ) ) ?
                ' selected="selected"' :
                '';
        $t_select .= '<option ' .
            'value="' . $t_category['project_id'] . ',' . $t_category['category_id'] . '"' . $t_selected . '>' .
            '[' . $t_category['project_name'] . '] ' . $t_category['category_name'] . '</option>';
    }
    $t_select .= '</select>';

    echo $t_select;
}

/**
 * Generate active frequency dropdown
 *
 * Generate a dropdown list containing active frequencies. If $p_frequency_id is specified, mark the corresponding
 * option as selected. If the given given $p_frequency_id is NOT an active frequency, add a disabled option to the
 * dropdown list (to reflect the currently stored value).
 *
 * @param int $p_frequency_id (Optional) Frequency to mark as selected
 * @param string $p_frequency_name (Optional) Frequency name
 * @return void
 */
function mst_helper_frequencies( $p_frequency_id = null, $p_frequency_name = null ) {
    $t_frequencies = frequency_get_all_active();
    $t_frequency_found = false;

    $t_select = '<select name="frequency_id" id="frequency_id">';

    if( is_array( $t_frequencies ) ) {
        foreach( $t_frequencies as $t_frequency ) {
            $t_selected = ( $p_frequency_id == $t_frequency['id'] ) ? ' selected="selected"' : '';
            $t_select .= "<option value=\"{$t_frequency['id']}\"$t_selected>{$t_frequency['name']}</option>";

            if( $t_frequency['id'] == $p_frequency_id ) {
                $t_frequency_found = true;
            }
        }
    }

    if( ( false == $t_frequency_found ) && ( null != $p_frequency_name ) ) {
        $t_select .= '<option value="' . $p_frequency_id . '" selected="selected">' . $p_frequency_name . '</option>';
    }

    $t_select .= '</select>';

    echo $t_select;
}
