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

/**
 * MantisScheduledTickets plugin
 */
class MantisScheduledTicketsPlugin extends MantisPlugin {
    /**
     * Register plugin
     *
     * @return void
     */
    function register() {
        $this->name = 'Mantis Scheduled Tickets';
        $this->description = 'Create tickets automatically, based on definable frequencies';
        $this->version = '0.3.1';
        $this->page = 'manage_configuration_edit_page';

        $this->requires = array(
                'MantisCore' => '1.2.0'
            );

        $this->author = 'MantisScheduledTickets Team';
        $this->contact = 'support@mantis-scheduled-tickets.net';
        $this->url = 'http://www.mantis-scheduled-tickets.net';
    }

    /**
     * Tasks to perform when the plugin is installed
     *
     * @return void
     */
    function install() {
        require_once 'core/frequency_api.php';
        require_once 'core/cron_api.php';
        require_once 'core/core_api.php';

        $t_auto_reporter_id = user_get_id_by_name( 'auto_reporter' );

        # create an 'auto_reporter' account if one doesn't already exist
        if( false == $t_auto_reporter_id ) {
            $t_password = auth_generate_random_password( $t_seed );
            $t_email = 'auto_reporter@localhost';
            $t_access_level = null;  # allow the default logic to kick in
            $t_protected = true;
            $t_enabled = true;

            user_create( 'auto_reporter', $t_password, $t_email, $t_access_level, $t_protected, $t_enabled );
        }

        # re-generate the crontab file (if the plugin was previously installed and records already exist in the plugin tables)
        if( st_tables_exist() ) {
            cron_regenerate_crontab_file();
        }

        return true;
    }

    /**
     * Uninstall plugin
     *
     * @return void
     */
    function uninstall() {
        require_once 'core/cron_api.php';

        cron_uninstall_plugin();
    }

    /**
     * Initialize database schema
     *
     * @return void
     */
    function schema() {
        $t_frequency_table = plugin_table( 'frequency' );
        $t_frequency_history_table = plugin_table( 'frequency_history' );
        $t_template_table = plugin_table( 'template' );
        $t_template_history_table = plugin_table( 'template_history' );
        $t_template_category_table = plugin_table( 'template_category' );
        $t_template_category_history_table = plugin_table( 'tmplt_cat_hist' );
        $t_bug_history_table = plugin_table( 'bug_history' );

        return array(
            array( 'CreateTableSQL', array( $t_frequency_table, "
                id                    I NOTNULL UNSIGNED AUTOINCREMENT PRIMARY,
                name                  C(100) NOTNULL,
                enabled               L NOTNULL DEFAULT 1,
                minute                C(100) NOTNULL DEFAULT \" '*' \",
                hour                  C(100) NOTNULL DEFAULT \" '*' \",
                day_of_month          C(200) NOTNULL DEFAULT \" '*' \",
                month                 C(50) NOTNULL DEFAULT \" '*' \",
                day_of_week           C(50) NOTNULL DEFAULT \" '*' \"
            " ) ),
            array( 'CreateIndexSQL', array( 'IX_frequency_enabled', $t_frequency_table, 'enabled' ) ),
            array( 'CreateIndexSQL', array( 'IX_frequency_name', $t_frequency_table, 'name' ) ),
            array( 'CreateIndexSQL', array( 'IX_frequency_cron_columns', $t_frequency_table, 'minute, hour, day_of_month, month, day_of_week' ) ),

            array( 'CreateTableSQL', array( $t_frequency_history_table, "
                id                    I NOTNULL UNSIGNED AUTOINCREMENT PRIMARY,
                user_id               I NOTNULL,
                frequency_id          I NOTNULL,
                date_modified         I NOTNULL,
                type                  I NOTNULL,
                field_name            C(64) NULL,
                old_value             C(255) NULL,
                new_value             C(255) NULL
            " ) ),
            array( 'CreateIndexSQL', array( 'IX_frequency_history_frequency_id_date_modified', $t_frequency_history_table, 'frequency_id, date_modified' ) ),

            array( 'CreateTableSQL', array( $t_template_table, "
                id                    I NOTNULL UNSIGNED AUTOINCREMENT PRIMARY,
                summary               C(128) NOTNULL,
                description           X NOTNULL,
                enabled               L NOTNULL DEFAULT 1
            " ) ),
            array( 'CreateIndexSQL', array( 'IX_template_enabled', $t_template_table, 'enabled' ) ),
            array( 'CreateIndexSQL', array( 'IX_template_summary', $t_template_table, 'summary' ) ),

            array( 'CreateTableSQL', array( $t_template_history_table, "
                id                    I NOTNULL UNSIGNED AUTOINCREMENT PRIMARY,
                user_id               I NOTNULL,
                template_id           I NOTNULL,
                date_modified         I NOTNULL,
                type                  I NOTNULL,
                field_name            C(64) NULL,
                old_value             C(255) NULL,
                new_value             C(255) NULL
            " ) ),
            array( 'CreateIndexSQL', array( 'IX_template_history_template_id_date_modified', $t_template_history_table, 'template_id, date_modified' ) ),

            array( 'CreateTableSQL', array( $t_template_category_table , "
                id                    I NOTNULL UNSIGNED AUTOINCREMENT PRIMARY,
                template_id           I NOTNULL,
                project_id            I NOTNULL,
                category_id           I NOTNULL,
                user_id               I NOTNULL,
                frequency_id          I NOTNULL
            " ) ),
            array( 'CreateIndexSQL', array( 'IX_template_category_frequency_id', $t_template_category_table, 'frequency_id' ) ),
            array( 'CreateIndexSQL', array( 'IX_template_category_template_id_category_id', $t_template_category_table, 'template_id, category_id' ) ),

            array( 'CreateTableSQL', array( $t_template_category_history_table, "
                id                    I NOTNULL UNSIGNED AUTOINCREMENT PRIMARY,
                user_id               I NOTNULL,
                template_id           I NOTNULL,
                project_category      C(259),
                date_modified         I NOTNULL,
                type                  I NOTNULL,
                field_name            C(64),
                old_value             C(255),
                new_value             C(255)
            " ) ),
            array( 'CreateIndexSQL', array( 'IX_template_category_history_template_id_date_modified', $t_template_category_history_table, 'template_id, date_modified' ) ),

            array( 'CreateTableSQL', array( $t_bug_history_table, "
                id                    I NOTNULL UNSIGNED AUTOINCREMENT PRIMARY,
                date_submitted        I NOTNULL,
                bug_id                I NULL,
                template_id           I NOTNULL,
                frequency_id          I NOTNULL,
                status_code           I1 NOTNULL
            " ) ),
            array( 'CreateIndexSQL', array( 'IX_bug_history_template_id_date_submitted', $t_bug_history_table, 'template_id, date_submitted' ) ),
            array( 'CreateIndexSQL', array( 'IX_bug_history_frequency_id_date_submitted', $t_bug_history_table, 'frequency_id, date_submitted' ) )
        );
    }

    /**
     * Default plugin configuration values
     *
     * @return void
     */
    function config() {
        global $g_administrator_email;

        return array(
            'manage_threshold' => ADMINISTRATOR,
            'email_reports_to' => $g_administrator_email,
            'auto_reporter_username' => 'auto_reporter',
            'send_email_on_successful_auto_report' => true
        );
    }

    /**
     * Register hooks (add menu items, include additional resources (JS files) etc.)
     *
     * @return void
     */
    function hooks() {
        return array(
            'EVENT_LAYOUT_RESOURCES' => 'resources',
            'EVENT_MENU_MANAGE' => 'menu_manage',
        );
    }

    /**
     * Initialize plugin
     *
     * @return void
     */
    function init() {
        require_once 'core/core_api.php';
        require_once 'core/cron_api.php';
        require_once 'core/frequency_api.php';
        require_once 'core/template_api.php';
    }

    /**
     * Register plugin resources (JS, CSS files)
     *
     * @param int $p_event Event id
     * @return void
     */
    function resources( $p_event ) {
        return
            '<script type="text/javascript" src="' . plugin_file( 'mantis_scheduled_tickets.js' ) . '"></script>' .
            '<link rel="stylesheet" type="text/css" href="' . plugin_file( 'mantis_scheduled_tickets.css' ). '" />';
    }

    /**
     * Add items to the 'Manage' menu
     *
     * @return void
     */
    function menu_manage() {
        global $g_MantisScheduledTickets_context;

        if( access_has_global_level( plugin_config_get( manage_threshold ) ) ) {
            $t_configuration_page = plugin_page( 'manage_configuration_edit_page' );
            $t_configuration_link = plugin_lang_get( 'manage_menu_link' );

            if( $g_MantisScheduledTickets_context ) {
                return array(
                    $t_configuration_link
                );
            } else {
                return array(
                    '<a href="' . string_html_specialchars( $t_configuration_page ) . '">' . $t_configuration_link . '</a>'
                );
            }
        }
    }
}
