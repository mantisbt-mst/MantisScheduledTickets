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

    $t_frequency_id = gpc_get_int( 'id' );
    $t_manage_frequency_page = plugin_page( 'manage_frequency_page', true );

    helper_ensure_confirmed( plugin_lang_get( 'frequency_delete_sure_msg' ), plugin_lang_get( 'frequency_delete' ) );

    frequency_delete( $t_frequency_id );
    frequency_log_event_special( $t_frequency_id, FREQUENCY_DELETED );
    cron_regenerate_crontab_file();
    cron_validate_crontab_file();

    html_page_top( null, $t_manage_frequency_page );

?>

<br />
<div align="center">
<?php
	echo lang_get( 'operation_successful' ) . '<br />';
	print_bracket_link( $t_manage_frequency_page, lang_get( 'proceed' ) );
?>
</div>

<?php
	html_page_bottom();
