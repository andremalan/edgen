<?php
/*
Plugin Name: Admin Login As Different User
Plugin URI: http://wordpress.org/extend/plugins/admin-login-as-different-user/
Description: A plugin that will allow the administrator to login as different user
Version: 1.0
Author: James Bolongan
Author URI:
*/

add_action('admin_menu', 'login_different_user_menu');

function login_different_user_menu() {
	add_submenu_page( 'tools.php', 'Login As....', 'Login As....', 'administrator', 'login-different-user', 'login_different_user_function');
}

function login_different_user_function()	{
	global $wpdb, $table_prefix,$current_user;

	$task = trim(addslashes($_GET['task']));
	if($task == 'process')	{
		test();
	}
	else	{
		$action_url = WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__)).plugin_basename('wp-userlogin.php');
		?>
		<div class="wrap">
			<h2>Log In as a Different User</h2>
			<form method="post" action="<?php echo $action_url; ?>">
				<label for="username">Log in as</label>
				<select name="user_name" id="user_name">
				<?php
				
				$rows = $wpdb->get_results( "SELECT ID,user_login FROM `".$table_prefix."users` ORDER BY user_login ASC" );
				if(count($rows) > 0)	{
					$userlevel_ids = array();
					foreach($rows as $row)	{
						?>
						<option value="<?php echo $row->user_login; ?>"><?php echo $row->user_login; ?> (<?php echo $row->ID;?>)</option>
						<?php
					}
				}
				?>
				</select>
				<br>
				<p class="submit">
						<input type="submit" tabindex="100" value="Log In" class="button-primary" id="wp-submit" name="wp-submit">
				</p>
			</form>
		</div>
		<?php
	}
}