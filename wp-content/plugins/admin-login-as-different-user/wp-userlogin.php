<?php

	/* 
		Package:  Admin Login As Different User
		A script that will enable the user to login using username.
	*/

	require('./../../../wp-load.php');

	global $wpdb, $table_prefix,$current_user;

	//get the user_name
	$user_login = trim(addslashes($_POST['user_name']));

	//get the userid by username
	$user = get_userdatabylogin($user_login);
	$user_id = $user->ID;

	//login
	wp_set_current_user($user_id, $user_login);
	wp_set_auth_cookie($user_id);
	do_action('wp_login', $user_login);

	$redirect_to = admin_url('profile.php');
	wp_safe_redirect($redirect_to);