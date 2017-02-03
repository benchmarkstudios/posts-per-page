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
    foreach (get_post_types(['public' => true, '_builtin' => false ]) as $post_type) {
        delete_option(BPPP_PLUGIN_PREFIX . $post_type);

        // for site options in Multisite
        delete_site_option($option_name);
    }
}
bppp_delete_plugin();
