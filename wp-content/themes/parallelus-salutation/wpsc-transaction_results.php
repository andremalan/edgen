<?php
	/**
	 * The Transaction Results Theme.
	 *
	 * Displays everything within transaction results.  Hopefully much more useable than the previous implementation.
	 *
	 * @package WPSC
	 * @since WPSC 3.8
	 */
          
	global $purchase_log, $errorcode, $sessionid, $echo_to_screen, $cart, $message_html, $current_user,$wpdb;
	$key = "user_email";
	$email =  get_user_meta($current_user->id, $key,true) ;
	$row = $wpdb->get_row( $wpdb->prepare( "SELECT user_login FROM ".  WP_TABLE_USERS  ) );	
	$current_user->id;


?>
	
<div class="wrap">

<?php
	
	echo wpsc_transaction_theme();
	
	if ( ( true === $echo_to_screen ) && ( $cart != null ) && ( $errorcode == 0 ) && ( $sessionid != null ) ) {			
		
		// Code to check whether transaction is processed, true if accepted false if pending or incomplete
		
		
		echo "<br />" . wpautop(str_replace("$",'\$',$message_html));	
				
	}elseif ( true === $echo_to_screen && ( !isset($purchase_log) ) ) {
			_e('Oops, there is nothing in your cart.', 'wpsc') . "<a href=".get_option("product_list_url").">" . __('Please visit our shop', 'wpsc') . "</a>";
	}
// echo '<br>';
//var_dump($echo_to_screen);
// echo '<br>';
//var_dump($purchase_log);
// echo '<br>';
//var_dump($sessionid);
echo '<br>';
//var_dump($cart);
// echo '<br>';
//var_dump($message_html);
// echo '<br>';
//var_dump($errorcode);

delete_user_meta($current_user->id, $key);
delete_user_meta($current_user->id, 'use_email');


?>	

</div>