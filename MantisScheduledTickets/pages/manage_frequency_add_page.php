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

    access_ensure_global_level( plugin_config_get( 'manage_threshold' ) );

    $t_page_title = plugin_lang_get( 'title_add_frequency' );
    $t_action_page = plugin_page( 'manage_frequency_add' );

    layout_page_header( $t_page_title );
    layout_page_begin();
    print_manage_menu();
    mst_core_print_scheduled_tickets_menu( MST_MANAGE_FREQUENCY_PAGE );

?>

<div class="col-md-12 col xs-12">
    <div class="space-10"></div>
    <div class="form-container">
        <form name="add_frequency" id="add_frequency" class="mst_form" method="post" action="<?php echo $t_action_page; ?>">
            <?php
                echo form_security_field( 'manage_frequency_add' );
            ?>

            <div class="widget-box widget-color-blue2">
                <div class="widget-header widget-header-small">
                    <h4>
                        <i class="ace-icon fa fa-calendar-o"></i>
                        <?php echo $t_page_title; ?>
                    </h4>
                </div>
                <div class="widget-body">
                    <div class="widget-main no-padding">
                        <table class="table table-bordered table-condensed">
                            <tbody>
                                <tr>
                                    <td class="category">
                                        <span class="required">*</span><?php echo plugin_lang_get( 'frequency_name' ); ?>
                                    </td>
                                    <td colspan="4">
                                        <input <?php echo helper_get_tab_index() ?> type="text" name="name" id="name" size="60" maxlength="128" />
                                    </td>
                                </tr>

                                <tr>
                                    <td class="category">
                                        <span class="required">*</span><?php echo plugin_lang_get( 'frequency_enabled' ); ?>
                                    </td>
                                    <td colspan="4">
                                        <input <?php echo helper_get_tab_index() ?> type="checkbox" name="enabled" id="enabled" checked="checked" />
                                    </td>
                                </tr>

                                <tr>
                                    <td class="category">
                                        <span class="required">*</span><?php echo plugin_lang_get( 'frequency_day_of_week' ); ?>
                                    </td>
                                    <td class="category">
                                        <span class="required">*</span><?php echo plugin_lang_get( 'frequency_month' ); ?>
                                    </td>
                                    <td class="category">
                                        <span class="required">*</span><?php echo plugin_lang_get( 'frequency_day_of_month' ); ?>
                                    </td>
                                    <td class="category">
                                        <span class="required">*</span><?php echo plugin_lang_get( 'frequency_hour' ); ?>
                                    </td>
                                    <td class="category">
                                        <span class="required">*</span><?php echo plugin_lang_get( 'frequency_minute' ); ?>
                                    </td>
                                </tr>

                                <tr>
                                    <!-- day of week -->
                                    <td>
                                        <?php mst_helper_render_day_of_week_options(); ?>
                                    </td>

                                    <!-- month -->
                                    <td>
                                        <?php mst_helper_render_month_options(); ?>
                                    </td>

                                    <!-- day of month -->
                                    <td>
                                        <?php mst_helper_render_day_of_month_options(); ?>
                                    </td>

                                    <!-- hour -->
                                    <td>
                                        <?php mst_helper_render_hour_options(); ?>
                                    </td>

                                    <!-- minute -->
                                    <td>
                                        <?php mst_helper_render_minute_options(); ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="widget-toolbox padding-8 clearfix">
                        <span class="required pull-right"> * <?php echo lang_get( 'required' ); ?></span>
                        <input type="submit" class="btn btn-primary btn-white btn-round" value="<?php echo plugin_lang_get( 'frequency_add' ); ?>" />
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<?php
    layout_page_end();
