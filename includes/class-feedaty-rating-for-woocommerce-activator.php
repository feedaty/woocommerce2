<?php

/**
 * Fired during plugin activation
 *
 * @link       https://profiles.wordpress.org/acmemediakits/
 * @since      1.0.0
 *
 * @package    Feedaty_Woocommerce_Rating
 * @subpackage Feedaty_Woocommerce_Rating/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Feedaty_Woocommerce_Rating
 * @subpackage Feedaty_Woocommerce_Rating/includes
 * @author     Mirko Bianco <mirko@acmemk.com>
 */
class Feedaty_Woocommerce_Rating_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		$option_name = 'feedaty-rating-for-woocommerce';
		add_option( $option_name, array () );
	}

}
