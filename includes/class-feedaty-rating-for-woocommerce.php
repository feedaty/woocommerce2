<?php
	
	/**
	 * The file that defines the core plugin class
	 *
	 * A class definition that includes attributes and functions used across both the
	 * public-facing side of the site and the admin area.
	 *
	 * @link       https://profiles.wordpress.org/acmemediakits/
	 * @since      1.0.0
	 *
	 * @package    Feedaty_Woocommerce_Rating
	 * @subpackage Feedaty_Woocommerce_Rating/includes
	 */
	
	/**
	 * The core plugin class.
	 *
	 * This is used to define internationalization, admin-specific hooks, and
	 * public-facing site hooks.
	 *
	 * Also maintains the unique identifier of this plugin as well as the current
	 * version of the plugin.
	 *
	 * @since      1.0.0
	 * @package    Feedaty_Woocommerce_Rating
	 * @subpackage Feedaty_Woocommerce_Rating/includes
	 * @author     Mirko Bianco <mirko@acmemk.com>
	 */
	class Feedaty_Woocommerce_Rating {
		
		/**
		 * The loader that's responsible for maintaining and registering all hooks that power
		 * the plugin.
		 *
		 * @since    1.0.0
		 * @access   protected
		 * @var      Feedaty_Woocommerce_Rating_Loader    $loader    Maintains and registers all hooks for the plugin.
		 */
		protected $loader;
		
		/**
		 * The unique identifier of this plugin.
		 *
		 * @since    1.0.0
		 * @access   protected
		 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
		 */
		protected $plugin_name;
		
		/**
		 * The current version of the plugin.
		 *
		 * @since    1.0.0
		 * @access   protected
		 * @var      string    $version    The current version of the plugin.
		 */
		protected $version;
		
		public $old_feedaty_plugin;
		
		protected   $legacy_options;
		
		/**
		 * Define the core functionality of the plugin.
		 *
		 * Set the plugin name and the plugin version that can be used throughout the plugin.
		 * Load the dependencies, define the locale, and set the hooks for the admin area and
		 * the public-facing side of the site.
		 *
		 * @since    1.0.0
		 */
		public function __construct() {
			if ( defined( 'FEEDATY_WOOCOMMERCE_RATING_VERSION' ) ) {
				$this->version = FEEDATY_WOOCOMMERCE_RATING_VERSION;
			} else {
				$this->version = '1.0.2';
			}
			$this->plugin_name = 'feedaty-rating-for-woocommerce';
			$this->legacy_options = sprintf( '%s-legacy', $this->plugin_name );
			
			$this->load_dependencies();
			$this->set_locale();
			$this->define_admin_hooks();
			$this->define_public_hooks();
			
			$this->old_feedaty_plugin = 'feedaty/Feedaty.php';
			
		}
		
		
		/**
		 * Load the required dependencies for this plugin.
		 *
		 * Include the following files that make up the plugin:
		 *
		 * - Feedaty_Woocommerce_Rating_Loader. Orchestrates the hooks of the plugin.
		 * - Feedaty_Woocommerce_Rating_i18n. Defines internationalization functionality.
		 * - Feedaty_Woocommerce_Rating_Admin. Defines all hooks for the admin area.
		 * - Feedaty_Woocommerce_Rating_Public. Defines all hooks for the public side of the site.
		 *
		 * Create an instance of the loader which will be used to register the hooks
		 * with WordPress.
		 *
		 * @since    1.0.0
		 * @access   private
		 */
		private function load_dependencies() {
			
			/**
			 * The class responsible for orchestrating the actions and filters of the
			 * core plugin.
			 */
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-feedaty-rating-for-woocommerce-loader.php';
			
			/**
			 * The class responsible for defining internationalization functionality
			 * of the plugin.
			 */
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-feedaty-rating-for-woocommerce-i18n.php';
			
			/**
			 * The class responsible for defining all actions that occur in the admin area.
			 */
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-feedaty-rating-for-woocommerce-admin.php';
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-feedaty-rating-for-woocommerce-csv_exporter.php';
			/**
			 * The class responsible for defining the API connection to main Feedaty Server.
			 */
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-feedaty-rating-for-woocommerce-webservice.php';
			/**
			 * The class responsible for defining all actions that occur in the public-facing
			 * side of the site.
			 */
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-feedaty-rating-for-woocommerce-public.php';
			
			$this->loader = new Feedaty_Woocommerce_Rating_Loader();
			
		}
		
		/**
		 * Define the locale for this plugin for internationalization.
		 *
		 * Uses the Feedaty_Woocommerce_Rating_i18n class in order to set the domain and to register the hook
		 * with WordPress.
		 *
		 * @since    1.0.0
		 * @access   private
		 */
		private function set_locale() {
			
			$plugin_i18n = new Feedaty_Woocommerce_Rating_i18n();
			
			$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
			
		}
		
		/**
		 * Register all of the hooks related to the admin area functionality
		 * of the plugin.
		 *
		 * @since    1.0.0
		 * @access   private
		 */
		private function define_admin_hooks() {
			
			$plugin_admin = new Feedaty_Woocommerce_Rating_Admin( $this->get_plugin_name(), $this->get_version(), $this->legacy_options );
			
			$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
			$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
			
			// Add Menu Items
			$this->loader->add_action('admin_menu',$plugin_admin,'add_plugin_admin_menu');
			
			// Add Widget
			$this->loader->add_action('widgets_init', $plugin_admin,'add_plugin_widgets');
			
			// Add Copyrights disclaimer
			$this->loader->add_action('feedaty_fwr__copyrights', $plugin_admin, 'plugin_copyrights');
			
			/**
			 * Add Settings link to the plugin
			 */
			$plugin_basename = plugin_basename( plugin_dir_path( __DIR__ ) . $this->plugin_name . '.php' );
			$this->loader->add_filter( 'plugin_action_links_' . $plugin_basename, $plugin_admin, 'add_action_links' );
			
			/**
			 * Save/Update plugin options
			 */
			$this->loader->add_action( 'admin_init', $plugin_admin, 'options_update' );
			
			/**
			 * Checks i legacy plugin is installed
			 * Copies all settings
			 * Disables legacy plugin
			 */
			$this->loader->add_action( 'plugin_loaded', $this, 'be_unique', 99 );
		}
		
		/**
		 * Register all of the hooks related to the public-facing functionality
		 * of the plugin.
		 *
		 * @since    1.0.0
		 * @access   private
		 */
		private function define_public_hooks() {
			
			$plugin_public = new Feedaty_Woocommerce_Rating_Public( $this->get_plugin_name(), $this->get_version() );
			
			$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
			$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
			
			// triggers Microdata for product
			$this->loader->add_filter( 'woocommerce_structured_data_product', $plugin_public, 'append_microdata' );
		}
		
		/**
		 * Run the loader to execute all of the hooks with WordPress.
		 *
		 * @since    1.0.0
		 */
		public function run() {
			$this->loader->run();
		}
		
		/**
		 * The name of the plugin used to uniquely identify it within the context of
		 * WordPress and to define internationalization functionality.
		 *
		 * @since     1.0.0
		 * @return    string    The name of the plugin.
		 */
		public function get_plugin_name() {
			return $this->plugin_name;
		}
		
		/**
		 * The reference to the class that orchestrates the hooks with the plugin.
		 *
		 * @since     1.0.0
		 * @return    Feedaty_Woocommerce_Rating_Loader    Orchestrates the hooks of the plugin.
		 */
		public function get_loader() {
			return $this->loader;
		}
		
		/**
		 * Retrieve the version number of the plugin.
		 *
		 * @since     1.0.0
		 * @return    string    The version number of the plugin.
		 */
		public function get_version() {
			return $this->version;
		}
		
		/**
		 * Checks legacy plugin.
		 *
		 * @since     1.0.0
		 * @return    boolean    false.
		 */
		public function be_unique () {
			// Loads wp-plugins dependencies
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			if ( is_admin() && is_plugin_active( $this->old_feedaty_plugin ) ) {
				$default_position = 'woocommerce_before_add_to_cart_button';
				$legacy_badge_positions = array (
					2  => 'woocommerce_single_product_summary',
					3  => 'woocommerce_before_add_to_cart_form',
					5  => $default_position,
					11 => 'woocommerce_after_add_to_cart_form',
					13 => 'woocommerce_product_meta_end'
				);
				$boolVals = array ( 'no' => 0, 'yes' => 1 );
				$options = array (
					//MAIN SETTINGS
					'merchant_code'         => get_option( 'feedaty-merchant-code' ),
					'client_secret'         => get_option( 'feedaty-client-secret' ),
					'send_order_trigger'    => sprintf( 'wc-%s', get_option( 'fdOrderStatus' ) ),
					'product_microdata'     => intval($boolVals[get_option( "feedaty-prod-microdata-enabled" )]),
					'product_reviews_tab'   => intval($boolVals[get_option( "feedaty-prod-tab-enabled" )]),
					// STORE BADGE
					'store_badge_enabled'   => intval($boolVals[get_option( 'wid-store-enabled' )]),
					'store_badge_id'        => get_option( 'wid-store-style' ),
					//PRODUCT BADGE
					'product_badge_enabled' => intval($boolVals[get_option( 'prod-enabled' )]),
					'product_badge_hook'    => in_array( get_option( 'prod-position' ), $legacy_badge_positions ) ? $legacy_badge_positions[get_option( 'prod-position' )] : $default_position,
					'product_badge_id'      => get_option( 'product-badge-style' )
				);
				register_setting( $this->legacy_options, $this->legacy_options, $options );
				deactivate_plugins( $this->old_feedaty_plugin );
			}
			
			return false;
		}
	}
