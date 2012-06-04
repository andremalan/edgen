<?php global $designBypassContent;

# Looking for the footer content? You'll find it inside "footer-default.php"

/*
	This page is the second part in catching plugins that try loading content outside the design making direct calls to "the_header()" and "the_footer()"
	Previously in "header.php" we turned on output buffering to capture the output. Now we will return that content and load the theme design.
*/

$designBypassContent = ob_get_clean();

// Add some BuddyPress specific theme structures
$designBypassContent = '<div id="BP-Container"><div id="BP-Content">'. $designBypassContent .'</div></div><div class="clear"></div>';
$designBypassContent = str_replace('item-list-tabs', 'item-list-tabs bp-content-tabs', $designBypassContent);
/*
	Last thing... load the theme design normally and it will detect the content of "$improperLoadContent" and add it to the output.
*/

create_page_layout('bp');	// context = page

?>