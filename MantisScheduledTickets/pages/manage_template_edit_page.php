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
    $g_MantisScheduledTickets_context = true;

    $t_page_title = plugin_lang_get( 'title_edit_template' );
    $t_edit_action = plugin_page( 'manage_template_edit' );
    $t_add_category_action = plugin_page( 'manage_template_category_add' );
    $t_delete_page = plugin_page( 'manage_template_delete_page' );
    $t_template_id = gpc_get_int( 'id' );
    $t_template = template_get_row( $t_template_id );

    html_page_top( $t_page_title );
    print_manage_menu();
    print_scheduled_tickets_menu();

?>

<div align="center">
    <form name="edit_template" method="post" action="<?php echo $t_edit_action; ?>">
        <?php
            echo form_security_field( 'manage_template_edit' );
        ?>

        <input type="hidden" name="template_id" value="<?php echo $t_template_id; ?>" />
        <input type="hidden" name="old_summary" value="<?php echo string_html_specialchars( $t_template['summary'] ); ?>" />
        <input type="hidden" name="old_description" value="<?php echo string_html_specialchars( $t_template['description'] ); ?>" />
        <input type="hidden" name="old_enabled" value="<?php echo $t_template['enabled']; ?>" />

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
                    <input <?php echo helper_get_tab_index(); ?> type="text" name="summary" size="105" maxlength="128" value="<?php echo $t_template['summary']; ?>" />
                </td>
            </tr>

            <tr <?php echo helper_alternate_class(); ?>>
                <td class="category" width="20%">
                    <span class="required">*</span><?php echo plugin_lang_get( 'template_description' ); ?>
                </td>
                <td width="80%">
                    <textarea <?php echo helper_get_tab_index(); ?> name="description" cols="80" rows="10"><?php echo $t_template['description']; ?></textarea>
                </td>
            </tr>

            <tr <?php echo helper_alternate_class(); ?>>
                <td class="category" width="20%">
                    <span class="required">*</span><?php echo plugin_lang_get( 'template_enabled' ); ?>
                </td>
                <td width="80%">
                    <input <?php echo helper_get_tab_index(); ?> type="checkbox" name="enabled" <?php echo $t_template['enabled'] ? 'checked' : '' ?> />
                </td>
            </tr>

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
                echo form_security_field( 'manage_template_delete' );
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
            <?php echo lang_get( 'category' ); ?>
        </td>
        <td>
            <?php echo plugin_lang_get( 'frequency' ); ?>
        </td>
        <td>
            <?php echo lang_get( 'assign_to' ); ?>
        </td>
        <td class="center">
            <?php echo lang_get( 'actions' ) ?>
        </td>
    </tr>
    <?php
        $t_categories = template_get_categories( $t_template_id );

        if( count( $t_categories ) > 0 ) {
            foreach( $t_categories as $t_category ) {
                $t_id = $t_category['id'];
                $t_deleted_category = ( '' == $t_category['category_name'] ) ? ' class="template_status_not_ok"' : '';
                $t_disable_edit = ( '' == $t_category['category_name'] ) ? ' disabled' : '';
    ?>
                <tr <?php echo helper_alternate_class(); ?>>
                    <td<?php echo $t_deleted_category; ?>>
                        <?php echo '[' . $t_category['project_name'] . '] ' . $t_category['category_name']; ?>
                    </td>
                    <td>
                        <?php echo $t_category['frequency_name']; ?>
                    </td>
                    <td>
                        <?php echo prepare_user_name( $t_category['user_id'] ); ?>
                    </td>
                    <td class="center">
                        <form name="manage_template_category_edit" method="post" action="<?php echo plugin_page( 'manage_template_category_edit_page' ); ?>">
                            <input type="hidden" name="id" value="<?php echo $t_id; ?>">
                            <input type="hidden" name="template_id" value="<?php echo $t_template_id; ?>">
                            <input type="hidden" name="category_id" value="<?php echo $t_category['category_id']; ?>">
                            <input type="submit" class="button-small" value="<?php echo plugin_lang_get( 'template_category_edit' ); ?>"<?php echo $t_disable_edit; ?>>
                        </form>
                        <form name="manage_template_category_delete" method="post" action="<?php echo plugin_page( 'manage_template_category_delete' ); ?>">
                            <?php
                                echo form_security_field( 'manage_template_category_delete' );
                            ?>
                            <input type="hidden" name="id" value="<?php echo $t_id; ?>">
                            <input type="hidden" name="template_id" value="<?php echo $t_template_id; ?>">
                            <input type="hidden" name="category_id" value="<?php echo $t_category['category_id']; ?>">
                            <input type="submit" class="button-small" value="<?php echo plugin_lang_get( 'template_category_delete' ); ?>">
                        </form>
                    </td>
                </tr>
    <?php
            } # end for loop
        } # end if
    ?>
    </table>
    <br />

    <form name="template_category_add" method="post" action="<?php echo $t_add_category_action; ?>">
        <?php echo form_security_field( 'manage_template_category_add' ) ?>
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
                    <?php template_helper_available_categories( $t_template_id ); ?>
                </td>
            </tr>
            <tr <?php echo helper_alternate_class(); ?>>
                <td class="category" width="20%">
                    <span class="required">*</span><?php echo plugin_lang_get( 'frequency' ); ?>
                </td>
                <td>
                    <?php template_helper_frequencies(); ?>
                </td>
            </tr>
            <tr <?php echo helper_alternate_class(); ?>>
                <td class="category" width="20%">
                    <?php echo lang_get( 'assign_to' ); ?>
                </td>
                <td>
                    <select name="user_id">
                        <option value="0"><?php echo plugin_lang_get('not_assigned'); ?></option>
                        <?php print_assign_to_option_list(); ?>
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
                    case TEMPLATE_CHANGED:
                        echo plugin_lang_get( 'template_' . $t_item['field_name'] );
                        break;
                    case TEMPLATE_CATEGORY_CHANGED:
                        switch( $t_item['field_name'] ) {
                            case 'frequency':
                                echo plugin_lang_get( 'frequency' );
                                break;
                            case 'assigned_to':
                                echo lang_get( 'assigned_to' );
                                break;
                        }
                        break;
                    case TEMPLATE_CATEGORY_ADDED:
                        echo plugin_lang_get( 'template_category_added' );
                        break;
                    case TEMPLATE_CATEGORY_DELETED:
                        echo plugin_lang_get( 'template_category_deleted' );
                        break;
                }
            ?>
        </td>
        <td class="small-caption">
            <?php
                switch( $t_item['type'] ) {
                    case TEMPLATE_ADDED:
                        echo plugin_lang_get( 'template_added' );
                        break;
                    case TEMPLATE_ENABLED:
                        echo plugin_lang_get( 'template_enabled' );
                        break;
                    case TEMPLATE_DISABLED:
                        echo plugin_lang_get( 'template_disabled' );
                        break;
                    case TEMPLATE_CHANGED:
                        switch( $t_item['field_name'] ) {
                            case 'summary':
                            case 'description':
                            case 'enabled':
                                echo $t_item['old_value'] . ' => ' . $t_item['new_value'];
                                break;
                        }
                        break;
                    case TEMPLATE_DELETED:
                        # we should never really get here... if the template was deleted, we wouldn't be rendering this page...
                        echo plugin_lang_get( 'template_deleted' );
                        break;
                    case TEMPLATE_CATEGORY_ADDED:
                        echo sprintf( $t_item['project_category'] );
                        break;
                    case TEMPLATE_CATEGORY_CHANGED:
                        switch( $t_item['field_name'] ) {
                            case 'frequency':
                            case 'assigned_to':
                                echo sprintf( plugin_lang_get( 'template_category_changed_info' ), $t_item['project_category'], $t_item['old_value'], $t_item['new_value'] );
                                break;
                        }
                        break;
                    case TEMPLATE_CATEGORY_DELETED:
                        echo sprintf( $t_item['project_category'] );
                        break;
                }
            ?>
        </td>
    </tr>
    <?php
        } # end for loop
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
