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

    form_security_validate( 'manage_template_edit' );

    $f_template_id = gpc_get_int( 'template_id' );

    $f_summary = gpc_get_string( 'summary' );
    $f_description = gpc_get_string( 'description' );
    $f_enabled = gpc_get_bool( 'enabled', false );
    $f_command = gpc_get_string( 'command', null );
    $f_diff_flag = gpc_get_bool( 'diff_flag', false );

    $t_old_record = new Template;
    $t_old_record->summary = gpc_get_string( 'old_summary' );
    $t_old_record->description = gpc_get_string( 'old_description' );
    $t_old_record->enabled = gpc_get_bool( 'old_enabled' );
    $t_old_record->command = gpc_get_string( 'old_command' );
    $t_old_record->diff_flag = gpc_get_bool( 'old_diff_flag' );

    $t_new_record = new Template;
    $t_new_record->summary = $f_summary;
    $t_new_record->description = $f_description;
    $t_new_record->enabled = $f_enabled;
    $t_new_record->command = $f_command;
    $t_new_record->diff_flag = $f_diff_flag;

    # if the command has changed or has been blanked out, ask for confirmation
    if( ( '' != $t_old_record->command ) && ( $t_old_record->command != $t_new_record->command ) ) {
        mst_helper_ensure_confirmed(
            plugin_lang_get( 'template_command_change_sure_msg' ),
            array(
                plugin_lang_get( 'template_command_arguments_keep' ),
                plugin_lang_get( 'template_command_arguments_delete' )
            )
        );
    }

    $t_action = gpc_get_string( 'action', '' );

    # update template record
    template_update( $f_template_id, $f_summary, $f_description, $f_enabled, $f_command, $f_diff_flag );
    template_log_changes( $f_template_id, $t_old_record, $t_new_record );

    if( plugin_lang_get( 'template_command_arguments_delete' ) == $t_action ) {
        template_delete_all_command_arguments( $f_template_id );
    }

    form_security_purge( 'manage_template_edit' );

    $t_redirect_url = plugin_page( 'manage_template_page', true );

    layout_page_header( null, $t_redirect_url );
    layout_page_begin();
    html_operation_successful( $t_redirect_url );
    layout_page_end();
