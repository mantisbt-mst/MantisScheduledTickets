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

    # validate auto_reporter account
    $t_auto_reporter_username = plugin_config_get( 'auto_reporter_username' );
    $t_auto_reporter_id = user_get_id_by_name( $t_auto_reporter_username );

    if( false === $t_auto_reporter_id ) {
        $t_auto_reporter_ok = false;
    } else {
        $t_auto_reporter_ok = true;
    }

    # validate crontab file against the database
    $t_crontab_file_ok = cron_validate_crontab_file( false );

    # generate email message
    $t_body = plugin_lang_get( 'email_validation_report' ) . PHP_EOL . PHP_EOL;
    $t_body .= plugin_lang_get( 'config_auto_reporter_username' ) . ': ' . ( $t_auto_reporter_ok ? plugin_lang_get( 'config_ok' ) : plugin_lang_get( 'config_not_ok' ) ) . PHP_EOL;
    $t_body .= plugin_lang_get( 'crontab_file' ) . ': ' . ( $t_crontab_file_ok ? plugin_lang_get( 'config_ok' ) : plugin_lang_get( 'config_not_ok' ) ) . PHP_EOL;

    mst_email_send( plugin_lang_get( 'email_subject_validation' ), $t_body );
