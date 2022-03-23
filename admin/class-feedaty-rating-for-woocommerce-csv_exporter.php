<?php
	
	if ( !defined( 'ABSPATH' ) ) {
		exit;
	}
	
	class fwr_csv_exporter	{
		
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
		 * Values passed from Export Form.
		 *
		 * @since    1.0.0
		 * @access   private
		 * @var      array    $args    The options for this export.
		 */
		private $args;
		/**
		 * Bunch of shorthand Vars from @args passed before
		 *
		 * @since    1.0.0
		 * @access   private
		 * @var      string    $dateFrom    The options for this export.
		 * @var      string    $dateTo    The options for this export.
		 * @var      string    $orderStatus    The options for this export.
		 */
		private $dateFrom;
		private $dateTo;
		private $orderStatus;
		
		/**
		 * Initialize the class and set its properties.
		 *
		 * @since    1.0.0
		 * @param      string    $plugin_name       The name of this plugin.
		 * @param      string    $version    The version of this plugin.
		 */
		public function __construct( $plugin_name, $version, $args ) {
			$this->plugin_name = $plugin_name;
			$this->version = $version;
			$this->args = $args[$this->plugin_name];
			$this->dateFrom = $this->args['orders_date_from_val'];
			$this->dateTo = $this->args['orders_date_to_val'];
			$this->orderStatus = isset( $this->args['orders_order_status'] ) ? $this->args['orders_order_status'] : 'completed';
		}
		
		/**
		 * Main function for this Export class
		 */
		public function export () {
			ob_start();
			$fName = sprintf( 'feedatyCSV__%s__%s.csv', $this->dateFrom, $this->dateTo );
			$handle = fopen( $fName, 'w' );
			fputcsv( $handle, array (
					'Order ID',
					'UserID',
					'E-mail',
					'Date',
					'Product ID',
					'Extra',
					'Product Url',
					'Product Image'
				)
			);
			
			if ( is_array( $orders = $this->get_orders() ) ) {
				foreach ( $orders as $order ) {
					$idOrder = $order->get_id();
					$alt_id = $idOrder;
					if ( version_compare( WC_VERSION, '4.0', '<=' ) ) {
						$checkStatus = sprintf( 'wc-%s', $this->orderStatus );
						if ( $order->post->post_status != $checkStatus ) {
							continue;
						}
					}
					// Woocommerce >= 5.x.x code
					else {
						if ( $order->get_status() != $this->orderStatus ) {
							continue;
						}
					}
					if ( method_exists( $order, 'get_billing_email' ) ) {
						$alt_id = $order->get_billing_email();
					}
					$email = $alt_id;
					$user_id = $alt_id;
					if ( is_array( $items = $order->get_items() ) ) {
						foreach ( $items as $item ) {
							if ( !method_exists( $order, 'get_date_completed' ) ) {
								continue;
							}
							$product = wc_get_product( $item['product_id'] );
							if ( is_object( $product ) ) {
								$product_id = $product->get_id();
								$img_url = wp_get_attachment_image_url( $product->get_image_id(), 'full' );
								//$date_time_format = get_option( 'date_format' );
								//Feedaty Import requires a specific date/time format
								$date_time_format = 'd/m/Y H:i';
								//$date_time_format = get_option['date_format'] . " " . get_option['time_format'];
								$date = date( $date_time_format, strtotime( $order->get_date_completed() ) );
								fputcsv( $handle, array (
									$idOrder,
									$user_id,
									$email,
									!empty( $date ) ? $date : ' ',
									$product_id,
									$item->get_name(),
									get_permalink( $product_id ),
									$img_url
								) );
							}
						}
					}
				}
			}
			
			fclose( $handle );
			ob_end_flush();
			
			$this->downloadCsv($fName);
			die();
		}
		
		/**
		 * Filters query params for retrieving order export
		 *
		 * @return array
		 * @throws Exception
		 */
		private function set_query_params () {
			$dateFormat = 'Y-m-d';
			$dateFrom = null;
			$operator = '<=';
			if ( !empty( $this->dateFrom ) ) {
				$dateFrom = new DateTime( $this->dateFrom );
				$operator = '...';
			}
			$dateTo = new DateTime();
			if ( !empty( $this->dateTo ) ) {
				$dateTo = new DateTime( $this->dateTo );
			}
			
			$params = array (
				'limit'  => -1,
				'status' => $this->orderStatus
			);
			$params['date_created'] = sprintf( '%s%s%s',
				empty( $dateTo ) ? null : date_format( $dateFrom, $dateFormat ),
				$operator,
				date_format( $dateTo, $dateFormat )
			);
			
			return $params;
		}
		
		/**
		 * Retrieves WooCommerce orders
		 *
		 * @return stdClass|WC_Order[]
		 * @throws Exception
		 */
		private function get_orders () {
			if ( is_multisite() ) {
				switch_to_blog( get_current_blog_id() );
				$orders = wc_get_orders( $this->set_query_params() );
				restore_current_blog();
			}
			else {
				$orders = wc_get_orders( $this->set_query_params() );
			}
			
			return $orders;
		}
		
		/**
		 * Parse Headers for downloading CSV files
		 *
		 * @param $file
		 */
		protected function downloadCsv ( $file ) {
			if ( file_exists( $file ) ) {
				//set headers
				header( 'Content-Description: File Transfer' );
				header( 'Content-Type: application/csv' );
				header( 'Content-Disposition: attachment; filename=' . basename( $file ) );
				header( 'Expires: 0' );
				header( 'Cache-Control: must-revalidate' );
				header( 'Pragma: public' );
				header( 'Content-Length: ' . filesize( $file ) );
				ob_clean();
				flush();
				readfile( $file );
			}
		}
	}