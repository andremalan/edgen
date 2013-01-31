<!doctype html>  
<?php require_once( FRAMEWORK_DIR . 'utilities/ti/ti.php'); ?>
<!--[if lt IE 7 ]> <html lang="en" class="no-js ie6"> <![endif]-->
<!--[if IE 7 ]>    <html lang="en" class="no-js ie7"> <![endif]-->
<!--[if IE 8 ]>    <html lang="en" class="no-js ie8"> <![endif]-->
<!--[if IE 9 ]>    <html lang="en" class="no-js ie9"> <![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--> <html class="no-js" <?php language_attributes(); ?>> <!--<![endif]-->
<head>
<!--test-->
	<?php get_header('default'); ?>

<!--end teset-->
</head>

<body <?php body_class(); ?>>
<div id="Wrapper">
	<?php
	
	// ideahack function to get the current page url to checkif its the transaction-results url

	function curPageURL() {
	 $pageURL = 'http';
	 if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
	 $pageURL .= "://";
	 if ($_SERVER["SERVER_PORT"] != "80") {
	  $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
	 } else {
	  $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
	 }
	 return $pageURL;
	}

	// ideahack check to see if we are on the transaction results page

	$isresults = strpos(curPageUrl(), 'transaction-results');

	// ideahack code to make "Pending Donations" happen
	
	if (wpsc_cart_item_count() > 0 && !$isresults) { // if you have something in your cart, and are not on the transaction results page (meaning you've just paid), display the pending donations tab
		echo '<div id="gotocheckout" style="background: none repeat scroll 0 0 #C5392F; border-radius: 0 0 4px 0; float: left; padding: 5px 15px 5px 10px;"><a style="color: #f5f5f5;" href="/students/checkout">View Pending Donations</a></div>'; }
	?>
	<div id="Top">
		<div class="clearfix">
		
			<?php emptyblock('top') ?>
			
		</div>		
	</div> <!--! end of #Top -->
	
	<div id="Middle">
		
		<div class="pageWrapper theContent clearfix">
			
			<div class="inner-1">
								
				<div class="inner-2 contentMargin">
			
					<?php emptyblock('middle') ?>
			
				</div>
			</div>
		</div> <!--! end of .pageWrapper -->
	</div> <!--! end of #Middle -->
	
	<div id="Bottom">		

		<?php emptyblock('bottom') ?>
		
	</div> <!--! end of #Bottom -->
</div> <!--! end of #Wrapper -->

<?php get_footer('default'); ?>
  
</body>
</html>