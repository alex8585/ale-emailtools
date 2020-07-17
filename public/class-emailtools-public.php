<?php


class Emailtools_Public {

	private $emailtools;
	private $version;

	public function __construct( $emailtools, $version ) {
		$options = get_option('emailtools');

		$this->is_terms =isset($options['is_terms_and_conditions']) ? $options['is_terms_and_conditions'] : ''; 
		$this->apikey = isset($options['emt_api_key']) ? $options['emt_api_key'] : ''; 
		$this->is_logging = isset($options['is_logging']) ? $options['is_logging'] : ''; 
		
		$this->emailtools = $emailtools;
		$this->version = $version;
		$this->emt = new EMTApi($this->apikey);
		print_r($_COOKIE['emt_uuuid'])die;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() { }

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		if($this->is_terms) {
			wp_register_script( 'tools', 'https://emailtools.ru/js/api/v1/tools.js?ver=1.2', array( 'jquery' ), $this->version, false );
			wp_enqueue_script('tools');
			wp_localize_script( 'tools', 'EMT', array( 
				'_client_id' => $this->apikey,
			) );
		}
	}
	
	public function plugin_init() {}

	public function init() {
		if($this->is_terms) {
			add_action('woocommerce_cart_item_removed', [$this, 'cart_item_removed'], 10, 2 );
			add_action('woocommerce_add_to_cart', [$this, 'add_to_cart'],10, 2);
			add_action('woocommerce_checkout_order_processed', [$this, 'before_onlinepay'], 10,3 );
			add_action('woocommerce_thankyou',  [$this,'new_order'], 10, 1 );
			add_action('woocommerce_archive_description',  [$this,'category_hook'], 10, 1 );
			add_action('woocommerce_before_single_product',  [$this,'single_product'], 10, 1 );
		}
	}
	
	public function wp() { }

	public function before_onlinepay($order_id, $posted_data, $order) {
		if ( WC()->cart->needs_payment() ) {
			if($posted_data['payment_method'] != 'bacs') {
				$email = WC()->customer->get_billing_email();
				$params = ['email' => $email];
				
				$response = $this->emt->sendOperation('onlinepay', $params);
				$this->log('onlinepay', $params, $response);
			}
		}
	}

	public function new_order($order_id) {

		$order = wc_get_order( $order_id );
		$email = $order->get_billing_email();
		$items = $order->get_items();
		$total = $order->get_total();

		$idsArr = [];
		foreach($items as $item ) {
			$idsArr[] = $item->get_product_id();
		}
		
		$params = [
			'email' => $email,
			'orderid'=> $order_id,
			'products'=> $idsArr,
			'total' => $total,
		];

		$response = $this->emt->sendOperation('sendOrder', $params);
		$this->log('sendOrder', $params, $response);
	}

	public function single_product() {
		global $product;
		
		$product_id = $product->get_id();
		$category_id = '';
		$category_ids = $product->get_category_ids();
		if( !empty($category_ids) ) {
			$category_id = array_shift($category_ids);
		}
		
		$params = [
			'productid'=>$product_id,
			'categoryid' => $category_id,
		];
		
		$response = $this->emt->sendOperation('viewProduct', $params);
		
		$this->log('viewProduct', $params, $response);
	}

	public function category_hook() {
		if(is_product_category()) {
			global $wp_query;
			$category_id = $wp_query->get_queried_object()->term_id;
			
			$params = ['categoryid' => $category_id];

			$response = $this->emt->sendOperation('viewCategory', $params);
			$this->log('viewCategory', $params, $response);
		}
		
	}

	public function add_to_cart($cart_item_key, $product_id) {
	
		$params = ['productid' => $product_id];
		
		$response = $this->emt->sendOperation('addtocart', $params);
		$this->log('addtocart', $params, $response);
	}

	public function cart_item_removed( $cart_item_key, $cart ){
		$line_item = $cart->removed_cart_contents[ $cart_item_key ];
		$product_id = $line_item[ 'product_id' ];
		$params = ['productid' => $product_id];

		$response = $this->emt->sendOperation('removeproduct', $params);
		$this->log('removeproduct', $params, $response);
		
	}

	private  function log($action, $params=[],$response=[]) {
		if($this->is_logging == 'on') {
			$logFile = ABSPATH . 'emtlog.txt';
			$msg = date("m d y H:i:s ") . "{$action}: " . json_encode($params). " API: ".json_encode($response).PHP_EOL; 
			file_put_contents($logFile, $msg, FILE_APPEND);
		}
    }
}
