<?php
/**
 * This is the PayPal Payments Standard 2.0 Gateway.
 * It uses the wpsc_merchant class as a base class which is handy for collating user details and cart contents.
 */

 /*
  * This is the gateway variable $nzshpcrt_gateways, it is used for displaying gateway information on the wp-admin pages and also
  * for internal operations.
  */
  
 /*
  * KNC version: 1.0
  * last updated: 1-11-2011
  */
  
$nzshpcrt_gateways[$num] = array(
	'name' => 'PayPal Payments Standard Knc Mu',
	'api_version' => 2.0,
	'image' => WPSC_URL . '/images/paypal.gif',
	'class_name' => 'wpsc_merchant_paypal_standard_knc_mu',
	'has_recurring_billing' => true,
	'wp_admin_cannot_cancel' => true,
	'display_name' => 'PayPal Payments Standard',
	'requirements' => array(
		/// so that you can restrict merchant modules to PHP 5, if you use PHP 5 features
		'php_version' => 4.3,
		 /// for modules that may not be present, like curl
		'extra_modules' => array()
	),

	// this may be legacy, not yet decided
	'internalname' => 'wpsc_merchant_paypal_standard_knc_mu',

	// All array members below here are legacy, and use the code in paypal_multiple.php
	'form' => 'knc_form_paypal_multiple',
	'submit_function' => 'knc_submit_paypal_multiple',
	'payment_type' => 'paypal',
	'supported_currencies' => array(
		'currency_list' =>  array('AUD', 'BRL', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'ILS', 'JPY', 'MXN', 'MYR', 'NOK', 'NZD', 'PHP', 'PLN', 'SEK', 'SGD', 'THB', 'TWD', 'USD'),
		'option_name' => 'paypal_curcode'
	)
);
	// define knc coupon table - addition by keysnclicks
	// should replace this with include_once later
	global $table_prefix, $wpdb;
	// Use the DB method if it exists
	if ( !empty( $wpdb->prefix ) )
		$wp_table_prefix = $wpdb->prefix;
	// Fallback on the wp_config.php global
	else if ( !empty( $table_prefix ) )
		$wp_table_prefix = $table_prefix;
	define('KNC_TABLE_COUPON_CODES', "{$wp_table_prefix}knc_gc_details");


/**
	* WP eCommerce PayPal Standard Merchant Class
	*
	* This is the paypal standard merchant class, it extends the base merchant class
	*
	* @package wp-e-commerce
	* @since 3.7.6
	* @subpackage wpsc-merchants
*/
class wpsc_merchant_paypal_standard_knc_mu extends wpsc_merchant {
  var $name = 'PayPal Payments Standard';
  var $paypal_ipn_values = array();

	/**
	* construct value array method, converts the data gathered by the base class code to something acceptable to the gateway
	* @access public
	*/
	function construct_value_array() {
		$this->collected_gateway_data = $this->_construct_value_array();
	}
	
	function convert( $amt ){
		if ( empty( $this->rate ) ) {
			$this->rate = 1;
			$paypal_currency_code = $this->get_paypal_currency_code();
			$local_currency_code = $this->get_local_currency_code();
			if( $local_currency_code != $paypal_currency_code ) {
				$curr=new CURRENCYCONVERTER();
				$this->rate = $curr->convert( 1, $paypal_currency_code, $local_currency_code );
			}
		}
		return $this->format_price( $amt / $this->rate );
	}
	
	function get_local_currency_code() {
		if ( empty( $this->local_currency_code ) ) {
			global $wpdb;
			$this->local_currency_code = $wpdb->get_var("SELECT `code` FROM `".WPSC_TABLE_CURRENCY_LIST."` WHERE `id`='".get_option('currency_type')."' LIMIT 1");
		}
		
		return $this->local_currency_code;
	}
	
	function get_paypal_currency_code() {
		if ( empty( $this->paypal_currency_code ) ) {
			global $wpsc_gateways;
			$this->paypal_currency_code = $this->get_local_currency_code();
			
			if ( ! in_array( $this->paypal_currency_code, $wpsc_gateways['wpsc_merchant_paypal_standard']['supported_currencies']['currency_list'] ) )
				$this->paypal_currency_code = get_option( 'paypal_curcode', 'USD' );
		}
		
		return $this->paypal_currency_code;
	}

	/**
	* construct value array method, converts the data gathered by the base class code to something acceptable to the gateway
	* @access private
	* @param boolean $aggregate Whether to aggregate the cart data or not. Defaults to false.
	* @return array $paypal_vars The paypal vars
	*/
	function _construct_value_array($aggregate = false) {
		global $wpdb, $wpsc_cart;
		$paypal_vars = array();
		$add_tax = ! wpsc_tax_isincluded();		

		// Store settings to be sent to paypal
		$paypal_vars += array(
			'business' => get_option('paypal_multiple_business'),
			'return' => add_query_arg('sessionid', $this->cart_data['session_id'], $this->cart_data['transaction_results_url']),
			'cancel_return' => $this->cart_data['transaction_results_url'],
			'rm' => '2',
			'currency_code' => $this->get_paypal_currency_code(),
			'lc' => $this->cart_data['store_currency'],
			'bn' => $this->cart_data['software_name'],

			'no_note' => '1',
			'charset' => 'utf-8',
		);

		// IPN data
		if (get_option('paypal_ipn') == 1) {
			$notify_url = $this->cart_data['notification_url'];
			$notify_url = add_query_arg('gateway', 'wpsc_merchant_paypal_standard_knc_mu', $notify_url);
			$notify_url = apply_filters('wpsc_paypal_standard_notify_url', $notify_url);
			$paypal_vars += array(
				'notify_url' => $notify_url,
			);
		}

		// Shipping
		if ((bool) get_option('paypal_ship')) {
			$paypal_vars += array(
				'address_override' => '1',
				'no_shipping' => '0',
			);
		}

		// Customer details
		$paypal_vars += array(
			'email' => $this->cart_data['email_address'],
			'first_name' => $this->cart_data['shipping_address']['first_name'],
			'last_name' => $this->cart_data['shipping_address']['last_name'],
			'address1' => $this->cart_data['shipping_address']['address'],
			'city' => $this->cart_data['shipping_address']['city'],
			'country' => $this->cart_data['shipping_address']['country'],
			'zip' => $this->cart_data['shipping_address']['post_code'],
			'state' => $this->cart_data['shipping_address']['state'],
		);
		
		if ( $paypal_vars['country'] == 'UK' ) {
			$paypal_vars['country'] = 'GB';
		}

		// Order settings to be sent to paypal
		$paypal_vars += array(
			'invoice' => $this->cart_data['session_id']
		);
		
		// Two cases:
		// - We're dealing with a subscription
		// - We're dealing with a normal cart
		if ($this->cart_data['is_subscription']) {
			$paypal_vars += array(
				'cmd'=> '_xclick-subscriptions',
			);

			$reprocessed_cart_data['shopping_cart'] = array(
				'is_used' => false,
				'price' => 0,
				'length' => 1,
				'unit' => 'd',
				'times_to_rebill' => 1,
			);

			$reprocessed_cart_data['subscription'] = array(
				'is_used' => false,
				'price' => 0,
				'length' => 1,
				'unit' => 'D',
				'times_to_rebill' => 1,
			);

			foreach ($this->cart_items as $cart_row) {
				if ($cart_row['is_recurring']) {
					$reprocessed_cart_data['subscription']['is_used'] = true;
					$reprocessed_cart_data['subscription']['price'] = $this->convert( $cart_row['price'] );
					$reprocessed_cart_data['subscription']['length'] = $cart_row['recurring_data']['rebill_interval']['length'];
					$reprocessed_cart_data['subscription']['unit'] = strtoupper($cart_row['recurring_data']['rebill_interval']['unit']);
					$reprocessed_cart_data['subscription']['times_to_rebill'] = $cart_row['recurring_data']['times_to_rebill'];
				} else {
					$item_cost = ($cart_row['price'] + $cart_row['shipping'] + $cart_row['tax']) * $cart_row['quantity'];

					if ($item_cost > 0) {
						$reprocessed_cart_data['shopping_cart']['price'] += $item_cost;
						$reprocessed_cart_data['shopping_cart']['is_used'] = true;
					}
				}

				$paypal_vars += array(
					'item_name' => __('Your Subscription', 'wpsc'),
					// I fail to see the point of sending a subscription to paypal as a subscription
					// if it does not recur, if (src == 0) then (this == underfeatured waste of time)
					'src' => '1'
				);

				// This can be false, we don't need to have additional items in the cart/
				if ($reprocessed_cart_data['shopping_cart']['is_used']) {
					$paypal_vars += array(
						"a1" => $this->convert($reprocessed_cart_data['shopping_cart']['price']),
						"p1" => $reprocessed_cart_data['shopping_cart']['length'],
						"t1" => $reprocessed_cart_data['shopping_cart']['unit'],
					);
				}

				// We need at least one subscription product,
				// If this is not true, something is rather wrong.
				if ($reprocessed_cart_data['subscription']['is_used']) {
					$paypal_vars += array(
						"a3" => $this->convert($reprocessed_cart_data['subscription']['price']),
						"p3" => $reprocessed_cart_data['subscription']['length'],
						"t3" => $reprocessed_cart_data['subscription']['unit'],
					);

					// If the srt value for the number of times to rebill is not greater than 1,
					// paypal won't accept the transaction.
					if ($reprocessed_cart_data['subscription']['times_to_rebill'] > 1) {
						$paypal_vars += array(
							'srt' => $reprocessed_cart_data['subscription']['times_to_rebill'],
						);
					}
				}
			} // end foreach cart item
		} else {
			$paypal_vars += array(
				'upload' => '1',
				'cmd' => '_ext-enter',
				'redirect_cmd' => '_cart',
			);
			$handling = $this->cart_data['base_shipping'];
			if($add_tax)
				$paypal_vars['tax_cart'] = $this->convert( $this->cart_data['cart_tax'] );
			
			// Set base shipping
			$paypal_vars += array(
				'handling_cart' => $this->convert( $handling )
			);
			
			// Stick the cart item values together here
			$i = 1;
			
			$cart_shipping = $wpsc_cart->calculate_total_shipping();
			$cart_total = wpsc_cart_total(false);
			$cart_discount = $wpsc_cart->coupons_amount;
			$cart_subtotal = $wpsc_cart->calculate_subtotal();
			
			// if NO or SOME discount applied & shipping IS or NOT there [=]
			//if ($cart_total > 0 && $cart_shipping>=0) {
			
				// if subtotal == discount i.e FULL DISCOUNT & some shipping exists [-]
				if($cart_subtotal==$cart_discount && $cart_shipping>0) {			
					
					$paypal_vars['item_name_'.$i] = "Shipping Charges";
					$paypal_vars['amount_'.$i] = $this->convert( $this->cart_data['total_price'] ) - $this->convert( $this->cart_data['base_shipping'] );
					$paypal_vars['quantity_'.$i] = 1;
					$paypal_vars['shipping_'.$i] = 0;
					$paypal_vars['shipping2_'.$i] = 0;
					$paypal_vars['handling_'.$i] = 0;
					$paypal_vars['item_number_'.$i] = 00;
					
					// if we need to send all products to paypal for display purpose then
					// disable this code but doing this also send amount=0 for all products
					// which in return send coupon codes in mail of 0 value
					// so we'll have to find an alternative (other than IPN amounts )to add value of codes
					// see line around line #620 - search string - $coupon_codes[$j] . "</strong> | Value: <strong>"
					$i = 2;
					foreach ($this->cart_items as $cart_row) {
						$paypal_vars += array(
							"item_name_$i" => $cart_row['name'],
							"amount_$i" => 0,
							"amount_local_$i" => $this->convert($cart_row['price']),
							"tax_$i" => ($add_tax) ? $this->convert($cart_row['tax']) : 0,
							"quantity_$i" => $cart_row['quantity'],
							"item_number_$i" => $cart_row['product_id'],
						);
						++$i;
					} /**/
					
				}else
				{
					foreach ($this->cart_items as $cart_row) {
						$paypal_vars += array(
							"item_name_$i" => $cart_row['name'],
							"amount_$i" => $this->convert($cart_row['price']),
							"amount_local_$i" => $this->convert($cart_row['price']),
							"tax_$i" => ($add_tax) ? $this->convert($cart_row['tax']) : 0,
							"quantity_$i" => $cart_row['quantity'],
							"item_number_$i" => $cart_row['product_id'],
							// additional shipping for the the (first item / total of the items)
							"shipping_$i" => $this->convert($cart_row['shipping']/ $cart_row['quantity'] ),
							// additional shipping beyond the first item
							"shipping2_$i" => $this->convert($cart_row['shipping']/ $cart_row['quantity'] ),
							"handling_$i" => '',
						);
						++$i;
					}
					
					// send discount data to paypal
					if ($this->cart_data['has_discounts']) {
						// set cart wide discount - addition by amit khanna
						if(!$cart_discount>0){ $cart_discount = 0; }
						$paypal_vars += array("discount_amount_cart" => $cart_discount);
					}
				}
			//} 
		}

		return $paypal_vars;
	}

	/**
	* submit method, sends the received data to the payment gateway
	* @access public
	*/
	function submit() {
		
		// get cart total
		$cart_total = wpsc_cart_total(false);

		// if there is no shipping &  disc == subtotal
		if($cart_total==0){
		
			$name_value_pairs = array();
			foreach ($this->collected_gateway_data as $key => $value) {
				$name_value_pairs[] = $key . '=' . urlencode($value);
				if($key == 'invoice'){
					$knc_session_id = $value;
				}
			}
			$_SESSION['knc_transaction_type'] = 'local';
			$this->paypal_ipn_values = $this->_construct_value_array();
			
			//var_dump($this->paypal_ipn_values);
			//exit();
			
			$knc_txn_id = strtoupper($this->knc_random_alpha_numeric(17));
			$this->set_transaction_details($knc_txn_id, 3); // mark order as "Accepted Payment" by updating database
			$this->knc_process_coupon_codes(); // generate coupon codes if any & manage existing code's usage

			// LOCAL TRANSACTION - NO PAYPAL
			$gateway_values = 'sessionid=' . $knc_session_id . '&tx=' . $knc_txn_id . '&st=Completed';
			$redirect = get_option( 'transact_url' )."?".$gateway_values;
			wp_redirect($redirect);
			exit();
			
		}else{
		//global $wpsc_cart;
		//$cart_shipping = $wpsc_cart->calculate_total_shipping();
		//var_dump($wpsc_cart->calculate_subtotal());
			$_SESSION['knc_transaction_type'] = 'remote';
			
			$name_value_pairs = array();
			foreach ($this->collected_gateway_data as $key => $value) {
				$name_value_pairs[] = $key . '=' . urlencode($value);
			}
			$gateway_values =  implode('&', $name_value_pairs);

			$redirect = get_option('paypal_multiple_url')."?".$gateway_values;
			// URLs up to 2083 characters long are short enough for an HTTP GET in all browsers.
			// Longer URLs require us to send aggregate cart data to PayPal short of losing data.
			// An exception is made for recurring transactions, since there isn't much we can do.
			if (strlen($redirect) > 2083 && !$this->cart_data['is_subscription']) {
				$name_value_pairs = array();
				foreach($this->_construct_value_array(true) as $key => $value) {
					$name_value_pairs[]= $key . '=' . urlencode($value);
				}
				$gateway_values =  implode('&', $name_value_pairs);

				$redirect = get_option('paypal_multiple_url')."?".$gateway_values;
			}

			if (defined('WPSC_ADD_DEBUG_PAGE') && WPSC_ADD_DEBUG_PAGE) {
				echo "<a href='".esc_url($redirect)."'>Test the URL here</a>";
				echo "<pre>".print_r($this->collected_gateway_data,true)."</pre>";
				exit();
			} else {
				wp_redirect($redirect);
				exit();
			}
		}

	}


	/**
	* parse_gateway_notification method, receives data from the payment gateway
	* @access private
	*/
	function parse_gateway_notification() {
		/// PayPal first expects the IPN variables to be returned to it within 30 seconds, so we do this first.
		$paypal_url = get_option('paypal_multiple_url');
		$received_values = array();
		$received_values['cmd'] = '_notify-validate';
  		$received_values += $_POST;
		$options = array(
			'timeout' => 5,
			'body' => $received_values,
			'user-agent' => ('WP e-Commerce/'.WPSC_PRESENTABLE_VERSION)
		);
		
		$response = wp_remote_post($paypal_url, $options);
		if( 'VERIFIED' == $response['body'] ) {
			$this->paypal_ipn_values = $received_values;
			$this->session_id = $received_values['invoice'];
			$this->set_purchase_processed_by_sessionid(3);

		} else {
			exit("IPN Request Failure");
		}
	}
	
	
	/**
	 * random_alpha_numeric method, generates an alpha numeric random number
	 * @access public
	 * addition by amitkhanna
	 */
	function knc_random_alpha_numeric($length) {
		$random = "";
		$prefix = "";
		$suffix = "";
		srand((double)microtime()*1000000);

		$data = "AbcDE123IKLMN67QRTUVWXYZ";
		$data .= "aBCdeijklmn123opq45rs67tuv89wxz";
		$data .= "0FGHOP89";
		$data .= "4J5fghSy";

		for($i = 0; $i < $length; $i++)
		{
			$random .= substr($data, (rand()%(strlen($data))), 1);
		}
		$random = $prefix . $random .$suffix;
		return $random;
	}
	
	
	/**
	 * knc_gc_get_admin_options meathod, retrieves knc gift coupon admin settings
	 * @access public
	 * addition by amitkhanna
	 */
	function knc_gc_get_admin_options() {

		global $wpdb;
		
		$row = $wpdb->get_row( $wpdb->prepare( "SELECT admin_options FROM ". KNC_GC_TABLE_ADMIN_OPTIONS ) );
		$knc_gc_options = unserialize($row->admin_options);
		
		// if found previously stored data then use that (basically re-update that)
		if (!empty($knc_gc_options)) {
			foreach ($knc_gc_options as $key => $option)
				$knc_gc_admin_options[$key] = $option;
		}	

		return $knc_gc_admin_options;
	}
	
	
	/**
	* knc_process_coupon_codes method, 
	* generate, email coupon codes & update existing coupons with "amount left", "is used", "active" 
	* @access public
	* addition by amitkhanna
	*/
	function knc_process_coupon_codes() {	
		global $wpdb, $wpsc_cart;

		// EXISTING COUPON CODE PROCESSING STARTS
		$knc_coupon_code = $this->cart_data['cart_discount_coupon'];
		$knc_applied_discount = $this->cart_data['cart_discount_value'];
		$knc_coupon_details  = $wpdb->get_row("SELECT * FROM  " .  KNC_TABLE_COUPON_CODES . "  WHERE coupon_code = '$knc_coupon_code'");
		
		$wpsc_coupon_details = $wpdb->get_row("SELECT * FROM  " .  WPSC_TABLE_COUPON_CODES . "  WHERE coupon_code = '$knc_coupon_code'");
		$knc_is_active = $wpsc_coupon_details->active;
		
		$knc_ip = 'is-percentage';
		$knc_coupon_is_percent = $wpsc_coupon_details->$knc_ip;
		
		// current date time
		$knc_date_now = date( 'Y-m-d H:i:s' );	
		
		// coupon amount left
		$knc_coupon_amount_left_old = $knc_coupon_details->amount_left;
		
		// if discount = amount left in coupon 
		// then apply discount and disable coupon
		if($knc_applied_discount == $knc_coupon_details->amount_left){
			$knc_result = $wpdb->update( WPSC_TABLE_COUPON_CODES, array('active' => 0, 'is-used' => 1 ), array( 'coupon_code' => $knc_coupon_code ) );																	
		}
		
		// amount left after subtracting discount
		if(!$knc_applied_discount<=0 || $knc_applied_discount!= null && $knc_applied_discount < $knc_coupon_amount_left_old){
			$knc_coupon_amount_left_new = $knc_coupon_amount_left_old - $knc_applied_discount;
		}
		
		// if coupon is % discount
		if($knc_coupon_is_percent == '1'){
			// deactive & mark coupon as used
			$update_result = $wpdb->update(
			WPSC_TABLE_COUPON_CODES, 
			array('is-used' => '1', 'active' => '0'), 
			array('coupon_code' => $knc_coupon_code) 
			);
			// set amount left = 0
			$update_result = $wpdb->update(
			KNC_TABLE_COUPON_CODES, 
			array('amount_left' => '0', 'last_used' => $knc_date_now), 
			array('coupon_code' => $knc_coupon_code) 
			);
		}else{
			// set the remaining balance
			$update_result = $wpdb->update(
			KNC_TABLE_COUPON_CODES, 
			array('amount_left' => $knc_coupon_amount_left_new, 'last_used' => $knc_date_now), 
			array('coupon_code' => $knc_coupon_code) 
			);
		}

		if($update_result!=false){
			$update_result = "COUPON VALUES UPDATED";
		}else{
			$update_result = "UPDATE UNSUCCESSFUL. TRY AGAIN.";
		}
		//  EXISTING COUPON CODE PROCESSING ENDS
		
		//  processes coupon products, generate & store coupon codes and email it to buyers

		$knc_coupon_data 		=  $this->knc_gc_get_admin_options();
		$knc_coupon_names 		=  (array)$knc_coupon_data['coupon_details']['coupon_name'];
		$knc_coupon_validities 	=  (array)$knc_coupon_data['coupon_details']['coupon_validity'];
		$knc_coupon_dmys 		=  (array)$knc_coupon_data['coupon_details']['coupon_dmy'];
		$knc_blog_name 			=	get_option('blogname');
		$knc_mail_from 			= 	$knc_coupon_data['mail_from'];
		$knc_mail_sub 			=	$knc_coupon_data['mail_subject'];
		$knc_mail_haystack		=	$knc_coupon_data['mail_message'];
		$knc_mail_txt_haystack	=	$knc_coupon_data['mail_message_txt'];
		$knc_test_mode			=	$knc_coupon_data['test_mode'];
		$knc_test_mail			=	$knc_coupon_data['test_mail'];
		$knc_first_name			=	$this->paypal_ipn_values['first_name'];
		$knc_last_name			=	$this->paypal_ipn_values['last_name'];
		
		static $knc_coupon_count= 0;
		$knc_mail_haystack		= nl2br($knc_coupon_data['mail_message']);
		$coupon_codes 			= Array();
		$discount_type 			= 0;
		$use_once 				= 0;
		$every_product			= 0;
		$start_date 			= date('Y-m-d') . " 00:00:00";
		
		$i = 1;
		
		//var_dump($this->paypal_ipn_values);
		//exit();
		
		if($_SESSION['knc_transaction_type']=='local'){
			$knc_total_cart_items = wpsc_cart_item_count();	
		}else{
			$knc_total_cart_items = $this->paypal_ipn_values['num_cart_items'];
		}
		
		for($i=1;$i<=$knc_total_cart_items;$i++)
		{
			// if coupon name matches the coupon name in IPN vars
			if($_SESSION['knc_transaction_type']=='local'){
				$knc_item_name = $this->paypal_ipn_values['item_name_'.$i];	// if transaction happens locally
			}else{
				$knc_item_name = $this->paypal_ipn_values['item_name'.$i];	// if paying using paypal
			}
			
			// if any of the "paypal item name" matches any of the "coupon names stored by plug-in"
			// then grab related details of "stored coupon " (eg: validity, dmy, one time etc.))
			foreach($knc_coupon_names as $key => $knc_coupon_name)
			{
				if(strncmp($knc_coupon_name, $knc_item_name, strlen($knc_coupon_name))==0)
				{									
					$knc_coupon_count++; 
					
					$couponValidity = $knc_coupon_validities[$key];
					$dmyStr = $knc_coupon_dmys[$key]; $dmyStr = explode('(', $dmyStr);
					$dmy = strtolower($dmyStr[0]);
					
					$end_date = date('Y-m-d', strtotime($couponValidity . " " . $dmy)) . " 00:00:00";
					$endDate_wo_time = explode(' ',$end_date);
					
					// coupon name
					$message_body .= "<br/><strong>" . $knc_item_name ."</strong><br/><br/>";
					$message_body_txt .= "\r\n" . $knc_item_name . "\r\n";

					// get coupon value to set as discount, from IPN vars
					// get coupon quantities
					if($_SESSION['knc_transaction_type']=='local'){
						$knc_discount = get_post_meta($this->paypal_ipn_values['item_number_'.$i], '_wpsc_price', true);
						$knc_item_quantity = $this->paypal_ipn_values['quantity_'.$i];
					}else{
						$knc_discount = get_post_meta($this->paypal_ipn_values['item_number'.$i], '_wpsc_price', true);
						$knc_item_quantity = $this->paypal_ipn_values['quantity'.$i];
					}
		
					// generate codes
					for($j=1;$j<=$knc_item_quantity;$j++){
						
						// generate coupon codes for emailing and db
						$coupon_codes[$j] = $this->knc_random_alpha_numeric(10);

						$message_body .= "Coupon Code: <strong>" . $coupon_codes[$j] . "</strong> | Value: <strong>" . $knc_discount . "</strong> | Expiry: <strong>" . $endDate_wo_time[0] . "</strong><br/>";
						$message_body_txt .= "Coupon Code: " . $coupon_codes[$j] . " | Value: " . $knc_discount . " | Expiry: " . $endDate_wo_time[0] . "\r\n";
						
						// insert coupon code into database and email it to the buyer
						if($wpdb->query("INSERT INTO `".WPSC_TABLE_COUPON_CODES."` ( `coupon_code` , `value` , `is-percentage` , `use-once` , `is-used` , `active` , `every_product` , `start` , `expiry`, `condition` ) VALUES ( '$coupon_codes[$j]', '$knc_discount', '$discount_type', '$use_once', '0', '1', '$every_product', '$start_date' , '$end_date' , '".serialize($new_rule)."' );")) 
						{  
						}
					}
					$message_body .= "<br/>";
				}
			}
		}							
		foreach ($this->paypal_ipn_values as $key => $value) 
		{
			// for debugging
			$message_body2 .= $key . " = " .$value ."<br/>";
		}
		
		// reset session coupon variables
		$_SESSION['knc_disc_value'] = null;	$_SESSION['coupon_numbers']  =   '';
		$_SESSION['current_coupon'] =   '';	$_SESSION['percent_applied'] = null;
		
		//var_dump($message_body_txt);
		//exit();
		
		
		if($knc_coupon_count!=0){

			// REPLACE TEMPLATE {TAGS}
			// coupon codes
			$knc_mail_message = str_replace('{COUPON CODES}', $message_body, $knc_mail_haystack);
			$knc_mail_message_txt = str_replace('{COUPON CODES}', $message_body_txt, $knc_mail_txt_haystack);
			// blog name
			$knc_mail_message = str_replace('{SITE NAME}', $knc_blog_name, $knc_mail_message);
			$knc_mail_message_txt = str_replace('{SITE NAME}', $knc_blog_name, $knc_mail_message_txt);
			// first name
			$knc_mail_message = str_replace('{FIRST NAME}', $knc_first_name, $knc_mail_message);
			$knc_mail_message_txt = str_replace('{FIRST NAME}', $knc_first_name, $knc_mail_message_txt);
			// last name
			$knc_mail_message = str_replace('{LAST NAME}', $knc_last_name, $knc_mail_message);
			$knc_mail_message_txt = str_replace('{LAST NAME}', $knc_last_name, $knc_mail_message_txt);
			
			// DEBUG VARS
			$knc_debug = false;
			
			if($knc_debug==true){
				$knc_mail_message .= $message_body2 ."<br/>";
				$knc_mail_message_txt .= $message_body2;
				$knc_mail_message .= $update_result ."<br/>";
				$knc_mail_message_txt .= $update_result;
				$knc_mail_message .= WPSC_TABLE_COUPON_CODES ."<br/>";
				$knc_mail_message_txt .= WPSC_TABLE_COUPON_CODES;
				$knc_mail_message .= "IS COUPON PERCENT: " . $knc_coupon_is_percent ."<br/>";
				$knc_mail_message_txt .= "IS COUPON PERCENT: " . $knc_coupon_is_percent;
				
				// DEBUG VARS - coupon values
				$knc_mail_message .= $knc_coupon_amount_left_old ."<br/>";
				$knc_mail_message .= $knc_applied_discount ."<br/>";
				$knc_mail_message .= $knc_coupon_amount_left_new ."<br/>";
				$knc_mail_message .= "transaction type: " . $_SESSION['knc_transaction_type'] ."<br/>";
				
				$knc_mail_message .= "****** DEBUG ENDS HERE ******" ."<br/>"; 
			}
			
			// MAIL TO WHOM
			if($knc_test_mode=='true'){
				$mailTo = $knc_test_mail;
			}else{
				$mailTo = $this->paypal_ipn_values['payer_email'];
			}
			
			// SEND MAIL
			$to 	  = $mailTo;
			$subject  = $knc_mail_sub;
			$htmlMessage  = "<html><head><title>Coupon Codes</title></head><body>";
			$htmlMessage .= "$knc_mail_message";
			$htmlMessage .= "</body></html>";
	
			require_once ABSPATH . WPINC . '/class-phpmailer.php';


			$knc_mailer             = new PHPMailer();
			$knc_mailer->From       = $knc_mail_from;
			$knc_mailer->FromName 	= $knc_mail_from;
			$knc_mailer->AddReplyTo($knc_mail_from, 'service department');
			$knc_mailer->Subject    = $subject;
			$knc_mailer->isHTML(true);
			$knc_mailer->AltBody    = $knc_mail_message_txt;
			$knc_mailer->MsgHTML($htmlMessage);
			$knc_mailer->AddAddress($to);
			$knc_mailer->Send();

		}
	}
		
	/**
	* process_gateway_notification method, receives data from the payment gateway
	* @access public
	*/
	function process_gateway_notification() {
		
	  // Compare the received store owner email address to the set one
		if(strtolower($this->paypal_ipn_values['receiver_email']) == strtolower(get_option('paypal_multiple_business'))) {
			switch($this->paypal_ipn_values['txn_type']) {
				case 'cart':
				case 'express_checkout':
					if((float)$this->paypal_ipn_values['mc_gross'] == (float)$this->cart_data['total_price']) {
						$this->set_transaction_details($this->paypal_ipn_values['txn_id'], 3);
						transaction_results($this->cart_data['session_id'],false);
						
						// generate and manage existing coupon codes
						// addition by amitkhanna 
						$this->knc_process_coupon_codes();
					}
				break;

				case 'subscr_signup':
				case 'subscr_payment':
					$this->set_transaction_details($this->paypal_ipn_values['subscr_id'], 3);
					foreach($this->cart_items as $cart_row) {
						if($cart_row['is_recurring'] == true) {
							do_action('wpsc_activate_subscription', $cart_row['cart_item_id'], $this->paypal_ipn_values['subscr_id']);
						}
					}
					transaction_results($this->cart_data['session_id'],false);
				break;

				case 'subscr_cancel':
				case 'subscr_eot':
				case 'subscr_failed':
					foreach($this->cart_items as $cart_row) {
						$altered_count = 0;
						if((bool)$cart_row['is_recurring'] == true) {
							$altered_count++;
							wpsc_update_cartmeta($cart_row['cart_item_id'], 'is_subscribed', 0);
						}
					}
				break;

				default:
				break;
			}
		}

		$message = "
		{$this->paypal_ipn_values['receiver_email']} => ".get_option('paypal_multiple_business')."
		{$this->paypal_ipn_values['txn_type']}
		{$this->paypal_ipn_values['mc_gross']} => {$this->cart_data['total_price']}
		{$this->paypal_ipn_values['txn_id']}

		".print_r($this->cart_items, true)."
		{$altered_count}
		";
	}



	function format_price($price, $paypal_currency_code = null) {
		if (!isset($paypal_currency_code)) {
			$paypal_currency_code = get_option('paypal_curcode');
		}
		switch($paypal_currency_code) {
			case "JPY":
			$decimal_places = 0;
			break;

			case "HUF":
			$decimal_places = 0;

			default:
			$decimal_places = 2;
			break;
		}
		$price = number_format(sprintf("%01.2f",$price),$decimal_places,'.','');
		return $price;
	}
}


/**
 * submit_paypal_multiple function.
 *
 * Use this for now, but it will eventually be replaced with a better form API for gateways
 * @access public
 * @return void
 */
function knc_submit_paypal_multiple(){
  if(isset($_POST['paypal_multiple_business'])) {
    update_option('paypal_multiple_business', $_POST['paypal_multiple_business']);
	}

  if(isset($_POST['paypal_multiple_url'])) {
    update_option('paypal_multiple_url', $_POST['paypal_multiple_url']);
	}

  if(isset($_POST['paypal_curcode'])) {
    update_option('paypal_curcode', $_POST['paypal_curcode']);
	}

  if(isset($_POST['paypal_curcode'])) {
    update_option('paypal_curcode', $_POST['paypal_curcode']);
	}

  if(isset($_POST['paypal_ipn'])) {
    update_option('paypal_ipn', (int)$_POST['paypal_ipn']);
	}

  if(isset($_POST['address_override'])) {
    update_option('address_override', (int)$_POST['address_override']);
	}
  if(isset($_POST['paypal_ship'])) {
    update_option('paypal_ship', (int)$_POST['paypal_ship']);
	}

  if (!isset($_POST['paypal_form'])) $_POST['paypal_form'] = array();
  foreach((array)$_POST['paypal_form'] as $form => $value) {
    update_option(('paypal_form_'.$form), $value);
	}

  return true;
}



/**
 * form_paypal_multiple function.
 *
 * Use this for now, but it will eventually be replaced with a better form API for gateways
 * @access public
 * @return void
 */
function knc_form_paypal_multiple() {
  global $wpdb, $wpsc_gateways;
  $output = "
  <tr>
      <td>Username:
      </td>
      <td>
      <input type='text' size='40' value='".get_option('paypal_multiple_business')."' name='paypal_multiple_business' />
      </td>
  </tr>
  <tr>
      <td>Url:
      </td>
      <td>
      <input type='text' size='40' value='".get_option('paypal_multiple_url')."' name='paypal_multiple_url' /> <br />

      </td>
  </tr>
  ";


	$paypal_ipn = get_option('paypal_ipn');
	$paypal_ipn1 = "";
	$paypal_ipn2 = "";
	switch($paypal_ipn) {
		case 0:
		$paypal_ipn2 = "checked ='checked'";
		break;

		case 1:
		$paypal_ipn1 = "checked ='checked'";
		break;
	}
	$paypal_ship = get_option('paypal_ship');
	$paypal_ship1 = "";
	$paypal_ship2 = "";
	switch($paypal_ship){
		case 1:
		$paypal_ship1 = "checked='checked'";
		break;

		case 0:
		default:
		$paypal_ship2 = "checked='checked'";
		break;

	}
	$address_override = get_option('address_override');
	$address_override1 = "";
	$address_override2 = "";
	switch($address_override) {
		case 1:
		$address_override1 = "checked ='checked'";
		break;

		case 0:
		default:
		$address_override2 = "checked ='checked'";
		break;
	}
	$output .= "
   <tr>
     <td>IPN :
     </td>
     <td>
       <input type='radio' value='1' name='paypal_ipn' id='paypal_ipn1' ".$paypal_ipn1." /> <label for='paypal_ipn1'>".__('Yes', 'wpsc')."</label> &nbsp;
       <input type='radio' value='0' name='paypal_ipn' id='paypal_ipn2' ".$paypal_ipn2." /> <label for='paypal_ipn2'>".__('No', 'wpsc')."</label>
     </td>
  </tr>
  <tr>
     <td style='padding-bottom: 0px;'>Send shipping details:
     </td>
     <td style='padding-bottom: 0px;'>
       <input type='radio' value='1' name='paypal_ship' id='paypal_ship1' ".$paypal_ship1." /> <label for='paypal_ship1'>".__('Yes', 'wpsc')."</label> &nbsp;
       <input type='radio' value='0' name='paypal_ship' id='paypal_ship2' ".$paypal_ship2." /> <label for='paypal_ship2'>".__('No', 'wpsc')."</label>

  	</td>
  </tr>
  <tr>
  	<td colspan='2'>
  	<span  class='wpscsmall description'>
  	Note: If your checkout page does not have a shipping details section, or if you don't want to send Paypal shipping information. You should change Send shipping details option to No.</span>
  	</td>
  </tr>
  <tr>
     <td style='padding-bottom: 0px;'>
      Address Override:
     </td>
     <td style='padding-bottom: 0px;'>
       <input type='radio' value='1' name='address_override' id='address_override1' ".$address_override1." /> <label for='address_override1'>".__('Yes', 'wpsc')."</label> &nbsp;
       <input type='radio' value='0' name='address_override' id='address_override2' ".$address_override2." /> <label for='address_override2'>".__('No', 'wpsc')."</label>
     </td>
   </tr>
   <tr>
  	<td colspan='2'>
  	<span  class='wpscsmall description'>
  	This setting affects your PayPal purchase log. If your customers already have a PayPal account PayPal will try to populate your PayPal Purchase Log with their PayPal address. This setting tries to replace the address in the PayPal purchase log with the Address customers enter on your Checkout page.
  	</span>
  	</td>
   </tr>\n";



	$store_currency_data = $wpdb->get_row("SELECT `code`, `currency` FROM `".WPSC_TABLE_CURRENCY_LIST."` WHERE `id` IN ('".absint(get_option('currency_type'))."')", ARRAY_A);
	$current_currency = get_option('paypal_curcode');
	if(($current_currency == '') && in_array($store_currency_data['code'], $wpsc_gateways['wpsc_merchant_paypal_standard']['supported_currencies']['currency_list'])) {
		update_option('paypal_curcode', $store_currency_data['code']);
		$current_currency = $store_currency_data['code'];
	}

	if($current_currency != $store_currency_data['code']) {
		$output .= "
  <tr>
      <td colspan='2'><strong class='form_group'>".__('Currency Converter')."</td>
  </tr>
  <tr>
		<td colspan='2'>".sprintf(__('Your website uses <strong>%s</strong>. This currency is not supported by PayPal, please  select a currency using the drop down menu below. Buyers on your site will still pay in your local currency however we will send the order through to Paypal using the currency you choose below.', 'wpsc'), $store_currency_data['currency'])."</td>
		</tr>\n";

		$output .= "    <tr>\n";



		$output .= "    <td>Select Currency:</td>\n";
		$output .= "          <td>\n";
		$output .= "            <select name='paypal_curcode'>\n";

		$paypal_currency_list = $wpsc_gateways['wpsc_merchant_paypal_standard']['supported_currencies']['currency_list'];

		$currency_list = $wpdb->get_results("SELECT DISTINCT `code`, `currency` FROM `".WPSC_TABLE_CURRENCY_LIST."` WHERE `code` IN ('".implode("','",$paypal_currency_list)."')", ARRAY_A);

		foreach($currency_list as $currency_item) {
			$selected_currency = '';
			if($current_currency == $currency_item['code']) {
				$selected_currency = "selected='selected'";
			}
			$output .= "<option ".$selected_currency." value='{$currency_item['code']}'>{$currency_item['currency']}</option>";
		}
		$output .= "            </select> \n";
		$output .= "          </td>\n";
		$output .= "       </tr>\n";
	}


$output .= "
   <tr class='update_gateway' >
		<td colspan='2'>
			<div class='submit'>
			<input type='submit' value='".__('Update &raquo;', 'wpsc')."' name='updateoption'/>
		</div>
		</td>
	</tr>

	<tr class='firstrowth'>
		<td style='border-bottom: medium none;' colspan='2'>
			<strong class='form_group'>Forms Sent to Gateway</strong>
		</td>
	</tr>

    <tr>
      <td>
      First Name Field
      </td>
      <td>
      <select name='paypal_form[first_name]'>
      ".nzshpcrt_form_field_list(get_option('paypal_form_first_name'))."
      </select>
      </td>
  </tr>
    <tr>
      <td>
      Last Name Field
      </td>
      <td>
      <select name='paypal_form[last_name]'>
      ".nzshpcrt_form_field_list(get_option('paypal_form_last_name'))."
      </select>
      </td>
  </tr>
    <tr>
      <td>
      Address Field
      </td>
      <td>
      <select name='paypal_form[address]'>
      ".nzshpcrt_form_field_list(get_option('paypal_form_address'))."
      </select>
      </td>
  </tr>
  <tr>
      <td>
      City Field
      </td>
      <td>
      <select name='paypal_form[city]'>
      ".nzshpcrt_form_field_list(get_option('paypal_form_city'))."
      </select>
      </td>
  </tr>
  <tr>
      <td>
      State Field
      </td>
      <td>
      <select name='paypal_form[state]'>
      ".nzshpcrt_form_field_list(get_option('paypal_form_state'))."
      </select>
      </td>
  </tr>
  <tr>
      <td>
      Postal code/Zip code Field
      </td>
      <td>
      <select name='paypal_form[post_code]'>
      ".nzshpcrt_form_field_list(get_option('paypal_form_post_code'))."
      </select>
      </td>
  </tr>
  <tr>
      <td>
      Country Field
      </td>
      <td>
      <select name='paypal_form[country]'>
      ".nzshpcrt_form_field_list(get_option('paypal_form_country'))."
      </select>
      </td>
  </tr> ";

  return $output;
}
?>
