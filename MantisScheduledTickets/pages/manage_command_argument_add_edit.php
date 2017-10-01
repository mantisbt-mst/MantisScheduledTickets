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

    form_security_validate( 'add_edit_command_argument' );

    $f_template_category_id = gpc_get_int( 'template_category_id' );
    $f_template_id = gpc_get_int( 'template_id' );
    $f_command_argument_id = gpc_get_int( 'command_argument_id', 0 );
    $f_argument_name = gpc_get_string( 'argument_name' );
    $f_argument_value = gpc_get_string( 'argument_value' );

    if( false == command_argument_is_valid( $f_argument_name, $f_command_argument_id ) ) {
        plugin_error( plugin_lang_get( 'error_command_argument' ), ERROR );
    }

    $t_old_command_arguments = command_arguments_format(
        command_argument_get_all( $f_template_category_id ),
        MST_ESCAPE_FOR_NONE
    );

    if( $f_command_argument_id ) {
        command_argument_update( $f_command_argument_id, $f_argument_name, $f_argument_value );
    } else {
        command_argument_add( $f_template_category_id, $f_argument_name, $f_argument_value );
    }

    $t_command_arguments = command_arguments_format(
        command_argument_get_all( $f_template_category_id ),
        MST_ESCAPE_FOR_NONE
    );

    template_category_log_event(
        $f_template_id,
        $f_template_category_id,
        'template_command_arguments',
        $t_old_command_arguments,
        $t_command_arguments
    );

    form_security_purge( 'add_edit_command_argument' );

    $t_redirect_url = plugin_page( 'manage_template_category_edit_page', true ) . '&id=' . $f_template_category_id;

    html_page_top( null, $t_redirect_url );

?>

<br />
<div align="center">
<?php
    echo lang_get( 'operation_successful' ).'<br />';
    print_bracket_link( $t_redirect_url, lang_get( 'proceed' ) );
?>
</div>

<?php
    html_page_bottom();
