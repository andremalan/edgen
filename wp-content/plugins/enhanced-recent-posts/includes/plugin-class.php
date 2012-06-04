<?php
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


//#################################################################
// Stop direct call
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { 
	die('You are not allowed to call this page directly.'); 
}
//#################################################################

//#################################################################
// Some constants 
//#################################################################

//#################################################################
// The plugin class
if (!class_exists("EnhancedRecentPostsPlugin")) {

class EnhancedRecentPostsPlugin {
	var $current_version = '1.3.1';
	var $options;
	
	/**
	* Constructor
	*/
	function EnhancedRecentPostsPlugin() {
		$this->load_options();
	}
	
	/**
	* Function to be called when the plugin is activated
	*/
	function activate() {
		global $enh_rp_widget;
		
		$active_version = $this->options['active_version'];
		
		if ($active_version==$this->current_version) {
			// do nothing
		} else {
			if ($active_version=='') {			
				add_option(ENHANCED_RECENT_POSTS_PLUGIN_OPTIONS, 
					$this->options, 
					'Enhanced Recent Posts plugin options');
				add_option(ENHANCED_RECENT_POSTS_WIDGET_OPTIONS, 
					$enh_rp_widget->options, 
					'Enhanced Recent Posts widget options');
			} 
		}
		
		// Update version number & save new options
		$this->options['active_version'] = $this->current_version;
		$this->save_options();
	}
	
	/**
	* Function that echoes the recent posts
	*/
	function list_recent_posts($args = '') {	
		$defaults = array(
			'posts_to_show'		=> 5,
			'show_select'		=> 'show-all',
			'include_cat'		=> '',
			'exclude_cat'		=> '',
			'orderby'			=> 'date',
			'show_date' 		=> 'off'
		);
		
		$r = wp_parse_args( $args, $defaults );

		// Build the parameter string for query posts
		//--
		$query_param = 'showposts=' . $r['posts_to_show'] . '&what_to_show=posts&nopaging=0&post_status=publish&orderby=' . $r['orderby'];
		
		if ($r['show_select']=='show-include') {
			$query_param .= "&cat=" . $r['include_cat'];
		} else if ($r['show_select']=='show-exclude') {
			$query_param .= "&cat=";
			
			$exclude_cat = explode(",", $r['exclude_cat']);
			for ($i=0; $i<count($exclude_cat); $i++) {
				$query_param .= "-" . $exclude_cat[$i];				
				if ($i!=count($exclude_cat)-1)
					$query_param .= ",";
			}
		}
		
		// Query the DB
		//--
		$posts = new WP_Query($query_param);
		
		if ($posts->have_posts()) {		
			echo '<ul class="enhanced-recent-posts">' . "\n";		
			while ($posts->have_posts()) {
				$posts->the_post();
			
?>
				<li>
					<?php if($r['show_date'] == 'on'): ?><span><?php the_time('F jS, Y'); ?>&nbsp;</span><?php endif;?>
					<a href="<?php the_permalink() ?>"><?php if ( get_the_title() ) the_title(); else the_ID(); ?></a>
				</li>
<?php
			
			}		
			echo '</ul>' . "\n";
		}
		
		// Restore global post data stomped by the_post().
		//--
		wp_reset_query();
	}
	
	/**
	* Load the options from database (set default values in case options are not set)
	*/
	function load_options() {
		$this->options = get_option(ENHANCED_RECENT_POSTS_PLUGIN_OPTIONS);
		
		if ( !is_array($this->options) ) {
			$this->options = array(
				'active_version'		=> ''
			);
		}
	}
	
	/**
	* Save options to database
	*/
	function save_options() {
		update_option(ENHANCED_RECENT_POSTS_PLUGIN_OPTIONS, $this->options);
	}
	
} // class EnhancedRecentPostsPlugin
} // if (!class_exists("EnhancedRecentPostsPlugin"))
	
?>