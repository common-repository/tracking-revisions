<?php

/**
 * Plugin Name: Tracking Revisions
 * Plugin URI: https://wordpress.org/plugins/tracking-revisions/
 * Description: Tracking all activities of the website
 * Version: 1.5.1
 * Author: Hien Nguyen Duy
 * Author URI: https://www.linkedin.com/in/hiennguyenduy/
 * Domain Path: languages
 * Text Domain: tracking-revisions
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

// Plugin constants
define('TKR_PLUGIN', 'tracking-revisions');
define('TKR_VERSION', '1.5.1');

define('TKR_URL', plugin_dir_url(__FILE__));
define('TKR_DIR', plugin_dir_path(__FILE__));
define('TKR_BASENAME', plugin_basename(__FILE__));

// Requires version
define('TKR_SUPPORTED_WP_VERSION', version_compare(get_bloginfo('version'), '5.0', '>='));
define('TKR_SUPPORTED_PHP_VERSION', version_compare(phpversion(), '7.0', '>='));

load_plugin_textdomain('tracking-revisions', false, dirname(TKR_BASENAME) . '/languages');

// Checking compatibility
if (TKR_SUPPORTED_WP_VERSION && TKR_SUPPORTED_PHP_VERSION) {
    // load: Func
    require_once TKR_DIR . '/inc/helper.php';

    // plugin: Init
    require_once TKR_DIR . '/admin/class-admin.php';
    $tkr_admin = new TKR_Admin();
    $tkr_admin->init();

    // get tracking
    $tkr_tracking = get_site_option('tkr_tracking', ['post_page_custom' => 1, 'taxonomy_custom' => 1]);

    // tracking (Post / Page / Custom Post Type): Init
    $post_page_custom = (isset($tkr_tracking['post_page_custom'])) ? $tkr_tracking['post_page_custom'] : 0;
    if ($post_page_custom) {
        require_once TKR_DIR . '/admin/class-tracking-posts.php';
        $tkr_tracking_posts = new TKR_Tracking_Posts();
        $tkr_tracking_posts->init();
    }
} else {
    // disable: Plugin Tracking Revisions
    add_action('admin_head', 'tkr_fail_notices');
    function tkr_fail_notices()
    {
        if (!TKR_SUPPORTED_WP_VERSION) {
            echo '<div class="error"><p>' . __('TRACKING REVISIONS requires WordPress 5.0 or higher. Please upgrade WordPress and activate the TRACKING REVISIONS plugin again.', 'tracking-revisions') . '</p></div>';
        }
        if (!TKR_SUPPORTED_PHP_VERSION) {
            echo '<div class="error"><p>' . __('TRACKING REVISIONS requires PHP 7.0 or higher. Please upgrade PHP and activate the TRACKING REVISIONS plugin again.', 'tracking-revisions') . '</p></div>';
        }
    }
}
