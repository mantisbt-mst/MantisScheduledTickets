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

    # validate auto_reporter account
    $t_auto_reporter_username = plugin_config_get( 'auto_reporter_username' );
    $t_auto_reporter_id = user_get_id_by_name( $t_auto_reporter_username );
    $t_check_for_updates = plugin_config_get( 'check_for_updates' );
    $t_validation_failed = false;

    if( $t_check_for_updates ) {
        $t_installed_version = $g_plugin_cache[plugin_get_current()]->version;
        $t_latest_version = mst_core_get_latest_plugin_version();

        $t_update_field = ( $t_installed_version != $t_latest_version ) ?
            plugin_lang_get( 'update_available_yes' ) :
            plugin_lang_get( 'update_available_no' );
    } else {
        $t_update_field = plugin_lang_get( 'update_disabled' );
    }

    # ensure that we have a valid auto_reporter username
    if( false === $t_auto_reporter_id ) {
        $t_auto_reporter_ok = false;
        $t_validation_failed = true;
    } else {
        $t_auto_reporter_ok = true;
    }

    # validate crontab file against the database
    $t_crontab_file_ok = cron_validate_crontab_file( false );
    $t_validation_failed |= ( false == $t_crontab_file_ok );

    # ensure that templates are associated with valid commands
    $t_templates = template_get_all();
    $t_valid_commands = mst_helper_get_valid_commands();
    $t_invalid_template_count = 0;
    if( is_array( $t_templates ) ) {
        foreach( $t_templates as $t_template ) {
            if( false == in_array( $t_template['command'], $t_valid_commands ) ) {
                $t_validation_failed = true;
                $t_invalid_template_count++;
            }
        }
    }

    # generate email message
    $t_body = plugin_lang_get( 'email_validation_report' ) . PHP_EOL . PHP_EOL;
    $t_body .= plugin_lang_get( 'config_auto_reporter_username' ) . ': ' .
        ( $t_auto_reporter_ok ? plugin_lang_get( 'config_ok' ) : plugin_lang_get( 'config_not_ok' ) ) . PHP_EOL;
    $t_body .= plugin_lang_get( 'crontab_file' ) . ': ' .
        ( $t_crontab_file_ok ? plugin_lang_get( 'config_ok' ) : plugin_lang_get( 'config_not_ok' ) ) . PHP_EOL;
    $t_body .= plugin_lang_get( 'email_invalid_templates' ) . ' ' . $t_invalid_template_count . PHP_EOL;
    $t_body .= plugin_lang_get( 'config_check_for_updates' ) . ': ' .
        ( $t_check_for_updates ? plugin_lang_get( 'config_option_enabled' ) : plugin_lang_get( 'config_option_disabled' ) ) . PHP_EOL;

    if( $t_check_for_updates ) {
        $t_body .= plugin_lang_get( 'config_installed_version' ) . ': ' . $t_installed_version . PHP_EOL;
        $t_body .= plugin_lang_get( 'config_latest_version' ) . ': ' . $t_latest_version . PHP_EOL;
    }

    mst_core_email_send(
        sprintf(
            plugin_lang_get( 'email_subject_validation' ),
            ( $t_validation_failed ) ?
                plugin_lang_get( 'validation_successful_no' ) :
                plugin_lang_get( 'validation_successful_yes' ),
            $t_update_field
        ),
        $t_body
    );

    echo $t_body;
