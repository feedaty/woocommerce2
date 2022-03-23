<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://profiles.wordpress.org/acmemediakits/
 * @since      1.0.0
 *
 * @package    Feedaty_Woocommerce_Rating
 * @subpackage Feedaty_Woocommerce_Rating/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Feedaty_Woocommerce_Rating
 * @subpackage Feedaty_Woocommerce_Rating/includes
 * @author     Mirko Bianco <mirko@acmemk.com>
 */
class Feedaty_Woocommerce_Rating_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		$option_name = 'feedaty-rating-for-woocommerce';
		
		delete_option($option_name);

// for site options in Multisite
		delete_site_option($option_name);
	}

}
