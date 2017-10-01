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

define( 'MST_ESCAPE_FOR_NONE', 0 );
define( 'MST_ESCAPE_FOR_HTML', 1 );
define( 'MST_ESCAPE_FOR_JS', 2 );
define( 'MST_ESCAPE_FOR_COMMAND_LINE', 3 );

/**
 * CommandArgument class_alias
 */
class CommandArgument {
    /**
     * Argument name
     */
    protected $name = null;

    /**
     * Argument value
     */
    protected $value = null;

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
     * @param string $name Parameter name
     * @return mixed Property value
     */
    public function __get( $name ) {
        return $this->$name;
    }
}

/**
 * Get command arguments
 *
 * @param int $p_template_category_id Template category id
 * @return mixed Array of command arguments
 */
function command_argument_get_all( $p_template_category_id ) {
    $t_command_argument_table = plugin_table( 'command_argument' );

    $c_template_category_id = db_prepare_int( $p_template_category_id );

    $query = "SELECT
                TCCA.id,
                TCCA.template_category_id,
                TCCA.argument_name,
                TCCA.argument_value
            FROM $t_command_argument_table AS TCCA
            WHERE TCCA.template_category_id = " . db_param();

    # run the query
    $result = db_query_bound( $query, $c_template_category_id );

    if( 0 == db_num_rows( $result ) ) {
        return null;
    }

    $t_row_count = db_num_rows( $result );
    $t_command_arguments = array();

    for( $i = 0; $i < $t_row_count; $i++ ) {
        array_push( $t_command_arguments, db_fetch_array( $result ) );
    }

    return $t_command_arguments;
}

/**
 * Check whether the given argument name is unique
 *
 * @param string $p_argument_name Argument name to check
 * @param int $p_template_category_id Template category record id
 * @param int $p_command_argument_id Command argument record id to exclude
 * @return bool True if the given argument name is unique, false otherwise
 */
function command_argument_is_unique( $p_argument_name, $p_template_category_id, $p_command_argument_id = null ) {
    $t_command_argument_table = plugin_table( 'command_argument' );
    $c_template_category_id = db_prepare_int( $p_template_category_id );
    $c_command_argument_id = db_prepare_int( $p_command_argument_id );

    $query = "SELECT COUNT(*)
            FROM $t_command_argument_table AS TCCA
            WHERE TCCA.template_category_id = " . db_param() . "
                AND TCCA.argument_name = " . db_param();

    if( $p_command_argument_id ) {
        $query .= " AND TCCA.id <> " . db_param();
    }

    $result = db_query_bound(
        $query,
        $p_command_argument_id ?
            array( $p_argument_name, $c_template_category_id, $c_command_argument_id ) :
            array( $p_argument_name, $c_template_category_id )
    );

    if( 0 < db_result( $result ) ) {
        return false;
    }

    return true;
}

/**
 * Validate command argument name
 *
 * A valid argument name must:
 * * only contain alphanumeric characters and/or dashes
 * * not be "--diff-left-side" or "--output-file" (reserved for plugin use)
 *
 * @param $p_argument_name Argument name
 * @param int $p_command_argument_id Command argument record id to exclude from uniqueness check
 * @return bool True if argument name is valid, false otherwise
 */
function command_argument_is_valid( $p_argument_name, $p_command_argument_id = null ) {
    preg_match( '/[^[:alnum:]-]/u', $p_argument_name, $t_matches );

    if( $t_matches ) {
        return false;
    }

    switch( trim( strtolower( $p_argument_name ) ) ) {
        case '--diff-left-side':
        case '--output-file':
        case '';
            return false;
    }

    return command_argument_is_unique( $p_argument_name, $p_command_argument_id );
}

/**
 * Add command arguments record
 *
 * @param int $p_template_category_id Template category id
 * @param string $p_argument_name Argument name
 * @param string $p_argument_value Argument value
 * @return void
 */
function command_argument_add( $p_template_category_id, $p_argument_name, $p_argument_value ) {
    $t_command_argument_table = plugin_table( 'command_argument' );
    $c_template_category_id = db_prepare_int( $p_template_category_id );

    $query = "INSERT $t_command_argument_table
                (template_category_id, argument_name, argument_value)
            VALUES
                (" . db_param(). ", " . db_param() . ", " . db_param() . ");";
    db_query_bound( $query, array( $c_template_category_id, $p_argument_name, $p_argument_value ) );
}

/**
 * Update command arguments record
 *
 * @param int $p_command_argument_id Command argument record id
 * @param string $p_argument_name Argument name
 * @param string $p_argument_value Argument value
 * @return void
 */
function command_argument_update( $p_command_argument_id, $p_argument_name, $p_argument_value ) {
    $t_command_argument_table = plugin_table( 'command_argument' );
    $c_command_argument_id = db_prepare_int( $p_command_argument_id );

    $query = "UPDATE $t_command_argument_table
            SET
                argument_name = " . db_param() . ",
                argument_value = " . db_param() . "
            WHERE id = " . db_param() . ";";
    db_query_bound( $query, array( $p_argument_name, $p_argument_value, $c_command_argument_id ) );
}

/**
 * Delete command arguments record
 *
 * NOTE: When calling this function, the values provided via $p_filter
 * MUST have already been cleansed (i.e. the value in the array should
 * be the result of the appropriate db_prepare_* function).
 *
 * @param int $p_filter Filter array
 * @return void
 */
function command_argument_delete( $p_filter ) {
    $t_command_argument_table = plugin_table( 'command_argument' );

    $query = "DELETE FROM $t_command_argument_table";

    $t_where_clause = null;

    if( $p_filter ) {
        foreach( $p_filter as $t_field_name => $t_field_value ) {
            $t_where_clause[] .= " $t_field_name = " . db_param();
            $t_values[] = $t_field_value;
        }
    }

    if( $t_where_clause ) {
        $query .= " WHERE " . join( ' ', $t_where_clause );
    }

    db_query_bound( $query, $t_values );
}

/**
 * Format command arguments
 *
 * The $p_escape_for flag indicates where the arguments are to be used (e.g. HTML, Javascript or command line), so that
 * the correct entities are escaped.
 * * NONE: no changes are made to the argument value
 * * HTML: double quotes are prefixed with a slash
 * * JS: single quotes are replaced with a slash, double quotes are replaced with &quot;
 * * COMMAND_LINE: double quotes are prefixed with a slash and the whole argument value is enclosed in double quotes
 *
 * @param mixed $p_command_arguments Array containing command arguments
 * @param int $p_escape_for Flag that indicates the intended use
 * @return string Formatted string containing command arguments
 */
function command_arguments_format( $p_command_arguments, $p_escape_for = MST_ESCAPE_FOR_NONE ) {
    $t_command_arguments[] = '';

    if( is_array( $p_command_arguments ) ) {
        foreach( $p_command_arguments as $t_argument ) {
            $t_command_argument = $t_argument['argument_name'];

            if( '' != $t_argument['argument_value'] ) {
                $t_command_argument .= '=' . command_argument_format( $t_argument['argument_value'], $p_escape_for );
            }

            $t_command_arguments[] = $t_command_argument;
        }
    }

    return trim( implode( ' ', $t_command_arguments ) );
}

/**
 * Format a single command argument
 *
 * This function is NOT meant to be called by page logic. Please call command_arguments_format instead (nts_ NOT nt_)
 *
 * @param string $p_command_argument_value Argument value
 * @param int $p_escape_for Flag that indicates the intended use
 * @return string Escaped argument value
 */
function command_argument_format( $p_command_argument_value, $p_escape_for ) {
    switch( $p_escape_for ) {
        case MST_ESCAPE_FOR_NONE:
            return $p_command_argument_value;
            break;
        case MST_ESCAPE_FOR_HTML:
            return str_replace( '"', '\"', $p_command_argument_value );
            break;
        case MST_ESCAPE_FOR_JS:
            return str_replace( "'", "\'", str_replace( '"', '&quot;', $p_command_argument_value ) );
            break;
        case MST_ESCAPE_FOR_COMMAND_LINE:
            if( '' != trim( $p_command_argument_value ) ) {
                return '"' . str_replace( '"', '\"', $p_command_argument_value ) . '"';
            } else {
                return '';
            }
            break;
        default:
            break;
    }
}
