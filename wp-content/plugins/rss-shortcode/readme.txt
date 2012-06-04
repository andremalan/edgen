=== RSS Shortcode ===
Contributors: joostdevalk
Donate link: http://yoast.com/
Tags: rss, shortcode
Requires at least: 2.6
Tested up to: 3.0
Stable tag: 0.2

Simple plugin to show RSS feeds in posts and pages using a shortcode.

== Description ==

Adds a simple to use [rss] shortcode with a couple of options:

* feed, to put in the feed URL
* num, to specify the number of items to show, defaults to 5
* excerpt (true|false), whether to show an excerpt or not, defaults to true
* target (defaults to none), to make links open in a new page (might break your html validity if under a strict doctype)

Example use:

[rss feed="http://yoast.com/feed/" num="10" excerpt="false"]

Or:

[rss feed="http://yoast.com/feed/" num="5" excerpt="true" target="_blank"]

* Find out more about the [RSS Shortcode](http://yoast.com/wordpress/rss-shortcode/) plugin
* Check out the other [WordPress plugins](http://yoast.com/wordpress/) by the author

== Installation ==

1. Upload the `rss-shortcode` folder to the `/wp-content/plugins/` directory.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Use the shortcode in your posts and pages.

== Screenshots ==

1. A simple listing of posts created by the RSS Shortcode.
2. The shortcode that created the listing above.

== Changelog ==

= 0.2 =
* Added the option to specify a link target in the shortcode.

= 0.1 =
* Initial release.