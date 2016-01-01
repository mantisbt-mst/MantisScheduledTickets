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
 * @copyright Copyright (C) 2015-2016 MantisScheduledTickets Team <support@mantis-scheduled-tickets.net>
 * @link http://www.mantis-scheduled-tickets.net
 */

    access_ensure_global_level( plugin_config_get( 'manage_threshold' ) );

    $f_id = gpc_get_int( 'id' );
    $f_template_id = gpc_get_int( 'template_id' );
    $f_category_id = gpc_get_int( 'category_id' );

    form_security_validate( 'manage_template_category_delete' );

    helper_ensure_confirmed( plugin_lang_get( 'template_category_delete_sure_msg' ), plugin_lang_get( 'template_category_delete' ) );

    template_category_delete( $f_id );
    template_category_log_event_special( $f_template_id, $f_category_id, TEMPLATE_CATEGORY_DELETED );

    form_security_purge( 'manage_template_category_delete' );

    $t_redirect_url = plugin_page( 'manage_template_edit_page', true ) . "&id=$f_template_id";

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