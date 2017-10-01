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

define( 'MST_CRONTAB_COMMENT_START', '### MantisScheduledTickets - start' );
define( 'MST_CRONTAB_COMMENT_END', '### MantisScheduledTickets - end' );
define( 'MST_CRONTAB_COMMAND_OPTIONS', '-q --secure-protocol=auto --no-check-certificate' );
define( 'MST_CRONTAB_VALIDATION_SCHEDULE', '0 0 * * *' );
define( 'MST_CRONTAB_COMMAND', 'wget' );

/**
 * Regenerate the crontab file for the current user
 *
 * This function reads the contents of the current crontab file
 * (output of 'crontab -l'), preserves entries NOT related to the
 * MantisScheduledTickets plugin, creates new entries for all frequency
 * records, then overwrites the contents of the crontab file.
 *
 * @return void
 */
function cron_regenerate_crontab_file() {
    $t_crontab_file = trim( cron_get_non_plugin_entries( cron_get_crontab_file() ) );
    $t_crontab_file .= PHP_EOL . PHP_EOL . MST_CRONTAB_COMMENT_START . PHP_EOL;
    $t_cron_bug_report_command = cron_get_bug_report_command();

    $t_frequencies = frequency_get_for_crontab();

    if( is_array( $t_frequencies ) ) {
        foreach( $t_frequencies as $t_frequency ) {
            $t_crontab_file .= $t_frequency['schedule'] . ' ' . $t_cron_bug_report_command . $t_frequency['id'] . PHP_EOL;
        }
    }

    $t_crontab_file .= MST_CRONTAB_VALIDATION_SCHEDULE . ' ' . cron_get_validation_command() . PHP_EOL;
    $t_crontab_file .= MST_CRONTAB_COMMENT_END;

    # escape double quotes...
    $t_crontab_file = str_replace( '"', '"\""', $t_crontab_file );

    # write the new cron file
    shell_exec( "echo \"$t_crontab_file\" | crontab -" );
}

/**
 * Remove plugin-related entries
 *
 * This function removes plugin-related entries in the crontab file
 * (used when uninstalling the plugin)
 *
 * @return void
 */
function cron_uninstall_plugin() {
    $t_crontab_file = trim( cron_get_non_plugin_entries( cron_get_crontab_file() ) );

    # escape double quotes...
    $t_crontab_file = str_replace( '"', '"\""', $t_crontab_file );

    # write the new cron file
    shell_exec( "echo \"$t_crontab_file\" | crontab -" );
}

/**
 * Validate the crontab file
 *
 * This function reads the contents of the current crontab file
 * (output of 'crontab -l'), preserves ONLY entries related to
 * frequency records, then compares this against the database records,
 * to ensure that everything matches (minute, hour, day of month etc.)
 *
 * @param bool $p_interactive Flag that indicates whether validation is performed in the context of a user session or not
 * @return bool True when the crontab file matches the database records, false otherwise
 */
function cron_validate_crontab_file( $p_interactive = true ) {
    $t_crontab_file = cron_get_plugin_entries( cron_get_crontab_file() );
    $t_frequencies = frequency_get_for_crontab();

    # attempt to match crontab entries to enabled frequency records
    if( is_array( $t_crontab_file ) ) {
        foreach( $t_crontab_file as $t_job_idx => $t_job ) {
            if( is_array( $t_frequencies ) ) {
                foreach( $t_frequencies as $t_frequency_idx => $t_frequency ) {
                    if( $t_job['id'] == $t_frequency['id'] ) {
                        $t_crontab_file[$t_job_idx]['matched'] = 1;
                        $t_frequencies[$t_frequency_idx]['matched'] = 1;
                        break;
                    }
                }
            }
        }
    }

    # check if there are any unmatched jobs/frequencies
    if( is_array( $t_crontab_file ) ) {
        foreach( $t_crontab_file as $t_job ) {
            if( 0 == $t_job['matched'] ) {
                if( $p_interactive ) {
                    error_parameters( plugin_lang_get( 'error_invalid_crontab_file' ), plugin_lang_get ( 'title' ) );
                    trigger_error( ERROR_PLUGIN_GENERIC, ERROR );
                }

                return false;
            }
        }
    }

    if( is_array( $t_frequencies ) ) {
        foreach( $t_frequencies as $t_frequency ) {
            if( 0 == $t_frequency['matched'] ) {
                if( $p_interactive ) {
                    error_parameters( plugin_lang_get( 'error_invalid_crontab_file' ), plugin_lang_get ( 'title' ) );
                    trigger_error( ERROR_PLUGIN_GENERIC, ERROR );
                }

                return false;
            }
        }
    }

    return true;
}

/**
 * Get the entire contents of the crontab file
 *
 * Get the contents of the current user's crontab file ('crontab -l')
 *
 * @return string The contents of the current user's crontab file
 */
function cron_get_crontab_file() {
    return shell_exec( 'crontab -l' );
}

/**
 * Get all the entries in the crontab files corresponding to frequency records
 *
 * @param string $p_crontab_file crontab file
 * @return mixed Array of plugin-related crontab entries
 */
function cron_get_plugin_entries( $p_crontab_file ) {
    $t_lines = explode( PHP_EOL, $p_crontab_file );
    $t_jobs = array();
    $t_cron_bug_report_command = cron_get_bug_report_command();

    if( is_array( $t_lines ) ) {
        foreach( $t_lines as $t_line ) {
            if( strpos( $t_line, $t_cron_bug_report_command ) ) {
                // get rid of the command and only keep the frequency id
                $t_components = explode( ' ', str_replace( '"', '', str_replace( $t_cron_bug_report_command, '', $t_line ) ) );
                $t_jobs[$t_components[5]] = array(
                    'id' => $t_components[5],
                    'schedule' => $t_components[0] . ' ' .
                        $t_components[1] . ' ' .
                        $t_components[2] . ' ' .
                        $t_components[3] . ' ' .
                        $t_components[4] . ' ',
                    'matched' => 0 );
            }
        }
    }

    return $t_jobs;
}

/**
 * Get all the entries in the crontab file NOT related to the MantisScheduledTickets plugin
 *
 * @param string $p_crontab_file crontab file
 * @return mixed Array of NON-plugin-related crontab entries
 */
function cron_get_non_plugin_entries( $p_crontab_file ) {
    $t_lines = explode( PHP_EOL, $p_crontab_file );
    $t_new_file = array();
    $t_cron_bug_report_command = cron_get_bug_report_command();
    $t_cron_validate_command = cron_get_validation_command();

    if( is_array( $t_lines ) ) {
        foreach( $t_lines as $t_line ) {
            if( false === strpos( $t_line, MST_CRONTAB_COMMENT_START ) &&
                false === strpos( $t_line, MST_CRONTAB_COMMENT_END ) &&
                false === strpos( $t_line, $t_cron_bug_report_command ) &&
                false === strpos( $t_line, $t_cron_validate_command ) ) {
                $t_new_file[] = $t_line;
            }
        }
    }

    return implode( PHP_EOL, $t_new_file );
}

/**
 * Get a crontab command
 *
 * @param string $p_script_name Script name
 * @return string Crontab command
 */
function cron_get_command( $p_script_name ) {
    global $g_path;

    return MST_CRONTAB_COMMAND . ' ' . MST_CRONTAB_COMMAND_OPTIONS . ' ' .
        cron_get_url( $g_path, plugin_config_get( 'crontab_base_url' ) ) .
        str_replace( '&', '\&', plugin_page( $p_script_name, true ) );
}

/**
 * Get the correct URL for use in the crontab file
 *
 * @param string Base Mantis URL
 * @param string $p_crontab_base_url Crontab base URL
 * @return string URL to use in the crontab file
 */
function cron_get_url( $p_path, $p_crontab_base_url ) {
    $t_path = $p_path;

    preg_match( '/(https?:\/\/)([^:\/]*)(:\d+)?(\/.*)?/', $t_path, $t_matches );

    if( is_array( $t_matches ) ) {
        # replace the first match (the original input string) with the crontab base URL
        $t_matches[0] = $p_crontab_base_url;

        # blank out matches 1, 2, 3 (the protocol, host and port matches)
        unset( $t_matches[1] );
        unset( $t_matches[2] );
        unset( $t_matches[3] );

        # re-assemble the URL
        $t_path = join( '', $t_matches );
    }

    return $t_path;
}

/**
 * Get the full crontab command to trigger issue creation
 *
 * @return string Bug (auto)report crontab command
 */
function cron_get_bug_report_command() {
    return cron_get_command( 'bug_report_auto.php&frequency_id=' );
}

/**
 * Get the full crontab command that performs validation
 *
 * @return string Frequency validation crontab command
 */
function cron_get_validation_command() {
    return cron_get_command( 'crontab_validate.php' );
}
