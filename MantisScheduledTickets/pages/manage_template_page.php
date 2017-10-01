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

    $t_page_title = plugin_lang_get( 'title_templates' );
    $t_template_edit_page = plugin_page( 'manage_template_edit_page' ) . '&id=';
    $t_enable_commands = plugin_config_get( 'enable_commands' );

    layout_page_header( $t_page_title );
    layout_page_begin();
    print_manage_menu();
    mst_core_print_scheduled_tickets_menu( MST_MANAGE_TEMPLATE_PAGE );

    $t_templates = template_get_all();

    if( $t_enable_commands ) {
        $t_valid_commands = mst_helper_get_valid_commands();
    }

?>

<div class="col-md-12 col-xs-12">
    <div class="space-10"></div>
    <div class="widget-box widget-color-blue2">
        <div class="widget-header widget-header-small">
            <h4>
                <i class="ace-icon fa fa-newspaper-o"></i>
                <?php echo $t_page_title; ?>
            </h4>
        </div>
        <div class="widget-body">
            <div class="widget-toolbox padding-8 clearfix">
                <div class="btn-toolbar">
                    <div class="btn-group pull-left">
                        <a class="btn btn-primary btn-white btn-round btn-sm" href="<?php echo plugin_page( 'manage_template_add_page' ) ?>"><?php echo plugin_lang_get( 'create_new_template_link' ) ?></a>
                    </div>
                </div>
            </div>
            <div class="widget-main no-padding">
                <div class="table-responsive">
                    <table id="templates" class="table table-bordered table-condensed table-hover table-striped">
                        <thead>
                            <th class="category">
                                <?php echo plugin_lang_get( 'template_summary' ); ?>
                            </th>
                            <th class="category">
                                <?php echo plugin_lang_get( 'template_description' ); ?>
                            </th>
                            <th class="category">
                                <?php echo plugin_lang_get( 'template_category_count' ); ?>
                            </th>
                            <th class="category">
                                <?php echo plugin_lang_get( 'template_status' ); ?>
                            </th>
                            <th class="category">
                                <?php echo plugin_lang_get( 'template_bug_count' ); ?>
                            </th>
                        </thead>
                        <tbody>
                            <?php
                            if( is_array( $t_templates ) ) {
                                foreach( $t_templates as $t_template ) {
                                    $t_id = $t_template['id'];
                                    $t_category_count_class = ( 0 == $t_template['category_count'] ) ?
                                        'template_category_unassociated' :
                                        'template_category_associated';
                                    $t_bug_count_class = ( 0 == $t_template['bug_count'] ) ?
                                        'template_bug_unassociated' :
                                        'template_bug_associated';

                                    if( $t_enable_commands ) {
                                        $t_command_is_valid = in_array( $t_template['command'], $t_valid_commands );
                                    } else {
                                        $t_command_is_valid = true;
                                    }

                                    if( ( 0 == $t_template['deleted_category_count'] ) &&
                                        ( 0 == $t_template['deleted_user_count'] ) &&
                                        $t_command_is_valid ) {
                                        $t_status_class = 'template_status_ok';
                                        $t_status = plugin_lang_get( 'legend_template_status_ok' );
                                    } else {
                                        $t_status_class = 'template_status_not_ok';
                                        $t_status = plugin_lang_get( 'legend_template_status_not_ok' );
                                    }

                                    if( 0 == $t_template['enabled'] ) {
                                        $t_strike_start = '<strike>';
                                        $t_strike_end = '</strike>';
                                    } else {
                                        $t_strike_start = $t_strike_end = '';
                                    }
                                ?>
                                    <tr <?php echo helper_alternate_class( $i ) ?>>
                                        <td>
                                            <a href="<?php echo $t_template_edit_page . $t_template['id']; ?>" id="template_summary_<?php echo $t_id; ?>">
                                                <?php echo $t_strike_start; echo htmlspecialchars( $t_template['summary'] ); echo $t_strike_end; ?>
                                            </a>
                                        </td>
                                        <td id="template_description_<?php echo $t_id; ?>"><?php echo htmlspecialchars( $t_template['description'] ); ?></td>
                                        <td id="category_count_<?php echo $t_id; ?>">
                                            <i class="fa fa-square-o fa-xlg <?php echo $t_category_count_class; ?>"></i>
                                            <?php echo $t_template['category_count']; ?>
                                        </td>
                                        <td id="template_status_<?php echo $t_id; ?>">
                                            <i class="fa fa-square-o fa-xlg <?php echo $t_status_class; ?>"></i>
                                            <?php echo $t_status; ?>
                                        </td>
                                        <td id="bug_count_<?php echo $t_id; ?>">
                                            <i class="fa fa-square-o fa-xlg <?php echo $t_bug_count_class; ?>"></i>
                                            <?php echo $t_template['bug_count']; ?>
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
                <table id="template_legend" class="table table-condensed">
                    <tbody>
                        <tr>
                            <td class="col-md-4 col-xs-4">
                                <table class="table table-condensed">
                                    <thead>
                                        <tr>
                                            <th class="small-caption" colspan="2"><?php echo plugin_lang_get( 'template_category_count' ); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="small-caption" title="<?php echo plugin_lang_get( 'legend_title_template_category_unassociated' ); ?>">
                                                <i class="fa fa-square-o fa-xlg template_category_unassociated"></i>
                                                <?php echo plugin_lang_get( 'legend_template_category_unassociated' ); ?>
                                            </td>
                                            <td class="small-caption" title="<?php echo plugin_lang_get( 'legend_title_template_category_associated' ); ?>">
                                                <i class="fa fa-square-o fa-xlg template_category_associated"></i>
                                                <?php echo plugin_lang_get( 'legend_template_category_associated' ); ?>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                            <td class="col-md-4 col-xs-4">
                                <table class="table table-condensed">
                                    <thead>
                                        <tr>
                                            <th class="small-caption" colspan="2"><?php echo plugin_lang_get( 'template_status' ); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="small-caption" title="<?php echo plugin_lang_get( 'legend_title_template_status_not_ok' ); ?>">
                                                <i class="fa fa-square-o fa-xlg template_status_not_ok"></i>
                                                <?php echo plugin_lang_get( 'legend_template_status_not_ok' ); ?>
                                            </td>
                                            <td class="small-caption" title="<?php echo plugin_lang_get( 'legend_title_template_status_ok' ); ?>">
                                                <i class="fa fa-square-o fa-xlg template_status_ok"></i>
                                                <?php echo plugin_lang_get( 'legend_template_status_ok' ); ?>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                            <td class="col-md-4 col-xs-4">
                                <table class="table table-condensed">
                                    <thead>
                                        <tr>
                                            <th class="small-caption" colspan="2"><?php echo plugin_lang_get( 'template_bug_count' ); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="small-caption" title="<?php echo plugin_lang_get( 'legend_title_template_bug_unassociated' ); ?>">
                                                <i class="fa fa-square-o fa-xlg template_bug_unassociated"></i>
                                                <?php echo plugin_lang_get( 'legend_template_bug_unassociated' ); ?>
                                            </td>
                                            <td class="small-caption" title="<?php echo plugin_lang_get( 'legend_title_template_bug_associated' ); ?>">
                                                <i class="fa fa-square-o fa-xlg template_bug_associated"></i>
                                                <?php echo plugin_lang_get( 'legend_template_bug_associated' ); ?>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
    layout_page_end();
