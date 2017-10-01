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

    form_security_validate( 'manage_template_category_add' );

    $f_template_id = gpc_get_int( 'template_id' );
    $f_project_category_id = gpc_get( 'category_id' );
    $f_frequency_id = gpc_get_int( 'frequency_id' );
    $f_user_id = gpc_get_int( 'user_id', 0 );

    list( $t_project_id, $t_category_id ) = explode( ',', $f_project_category_id, 2 );

    $t_template_category_id = template_category_add(
        $f_template_id,
        $t_project_id,
        $t_category_id,
        $f_frequency_id,
        $f_user_id
    );
    template_category_log_event_special( $f_template_id, $t_template_category_id, MST_TEMPLATE_CATEGORY_ADDED );

    form_security_purge( 'manage_template_category_add' );

    $t_redirect_url = plugin_page( 'manage_template_edit_page', true ) . '&id=' . $f_template_id;

    layout_page_header( null, $t_redirect_url );
    layout_page_begin();
    html_operation_successful( $t_redirect_url );
    layout_page_end();
