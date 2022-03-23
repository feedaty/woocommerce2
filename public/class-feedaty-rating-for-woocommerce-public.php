<?php
/**
 * The public-facing functionality of the plugin.
 *
 *
 * @package    Feedaty_Woocommerce_Rating
 * @subpackage Feedaty_Woocommerce_Rating/public
 * @author     Mirko Bianco <mirko@acmemk.com>
 */
class Feedaty_Woocommerce_Rating_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;
	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;
	/**
	 * The options for this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $options The options for this plugin.
	 */
	private $options;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		//Retrieves Plugin Options
		$this->options = get_option( $this->plugin_name );
		// Trigger Product Badge Widget
		if ( is_array( $this->options ) && !empty( $this->options['product_badge_enabled'] ) ) {
			add_action( $this->options['product_badge_hook'], array ( $this, 'deploy_product_widget' ) );
		}
		if ( is_array( $this->options ) && !empty( $this->options['product_carousel_enabled'] ) > 0 ) {
			add_action( $this->options['product_carousel_hook'], array ( $this, 'deploy_product_carousel' ) );
		}
		// Manages Product Tabs
		add_filter( 'woocommerce_product_tabs', array ( $this, 'tabs_manager' ), 99 );
		// Trigger reviews display
		add_action( "{$this->plugin_name}-single-review", array ( $this, 'star_seeder' ), 10 );
		add_action( "{$this->plugin_name}-single-review", array ( $this, 'review_seeder' ), 20 );
		add_action( "{$this->plugin_name}-after-reviews-loop", array ( $this, 'read_all_reviews' ), 10 );
		add_action( "{$this->plugin_name}-no_results", array ( $this, 'no_results' ), 10 );
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 *  No Plugin styles defined. Uncomment if frontend css needed
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/feedaty-rating-for-woocommerce-public.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		if ( !empty( $this->options['script'] ) ) {
			wp_register_script( $this->plugin_name, $this->options['script'], [], false, true );
			wp_enqueue_script( $this->plugin_name );
		}
		
		//wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/feedaty-rating-for-woocommerce-public.js', array( 'jquery' ), $this->version, false );

	}
	
	/**
	 * Append Feedaty Microdata for Reviews
	 *
	 * @param $markup
	 *
	 * @return mixed
	 */
	public function append_microdata ( $markup ) {
		if ( isset( $this->options['product_microdata'] ) && $this->options['product_microdata'] > 0 ) {
			global $product;
			if ( is_object( $product ) && $id = $product->get_id() ) {
				$webService = new Feedaty_Woocommerce_Rating_Webservice( $this->plugin_name, $this->version );
				$ratings = $webService->getRatings( $id, 'product', true );
				$markup['aggregateRating'] = array (
					'ratingValue' => isset( $ratings->Product->AvgRating ) ? $ratings->Product->AvgRating : null,
					'reviewCount' => isset( $ratings->Product->RatingsCount ) ? $ratings->Product->RatingsCount : null
				);
			}
		}
		
		return $markup;
	}
	
	/**
	 * Retrieves Badge from API and displays it inside product page
	 *
	 * @return false
	 */
	public function deploy_product_widget () {
		global $product;
		if ( is_object( $product ) && $id = $product->get_id() ) {
			$webservice = new Feedaty_Woocommerce_Rating_Webservice( $this->plugin_name, $this->version );
			$badge = $webservice->getWidgets( $this->options['product_badge_id'], 'product' );
			if ( is_object( $badge ) ) {
				$html = $webservice->api_injector( $badge->html, $id );
				printf(
					'<div class="feedaty_fwr_product_badge">%s</div>',
					$html
				);
			}
		}
		
		return false;
	}
	
	/**
	 * Retrieves Carousel from API and displays it inside product page
	 *
	 * @return false
	 */
	public function deploy_product_carousel () {
		global $product;
		if ( is_object( $product ) && $id = $product->get_id() ) {
			$webservice = new Feedaty_Woocommerce_Rating_Webservice( $this->plugin_name, $this->version );
			$badge = $webservice->getWidgets( $this->options['product_carousel_id'], 'carouselproduct' );
			if ( is_object( $badge ) ) {
				$html = $webservice->api_injector( $badge->html, $id );
				printf(
					'<div class="feedaty_fwr_product_badge">%s</div>',
					$html
				);
			}
		}
		
		return false;
	}
	
	/**
	 * Parses Feedaty Reviews Tab
	 */
	public function deploy_reviews_tab () {
		global $product;
		if ( is_object( $product ) && $id = $product->get_id() ) {
			$webservice = new Feedaty_Woocommerce_Rating_Webservice( $this->plugin_name, $this->version );
			$reviews = $webservice->fdGetProductData( $id );
			// Loads Template
			require_once( plugin_dir_path( __FILE__ ) . 'partials/feedaty-rating-for-woocommerce-public-display.php' );
		}
	}
	
	/**
	 * Parses Product Review inside Reviews Loop
	 *
	 * @param $review
	 */
	public function review_seeder ( $review ) {
		printf( '<span><i>%s</i></span>', $review->ProductReview );
	}
	
	/**
	 * Parses Product Stars inside Reviews Loop
	 *
	 * @param $review
	 */
	public function star_seeder ( $review ) {
		printf( '<span><img src="//widget.feedaty.com/public/2021/default/images/svg/stars/goldstars/%d.svg" width="120px"/></span>', $review->ProductRating );
	}
	
	/**
	 * Parses Link to All Product Reviews
	 *
	 * @param $reviews
	 */
	public function read_all_reviews ($reviews) {
		printf(
			'<a target="_blank" href="%s">%s</a>',
			$reviews->Product->Url,
			__( 'Read All Product Reviews', $this->plugin_name )
		);
	}
	
	/**
	 * Parses No Result message
	 */
	public function no_results () {
		printf(
			'<div class="reviews-no-review">%s</div>',
			__( 'Still no Reviews for this Product', $this->plugin_name )
		);
	}
	
	/**
	 * Manages Product Tabs based on Plugin Settings
	 *
	 * @param $tabs
	 *
	 * @return mixed
	 */
	public function tabs_manager ( $tabs ) {
		if ( isset( $this->options['hide_default_reviews'] ) && $this->options['hide_default_reviews'] > 0 ) {
			unset( $tabs['reviews'] );
		}
		if ( isset( $this->options['product_reviews_tab'] ) && $this->options['product_reviews_tab'] > 0 ) {
			$tabs['feedaty_reviews'] = array (
				'title'    => __( 'Feedaty Reviews', $this->plugin_name ),
				'priority' => 30,
				'callback' => array ( $this, 'deploy_reviews_tab' )
			);
		}
		
		return $tabs;
	}
	
}
