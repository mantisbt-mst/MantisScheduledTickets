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
    $g_MantisScheduledTickets_context = true;

    html_page_top( plugin_lang_get( 'title_templates' ) );
    print_manage_menu();
    mst_core_print_scheduled_tickets_menu( MST_MANAGE_TEMPLATE_PAGE );

    $t_template_edit_page = plugin_page( 'manage_template_edit_page' ) . '&id=';

    $t_templates = template_get_all();

    $t_enable_commands = plugin_config_get( 'enable_commands' );

    if( $t_enable_commands ) {
        $t_valid_commands = mst_helper_get_valid_commands();
    }

?>

<br/>

<table class="width100" cellspacing="1">
    <tr>
        <td class="form-title" colspan="2">
            <?php echo plugin_lang_get( 'title_templates' ); ?>
            &nbsp;
            <?php print_button( plugin_page( 'manage_template_add_page' ), plugin_lang_get( 'create_new_template_link' ) ); ?>
        </td>
    </tr>

    <tr>
        <td class="category">
            <?php echo plugin_lang_get( 'template_summary' ); ?>
        </td>
        <td class="category">
            <?php echo plugin_lang_get( 'template_description' ); ?>
        </td>
        <td class="category">
            <?php echo plugin_lang_get( 'template_category_count' ); ?>
        </td>
        <td class="category">
            <?php echo plugin_lang_get( 'template_status' ); ?>
        </td>
        <td class="category">
            <?php echo plugin_lang_get( 'template_bug_count' ); ?>
        </td>
    </tr>

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
                <td id="category_count_<?php echo $t_id; ?>" class="<?php echo $t_category_count_class; ?>"><?php echo $t_template['category_count']; ?></td>
                <td id="template_status_<?php echo $t_id; ?>" class="<?php echo $t_status_class; ?>"><?php echo $t_status; ?></td>
                <td id="bug_count_<?php echo $t_id; ?>" class="<?php echo $t_bug_count_class; ?>"><?php echo $t_template['bug_count']; ?></td>
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
            <?php echo plugin_lang_get( 'template_category_count' ); ?>
            <table class="width100" cellspacing="1">
                <tr>
                    <td class="small-caption template_category_unassociated" width="50%" title="<?php echo plugin_lang_get( 'legend_title_template_category_unassociated' ); ?>">
                        <?php echo plugin_lang_get( 'legend_template_category_unassociated' ); ?>
                    </td>
                    <td class="small-caption template_category_associated" width="50%" title="<?php echo plugin_lang_get( 'legend_title_template_category_associated' ); ?>">
                        <?php echo plugin_lang_get( 'legend_template_category_associated' ); ?>
                    </td>
                </tr>
            </table>
        </td>
        <td>
            <?php echo plugin_lang_get( 'template_status' ); ?>
            <table class="width100" cellspacing="1">
                <tr>
                    <td class="small-caption template_status_not_ok" width="50%" title="<?php echo plugin_lang_get( 'legend_title_template_status_not_ok' ); ?>">
                        <?php echo plugin_lang_get( 'legend_template_status_not_ok' ); ?>
                    </td>
                    <td class="small-caption template_status_ok" width="50%" title="<?php echo plugin_lang_get( 'legend_title_template_status_ok' ); ?>">
                        <?php echo plugin_lang_get( 'legend_template_status_ok' ); ?>
                    </td>
                </tr>
            </table>
        </td>
        <td>
            <?php echo plugin_lang_get( 'template_bug_count' ); ?>
            <table class="width100" cellspacing="1">
                <tr>
                    <td class="small-caption template_bug_unassociated" width="50%" title="<?php echo plugin_lang_get( 'legend_title_template_bug_unassociated' ); ?>">
                        <?php echo plugin_lang_get( 'legend_template_bug_unassociated' ); ?>
                    </td>
                    <td class="small-caption template_bug_associated" width="50%" title="<?php echo plugin_lang_get( 'legend_title_template_bug_associated' ); ?>">
                        <?php echo plugin_lang_get( 'legend_template_bug_associated' ); ?>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<br />

<?php
    html_page_bottom();
