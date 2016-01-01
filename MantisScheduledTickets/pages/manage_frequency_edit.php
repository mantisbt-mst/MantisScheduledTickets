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

    access_ensure_global_level( plugin_config_get( 'manage_threshold' ) );

    form_security_validate( 'manage_frequency_edit' );

    $f_id = gpc_get_int( 'frequency_id' );
    $f_name = gpc_get_string( 'name' );
    $f_enabled = gpc_get_bool( 'enabled', false );
    $f_minute = gpc_get_string( 'minute' );
    $f_minute_select = gpc_get_string_array( 'minute_select', '' );
    $f_hour = gpc_get_string( 'hour' );
    $f_hour_select = gpc_get_string_array( 'hour_select', '' );
    $f_day_of_month = gpc_get_string( 'day_of_month' );
    $f_day_of_month_select = gpc_get_string_array( 'day_of_month_select', '' );
    $f_month = gpc_get_string( 'month' );
    $f_month_select = gpc_get_string_array( 'month_select', '' );
    $f_day_of_week = gpc_get_string( 'day_of_week' );
    $f_day_of_week_select = gpc_get( 'day_of_week_select', '' );

    if( 'all' == $f_minute ) {
        $t_minute = '*';
    } else {
        $t_minute = join( ',', $f_minute_select );
    }

    if( 'all' == $f_hour ) {
        $t_hour = '*';
    } else {
        $t_hour = join( ',', $f_hour_select );
    }

    if( 'all' == $f_day_of_month ) {
        $t_day_of_month = '*';
    } else {
        $t_day_of_month = join( ',', $f_day_of_month_select );
    }

    if( 'all' == $f_month ) {
        $t_month = '*';
    } else {
        $t_month = join( ',', $f_month_select );
    }

    if( 'all' == $f_day_of_week ) {
        $t_day_of_week = '*';
    } else {
        $t_day_of_week = join( ',', $f_day_of_week_select );
    }

    if( ( 'all' == $f_minute ) || ( 'all' == $f_hour ) ) {
        helper_ensure_confirmed( plugin_lang_get( 'frequency_dangerous_sure_msg' ), plugin_lang_get( 'frequency_dangerous_proceed' ) );
    }

    $t_old_record = new Frequency;
    $t_old_record->name = gpc_get_string( 'old_name' );
    $t_old_record->enabled = gpc_get_bool( 'old_enabled' );
    $t_old_record->minute = gpc_get_string( 'old_minute' );
    $t_old_record->hour = gpc_get_string( 'old_hour' );
    $t_old_record->day_of_month = gpc_get_string( 'old_day_of_month' );
    $t_old_record->month = gpc_get_string( 'old_month' );
    $t_old_record->day_of_week = gpc_get_string( 'old_day_of_week' );

    $t_new_record = new Frequency;
    $t_new_record->name = $f_name;
    $t_new_record->enabled = $f_enabled;
    $t_new_record->minute = $t_minute;
    $t_new_record->hour = $t_hour;
    $t_new_record->day_of_month = $t_day_of_month;
    $t_new_record->month = $t_month;
    $t_new_record->day_of_week = $t_day_of_week;


    # update frequency record
    frequency_update( $f_id, $f_name, $f_enabled, $t_minute, $t_hour, $t_day_of_month, $t_month, $t_day_of_week );
    frequency_log_changes( $f_id, $t_old_record, $t_new_record );
    cron_regenerate_crontab_file();
    cron_validate_crontab_file();

    form_security_purge( 'manage_frequency_edit' );

    $t_redirect_url = plugin_page( 'manage_frequency_page', true );

    html_page_top( null, $t_redirect_url );

?>

<br />
<div align="center">
<?php
	echo lang_get( 'operation_successful' ) . '<br />';
	print_bracket_link( $t_redirect_url, lang_get( 'proceed' ) );
?>
</div>

<?php
	html_page_bottom();
