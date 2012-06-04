<?php

#-----------------------------------------------------------------
# BuddyPress Related Functions
#-----------------------------------------------------------------



// Include BuddyPress JS and CSS functions
//................................................................

// Load the default BuddyPress AJAX functions
require_once( BP_PLUGIN_DIR . '/bp-themes/bp-default/_inc/ajax.php' );

// Add a class when admin bar is enabled for logged out users
if (!bp_get_option( 'hide-loggedout-adminbar' )) {
	// Add class to body
	add_filter('body_class','bp_adminbar_class');
	function bp_adminbar_class($classes) {
		$classes[] = 'bp-adminbar';
		return $classes;
	}	
}

if ( ! function_exists( 'bp_enqueue_defaults_init' ) ) :
	function bp_enqueue_defaults_init() {
		global $cssPath;
		
		// Load the BuddyPress CSS file
		theme_register_css( 'buddypress', $cssPath.'buddypress.css', 1 );
				
		// Load the default BuddyPress javascript
		wp_enqueue_script( 'bp-js', BP_PLUGIN_URL . '/bp-themes/bp-default/_inc/global.js', array( 'jquery' ) );

	}
endif;
add_action( 'init', 'bp_enqueue_defaults_init', 1);



// Theme styled (and resized) avatar URL's for BuddyPress functions 
//................................................................

if ( ! function_exists( 'bp_theme_avatar_url' ) ) :
	function bp_theme_avatar_url( $width, $height, $use_function = '', $avatarURL = '' ) {
		
		if (!$avatarURL) {
			switch ($use_function) {
				case "loggedin_user":
					//$avatarURL = bp_get_loggedin_user_avatar( 'type=full&width='.$width.'&height='.$height.'&html=false' );
					$avatarURL = bp_core_fetch_avatar(array( 'item_id' => $GLOBALS['bp']->loggedin_user->id, 'type' => 'full', 'html' => 'false', 'width' => $width, 'height' => $height ));
					break;
				case "member_avatar":
					$avatarURL = bp_core_fetch_avatar( array('item_id' => $GLOBALS['members_template']->member->id, 'email' => $GLOBALS['members_template']->member->user_email, 'type' => 'full', 'html' => 'false', 'width' => $width, 'height' => $height) );
					break;
				case "group_avatar":
					$avatarURL = bp_core_fetch_avatar( array('item_id' => $GLOBALS['groups_template']->group->id, 'object' => 'group', 'avatar_dir' => 'group-avatars', 'type' => 'full', 'html' => 'false', 'width' => $width, 'height' => $height) );
					break;
				case "group_member_avatar":
					$avatarURL = bp_core_fetch_avatar( array('item_id' => $GLOBALS['members_template']->member->user_id, 'email' => $GLOBALS['members_template']->member->user_email, 'type' => 'full', 'html' => 'false', 'width' => $width, 'height' => $height) );
					break;
				default: // Activity avatar 
					// Primary activity avatar is always a user, but can be modified via a filter
					$object  = apply_filters( 'bp_get_activity_avatar_object_' . $GLOBALS['activities_template']->activity->component, 'user' );
					$item_id = apply_filters( 'bp_get_activity_avatar_item_id', $GLOBALS['activities_template']->activity->user_id );
					// If this is a user object pass the users' email address for Gravatar so we don't have to refetch it.
					if ( 'user' == $object && empty( $email ) && isset( $GLOBALS['activities_template']->activity->user_email ) ) {
					   $email = $GLOBALS['activities_template']->activity->user_email;
					}
					$avatarURL =  bp_core_fetch_avatar( array( 'item_id' => $item_id, 'object' => $object, 'type' => 'full', 'html' => 'false', 'width' => $width, 'height' => $height, 'email' => $email ) );
					//$avatarURL = custom_bp_activity_avatar( 'type=full&width='.$width.'&height='.$height.'&html=false' );
			}
		}
		$avatarQuery = parse_url($avatarURL);
		if (!empty($avatarQuery[query])) {
			// has query params so get "d=http://..." containing image we need resized
			parse_str($avatarQuery[query]);
			$avatarImage = vt_resize( '', $d, $width, $height, true );
			$avatarURL = str_replace($d, $avatarImage['url'], $avatarURL);
		} else {
			// no query string so it's porbably a user uploaded image
			
			if (strrpos($avatarURL, 'bpfull-') > 0) {
				// test for "bpfull-". this comes before "bpful-128x128.jpg" (we don't want it already resized)
				$start = substr( $avatarURL, 0, strrpos($avatarURL, 'bpfull')+6 );
				$end = substr( $avatarURL, strripos($avatarURL, '.'), strlen($avatarURL) );
				// put image back together
				$avatarURL = $start . $end;
			}
			$avatarImage = vt_resize( '', $avatarURL, $width, $height, true );
			$avatarURL = $avatarImage['url'];
		}
		
		return $avatarURL;
	}
endif;


// Override the WP "get_avatar()" function if BP user avatar exists.
// 
// This is a second instance of filtering the WP "get_avatar()"
// function because the default BP core version ignors the size
// specified in the "get_avatar()" function with BP avatars and 
// instead uses the "BP_AVATAR_THUMB_WIDTH/HEIGHT" constants which
// makes no sense because it has the size information available.
//................................................................
if ( ! function_exists( 'bp_theme_fetch_avatar_filter' ) ) :

	// Not necessary in the WP admin, so...
	if ( !is_admin() ) :

		function bp_theme_fetch_avatar_filter( $avatar, $user, $size, $default, $alt = '' ) {
			global $pagenow;
		
			// Do not filter if inside WordPress options page
			if ( 'options-discussion.php' == $pagenow )
				return $avatar;
		
			// If passed an object, assume $user->user_id
			if ( is_object( $user ) )
				$id = $user->user_id;
		
			// If passed a number, assume it was a $user_id
			else if ( is_numeric( $user ) )
				$id = $user;
		
			// If passed a string and that string returns a user, get the $id
			else if ( is_string( $user ) && ( $user_by_email = get_user_by_email( $user ) ) )
				$id = $user_by_email->ID;
		
			// If somehow $id hasn't been assigned, return the result of get_avatar
			if ( empty( $id ) )
				return !empty( $avatar ) ? $avatar : $default;
		
			if ( !$alt )
				$alt = __( 'Avatar of %s', 'buddypress' );
		
			// Let the theme's BP functions handle the fetching of the avatar
			$bp_avatar = bp_theme_avatar_url( $size, $size, '', bp_core_fetch_avatar(array( 'item_id' => $id, 'type' => 'full', 'html' => 'false', 'width' => $size, 'height' => $size, 'alt' => $alt )) );
		
			// If BuddyPress found an avatar, use it. If not, use the result of get_avatar
			return ( !$bp_avatar ) ? $avatar : '<img src="'.$bp_avatar.'" width="'.$size.'" height="'.$size.'" alt="'.$alt.'">';
		}
		add_filter( 'get_avatar', 'bp_theme_fetch_avatar_filter', 10, 5 );

	endif;
endif;


// Modify the activity output
//................................................................

if ( ! function_exists( 'custom_bp_activity_action' ) ) :
	function custom_bp_activity_action() {
		$content = bp_get_activity_action();
		$content = str_replace('&middot;', '', $content); // no more dots between content
		$content = str_replace(': <span', '<span', $content); // get rid of the ":" before date
		$content = str_replace('started the forum topic', __( 'Started topic:', 'buddypress' ), $content);
		$content = str_replace('posted on the forum topic', __( 'Posted on:', 'buddypress' ), $content);
		$content = str_replace('</a> created', '</a> '. __( 'Created', 'buddypress' ), $content);	// capitalization fix
		$content = str_replace('</a> posted', '</a> '. __( 'Posted', 'buddypress' ), $content);		// capitalization fix
		$content = str_replace('</a> started', '</a> '. __( 'Started', 'buddypress' ), $content);	// capitalization fix
		$content = str_replace('in the group', __( 'in', 'buddypress' ), $content);
		if ( bp_activity_user_can_delete() ) {
			$content = str_replace('</p>', bp_get_activity_delete_link() . '</p>', $content);			// append delete link
		}
		echo $content;
	}
endif;



// Customized instance of custom_bp_activity_avatar (includes HTML option) 
//................................................................

if ( ! function_exists( 'bp_custom_activity_recurse_comments' ) ) :
	function bp_custom_activity_recurse_comments( $content ) {
		global $activities_template, $bp;

		$content = str_replace('</span> &middot;', '</span> ', $content); // change dot between "reply" and "delete"

		// Get the avatar for the current user
		$avatarURL = bp_theme_avatar_url(AVATAR_SIZE, AVATAR_SIZE);
		$newAvatar = '<div class="avatar" style="background-image: url(\''.$avatarURL.'\');"></div>';
		
		// The image tag to replace
		$fullImgPattern = '/<img[^>]+\>/i';

		// drop the new avatar into the existing $content
		preg_replace($fullImgPattern, $newAvatar, $content);		

		//$content = '<div class="item-container"><div class="item-content">'. $content .'</div></div>';

		return $content;

	}
endif;

add_filter('bp_activity_recurse_comments', 'bp_custom_activity_recurse_comments');




#-----------------------------------------------------------------
# BuddyPress specific menu options in WP Nav Menus
#-----------------------------------------------------------------


// Specify BuddyPress menu items to include in meta box
//................................................................

$bp_nav_menu_items = array(
	/*array (
		'post_title' => __( 'Activity', 'buddypress' ),
		'url' => get_home_url(1) . '/' . BP_ACTIVITY_SLUG . '/'
	),
	array (
		'post_title' => __( 'Members', 'buddypress' ),
		'url' => get_home_url(1) . '/' . BP_MEMBERS_SLUG . '/'
	),
	array (
		'post_title' => __( 'Groups', 'buddypress' ),
		'url' => get_home_url(1) . '/' . BP_GROUPS_SLUG . '/'
	),
	array (
		'post_title' => __( 'Forums', 'buddypress' ),
		'url' => get_home_url(1) . '/' . BP_FORUMS_SLUG . '/'
	),
	array (
		'post_title' => __( 'Blogs', 'buddypress' ),
		'url' => get_home_url(1) . '/' . BP_BLOGS_SLUG . '/'
	),
	array (
		'post_title' => __( 'Register', 'buddypress' ),
		'url' => get_home_url(1) . '/' . BP_REGISTER_SLUG . '/',
		'classes' => array('-function-is-user-logged-in')
	),*/
	array (
		'post_title' => __( 'Login', 'buddypress' ),
		'url' => get_home_url() . '/#LoginPopup',
		'classes' => array('popup', '-function-is-user-logged-in')
	),
	array (
		'post_title' => __( 'Logout', 'buddypress' ),
		'url' => add_query_arg( 
				array('action' => 'logout', '_wpnonce' => '((logout_nonce))'), 
				site_url('wp-login.php', 'login')
		),
		'classes' => array('function-is-user-logged-in')
	)
);

	

// Displays a metabox for BuddyPress specific menu items
//................................................................

function bp_nav_menu_item_meta_box() {
	global $_nav_menu_placeholder, $nav_menu_selected_id, $bp_nav_menu_items;

	$post_type = array();
	
	$post_type_name = 'bp-menu';

	$args = array(
		'offset' => 0,
		'order' => 'ASC',
		'orderby' => 'title',
		'posts_per_page' => 50,
		'post_type' => $post_type_name,
		'suppress_filters' => true,
		'update_post_term_cache' => false,
		'update_post_meta_cache' => false
	);

	if ( isset( $post_type['args']->_default_query ) )
		$args = array_merge($args, (array) $post_type['args']->_default_query );

	$menu_items = $bp_nav_menu_items;

	if ( !$menu_items )
		$error = '<li id="error">Error: Links not found</li>';

	$db_fields = false;
	$walker = new Walker_Nav_Menu_Checklist( $db_fields );

	$current_tab = 'all';

	?>
	<div id="posttype-<?php echo $post_type_name; ?>" class="posttypediv">
		<ul id="posttype-<?php echo $post_type_name; ?>-tabs" class="posttype-tabs add-menu-item-tabs">
			<li <?php echo ( 'all' == $current_tab ? ' class="tabs"' : '' ); ?>><a class="nav-tab-link" href="<?php if ( $nav_menu_selected_id ) echo esc_url(add_query_arg($post_type_name . '-tab', 'all', remove_query_arg($removed_args))); ?>#<?php echo $post_type_name; ?>-all"><?php _e('All Links', 'buddypress'); ?></a></li>
		</ul>

		<div id="<?php echo $post_type_name; ?>-all" class="tabs-panel tabs-panel-view-all <?php
			echo ( 'all' == $current_tab ? 'tabs-panel-active' : 'tabs-panel-inactive' );
		?>">
			<ul id="<?php echo $post_type_name; ?>checklist" class="list:<?php echo $post_type_name?> categorychecklist form-no-clear">
				<?php
				$links = array();
				$deafault_links = array (
					'ID' => 0,
					'object_id' => 0,
					'post_content' => '',
					'post_excerpt' => '',
					'post_parent' => '',
					'post_type' => 'nav_menu_item',
					'object' => '',
					'type' => 'custom',
					'post_title' => '',
					'url' => ''
				);

				foreach( (array) $menu_items as $menu_item ) {
					$menu_item['object_id'] = ( 0 > $_nav_menu_placeholder ) ? intval($_nav_menu_placeholder) - 1 : -1;
					$links[] = (object) array_merge($deafault_links, $menu_item);
				}
				$args['walker'] = $walker;
				echo walk_nav_menu_tree( array_map('wp_setup_nav_menu_item', $links), 0, (object) $args );
				?>
			</ul>
		</div><!-- /.tabs-panel -->

		<p class="button-controls">
			<span class="list-controls">
				<a href="<?php
					echo esc_url(add_query_arg(
						array(
							$post_type_name . '-tab' => 'all',
							'selectall' => 1,
						),
						remove_query_arg($removed_args)
					));
				?>#posttype-<?php echo $post_type_name; ?>" class="select-all"><?php _e('Select All', 'buddypress'); ?></a>
			</span>

			<span class="add-to-menu">
				<img class="waiting" src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" alt="" />
				<input type="submit"<?php disabled( $nav_menu_selected_id, 0 ); ?> class="button-secondary submit-add-to-menu" value="<?php esc_attr_e('Add to Menu'); ?>" name="add-post-type-menu-item" id="submit-posttype-<?php echo $post_type_name; ?>" />
			</span>
		</p>

	</div><!-- /.posttypediv -->
	<?php
}


// This function tells WP to add a new "meta box"
function add_bp_menu_meta_box() {
	global $bp_nav_menu_items;
	// Create the meta box call
	add_meta_box( "add-bpMenu", __( 'Special Functionality', 'buddypress' ), 'bp_nav_menu_item_meta_box', 'nav-menus', 'side', 'default', $bp_nav_menu_items );
}
// Hook things in, late enough so that add_meta_box() is defined
if (is_admin())
	add_action('admin_menu', 'add_bp_menu_meta_box');
	


// Custom Default User Avatar
if ( ! function_exists( 'bp_custom_default_avatar' ) ) :
	function bp_custom_default_avatar( $url ) {
		global $themePath;
		return $themePath .'assets/images/icons/avatar-1.png';
	}
	
	// Apply filter to BP function
	add_filter( 'bp_core_mysteryman_src', 'bp_custom_default_avatar' );
endif;



// Custom Default User Avatar
if ( ! function_exists( 'bp_custom_default_avatar' ) ) :
	function bp_custom_default_avatar( $url ) {
		global $themePath;
		return $themePath .'assets/images/icons/avatar-3.png';
	}
	
	// Apply filter to BP function
	add_filter( 'bp_core_mysteryman_src', 'bp_custom_default_avatar' );
endif;



// Custom Default Group Avatar (http://wpmu.org/how-to-add-a-custom-default-avatar-for-buddypress-members-and-groups/)
/*if ( ! function_exists( 'bp_custom_default_group_avatar' ) ) :
	function bp_custom_default_group_avatar($avatar) {
		global $bp, $groups_template, $themePath;
	
		if( strpos($avatar,'group-avatars') ) {
			return $avatar;
		} else {
			$custom_avatar = $themePath .'assets/images/icons/group-avatar-1.png';
			
			if($bp->current_action == "")
				return '<img width="'.BP_AVATAR_THUMB_WIDTH.'" height="'.BP_AVATAR_THUMB_HEIGHT.'" src="'.$custom_avatar.'" class="avatar" alt="' . attribute_escape( $groups_template->group->name ) . '" />';
			else
				return '<img width="'.BP_AVATAR_FULL_WIDTH.'" height="'.BP_AVATAR_FULL_HEIGHT.'" src="'.$custom_avatar.'" class="avatar" alt="' . attribute_escape( $groups_template->group->name ) . '" />';
		}
	}
	
	// Apply filter to BP function
	add_filter( 'bp_get_group_avatar', 'bp_custom_default_group_avatar');
endif;*/

?>