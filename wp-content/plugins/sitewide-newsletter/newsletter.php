<?php
/*
Plugin Name: Get User Emails
Description: Allows site administrators to send a newsletter to all users
Version: 0.4
Author: Chris Taylor + Andre Malan
Author URI: http://andremalan.net
Plugin URI: http://ideahack.com
*/
// when the admin menu is built
if (defined('WP_ALLOW_MULTISITE') && WP_ALLOW_MULTISITE) {
	add_action('network_admin_menu', 'sitewide_newsletters_add_admin');
} else {
	add_action('admin_menu', 'sitewide_newsletters_add_admin');
}

// add the admin newsletters button
function sitewide_newsletters_add_admin() {
	global $current_user;
	add_submenu_page('users.php', 'Get user emails', 'Get user emails', 'edit_users', 'user_emails', 'sitewide_newsletters');
}

// build the newsletters form
function sitewide_newsletters()
{
	global $current_user, $wpdb;
	// if sending a newsletter
	if (@$_POST["fromemail"] != "") {
			
			$message_headers = 'From:<' . addslashes($_POST["fromemail"]) . '>' . "\r\n" .
			'Reply-To: ' . get_site_option("admin_email") . '' . "\r\n" .
			'X-Mailer: PHP/' . phpversion();
			
				$emails = $wpdb->get_results( "SELECT user_email FROM $wpdb->users");
			  $email_list = array();
			  //$admin_email = get_site_option("admin_email");
			  $admin_email = $_POST["fromemail"];
				foreach ($emails as $email) {		
					$email_list[] = $email->user_email;
				}
				$content = implode(", ", $email_list);
				wp_mail( $admin_email, 'Your Current Users',"Your current users:  " . $content);
	}
	
	print '
	<div class="wrap">
	';
	print '
	<h2>Get User Emails</h2>
	
	<p>Enter your email below to be emailed a list of site users.</p>
	
	<form action="users.php?page=user_emails" method="post">
	
		<fieldset>
		
		
		<p><label for="fromemail" style="float: left;width: 15%;">To email</label><input type="text" name="fromemail" id="fromemail" style="width: 80%" value="' . get_site_option("admin_email") . '" /></p>
			
		
		<p><label for="send_sitewide_newsletter" style="float: left;width: 15%;">Send newsletter</label><input type="submit" name="send_sitewide_newsletter" id="send_sitewide_newsletter" value="Get Emails" class="button" /></p>
		
		</fieldset>

	</form>
	';
	print '
	</div>
	';
}
?>