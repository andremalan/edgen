<?php
if ( !defined('ABSPATH') )
	exit('Sorry, you are not allowed to access this page directly.');

$helplinks = array(
	array(
		'text' => 'Tutorial',
		'url' => "http://www.dyerware.com/main/products/easy-chart-builder/easy-chart-builder-plugin-for-wordpress.html",
		'icon' => "http://www.dyerware.com/images/book.png" ),
	array(
		'text' => 'FAQ',
		'url' => "http://www.dyerware.com/main/products/easy-chart-builder/easy-chart-builder-faq.html",
		'icon' => "http://www.dyerware.com/images/help.png" ),
	array(
		'text' => 'Forum',
		'url' => "http://www.dyerware.com/forum",
		'icon' => "http://www.dyerware.com/images/pencil.png" ),		
	array(
		'text' => 'Rate',
		'url' => "http://wordpress.org/extend/plugins/easy-chart-builder",
		'icon' => "http://www.dyerware.com/images/favorites.png" ),
);

$infomercials = array(
	array(
		'text' => 'Easy Spoiler',
		'url' => "http://www.dyerware.com/main/products/easy-spoiler/easy-spoiler-plugin-for-wordpress.html",
		'desc' => 'Styled and full-featured spoiler tag for articles, comments, and widgets', 
		'icon' => "http://www.dyerware.com/images/checkmark.png" ),
	array(
		'text' => 'Easy Review Builder',
		'desc' => 'Create styled star-based review summary boxes',
		'url' => "http://www.dyerware.com/main/products/easy-review-builder/easy-review-builder.html",
		'icon' => "http://www.dyerware.com/images/checkmark.png" ),
	array(
		'text' => 'Gallery and Caption',
		'desc' => 'Upgrade your wordpress galleries and captioned images with animations and effects',
		'url' => "http://www.dyerware.com/main/products/gallery-and-caption/gallery-and-caption-plugin-for-wordpress.html",
		'icon' => "http://www.dyerware.com/images/checkmark.png" ),		
);
?>
<style type="text/css">
	.wp-admin form, .wp-admin div.updated {
		margin-right: 180px
	}
	div.dyerware-adminfobar {
		float: right;
		width: 150px;
		border-left: 1px solid #ccc;
		padding-left: 1em;
		margin-left: 1em;
	}
	.dyerware-adminfobar a {
		text-decoration: none
	}
	.dyerware-adminfobar ul {
		list-style: inside;
		padding: 0;
	}
	.dyerware-adminfobar li {
		margin: .75em 0 .75em 0;
	}
	.dyerware-adminfobar hr {
		color: #ccc
	}
</style>

<div class="dyerware-adminfobar">
	<center>Support</center>
	<hr size="0" />
	<ul>
<?php foreach ($helplinks as $each) : unset($hr) ; extract($each) ?>
		<?php if ($hr) : ?><hr size="0" /><?php endif ?>
		<li style="list-style-image:url(<?php echo $icon ?>)">
		<a target="_blank" href="<?php echo $url ?>" ><?php echo $text ?></a>
		</li>
<?php endforeach ?>
	</ul>

    <div style="height:20px"></div>
	<center>plugins by <strong>dyerware</strong></center>
	<hr size="0" />
	<ul>	
<?php foreach ($infomercials as $each) : unset($hr) ; extract($each) ?>
		<?php if ($hr) : ?><hr size="0" /><?php endif ?>
		<li style="list-style-image:url(<?php echo $icon ?>)">
		<a target="_blank" href="<?php echo $url ?>" ><?php echo $text ?></a>
		</li><?php echo $desc ?>
<?php endforeach ?>
	</ul>
	
	<div style="height:20px"></div>
	<hr size="0" />
	<center><small>
		<!-- License --><?php if (@!$license) $license = 'GPL'; ?>
		<?php include "license.$license.php" ?>
		<!-- /License -->
		
		<p>
	    <a href="http://validator.w3.org/check?uri=referer"><img
	        src="http://www.w3.org/Icons/valid-xhtml10"
	        alt="Valid XHTML 1.0 Transitional" height="31" width="88" /></a>
	  	</p>
	</small></center>
</div>
