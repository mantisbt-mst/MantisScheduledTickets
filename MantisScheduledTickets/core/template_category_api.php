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

/**
 * TemplateCategory class
 */
class TemplateCategory {
    /**
     * Project id
     */
    protected $project_id = null;

    /**
     * Category id
     */
    protected $category_id = null;

    /**
     * Frequency record id
     */
    protected $frequency_id = null;

    /**
     * User record id
     */
    protected $user_id = null;

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
 * Get template/categories for a given frequency
 *
 * NOTE: When calling this function, the values provided via $p_filter
 * MUST have already been cleansed (i.e. the value in the array should
 * be the result of the appropriate db_prepare_* function).
 *
 * @param int $p_filter Filter array
 * @return mixed Array of templates/categories
 */
function template_category_get_all( $p_filter ) {
    $t_template_table = plugin_table( 'template' );
    $t_template_category_table = plugin_table( 'template_category' );
    $t_frequency_table = plugin_table( 'frequency' );
    $t_category_table = db_get_table( 'mantis_category_table' );
    $t_project_table = db_get_table( 'mantis_project_table' );
    $t_user_table = db_get_table( 'mantis_user_table' );

    $query = "SELECT
                TC.id AS template_category_id,
                T.id AS template_id,
                T.summary,
                T.description,
                TC.category_id,
                IF(C.id IS NULL, 1, 0) AS invalid_category,
                C.name AS category_name,
                TC.project_id,
                IF(P.id IS NULL, 1, 0) AS invalid_project,
                P.name AS project_name,
                TC.frequency_id,
                F.name AS frequency_name,
                F.enabled AS frequency_enabled,
                TC.user_id,
                IF(TC.user_id != 0 AND U.id IS NULL, 1, 0) AS invalid_user,
                T.command,
                CASE
                    WHEN T.command != '' THEN 1
                    ELSE 0
                END AS has_command,
                T.diff_flag
            FROM $t_template_category_table AS TC
            JOIN $t_template_table AS T
                ON T.id = TC.template_id
            LEFT JOIN $t_category_table AS C
                ON C.id = TC.category_id
            LEFT JOIN $t_project_table AS P
                ON P.id = TC.project_id
            LEFT JOIN $t_frequency_table AS F
                ON F.id = TC.frequency_id
            LEFT JOIN $t_user_table AS U
                ON U.id = TC.user_id";

    $t_values = null;
    if( is_array( $p_filter ) ) {
        foreach( $p_filter as $t_field_name => $t_field_value ) {
            $t_where_clause[] .= " $t_field_name = " . db_param();
            $t_values[] = $t_field_value;
        }

        if( $t_where_clause ) {
            $query .= " WHERE " . join( ' AND ', $t_where_clause );
        }
    }

    $query .= "
            ORDER BY
                P.name,
                C.name,
                TC.id;";

    # run the query
    $result = db_query_bound( $query, $t_values );

    $t_row_count = db_num_rows( $result );
    $t_template_categories = array();

    for( $i = 0; $i < $t_row_count; $i++ ) {
        array_push( $t_template_categories, db_fetch_array( $result ) );
    }

    return $t_template_categories;
}

/**
 * Get a single template category record by the specified id
 *
 * @param int $p_template_category_id Template category record id
 * @return mixed Associative array containing a single template category record
 */
function template_category_get_row( $p_template_category_id ) {
    $t_filter = array( 'TC.id' => db_prepare_int( $p_template_category_id ) );
    $t_template_categories = template_category_get_all( $t_filter );

    if( false == is_array( $t_template_categories ) ) {
        plugin_error( plugin_lang_get( 'error_template_category_not_found' ), ERROR );
    }

    return $t_template_categories[0];
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
    $t_template_category_table = plugin_table( 'template_category' );

    $c_template_id = db_prepare_int( $p_template_id );
    $c_project_id = db_prepare_int( $p_project_id );
    $c_category_id = db_prepare_int( $p_category_id );
    $c_frequency_id = db_prepare_int( $p_frequency_id );
    $c_user_id = db_prepare_int( $p_user_id );

    $query = "INSERT $t_template_category_table
                (template_id, project_id, category_id, user_id, frequency_id)
            VALUES
                (" . db_param() . ", " . db_param(). ", " . db_param() . ", " . db_param() . ", " . db_param() . ");";
    db_query_bound( $query, array( $c_template_id, $c_project_id, $c_category_id, $c_user_id, $c_frequency_id ) );

    return db_insert_id( $t_template_category_table );
}

/**
 * Update a template/category record with new frequency and/or user id
 *
 * @param int $p_template_category_id Template/category record id
 * @param int $p_project_id Project record id
 * @param int $p_category_id Category record id
 * @param int $p_frequency_id Frequency id
 * @param int $p_user_id User id
 * @return void
 */
function template_category_update( $p_template_category_id, $p_project_id, $p_category_id, $p_frequency_id, $p_user_id ) {
    $t_template_category_table = plugin_table( 'template_category' );

    $c_template_category_id = db_prepare_int( $p_template_category_id );
    $c_project_id = db_prepare_int( $p_project_id );
    $c_category_id = db_prepare_int( $p_category_id );
    $c_frequency_id = db_prepare_int( $p_frequency_id );
    $c_user_id = db_prepare_int( $p_user_id );

    $query = "UPDATE $t_template_category_table
            SET
                project_id = " . db_param() . ",
                category_id = " . db_param() . ",
                frequency_id = " . db_param() . ",
                user_id = " . db_param() . "
            WHERE id = " . db_param() . ";";
    db_query_bound( $query, array( $c_project_id, $c_category_id, $c_frequency_id, $c_user_id, $c_template_category_id ) );
}

/**
 * Disassociate a given project/category from the given template
 *
 * @param int $p_template_category_id Template/category record id
 * @return void
 */
function template_category_delete( $p_template_category_id ) {
    $t_template_category_table = plugin_table( 'template_category' );

    command_argument_delete( array( 'template_category_id' => db_prepare_int ($p_template_category_id ) ) );

    $c_template_category_id = db_prepare_int( $p_template_category_id );

    $query = "DELETE FROM $t_template_category_table WHERE id = " . db_param() . ";";
    db_query_bound( $query, array( $c_template_category_id ) );
}

/**
 * Get the id of the previous execution's command output note id
 *
 * @param int $p_template_category_id Template category record id
 * @return mixed Array containing the id, as well as the actual text,
 * of the previous execution's command output note
 */
function template_category_get_previous_bugnote( $p_template_category_id ) {
    $t_bugnote_text_table = db_get_table( 'mantis_bugnote_text_table' );
    $t_bug_history_table = plugin_table( 'bug_history' );

    $c_template_category_id = db_prepare_int( $p_template_category_id );

    $query = "SELECT
                BNT.id AS note_id,
                BNT.note AS note_text
            FROM $t_bugnote_text_table AS BNT
            WHERE BNT.id =
                (
                    SELECT
                        BH.command_output_note_id
                    FROM $t_bug_history_table AS BH
                    WHERE BH.template_category_id = " . db_param() . "
                    ORDER BY
                        BH.date_submitted DESC
                    LIMIT 1
                )";
    $result = db_query_bound( $query, array( $c_template_category_id ) );

    if( 0 == db_num_rows( $result ) ) {
        return null;
    }

    $t_bugnote = db_fetch_array( $result );

    return array( 'note_id' => $t_bugnote['note_id'], 'note_text' => $t_bugnote['note_text'] );
}

/**
 * Log template/category event (add, delete)
 *
 * @param int $p_template_id Template record id
 * @param int $p_template_category_id Template category id
 * @param int $p_event_type Event type
 * @return void
 */
function template_category_log_event_special( $p_template_id, $p_template_category_id, $p_event_type ) {

    $t_template_category_history_table = plugin_table( 'tmplt_cat_hist' );
    $t_user_id = auth_get_current_user_id();

    $c_template_id = db_prepare_int( $p_template_id );
    $c_template_category_id = db_prepare_int( $p_template_category_id );

    $query = "INSERT $t_template_category_history_table
            (
                user_id,
                template_id,
                template_category_id,
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
    db_query_bound( $query, array( $t_user_id, $c_template_id, $c_template_category_id, db_now(), $p_event_type ) );
}

/**
 * Log template/category change event
 *
 * Record the actual changes made (old/new values)
 *
 * @param int $p_template_id Template record id
 * @param int $p_template_category_id Template category id
 * @param string $p_field_name Field name
 * @param mixed $p_old_value Old field value
 * @param mixed $p_new_value New field value
 * @return void
 */
function template_category_log_event( $p_template_id, $p_template_category_id, $p_field_name, $p_old_value, $p_new_value ) {
    $t_template_category_history_table = plugin_table( 'tmplt_cat_hist' );
    $t_user_id = auth_get_current_user_id();

    $c_template_id = db_prepare_int( $p_template_id );
    $c_template_category_id = db_prepare_int( $p_template_category_id );

    $query = "INSERT $t_template_category_history_table
            (
                user_id,
                template_id,
                template_category_id,
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
                db_param() . ', ' .
                db_param() .
            ');';
    db_query_bound(
        $query,
        array(
            $t_user_id,
            $c_template_id,
            $c_template_category_id,
            db_now(),
            MST_TEMPLATE_CATEGORY_CHANGED,
            $p_field_name,
            $p_old_value,
            $p_new_value
        )
    );
}

/**
 * Determine differences between two given template/category records and log them
 *
 * @param int $p_template_id Template record id
 * @param int $p_template_category_id Template category id
 * @param mixed $p_old_record Template/category object representing the old record
 * @param mixed $p_new_record Template/category object representing the new record
 * @return void
 */
function template_category_log_changes( $p_template_id, $p_template_category_id, $p_old_record, $p_new_record ) {
    if ( $p_old_record->project_id != $p_new_record->project_id ) {
        template_category_log_event(
            $p_template_id,
            $p_template_category_id,
            'project',
            sprintf( plugin_lang_get( 'history_field' ), project_get_name( $p_old_record->project_id ), $p_old_record->project_id ),
            sprintf( plugin_lang_get( 'history_field' ), project_get_name( $p_new_record->project_id ), $p_new_record->project_id )
        );
    }

    if ( $p_old_record->category_id != $p_new_record->category_id ) {
        template_category_log_event(
            $p_template_id,
            $p_template_category_id,
            'category',
            sprintf( plugin_lang_get( 'history_field' ), category_get_name( $p_old_record->category_id ), $p_old_record->category_id ),
            sprintf( plugin_lang_get( 'history_field' ), category_get_name( $p_new_record->category_id ), $p_new_record->category_id )
        );
    }

    if ( $p_old_record->frequency_id != $p_new_record->frequency_id ) {
        template_category_log_event(
            $p_template_id,
            $p_template_category_id,
            'frequency',
            sprintf( plugin_lang_get( 'history_field' ), frequency_get_name( $p_old_record->frequency_id ), $p_old_record->frequency_id ),
            sprintf( plugin_lang_get( 'history_field' ), frequency_get_name( $p_new_record->frequency_id ), $p_new_record->frequency_id )
        );
    }

    if ( $p_old_record->user_id != $p_new_record->user_id ) {
        template_category_log_event(
            $p_template_id,
            $p_template_category_id,
            'assigned_to',
            sprintf( plugin_lang_get( 'history_field' ), user_get_name( $p_old_record->user_id ), $p_old_record->user_id ),
            sprintf( plugin_lang_get( 'history_field' ), user_get_name( $p_new_record->user_id ), $p_new_record->user_id )
        );
    }
}

/**
 * Get template category history
 *
 * @param int $p_template_category_id Template record id
 * @return mixed Array of template category history records
 */
function template_category_get_history( $p_template_category_id ) {
    $t_template_category_history_table = plugin_table( 'tmplt_cat_hist' );

    $c_template_category_id = db_prepare_int( $p_template_category_id );

    $query = "SELECT
                TCH.date_modified,
                TCH.user_id,
                TCH.type,
                TCH.field_name,
                TCH.old_value,
                TCH.new_value
            FROM $t_template_category_history_table AS TCH
            WHERE TCH.template_category_id = " . db_param() . "
            ORDER BY
                date_modified,
                type;";
    $result = db_query_bound( $query, array( $c_template_category_id ) );

    $t_row_count = db_num_rows( $result );
    $t_history = array();

    for( $i = 0; $i < $t_row_count; $i++ ) {
        array_push( $t_history, db_fetch_array( $result ) );
    }

    return $t_history;
}
