<?php

if (__FILE__ == $_SERVER['SCRIPT_FILENAME']) { die(); }

/* 
 * NOTE: this file is for compatibility.
 * Layouts are created in the theme options and "design-{name}.php" files.
 * Content is generated by the "template-{context}.php" files.
*/

/* This template is only used on multisite installations */
create_page_layout('bp-registration-activate');	// context = bp-registration-activate

?>