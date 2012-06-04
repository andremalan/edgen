<style type="text/css">
	form textarea.input-textarea { width: 100% !important; height: 200px !important; }
</style>
<?php

$keys = $layout_admin->keys;
$data = $layout_admin->data;

// Setup defaults from other areas
// ----------------------------------------

// Header drop down data
$page_headers = $layout_admin->get_val('page_headers', '_plugin');
$page_headers_saved = $layout_admin->get_val('page_headers', '_plugin_saved');
if (!empty($page_headers_saved)) {
	$page_headers = array_merge((array)$page_headers_saved, (array)$page_headers);
}

// Footer drop down data
$page_footers = $layout_admin->get_val('page_footers', '_plugin');
$page_footers_saved = $layout_admin->get_val('page_footers', '_plugin_saved');
if (!empty($page_footers_saved)) {
	$page_footers = array_merge((array)$page_footers_saved, (array)$page_footers);
}

// Page layout drop down data
$page_layouts = $layout_admin->get_val('layouts', '_plugin');
$page_layouts_saved = $layout_admin->get_val('layouts', '_plugin_saved');
if (!empty($page_layouts_saved)) {
	$page_layouts = array_merge((array)$page_layouts_saved, (array)$page_layouts);
}

// Assemble the drop down options for each
$select_header = array();
if (!empty($page_headers)) {
	foreach ((array) $page_headers as $item) {
		if (!empty($item)) $select_header[$item['key']] = $item['label'];
	}
}
$select_footer = array();
if (!empty($page_footers)) {
	foreach ((array) $page_footers as $item) {
		if (!empty($item)) $select_footer[$item['key']] = $item['label'];
	}
}
$select_layout = array();
if (!empty($page_layouts)) {
	foreach ((array) $page_layouts as $item) {
		if (!empty($item)) $select_layout[$item['key']] = $item['label'];
	}
}

// DEFAULT DESIGN SETTINGS

	// FORCE THE KEYS - THIS IS IMPORTANT FOR SECTIONS WITH OPTIONS ON THE MAIN PAGE
	$layout_admin->keys = array('_plugin', 'layout_settings');
	
	echo	'<h2>'. __('Layout &amp; Template Defaults', THEME_NAME) .'</h2>' . 
			'<div class="hr"></div>' .
			'<p>' . __('Configure the themes default layout options.', THEME_NAME) . '</p>';

	$form_link = array('navigation' => 'layout_settings', 'action' => 'save', 'keys' => '_plugins,layout_settings', 'action_keys' => '_plugins,layout_settings');
	$layout_admin->settings_form_header($form_link);

	echo '<a name="layouts"></a>';
	echo '<table class="form-table">';

		$select = $select_header;
		$comment = __('This header will be used for layouts without a header specified.', THEME_NAME);
		$comment = $layout_admin->format_comment($comment);
		$row = array(__('Header', THEME_NAME), $layout_admin->settings_select('layout,header', $select) . $comment);
		$layout_admin->setting_row($row);

		$select = $select_footer;
		$comment = __('This footer will be used for layouts without a footer specified.', THEME_NAME);
		$comment = $layout_admin->format_comment($comment);
		$row = array(__('Footer', THEME_NAME), $layout_admin->settings_select('layout,footer', $select) . $comment);
		$layout_admin->setting_row($row);

		$select = $select_layout;
		$comment = __('This layout will be used for any content without a layout specified.', THEME_NAME);
		$comment = $layout_admin->format_comment($comment);
		$row = array(__('Main Layout', THEME_NAME), $layout_admin->settings_select('layout,default', $select) . $comment);
		$layout_admin->setting_row($row);

	echo '</table>';
	
	echo '<div class="hr"></div> <h3>'. __('Templates', THEME_NAME) .'</h3>';
	echo '<table class="form-table">';

		// Home page
		$select = $select_layout;
		$comment = __('The default layout to use for the home page of the site. Only applies for "<code>Front page displays > Your latest posts</code>" (<a href="options-reading.php">Reading Settings</a>). If using "A static page" you should set your home page from the "Layout Options" box for that specific page.', THEME_NAME);
		$comment = $layout_admin->format_comment($comment);
		$row = array(__('Home page', THEME_NAME), $layout_admin->settings_select('layout,home', $select) . $comment);
		$layout_admin->setting_row($row);

		// Pages
		$select = $select_layout;
		$comment = __('The default layout to use for new pages.', THEME_NAME);
		$comment = $layout_admin->format_comment($comment);
		$row = array(__('Pages', THEME_NAME), $layout_admin->settings_select('layout,page', $select) . $comment);
		$layout_admin->setting_row($row);

		// Posts
		$select = $select_layout;
		$comment = __('The default layout to use for new posts.', THEME_NAME);
		$comment = $layout_admin->format_comment($comment);
		$row = array(__('Posts', THEME_NAME), $layout_admin->settings_select('layout,post', $select) . $comment);
		$layout_admin->setting_row($row);

		// Blog
		$blog_select = $select_layout;
		$blog_comment = __('This is the WordPress version of a "blog page". Used when a category, author, or date is queried. Note that this layout will be overridden by selections for "Category", "Author", "Tag" and "Date" for their respective query types.', THEME_NAME);
		$blog_comment = $layout_admin->format_comment($blog_comment);
	
			$select = $select_layout;
			array_unshift($select, __('Category (optional)', THEME_NAME)); // add blank value to start (this option can have a "none" setting)
			$comment = __('<strong>Category layout:</strong> Used when a category is queried. Typically the same layout as "Blog".', THEME_NAME);
			$comment = $layout_admin->format_comment($comment);
			$category_row = '<br />' . $layout_admin->settings_select('layout,category', $select) . $comment;
	
			$select = $select_layout;
			array_unshift($select, __('Author (optional)', THEME_NAME)); // add blank value to start (this option can have a "none" setting)
			$comment = __('<strong>Author layout:</strong> Used when posts for a specific author are queried. Typically the same layout as "Blog".', THEME_NAME);
			$comment = $layout_admin->format_comment($comment);
			$author_row = '<br />' . $layout_admin->settings_select('layout,author', $select) . $comment;
	
			$select = $select_layout;
			array_unshift($select, __('Tag (optional)', THEME_NAME)); // add blank value to start (this option can have a "none" setting)
			$comment = __('<strong>Tag layout:</strong> Used when a tag is queried. Typically the same layout as "Blog".', THEME_NAME);
			$comment = $layout_admin->format_comment($comment);
			$tag_row = '<br />' . $layout_admin->settings_select('layout,tag', $select) . $comment;
	
			$select = $select_layout;
			array_unshift($select, __('Date (optional)', THEME_NAME)); // add blank value to start (this option can have a "none" setting)
			$comment = __('<strong>Date layout:</strong> Used when posts for a specific date or time are queried. Typically the same layout as "Blog".', THEME_NAME);
			$comment = $layout_admin->format_comment($comment);
			$date_row = '<br />' . $layout_admin->settings_select('layout,date', $select) . $comment;
		
			// Output completed blog row
			$row = array(__('Blog', THEME_NAME), $layout_admin->settings_select('layout,blog', $blog_select) . $blog_comment . $category_row . $author_row . $tag_row . $date_row);
			$layout_admin->setting_row($row); 
		

		// Search
		$select = $select_layout;
		$comment = __('The layout to use for search results.', THEME_NAME);
		$comment = $layout_admin->format_comment($comment);
		$row = array(__('Search', THEME_NAME), $layout_admin->settings_select('layout,search', $select) . $comment);
		$layout_admin->setting_row($row);

		// Error
		$select = $select_layout;
		$comment = __('The layout to use for error pages.', THEME_NAME);
		$comment = $layout_admin->format_comment($comment);
		$row = array(__('Error', THEME_NAME), $layout_admin->settings_select('layout,error', $select) . $comment);
		$layout_admin->setting_row($row);


		// BuddyPress layouts
		if (bp_plugin_is_active()) {

			$BP_select = $select_layout;
			$BP_comment = __('The default layout for your BuddyPress pages.', THEME_NAME);
			$BP_comment = $layout_admin->format_comment($BP_comment);
		
				$select = $select_layout;
				array_unshift($select, __('Activity (optional)', THEME_NAME)); // add blank value to start (this option can have a "none" setting)
				$comment = __('<strong>Activity</strong>', THEME_NAME);
				$comment = $layout_admin->format_comment($comment);
				$bp_activity_row = '<br />' . $layout_admin->settings_select('layout,bp-activity', $select) . $comment;
		
				$select = $select_layout;
				array_unshift($select, __('Blogs (optional)', THEME_NAME)); // add blank value to start (this option can have a "none" setting)
				$comment = __('<strong>Blogs</strong>', THEME_NAME);
				$comment = $layout_admin->format_comment($comment);
				$bp_blogs_row = '<br />' . $layout_admin->settings_select('layout,bp-blogs', $select) . $comment;
		
				$select = $select_layout;
				array_unshift($select, __('Forums (optional)', THEME_NAME)); // add blank value to start (this option can have a "none" setting)
				$comment = __('<strong>Forums</strong>', THEME_NAME);
				$comment = $layout_admin->format_comment($comment);
				$bp_forums_row = '<br />' . $layout_admin->settings_select('layout,bp-forums', $select) . $comment;
		
				$select = $select_layout;
				array_unshift($select, __('Groups (optional)', THEME_NAME)); // add blank value to start (this option can have a "none" setting)
				$comment = __('<strong>Groups</strong>', THEME_NAME);
				$comment = $layout_admin->format_comment($comment);
				$bp_groups_row = '<br />' . $layout_admin->settings_select('layout,bp-groups', $select) . $comment;
				
				$select = $select_layout;
				array_unshift($select, __('Groups - single (optional)', THEME_NAME)); // add blank value to start (this option can have a "none" setting)
				$comment = __('<strong>Groups - single</strong>', THEME_NAME);
				$comment = $layout_admin->format_comment($comment);
				$bp_groups_single_row = '<br />' . $layout_admin->settings_select('layout,bp-groups-single', $select) . $comment;
				
				$select = $select_layout;
				array_unshift($select, __('Groups - single plugins (optional)', THEME_NAME)); // add blank value to start (this option can have a "none" setting)
				$comment = __('<strong>Groups - single plugins</strong>', THEME_NAME);
				$comment = $layout_admin->format_comment($comment);
				$bp_groups_single_plugins_row = '<br />' . $layout_admin->settings_select('layout,bp-groups-single-plugins', $select) . $comment;
				
				$select = $select_layout;
				array_unshift($select, __('Members (optional)', THEME_NAME)); // add blank value to start (this option can have a "none" setting)
				$comment = __('<strong>Members</strong>', THEME_NAME);
				$comment = $layout_admin->format_comment($comment);
				$bp_members_row = '<br />' . $layout_admin->settings_select('layout,bp-members', $select) . $comment;

				$select = $select_layout;
				array_unshift($select, __('Members - single (optional)', THEME_NAME)); // add blank value to start (this option can have a "none" setting)
				$comment = __('<strong>Members - single</strong>', THEME_NAME);
				$comment = $layout_admin->format_comment($comment);
				$bp_members_single_row = '<br />' . $layout_admin->settings_select('layout,bp-members-single', $select) . $comment;
				
				
				$select = $select_layout;
				array_unshift($select, __('Members - single plugins (optional)', THEME_NAME)); // add blank value to start (this option can have a "none" setting)
				$comment = __('<strong>Members - single plugins</strong>', THEME_NAME);
				$comment = $layout_admin->format_comment($comment);
				$bp_members_single_plugins_row = '<br />' . $layout_admin->settings_select('layout,bp-members-single-plugins', $select) . $comment;
				
				//$bp_members_single_plugins_row = $layout_admin->settings_hidden('layout,bp-members-single-plugins');
				
				// Output completed blog row
				$row = array(__('BuddyPress', THEME_NAME), $layout_admin->settings_select('layout,bp', $BP_select) . $BP_comment . $bp_activity_row . $bp_blogs_row . $bp_forums_row . $bp_groups_row . $bp_groups_single_row . $bp_groups_single_plugins_row . $bp_members_row . $bp_members_single_row . $bp_members_single_plugins_row );
				$layout_admin->setting_row($row); 

		}  // end BuddyPress layouts


	echo '</table>';

	// key for this data type is generated at random when adding new slides.
	echo '<input type="hidden" name="key" value="'. $layout_admin->get_val('key') .'" />'; // Normal way causes error --> $layout_admin->settings_hidden('index'); 

	// save button
	$layout_admin->settings_save_button(__('Save Settings', THEME_NAME), 'button-primary');
	
	$options = array('navigation' => 'export', 'keys' => '_plugin,layout_settings', 'class' => 'button');
	echo '<br /><div>' . $layout_admin->settings_link(__('Export Layout Settings', THEME_NAME), $options) . '</div><br />';
	

	?>
	<br /><br />


	
	<script type="text/javascript">
	
	jQuery(document).ready(function($) {
		
		// show/hide custom skin input
		jQuery("select[name='skin']").change( function() {
			var $custom = jQuery("#custom_skin_input");
			if (jQuery(this).val() == 'custom') {
				$custom.slideDown();
			} else {
				$custom.slideUp();
			}
		});
		
		// show/hide custom heading font
		jQuery("select[name='fonts,heading']").change( function() {
			var $custom_cufon = jQuery("#heading_cufon");
			var $custom_standard = jQuery("#heading_standard");

			if (jQuery(this).val() == 'custom:cufon') {
				$custom_cufon.slideDown();
			} else {
				$custom_cufon.slideUp();
			}

			if (jQuery(this).val() == 'custom:standard') {
				$custom_standard.slideDown();
			} else {
				$custom_standard.slideUp();
			}
		});
		
		// show/hide custom body font
		jQuery("select[name='fonts,body']").change( function() {
			var $custom = jQuery("#custom_body_font");
			if (jQuery(this).val() == 'custom:standard') {
				$custom.slideDown();
			} else {
				$custom.slideUp();
			}
		});


	});
	
	</script> 
