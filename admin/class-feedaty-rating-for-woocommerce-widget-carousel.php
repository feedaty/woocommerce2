<?php
	
	class class_feedaty_woocommerce_rating_widget_carousel extends WP_Widget 	{
		public $plugin_name;
		public $options;
		public $version;
		private $badge;
		public $webservice;
		function __construct ($plugin_name) {
			$this->plugin_name = $plugin_name;
			$this->options = get_option( $this->plugin_name );
			$this->version = @$this->options['version'];
			if ( is_array( $this->options ) && isset( $this->options['store_carousel_id'] ) ) {
				$this->webservice = new Feedaty_Woocommerce_Rating_Webservice( $this->plugin_name, $this->version );
				$this->badge = $this->webservice->getWidgets( $this->options['store_carousel_id'], 'carousel' );
			}
			parent::__construct(
			//Base ID of the widget
				'feedaty_woocommerce_rating_widget_carousel',
				//Widget display name for Backend
				__( 'Feedaty FWR Store Carousel', $this->plugin_name ),
				//Widget Description
				array ( 'description' => __( 'Displays your Feedaty Store Carousel Widget', $this->plugin_name ) )
			);
		}
		
		public function widget ( $args, $instance ) {
			// before and after widget arguments are defined by themes
			echo $args['before_widget'];
			if ( is_array( $this->options ) && !empty( $this->options['store_carousel_enabled'] ) ) {
				echo $this->webservice->api_injector( $this->badge->html );
			}
			echo $args['after_widget'];
		}
		
		public function form( $instance ) {
			if ( is_object( $this->badge ) && isset( $this->badge->thumb ) ) {
				printf( '<img src="%s" />', $this->badge->thumb );
			}
			printf( '<p>%s<br><a href="%s">%s</a></p>',
				__('You can change the widget inside the plugin store settings page.', $this->plugin_name),
				admin_url( 'admin.php?page=feedaty_fwr_store_carousel' ),
				__( 'Choose Carousel', $this->plugin_name )
			);
		}
// Updating widget replacing old instances with new
		public function update( $new_instance, $old_instance ) {
			$instance             = array();
			//['title']    = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
			//$instance['base_url'] = ( ! empty( $new_instance['base_url'] ) ) ? strip_tags( $new_instance['base_url'] ) : '';
			
			return $instance;
		}
	}