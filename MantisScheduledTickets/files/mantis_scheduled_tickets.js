/*
MantisScheduledTickets - a MantisBT (http://www.mantisbt.org) plugin

MantisScheduledTickets is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
(at your option) any later version.

MantisScheduledTickets is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with MantisScheduledTickets.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * Mantis Scheduled Tickets
 *
 * @package MantisScheduledTickets
 * @filesource
 * @copyright Copyright (C) 2015-2017 MantisScheduledTickets Team <support@mantis-scheduled-tickets.net>
 * @link http://www.mantis-scheduled-tickets.net
 */

function enable_disable( id, disabled ) {
    document.getElementById(id).disabled = disabled;
}

function enable_disable_diff( id1, id2 ) {
    var command = document.getElementById(id1);
    var diff = document.getElementById(id2);

    if ( !command || !diff ) {
        return;
    }

    diff.disabled = ( 0 == command.selectedIndex );

    if( diff.disabled ) {
        diff.checked = false;
    }
}

function focus_on_first_element() {
    var forms = document.forms;

    // skip the first two forms on the page ("select project" and "jump to issue", respectively)
    if( forms  && forms.length > 2 ) {
        for( var i = 2; i < forms.length; i++ ) {
            for( var j = 0; j < forms[i].length; j++ ) {
                if( forms[i][j].disabled == false && forms[i][j].style.display != 'none' && !forms[i][j].readonly != undefined && forms[i][j].type != 'hidden' ) {
                    forms[i][j].focus();
                    return;
                }
            }
        }
    }
}

function reset_command_argument_form() {
    // clear highlighting for the entire table
    var classNames;
    var argumentsTable = document.getElementById( 'command_arguments' );
    var submitButton = document.getElementById( 'submit_button' );

    if ( argumentsTable ) {
        for ( var i = 0; i < argumentsTable.rows.length; i++ ) {
            classNames = argumentsTable.rows[i].className;
            argumentsTable.rows[i].className = classNames.replace( 'argument_edited', '' );
        }
    }

    if ( submitButton ) {
        submitButton.value = buttonAddLabel;
    }
}

function populate_command_argument_form(id, argument_name, argument_value) {
    reset_command_argument_form();

    // highlight the record in question and populate the form
    var idField = document.getElementById( 'command_argument_id' );
    var argumentName = document.getElementById( 'argument_name' );
    var argumentValue = document.getElementById( 'argument_value' );
    var submitButton = document.getElementById( 'submit_button' );
    var row = document.getElementById( 'argument_' + id );

    if ( idField ) {
        idField.value = id;
    }

    if ( argumentName ) {
        argumentName.value = argument_name;
    }

    if ( argumentValue ) {
        argumentValue.value = argument_value;
    }

    if ( submitButton ) {
        submitButton.value = buttonUpdateLabel;
    }

    if ( row ) {
        row.className = 'argument_edited ' + row.className;
    }
}
