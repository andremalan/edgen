<?php

// Core constants
function knc_gc_core_const()
{
	global $table_prefix, $wpdb;
	
	$knc_gc_plugin_url = WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__)); 
	define('KNC_GC_URL', $knc_gc_plugin_url);
	
	// Use the DB method if it exists
	if ( !empty( $wpdb->prefix ) )
		$wp_table_prefix = $wpdb->prefix;
	
	// Fallback on the wp_config.php global
	else if ( !empty( $table_prefix ) )
		$wp_table_prefix = $table_prefix;

	define('KNC_GC_TABLE_COUPON_CODES' 		,  "{$wp_table_prefix}knc_gc_details");
	define('KNC_GC_TABLE_ADMIN_OPTIONS'		,  "{$wp_table_prefix}knc_gc_options");
	define('KNC_GC_TABLE_ADMIN_DB_VERSION'  ,  "knc_gc_options_db_version");
	define('KNC_GC_TABLE_COUPON_DB_VERSION' ,  "knc_gc_coupon_db_version");
	
	define('KNC_PAYMENT_GATEWAY' ,  "wpsc_merchant_paypal_standard_knc_mu");
	
}


// Error constants
function knc_gc_error_const()
{
	$knc_error_incorrect_coupon = "Incorrect coupon number.";
	$knc_error_invalid_disc_value = "Please enter a valid discount value.";
	$knc_error_empty_field = "Coupon number field empty. Please enter a coupon number.";
	$knc_error_disc_more_than_cart = "Entered value is more than your total purchase value. Applying maximum discount possible.";
	
	define('KNC_ERR_INCORRECT_CODE', $knc_error_incorrect_coupon);
	define('KNC_ERR_INVALID_DISC_VALUE', $knc_error_invalid_disc_value);
	define('KNC_ERR_EMPTY_FIELD', $knc_error_empty_field);
	define('KNC_ERR_DISC_MORETHAN_CART', $knc_error_disc_more_than_cart);
}

?>