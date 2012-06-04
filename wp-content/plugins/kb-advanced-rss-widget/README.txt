=== KB Advanced RSS Widget ===
Contributors: adamrbrown
Donate link: http://adambrown.info/b/widgets/donate/
Tags: widget, rss, feed, feeds, sidebar, widgets, atom, xml, rss2
Requires at least: 2.0
Tested up to: 2.8.3
Stable tag: trunk

Similar to the default RSS widget, but gives you complete control over how RSS feeds are parsed for your sidebar.

== Description ==

WordPress comes with a default RSS widget, but you get no control over how the feed shows up in your sidebar. Want more control? The KB Advanced RSS widget gives it to you. With it, you can

* Decide which RSS fields to display (as opposed to the default RSS widget, which limits you to link, title, and description);
* Decide how to format the fields (it doesn't have to be a list if you don't want it to be);

Be aware that it's called "advanced" for a reason. You need to know some HTML to use this fully. Also, please note that this is a widget, so you need to be using a widgets-enabled theme.

**Before upgrading to  v2.8 of the plugin, read this:** Because the WordPress developers dramatically re-wrote the widgets API in WP 2.8, I had to rewrite this plugin completely. Updating to v2.8 of the plugin will correct many annoying bugs. But, alas, it will also (most likely) cause you to lose all the options you may have set in a previous version of this plugin. Sorry. I don't foresee this happening more than once.

= Support =

If you post your support questions as comments below, I probably won't see them. If the FAQs don't answer your question, you can post support questions at the [KB Advanced RSS plugin page](http://adambrown.info/b/widgets/category/kb-advanced-rss/) on my site.

== Installation ==

You MUST be using a widgets-enabled theme. If you are using pre-2.2 WordPress, you'll also need the [sidebar widgets plugin](http://wordpress.org/extend/plugins/widgets/).

1. WP 2.7+: Use the plugin installer. Older versions of WP: Upload `kb_advanced_rss.php` to either `/wp-content/plugins/widgets/` or `/wp-content/plugins/`, then activate the plugin.
1. Add the new KB Advanced RSS widget to your sidebar through the 'Presentation => Sidebar Widgets' menu in WordPress. You'll find that the widget has several options, but only the first couple are required.

**For detailed instructions,** check out the "Other Notes" tab.

= Support =

Be advised: **If you post your support questions as comments below, I probably won't see them.** Post your support questions at the [KB Advanced RSS plugin page](http://adambrown.info/b/widgets/category/kb-advanced-rss/) on my site if you want an answer.

== Screenshots ==

You can see examples at the [KB Advanced RSS plugin page](http://adambrown.info/b/widgets/kb-advanced-rss/).

== Other Notes ==

**Instructions**

Background before we continue: Every RSS feed contains a number of items (e.g. headlines). Each item contains a variety of elements; at a minimum, each item usually has a title, a link, and a description. This plugin uses [MagpieRSS](http://magpierss.sourceforge.net/) (included) to grab and parse RSS feeds.

**Example 1: Basic usage**

To show how this widget works, let's use it to parse an RSS feed in exactly the way that the default RSS widget does. 
1. The default widget begins with `<ul>` (to make the feed items a list). 
1. Then, it prints out the following line for each item in the feed: `<li><a href="LINK" title="DESCRIPTION">TITLE</a></li>`. 
1. Finally, it closes the feed with `</ul>` (to end the list).

By default, the KB Advanced RSS widget will do exactly the same thing. But you can change any or all of these three things using the widgets interface. First, you enter the HTML that you want to have precede the widget. In this case, that's `<ul>`. Then, you enter the HTML that should follow the widget. That's `</ul>` here. Then, you tell it how to parse each item in the feed, using `^ELEMENT$` to specify elements in the feed. To replicate the basic, built-in RSS widget, you would write this: `<li><a href="^link$" title="^description$">^title$</a></li>`. Easy, isn't it?

**Example 2: Adding another element**

How do you know which elements are available? Looking at the RSS feed is a good starting point, but you should be aware that the MagpieRSS parser modifies feeds when it parses them. To see exactly which elements are available, go to any page on your blog, then add `?kbrss=RSS_URL` to your blog's URL (replacing RSS_URL with the complete URL for the feed you are interested in).

For example, if your blog were at example.com, and you were interested in the Yahoo! News Most Emailed Stories feed, you would type this into your browser: `http://example.com/?kbrss=http://rss.news.yahoo.com/rss/mostemailed`. (That only works if you're logged in as an admin, so you'll have to install the plugin and then try it on your own site to see what that does.)

If you do this, my plugin will spit out a copy of the PHP array that MagpieRSS produces when parsing your feed. Each item in the feed shows up as a numbered part of the array. Within each item, you'll see that you have fields like "title," "link," "description," and possibly others available. Pick any one of these and add it to your KB Advanced RSS widget. Done.

**Example 3: Trimming an element**

Suppose you want to display each item's description, but some of the descriptions are way too long. If you wanted to trim the description to 50 characters (or any other number), write `^description[opts:trim=50]$`. Note that the `[opts:...]` comes before the `$`. If you go the [plugin's page](http://adambrown.info/b/widgets/category/kb-advanced-rss/), you'll see that I used this technique on the example at the bottom of the sidebar.

You can also trim each item from the left. Suppose that each item's title in your feed starts with the word "News flash!" and you want to get rid of it. Trim the first 11 characters off the title like this: `^title[opts:ltrim=11]$`.

If, after removing the first 11 characters, you also want to trim the title to only 10 characters total, you would write this: `^title[opts:ltrim=11&trim=10]$`.

**Example 4: Displaying dates**

Most feeds will use a `pubdate` fields that produces something ugly like this:

`[pubdate] => Thu, 20 Dec 2007 19:32:38 +0000`

To modify that, use the `date` option, using [PHP date syntax](http://www.php.net/date), like so:

`^pubdate[opts:date=F jS Y]$`

The `F jS Y` is PHP date syntax for something like `December 20th, 2007`.

**Example 5: What if an RSS item contains an array of elements?**

Okay, now we've moved into the really advanced stuff. You probably won't follow this next part unless you use the ?kbrss= thing from example 2 first and see what I'm talking about. Note that some of the items in Yahoo's feed contain something that looks something like the following (it will look slightly different):

`[media] => Array
		(
			[text] => <p>a bunch of stuff</p>
			[credit] => (Reuters)
		)
`

There are two ways to display elements from this array.

Use `=>` to access one field from the array. So to access the text field from this array, you would need to type this: `^media=>text$` into the KB Advanced RSS widget options. (In versions 2.0+ of the widget, you can type `^media[opts:subfield=text]$` instead if you want.)

Here's another example of a feed containing an array, but with a twist. When MagpieRSS parse feeds from some versions of WordPress, for example, MagpieRSS turns the "categories" field in the feed into an array. For example, here's how MagpieRSS parsed part of my blog's feed (when it ran on an earlier version of WordPress):

`
   [title] => KB Countdown update
   [link] => http://adambrown.info/b/widgets/kb-countdown/
   [comments] => http://adambrown.info/b/widgets/kb-countdown/feed/
   [pubdate] => Sat, 31 Mar 2007 22:38:51 +0000
   [author] => Adam
   [categories] => Array
       (
           [0] => KB Countdown
           [1] => Widgets
       )
   [guid] => http://adambrown.info/b/widgets/kb-countdown/
`

Now, if you only wanted to list the first category, you would write `^categories=>0$`, as above. But what if you want to loop through all the categories and print all of them? Then write this: `^categories[opts:loop=true&beforeloop=BEFORE&afterloop=AFTER]$`, where BEFORE and AFTER are the html you want to appear before and after each element in the array. For example, you might write this: `^categores[opts:loop=true&beforeloop=<li>&afterloop=</li>]$`, or more properly, this: `<ul>^categores[opts:loop=true&beforeloop=<li>&afterloop=</li>]$</ul>`.

**Complete list of options**

Now that you've learned how to use `[opts:...]`, here's a complete list of the options available. Note that these options apply to an individual field, not to the feed as a whole.

* `[opts:date=...]` - For displaying the `[pubdate]` field nicely.
* `[opts:trim=40]` - For trimming a field to 40 characters in length.
* `[opts:ltrim=50]` - For trimming 50 characters off the left (beginning) of a field.
* `[opts:bypasssecurity=1]` - For allowing a field's javascript through, if necessary. Use carefully. Disabled in WPMU.
* `[opts:loop=true]` - For subarrays. See example above. You'll also use `beforeloop` and `afterloop`.
* `[opts:subfield=...]` - Alternative syntax for displaying a specific field from an array. See example above.

Okay, that's the basics. Check the FAQ for further details.


== Frequently Asked Questions ==

= How do I use this thing? =

Check out the "Other Notes" tab, above, for instructions.

= What code do I need to place in my sidebar? =

None. This is a widget. If you are using pre-WP v2.2, you need to have the [widgets plugin](http://wordpress.org/extend/plugins/widgets/) running. No matter what version of WP you're using, you need to be using a widgets-enabled theme. You control all options for KB Advanced RSS from the widgets administration menu.

= What can I do with this widget? =

Lots of things. The built-in RSS widget will handle traditional headline-style feeds well, but this widget allows you to handle untraditional feeds just as easily. For example:

* Weather. Weather.com provides RSS feeds, but you'll find more flexible feeds at [RSSweather.com](http://www.rssweather.com/).
* Upcoming events. If you have an RSS feed of calendar data, give it a go.

Note that finding a suitable feed is up to you. It needs to be RSS, not just XML. (RSS is a sub-type of XML.) If you're not sure whether the feed will work with MagpieRSS's feed parser, then use the widget's built in debugger (see below) to check out the feed in question.

= The feeds don't update =

They update only once per hour to avoid slowing down your site. After that, they only update if your server is actually able to communicate with the feed's site. Be patient.

= I add the widget to my sidebar, but it doesn't show up =

In the widget's options, make sure "Hide widget when feed is down" is **not** checked. Go back to your blog and reload. You'll probably see something like "An error has occured; the feed is probably down." Read on...

= "An error has occured; the feed is probably down." =

This widget relies on [MagpieRSS's](http://magpierss.sourceforge.net/) feed parsing abilities. MagpieRSS grabs the requested feed then passes it to this widget for formatting. If you are seeing this error, it means one of three things:

1. The feed really is down. Wait a while and try again.
1. Your host is blocking MagpieRSS from fetching the feed (very likely). [Read more here](http://wordpress.org/support/topic/120458?replies=24#post-602781).
1. Try updating to the most recent version of Wordpress and of this plugin.
1. Some users of this widget have suggested additional solutions to this problem. Check out the comments on [this blog post](http://adambrown.info/b/widgets/2007/08/20/an-error-has-occured-the-feed-is-probably-down/).

In any case, you may want to first try using Wordpress's built-in RSS widget. If neither it nor my widget can display the feed, then you know for certain that it's one of those three reasons--and not the widget itself--causing the failure.


= Which fields are available in the feed? Or: I need to debug the feed. =

Begin by looking at the source code for the feed. But note that Wordpress parses feeds in ways that you might not expect. After you've installed my widget, you can add `?kbrss=http://path.to.feed/` to your blog's URL to see exactly which fields are available. (You'll need to be logged in as an admin to do this).

If you see that there is a field called `title` (there probably is), you would include this in your widget's output by writing `^title$`. You would probably want to wrap this in some HTML, like this: `<li>^title$</li>`. Look under the "Other Notes" tab for more details about how to display RSS feeds the way you want them in your sidebar.

If all you see is `array()`--or worse, an error message--then there's a good chance that the feed in question is not an RSS feed, at least not one that the MagpieRSS parser knows how to handle.

= How do I trim the length of an RSS field? =

Check out the "Other Notes" tab.

= Some of the available fields are arrays! =

No problem. Check out the "Other Notes" tab.

= What options are available? =

Check out the "Other Notes" tab.

= The feed shows up as gobbledygook! =

Try checking the "convert to UTF-8" option. (Thanks to [Christoph Juergens](http://www.cjuergens.de/).)

= I'd like to modify the feed before displaying it =

For example, suppose your feed has a field called "image" that contains something like this:

`<img src="http://example.com/img.jpg" />`

and all you want is the URL. Obviously, you'll need to do customizations like this yourself. But here's a couple tips. Usually, the easiest route is to create your own option. Write something like this: `^image[opts:extractUrl=1]$`.

Next, open up the plugin file and search for `function item_cleanup`. Insert your code in there. Something like this:

`if (1==$extractURL){
...
}`

= How do I add additional options? =

Read the answer to the previous question. If you think other folks would like the same option, let me know so I can add it to the plugin.

= I have a question that isn't addressed here. =

Be advised: **If you post your support questions as comments below, I probably won't see them.** Post your support questions at the [KB Advanced RSS plugin page](http://adambrown.info/b/widgets/category/kb-advanced-rss/) on my site if you want an answer.

== Changelog ==

The complete changelog back to version 1.0 is in the plugin code. Here's the changelog starting with v2.8 of the plugin:

= 2.8 =
* Fixes the "multiple widgets" problem caused by the developers' changes to the API in WP 2.8
* Now includes its own copy of magpie, since WP is moving toward simplepie
* Relies entirely on independent rss caching, not magpie's caching
* REMOVED the ability to call this plugin manually from a template. MUST be used as a widget.

= 2.8.01 =
* Minor bugfix. Oops. Saves correctly now.

= 2.8.02 = 
* Repaired the utf8 option