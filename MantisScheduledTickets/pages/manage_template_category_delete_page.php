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

    $f_template_category_id = gpc_get_int( 'id' );
    $f_template_id = gpc_get_int( 'template_id' );

    form_security_validate( 'manage_template_category_delete' );

    helper_ensure_confirmed( plugin_lang_get( 'template_category_delete_sure_msg' ), lang_get( 'delete_link' ) );

    template_category_delete( $f_template_category_id );
    template_category_log_event_special( $f_template_id, $f_template_category_id, MST_TEMPLATE_CATEGORY_DELETED );

    form_security_purge( 'manage_template_category_delete' );

    $t_redirect_url = plugin_page( 'manage_template_edit_page', true ) . "&id=$f_template_id";

    layout_page_header( null, $t_redirect_url );
    layout_page_begin();
    html_operation_successful( $t_redirect_url );
    layout_page_end();
