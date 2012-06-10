<?php global $cssPath, $jsPath, $themePath, $theLayout; ?>
<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"><?php // Force latest IE rendering engine ?>
<title><?php
	if (is_home()) { bloginfo('name'); echo " - "; bloginfo('description'); }
	elseif (is_category() || is_tag()) { single_cat_title(); }
	elseif (is_single() || is_page()) { single_post_title(); }
	elseif (is_search()) { _e('Search Results', THEME_NAME); echo " ".wp_specialchars($s); }
	else { echo trim(wp_title(' ',false)); }
	if (!is_home()) { theme_var('options,append_to_title'); }
	?></title>

<?php // Favorites and mobile bookmark icons ?>
<link rel="shortcut icon" href="<?php theme_var('options,favorites_icon','http://para.llel.us/favicon.ico'); ?>">
<link rel="apple-touch-icon-precomposed" href="<?php theme_var('options,apple_touch_icon','http://para.llel.us/apple-touch-icon.png'); ?>">

<?php // JS variables needed to trigger theme functionality ?>
<script type="text/javascript"> 
	var fadeContent = '<?php theme_var('options,fade_in_content','none'); ?>'; 
	var toolTips = '<?php theme_var('options,tool_tips','none'); ?>'; 
</script>

<?php 
// WordPress headers.
// This includes all theme CSS and some JS files. You can add or modify the list from "functions.php" 
wp_head();

// Feed link / Pingback link ?>
<link rel="alternate" type="application/atom+xml" title="<?php bloginfo('name'); ?> Atom Feed" href="<?php bloginfo('atom_url'); ?>">
<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>">

<?php // jQuery fallback. Will load a local copy if WP Head fails to load. ?>
<script>!window.jQuery && document.write(unescape('%3Cscript src="<?php echo $jsPath; ?>libs/jquery-1.6.2.min.js"%3E%3C/script%3E'))</script>

<!--[if lte IE 8]>
<link rel="stylesheet" type="text/css" href="<?php echo $cssPath; ?>ie.css" />
<![endif]-->

<style type="text/css">
<?php 
// Body font
if ($theLayout['body_font']) : 
	echo 'body, select, input, textarea {  font-family: '. $theLayout['body_font'] .'; } ';
endif;

// Default body background
$bg = get_theme_var('design_setting,body');
if ( $bg['bg_color'] || $bg['background']) {
	$theBg = ($bg['bg_color']) ? '#'.str_replace('#','',$bg['bg_color']) : 'transparent';
	if ($bg['background']) {
		$theBg .= ' url(\''.$bg['background'].'\') '.$bg['bg_repeat'].' '.$bg['bg_pos_x'].' '.$bg['bg_pos_y'];
	}
	echo 'body { background: '.$theBg.'; }';
}
	
// Heading font
if ($theLayout['heading_font']['standard']) : 
	echo 'h1, h2, h3, h4, h5, h6 {  font-family: '. $theLayout['heading_font']['standard'] .'; } ';
endif;

// Custom CSS entered in design settings
if ($customCSS = get_theme_var('design_setting,css_custom')) :
	echo prep_content($customCSS); 
endif;
?>
</style> 

<!-- facebook custom og: meta tags for sharing contributions -->
<meta property="og:title" content="I just contributed on Education Generation!" />
<meta property="og:description" content="I just contributed to a student on Education Generation! Education Generation is a global community providing access to quality education for high potential students. Through their online platform, individuals from around the world make contributions as low as $20 to collectively support individual students. 100% of your money (after paypal fees) supports their studies."/>
<meta property="og:image" content="http://educationgeneration.org/wp-content/uploads/2011/10/logo.png" />


<?php 

 
// Custom JavaScript entered in design settings
if ($customJS = get_theme_var('design_setting,js_custom')) : ?>
<script type="text/javascript">
	<?php echo prep_content($customJS); ?>
</script>
<?php endif; ?>