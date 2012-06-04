<?php
/*
Plugin Name: Enhanced Recent Posts
Version: 1.3.1
Plugin URI: http://enhanced-recent-posts.vincentprat.info
Description: A plugin for wordpress which enhances the built-in "Recent Posts" widget. Please make a donation if you are satisfied.
Author: Vincent Prat
Author URI: http://www.vincentprat.info
*/

/*  Copyright 2006 Vincent Prat  (email : vpratfr@yahoo.fr)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/


//############################################################################
// Stop direct call
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { 
	die('You are not allowed to call this page directly.'); 
}
//############################################################################

//############################################################################
// you can deactivate the javascript effect by setting the next variable to false
define('ENHANCED_RECENT_POSTS_USE_JAVASCRIPT', true);					
//############################################################################

//############################################################################
// plugin directory
define('ENHANCED_RECENT_POSTS_DIR', dirname (__FILE__));	

// i18n plugin domain 
define('ENHANCED_RECENT_POSTS_I18N_DOMAIN', 'enhanced-recent-posts');

// The options of the plugin
define('ENHANCED_RECENT_POSTS_PLUGIN_OPTIONS', 'enh_rp_plugin_options');	
define('ENHANCED_RECENT_POSTS_WIDGET_OPTIONS', 'enh_rp_widget_options');	
//############################################################################

//############################################################################
// Include the plugin files
require_once(ENHANCED_RECENT_POSTS_DIR . '/includes/plugin-class.php');
require_once(ENHANCED_RECENT_POSTS_DIR . '/includes/widget-class.php');
//############################################################################

//############################################################################
// Init the plugin classes
global $enh_rp_plugin, $enh_rp_widget;

$enh_rp_plugin = new EnhancedRecentPostsPlugin();
$enh_rp_widget = new EnhancedRecentPostsWidget();
//############################################################################

//############################################################################
// Load the plugin text domain for internationalisation
if (!function_exists('enh_rp_init_i18n')) {
	function enh_rp_init_i18n() {
		load_plugin_textdomain(ENHANCED_RECENT_POSTS_I18N_DOMAIN, 'wp-content/plugins/enhanced-recent-posts');
	} // function enh_rp_init_i18n()

	enh_rp_init_i18n();
} // if (!function_exists('enh_rp_init_i18n'))
//############################################################################

//############################################################################
// Add filters and actions
add_action('widgets_init', array(&$enh_rp_widget, 'register_widget'));

if (is_admin()) {
	add_action(
		'activate_enhanced-recent-posts/enhanced-recent-posts.php',
		array(&$enh_rp_plugin, 'activate'));
} else {
}
//############################################################################

//############################################################################
// Template functions for direct use in themes

//############################################################################


?>