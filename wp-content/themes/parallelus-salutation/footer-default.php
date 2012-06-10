<?php global $cssPath, $jsPath, $themePath, $theLayout, $theHeader;

// Login popup window 
// - Call with link: <a href="#LoginPopup" class="inlinePopup">Login</a>  ?>

<div class="hidden">
	<div id="LoginPopup">
		<form class="loginForm" id="popupLoginForm" method="post" action="<?php echo wp_login_url(); // optional redirect: wp_login_url('/redirect/url/'); ?>">
			<div id="loginBg"><div id="loginBgGraphic"></div></div>
			<div class="loginContainer">
				<h3>Sign in to your account</h3>
				<fieldset class="formContent">
					<legend>Account Login</legend>
					<div class="fieldContainer">
						<label for="ModalUsername">Username</label>
						<input id="ModalUsername" name="log" type="text" class="textInput" />
					</div>
					<div class="fieldContainer">
						<label for="ModalPassword">Password</label>
						<input id="ModalPassword" name="pwd" type="password" class="textInput" />
					</div>
				</fieldset>
			</div>
			<div class="formContent">
				<button type="submit" class="btn signInButton"><span>Sign in</span></button>
			</div>
			<div class="hr"></div>
			<div class="formContent">
				<a href="<?php bloginfo('wpurl') ?>/wp-login.php?action=lostpassword" id="popupLoginForgotPswd">Forgot your password?</a>
			</div>
		</form>
	</div>
</div>
<?php

// WordPress Footer Includes
wp_footer();

// Cufon fonts for headings
if ($theLayout['heading_font']['cufon']) : ?>
<script src="<?php echo $theLayout['heading_font']['cufon']; ?>"></script>
<script type="text/javascript">
	Cufon.replace
		('h1:not(.cta-title),h2:not(.cta-title),h3:not(.cta-title),h4:not(.cta-title),h5:not(.cta-title),h6:not(.cta-title)', {hover: true})
		('.widget .item-list .item-title', {hover: true });
	Cufon.now();
</script>
<?php endif; ?>

<?php // Main menu dropdowns  ?>
<script type="text/javascript">
	if ( jQuery('#MM ul') ) { ddsmoothmenu.init({ mainmenuid: "MM", orientation: "h", classname: "slideMenu", contentsource: "markup" }); }
</script>
<script src="<?php echo $jsPath; ?>onLoad.js"></script><?php // Functions to call after page load ?>

<?php 
// Google analytics (asynchronous method from http://mathiasbynens.be/notes/async-analytics-snippet)
if (get_theme_var('options,google_analytics')) : ?>
	<script type="text/javascript">
	var _gaq = [['_setAccount', '<?php theme_var('options,google_analytics'); ?>'], ['_trackPageview']];
	(function(d, t) {
	var g = d.createElement(t),
		s = d.getElementsByTagName(t)[0];
	g.async = true;
	g.src = ('https:' == location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
	s.parentNode.insertBefore(g, s);
	})(document, 'script');
	</script>
<?php endif; ?>