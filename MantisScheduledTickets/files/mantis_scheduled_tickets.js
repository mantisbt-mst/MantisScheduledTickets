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
 * @copyright Copyright (C) 2015-2016 MantisScheduledTickets Team <support@mantis-scheduled-tickets.net>
 * @link http://www.mantis-scheduled-tickets.net
 */

function enable_disable( id, disabled ) {
    document.getElementById(id).disabled = disabled;
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
