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

    access_ensure_global_level( plugin_config_get( 'manage_threshold' ) );
    $g_MantisScheduledTickets_context = true;

    html_page_top( plugin_lang_get( 'title_frequencies' ) );
    print_manage_menu();
    mst_core_print_scheduled_tickets_menu( MST_MANAGE_FREQUENCY_PAGE );

    $t_frequency_edit_page = plugin_page( 'manage_frequency_edit_page' ) . '&id=';

    $t_frequencies = frequency_get_all();
    $t_crontab_entries = cron_get_plugin_entries( cron_get_crontab_file() );

?>

<br/>

<table class="width100" cellspacing="1">
	<tr>
		<td class="form-title" colspan="7">
            <?php echo plugin_lang_get( 'title_frequencies' ); ?>
            &nbsp;
            <?php print_button( plugin_page( 'manage_frequency_add_page' ), plugin_lang_get( 'create_new_frequency_link' ) ); ?>
            &nbsp;
            <?php print_button( plugin_page( 'manage_frequency_regenerate_crontab' ), plugin_lang_get( 'regenerate_crontab_link' ) ); ?>
        </td>
	</tr>

    <tr>
        <td class="category">
            <?php echo plugin_lang_get( 'frequency_name' ); ?>
        </td>
        <td class="category">
            <?php echo plugin_lang_get( 'frequency_day_of_week' ); ?>
        </td>
        <td class="category">
            <?php echo plugin_lang_get( 'frequency_month' ); ?>
        </td>
        <td class="category">
            <?php echo plugin_lang_get( 'frequency_day_of_month' ); ?>
        </td>
        <td class="category">
            <?php echo plugin_lang_get( 'frequency_hour' ); ?>
        </td>
        <td class="category">
            <?php echo plugin_lang_get( 'frequency_minute' ); ?>
        </td>
        <td class="category">
            <?php echo plugin_lang_get( 'frequency_crontab_status' ); ?>
        </td>
        <td class="category">
            <?php echo plugin_lang_get( 'frequency_template_count' ); ?>
        </td>
        <td class="category">
            <?php echo plugin_lang_get( 'frequency_bug_count' ); ?>
        </td>
    </tr>

    <?php
    if( is_array( $t_frequencies ) ) {
        foreach( $t_frequencies as $t_frequency ) {
            $t_template_count_class = ( 0 == $t_frequency['template_count'] ) ? 'frequency_template_unassociated' : 'frequency_template_associated';
            $t_bug_count_class = ( 0 == $t_frequency['bug_count'] ) ? 'frequency_bug_unassociated' : 'frequency_bug_associated';
            $t_id = $t_frequency['id'];

            if( 0 == $t_frequency['enabled'] ) {
                $t_strike_start = '<strike>';
                $t_strike_end = '</strike>';
                $t_crontab_class = 'frequency_crontab_disabled';
                $t_crontab_status = plugin_lang_get( 'legend_frequency_crontab_disabled' );
            }
            else {
                $t_strike_start = $t_strike_end = '';

                if( array_key_exists( $t_id, $t_crontab_entries ) ) {
                    $t_crontab_class = 'frequency_crontab_ok';
                    $t_crontab_status = plugin_lang_get( 'legend_frequency_crontab_ok' );
                } else {
                    $t_crontab_class = 'frequency_crontab_not_ok';
                    $t_crontab_status = plugin_lang_get( 'legend_frequency_crontab_not_ok' );
                }
            }
        ?>
            <tr <?php echo helper_alternate_class( $i ) ?>>
                <td>
                    <a href="<?php echo $t_frequency_edit_page . $t_id; ?>" id="frequency_name_<?php echo $t_id; ?>">
                        <?php echo $t_strike_start; echo htmlspecialchars( $t_frequency['name'] ); echo $t_strike_end; ?>
                    </a>
                </td>
                <td id="day_of_week_<?php echo $t_id; ?>"><?php echo mst_helper_day_of_week_column( $t_frequency['day_of_week'] ); ?></td>
                <td id="month_<?php echo $t_id; ?>"><?php echo mst_helper_month_column( $t_frequency['month'] ); ?></td>
                <td id="day_of_month_<?php echo $t_id; ?>"><?php echo mst_helper_day_of_month_column( $t_frequency['day_of_month'] ); ?></td>
                <td id="hour_<?php echo $t_id; ?>"><?php echo mst_helper_hour_column( $t_frequency['hour'] ); ?></td>
                <td id="minute_<?php echo $t_id; ?>"><?php echo mst_helper_minute_column( $t_frequency['minute'] ); ?></td>
                <td id="crontab_status_<?php echo $t_id; ?>" class="<?php echo $t_crontab_class; ?>"><?php echo $t_crontab_status; ?></td>
                <td id="template_count_<?php echo $t_id; ?>" class="<?php echo $t_template_count_class; ?>"><?php echo $t_frequency['template_count']; ?></td>
                <td id="bug_count_<?php echo $t_id; ?>" class="<?php echo $t_bug_count_class; ?>"><?php echo $t_frequency['bug_count']; ?></td>
            </tr>
	<?php
        }
	}
	?>
</table>
<br />

<table width="100%">
    <tr>
        <td>
            <?php echo plugin_lang_get( 'frequency_template_count' ); ?>
            <table class="width100" cellspacing="1">
                <tr>
                    <td class="small-caption frequency_template_unassociated" width="50%" title="<?php echo plugin_lang_get( 'legend_title_frequency_template_unassociated' ); ?>">
                        <?php echo plugin_lang_get( 'legend_frequency_template_unassociated' ); ?>
                    </td>
                    <td class="small-caption frequency_template_associated" width="50%" title="<?php echo plugin_lang_get( 'legend_title_frequency_template_associated' ); ?>">
                        <?php echo plugin_lang_get( 'legend_frequency_template_associated' ); ?>
                    </td>
                </tr>
            </table>
        </td>
        <td>
            <?php echo plugin_lang_get( 'frequency_crontab_status' ); ?>
            <table class="width100" cellspacing="1">
                <tr>
                    <td class="small-caption frequency_crontab_not_ok" width="33%" title="<?php echo plugin_lang_get( 'legend_title_frequency_crontab_not_ok' ); ?>">
                        <?php echo plugin_lang_get( 'legend_frequency_crontab_not_ok' ); ?>
                    </td>
                    <td class="small-caption frequency_crontab_disabled" width="33%" title="<?php echo plugin_lang_get( 'legend_title_frequency_crontab_disabled' ); ?>">
                        <?php echo plugin_lang_get( 'legend_frequency_crontab_disabled' ); ?>
                    </td>
                    <td class="small-caption frequency_crontab_ok" width="33%" title="<?php echo plugin_lang_get( 'legend_title_frequency_crontab_ok' ); ?>">
                        <?php echo plugin_lang_get( 'legend_frequency_crontab_ok' ); ?>
                    </td>
                </tr>
            </table>
        </td>
        <td>
            <?php echo plugin_lang_get( 'frequency_bug_count' ); ?>
            <table class="width100" cellspacing="1">
                <tr>
                    <td class="small-caption frequency_bug_unassociated" width="50%" title="<?php echo plugin_lang_get( 'legend_title_frequency_bug_unassociated' ); ?>">
                        <?php echo plugin_lang_get( 'legend_frequency_bug_unassociated' ); ?>
                    </td>
                    <td class="small-caption frequency_bug_associated" width="50%" title="<?php echo plugin_lang_get( 'legend_title_frequency_bug_associated' ); ?>">
                        <?php echo plugin_lang_get( 'legend_frequency_bug_associated' ); ?>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<br />

<?php
    html_page_bottom();
