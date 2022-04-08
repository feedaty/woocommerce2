<?php
	
	/**
	 * The admin-specific functionality of the plugin.
	 *
	 * @link       https://profiles.wordpress.org/acmemediakits/
	 * @since      1.0.0
	 *
	 * @package    Feedaty_Woocommerce_Rating
	 * @subpackage Feedaty_Woocommerce_Rating/admin
	 * @author     Mirko Bianco <mirko@acmemk.com>
	 */
	class Feedaty_Woocommerce_Rating_Admin
	{
		
		/**
		 * The ID of this plugin.
		 *
		 * @since    1.0.0
		 * @access   private
		 * @var      string $plugin_name The ID of this plugin.
		 */
		private $plugin_name;
		/**
		 * The version of this plugin.
		 *
		 * @since    1.0.0
		 * @access   private
		 * @var      string $version The current version of this plugin.
		 */
		private $version;
		/**
		 * Previous plugin settings collection name.
		 *
		 * @since    1.0.0
		 * @access   private
		 * @var      string $legacy Previous plugin settings collection name.
		 */
		private $legacy;
		/**
		 * Shorthand for the date_format string.
		 *
		 * @since    1.0.0
		 * @access   private
		 * @var      string $wp_date_format date_format string
		 */
		private $wp_date_format;
		/**
		 * The locale sttings of this plugin.
		 *
		 * @since    1.0.0
		 * @access   private
		 * @var      string $locale The locale sttings for data displaying from API.
		 */
		private $locale;
		/**
		 * The options for this plugin.
		 *
		 * @since    1.0.0
		 * @access   public
		 * @var      string $options The options for this plugin.
		 */
		public $options;
		private $log;
		
		/**
		 * Initialize the class and set its properties.
		 *
		 * @param string $plugin_name The name of this plugin.
		 * @param string $version     The version of this plugin.
		 *
		 * @since    1.0.0
		 */
		public function __construct ( $plugin_name, $version, $legacy ) {
			$this->log = false;
			//call_user_func(array($this, 'logger'), ['log start'] );
			$this->plugin_name = $plugin_name;
			$this->version = $version;
			$this->legacy = $legacy;
			$this->wp_date_format = get_option( 'date_format' );
			$this->locale = get_locale() == 'it_IT' ? 'it-IT' : 'en-US';
			// Retrieve WooComemrce order statuses
			add_action( 'woocommerce_loaded', array ( $this, 'list_order_statuses' ) );
			
			// Create filter for listing hooks inside product page, this list can be expanded in funkctions.php
			add_filter( $this->plugin_name . '_product_badge_hooks', array ( $this, 'product_badge_hooks' ) );
			// Create filter for importing legacy settings
			add_filter( "{$this->plugin_name}_heritage", array ( $this, 'heritage' ) );
			
			// Retrieve plugin options and apply legacy settings
			$this->options = apply_filters( "{$this->plugin_name}_heritage", get_option( $this->plugin_name ) );
			//do_action( 'qm/debug', add_query_arg( $this->options, 'https://widget.zoorate.com/go.php') );
			//do_action('qm/debug',$this->options);
			// Add trigger for sending orders completed to API
			if ( is_array( $this->options ) && isset( $this->options['send_order_trigger'] ) ) {
				$hook = str_replace( 'wc-', 'woocommerce_order_status_', $this->options['send_order_trigger'] );
				add_action( $hook, array ( $this, 'send_order' ), 10, 1 );
			}
		}
		
		public function guiLang () {
			return [
				'all'   => __( 'Default Language' ),
				'it-IT' => __( 'Italian', $this->plugin_name ),
				'en-US' => __( 'English US', $this->plugin_name ),
				'es-ES' => __( 'Spanish (Spain)', $this->plugin_name ),
				'fr-FR' => __( 'French', $this->plugin_name ),
				'de-DE' => __( 'German', $this->plugin_name )
			];
		}
		
		public function langLang () {
			return [
				'all'   => __( 'All Languages' ),
				'it-IT' => __( 'Italian', $this->plugin_name ),
				'en-US' => __( 'English US', $this->plugin_name ),
				'es-ES' => __( 'Spanish (Spain)', $this->plugin_name ),
				'fr-FR' => __( 'French', $this->plugin_name ),
				'de-DE' => __( 'German', $this->plugin_name )
			];
		}
		
		/**
		 * Saves options
		 *
		 * @return false
		 */
		public function options_update () {
			register_setting( $this->plugin_name, $this->plugin_name, array ( 'type' => 'array', 'sanitize_callback' => array ( $this, 'validate' ) ) );
		}
		
		/**
		 * Validates data before saving
		 *
		 * @param $input
		 *
		 * @return mixed|string|void
		 */
		public function validate ( $input ) {
			$valid = $this->options;
			if ( empty( $valid ) || !is_array($valid) ) {
				$valid = [];
			}
			if ( is_array( $input ) && isset( $input['page_id'] ) ) {
				$collections = call_user_func( array ( $this, $input['page_id'] ) );
				foreach ( $collections as $k => $collection ) {
					$key = is_array( $collection['name'] ) ? $collection['name'][0] : $collection['name'];
					$value = isset( $input[$key] ) ? $input[$key] : null;
					if ( $k !== 'page_id' ) {
						if ( $k == 'delete_transient' ) {
							delete_transient( $this->plugin_name );
						}
						else {
							$valid[$key] = call_user_func( $collection['validate'], $value );
						}
					}
				}
			}
			
			return $valid;
		}
		
		/**
		 * Merges legacy options with plugin options
		 *
		 * @param $hereafter
		 *
		 * @return array|mixed
		 */
		public function heritage ( $hereafter ) {
			if ( $checkOpt = get_option( $this->legacy ) ) {
				$legacy = $checkOpt['legacy'];
				if ( is_array( $legacy ) ) {
					if ( is_array( $hereafter ) ) {
						foreach ( $hereafter as $k => $v ) {
							if ( isset( $legacy[$k] ) && empty( $v ) ) {
								$hereafter[$k] = $legacy[$k];
							}
						}
					}
					else {
						$hereafter = $legacy;
					}
				}
				
			}
			
			return $hereafter;
		}
		
		/**
		 * My empty response
		 *
		 * @return false
		 */
		public function acme_false () {
			return false;
		}
		
		/**
		 * Register the stylesheets for the admin area.
		 *
		 * @since    1.0.0
		 */
		public function enqueue_styles () {
			wp_enqueue_style( 'jquery-ui-custom', plugin_dir_url( __FILE__ ) . 'css/jquery-ui.min.css', [], $this->version, 'all' );
			wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/feedaty-rating-for-woocommerce-admin.css', array ('jquery-ui-custom'), $this->version, 'all' );
			
		}
		
		/**
		 * Register the JavaScript for the admin area.
		 *
		 * @since    1.0.0
		 */
		public function enqueue_scripts () {
			wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/feedaty-rating-for-woocommerce-admin.js', array ( 'jquery', 'jquery-ui-datepicker' ), time()/*$this->version*/, true );
			wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/acme_image_uploader.js', array ( 'jquery' ), $this->version, true );
			
		}
		
		/**
		 * Add settings action link to the plugins page.
		 *
		 * @since    1.0.0
		 */
		public function add_action_links ( $links ) {
			$settings_link = array (
				'<a href="' . admin_url( 'admin.php?page=feedaty_fwr_main_options' ) . '">' . __( 'Settings', $this->plugin_name ) . '</a>',
			);
			
			return array_merge( $settings_link, $links );
		}
		
		/**
		 * Main Feedaty menu container
		 *
		 * @return false
		 */
		public function create_feedaty_admin_menu () {
			global $admin_page_hooks;
			if ( !isset( $admin_page_hooks['feedaty_fwr_panel'] ) ) {
				add_menu_page(
					'Feedaty Opinioni Certificate',
					'Feedaty WR',
					'manage_options',
					'feedaty_fwr_panel',
					null,
					sprintf( '%s/%s/assets/feedaty.png', plugins_url(), $this->plugin_name )
				);
			}
			
			return false;
		}
		
		/**
		 * SubMenu
		 *
		 */
		public function add_plugin_admin_menu () {
			$parent = 'feedaty_fwr_panel';
			$this->create_feedaty_admin_menu();
			
			// Main Plugin settings submenu:
			add_submenu_page(
				$parent,
				__( 'Feedaty Global Settings', $this->plugin_name ),
				__( 'Global Settings', $this->plugin_name ),
				'manage_options',
				'feedaty_fwr_main_options',
				array ( $this, 'feedaty_fwr_main_options' )
			);
			
			// Store Badge settings submenu:
			add_submenu_page(
				$parent,
				__( 'Feedaty Store Badge', $this->plugin_name ),
				__( 'Store Badge', $this->plugin_name ),
				'manage_options',
				'feedaty_fwr_store_badge',
				array ( $this, 'feedaty_fwr_store_badge' )
			);
			
			// Store Carousel settings submenu:
			add_submenu_page(
				$parent,
				__( 'Feedaty Store Carousel', $this->plugin_name ),
				__( 'Store Carousel', $this->plugin_name ),
				'manage_options',
				'feedaty_fwr_store_carousel',
				array ( $this, 'feedaty_fwr_store_carousel' )
			);
			
			// Product Badge settings submenu:
			add_submenu_page(
				$parent,
				__( 'Feedaty Product Badge', $this->plugin_name ),
				__( 'Product Badge', $this->plugin_name ),
				'manage_options',
				'feedaty_fwr_product_badge',
				array ( $this, 'feedaty_fwr_product_badge' )
			);
			
			// Product Carousel settings submenu:
			add_submenu_page(
				$parent,
				__( 'Feedaty Product Carousel', $this->plugin_name ),
				__( 'Product Carousel', $this->plugin_name ),
				'manage_options',
				'feedaty_fwr_product_carousel',
				array ( $this, 'feedaty_fwr_product_carousel' )
			);
			
			// Orders export:
			add_submenu_page(
				$parent,
				__( 'Feedaty Export Orders', $this->plugin_name ),
				__( 'Export', $this->plugin_name ),
				'manage_options',
				'feedaty_fwr_export',
				array ( $this, 'feedaty_fwr_export' )
			);
			
			// Remove parent empty menu page
			remove_submenu_page( $parent, $parent );
		}
		
		/**
		 * Creates New Widget
		 */
		public function add_plugin_widgets () {
			// Loads dependencies
			require_once( plugin_dir_path( __FILE__ ) . 'class-feedaty-rating-for-woocommerce-widget.php' );
			require_once( plugin_dir_path( __FILE__ ) . 'class-feedaty-rating-for-woocommerce-widget-carousel.php' );
			// Defines new Widget
			$widget = new class_feedaty_woocommerce_rating_widget( $this->plugin_name );
			register_widget( $widget );
			$wid_carousel = new class_feedaty_woocommerce_rating_widget_carousel( $this->plugin_name );
			register_widget( $wid_carousel );
		}
		
		/**
		 * Deploys Main Settings Page
		 */
		public function feedaty_fwr_main_options () {
			// Loads form data
			add_action( 'feedaty_fwr__global_settings', array ( $this, 'main_form_options' ), 10 );
			// Loads Template
			require_once( plugin_dir_path( __FILE__ ) . 'partials/feedaty-rating-for-woocommerce-admin-display.php' );
		}
		
		/**
		 * Parse Main Settings Form Fields
		 */
		public function main_form_options () {
			
			$fields = array ();
			// the list of order statuses must be called after WooCommerce init
			$main_setting_fields = $this->main_setting_fields();
			$main_setting_fields['send_order_trigger']['options'] = $this->order_statuses;
			// Passes the form array to the Acme Form Builder functions
			foreach ( $main_setting_fields as $main_settings_field ) {
				if ( $main_settings_field['filter'] == 'acme_fbuild__hidden' ) {
					$hidden[] = apply_filters( $main_settings_field['filter'], $main_settings_field );
				}
				else {
					$fields[] = apply_filters( $main_settings_field['filter'], $main_settings_field );
				}
			}
			// Prepends hidden fields
			foreach ( $hidden as $field ) {
				print( $field );
			}
			// Wraps form filed in html tags
			foreach ( $fields as $field ) {
				printf( '<p class="form-field main-form">%s</p>', $field );
			}
		}
		
		/**
		 * Defines form fields for the Main Settings Section
		 *
		 * @param array $append Additional fields for the filter
		 *
		 * @return array
		 * @since    1.0.0
		 */
		public function main_setting_fields ( $append = array () ) {
			$basic = array (
				'page_id'              => array (
					'filter'   => 'acme_fbuild__hidden',
					'name'     => array ( 'page_id' ),
					'value'    => 'main_setting_fields',
					'basename' => $this->plugin_name
				),
				'version'              => array (
					'filter'   => 'acme_fbuild__hidden',
					'name'     => array ( 'version' ),
					'value'    => $this->version,
					'validate' => 'sanitize_text_field',
					'basename' => $this->plugin_name
				),
				'delete_transient'     => array (
					'filter'   => 'acme_fbuild__hidden',
					'name'     => array ( 'delete_transient' ),
					'value'    => 1,
					'basename' => $this->plugin_name
				),
				'merchant_code'        => array (
					'filter'   => 'acme_fbuild__text',
					'label'    => __( 'Merchant Code', $this->plugin_name ),
					'name'     => array ( 'merchant_code' ),
					'value'    => isset( $this->options['merchant_code'] ) ? $this->options['merchant_code'] : null,
					'validate' => 'sanitize_text_field',
					'class'    => array ( 'small' ),
					'basename' => $this->plugin_name
				),
				'client_secret'        => array (
					'filter'   => 'acme_fbuild__text',
					'label'    => __( 'Client Secret', $this->plugin_name ),
					'name'     => array ( 'client_secret' ),
					'value'    => isset( $this->options['client_secret'] ) ? $this->options['client_secret'] : null,
					'validate' => 'sanitize_text_field',
					'class'    => array ( 'large' ),
					'basename' => $this->plugin_name
				),
				'guiLang'              => array (
					'filter'   => 'acme_fbuild__select',
					'label'    => __( 'User Interface Language', $this->plugin_name ),
					'name'     => array ( 'guiLang' ),
					'value'    => isset( $this->options['guiLang'] ) ? $this->options['guiLang'] : null,
					'options'  => $this->guiLang(),
					'validate' => 'sanitize_text_field',
					'basename' => $this->plugin_name
				),
				'langLang'             => array (
					'filter'   => 'acme_fbuild__select',
					'label'    => __( 'Reviews Language', $this->plugin_name ),
					'name'     => array ( 'langLang' ),
					'value'    => isset( $this->options['langLang'] ) ? $this->options['langLang'] : null,
					'options'  => $this->langLang(),
					'validate' => 'sanitize_text_field',
					'basename' => $this->plugin_name
				),
				'send_order_trigger'   => array (
					'filter'   => 'acme_fbuild__select',
					'label'    => __( 'Send Orders Trigger (API callback only)', $this->plugin_name ),
					'name'     => array ( 'send_order_trigger' ),
					'value'    => isset( $this->options['send_order_trigger'] ) ? $this->options['send_order_trigger'] : null,
					'validate' => 'sanitize_text_field',
					'class'    => array ( 'small' ),
					'basename' => $this->plugin_name
				),
				'product_microdata'    => array (
					'filter'   => 'acme_fbuild__checkbox',
					'label'    => __( 'Enable Product Microdata', $this->plugin_name ),
					'name'     => array ( 'product_microdata' ),
					'value'    => isset( $this->options['product_microdata'] ) ? $this->options['product_microdata'] : 0,
					'validate' => 'intval',
					'basename' => $this->plugin_name
				),
				'product_reviews_tab'  => array (
					'filter'   => 'acme_fbuild__checkbox',
					'label'    => __( 'Enable Product Reviews Tab', $this->plugin_name ),
					'name'     => array ( 'product_reviews_tab' ),
					'value'    => isset( $this->options['product_reviews_tab'] ) ? intval( $this->options['product_reviews_tab'] ) : 0,
					'validate' => 'intval',
					'basename' => $this->plugin_name
				),
				'hide_default_reviews' => array (
					'filter'   => 'acme_fbuild__checkbox',
					'label'    => __( 'Hide Default WooCommerce Reviews Tab', $this->plugin_name ),
					'name'     => array ( 'hide_default_reviews' ),
					'value'    => isset( $this->options['hide_default_reviews'] ) ? $this->options['hide_default_reviews'] : 0,
					'validate' => 'intval',
					'basename' => $this->plugin_name
				)
			);
			
			return array_merge( $basic, $append );
		}
		
		/**
		 * Retrieves raw list of orders atatuses from WooCommerce
		 *
		 * @return array
		 * @since    1.0.0
		 */
		public function list_order_statuses () {
			$this->order_statuses = wc_get_order_statuses();
		}
		
		/**
		 * Deploys Product Badge Settings
		 */
		public function feedaty_fwr_product_badge () {
			// Loads form data
			add_action( 'feedaty_fwr__product_badge', array ( $this, 'feedaty_fwr_product_badge_form' ), 10 );
			// Loads Template
			require_once( plugin_dir_path( __FILE__ ) . 'partials/feedaty-rating-for-woocommerce-product_badge.php' );
		}
		
		/**
		 * Parse Product Badge Settings Form Fields
		 */
		public function feedaty_fwr_product_badge_form () {
			$fields = array ();
			$class = array ();
			// Passes the form array to the Acme Form Builder functions
			foreach ( $this->product_badge_fields() as $product_badge_field ) {
				if ( $product_badge_field['filter'] == 'acme_fbuild__hidden' ) {
					$hidden[] = apply_filters( $product_badge_field['filter'], $product_badge_field );
				}
				else {
					$class[] = isset( $product_badge_field['class'] ) ? $product_badge_field['class'] : array ();
					$fields[] = apply_filters( $product_badge_field['filter'], $product_badge_field );
				}
			}
			// Prepends hidden fields
			foreach ( $hidden as $field ) {
				print( $field );
			}
			// Wraps form filed in html tags
			foreach ( $fields as $index => $field ) {
				printf( '<p class="form-field product_badge %s">%s</p>', implode( ' ', $class[$index] ), $field );
			}
		}
		
		/**
		 * Defines form fields for the Product Badge Section
		 *
		 * @return array
		 * @since    1.0.0
		 */
		public function product_badge_fields () {
			$res = array ();
			$res['page_id'] = array (
				'filter'   => 'acme_fbuild__hidden',
				'name'     => array ( 'page_id' ),
				'value'    => 'product_badge_fields',
				'basename' => $this->plugin_name
			);
			$res['product_badge_enabled'] = array (
				'filter'   => 'acme_fbuild__checkbox',
				'label'    => __( 'Enable Product Badge', $this->plugin_name ),
				'name'     => array ( 'product_badge_enabled' ),
				'value'    => isset( $this->options['product_badge_enabled'] ) ? $this->options['product_badge_enabled'] : 0,
				'validate' => 'intval',
				'basename' => $this->plugin_name
			);
			$selectValues = apply_filters( $this->plugin_name . '_product_badge_hooks', array () );
			$res['product_badge_hook'] = array (
				'filter'   => 'acme_fbuild__select',
				'label'    => __( 'Position (WC hook)', $this->plugin_name ),
				'name'     => array ( 'product_badge_hook' ),
				'value'    => isset( $this->options['product_badge_hook'] ) ? $this->options['product_badge_hook'] : null,
				'options'  => $selectValues,
				'validate' => 'sanitize_text_field',
				'basename' => $this->plugin_name
			);
			foreach ( $this->feedaty_fwr_badge_list( 'product' ) as $item ) {
				$label = sprintf(
					'<span class="wrap"><strong>%s</strong><br>%s<br>%s</span><span class="wrap"><img src="%s" /></span>',
					$item['label'],
					$item['description'],
					$item['size'],
					esc_attr( $item['thumb'] )
				);
				$res[$item['id']] = array (
					'filter'     => 'acme_fbuild__radio',
					'label'      => $label,
					'show_colon' => false,
					'name'       => array ( 'product_badge_id' ),
					'value'      => esc_attr( $item['value'] ),
					'compare'    => isset( $this->options['product_badge_id'] ) ? $this->options['product_badge_id'] : null,
					'validate'   => 'sanitize_text_field',
					'basename'   => $this->plugin_name,
					'class'      => array ( 'custom_radio' )
				);
				$res['script'] = array (
					'filter'   => 'acme_fbuild__hidden',
					'name'     => array ( 'script' ),
					'value'    => $item['script'],
					'basename' => $this->plugin_name,
					'validate' => 'esc_url_raw'
				);
			}
			
			return $res;
		}
		
		/**
		 * Deploys Product Carousel Settings
		 */
		public function feedaty_fwr_product_carousel () {
			// Loads form data
			add_action( 'feedaty_fwr__product_badge', array ( $this, 'feedaty_fwr_product_carousel_form' ), 10 );
			// Loads Template
			require_once( plugin_dir_path( __FILE__ ) . 'partials/feedaty-rating-for-woocommerce-product_badge.php' );
		}
		
		/**
		 * Parse Product Carousel Settings Form Fields
		 */
		public function feedaty_fwr_product_carousel_form () {
			$fields = array ();
			$class = array ();
			// Passes the form array to the Acme Form Builder functions
			foreach ( $this->product_carousel_fields() as $product_carousel_field ) {
				if ( $product_carousel_field['filter'] == 'acme_fbuild__hidden' ) {
					$hidden[] = apply_filters( $product_carousel_field['filter'], $product_carousel_field );
				}
				else {
					$class[] = isset( $product_carousel_field['class'] ) ? $product_carousel_field['class'] : array ();
					$fields[] = apply_filters( $product_carousel_field['filter'], $product_carousel_field );
				}
			}
			// Prepends hidden fields
			foreach ( $hidden as $field ) {
				print( $field );
			}
			// Wraps form filed in html tags
			foreach ( $fields as $index => $field ) {
				printf( '<p class="form-field product_carousel %s">%s</p>', implode( ' ', $class[$index] ), $field );
			}
		}
		
		/**
		 * Defines form fields for the Product Carousel Section
		 *
		 * @return array
		 * @since    1.0.0
		 */
		public function product_carousel_fields () {
			$res = array ();
			$res['page_id'] = array (
				'filter'   => 'acme_fbuild__hidden',
				'name'     => array ( 'page_id' ),
				'value'    => 'product_carousel_fields',
				'basename' => $this->plugin_name
			);
			$res['product_carousel_enabled'] = array (
				'filter'   => 'acme_fbuild__checkbox',
				'label'    => __( 'Enable Product Carousel', $this->plugin_name ),
				'name'     => array ( 'product_carousel_enabled' ),
				'value'    => isset( $this->options['product_carousel_enabled'] ) ? $this->options['product_carousel_enabled'] : 0,
				'validate' => 'intval',
				'basename' => $this->plugin_name
			);
			$selectValues = apply_filters( $this->plugin_name . '_product_badge_hooks', array () );
			$res['product_carousel_hook'] = array (
				'filter'   => 'acme_fbuild__select',
				'label'    => __( 'Position (WC hook)', $this->plugin_name ),
				'name'     => array ( 'product_carousel_hook' ),
				'value'    => isset( $this->options['product_carousel_hook'] ) ? $this->options['product_carousel_hook'] : null,
				'options'  => $selectValues,
				'validate' => 'sanitize_text_field',
				'basename' => $this->plugin_name
			);
			foreach ( $this->feedaty_fwr_badge_list( 'carouselproduct' ) as $item ) {
				$label = sprintf(
					'<span class="wrap"><strong>%s</strong><br>%s<br>%s</span><span class="wrap"><img src="%s" /></span>',
					$item['label'],
					$item['description'],
					$item['size'],
					esc_attr( $item['thumb'] )
				);
				$res[$item['id']] = array (
					'filter'     => 'acme_fbuild__radio',
					'label'      => $label,
					'show_colon' => false,
					'name'       => array ( 'product_carousel_id' ),
					'value'      => esc_attr( $item['value'] ),
					'compare'    => isset( $this->options['product_carousel_id'] ) ? $this->options['product_carousel_id'] : null,
					'validate'   => 'sanitize_text_field',
					'basename'   => $this->plugin_name,
					'class'      => array ( 'custom_radio' )
				);
				$res['script'] = array (
					'filter'   => 'acme_fbuild__hidden',
					'name'     => array ( 'script' ),
					'value'    => $item['script'],
					'basename' => $this->plugin_name,
					'validate' => 'esc_url_raw'
				);
			}
			
			return $res;
		}
		
		/**
		 * Defines a list of hooks from WooCommerce Single Page
		 *
		 * @param array $args Additional fields for the filter
		 *
		 * @return array
		 * @since    1.0.0
		 */
		public function product_badge_hooks ( $args ) {
			$default = array (
				'woocommerce_single_product_summary'    => __( 'Single Product Summary', $this->plugin_name ),
				'woocommerce_before_add_to_cart_form'   => __( 'Before Add To Cart Form', $this->plugin_name ),
				'woocommerce_before_add_to_cart_button' => __( 'Before Add To Cart Button', $this->plugin_name ),
				'woocommerce_after_add_to_cart_form'    => __( 'After Add To Cart Form', $this->plugin_name ),
				'woocommerce_product_meta_end'          => __( 'Product Meta End', $this->plugin_name ),
				'woocommerce_review_before'             => __( 'Before Reviews', $this->plugin_name )
			);
			
			return array_merge( $args, $default );
		}
		
		/**
		 * Deploys Store Badge Settings
		 */
		public function feedaty_fwr_store_badge () {
			// Loads form data
			add_action( 'feedaty_fwr__store_badge', array ( $this, 'feedaty_fwr_store_badge_form' ), 10 );
			// Loads Template
			require_once( plugin_dir_path( __FILE__ ) . 'partials/feedaty-rating-for-woocommerce-store_badge.php' );
		}
		
		/**
		 * Parse Store Badge Settings Form Fields
		 */
		public function feedaty_fwr_store_badge_form () {
			$fields = array ();
			$class = array ();
			// Passes the form array to the Acme Form Builder functions
			foreach ( $this->store_badge_fields() as $store_badge_field ) {
				if ( $store_badge_field['filter'] == 'acme_fbuild__hidden' ) {
					$hidden[] = apply_filters( $store_badge_field['filter'], $store_badge_field );
				}
				else {
					$class[] = isset( $store_badge_field['class'] ) ? $store_badge_field['class'] : array ();
					$fields[] = apply_filters( $store_badge_field['filter'], $store_badge_field );
				}
			}
			// Prepends hidden fields
			foreach ( $hidden as $field ) {
				print( $field );
			}
			// Wraps form filed in html tags
			foreach ( $fields as $index => $field ) {
				printf( '<p class="form-field store_badge %s">%s</p>', implode( ' ', $class[$index] ), $field );
			}
		}
		
		/**
		 * Defines form fields for the Product Badge Section
		 *
		 * @return array
		 * @since    1.0.0
		 */
		public function store_badge_fields () {
			$res = array ();
			$res['page_id'] = array (
				'filter'   => 'acme_fbuild__hidden',
				'name'     => array ( 'page_id' ),
				'value'    => 'store_badge_fields',
				'basename' => $this->plugin_name
			);
			$res['store_badge_enabled'] = array (
				'filter'   => 'acme_fbuild__checkbox',
				'label'    => __( 'Enable Store Badge', $this->plugin_name ),
				'name'     => array ( 'store_badge_enabled' ),
				'value'    => isset( $this->options['store_badge_enabled'] ) ? $this->options['store_badge_enabled'] : 0,
				'validate' => 'intval',
				'basename' => $this->plugin_name,
				'class'    => array ( 'custom_checkbox' )
			);
			foreach ( $this->feedaty_fwr_badge_list( 'merchant' ) as $item ) {
				$label = sprintf(
					'<span class="wrap"><strong>%s</strong><br>%s<br>%s</span><span class="wrap"><img src="%s" /></span>',
					$item['label'],
					$item['description'],
					$item['size'],
					esc_attr( $item['thumb'] )
				);
				
				$res[$item['id']] = array (
					'filter'     => 'acme_fbuild__radio',
					'label'      => $label,
					'show_colon' => false,
					'name'       => array ( 'store_badge_id' ),
					'value'      => esc_attr( $item['value'] ),
					'compare'    => isset( $this->options['store_badge_id'] ) ? $this->options['store_badge_id'] : null,
					'validate'   => 'sanitize_text_field',
					'basename'   => $this->plugin_name,
					'class'      => array ( 'custom_radio' )
				);
				$res['script'] = array (
					'filter'   => 'acme_fbuild__hidden',
					'name'     => array ( 'script' ),
					'value'    => $item['script'],
					'basename' => $this->plugin_name,
					'validate' => 'esc_url_raw'
				);
			}
			
			return $res;
		}
		
		/**
		 * Deploys Store Carousel Settings
		 */
		public function feedaty_fwr_store_carousel () {
			// Loads form data
			add_action( 'feedaty_fwr__store_badge', array ( $this, 'feedaty_fwr_store_carousel_form' ), 10 );
			// Loads Template
			require_once( plugin_dir_path( __FILE__ ) . 'partials/feedaty-rating-for-woocommerce-store_badge.php' );
		}
		
		/**
		 * Parse Store Badge Settings Form Fields
		 */
		public function feedaty_fwr_store_carousel_form () {
			$fields = array ();
			$class = array ();
			// Passes the form array to the Acme Form Builder functions
			foreach ( $this->store_carousel_fields() as $store_carousel_field ) {
				if ( $store_carousel_field['filter'] == 'acme_fbuild__hidden' ) {
					$hidden[] = apply_filters( $store_carousel_field['filter'], $store_carousel_field );
				}
				else {
					$class[] = isset( $store_carousel_field['class'] ) ? $store_carousel_field['class'] : array ();
					$fields[] = apply_filters( $store_carousel_field['filter'], $store_carousel_field );
				}
			}
			// Prepends hidden fields
			foreach ( $hidden as $field ) {
				print( $field );
			}
			// Wraps form filed in html tags
			foreach ( $fields as $index => $field ) {
				printf( '<p class="form-field store_carousel %s">%s</p>', implode( ' ', $class[$index] ), $field );
			}
		}
		
		/**
		 * Defines form fields for the Product Badge Section
		 *
		 * @return array
		 * @since    1.0.0
		 */
		public function store_carousel_fields () {
			$res = array ();
			$res['page_id'] = array (
				'filter'   => 'acme_fbuild__hidden',
				'name'     => array ( 'page_id' ),
				'value'    => 'store_carousel_fields',
				'basename' => $this->plugin_name
			);
			$res['store_carousel_enabled'] = array (
				'filter'   => 'acme_fbuild__checkbox',
				'label'    => __( 'Enable Store Carousel', $this->plugin_name ),
				'name'     => array ( 'store_carousel_enabled' ),
				'value'    => isset( $this->options['store_carousel_enabled'] ) ? $this->options['store_carousel_enabled'] : 0,
				'validate' => 'intval',
				'basename' => $this->plugin_name,
				'class'    => array ( 'custom_checkbox' )
			);
			foreach ( $this->feedaty_fwr_badge_list( 'carousel' ) as $item ) {
				$label = sprintf(
					'<span class="wrap"><strong>%s</strong><br>%s<br>%s</span><span class="wrap"><img src="%s" /></span>',
					$item['label'],
					$item['description'],
					$item['size'],
					esc_attr( $item['thumb'] )
				);
				
				$res[$item['id']] = array (
					'filter'     => 'acme_fbuild__radio',
					'label'      => $label,
					'show_colon' => false,
					'name'       => array ( 'store_carousel_id' ),
					'value'      => esc_attr( $item['value'] ),
					'compare'    => isset( $this->options['store_carousel_id'] ) ? $this->options['store_carousel_id'] : null,
					'validate'   => 'sanitize_text_field',
					'basename'   => $this->plugin_name,
					'class'      => array ( 'custom_radio' )
				);
				$res['script'] = array (
					'filter'   => 'acme_fbuild__hidden',
					'name'     => array ( 'script' ),
					'value'    => $item['script'],
					'basename' => $this->plugin_name,
					'validate' => 'esc_url_raw'
				);
			}
			
			return $res;
		}
		
		/**
		 * Deploys Export Options
		 */
		public function feedaty_fwr_export () {
			// Loads form data
			add_action( 'feedaty_fwr__form_export', array ( $this, 'form_export' ), 10 );
			// Loads Template
			require_once( plugin_dir_path( __FILE__ ) . 'partials/feedaty-rating-for-woocommerce-export.php' );
		}
		
		/**
		 * Parse Export Form
		 */
		public function form_export () {
			$fields = array ();
			// Loads Dependencies
			// Define Form Fields
			$form_fields = array (
				'date_format'          => array (
					'filter'   => 'acme_fbuild__hidden',
					'name'     => array ( 'date_format' ),
					'value'    => $this->wp_date_format,
					'validate' => 'sanitize_text_field',
					'basename' => $this->plugin_name
				),
				'orders_date_from'     => array (
					'filter'   => 'acme_fbuild__text',
					'label'    => __( 'From', $this->plugin_name ),
					'name'     => array ( 'orders_date_from' ),
					'value'    => null,
					'class'    => array ( 'small', 'date_picker' ),
					'basename' => $this->plugin_name
				),
				'orders_date_from_val' => array (
					'filter'   => 'acme_fbuild__hidden',
					'name'     => array ( 'orders_date_from_val' ),
					'value'    => null,
					'validate' => 'sanitize_text_field',
					'basename' => $this->plugin_name
				),
				'orders_date_to'       => array (
					'filter'   => 'acme_fbuild__text',
					'label'    => __( 'To', $this->plugin_name ),
					'name'     => array ( 'orders_date_to' ),
					'value'    => null,
					'class'    => array ( 'small', 'date_picker' ),
					'basename' => $this->plugin_name
				),
				'orders_date_to_val'   => array (
					'filter'   => 'acme_fbuild__hidden',
					'name'     => array ( 'orders_date_to_val' ),
					'value'    => null,
					'validate' => 'sanitize_text_field',
					'basename' => $this->plugin_name
				)
			);
			// On Submit runs export
			if ( isset( $_POST['submit'] ) ) {
				$export = new fwr_csv_exporter( $this->plugin_name, $this->version, $_POST );
				$export->export();
			}
			// Passes the form array to the Acme Form Builder functions
			foreach ( $form_fields as $form_field ) {
				if ( $form_field['filter'] == 'acme_fbuild__hidden' ) {
					$hidden[] = apply_filters( $form_field['filter'], $form_field );
				}
				else {
					$fields[] = apply_filters( $form_field['filter'], $form_field );
				}
			}
			// Prepends hidden fields
			foreach ( $hidden as $field ) {
				print( $field );
			}
			// Wraps form filed in html tags
			foreach ( $fields as $field ) {
				printf( '<p class="form-field export">%s</p>', $field );
			}
		}
		
		/**
		 * Inizializes API connection and retrieve Badges data
		 *
		 * @param $type
		 *
		 * @return array
		 */
		private function feedaty_fwr_badge_list ( $type ) {
			$res = array ();
			$webService = new Feedaty_Woocommerce_Rating_Webservice( $this->plugin_name, $this->version );
			$badges = $webService->getWidgets();
			
			if ( is_object( $badges ) ) {
				foreach ( $badges->$type->variants as $collection => $badge ) {
					$label = sprintf( 'name_shown_%s', $this->locale );
					$description = sprintf( 'description_%s', $this->locale );
					$size = sprintf( 'size_shown_%s', $this->locale );
					$res[] = array (
						'id'          => $badge->name,
						'value'       => $collection,
						'thumb'       => $badge->thumb,
						'label'       => $badge->$label,
						'description' => $badge->$description,
						'size'        => $badge->$size,
						'script'      => $webService->build_scripts( $badge->html, true )
					);
				}
			}
			return $res;
		}
		
		/**
		 * Send Orders through Feedaty API
		 */
		public function send_order ( $order_id ) {
			call_user_func( array ( $this, 'logger' ), [ 'log start', 'order_send hooked', $order_id ] );
			$webService = new Feedaty_Woocommerce_Rating_Webservice( $this->plugin_name, $this->version );
			$webService->hookFeedatyOrder( $order_id );
		}
		
		public function plugin_copyrights () {
			printf( '<div class="%1$s__copyrights">%2$s <a href="%3$s" target="_blank">Feedaty</a>.</div>',
				$this->plugin_name,
				__( 'Developed with love by', $this->plugin_name ),
				'https://www.feedaty.com'
			);
		}
		
		public function logger ( $mixMsg, $strAction = 'append' ) {
			if ( false == $this->log ) {
				return;
			}
			$file = sprintf( '%slogs/logger.log',
				plugin_dir_path( __FILE__ )
			);
			switch ( $strAction ) {
				case 'read':
					$mode = 'r';
					break;
				case 'reset':
					$mode = 'w';
					break;
				default:
					$mode = 'a';
					break;
			}
			$handle = fopen( $file, $mode );
			switch ( true ) {
				case null == $mixMsg:
					$res = fread( $handle, filesize( $file ) );
					break;
				case is_array( $mixMsg ):
					$row = sprintf( "\n[%s] passed array:\n%s\n%s",
						date( 'Y-m-d H:i:s' ),
						print_r( $mixMsg, true ),
						str_repeat( '#', 15 )
					);
					$res = fwrite( $handle, $row );
					break;
				default:
					$row = sprintf( "\n[%s] %s",
						date( 'Y-m-d H:i:s' )
						, $mixMsg
					);
					$res = fwrite( $handle, $row );
					break;
			}
			
			return $res;
		}
		
	}

