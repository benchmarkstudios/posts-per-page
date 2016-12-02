<?php
// make sure uninstallation is triggered
if (! defined('WP_UNINSTALL_PLUGIN')) {
    exit();
}

/**
 * uninstall - clean up database removing plugin options
 *
 * @since 1.0
*/
function bppp_delete_plugin()
{
    delete_option('optionnamehere');
}
bppp_delete_plugin();
