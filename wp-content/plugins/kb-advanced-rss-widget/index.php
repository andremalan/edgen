<?php
/*
Plugin Name: KB Advanced RSS Widget
Description: Gives user complete control over how feeds are displayed.
Author: Adam R. Brown
Version: 2.8.02
Plugin URI: http://adambrown.info/b/widgets/category/kb-advanced-rss/
Author URI: http://adambrown.info/
*/



/* CHANGE LOG
	1.0 	Original
	1.0.1 	Simply some code
	1.1	Fix bug that kept it from working on pre-PHP5 systems
	1.1a	Minor text changes
	1.2	Title can be blank
	1.2.1	bug
	1.3	Workaround for a WP v2.2 bug
	1.3.1	Better troubleshooter
	1.4	More robust compatibility with 2.2+
	1.5	New options 
		- can now choose whether to link title to RSS feed. 
		- use ?kbrss_cache=flush to force purge of cache.
		- Easier to have more than 9 widgets (use the setting below)
		- Now defaults to 18 widgets max (for folks making a news aggregation page, I guess)
	1.5.1	Links to correct URL if link option is selected.
		- now defaults to 20 max
	1.5.2	Embeds only the necessary CSS info.
	1.5.3	works with wp2.2.1. When will the developers quit screwing with the widgets api on every WP update? grrrrr
	1.5.4	for real this time
	1.6	option to convert feed from ISO-8859-1 to UTF-8. Thanks to Christoph Juergens (www.cjuergens.de)
	1.6.1	new setting: easily change the max number of items a feed can have.
	1.7	several:
		- checking cache freshness is now an optional setting
		- unless KBRSS_WPMU is true, fewer filters on what can be in widget options
	2.0	Rewritten for better performance and easier customization. Now written as a class. Also uses a new syntax in the widget admin. See FAQ.
	2.0.1	Minor bug fix that would have caused invalid HTML output. Sorry.
	2.1	Adding options. Check it out.
		- checkbox to reverse order of feed
		- checkbox to hide feed when it's empty or down
		- opts:bypasssecurity to allow javascript in feeds you trust
		- fix bug so it now strips feed:// off the front like it was supposed to already :)
	2.1.1	Fix typo on line 148 that prevented the kb_rss_template() function from working correctly
	2.1.2	Some minor tweaks in preparation for wp 2.5.
	2.1.3	fixes for wp 2.7. WOULD THE WORDPRESS DEVELOPERS PLEASE QUIT SCREWING WITH THE WIDGET API!!!!!
	2.8	Completely rewritten to work in 2.8. WOULD THE WP DEVELOPERS PLEASE QUIT SCREWING WITH THE WIDGET API!!!!!
		- Fixes the "multiple widgets" problem caused by the developers' changes to the API in WP 2.8
		- Now includes its own copy of magpie, since WP is moving toward simplepie
		- Relies entirely on independent rss caching, not magpie's caching
		- REMOVED the ability to call this plugin manually from a template. MUST be used as a widget.
	2.8.01
		Minor bugfix. Oops. Saves correctly now.
	2.8.02 Restored the utf8 option
*/







// this is an old plugin--one of the first plugins released using the
// new (at the time) widgets API. Because the WP developers don't care
// much about plugin developers, they changed the widgets API several
// times over the next several versions of WP. So we need to start
// by checking which version of WP is running. If it's an old one, then
// we need to load up an old version of the plugin.
function kbar_load_version(){
	global $wp_version;
	
	$p = dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'versions' . DIRECTORY_SEPARATOR;
	
	// trunk versions sometimes have names like "2.8-bleeding-edge"
	// all we want is the numbers and dots
	$v = preg_replace( '~[^0-9\.]~', '', $wp_version );	
	
	$v = explode('.',$v);
	
	if ( 1==$v[0]  ||  ( 2==$v[0] && 7>$v[1] ) )
		return $p . 'pre-wp-2-7.php';
	elseif ( 2==$v[0] && 7==$v[1] )
		return $p . 'wp-2-7.php';
	else
		return $p . 'wp-2-8.php';
}
require_once( kbar_load_version() );




?>