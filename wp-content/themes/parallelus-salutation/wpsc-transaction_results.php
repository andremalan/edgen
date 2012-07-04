<?php
	/**
	 * The Transaction Results Theme.
	 *
	 * Displays everything within transaction results.  Hopefully much more useable than the previous implementation.
	 *
	 * @package WPSC
	 * @since WPSC 3.8
	 */

	global $purchase_log, $errorcode, $sessionid, $echo_to_screen, $cart, $message_html;
?>
<div class="wrap">
  <div>
    <iframe src="//www.facebook.com/plugins/like.php?href=http%3A%2F%2Feducationgeneration.org&amp;send=false&amp;layout=standard&amp;width=450&amp;show_faces=true&amp;action=recommend&amp;colorscheme=light&amp;font&amp;height=80&amp;appId=241103922605192" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:450px; height:80px;" allowTransparency="true"></iframe>
  </div>
<?php
	echo wpsc_transaction_theme();
	if ( ( true === $echo_to_screen ) && ( $cart != null ) && ( $errorcode == 0 ) && ( $sessionid != null ) ) {			
		
		// Code to check whether transaction is processed, true if accepted false if pending or incomplete
		
		//ideahack, making it not display the email. 		
		//echo "<br />" . wpautop(str_replace("$",'\$',$message_html));						
		echo "Thanks for your donation! You should receive a confirmation email shortly. In the mean time, why not use the tweet or recommend button above to let others know?";
		//ideahack end
	}elseif ( true === $echo_to_screen && ( !isset($purchase_log) ) ) {
			_e('Oops, there is nothing in your cart.', 'wpsc') . "<a href=".get_option("product_list_url").">" . __('Please visit our shop', 'wpsc') . "</a>";
	}
?>	
</div>
