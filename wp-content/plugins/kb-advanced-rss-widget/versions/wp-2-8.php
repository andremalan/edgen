<?php





// this code gets run if you are using WP 2.8 or higher.
// if you are using an older version of WP, then the widget
// uses one of the other files in /versions/, not this code.





// max number of items you can display from a single feed. Obviously, 
// you can't get more than are in the actual feed. Setting this to a
// reasonable number keeps the widget admin menu easier to use.
define('KBRSS_MAXITEMS', 25);

// let's autodetect WPMU and disable some sensitive functions. You can
// manually set this to TRUE if you really want to. Setting this TRUE
// will add a few extra filters to what folks can put into their widgets. 
// Note that it's up to you as a responsible WPMU host to determine
// whether this plugin is really secure enough for WPMU, though.
// Setting true also disables the [opts:bypasssecurity] option.
if ( $wpmu_version )
	define('KBRSS_WPMU', true);
else
	define('KBRSS_WPMU', false); // change here to override and make always true






// replicate a PHP 5 function for our PHP 4 friends
if ( !function_exists('htmlspecialchars_decode') ){
	function htmlspecialchars_decode($text){
		return strtr($text, array_flip(get_html_translation_table(HTML_SPECIALCHARS)));
	}
}



// for loading up magpie
function kbar_load_magpie(){
	if (function_exists('fetch_rss'))
		return true;
	
	// we don't really need magpie's cache, and since WP now uses
	// simplepie instead of magpie, I ought to be able to safely
	// turn off magpie's cache
	// besides, we won't even get to this line if WP already included
	// magpie on its own (see preceding line of code), so this definition
	// shouldn't even happen unless we're the only thing using magpie
    if ( !defined('MAGPIE_CACHE_ON') ) {
        define('MAGPIE_CACHE_ON', false);
    }

	if (file_exists(ABSPATH . WPINC . '/rss.php') ) // first try to grab WP's version of magpie
		require_once(ABSPATH . WPINC . '/rss.php');
	elseif (file_exists(ABSPATH . WPINC . '/rss-functions.php') )
		require_once(ABSPATH . WPINC . '/rss-functions.php');
	
	if (function_exists('fetch_rss'))
		return true;
	
	// we shouldn't get here unless WP cuts its version of magpie from the code.
	// just in case, though, here's a copy of magpie of our own:
	if (file_exists( dirname(__FILE__) . DIRECTORY_SEPARATOR . 'magpierss-0.72' . DIRECTORY_SEPARATOR . 'rss_fetch.inc' ) )
		require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'magpierss-0.72' . DIRECTORY_SEPARATOR . 'rss_fetch.inc');

	if (function_exists('fetch_rss'))
		return true;
		
	return false;
}



/**
 * KB Advanced RSS widget class
 *
 * @since 2.8.0
 */
class KB_Adv_RSS extends WP_Widget {

	// note that four of the functions in this class are required
	// overrides of functions in class WP_Widget
	// everything else in here is specific to the KB Adv RSS
	// widget

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
	var $cached; // cached feed contents
	var $md5_option_ts; // name of option where we save the cache's timestamp
	var $flushed=false; // indicates that feed's cache was purged
	
	// rss channel info
	var $link; // link to origin website
	var $desc; // feed's description
	
	// rss item info
	var $tokens; // holds tokens like ^link$, ^title$, etc.
	var $items; // holds the items output as a string




	///////////// REQUIRED METHODS


	// REQUIRED. Defines widget's name and such.
	function KB_Adv_RSS() {
		$widget_ops = array( 'description' => 'Entries from any RSS or Atom feed, displayed how you want them', 'classname'=>'widget_kbrss' );
		$control_ops = array( 'width' => 700, 'height' => 580 );
		$this->WP_Widget( 'kb-advanced-rss', 'KB Advanced RSS', $widget_ops, $control_ops );
	}

	// REQUIRED. Displays widget output. Relies heavily on helper options defined below.
	function widget($args, $instance) {
		$i=1;

		/* check for errors
		if ( isset($instance['error']) && $instance['error'] ){
			echo '<!-- kb advanced rss had an error - unable to fetch feed. sorry. -->';
			return false;
		} */
		
		// load up magpie
		if ( !kbar_load_magpie() ){
			echo '<!-- kb advanced rss had an error - cannot load magpie. sorry. -->';
			return false;
		}
		
		// load options, set defaults
		if ( !$this->load_options($instance)){
			echo '<!-- kb advanced rss had an error - unable to load options. sorry. -->';
			return false;
		}
		
		// detect output tokens, like ^title$ and such
		if ( !$this->detect_tokens() ){
			echo '<!-- kb advanced rss had an error - Error; check your widget settings. You MUST specify an output format that contains usable tags. -->';
			return false;
		}
		
		// check the rss cache
		$this->force_cache();
		
		// load up the feed and process it
		if ( !$this->get_feed() ){ // fails if feed is down or empty
			echo '<!-- kb advanced rss had an error - target feed is either empty or unavailable. -->';
			return false;
		}
		
		// display widget
		extract($args, EXTR_SKIP);
		
		echo $before_widget;
		if ( $this->title )
			echo $before_title . $this->title . $after_title;
		echo $this->output_begin;
		echo $this->items;
		echo $this->output_end;
		echo $after_widget;
		
	}


	// REQUIRED. Processes submitted admin form.
	function update($new_instance, $old_instance) {
		$testurl = ( $new_instance['url'] != $old_instance['url'] );
		return KB_Adv_RSS_process( $new_instance, $testurl );
	}

	// REQUIRED. Displays widget's admin form.
	function form($instance) { /// UPDATED

		if ( empty($instance) ){ // set defaults
			$output_format = "<li><a class='kbrsswidget' href='^link\$' title='^description\$'>^title\$</a></li>";
			$output_begin = "<ul>";
			$output_end = "</ul>";
			if ( file_exists(dirname(__FILE__) . '/rss.png') ){
				$icon = str_replace(ABSPATH, get_settings('siteurl').'/', dirname(__FILE__)) . '/rss.png';
			}else{
				$icon = includes_url('images/rss.png');
			}
			$instance = array( 'title' => '', 'url' => '', 'items' => 10, 'error' => false, 'icon'=>$icon, 'linktitle'=>0, 'display_empty'=>0, 'reverse_order'=>0, 'utf'=>0, 'output_format'=>$output_format, 'output_begin'=>$output_begin, 'output_end'=>$output_end );
		}

		$instance['number'] = $this->number;

		$this->build_form( $instance );
	}
	
	
	
	
	//////////////////////////////////
	//////////////////////////////////
	//////////////////////////////////
	// Helper methods for OUTPUT ($this->widget())
	//////////////////////////////////
	//////////////////////////////////
	//////////////////////////////////
	
	// defines all vars used in producing feed output
	function load_options( $instance ){
	
		// title to display above widget
		$this->title = $instance['title'];
		$this->linktitle = $instance['linktitle'];
		
		// number of feed items to display
		$this->num_items = (int) $instance['items'];
		if ( empty($this->num_items) || ($this->num_items < 1) || ($this->num_items > KBRSS_MAXITEMS) )
			$this->num_items = KBRSS_MAXITEMS;
		
		// feed url
		$url = $instance['url'];
		// If the feed URL is given as "feed:http://example.com/rss", lop off the "feed:' part:
		while ( stristr($url, 'http') != $url )
			$url = substr($url, 1);
		if ( empty($url) )
			return false;
		$this->url = $url;

		// for caching
		$this->md5 = md5($this->url);
		$this->md5_option = 'rss_' . $this->md5;
		$this->md5_option_ts = $this->md5_option . '_ts';

		// formatting options
		$this->output_begin = $instance['output_begin'];
		$this->output_format = $instance['output_format'];
		$this->output_end = $instance['output_end'];
		$this->utf = $instance['utf'];
		$this->display_empty = $instance['display_empty'];
		$this->reverse_order = $instance['reverse_order'];
		
		// icon?
		$this->icon = $instance['icon'];
		
		// default format:
		if ( empty($this->output_format) )
			$this->output_format = '<li><a class="kbrsswidget" href="^link$" title="^description$">^title$</a></li>';
		
		return true;	
	}
	
	
	/* 	$this->detect_tokens() scans widget's "output format" option to figure out which 
		"tokens" (item fields) to display, and how.
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
	done with examples. 	*/		
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
	
	// check whether to clear the rss cache
	function force_cache(){
		// before purging cache, let's make sure we've got a
		// copy of it handy:
		$rss = get_option( $this->md5_option );
		if (is_object( $rss ))
			$this->cached = $rss;
		
		// if logged in as admin, you can force a cache flush by adding ?kbrss_cache=flush to your blog URL
		global $userdata;
		if ( ('flush' == $_GET['kbrss_cache']) && ($userdata->user_level >= 7) ){
			delete_option( $this->md5_option );
			$this->flushed = true;
			return;
		}
		
		// Regardless, we'll flush the cache every hour. (WP should flush hourly on its own, though.)
		$cachetime = get_option( $this->md5_option_ts );
		if ( $cachetime < ( time() - 3600 ) ){
			delete_option( $this->md5_option );
			$this->flushed = true;
		}
	}
	
	// fetch the feed and format it for display (requires that other methods be called first to
	// prepare the data; see $this->widget().
	function get_feed(){

		if ( !$this->flushed && $this->cached ){
			$rss = $this->cached; // use our cached copy
		
		}else{
			$rss = @fetch_rss($this->url); // get fresh copy
			
			if ( !is_array($rss->items) || empty( $rss->items ) ){
				// if fetch failed, then use our cached copy
				$rss = $this->cached; // use cached copy
				
				// if we flushed the cache, then restore it since
				// we weren't able to update it
				if ( $this->flushed )
					update_option( $this->md5_option, $rss );

			}elseif ( !$rss->from_cache ){
				// fetch didn't fail, apparently; we check
				// $rss->from_cache to verify that fetch wasn't from
				// magpie's cache. It wasn't, so we should update
				// our cache:
				update_option( $this->md5_option, $rss );
				update_option( $this->md5_option_ts, time() );
			}
		}
		
		// PART I: PREPARE CHANNEL INFORMATION:

		// link to RSS's origin site
		$this->link = esc_url(strip_tags($rss->channel['link']));
		while( strstr($this->link, 'http') != $this->link )
			$this->link = substr( $this->link, 1 );

		// feed description
		$this->desc = esc_attr(strip_tags(@html_entity_decode($rss->channel['description'], ENT_QUOTES, get_option('blog_charset'))));

		// clean up url before displaying to screen
		$this->url = esc_url(strip_tags($this->url));
	
		// link title to source URL?
		if ( ($this->linktitle) && $this->title )
			$this->title = '<a href="'. $this->link .'">'. $this->title .'</a>';
		
		// add icon to title, if necessary
		if ( $this->icon )
			$this->title = '<a class="kbrsswidget" href="'.$this->url.'" title="Syndicate this content"><img width="14" height="14" src="'.$this->icon.'" alt="RSS" style="background:orange;color:white;" /></a> '.$this->title;
		
		
		
		// PART II: PREPARE ITEM INFORMATION
		
		if ( is_array($rss->items) && !empty( $rss->items ) ){	// if there are items in the feed:
			
			if ($this->reverse_order)
				$rss->items = array_reverse( $rss->items );
			
			$rss->items = array_slice($rss->items, 0, $this->num_items);

			// initialize output. Note that $rss->items, $this->items, and $instance['items'] are NOT similar.
			$this->items = ''; // holds our output while we assemble it

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
	
	// helper for the items loop in previous function. This is where we implement most of the 
	// options. This is the part of the plugin to edit if you
	// want to add a new functionality. See the FAQ for details.
	function item_cleanup($text,$opts=false,$url=false){
		// some cleanup for security. To bypass, set KBRSS_WPMU false (top of this file) and use [opts:bypasssecurity] in field options.
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
		/////////////////////////////////////////////////////////////////////////////////////////////
		///////////////////////////////// BEGIN CUSTOMIZATIONS //////////////////////////////////////
		/////////////////////////////////////////////////////////////////////////////////////////////
		// If you want to write customizations, put them here. The variable to modify is $text. See the FAQ.
		/////////////////////////////////////////////////////////////////////////////////////////////
		/////////////////////////////////////////////////////////////////////////////////////////////
		
		/////////////////////////////////////////////////////////////////////////////////////////////
		/////////////////////////////////////////////////////////////////////////////////////////////
		///////////////////////////////// END CUSTOMIZATIONS ////////////////////////////////////////
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
	// another helper for preceding
	function make_date($string, $format){
		$time = strtotime( $string );
		if (false===$time || -1===$time)
			return $string;
		return date( $format, $time );
	}
	
	
	
	
	//////////////////////////////////
	//////////////////////////////////
	//////////////////////////////////
	// Helper methods for ADMIN.
	//////////////////////////////////
	//////////////////////////////////
	//////////////////////////////////
	
	
	// displays the widget's option form in the admin screen
	function build_form( $args, $inputs = null ) { // UPDATED

		$default_inputs = array( 'url' => true, 'title' => true, 'items' => true, 'icon'=>true, 'linktitle'=>true, 'display_empty'=> true, 'reverse_order'=>true, 'utf'=>true, 'output_format'=>true, 'output_begin'=>true, 'output_end'=>true );
		$inputs = wp_parse_args( $inputs, $default_inputs );
		extract( $args );
		//extract( $inputs, EXTR_SKIP);

		// scrub for form and set defaults where necessary
		
		$number = esc_attr( $number );
		$url    = esc_url( $url );
		$icon   = esc_url ( $icon );
		$title  = esc_attr( $title );
		$linktitle = (int) $linktitle;
		$display_empty = (int) $display_empty;
		$reverse_order = (int) $reverse_order;
		$utf = (int) $utf;
		$output_begin = esc_attr( $output_begin );
		$output_end = esc_attr( $output_end );
		
		$items  = (int) $items;
		if ( $items < 1 || KBRSS_MAXITEMS < $items )
			$items  = 10;

		if ( '' == $output_format )
			$output_format = "<li><a class='kbrsswidget' href='^link\$' title='^description\$'>^title\$</a></li>";
		$output_format = esc_attr( $output_format );

		if ( !empty($error) )
			echo '<p class="widget-error"><strong>' . sprintf( __('KB Advanced RSS Error: %s'), $error) . '</strong></p>';
			
		// form output begins now:
		?>	
			<p><strong>For help:</strong> <a href="http://wordpress.org/extend/plugins/kb-advanced-rss-widget/other_notes/">Read the documentation</a>.

			<table>
			<tr>
				<td><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title (optional):', 'kbwidgets'); ?> </label></td>
				<td colspan="3"><input style="width: 400px;" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></td>
			</tr>
			<tr>
				<td><label for="<?php echo $this->get_field_id('url'); ?>"><?php _e('RSS feed URL:', 'kbwidgets'); ?> </label></td>
				<td colspan="3"><input style="width: 400px;" id="<?php echo $this->get_field_id('url'); ?>" name="<?php echo $this->get_field_name('url'); ?>" type="text" value="<?php echo $url; ?>" /></td>
			</tr>
			<tr>
				<td><label for="<?php echo $this->get_field_id('icon'); ?>"><?php _e('RSS icon URL (optional):', 'kbwidgets'); ?> </label></td>
				<td colspan="3"><input style="width: 400px;" id="<?php echo $this->get_field_id('icon'); ?>>" name="<?php echo $this->get_field_name('icon'); ?>" value="<?php echo $icon; ?>" /></td>
			</tr>
			<tr>
				<td><label for="<?php echo $this->get_field_id('items'); ?>"><?php _e('Number of items to display:', 'kbwidgets'); ?> </label></td>
				<td colspan="3"><select id="<?php echo $this->get_field_id('items'); ?>" name="<?php echo $this->get_field_name('items'); ?>"><?php for ( $i = 1; $i <= KBRSS_MAXITEMS; ++$i ) echo "<option value='$i' ".($items==$i ? "selected='selected'" : '').">$i</option>"; ?></select></td>
			</tr>
			<tr>
				<td style="text-align:right;"><input type="checkbox" name="<?php echo $this->get_field_name('linktitle'); ?>" id="<?php echo $this->get_field_id('linktitle'); ?>" value="1" <?php if ( $linktitle ) { echo 'checked="checked"'; } ?> /> </td>
				<td><label for="<?php echo $this->get_field_id('linktitle'); ?>">Link title to feed URL? </label></td>


				<td><input type="checkbox" name="<?php echo $this->get_field_name('display_empty'); ?>" id="<?php echo $this->get_field_id('display_empty'); ?>" value="1" <?php if ( !$display_empty ){ echo 'checked="checked"'; } ?> /> </td>
				<td><label for="<?php echo $this->get_field_id('display_empty'); ?>">Hide widget when feed is down? </label></td>
			</tr>
			<tr>
				<td style="text-align:right;"><input type="checkbox" name="<?php echo $this->get_field_name('utf'); ?>" id="<?php echo $this->get_field_id('utf'); ?>" value="1" <?php if ( $utf ) { echo 'checked="checked"'; } ?> /> </td>
				<td><label for="<?php echo $this->get_field_id('utf'); ?>">Convert feed to UTF-8? </label></td>


				<td><input type="checkbox" name="<?php echo $this->get_field_name('reverse_order'); ?>" id="<?php echo $this->get_field_id('reverse_order'); ?>" value="1" <?php if ( $reverse_order ){ echo 'checked="checked"'; } ?> /> </td>
				<td><label for="<?php echo $this->get_field_id('reverse_order'); ?>">Reverse order of items in feed? </label></td>
			</tr>
			</table>
			
			<p> &nbsp; </p>
			
			<p><strong>Formatting Options</strong><br /><small>Use the default settings to make your feed look like it would using WP's built-in RSS widget. To customize, use the advanced fields below.</small></p>
			<p style="text-align:center;"><?php _e('What HTML should precede the feed? (Default: &lt;ul&gt;)', 'kbwidgets'); ?></p>
			<input style="width: 680px;" id="<?php echo $this->get_field_id('output_begin'); ?>" name="<?php echo $this->get_field_name('output_begin'); ?>" type="text" value="<?php echo $output_begin; ?>" />
			<p style="text-align:center;"><?php _e('What HTML should follow the feed? (Default: &lt;/ul&gt;)', 'kbwidgets'); ?></p>
			<input style="width: 680px;" id="<?php echo $this->get_field_id('output_end'); ?>" name="<?php echo $this->get_field_name('output_end'); ?>" type="text" value="<?php echo $output_end; ?>" />
			<p style="text-align:center;"><?php _e("How would you like to format the feed's items? Use <code>^element$</code>. Default:", 'kbwidgets'); ?><br /><small><code>&lt;li&gt;&lt;a href='^link$' title='^description$'&gt;^title$&lt;/a&gt;&lt;/li&gt;</code></small></p>
			<textarea style="width:680px;height:50px;" id="<?php echo $this->get_field_id('output_format'); ?>" name="<?php echo $this->get_field_name('output_format'); ?>" rows="3" cols="40"><?php echo $output_format; ?></textarea>
		
		<?php // done with admin form
	}


}
/////////// END OF CLASS



/////////// HELPER FUNCTIONS:



/**
 * After widget's admin screen is submitted, process the new settings.
 * If feed's url was changed, then check it.
 */
function KB_Adv_RSS_process( $widget_rss, $check_feed=true ) { // UPDATED
	$items = (int) $widget_rss['items'];
	if ( $items < 1 || KBRSS_MAXITEMS < $items )
		$items = 10;
	
	$url           = esc_url_raw(strip_tags( $widget_rss['url'] ));
	$icon          = esc_url_raw(strip_tags( $widget_rss['icon'] ));
	
	$valid = array(0,1);
	$linktitle = (in_array($widget_rss['linktitle'],$valid)) ? (int) $widget_rss['linktitle'] : 0;
	$display_empty = (in_array($widget_rss['display_empty'],$valid)) ? (int) (1-$widget_rss['display_empty']) : 0;
	$reverse_order = (in_array($widget_rss['reverse_order'],$valid)) ? (int) $widget_rss['reverse_order'] : 0;
	$utf = (in_array($widget_rss['utf'],$valid)) ? (int) $widget_rss['utf'] : 0;
	
	/*if (KBRSS_WPMU){ // extra filters if set TRUE
		$title     = trim(strip_tags( $widget_rss['title'] ));
		$output_format = trim( strip_tags( $widget_rss['output_format'] ) );
		$output_begin = trim( strip_tags( $widget_rss['output_begin'] ) );
		$output_end = trim( strip_tags( $widget_rss['output_end'] ) );
	}else{*/
		$title     = trim( $widget_rss['title'] );
		$output_format = trim( $widget_rss['output_format'] );
		$output_begin = trim( $widget_rss['output_begin'] );
		$output_end = trim( $widget_rss['output_end'] );
	//}

	// done scrubbing input; check the feed if the feed's url changed.
	
	/*
	if ( $check_feed ) {
		$rss = fetch_feed($url);
		$error = false;
		$link = '';
		if ( is_wp_error($rss) ) {
			$error = $rss->get_error_message();
		} else {
			$link = esc_url(strip_tags($rss->get_permalink()));
			while ( stristr($link, 'http') != $link )
				$link = substr($link, 1);
		}
	}
	*/
	
	return compact( 'title', 'url', 'link', 'items', 'error', 'icon', 'linktitle', 'display_empty', 'reverse_order', 'utf', 'output_format', 'output_begin', 'output_end' );
}













///////////////////////////////////////
///////////////////////////////////////
///////////////////////////////////////
// add a filter for troubleshooting feeds
///////////////////////////////////////
///////////////////////////////////////
function widget_kbrss_troubleshooter(){
	if ( !($_GET['kbrss']) )
		return;

	global $userdata;
	if ( $userdata->user_level >= 7 ){	// that ought to do it
		
		// try to find magpie:
		if ( !kbar_load_magpie() )
			wp_die( "Unable to load up magpie. Sorry." );
		
		$rss = @fetch_rss($_GET['kbrss']);
		$out = "<html><head><title>KB RSS Troubleshooter</title></head><body><div style='background:#cc0;padding:1em;'><h2>KB Advanced RSS Troubleshooter</h2><p>Below, you should see the feed as Magpie RSS passes it to the KB Advanced RSS widget. If you don\'t see anything, the feed might be down. Try reloading the page.</p></div><pre>";
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
add_action('template_redirect', 'widget_kbrss_troubleshooter');









////////////////////////////////////
// Register the widget:
add_action('widgets_init', create_function('', 'return register_widget("KB_Adv_RSS");'));


?>