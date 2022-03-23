<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://profiles.wordpress.org/acmemediakits/
 * @since      1.0.0
 *
 * @package    Feedaty_Woocommerce_Rating
 * @subpackage Feedaty_Woocommerce_Rating/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Feedaty_Woocommerce_Rating
 * @subpackage Feedaty_Woocommerce_Rating/includes
 * @author     Mirko Bianco <mirko@acmemk.com>
 */
class Feedaty_Woocommerce_Rating_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'feedaty-rating-for-woocommerce',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
