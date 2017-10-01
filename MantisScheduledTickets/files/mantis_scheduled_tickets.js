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

var buttonAddLabel = 'Add';
var buttonUpdateLabel = 'Update';

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
    if (window.location.href.indexOf('MantisScheduledTickets') != -1 ) {
        $('.mst_form').find('input[type=text],textarea,select').filter(':visible:first').focus();
    }
}

function highlight_manage_MST_menu() {
    if (window.location.href.indexOf('page=MantisScheduledTickets') != -1) {
        $('.mst_manage_link').parent().addClass('active');
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

function bind_events() {
    if (window.location.href.indexOf('MantisScheduledTickets') == -1 ) {
        return;
    }

    $('#day_of_week_radio_all').bind('click', function() {
        enable_disable('day_of_week_select', true);
    });

    $('#day_of_week_radio_select').bind('click', function() {
        enable_disable('day_of_week_select', false);
    });

    $('#month_radio_all').bind('click', function() {
        enable_disable('month_select', true);
    });

    $('#month_radio_select').bind('click', function() {
        enable_disable('month_select', false);
    });

    $('#day_of_month_radio_all').bind('click', function() {
        enable_disable('day_of_month_select', true);
    });

    $('#day_of_month_radio_select').bind('click', function() {
        enable_disable('day_of_month_select', false);
    });

    $('#hour_radio_all').bind('click', function() {
        enable_disable('hour_select', true);
    });

    $('#hour_radio_select').bind('click', function() {
        enable_disable('hour_select', false);
    });

    $('#minute_radio_all').bind('click', function() {
        enable_disable('minute_select', true);
    });

    $('#minute_radio_select').bind('click', function() {
        enable_disable('minute_select', false);
    });

    $('#command').change(function() {
        enable_disable_diff('command', 'diff_flag');
    });

    $('.mst_form_reset_button').bind('click', function() {
        reset_command_argument_form();
    });

    $('.mst_edit_command_argument').bind('click', function(event) {
        id = event.target.id.replace('edit_', '');
        argument_name = $('#' + event.target.id).attr('data-argument-name');
        argument_value = $('#' + event.target.id).attr('data-argument-value');

        populate_command_argument_form(id, argument_name, argument_value);
    });
}

$(function() {
    focus_on_first_element();
    bind_events();
    highlight_manage_MST_menu();
});
