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

##########################################################################
# NOTE:                                                                  #
#                                                                        #
# This script will NOT work out of the box.                              #
#                                                                        #
# Please adjust the values below to match your environment. Also, please #
# do NOT specify passwords in this script, please use the file specified #
# by --defaults-file (see also "Keeping Password Secure"                 #
# http://dev.mysql.com/doc/refman/5.7/en/password-security.html)         #
#                                                                        #
##########################################################################

require_once 'script_framework.php';

# parse & validate command line arguments
init_script(
    $argv
);

$backup_file = './bugtracker_backup.sql';

shell_exec( 'mysqldump ' .
    '--add-drop-database ' .
    '--create-options ' .
    '--disable-keys ' .
    '--defaults-file="credentials.txt" ' .
    '--extended-insert ' .
    '--force ' .
    '--host=localhost ' .
    '--log-error="mysqldump.log" ' .
    '--user=username ' .
    'bugtracker ' .
    '> "' . $backup_file . '"' );

$t_command_output = "Mantis database backed up to \"$backup_file\"";

$g_actions->addChild( 'command_output', htmlspecialchars( $t_command_output ) );

create_output_file();
