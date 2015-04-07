<?php

/**
 *
 * @wordpress-plugin
 * Plugin Name:       PayPal Easy Digital Shop
 * Plugin URI:        http://webs-spider.com/
 * Description:       Easy Digital Shop plugin for selling digital downloads via PayPal payment gateway. Easy Digital Shop is easy to used and easy to integration.
 * Version:           1.0.2
 * Author:            mbjtechnolabs
 * Author URI:        http://webs-spider.com/
 * License:           GNU General Public License v3.0
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       easy-digital-shop
 * Domain Path:       /languages
 */
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

 define("EDS_DIR_CONTENT", WP_CONTENT_DIR . "/easy-digital-shop");
 
if (!defined('EDS_FILE_PERMISSIONS')) {
    define('EDS_FILE_PERMISSIONS', 0666);
}

$upload_dir = wp_upload_dir();
if (!defined('EDS_DIR')) {
    define('EDS_DIR', $upload_dir['basedir'] . '/easy_digital_shop_uploads');
}

/**
 *  define PIW_PLUGIN_DIR constant for global use
 */
if (!defined('EDS_PLUGIN_DIR'))
    define('EDS_PLUGIN_DIR', dirname(__FILE__));

/**
 * define PIW_PLUGIN_URL constant for global use
 */
if (!defined('EDS_PLUGIN_URL'))
    define('EDS_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 *  define log file path
 */
if (!defined('EASY_DIGITAL_SHOP_LOG_DIR')) {
    define('EASY_DIGITAL_SHOP_LOG_DIR', ABSPATH . 'easy-digital-shop/');
}

/**
 * define plugin basename
 */
if (!defined('EDS_PLUGIN_BASENAME')) {
    define('EDS_PLUGIN_BASENAME', plugin_basename(__FILE__));
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-easy-digital-shop-activator.php
 */
function activate_easy_digital_shop() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-easy-digital-shop-activator.php';
    Easy_Digital_Shop_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-easy-digital-shop-deactivator.php
 */
function deactivate_easy_digital_shop() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-easy-digital-shop-deactivator.php';
    Easy_Digital_Shop_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_easy_digital_shop');
register_deactivation_hook(__FILE__, 'deactivate_easy_digital_shop');

/**
 * The core plugin class that is used to define internationalization,
 * dashboard-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-easy-digital-shop.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_easy_digital_shop() {

    $plugin = new Easy_Digital_Shop();
    $plugin->run();
}

run_easy_digital_shop();
