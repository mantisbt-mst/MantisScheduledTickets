#!/usr/bin/php
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

require_once 'script_framework.php';

# parse & validate command line arguments
init_script(
    $argv,
    array(
        '--dir' => array( 'description' => 'List the contents of the specified directory', 'required' => true ),
        '--credentials' => array( 'description' => 'Credentials file', 'required' => false )
    )
);

if( isset( $g_args['--credentials'] ) ) {
    $t_credentials = parse_credentials(
        $g_args['--credentials'],
        array( 'username', 'password' )
    );
}

# actual script logic
$tmp_file = shell_exec( 'tempfile' );
shell_exec( "ls -al \"${g_args['--dir']}\" > \"$tmp_file\"" );

$t_diff_output = '';
$t_perform_diff = false;

if( isset( $g_args['--diff-left-side'] ) && ( '' != $g_args['--diff-left-side'] ) ) {
    $t_perform_diff = true;
    $t_diff_output = shell_exec( "diff \"$tmp_file\" \"${g_args['--diff-left-side']}\"" );
}

$t_command_output = @file_get_contents( $tmp_file );

shell_exec( "rm $tmp_file" );


# prepare the output file
$g_actions->addChild( 'command_output', htmlspecialchars( $t_command_output ) );

if( $t_perform_diff ) {
    if( '' != $t_diff_output ) {
        $g_actions->addChild( 'diff_output', $t_diff_output );
        $g_actions->addChild( 'add_monitor', 'administrator' );
        $g_actions->addChild( 'assign_to', 'administrator' );
    } else {
        $g_actions->addChild( 'skip_assign' );
        $g_actions->addChild( 'status', 'resolved' );
    }
}

create_output_file();
