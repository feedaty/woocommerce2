<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://profiles.wordpress.org/acmemediakits/
 * @since             1.0.0
 * @package           Feedaty_Woocommerce_Rating
 *
 * @wordpress-plugin
 * Plugin Name:       Feedaty Opinioni Certificate
 * Plugin URI:        https://www.feedaty.com/
 * Description:       The new Feedaty Rating for WooCommerce will let you easily connect your shop to Feedaty
 * Version:           1.0.2
 * Author:            Mirko Bianco
 * Author URI:        https://profiles.wordpress.org/acmemediakits/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       feedaty-rating-for-woocommerce
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'FEEDATY_WOOCOMMERCE_RATING_VERSION', '1.0.2' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-feedaty-rating-for-woocommerce-activator.php
 */
function activate_feedaty_woocommerce_rating() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-feedaty-rating-for-woocommerce-activator.php';
	Feedaty_Woocommerce_Rating_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-feedaty-rating-for-woocommerce-deactivator.php
 */
function deactivate_feedaty_woocommerce_rating() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-feedaty-rating-for-woocommerce-deactivator.php';
	Feedaty_Woocommerce_Rating_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_feedaty_woocommerce_rating' );
register_deactivation_hook( __FILE__, 'deactivate_feedaty_woocommerce_rating' );


/**
 * ACME Form Builder functions
 */
require plugin_dir_path( __FILE__ ) . 'includes/acme_form_builder.php';

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-feedaty-rating-for-woocommerce.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_feedaty_woocommerce_rating() {

	$plugin = new Feedaty_Woocommerce_Rating();
	$plugin->run();

}
run_feedaty_woocommerce_rating();
