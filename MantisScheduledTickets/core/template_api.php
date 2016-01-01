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

define( 'TEMPLATE_ADDED', 1 );
define( 'TEMPLATE_ENABLED', 2 );
define( 'TEMPLATE_DISABLED', 3 );
define( 'TEMPLATE_CHANGED', 4 );
define( 'TEMPLATE_DELETED', 5 );

define( 'TEMPLATE_CATEGORY_ADDED', 11 );
define( 'TEMPLATE_CATEGORY_CHANGED', 12 );
define( 'TEMPLATE_CATEGORY_DELETED', 13 );

/**
 * Template class
 */
class Template {
    /**
     * Template summary
     */
    protected $summary = '';

    /**
     * Template description
     */
    protected $description = '';

    /**
     * Flag which indicates whether template is enabled or not
     */
    protected $enabled = 0;

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
 * TemplateCategory class
 */
class TemplateCategory {
    /**
     * Frequency record id
     */
    protected $frequency_id = 0;

    /**
     * User record id
     */
    protected $user_id = 0;

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
 * Get all template records
 *
 * Get an array of all template records that match the given filter
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

    $t_where = array();
    $t_params = array();

    $query = "SELECT
                T.id,
                T.summary,
                T.description,
                T.enabled,
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
    if( $p_filter ) {
        foreach( $p_filter as $t_column => $t_values ) {
            $t_where[] = "$t_column = " . db_param();
            $t_params[] = $t_values;
        }

        $query .= ' WHERE ' . implode( ' AND ', $t_where );
    }

    $query .= " ORDER BY T.summary;";

    # run the query
    $result = db_query_bound( $query, $t_params );

    if( 0 == db_num_rows( $result ) ) {
        return null;
    }

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
    $p_filter = array( 'id' => $p_template_id );
    $t_templates = template_get_all( $p_filter );

    if( !is_array( $t_templates[0] ) ) {
        plugin_error( plugin_lang_get( 'error_template_not_found' ), ERROR );
    } else {
        return $t_templates[0];
    }
}

/**
 * Get template/categories for a given frequency
 *
 * @param int $p_frequency_id Frequency id
 * @return mixed Array of templates/categories associated with the given frequency id
 */
function template_categories_get_by_frequency_id( $p_frequency_id ) {
    $t_template_table = plugin_table( 'template' );
    $t_template_category_table = plugin_table( 'template_category' );
    $t_category_table = db_get_table( 'mantis_category_table' );
    $t_project_table = db_get_table( 'mantis_project_table' );
    $t_user_table = db_get_table( 'mantis_user_table' );

    $query = "SELECT
                T.id AS template_id,
                T.summary,
                T.description,
                TC.category_id,
                IF(C.id IS NULL, 1, 0) AS invalid_category,
                P.id AS project_id,
                IF(P.id IS NULL, 1, 0) AS invalid_project,
                TC.user_id,
                IF(TC.user_id != 0 AND U.id IS NULL, 1, 0) AS invalid_user
            FROM $t_template_category_table AS TC
            JOIN $t_template_table AS T
                ON T.id = TC.template_id
            LEFT JOIN $t_category_table AS C
                ON C.id = TC.category_id
            LEFT JOIN $t_project_table AS P
                ON P.id = TC.project_id
            LEFT JOIN $t_user_table AS U
                ON U.id = TC.user_id
            WHERE T.enabled = 1
                AND TC.frequency_id = " . db_param() . "
            ORDER BY
                P.name,
                C.name;";

    # run the query
    $result = db_query_bound( $query, $p_frequency_id );

    if( 0 == db_num_rows( $result ) ) {
        return null;
    }

    $t_row_count = db_num_rows( $result );
    $t_template_categories = array();

    for( $i = 0; $i < $t_row_count; $i++ ) {
        array_push( $t_template_categories, db_fetch_array( $result ) );
    }

    return $t_template_categories;
}

/**
 * Get categories associated with a given template
 *
 * @param int $p_id Template record id
 * @param int $p_category_id (Optional) Category id (a single record should be returned in this case)
 * @return mixed Array of project/category, frequency and assignee records associated with the given template id
 */
function template_get_categories( $p_id, $p_category_id = null ) {
    $t_template_category_table = plugin_table( 'template_category' );
    $t_frequency_table = plugin_table( 'frequency' );
    $t_category_table = db_get_table( 'mantis_category_table' );
    $t_project_table = db_get_table( 'mantis_project_table' );

    $query = "SELECT
                TC.id,
                C.project_id,
                P.name AS project_name,
                TC.category_id,
                C.name AS category_name,
                TC.user_id,
                TC.frequency_id,
                F.name AS frequency_name
            FROM $t_template_category_table AS TC
            JOIN $t_frequency_table AS F
                ON F.id = TC.frequency_id
            LEFT JOIN $t_category_table AS C
                ON C.id = TC.category_id
            LEFT JOIN $t_project_table AS P
                ON P.id = TC.project_id
            WHERE TC.template_id = " . db_param();

    if( $p_category_id ) {
        $query .= " AND TC.category_id = " . db_param();
    }

    $query .= "
            ORDER BY
              P.name,
              C.name;";

    # run the query
    $result = db_query_bound( $query, array( $p_id, $p_category_id ) );

    if( 0 == db_num_rows( $result ) ) {
        return null;
    }

    $t_row_count = db_num_rows( $result );
    $t_categories = array();

    for( $i = 0; $i < $t_row_count; $i++ ) {
        array_push( $t_categories, db_fetch_array( $result ) );
    }

    return $t_categories;
}

/**
 * Check whether the given template summary is unique
 *
 * @param string $p_summary Template summary
 * @param int $p_id Template record id
 * @return bool True if template summary is unique, false otherwise
 */
function template_summary_is_unique( $p_summary, $p_id = null ) {
    $t_template_table = plugin_table( 'template' );

    $query = "SELECT
                COUNT(*)
            FROM $t_template_table AS T
            WHERE T.summary = " . db_param();

    if( $p_id ) {
        $query .= " AND T.id <> " . db_param();
    }
    $result = db_query_bound( $query, array( $p_name, $p_id ) );

    if( 0 < db_result( $result ) ) {
        return false;
    }

    return true;
}

/**
 * Ensure that the given template is unique
 *
 * @todo plugin error reporting appears to be broken in Mantis 1.2.19; revisit in the future
 *
 * @param string $p_summary Template summary
 * @param string $p_description Template description
 * @param int $p_id Template record id
 * @return void
 */
function template_ensure_unique( $p_summary, $p_description, $p_id = null ) {
    if( !template_summary_is_unique( $p_summary, $p_id ) ) {
        /* @todo
        error_parameters( plugin_lang_get( 'error_template_summary_not_unique' ) );
        trigger_error( ERROR_PLUGIN_GENERIC, ERROR );
        */

        plugin_error( plugin_lang_get( 'error_template_summary_not_unique' ), ERROR );
    }
}

/**
 * Create template record
 *
 * @param string $p_summary Template summary
 * @param string $p_description Template description
 * @param bool $p_enabled Flag which indicates whether template is enabled or not
 * @return int Template record id
 */
function template_add( $p_summary, $p_description, $p_enabled ) {
    if( '' == $p_summary ) {
        error_parameters( plugin_lang_get( 'template_summary' ) );
        trigger_error( ERROR_EMPTY_FIELD, ERROR );
    }

    if( '' == $p_description ) {
        error_parameters( plugin_lang_get( 'template_description' ) );
        trigger_error( ERROR_EMPTY_FIELD, ERROR );
    }

    template_ensure_unique( $p_summary, $p_description );

    $t_template_table = plugin_table( 'template' );

    $query = "INSERT INTO $t_template_table
                (summary, description, enabled)
            VALUES
                (" . db_param() . ", " . db_param() . ", " . db_param() . ");";
    db_query_bound( $query, array( $p_summary, $p_description, $p_enabled ) );

    return db_insert_id( $t_template_table );
}

/**
 * Update template record
 *
 * @param int $p_id Template record id
 * @param string $p_summary Template summary
 * @param string $p_description Template description
 * @param bool $p_enabled Flag which indicates whether template is enabled or not
 * @return void
 */
function template_update( $p_id, $p_summary, $p_description, $p_enabled ) {
    if( '' == $p_summary ) {
        error_parameters( plugin_lang_get( 'template_summary' ) );
        trigger_error( ERROR_EMPTY_FIELD, ERROR );
    }

    if( '' == $p_description ) {
        error_parameters( plugin_lang_get( 'template_description' ) );
        trigger_error( ERROR_EMPTY_FIELD, ERROR );
    }

    template_ensure_unique( $p_summary, $p_description, $p_id );

    $t_template_table = plugin_table( 'template' );

    $query = "UPDATE $t_template_table
            SET
                summary = " . db_param() . ",
                description = " . db_param() . ",
                enabled = " . db_param() . "
            WHERE id = " . db_param() . ";";
    db_query_bound( $query, array( $p_summary, $p_description, $p_enabled, $p_id ) );
}

/**
 * Delete template record
 *
 * @param int $p_id Template record id
 * @return void
 */
function template_delete( $p_id ) {
    $t_template_category_table = plugin_table( 'template_category' );
    $t_template_table = plugin_table( 'template' );

    $query = "DELETE FROM $t_template_category_table WHERE template_id = " . db_param();
    db_query_bound( $query, array( $p_id ) );

    $query = "DELETE FROM $t_template_table WHERE id = " . db_param();
    db_query_bound( $query, array( $p_id ) );

    return true;
}

/**
 * Check whether the given combination of template/project/category/frequency/user id unique
 *
 * @param int $p_template_id Template record id
 * @param int $p_project_id Project record id
 * @param int $p_category_id Category record id
 * @param int $p_frequency_id Frequency record id
 * @param int $p_user_id User record id
 * @param int $p_id Template/category record id
 * @return bool True if the combination is unique, false otherwise
 */
function template_category_frequency_user_is_unique( $p_template_id, $p_project_id, $p_category_id, $p_frequency_id, $p_user_id, $p_id = null ) {
    $t_template_category_table = plugin_table( 'template_category' );

    $query = "SELECT
                COUNT(*)
            FROM $t_template_category_table AS TC
            WHERE TC.template_id = " . db_param() . "
                AND TC.project_id = " . db_param() . "
                AND TC.category_id = " . db_param() . "
                AND TC.frequency_id = " . db_param() . "
                AND TC.user_id = " . db_param();

    if( $p_id ) {
        $query .= " AND TC.id <> " . db_param();
    }
    $result = db_query_bound( $query, array( $p_template_id, $p_project_id, $p_category_id, $p_frequency_id, $p_user_id, $p_id ) );

    if( 0 < db_result( $result ) ) {
        return false;
    }

    return true;
}


/**
 * Check whether the given combination of template/project/category/frequency/user id unique
 *
 * @param int $p_template_id Template record id
 * @param int $p_project_id Project record id
 * @param int $p_category_id Category record id
 * @param int $p_frequency_id Frequency record id
 * @param int $p_user_id User record id
 * @param int $p_id Template/category record id
 * @return bool True if the combination is unique, false otherwise
 */
function template_category_frequency_user_ensure_unique( $p_template_id, $p_project_id, $p_category_id, $p_frequency_id, $p_user_id, $p_id = null ) {
    if( !template_category_frequency_user_is_unique( $p_template_id, $p_project_id, $p_category_id, $p_frequency_id, $p_user_id, $p_id ) ) {
        /* @todo
        error_parameters( plugin_lang_get( 'error_template_category_frequency_user_not_unique' ) );
        trigger_error( ERROR_PLUGIN_GENERIC, ERROR );
        */

        plugin_error( plugin_lang_get( 'error_template_category_frequency_user_not_unique' ), ERROR );
    }
}

/**
 * Associate a category, frequency and user id to a template
 *
 * @param int $p_template_id Template record id
 * @param int $p_project_id Project record id
 * @param int $p_category_id Category record id
 * @param int $p_frequency_id Frequency record id
 * @param int $p_user_id User record id
 * @return int Template/category record id
 */
function template_category_add( $p_template_id, $p_project_id, $p_category_id, $p_frequency_id, $p_user_id ) {
    template_category_frequency_user_ensure_unique( $p_template_id, $p_project_id, $p_category_id, $p_frequency_id, $p_user_id );

    $t_template_category_table = plugin_table( 'template_category' );

    $query = "INSERT $t_template_category_table
                (template_id, project_id, category_id, user_id, frequency_id)
            VALUES
                (" . db_param() . ", " . db_param(). ", " . db_param() . ", " . db_param() . ", " . db_param() . ");";
    db_query_bound( $query, array( $p_template_id, $p_project_id, $p_category_id, $p_user_id, $p_frequency_id ) );

    return db_insert_id( $t_template_category_table );
}

/**
 * Update a template/category record with new frequency and/or user id
 *
 * @param int $p_id Template/category record id
 * @param int $p_template_id Template record id
 * @param int $p_category_id Category record id
 * @param int $p_frequency_id Frequency id
 * @param int $p_user_id User id
 * @return void
 */
function template_category_update( $p_id, $p_template_id, $p_category_id, $p_frequency_id, $p_user_id ) {
    template_category_frequency_user_ensure_unique( $p_template_id, $p_category_id, $p_frequency_id, $p_user_id, $p_id );

    $t_template_category_table = plugin_table( 'template_category' );

    $query = "UPDATE $t_template_category_table
            SET
                frequency_id = " . db_param() . ",
                user_id = " . db_param() . "
            WHERE id = " . db_param() . ";";
    db_query_bound( $query, array( $p_frequency_id, $p_user_id, $p_id ) );
}

/**
 * Disassociate a given project/category from the given template
 *
 * @param int $p_id Template/category record id
 * @return void
 */
function template_category_delete( $p_id ) {
    $t_template_category_table = plugin_table( 'template_category' );

    $query = "DELETE FROM $t_template_category_table
                WHERE id = " . db_param() . ";";
    db_query_bound( $query, array( $p_id ) );
}

/**
 * Get a specific template/category record
 *
 * @param int $p_id Template/category record id
 * @return mixed Associative array containing template/category record
 */
function template_category_get( $p_id ) {
    $t_template_category_table = plugin_table( 'template_category' );
    $t_category_table = db_get_table( 'mantis_category_table' );

    $query = "SELECT
                C.project_id,
                C.name AS category_name,
                C.id AS category_id,
                TC.frequency_id,
                TC.user_id
            FROM $t_template_category_table AS TC
            JOIN $t_category_table AS C
                ON C.id = TC.category_id
            WHERE TC.id = " . db_param() . ";";
    $result = db_query_bound( $query, array( $p_id ) );

    if( 0 == db_num_rows( $result ) ) {
        return null;
    }

    return db_fetch_array( $result );
}

/**
 * Get all enabled projects/categories
 *
 * @return void
 */
function template_helper_available_categories() {
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

    if( 0 == db_num_rows( $result ) ) {
        return null;
    }

    $t_row_count = db_num_rows( $result );
    $t_prev_project_name = '';

    $t_select = '<select name="category_id">';
    for( $i = 0; $i < $t_row_count; $i++ ) {
        $t_category = db_fetch_array( $result );

        if( $t_prev_project_name != $t_category['project_name'] ) {
            $t_prev_project_name = $t_category['project_name'];
            $t_select .= "<optgroup label=\"$t_prev_project_name\">";
        }
        $t_select .= '<option
            value="' . $t_category['project_id'] . ',' . $t_category['category_id'] . '"' .
            ( $t_category['already_associated'] ? ' disabled' : '' ) .
            '>' .
            $t_category['category_name'] . '</option>';
    }
    $t_select .= '</select>';

    echo $t_select;
}

/**
 * Generate active frequency dropdown
 *
 * @param int $p_frequency_id (Optional) Frequency to mark as selected
 * @return void
 */
function template_helper_frequencies( $p_frequency_id = null ) {
    $t_filter = array( 'enabled' => 1 );
    $t_frequencies = frequency_get_all( $t_filter );

    $t_select = '<select name="frequency_id">';
    foreach( $t_frequencies as $t_frequency ) {
        $t_selected = ( $p_frequency_id == $t_frequency['id'] ) ? ' selected' : '';
        $t_select .= "<option value=\"{$t_frequency['id']}\"$t_selected>{$t_frequency['name']}</option>";
    }
    $t_select .= '</select>';

    echo $t_select;
}

/**
 * Log template event (add, delete)
 *
 * @param int $p_id Template record id
 * @param int $p_event_type Event type
 * @return void
 */
function template_log_event_special( $p_id, $p_event_type ) {
    $t_template_history_table = plugin_table( 'template_history' );
    $t_user_id = auth_get_current_user_id();

    $query = "INSERT $t_template_history_table
                (user_id, template_id, date_modified, type)
            VALUES
                (" . db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . db_param() . ');';
    db_query_bound( $query, array( $t_user_id, $p_id, db_now(), $p_event_type ) );
}

/**
 * Log template change event
 *
 * Record the actual changes made (old/new values)
 *
 * @param int $p_id Template record id
 * @param string $p_field_name Field name
 * @param mixed $p_old_value Old field value
 * @param mixed $p_new_value New field value
 * @return void
 */
function template_log_event( $p_id, $p_field_name , $p_old_value, $p_new_value ) {
    $t_template_history_table = plugin_table( 'template_history' );
    $t_user_id = auth_get_current_user_id();

    $query = "INSERT $t_template_history_table
                (user_id, template_id, date_modified, type, field_name, old_value, new_value)
            VALUES
                (" . db_param() . ', ' . db_param() . ', ' . db_param() . ', ' .  db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . db_param() . ');';
    db_query_bound( $query, array( $t_user_id, $p_id, db_now(), TEMPLATE_CHANGED, $p_field_name , $p_old_value, $p_new_value ) );
}

/**
 * Determine differences between two given template records and log them
 *
 * @param int $p_id Template record id
 * @param mixed $p_old_record Template object representing the old record
 * @param mixed $p_new_record Template object representing the new record
 * @return void
 */
function template_log_changes( $p_id, $p_old_record, $p_new_record ) {
    if( $p_old_record->summary != $p_new_record->summary ) {
        template_log_event( $p_id, 'summary', $p_old_record->summary, $p_new_record->summary );
    }

    if( $p_old_record->description != $p_new_record->description ) {
        template_log_event( $p_id, 'description', $p_old_record->description, $p_new_record->description );
    }

    if( $p_old_record->enabled != $p_new_record->enabled ) {
        template_log_event_special( $p_id, $p_new_record->enabled ? TEMPLATE_ENABLED : TEMPLATE_DISABLED );
    }
}

/**
 * Log template/category event (add, delete)
 *
 * @param int $p_id Template record id
 * @param int $p_category_id Category id
 * @param int $p_event_type Event type
 * @return void
 */
function template_category_log_event_special( $p_id, $p_category_id, $p_event_type ) {
    $t_template_category_history_table = plugin_table( 'tmplt_cat_hist' );
    $t_user_id = auth_get_current_user_id();

    $t_project_category = category_full_name( $p_category_id, true, 0 );

    $query = "INSERT $t_template_category_history_table
                (user_id, template_id, date_modified, type, project_category )
            VALUES
                (" . db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . db_param() . ');';
    db_query_bound( $query, array( $t_user_id, $p_id, db_now(), $p_event_type, $t_project_category ) );
}

/**
 * Log template/category change event
 *
 * Record the actual changes made (old/new values)
 *
 * @param int $p_id Template record id
 * @param int $p_category_id Category id
 * @param string $p_field_name Field name
 * @param mixed $p_old_value Old field value
 * @param mixed $p_new_value New field value
 * @return void
 */
function template_category_log_event( $p_id, $p_category_id, $p_field_name, $p_old_value, $p_new_value ) {
    $t_template_category_history_table = plugin_table( 'tmplt_cat_hist' );
    $t_user_id = auth_get_current_user_id();

    $t_project_category = category_full_name( $p_category_id, true, 0 );

    $query = "INSERT $t_template_category_history_table
                (user_id, template_id, date_modified, type, project_category, field_name, old_value, new_value)
            VALUES
                (" . db_param() . ', ' . db_param() . ', ' . db_param() . ', ' .  db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . db_param() . ');';
    db_query_bound( $query, array( $t_user_id, $p_id, db_now(), TEMPLATE_CATEGORY_CHANGED, $t_project_category, $p_field_name , $p_old_value, $p_new_value ) );
}

/**
 * Determine differences between two given template/category records and log them
 *
 * @param int $p_id Template record id
 * @param int $p_category_id Category record id
 * @param mixed $p_old_record Template/category object representing the old record
 * @param mixed $p_new_record Template/category object representing the new record
 * @return void
 */
function template_category_log_changes( $p_id, $p_category_id, $p_old_record, $p_new_record ) {
    if ( $p_old_record->frequency_id != $p_new_record->frequency_id ) {
        template_category_log_event( $p_id, $p_category_id, 'frequency', $p_old_record->frequency_id, $p_new_record->frequency_id );
    }

    if ( $p_old_record->user_id != $p_new_record->user_id ) {
        template_category_log_event( $p_id, $p_category_id, 'assigned_to', $p_old_record->user_id, $p_new_record->user_id );
    }
}

/**
 * Get template history
 *
 * @param int $p_id Template record id
 * @return mixed Array of template history records
 */
function template_get_history( $p_id ) {
    $t_template_history_table = plugin_table( 'template_history' );
    $t_template_category_table = plugin_table( 'template_category' );
    $t_template_category_history_table = plugin_table( 'tmplt_cat_hist' );
    $t_frequency_table = plugin_table( 'frequency' );
    $t_user_table = db_get_table( 'mantis_user_table' );

    $query = "SELECT
                TH.date_modified,
                TH.user_id,
                TH.type,
                TH.field_name,
                TH.old_value,
                TH.new_value,
                NULL AS project_category
            FROM $t_template_history_table AS TH
            WHERE TH.template_id = " . db_param() . "

            UNION ALL

            SELECT
                TCH.date_modified,
                TCH.user_id,
                TCH.type,
                TCH.field_name,
                IF(TCH.field_name = 'frequency', OF.name, OU.username) AS old_value,
                IF(TCH.field_name = 'frequency', NF.name, NU.username) AS new_value,
                TCH.project_category
            FROM $t_template_category_history_table AS TCH
            LEFT JOIN $t_frequency_table AS OF
                ON OF.id = TCH.old_value
            LEFT JOIN $t_frequency_table AS NF
                ON NF.id = TCH.new_value
            LEFT JOIN $t_user_table AS OU
                ON OU.id = TCH.old_value
            LEFT JOIN $t_user_table AS NU
                ON NU.id = TCH.new_value
            WHERE TCH.template_id = " . db_param() . "
            ORDER BY
              date_modified;";
    $result = db_query_bound( $query, array( $p_id, $p_id ) );

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
