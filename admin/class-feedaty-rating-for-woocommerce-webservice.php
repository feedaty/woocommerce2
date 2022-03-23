<?php
	
	class Feedaty_Woocommerce_Rating_Webservice	{
		
		private $plugin_name;
		private $options;
		
		private $merchantCode;
		private $clientSecret;
		private $status;
		private $log;
		private $version;
		private $expire;
		
		
		
		public function __construct ( $plugin_name, $version ) {
			$this->plugin_name = $plugin_name;
			$this->version = $version;
			$this->options = get_option( $this->plugin_name );
			$this->log = true;
			$this->expire = HOUR_IN_SECONDS * 3;
			if ( is_array( $this->options ) ) {
				$this->merchantCode = $this->options['merchant_code'];
				$this->clientSecret = $this->options['client_secret'];
				$this->status = $this->options['send_order_trigger'];
			}
		}
		
		/**
		 * Retrieves Widgets form API
		 *
		 * @param null $collection
		 *
		 * @return array    $response
		 */
		public function getWidgets( $collection = null, $caller = null ) {
			$transientKey = sprintf( 'feeday_widgets-%s', $this->plugin_name );
			$content = json_decode( trim( get_transient( $transientKey ) ) );
			
			if ( empty( $content ) ) {
				$url = add_query_arg( [
					'action'    => 'widget_list',
					'style_ver' => '2021',
					'merchant'  => $this->merchantCode
				], 'https://widget.feedaty.com/' );
				
				$connection = wp_remote_get( $url );
				if ( is_array($connection) && $connection['response']['code'] == '200' ) {
					$remoteObj = wp_remote_retrieve_body( $connection );
					$content = json_decode( trim( $remoteObj ) );
					delete_transient( $transientKey );
					set_transient( $transientKey, $remoteObj, $this->expire );
				}
			}
			
			if ( $collection) {
				if ( empty( $caller ) ) {
					$caller = 'merchant';
				}
				if ( is_object( $content->$caller->variants ) ) {
					return $content->$caller->variants->$collection;
				}
				
				return null;
			}
			
			return $content;
		}
		
		
		public function api_injector ( $html, $product_id = null ) {
			$html = $this->build_scripts( $html );
			$raw = $this->html_attributes( $html );
			$attributes = $raw[0];
			$parsed_html = substr( $html, 0, $raw[1] );
			$editor = [
				'data-gui'  => 'guiLang',
				'data-lang' => 'langLang'
			];
			$tag_rewrite = [];
			foreach ( $attributes as $key => $void ) {
				if ( !empty( $editor[$key] ) ) {
					$attributes[$key] = empty( $this->options[$editor[$key]] ) ? null : $this->options[$editor[$key]];
				}
				if ( !empty( $product_id ) ) {
					$attributes['data-sku'] = $product_id;
				}
				$tag_rewrite[] = sprintf( '%s="%s"', $key, $attributes[$key] );
			}
			$parsed_html .= sprintf( '<div %s></div>', implode( ' ', $tag_rewrite ) );
			return $parsed_html;
		}
		
		public function build_scripts ( $html, $src = false ) {
			$re = '/<script[\s\S]*?>[\s\S]*?<\/script>/i';
			preg_match_all($re, $html, $script_tag, PREG_SET_ORDER, 0);
			$re_src = '/\bhttps?:\/\/[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|\/))/';
			preg_match($re_src, $script_tag[0][0], $matches, PREG_OFFSET_CAPTURE, 0);
			if ( strlen( $matches[0][0] ) ) {
				if ( true == $src ) {
					return $matches[0][0];
				}
				return preg_replace( $re, '', $html );
			}
			
			return $html;
		}
		
		public function html_attributes ($html) {
			$re = '/<div(.*)>/m';
			$attrs = array ();
			preg_match_all($re, $html, $matches, PREG_OFFSET_CAPTURE, 0);
			$raw = preg_split( '/\ /', $matches[0][0][0] );
			foreach($raw as $attr){
				$pair = preg_split( '/\"/', $attr );
				if ( !empty( $pair[1] ) ) {
					$attribute = substr( $pair[0], 0, -1 );
					$attrs[$attribute] = $pair[1];
				}
			}
			
			return [ $attrs, $matches[0][0][1] ];
		}
		
		
		/**
		 * Function hookFeedatyOrder - hook per l'ordine
		 *
		 * @param $order_id
		 *
		 */
		function hookFeedatyOrder( $order_id ){
			$token = $this->getRequestToken();
			
			$accessToken = json_decode( $this->getAccessToken( $token )['body'] );
			
			$this->sendOrder( $accessToken, $order_id );
			
		}
		
		
		/**
		 * Function serializeItems
		 *
		 * @param $items
		 *
		 * @return $return - array product
		 */
		private function getProducts( $items ){
			
			
			foreach ( $items as $item_key => $item ) {
				
				//$product = $item->get_product();
				$product = wc_get_product( $item['product_id'] );
				
				//if( false  !== $product || !$product ) {
				if( is_object($product) ) {
					
					$product_id = $item['product_id'];
					
					$image_id  = $product->get_image_id();
					
					$fdproduct["SKU"] = (string) $product_id;
					
					$fdproduct["Name"] = $item->get_name();
					
					$fdproduct["ThumbnailURL"]  = wp_get_attachment_image_url( $image_id, 'full' );
					
					$fdproduct["URL"] = get_permalink($product_id);
					
					$fdproducts[] = $fdproduct;
					
				}
				
			}
			
			//var_dump($fdproducts);exit;
			
			return $fdproducts;
			
		}
		
		
		/**
		 * Function sendOrder
		 *
		 * @param $accessToken
		 * @param $order_id
		 *
		 * @return $response
		 */
		function sendOrder( $accessToken, $order_id ) {
			// get an instance of the WC_Order object
			
			$order = wc_get_order( $order_id );
			
			$header = [
				'Content-Type' => 'application/json',
				'Authorization' => 'Oauth ' . $accessToken->AccessToken
			];
			
			
			$url = 'http://api.feedaty.com/Orders/Insert';
			$items = $order->get_items();
			$tmp_order["ID"] = (string)$order_id;
			$tmp_order["Platform"] = sprintf( 'WooCommerce: %s - Version: %s', $this->plugin_name, $this->version );
			$tmp_order["Date"] = (string)$order->get_date_modified();
			$tmp_order["CustomerID"] = $order->get_billing_email();
			$tmp_order["CustomerEmail"] = $order->get_billing_email();
			$tmp_order["Culture"] = $this->fdtGetCultureCode();
			//get products
			$tmp_order["Products"] = $this->getProducts( $items );
			
			
			$fd_data[] = $tmp_order;
			
			$response = wp_remote_post( $url, [
				'body'        => json_encode( $fd_data ),
				'timeout'     => '60',
				'redirection' => '5',
				'httpversion' => '1.0',
				'blocking'    => true,
				'headers'     => $header
			] );
			
			return $response;
		}
		
		
		/**
		 * Function getAccessToken
		 *
		 * @param $token
		 *
		 * @param $response
		 */
		function getAccessToken( $token ) {
			
			$encripted_code = $this->encriptToken( $token );
			
			
			$body = array (
				'oauth_token' => $token->RequestToken,
				'grant_type'  => 'authorization'
			);
			
			$header = array(
				'Content-Type' => 'application/x-www-form-urlencoded',
				'Authorization' => 'Basic '.$encripted_code,
				'User-Agent' => 'WPFeedaty'
			);
			
			
			
			
			$url = "http://api.feedaty.com/OAuth/AccessToken";
			
			
			
			$response = wp_remote_post( $url, [
				'body'        => substr(add_query_arg($body,''), 1),//$data,
				'timeout'     => '60',
				'redirection' => '5',
				'httpversion' => '1.1',
				'blocking'    => true,
				'headers'     => $header
			] );
			
			return $response;
			
		}
		
		
		
		/**
		 * Function encriptToken
		 *
		 * @param $token
		 *
		 * @return $base64_sha_token
		 */
		private function encriptToken($token){
			
			$sha_token = sha1($token->RequestToken.$this->clientSecret);
			
			$base64_sha_token = base64_encode($this->merchantCode.":".$sha_token);
			
			return $base64_sha_token;
			
		}
		
		
		/**
		 * Function getRequestToken
		 *
		 * @return $response - OAuth/RequestToken
		 */
		function getRequestToken() {
			
			$header = [ 'Content-Type' => 'application/x-www-form-urlencoded' ];
			$url = "http://api.feedaty.com/OAuth/RequestToken";
			
			$connection = wp_remote_get( $url, [
				'timeout'     => '60',
				'headers'     => $header
			] );
			if ( is_array($connection) && $connection['response']['code'] == '200' ) {
				$response = json_decode( trim(  wp_remote_retrieve_body( $connection ) ) );
				
				return $response;
			}
		}
		
		
		/**
		 * Function fdGetProductData
		 *
		 * @param $id
		 *
		 * @return $data
		 */
		public function fdGetProductData($id) {
			
			$transientKey = sprintf( 'feeday_ProductData-%s__%s', $this->plugin_name, $id );
			$content = json_decode( trim( get_transient( $transientKey ) ) );
			
			if( empty($content) ) {
				$url = add_query_arg( [
					'function'      => 'feed',
					'action'        => 'ws',
					'task'          => 'product',
					'merchant_code' => $this->merchantCode,
					'ProductID'     => $id
				], 'https://widget.zoorate.com/go.php' );
				$connection = wp_remote_get( $url );
				if ( is_array($connection) && $connection['response']['code'] == '200' ) {
					$remoteObj = wp_remote_retrieve_body( $connection );
					$content = json_decode( trim( $remoteObj ) );
					delete_transient( $transientKey );
					set_transient( $transientKey, $remoteObj, $this->expire );
				}
				
			}
			
			return $content;
			
		}
		
		/**
		 * Function fdGetCultureCode
		 *
		 *
		 * @return string
		 */
		private function fdtGetCultureCode() {
			
			$lang = substr( get_locale(), 0, 2 );
			
			if ( $lang != "en" && $lang != "it" && $lang != "es" && $lang != "de" && $lang != "fr" ) {
				
				$culture = "en";
				
			}
			
			else {
				
				$culture = $lang;
				
			}
			
			
			return $culture;
			
		}
		
		public function getRatings($sku = "null" , $scope = "product", $cache = true ) {
			$transientKey = sprintf( 'feeday_ratings-%s__%s', $this->plugin_name, $sku );
			$content = json_decode( trim( get_transient( $transientKey ) ) );
			
			
			if( empty($content) ) {
				$url = add_query_arg( [
					'function' => 'feed_v6',
					'action'   => 'ratings',
					'scope'    => $scope,
					'merchant' => $this->merchantCode,
					'sku'      => $sku,
					'lang'     => 'null'
				], 'https://widget.zoorate.com/go.php' );
				
				$connection = wp_remote_get( $url );
				if ( is_array($connection) && $connection['response']['code'] == '200' ) {
					$remoteObj = wp_remote_retrieve_body( $connection );
					$content = json_decode( trim( $remoteObj ) );
					delete_transient( $transientKey );
					set_transient( $transientKey, $remoteObj, $this->expire );
				}
			}
			
			return $content->data;
			
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
				case is_object( $mixMsg ):
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