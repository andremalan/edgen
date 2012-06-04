<?php






// due to changes in the WP interface with WP version 2.7, I had to fix a few weird problems (and put in a bug alert). Grr.







// SETTINGS
define('KBRSS_HOWMANY', 20);	// max number of KB RSS widgets that you can have. Set to whatever you want. But don't put it higher than you need, or you may gum up your server.
define('KBRSS_MAXITEMS', 20);	// max number of items you can display from a feed. Obviously, you can't get more than are in the actual feed.
define('KBRSS_FORCECACHE', false); // if your widgets don't update after more than 1 hour, set this to true.

define('KBRSS_WPMU', false); // set to TRUE if you're on WP-MU to add a few extra filters to what folks can put into their widgets. Note that it's up to
				// you to determine whether this plugin is really secure enough for WPMU, though.
				// Setting true also disables the [opts:bypasssecurity] option.








// okay, settings are done. Stop editing unless you know what you're doing.







function widget_kbrss_init() {

	// prevent fatals
	if ( !function_exists('register_sidebar_widget') )
		return;
	if ( class_exists('kb_advRss') )
		return;

	// replicate a PHP 5 function for our PHP 4 friends
	if ( !function_exists('htmlspecialchars_decode') ){
	    function htmlspecialchars_decode($text){
	        return strtr($text, array_flip(get_html_translation_table(HTML_SPECIALCHARS)));
	    }
	}
	
	// make it work all the way back to WP 2.0:
	if (!function_exists('attribute_escape')){
		function attribute_escape($text) {
			$safe_text = wp_specialchars($text, true);
			return apply_filters('attribute_escape', $safe_text, $text);
		}
	}

	// class for the front end: displaying the widget on your site.
	class kb_advRss{
	
		// options
		var $title; // displayed above widget
		var $linktitle; // link title to source URL?
		var $num_items; // num of items from RSS feed to display
		var $url; // url of RSS feed
		var $output_begin; // what HTML will we precede the feed items with? (e.g. <ul>)
		var $output_format; // how will we display each feed item? (e.g. <li><a class="kbrsswidget" href="^link$" title="^description$">^title$</a></li>)
		var $output_end; // what HTML will we follow the feed items with? (e.g. </ul>)
		var $utf; // convert the feed to UTF?
		var $icon; // URL to an RSS icon to display
		var $display_empty; // Display an error when feed is down/empty? (false will hide the widget when feed is down)
		var $reverse_order; // reverse order of feed items?
		
		// data used internally
		var $number; // which widget is this? You can use multiple widgets, so we need to know which we're working with here.
		var $md5; // md5 hash of url
		var $md5_option; // name of option where we cache the feed
		var $md5_option_ts; // name of option where we save the cache's timestamp
		
		// rss channel info
		var $link; // link to origin website
		var $desc; // feed's description
		
		// rss item info
		var $tokens; // holds tokens like ^link$, ^title$, etc.
		var $items; // holds the items output as a string

		// make sure magpie is loaded
		function load_magpie(){
			if (function_exists('fetch_rss'))
				return true;

			if (file_exists(ABSPATH . WPINC . '/rss.php') )
				require_once(ABSPATH . WPINC . '/rss.php');
			elseif (file_exists(ABSPATH . WPINC . '/rss-functions.php') )
				require_once(ABSPATH . WPINC . '/rss-functions.php');

			if (function_exists('fetch_rss'))
				return true;
			return false;
		}
		
		// load up our options (or defaults)
		// can also set options manually via an array of args
		function load_options( $args = false ){
			if (is_array($args)){
				$this->number = 0;
				$options[0]['url'] = $args[0];
				$options[0]['items'] = $args[1];
				$options[0]['output_format'] = $args[2];
				$options[0]['utf'] = $args[3];
			}else{
				$options = get_option('widget_kbrss');
			}
			
			// title to display above widget
			$this->title = $options[$this->number]['title'];
			$this->linktitle = $options[$this->number]['linktitle'];

			// number of feed items to display
			$this->num_items = (int) $options[$this->number]['items'];
			if ( empty($this->num_items) || ($this->num_items < 1) || ($this->num_items > KBRSS_MAXITEMS) )
				$this->num_items = KBRSS_MAXITEMS;
			
			// feed URL
			$this->url = $options[$this->number]['url'];
			if ( empty($this->url) || false===strpos($this->url,'http') )
				return false;

			// If the feed URL is given as "feed:http://example.com/rss", lop off the "feed:' part:
			while ( strstr($this->url, 'http') != $this->url )
				$this->url = substr($this->url, 1);

			// for caching
			$this->md5 = md5($this->url);
			$this->md5_option = 'rss_' . $this->md5;
			$this->md5_option_ts = $this->md5_option . '_ts';
			
			// formatting options
			$this->output_begin = $options[$this->number]['output_begin'];
			$this->output_format = $options[$this->number]['output_format'];
			$this->output_end = $options[$this->number]['output_end'];
			$this->utf = $options[$this->number]['utf'];
			$this->display_empty = (1==$options[$this->number]['display_empty']) ? true : false;
			$this->reverse_order = (1==$options[$this->number]['reverse_order']) ? true : false;
			
			// icon?
			$this->icon = $options[$this->number]['icon'];
			
			// default format:
			if ( empty($this->output_format) )
				$this->output_format = '<li><a class="kbrsswidget" href="^link$" title="^description$">^title$</a></li>';

			return true;
		}
		
		// manually flush the cache (two ways to do it)
		function force_cache(){
			// METHOD 1: if logged in as admin, you can force a cache flush by adding ?kbrss_cache=flush to your blog URL
			global $userdata;
			if ( ('flush' == $_GET['kbrss_cache']) && ($userdata->user_level >= 7) ){
				delete_option( $this->md5_option );
				return;
			}
			// METHOD 2: If KBRSS_FORCECACHE is true, we'll flush the cache every hour. (WP should flush hourly on its own, though.)
			if ( ! KBRSS_FORCECACHE )
				return;
			$cachetime = get_option( $this->md5_option_ts );
			if ( $cachetime < ( time() - 3600 ) )
				delete_option( $this->md5_option );
		}
		
		// fetch the feed and format it for display (but don't call this function directly; use one of the wrappers)
		function get_feed(){
			$rss = @fetch_rss($this->url);
			
			// PART I: PREPARE CHANNEL INFORMATION:

			// link to RSS's origin site
			$this->link = clean_url(strip_tags($rss->channel['link']));
			while( strstr($this->link, 'http') != $this->link )
				$this->link = substr( $this->link, 1 );

			// feed description
			$this->desc = attribute_escape(strip_tags(html_entity_decode($rss->channel['description'], ENT_QUOTES)));

			// clean up url before displaying to screen
			$this->url = clean_url(strip_tags($this->url));
			
			// link title to source URL?
			if ( ('link'==$this->linktitle) && $this->title )
				$this->title = '<a href="'. $this->link .'">'. $this->title .'</a>';
			
			// add icon to title, if necessary
			if ('' != $this->icon)
				$this->title = '<a class="kbrsswidget" href="'.$this->url.'" title="Syndicate this content"><img width="14" height="14" src="'.$this->icon.'" alt="RSS" style="background:orange;color:white;" /></a> '.$this->title;
			
			// PART II: PREPARE ITEM INFORMATION
			if ( is_array($rss->items) && !empty( $rss->items ) ){
				if ($this->reverse_order)
					$rss->items = array_reverse( $rss->items );
				$rss->items = array_slice($rss->items, 0, $this->num_items);

				// initialize. Critical if there are multiple kb rss widgets.
				$this->items = '';

				// loop through each item in the feed
				foreach( $rss->items as $item ){
					// loop through each token that we need to find
					$find = array(); // initialize
					foreach( $this->tokens as $token ){
						$replace = ''; // initialize
						// how to display this field?
						if ( is_array($item[ $token['field'] ]) ){
							// display a subfield:
							if ( $token['opts']['subfield'] ){
								$replace = $item[ $token['field'] ][ $token['opts']['subfield'] ];
								$replace = $this->item_cleanup( $replace, $token['opts'] );
							// loop through items in this field:
							}elseif ( $token['opts']['loop'] ) {
								foreach( $item[ $token['field'] ] as $subfield ){
									$subfield = $this->item_cleanup( $subfield, $token['opts'] );
									$replace .= $token['opts']['beforeloop'] . $subfield . $token['opts']['afterloop'];
									}
							}
						}else{
							$replace = $item[ $token['field'] ];
							$is_url = ('link'==$token['slug']) ? true : false;
							$replace = $this->item_cleanup( $replace, $token['opts'], $is_url );
						}
						$find[ $token['slug'] ] = $replace;
					}
					$keys = array_keys( $find );
					$vals = array_values( $find );
					$this->items .= str_replace( $keys, $vals, $this->output_format );
				}
				
				if ($this->utf)
					$this->items = utf8_encode( $this->items );

			}else{ // no feed, display an error message
				if ($this->display_empty){
					if ( '<li' === substr( $this->output_format, 0, 3 ) )
						$this->items = '<li>' . __( 'An error has occurred; the feed is probably down. Try again later.' ) . '</li>';
					else
						$this->items = __( 'An error has occurred; the feed is probably down. Try again later.' );
				}else{	// display nothing when feed is down/empty
					$this->items = '';
					return false;
				}
			}
			return true; // always return true, except for one case above
		}
		// helper for the items loop in previous function. This is where we implement most of the options. This is the part of the plugin to edit if you
		// want to add a new functionality. See the FAQ for details.
		function item_cleanup($text,$opts=false,$url=false){
			// some cleanup for security. To bypass, set KBRSS_ALLOW_NOSECURITY true (top of this file) and use [opts:bypasssecurity] in field options.
			if (KBRSS_WPMU || !is_array($opts) || !array_key_exists('bypasssecurity',$opts)){
				if ($url)
					$text = clean_url(strip_tags($text));
				else
					$text = str_replace(array("\n", "\r"), ' ', attribute_escape(strip_tags(html_entity_decode($text, ENT_QUOTES))));
			}

			// apply opts, if given:
			if (!is_array($opts))
				return $text;
			extract($opts, EXTR_SKIP);
			
			// date formatting on pubdate
			if ($date){
				$text = $this->make_date($text,$date);
			}
			
			/////////////////////////////////////////////////////////////////////////////////////////////
			///////////////////////////////// CUSTOMIZATIONS /////////////////////////////////////////
			/////////////////////////////////////////////////////////////////////////////////////////////
			// If you want to write customizations, put them here. The variable to modify is $text. See the FAQ.
			/////////////////////////////////////////////////////////////////////////////////////////////
			/////////////////////////////////////////////////////////////////////////////////////////////

			// left trimming:
			$ltrim = (is_numeric($ltrim) && 0<$ltrim) ? (int) $ltrim : null;
			if (is_int($ltrim))
				$text = substr( $text, $ltrim );

			// length trimming (do after left trimming)
			$trim = (is_numeric($trim) && 0<$trim) ? (int) $trim : null;
			if (is_int($trim))
				$text = substr( $text, 0, $trim );

			return $text;
		}
		// another helper
		function make_date($string, $format){
			$time = strtotime( $string );
			if (false===$time || -1===$time)
				return $string;
			return date( $format, $time );
		}
		
		/* 	scans widget's "output format" option to figure out which "tokens" (item fields) to display, and how.
			returns an array of tokens, each of which is stored as an array, like this:
				array(
					array(
						'slug'=> token, exactly as written in widget's options , 
						'field'=> name of field in rss--might be the same as 'slug', 
						'opts'=>array of options, or NULL if no options.
					),
					// a basic example:
					array(
						'slug'=>'^title$', 	// this is what you write in widget's options to display title
						'field'=>'title', 	// this is the name of the field: "title"
						'opts'=>null		// no options (e.g. trimming) specified
					),
					// (the following examples omit the keys to keep it brief; pretend the keys are still there)
					array('^description$', 'description', null),	// display the item's description
					array('^description%%75$', 'description', array('trim'=>75)),	// display the item's description, trimmed to 75 chars max
					array('^dc=>creator$', 'dc', array('subfield'=>'creator')),		// displays the item's dc:creator field
					array('^dc=>creator&&5$', 'dc', array('subfield'=>'creator', 'trim'=>5)),		// displays the item's dc:creator field, trimmed to 5 chars max
					// a complicated example: looping fields in an array. this will loop through all fields in "categories" array (this was useful on old versions of WP)
					array(
						'slug'=>'^categories||<li>||</li>$', 
						'field'=>'categories', 
						'opts'=>array(
							'loop'=>array(
								'before'=>'<li>', 
								'after'=>'</li>'
							)
						) 
					),
				); 	
				// done with examples. 	*/		
		function detect_tokens(){
			if (''==$this->output_format)
				return false;
			preg_match_all( '~\^([^$]+)\$~', $this->output_format, $matches, PREG_SET_ORDER);
			/* $matches will look something like this
				[0] => Array        (		[0] => ^title$,	[1] => title		)
				[1] => Array        (		[0] => ^description%%75$,	[1] => description%%75		)	*/
			if (!is_array($matches) || empty($matches))
				return false;
			$tokens = array();
			$used = array();
			foreach( $matches as $match ){
				// if they use the same token twice, let's not insert it into the tokens array twice:
				if ( in_array($match[0], $used) )
					continue;
				$used[] = $match[0];

				// initialize (critical)
				$token = array();
				$token['slug'] = $match[0];
				$token['opts'] = array(); // important

				// THE NEW SYNTAX: ^fieldname[opts:trim=50&ltrim=30&date=]$
				if ( strpos($match[1], '[opts:') ){
					$explode = explode( '[opts:', $match[1], 2 );
					$match[1] = $explode[0];
					$opts = substr( $explode[1], 0, -1 ); // cut off ] at the end
					parse_str( $opts, $options );
					$token['opts'] = array_merge( $token['opts'], $options );
				}

				// BACKWARDS COMPATIBILITY: LOOK FOR %%, =>, ||

				// detect options: Trim? %%
				if ( strpos($match[1], '%%') ){
					$explode = explode( '%%', $match[1] );
					$match[1] = $explode[0];
					$token['opts']['trim'] = $explode[1];
				}

				// detect options: displaying arrays
				if ( strpos($match[1], '=>') ){
					$explode = explode( '=>', $match[1], 2);
					$match[1] = $explode[0];
					$token['opts']['subfield'] = $explode[1];
				}elseif( strpos($match[1], '||') ){
					$explode = explode( '||', $match[1], 3);
					$match[1] = $explode[0];
					$token['opts']['loop'] = true;
					$token['opts']['beforeloop'] = $explode[1];
					$token['opts']['afterloop'] = $explode[2];
				}

				$token['field'] = $match[1];

				// all done. add to master array
				$tokens[] = $token;
			}

			if (empty($tokens))
				return false;

			$this->tokens = $tokens;
			return true;
		}

		// workhorse function. Grabs the feed, prepares it, etc. Does everything but print to screen.
		function prepare_widget(){
			if (!$this->load_magpie())
				return false;
			if (!$this->load_options())
				return false;
			if (!$this->detect_tokens())
				return false;
			$this->force_cache();
			if (!$this->get_feed()) // fails if feed is down or empty
				return false;
			return true;
		}
		
		// called to display a widget
		function display_widget( $args, $num = 1 ){
			$this->number = $num;
			if (!$this->prepare_widget()){
				echo '<!-- kb advanced rss had an error. sorry. -->';
				return;
			}
			extract( $args );
			echo $before_widget;
			if ( $this->title )
				echo $before_title . $this->title . $after_title;
			echo $this->output_begin;
			echo $this->items;
			echo $this->output_end;
			echo $after_widget;
		}
		
		/* to use this plugin in your template, call this method, like so (actually, just use the kb_rss_template() wrapper, it's easier)
			global $kb_advRss; // if necessary
			$kb_advRss->display_template( $url, $numItems, $format, $utf );			*/
		function display_template( $url, $format, $numItems=10, $utf=false, $echo=true ){
			if (!$this->load_magpie())
				return false;
			if (!$this->load_options( array($url, $numItems, $format, $utf) ))
				return false;
			if (!$this->detect_tokens())
				return false;
			$this->force_cache();
			if (!$this->get_feed())	// fails if feed is down or empty
				return false;
			if ($echo)
				echo $this->items;
			else
				return $this->items;
		}
	}	// END OF CLASS

	// for backwards compatibility, we still use the old function name, but it's just a wrapper for the new class
	function widget_kbrss( $args, $number = 1 ){
		global $kb_advRss;
		$kb_advRss->display_widget( $args, $number );
	}

	/* use this function to display an RSS feed in your template (i.e. without using the widgets interface). Args:
		$url:		The feed's URL
		$format:	A string formatted following the plugin's instructions. Something like '<li><a href="^link$">^title$</a></li>'
		$numItems:	An integer--number of items from the feed to show
		$utf:		boolean; Should we convert the feed to UTF?
		$echo:	boolean: echo or return the result?
	*/
	function kb_rss_template($url, $format, $numItems=10, $utf=false, $echo=true){
		global $kb_advRss;
		return $kb_advRss->display_template( $url, $format, $numItems, $utf, $echo );
	}

	function widget_kbrss_control($number) {
		$options = get_option('widget_kbrss');
		$newoptions = $options;

		if ( $_POST["kbrss-submit-$number"] ) {
			$newoptions[$number]['items'] = (int) $_POST["kbrss-items-$number"];
			$newoptions[$number]['url'] = strip_tags(stripslashes($_POST["kbrss-url-$number"]));
			$newoptions[$number]['icon'] = strip_tags(stripslashes($_POST["kbrss-icon-$number"]));
			if (KBRSS_WPMU){
				$newoptions[$number]['title'] = trim(strip_tags(stripslashes($_POST["kbrss-title-$number"])));
			}else{
				$newoptions[$number]['title'] = trim( stripslashes($_POST["kbrss-title-$number"]) );
			}
			$newoptions[$number]['linktitle'] = ( "link" == $_POST["kbrss-linktitle-$number"] ) ? "link" : null;
			$newoptions[$number]['display_empty'] = (1==$_POST["kbrss-hideempty-$number"]) ? 0 : 1;
			$newoptions[$number]['reverse_order'] = (1==$_POST["kbrss-reverseorder-$number"]) ? 1 : 0;
			$newoptions[$number]['output_format'] = htmlspecialchars_decode( stripslashes($_POST["kbrss-output_format-$number"]) );
			$newoptions[$number]['output_begin'] = htmlspecialchars_decode( stripslashes($_POST["kbrss-output_begin-$number"]) );
			$newoptions[$number]['output_end'] = htmlspecialchars_decode( stripslashes($_POST["kbrss-output_end-$number"]) );
			$newoptions[$number]['utf'] = ( "utf" == $_POST["kbrss-utf-$number"] ) ? "utf" : null;


		}
		if ( $options != $newoptions ) {
			$options = $newoptions;
			update_option('widget_kbrss', $options);
		}
		$url = htmlspecialchars($options[$number]['url'], ENT_QUOTES);
		$icon = htmlspecialchars($options[$number]['icon'], ENT_QUOTES);
		$items = (int) $options[$number]['items'];
		$title = htmlspecialchars($options[$number]['title'], ENT_QUOTES);
		$linktitle = $options[$number]['linktitle'];
		$display_empty = (int) $options[$number]['display_empty'];
		$reverse_order = (int) $options[$number]['reverse_order'];
		$output_format = htmlspecialchars($options[$number]['output_format'], ENT_QUOTES);
		$output_begin = htmlspecialchars($options[$number]['output_begin'], ENT_QUOTES);
		$output_end = htmlspecialchars($options[$number]['output_end'], ENT_QUOTES);
		$utf = $options[$number]['utf'];


		if ( empty($items) || $items < 1 ){
			$items = 10;
		}
		if ( '' == $output_format ){
			$output_format = "<li><a class='kbrsswidget' href='^link\$' title='^description\$'>^title\$</a></li>";
		}
		if ( '' == $url ){
			$output_begin = "<ul>";	// note that we're checking whether the url is empty. that way we don't re-populate these fields if somebody
			$output_end = "</ul>";	// intentionally cleared them. we only want to populate them when beginning a new widget.
			if ( file_exists(dirname(__FILE__) . '/rss.png') ){
				$icon = str_replace(ABSPATH, get_settings('siteurl').'/', dirname(__FILE__)) . '/rss.png';
			}else{
				$icon = get_settings('siteurl').'/wp-includes/images/rss.png';
			}
			$url = "http://";
		}
		
	?>
				<div id="kb_rss_settings_<?php echo $number; ?>">
					<p><strong>Bug alert!</strong> Hit "Done," not "Cancel," to close this box, or all your settings for this widget may be lost. (This is a weird WP 2.7 bug.)</p>
					<p><strong>For help:</strong> <a href="http://wordpress.org/extend/plugins/kb-advanced-rss-widget/other_notes/">Read the documentation</a>.

					<table>
					<tr>
						<td><?php _e('Title (optional):', 'kbwidgets'); ?> </td>
						<td colspan="3"><input style="width: 400px;" id="kbrss-title-<?php echo "$number"; ?>" name="kbrss-title-<?php echo "$number"; ?>" type="text" value="<?php echo $title; ?>" /></td>
					</tr>
					<tr>
						<td><label for="kbrss-url-<?php echo $number; ?>"><?php _e('RSS feed URL:', 'kbwidgets'); ?> </label></td>
						<td colspan="3"><input style="width: 400px;" id="kbrss-url-<?php echo $number; ?>" name="kbrss-url-<?php echo "$number"; ?>" type="text" value="<?php echo $url; ?>" /></td>
					</tr>
					<tr>
						<td><label for="kbrss-icon-<?php echo $number; ?>"><?php _e('RSS icon URL (optional):', 'kbwidgets'); ?> </label></td>
						<td colspan="3"><input style="width: 400px;" id="kbrss-icon-<?php echo $number; ?>" name="kbrss-icon-<?php echo $number; ?>" value="<?php echo $icon; ?>" /></td>
					</tr>
					<tr>
						<td><label for="kbrss-items-<?php echo $number; ?>"><?php _e('Number of items to display:', 'kbwidgets'); ?> </label></td>
						<td colspan="3"><select id="kbrss-items-<?php echo $number; ?>" name="kbrss-items-<?php echo $number; ?>"><?php for ( $i = 1; $i <= KBRSS_MAXITEMS; ++$i ) echo "<option value='$i' ".($items==$i ? "selected='selected'" : '').">$i</option>"; ?></select></td>
					</tr>
					<tr>
						<td style="text-align:right;"><input type="checkbox" name="kbrss-linktitle-<?php echo $number; ?>" id="kbrss-linktitle-<?php echo $number; ?>" value="link" <?php if ( "link" == $linktitle ) { echo 'checked="checked"'; } ?> /> </td>
						<td><label for="kbrss-linktitle-<?php echo $number; ?>">Link title to feed URL? </label></td>


						<td><input type="checkbox" name="kbrss-hideempty-<?php echo $number; ?>" id="kbrss-hideempty-<?php echo $number; ?>" value="1" <?php if ( 1!=$display_empty ){ echo 'checked="checked"'; } ?> /> </td>
						<td><label for="kbrss-hideempty-<?php echo $number; ?>">Hide widget when feed is down? </label></td>
					</tr>
					<tr>
						<td style="text-align:right;"><input type="checkbox" name="kbrss-utf-<?php echo $number; ?>" id="kbrss-utf-<?php echo $number; ?>" value="utf" <?php if ( "utf" == $utf ) { echo 'checked="checked"'; } ?> /> </td>
						<td><label for="kbrss-utf-<?php echo $number; ?>">Convert feed to UTF-8? </label></td>


						<td><input type="checkbox" name="kbrss-reverseorder-<?php echo $number; ?>" id="kbrss-reverseorder-<?php echo $number; ?>" value="1" <?php if ( 1===$reverse_order ){ echo 'checked="checked"'; } ?> /> </td>
						<td><label for="kbrss-reverseorder-<?php echo $number; ?>">Reverse order of items in feed? </label></td>
					</tr>
					</table>
					
					<p> &nbsp; </p>
					
					<p><strong>Formatting Options</strong><br /><small>Use the default settings to make your feed look like it would using the built-in RSS widget. To customize, use the advanced fields below.</small></p>
					<p style="text-align:center;"><?php _e('What HTML should precede the feed? (Default: &lt;ul&gt;)', 'kbwidgets'); ?></p>
					<input style="width: 680px;" id="kbrss-output_begin-<?php echo "$number"; ?>" name="kbrss-output_begin-<?php echo "$number"; ?>" type="text" value="<?php echo $output_begin; ?>" />
					<p style="text-align:center;"><?php _e('What HTML should follow the feed? (Default: &lt;/ul&gt;)', 'kbwidgets'); ?></p>
					<input style="width: 680px;" id="kbrss-output_end-<?php echo "$number"; ?>" name="kbrss-output_end-<?php echo "$number"; ?>" type="text" value="<?php echo $output_end; ?>" />
					<p style="text-align:center;"><?php _e("How would you like to format the feed's items? Use <code>^element$</code>. Default:", 'kbwidgets'); ?><br /><small><code>&lt;li&gt;&lt;a href='^link$' title='^description$'&gt;^title$&lt;/a&gt;&lt;/li&gt;</code></small></p>
					<textarea style="width:680px;height:50px;" id="kbrss-output_format-<?php echo "$number"; ?>" name="kbrss-output_format-<?php echo "$number"; ?>" rows="3" cols="40"><?php echo $output_format; ?></textarea>
					<input type="hidden" id="kbrss-submit-<?php echo "$number"; ?>" name="kbrss-submit-<?php echo "$number"; ?>" value="1" />
				</div>
	<?php
	}
	
	function widget_kbrss_control_scripts(){
		echo '
		<style type="text/css"><!--
		.kb_rss_nav{border-bottom:solid 1px #555;}
		.kb_rss_nav a{border:solid 1px #555;padding:0 1em;margin-left:0.5em;background:#ffa;color:#000;font-weight:bold;}
		.kb_rss_nav a:hover,.kb_rss_nav a:active{background:#faa;color:#000;}
		// -->
		</style>
		';
	}
	add_action('admin_head','widget_kbrss_control_scripts');

	function widget_kbrss_setup() {
		$options = $newoptions = get_option('widget_kbrss');
		if ( isset($_POST['kbrss-number-submit']) ) {
			$number = (int) $_POST['kbrss-number'];
			if ( $number > KBRSS_HOWMANY ) $number = KBRSS_HOWMANY;
			if ( $number < 1 ) $number = 1;
			$newoptions['number'] = $number;
		}
		if ( $options != $newoptions ) {
			$options = $newoptions;
			update_option('widget_kbrss', $options);
			widget_kbrss_register($options['number']);
		}
	}

	function widget_kbrss_page() {
		$options = $newoptions = get_option('widget_kbrss');
	?>
		<div class="wrap">
			<form method="post" action="">
				<h2>KB Advanced RSS Feed Widgets</h2>
				<p style="line-height: 30px;"><?php _e('How many KB Advanced RSS widgets would you like?', 'kbwidgets'); ?>
				<select id="kbrss-number" name="kbrss-number" value="<?php echo $options['number']; ?>">
	<?php for ( $i = 1; $i <= KBRSS_HOWMANY; ++$i ) echo "<option value='$i' ".($options['number']==$i ? "selected='selected'" : '').">$i</option>"; ?>
				</select>
				<span class="submit"><input type="submit" name="kbrss-number-submit" id="kbrss-number-submit" value="<?php _e('Save'); ?>" /></span></p>
			</form>
		</div>
	<?php
	}

	function widget_kbrss_register() {
		$options = get_option('widget_kbrss');
		$number = $options['number'];
		if ( $number < 1 ) 
			$number = 1;
		if ( $number > KBRSS_HOWMANY ) 
			$number = KBRSS_HOWMANY;
		for ($i = 1; $i <= KBRSS_HOWMANY; $i++) {
			$name = array('KB Advanced RSS %s', null, $i);
			$id = "kb-advanced-rss-$i"; // Never never never translate an id
			$dims = array('width' => 700, 'height' => 580);
			$class = array( 'classname' => 'widget_kbrss' ); // css classname
			$name = sprintf(__('KB Advanced RSS %d'), $i);
			wp_register_sidebar_widget($id, $name, $i <= $number ? 'widget_kbrss' : /* unregister */ '', $class, $i);
			wp_register_widget_control($id, $name, $i <= $number ? 'widget_kbrss_control' : /* unregister */ '', $dims, $i);
		}
	
		add_action('sidebar_admin_setup', 'widget_kbrss_setup');
		add_action('sidebar_admin_page', 'widget_kbrss_page');
	}
	
	$GLOBALS['kb_advRss'] = new kb_advRss();
	widget_kbrss_register();

}

// add a filter for troubleshooting feeds
function widget_kbrss_troubleshooter(){
	if ( !($_GET['kbrss']) )
		return;

	global $userdata;
	if ( $userdata->user_level >= 7 ){	// that ought to do it
		if ( file_exists(ABSPATH . WPINC . '/rss.php') )
			require_once(ABSPATH . WPINC . '/rss.php');
		else
			require_once(ABSPATH . WPINC . '/rss-functions.php');
		$rss = @fetch_rss($_GET['kbrss']);
		$out = "<html><head><title>KB RSS Troubleshooter</title></head><body><div style='background:#cc0;padding:1em;'><h2>KB Advanced RSS Troubleshooter</h2><p>Below, you should see the feed as Wordpress passes it to the KB Advanced RSS widget.</p></div><pre>";
		$out .= htmlspecialchars( print_r($rss->items, true) );
		$out .= "</pre></body></html>";
		print $out;
		die;
	}else{
		print "<p>You must be logged in as an administrator to troubleshoot feeds.</p>";
		die;
	}
	return;
}

add_action('widgets_init', 'widget_kbrss_init');
add_action('template_redirect', 'widget_kbrss_troubleshooter');

?>