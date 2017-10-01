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

    $f_command_argument_id = gpc_get_int( 'id' );
    $f_template_category_id = gpc_get_int( 'template_category_id' );
    $f_template_id = gpc_get_int( 'template_id' );

    form_security_validate( 'delete_command_argument' );

    helper_ensure_confirmed(
        plugin_lang_get( 'command_argument_delete_sure_msg' ), lang_get( 'delete_link' )
    );

    $t_old_command_arguments = command_arguments_format(
        command_argument_get_all( $f_template_category_id ),
        MST_ESCAPE_FOR_NONE
    );

    command_argument_delete( array( 'id' => db_prepare_int( $f_command_argument_id ) ) );

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

    form_security_purge( 'delete_command_argument' );

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
