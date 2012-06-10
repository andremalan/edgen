=== Easy Chart Builder for WordPress ===

Contributors: dyerware
Donate link: http://www.dyerware.com/main/products/easy-chart-builder/easy-chart-builder-plugin-for-wordpress.html
Tags: chart,graph,charts,graphs,line,review,rating,comparison,mobile,shortcode,dyerware
Requires at least: 2.8
Tested up to: 3.1.3
Stable tag: 1.2

This plugin allows you to easily create charts within your blog by use of shortcodes.


== Description ==

This plugin allows you to easily insert charts into your blog, by way of shortcodes. While multipurpose, the chart system is intended to make it easy for posting detailed review measurements of some sort, such as poll data, video card comparisons, sports scores, etc.  You specify the names of the devices being measured, the tests performed, and each device's measurements for said test. 

An admin settings page allows you to tailor many shortcode defaults, simplifying overall use.

The shortcode is supported on posts, pages, and in widgets.

Graphs scale to meet the size of the end-user client display, and support mobile displays such as those rendered by the wptouch plugin. 

Charts supported are horizontal bar, stacked horizontal bar, vertical bar, stacked vertical bar, line graph, and pie.  More can be added if desired.

PHP5 Required.

The shortcode format is:

**[easychart argument="value" ...]**

For a complete example and more detailed documentation, visit the plugin home page.  If you do not understand these instructions, please ask questions in the comments section on the plugin home page.  We will be more than happy to answer them.

Go here for up-to-date usage and examples:
[EasyChart Tutorial](http://www.dyerware.com/main/products/easy-chart-builder/easy-chart-builder-plugin-for-wordpress.html "EasyChart Tutorial")


== Installation ==

1. Verify that you have PHP5, which is required for this plugin.

2. Upload the `easy-chart-builder` directory to the `/wp-content/plugins/` directory

3. Activate the plugin through the 'Plugins' menu in WordPress

Now you can insert the shortcodes within your posts and pages.


== Frequently Asked Questions ==

For an up-to-date FAQ, please visit:
[EasyChart FAQ](http://www.dyerware.com/main/products/easy-chart-builder/easy-chart-builder-faq.html "EasyChart FAQ")

== Screenshots ==

1. The horizontal bar chart sans data table (colors are configurable)

2. The vertical bar chart with data table (colors are configurable)

3. The pie chart sans data table (colors are configurable).

4. The data table (optional)

5. A chart showing the watermark feature.

6. The line chart sans data table (colors are configurable)

7. Marker shown.  You can add markers on datapoints for discussion

8. Admin settings panel (out of date)

== Upgrade Notice ==

= 1.2 =
Improved unicode support for groupnames and valuenames.

= 1.1 =
Added scatter plot chart type (type "scatter").  This one assumes the groupXvalues are xy pairs, so "x1,y1,x2,y2,(etc)".  This is different than the other chart types.
Added radar chart type (type "radar").
Improved unicode support for groupnames and valuenames.

= 1.0 =
Added axis option to disable any of the two axis (or both): both, none, values, names.
Added two chart types: horizbaroverlap, vertbaroverlap
Improved unicode support for groupnames and valuenames.

= 0.9.8 =
Added admin panel feature to customize the datatable button text
Added admin panel feature to always show the datatable (no button to show/hide).

= 0.9.7 =
Added grid background option (use argument 'grid' with true or false).
Currency now results in comma separated values in table data.

= 0.9.4 =
Some installs did not see color picker guy in admin panel

= 0.9.3 =
Compatibility issue with plugin dir.

= 0.9.2 =
Admin panel color fields are now visual and feature color pickers.  widget fix.

= 0.9.1 =
Corrected wrong title in admin panel.  Updated screenshot.

= 0.9 =
An admin settings page now allows you to tailor many shortcode defaults, simplifying your customization.  Javascript files now only load when required.

= 0.8.2 =
Fixed wrong label order for horizontal chart types

= 0.8.1 =
Fixed stacked charts problem with value axis

= 0.8 =
Markers, two new chart types, and bugfixes

= 0.7 =
Shortcode can now be used in widgets.  New optional parameter, "minaxis".  Now supports graphing up to 12 groups (formerly 8).

= 0.6.1 =
Watermark glitch introduced in 0.6

= 0.6 =
New chart type, new currency display options

= 0.5.1 =
Adds watermark feature


== Changelog ==

= 1.2 =
 * Improved unicode support for groupnames and valuenames.

= 1.1 =
 * Added scatter plot chart type (type "scatter").  This one assumes the groupXvalues are xy pairs, so "x1,y1,x2,y2,(etc)".  This is different than the other chart types.
 * Added radar chart type (type "radar").
 * Improved unicode support for groupnames and valuenames.

= 1.0 =
 * Added axis option to disable any of the two axis (or both): both, none, values, names.
 * Added two chart types: horizbaroverlap, vertbaroverlap
 * Improved unicode support for groupnames and valuenames.

= 0.9.8 =
 * Added admin panel feature to customize the datatable button text
 * Added admin panel feature to always show the datatable (no button to show/hide)

= 0.9.7 =
 * Fixed commas.

= 0.9.6 =
 * Added grid background option (use argument 'grid' with true or false).
 * Currency now results in comma separated values in table data.

= 0.9.4 =
 * Fix: some installs did not see color picker gui in admin panel

= 0.9.3 =
 * Compatibility issue with plugin dir.

= 0.9.2 =
 * Admin panel color fields are now visual and feature color pickers
 * Fix for charts in sidebar widgets

= 0.9.1 =
 * Fixed incorrect admin panel title.
 * Updated to include screenshot of admin panel.

= 0.9 =
 * An admin settings panel for ECB is now available.  Customize gobs of default settings without having to enter them via shortcode (and when we say gobs we mean it!).
 * Javascript files now only load when required, making for faster page loads when no chart exists.
 * Admin settings panel has fast-links to tutorial and dedicated forum (if you have posted comments on our site, your account is synced with forum).

= 0.8.2 =
 * Fixed wrong label order for horizontal chart types

= 0.8.1 =
 * Fixed stacked charts problem with value axis

= 0.8 =
 * New: Two new chart types (horizbarstack and vertbarstack)
 * New: Markers.  Place one or more marker in the graph.
 * Linecharts now work with clipping
 * Watermark bug with clipping fixed
 * Smaller javascript component for faster loading

= 0.7 =
 * The shortcode can now be used in widgets, for example on the sidebar.
 * New minaxis optional parameter can be used to set to the minimum axis value you wish the plot
   to start at.  This clips away uninteresting areas of a graph.
 * Upped number of groups from 8 to 12.

= 0.6.1 =
 * Oops! Small watermark parameter parsing glitch.  ALL that testing this slips through.. *sigh*

= 0.6 =
 * New chart type, "line"
 * New currency and precision options
 * Some minor fixes

= 0.5.1 =
 * Small nit with watermarkcolor

= 0.5 =
 * Added new chart type "line".  It is not very effective, IMHO.  If you use it let me know
 * Added new watermark feature whereby you can color a background region of the graph.

= 0.4 =
 * Fixed bug involving large values
 * Default colors are now provided if none specified
 * Added CSS imgstyle parameter for any special style magic needed.  The default image style now has float:center added.
 * tutorial at www.dyerware.com now has pie chart example. 

= 0.3 =

 * Removed dependency on prototype js library which was causing a conflict with plugins
 * Fixed data table coloring issues where the color key was sometimes missing.
 * Rounded corners for table (except for internet explorer)
 * CSS configuration parameter for the data table
 * Now aborts and reports an error if there is a malformed shortcode.

= 0.2 =

 * Fixed valuenames bug with horizbar charts.
 * You can now specify the ALT and TITLE attributes of the chart image
 * You can now optionally include a chart data table 
 * You can now specify the background color and background fade-out color of the chart.
 * Fixed bug with floating point group values.

= 0.1 =

 * First public release
