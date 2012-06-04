<?php global $designBypassContent;

# Looking for the footer content? You'll find it inside "footer-default.php"

/*
	This page is the second part in catching plugins that try loading content outside the design making direct calls to "the_header()" and "the_footer()"
	Previously in "header.php" we turned on output buffering to capture the output. Now we will return that content and load the theme design.
*/

$designBypassContent = ob_get_clean();

/*
	Last thing... load the theme design normally and it will detect the content of "$improperLoadContent" and add it to the output.
*/

create_page_layout('page');	// context = page

?>