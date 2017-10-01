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

    $t_page_title = plugin_lang_get( 'title_frequencies' );
    $t_frequency_edit_page = plugin_page( 'manage_frequency_edit_page' ) . '&id=';

    layout_page_header( plugin_lang_get( 'title_frequencies' ) );
    layout_page_begin();
    print_manage_menu();
    mst_core_print_scheduled_tickets_menu( MST_MANAGE_FREQUENCY_PAGE );

    $t_frequencies = frequency_get_all();
    $t_crontab_entries = cron_get_plugin_entries( cron_get_crontab_file() );

?>

<div class="col-md-12 col-xs-12">
    <div class="space-10"></div>
    <div class="widget-box widget-color-blue2">
        <div class="widget-header widget-header-small">
            <h4>
                <i class="ace-icon fa fa-calendar-o"></i>
                <?php echo $t_page_title; ?>
            </h4>
        </div>
        <div class="widget-body">
            <div class="widget-toolbox padding-8 clearfix">
                <div class="btn-toolbar">
                    <div class="btn-group pull-left">
                        <a class="btn btn-primary btn-white btn-round btn-sm" href="<?php echo plugin_page( 'manage_frequency_add_page' ) ?>"><?php echo plugin_lang_get( 'create_new_frequency_link' ) ?></a>
                        <a class="btn btn-primary btn-white btn-round btn-sm" href="<?php echo plugin_page( 'manage_frequency_regenerate_crontab' ) ?>"><?php echo plugin_lang_get( 'regenerate_crontab_link' ) ?></a>
                    </div>
                </div>
            </div>
            <div class="widget-main no-padding">
                <div class="table-responsive">
                    <table id="frequencies" class="table table-bordered table-condensed table-hover table-striped">
                        <thead>
                            <tr>
                                <th>
                                    <?php echo plugin_lang_get( 'frequency_name' ); ?>
                                </th>
                                <th>
                                    <?php echo plugin_lang_get( 'frequency_day_of_week' ); ?>
                                </th>
                                <th>
                                    <?php echo plugin_lang_get( 'frequency_month' ); ?>
                                </th>
                                <th>
                                    <?php echo plugin_lang_get( 'frequency_day_of_month' ); ?>
                                </th>
                                <th>
                                    <?php echo plugin_lang_get( 'frequency_hour' ); ?>
                                </th>
                                <th>
                                    <?php echo plugin_lang_get( 'frequency_minute' ); ?>
                                </th>
                                <th>
                                    <?php echo plugin_lang_get( 'frequency_crontab_status' ); ?>
                                </th>
                                <th>
                                    <?php echo plugin_lang_get( 'frequency_template_count' ); ?>
                                </th>
                                <th>
                                    <?php echo plugin_lang_get( 'frequency_bug_count' ); ?>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
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
                                    <tr>
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
                                        <td id="crontab_status_<?php echo $t_id; ?>">
                                            <i class="fa fa-square-o fa-xlg <?php echo $t_crontab_class; ?>"></i>
                                            <?php echo $t_crontab_status; ?>
                                        </td>
                                        <td id="template_count_<?php echo $t_id; ?>">
                                            <i class="fa fa-square-o fa-xlg <?php echo $t_template_count_class; ?>"></i>
                                            <?php echo $t_frequency['template_count']; ?>
                                        </td>
                                        <td id="bug_count_<?php echo $t_id; ?>">
                                            <i class="fa fa-square-o fa-xlg <?php echo $t_bug_count_class; ?>"></i>
                                            <?php echo $t_frequency['bug_count']; ?>
                                        </td>
                                    </tr>
                            <?php
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="col-md-12 col-xs-12">
    <div class="space-10"></div>
    <div class="widget-box widget-color-blue2">
        <div class="widget-header widget-header-small">
            <h4 class="widget-title">
                <i class="ace-icon fa fa-map-o"></i>
            </h4>
            <div class="widget-toolbar">
                <a data-action="collapse" href="#">
                    <i class="1 ace-icon fa fa-chevron-up bigger-125"></i>
                </a>
            </div>
        </div>
        <div class="widget-body">
            <div class="widget-main no-padding">
                <table id="frequency_legend" class="table table-condensed">
                    <tbody>
                        <tr>
                            <td class="col-md-4 col-xs-4">
                                <table class="table table-condensed">
                                    <thead>
                                        <tr>
                                            <th class="small-caption" colspan="3"><?php echo plugin_lang_get( 'frequency_crontab_status' ); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="small-caption" title="<?php echo plugin_lang_get( 'legend_title_frequency_crontab_not_ok' ); ?>">
                                                <i class="fa fa-square-o fa-xlg frequency_crontab_not_ok"></i>
                                                <?php echo plugin_lang_get( 'legend_frequency_crontab_not_ok' ); ?>
                                            </td>
                                            <td class="small-caption" title="<?php echo plugin_lang_get( 'legend_title_frequency_crontab_disabled' ); ?>">
                                                <i class="fa fa-square-o fa-xlg frequency_crontab_disabled"></i>
                                                <?php echo plugin_lang_get( 'legend_frequency_crontab_disabled' ); ?>
                                            </td>
                                            <td class="small-caption" title="<?php echo plugin_lang_get( 'legend_title_frequency_crontab_ok' ); ?>">
                                                <i class="fa fa-square-o fa-xlg frequency_crontab_ok"></i>
                                                <?php echo plugin_lang_get( 'legend_frequency_crontab_ok' ); ?>
                                            </td>
                                    </tbody>
                                </table>
                            </td>
                            <td class="col-md-4 col-xs-4">
                                <table class="table table-condensed">
                                    <thead>
                                        <tr>
                                            <th class="small-caption" colspan="2"><?php echo plugin_lang_get( 'frequency_template_count' ); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="small-caption" title="<?php echo plugin_lang_get( 'legend_title_frequency_template_unassociated' ); ?>">
                                                <i class="fa fa-square-o fa-xlg frequency_template_unassociated"></i>
                                                <?php echo plugin_lang_get( 'legend_frequency_template_unassociated' ); ?>
                                            </td>
                                            <td class="small-caption" title="<?php echo plugin_lang_get( 'legend_title_frequency_template_associated' ); ?>">
                                                <i class="fa fa-square-o fa-xlg frequency_template_associated"></i>
                                                <?php echo plugin_lang_get( 'legend_frequency_template_associated' ); ?>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                            <td class="col-md-4 col-xs-4">
                                <table class="table table-condensed">
                                    <thead>
                                        <tr>
                                            <th class="small-caption" colspan="2"><?php echo plugin_lang_get( 'frequency_bug_count' ); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="small-caption" title="<?php echo plugin_lang_get( 'legend_title_frequency_bug_unassociated' ); ?>">
                                                <i class="fa fa-square-o fa-xlg frequency_bug_unassociated"></i>
                                                <?php echo plugin_lang_get( 'legend_frequency_bug_unassociated' ); ?>
                                            </td>
                                            <td class="small-caption" title="<?php echo plugin_lang_get( 'legend_title_frequency_bug_associated' ); ?>">
                                                <i class="fa fa-square-o fa-xlg frequency_bug_associated"></i>
                                                <?php echo plugin_lang_get( 'legend_frequency_bug_associated' ); ?>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
    layout_page_end();
