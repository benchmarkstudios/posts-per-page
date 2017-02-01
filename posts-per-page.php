<?php
/**
 * Plugin Name: Posts Per Page
 * Plugin URI: https://github.com/benchmark/posts-per-page
 * Description: Control posts per page for all your custom post types.
 * Version: 1.0
 * Author: Benchmark
 * Author URI: http://benchmark.co.uk/
 * Text Domain: posts-per-page
 * License: GPL2
 * Domain Path: /languages/
 *
 * Copyright 2016 - 2017 Benchmark Studios Ltd.
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @package posts-per-page
 * @author Benchmark Studios Ltd., Lukas Juhas
 * @version 1.0
 *
 */

// exit if accessed directly
if (! defined('ABSPATH')) {
    exit;
}

// define stuff
define('BPPP_VERSION', '1.0');
define('BPPP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('BPPP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('BPPP_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('BPPP_PLUGIN_DOMAIN', 'posts-per-page');
define('BPPP_PLUGIN_PREFIX', 'bppp' . '_');

class Benchmark_Posts_Per_Page
{
    /**
     * constructor
     * @since 1.0
     */
    public function __construct()
    {
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_menu', array($this, 'manage_menus'));
        add_action('init', array($this, 'update_options'));

        add_filter('plugin_action_links_' . BPPP_PLUGIN_BASENAME, array($this, 'action_links'));

        // make sure we modify queries on front end only
        if (!is_admin()) {
            add_filter('pre_get_posts', array($this, 'posts_per_page'));
        }
    }

    /**
     * register settings
     * @since 1.0
     */
    public function register_settings()
    {
        foreach ($this->get_posttypes() as $post_type) {
            register_setting(BPPP_PLUGIN_DOMAIN, BPPP_PLUGIN_PREFIX . $post_type);
        }
    }

    /**
     * get cuomst post types
     * @since 1.0
     */
    public function get_posttypes()
    {
        return $post_types = get_post_types([
            'public'   => true,
            '_builtin' => false,
        ]);
    }

    /**
     * settings page markup
     * @since 1.0
     */
    public function settings_page()
    {
        ?>
    		<div class="wrap">
    		  <h2><?php _e('Posts Per Page', BPPP_PLUGIN_DOMAIN); ?></h2>

    		  <form method="post" action="options.php">
    		      <?php settings_fields(BPPP_PLUGIN_DOMAIN); ?>
              <?php do_settings_sections(BPPP_PLUGIN_DOMAIN); ?>
    		      <table class="form-table">
                  <tr>
                      <th scope="row"><label for="posts_per_page"><?php _e('Blog pages show at most', BPPP_PLUGIN_DOMAIN); ?></label></th>
                      <td>
                          <input name="posts_per_page" type="number" step="1" min="1" id="posts_per_page" value="<?php echo get_option('posts_per_page') ?>" class="small-text"> <?php _e('posts', BPPP_PLUGIN_DOMAIN); ?>
                          <p class="description"><?php _e('Please note this option is in sync with one in the <a href="/wp-admin/options-reading.php">reading options</a>', BPPP_PLUGIN_DOMAIN); ?>.</p>
                      </td>
                  </tr>
                  <?php if ($this->get_posttypes()) : ?>
                      <tr>
                          <th><h4><?php _e('Custom Post Types', BPPP_PLUGIN_DOMAIN); ?>:</h4></th>
                      </tr>
                      <?php foreach ($this->get_posttypes() as $post_type) : ?>
                          <tr>
                              <th scope="row">
                                  <label for="<?php echo BPPP_PLUGIN_PREFIX . $post_type; ?>">
                                      <?php _e(ucwords(str_replace('_', ' ', $post_type)), BPPP_PLUGIN_DOMAIN); ?>
                                  </label>
                              </th>
                              <td>
                                  <input name="<?php echo BPPP_PLUGIN_PREFIX ?><?php echo $post_type; ?>" type="number" step="1" min="1" id="<?php echo BPPP_PLUGIN_PREFIX; ?><?php echo $post_type; ?>" value="<?php echo get_option(BPPP_PLUGIN_PREFIX . $post_type); ?>" class="small-text"> <?php _e('posts', BPPP_PLUGIN_DOMAIN); ?>
                              </td>
                          </tr>
                      <?php endforeach; ?>
                  <?php endif; ?>
    		      </table>
    		      <?php submit_button(); ?>
    		  </form>
    		</div>
    	<?php

    }

    /**
     * remove unwanted menus
     * @since 1.0
     */
    public function manage_menus()
    {
        // add menu pages
        add_submenu_page('options-general.php', __('Posts Per Page', BPPP_PLUGIN_DOMAIN), __('Posts Per Page', BPPP_PLUGIN_DOMAIN), 'manage_options', 'ppp-settings', array($this, 'settings_page'));
    }

    /**
     * plugin action links
     *
     * @since 1.0
    */
    public function action_links($links)
    {
        $links[] = '<a href="'. get_admin_url(null, 'options-general.php?page=ppp-settings') .'">'._x('Settings', 'Plugin Settings link', BPPP_PLUGIN_DOMAIN).'</a>';
        $links[] = '<a target="_blank" href="http://benchmark.co.uk/contact-us/">'._x('Support', 'Plugin Support link', BPPP_PLUGIN_DOMAIN).'</a>';
        return $links;
    }

    /**
     * custom posts per page
     * @since 1.0
     */
    public function posts_per_page($query)
    {
        // validate query
        if (is_admin() || ! $query->is_main_query()) {
            return;
        }

        // make sure it's one of our posts types
        if (isset($query->query_vars['post_type']) && in_array($query->query_vars['post_type'], $this->get_posttypes())) {
            // adjust posts per page
            $query->query_vars['posts_per_page'] = get_option(BPPP_PLUGIN_PREFIX . $query->query_vars['post_type']);
        }

        // return query
        return $query;
    }

    /**
     * update options
     * @since 1.0
     */
    public function update_options()
    {
        // validate nonce field
        if (! isset($_POST['_wpnonce']) || ! wp_verify_nonce($_POST['_wpnonce'], BPPP_PLUGIN_DOMAIN . '-options')) {
            return false;
        }

        extract($_POST);

        // We need to "manually" update posts_per_page option as this will not be
        // automatically updated by submiting form to options.php:
        //
        // "For existing options `$autoload` can only be updated using
        // `update_option()` if `$value` is also changed. Accepts
        // 'yes' or true to enable, 'no' or false to disable.
        // For non-existent options, the default value is 'yes'.
        if (isset($posts_per_page)) {
            update_option('posts_per_page', $posts_per_page);
        }

        // don't do anything, let the other options update.
    }
}

// init
$Benchmark_Posts_Per_Page = new Benchmark_Posts_Per_Page();
