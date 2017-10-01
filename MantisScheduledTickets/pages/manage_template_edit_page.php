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

    $t_page_title = plugin_lang_get( 'title_edit_template' );
    $t_edit_action = plugin_page( 'manage_template_edit' );
    $t_add_category_action = plugin_page( 'manage_template_category_add' );
    $t_delete_page = plugin_page( 'manage_template_delete_page' );
    $t_edit_category_page = plugin_page( 'manage_template_category_edit_page' );
    $t_delete_category_page = plugin_page( 'manage_template_category_delete_page' );

    $t_template_id = gpc_get_int( 'id' );
    $t_template = template_get_row( $t_template_id );
    $t_has_command = ( '' != $t_template['command'] ) ? 1 : 0;
    $t_enable_commands = plugin_config_get( 'enable_commands' );

    if( $t_enable_commands ) {
        $t_command_class = mst_helper_command_is_valid( $t_template['command'] ) ? '' : ' class="template_command_invalid"';
    } else {
        $t_command_class = '';
    }

    $t_yes = plugin_lang_get( 'yes' );
    $t_no = plugin_lang_get( 'no' );

    html_page_top( $t_page_title );
    print_manage_menu();
    mst_core_print_scheduled_tickets_menu();

?>

<div align="center">
    <form name="edit_template" id="edit_template" method="post" action="<?php echo $t_edit_action; ?>">
        <?php
            echo form_security_field( 'edit_template' );
        ?>

        <input type="hidden" name="template_id" value="<?php echo $t_template_id; ?>" />
        <input type="hidden" name="old_summary" value="<?php echo htmlspecialchars( $t_template['summary'] ); ?>" />
        <input type="hidden" name="old_description" value="<?php echo htmlspecialchars( $t_template['description'] ); ?>" />
        <input type="hidden" name="old_enabled" value="<?php echo $t_template['enabled']; ?>" />
        <input type="hidden" name="old_command" value="<?php echo htmlspecialchars( $t_template['command'] ); ?>" />
        <input type="hidden" name="old_diff_flag" value="<?php echo $t_template['diff_flag']; ?>" />

        <table class="width75" cellspacing="1">
            <tr>
                <td class="form-title">
                    <?php echo $t_page_title; ?>
                </td>
            </tr>

            <tr <?php echo helper_alternate_class(); ?>>
                <td class="category" width="20%">
                    <span class="required">*</span><?php echo plugin_lang_get( 'template_summary' ); ?>
                </td>
                <td width="80%">
                    <input <?php echo helper_get_tab_index(); ?> type="text" name="summary" id="summary" size="105" maxlength="128" value="<?php echo htmlspecialchars( $t_template['summary'] ); ?>" />
                </td>
            </tr>

            <tr <?php echo helper_alternate_class(); ?>>
                <td class="category" width="20%">
                    <span class="required">*</span><?php echo plugin_lang_get( 'template_description' ); ?>
                </td>
                <td width="80%">
                    <textarea <?php echo helper_get_tab_index(); ?> name="description" id="description" cols="80" rows="10"><?php echo htmlspecialchars( $t_template['description'] ); ?></textarea>
                </td>
            </tr>

            <tr <?php echo helper_alternate_class(); ?>>
                <td class="category" width="20%">
                    <span class="required">*</span><?php echo plugin_lang_get( 'template_enabled' ); ?>
                </td>
                <td width="80%">
                    <input <?php echo helper_get_tab_index(); ?> type="checkbox" name="enabled" id="enabled" <?php echo $t_template['enabled'] ? 'checked="checked"' : '' ?> />
                </td>
            </tr>

            <?php
                if( $t_enable_commands ) {
            ?>
                <tr <?php echo helper_alternate_class();?>>
                    <td class="category" width="20%">
                        <?php echo plugin_lang_get( 'template_command' ); ?>
                    </td>
                    <td width="80%" <?php echo $t_command_class; ?>>
                        <?php mst_helper_commands( $t_template['command'] ); ?>
                    </td>
                </tr>

                <tr <?php echo helper_alternate_class(); ?>>
                    <td class="category" width="20%">
                        <?php echo plugin_lang_get( 'template_diff_flag' ); ?>
                    </td>
                    <td width="80%">
                        <input <?php echo helper_get_tab_index(); ?> type="checkbox" name="diff_flag" id="diff_flag" <?php echo $t_template['diff_flag'] ? 'checked="checked"' : '' ?> <?php echo $t_has_command ? '' : 'disabled="disabled"'; ?> />
                    </td>
                </tr>
            <?php
                }
            ?>

            <!-- buttons -->
            <tr>
                <td>
                    <input <?php echo helper_get_tab_index(); ?> type="submit" class="button" value="<?php echo plugin_lang_get( 'template_update' ); ?>" />
                </td>
            </tr>
        </table>
    </form>
</div>
<br />

<?php
    if( 0 == $t_template['bug_count'] ) {
?>
    <!-- delete -->
    <div class="border center">
        <form name="delete_template" method="post" action="<?php echo $t_delete_page; ?>">
            <?php
                echo form_security_field( 'delete_template' );
            ?>
            <input type="hidden" name="id" value="<?php echo $t_template_id; ?>" />
            <input type="submit" class="button" value="<?php echo plugin_lang_get( 'template_delete' ); ?>" />
        </form>
    </div>
    <br />
<?php
    }
?>
<br />

<div align="center">
    <table class="width75" cellspacing="1">

    <!-- Title -->
    <tr>
        <td class="form-title" colspan="3">
            <?php echo lang_get( 'categories' ) ?>
        </td>
    </tr>
    <tr class="row-category">
        <td>
            <?php echo plugin_lang_get( 'template_category_id' ); ?>
        </td>
        <td>
            <?php echo lang_get( 'category' ); ?>
        </td>
        <td>
            <?php echo plugin_lang_get( 'frequency' ); ?>
        </td>
        <td>
            <?php echo lang_get( 'assign_to' ); ?>
        </td>
        <?php
            if( $t_enable_commands ) {
        ?>
            <td>
                <?php echo plugin_lang_get( 'template_command_arguments' ); ?>
            </td>
        <?php
            }
        ?>
        <td class="center">
            <?php echo lang_get( 'actions' ) ?>
        </td>
    </tr>
    <?php
        $t_categories = template_category_get_all( array( 'template_id' => db_prepare_int( $t_template_id ) ) );

        if( is_array( $t_categories ) ) {
            foreach( $t_categories as $t_category ) {
                $t_category_id = $t_category['template_category_id'];
                $t_deleted_category = ( '' == $t_category['category_name'] ) ? ' class="template_status_not_ok"' : '';

                if( 1 == $t_category['frequency_enabled'] ) {
                    $t_strike_start = $t_strike_end = '';
                } else {
                    $t_strike_start = '<strike>';
                    $t_strike_end = '</strike>';
                }
    ?>
                <tr <?php echo helper_alternate_class(); ?>>
                    <td id="template_category_id_<?php echo $t_category_id; ?>"><?php echo $t_category_id; ?></td>
                    <td<?php echo $t_deleted_category; ?> id="project_category_<?php echo $t_category_id; ?>">
                        <?php echo '[' . $t_category['project_name'] . '] ' . $t_category['category_name']; ?>
                    </td>
                    <td id="frequency_<?php echo $t_category_id; ?>">
                        <?php echo $t_strike_start . $t_category['frequency_name'] . $t_strike_end; ?>
                    </td>
                    <td id="user_id_<?php echo $t_category_id; ?>">
                        <?php echo prepare_user_name( $t_category['user_id'] ); ?>
                    </td>
                    <?php
                        if( $t_enable_commands ) {
                    ?>
                        <td id="command_arguments_<?php echo $t_category_id; ?>">
                            <?php
                                echo command_arguments_format(
                                    command_argument_get_all( $t_category['template_category_id'] ),
                                    MST_ESCAPE_FOR_NONE
                                );
                            ?>
                        </td>
                    <?php
                        }
                    ?>
                    <td class="center">
                        <form name="edit_template_category" method="post" action="<?php echo $t_edit_category_page; ?>">
                            <input type="hidden" name="id" value="<?php echo $t_category_id; ?>">
                            <input type="hidden" name="template_id" value="<?php echo $t_template_id; ?>">
                            <input type="hidden" name="has_command" value="<?php echo $t_has_command; ?>">
                            <input type="submit" class="button-small" value="<?php echo lang_get( 'edit_link' ); ?>" id="edit_<?php echo $t_category_id; ?>">
                        </form>
                        <form name="delete_template_category" method="post" action="<?php echo $t_delete_category_page; ?>">
                            <?php
                                echo form_security_field( 'delete_template_category' );
                            ?>
                            <input type="hidden" name="id" value="<?php echo $t_category_id; ?>">
                            <input type="hidden" name="template_id" value="<?php echo $t_template_id; ?>">
                            <input type="submit" class="button-small" value="<?php echo lang_get( 'delete_link' ); ?>" id="delete_<?php echo $t_category_id; ?>">
                        </form>
                    </td>
                </tr>
    <?php
            } # end for loop
        } # end if
    ?>
    </table>
    <br />

    <form name="add_template_category" id="add_template_category" method="post" action="<?php echo $t_add_category_action; ?>">
        <?php
            echo form_security_field( 'add_template_category' );
        ?>
        <input type="hidden" name="template_id" value="<?php echo $t_template_id; ?>">

        <table class="width50">
            <tr>
                <td class="form-title" colspan="2">
                    <?php echo lang_get( 'add_category_button' ) ?>
                </td>
            </tr>
            <tr <?php echo helper_alternate_class(); ?>>
                <td class="category" width="20%">
                    <span class="required">*</span><?php echo lang_get( 'category' ); ?>
                </td>
                <td>
                    <?php mst_helper_available_categories(); ?>
                </td>
            </tr>
            <tr <?php echo helper_alternate_class(); ?>>
                <td class="category" width="20%">
                    <span class="required">*</span><?php echo plugin_lang_get( 'frequency' ); ?>
                </td>
                <td>
                    <?php mst_helper_frequencies(); ?>
                </td>
            </tr>
            <tr <?php echo helper_alternate_class(); ?>>
                <td class="category" width="20%">
                    <?php echo lang_get( 'assign_to' ); ?>
                </td>
                <td>
                    <select name="user_id" id="user_id">
                        <option value="0"><?php echo plugin_lang_get( 'not_assigned' ); ?></option>
                        <?php print_assign_to_option_list( 0 ); ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <input type="submit" value="<?php echo lang_get( 'add_category_button' ); ?>">
                </td>
            </tr>
        </table>
    </form>
</div>

<!-- History -->
<a name="history" id="history" />
<?php
    collapse_open( 'history' );
    $t_history = template_get_history( $t_template_id );
    $t_normal_date_format = config_get( 'normal_date_format' );
?>
<table class="width100" cellspacing="0">
    <tr>
        <td class="form-title" colspan="4">
            <?php
                collapse_icon( 'history' );
                echo plugin_lang_get( 'template_history' );
            ?>
        </td>
    </tr>
    <tr class="row-category-history">
        <td class="small-caption">
            <?php echo lang_get( 'date_modified' ); ?>
        </td>
        <td class="small-caption">
            <?php echo lang_get( 'username' ); ?>
        </td>
        <td class="small-caption">
            <?php echo lang_get( 'field' ); ?>
        </td>
        <td class="small-caption">
            <?php echo lang_get( 'change' ); ?>
        </td>
    </tr>
    <?php
        if( is_array( $t_history ) ) {
            foreach ( $t_history as $t_item ) {
    ?>
                <tr <?php echo helper_alternate_class(); ?>>
                    <td class="small-caption" nowrap="nowrap">
                        <?php echo date( $t_normal_date_format, $t_item['date_modified'] ); ?>
                    </td>
                    <td class="small-caption">
                        <?php print_user( $t_item['user_id'] ); ?>
                    </td>
                    <td class="small-caption">
                        <?php
                            switch( $t_item['type'] ) {
                                case MST_TEMPLATE_CHANGED:
                                    echo plugin_lang_get( 'template_' . $t_item['field_name'] );
                                    break;
                                case MST_TEMPLATE_CATEGORY_CHANGED:
                                    switch( $t_item['field_name'] ) {
                                        case 'project':
                                            echo lang_get( 'project_name' );
                                            break;
                                        case 'category':
                                            echo lang_get( 'category' );
                                            break;
                                        case 'frequency':
                                            echo plugin_lang_get( 'frequency' );
                                            break;
                                        case 'assigned_to':
                                            echo lang_get( 'assigned_to' );
                                            break;
                                        case 'template_command_arguments':
                                            echo plugin_lang_get( 'template_command_arguments' );
                                            break;
                                    }
                                    break;
                                case MST_TEMPLATE_CATEGORY_ADDED:
                                    echo plugin_lang_get( 'template_category_added' );
                                    break;
                                case MST_TEMPLATE_CATEGORY_DELETED:
                                    echo plugin_lang_get( 'template_category_deleted' );
                                    break;
                                case MST_TEMPLATE_CONFIG_CHANGED:
                                    echo ( $t_item['new_value'] ) ?
                                        plugin_lang_get( 'config_commands_enabled' ) :
                                        plugin_lang_get( 'config_commands_disabled' );
                                    break;
                            }
                        ?>
                    </td>
                    <td class="small-caption">
                        <?php
                            switch( $t_item['type'] ) {
                                case MST_TEMPLATE_ADDED:
                                    echo plugin_lang_get( 'template_added' );
                                    break;
                                case MST_TEMPLATE_ENABLED:
                                    echo plugin_lang_get( 'template_enabled' );
                                    break;
                                case MST_TEMPLATE_DISABLED:
                                    echo plugin_lang_get( 'template_disabled' );
                                    break;
                                case MST_TEMPLATE_CHANGED:
                                    switch( $t_item['field_name'] ) {
                                        case 'summary':
                                        case 'description':
                                        case 'enabled':
                                        case 'command':
                                            echo $t_item['old_value'] . ' => ' . $t_item['new_value'];
                                            break;
                                        case 'diff_flag':
                                            echo ( $t_item['old_value'] ? $t_yes : $t_no ) . ' => ' . ( $t_item['new_value'] ? $t_yes : $t_no );
                                            break;
                                    }
                                    break;
                                case MST_TEMPLATE_DELETED:
                                    # we should never really get here... if the template was deleted, we wouldn't be rendering this page...
                                    echo plugin_lang_get( 'template_deleted' );
                                    break;
                                case MST_TEMPLATE_CATEGORY_ADDED:
                                    echo sprintf(
                                        plugin_lang_get( 'template_category_added_info' ),
                                        $t_item['template_category_id']
                                    );
                                    break;
                                case MST_TEMPLATE_CATEGORY_CHANGED:
                                    switch( $t_item['field_name'] ) {
                                        case 'project':
                                        case 'category':
                                        case 'frequency':
                                        case 'assigned_to':
                                        case 'template_command_arguments':
                                            echo sprintf(
                                                plugin_lang_get( 'template_category_changed_info' ),
                                                $t_item['template_category_id'],
                                                $t_item['old_value'],
                                                $t_item['new_value']
                                            );
                                            break;
                                    }
                                    break;
                                case MST_TEMPLATE_CATEGORY_DELETED:
                                    echo sprintf(
                                        plugin_lang_get( 'template_category_deleted_info' ),
                                        $t_item['template_category_id']
                                    );
                                    break;
                            }
                        ?>
                    </td>
                </tr>
    <?php
            } # end for loop
        }
    ?>
</table>
<?php
	collapse_closed( 'history' );
?>
<table class="width100" cellspacing="0">
<tr>
	<td class="form-title" colspan="4">
        <?php
            collapse_icon( 'history' );
            echo plugin_lang_get( 'template_history' );
        ?>
	</td>
</tr>
</table>

<?php
	collapse_end( 'history' );
    html_page_bottom();
?>

<script type="text/javascript">
    focus_on_first_element();
</script>
