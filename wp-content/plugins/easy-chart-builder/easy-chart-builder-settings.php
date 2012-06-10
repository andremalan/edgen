<?php
// This source originated from Hackadellic's TOC plugin
if ( !defined('ABSPATH') )
	exit("Sorry, you are not allowed to access this page directly.");
if ( !isset($this) || !is_a($this, wpEasyCharts) )
	exit("Invalid operation context.");


$groupcolors = array(sizeof($this->numColorGroups));
for ($index=1;$index<=$this->numColorGroups;$index++)
{
    $groupcolors[($index-1)] = (object)array(
    'title' => 'Group ' . $index . ' Color',
    'key' => 'DEF_COLORS_' . $index,
    'help' => 'HTML Color code for Group '.$index,
    'class' => 'dyerware-color'
    );
}
		
	
$sections = array(
	(object) array(
		'title' => 'Chart Attribute Defaults',
		'help' => 'Fill in the desired defaults for the following options.  You can override these within the shortcode itself by specifying them directly.',
		'options' => array(
			(object) array(
				'title' => 'Chart Type',
				'key' => 'DEF_TYPE',
				'pick' => (object)array("horizbar","vertbar","pie","line","horizbarstack","vertbarstack", "horizbaroverlap", "vertbaroverlap", "scatter", "radar"),
				'help' => 'The default chart type (type).  Remember, this is just the default.  You can always specify whatever chart type you want each time you instantiate the shortcode.'),
			(object) array(
				'title' => 'Chart Height',
				'key' => 'DEF_HEIGHT', 
				'help' => 'The default height in pixels for the chart (height).  Keep in mind this is only used to construct the aspect ratio.  The plugin scales the chart to the space available.' ),
			(object) array(
				'title' => 'Chart Width',
				'key' => 'DEF_WIDTH', 
				'help' => 'The default width in pixels for the chart (height).  Keep in mind this is only used to construct the aspect ratio.  The plugin scales the chart to the space available.' ),
			(object) array(
				'title' => 'Chart Title',
				'key' => 'DEF_TITLE', 
				'help' => 'The default title that will appear on top of the chart (title).' ),
			(object) array(
				'title' => 'Chart Grid',
				'key' => 'DEF_GRID', 
				'help' => 'The default setting for showing a grid behind the chart (grid).' ),			
			(object) array(
				'title' => 'Axis Options',
				'key' => 'DEF_AXISOPTS',
				'pick' => (object)array("both","values","names", "none"),
				'help' => 'The default axis to show (axis). The default is both.  If you want just the values, just the names, or neither you can override the default here or in the shortcode'),				
			(object) array(
				'title' => 'Hide Data Table',
				'key' => 'DEF_HIDECHART', 
				'style' => 'max-width: 5em',
				'text' => 'Hide the data table',
				'help' => 'The default option for hiding the data table (hidechartdata).' ),
			(object) array(
				'title' => 'Table Always Open',
				'key' => 'DEF_TABLEALWAYSOPEN', 
				'style' => 'max-width: 5em',
				'text' => 'Table data is always open (if shown)',
				'help' => 'The data table will have no show/hide button.' ),		
			(object) array(
				'title' => 'Data Table Title',
				'key' => 'DEF_DATATABLE_TITLE', 
				'help' => 'The  title that will appear on top of the data table.' ),
		)),			
		
	(object) array(
		'title' => 'Number Attributes',
		'help' => 'These settings change number presentation default behaviors.  You can override these within the shortcode itself by specifying them directly.',
		'options' => array(
			(object) array(
				'title' => 'Currency',
				'key' => 'DEF_CURRENCY',
				'help' => 'Text of a currency symbol image (currency).'),
			(object) array(
				'title' => 'Precision',
				'key' => 'DEF_PRECISION', 
				'help' => 'Decimal places for numeric values (precision).  If empty, your precision of numbers remain as entered.  For currency you may want to assign this to 2.' ),						
		)),						

	(object) array(
		'title' => 'Chart Color Attributes',
		'help' => 'These settings change color defaults.  You can override these within the shortcode itself by specifying them directly.',
		'options' => array(
			(object) array(
				'title' => 'Chart Color',
				'key' => 'DEF_CHARTCOLOR',
				'class' => 'dyerware-color',
				'help' => 'HTML Color code for the chart background color (chartcolor).' ),
			(object) array(
				'title' => 'Chart Fade Color',
				'key' => 'DEF_CHARTFADECOLOR', 
				'class' => 'dyerware-color',
				'help' => 'HTML Color code for the chart background fade color (chartfadecolor).' ),
			(object) array(
				'title' => 'Watermark Color',
				'key' => 'DEF_WATERMARKCOLOR',
				'class' => 'dyerware-color', 
				'help' => 'HTML Color code for watermarks (watermarkcolor).' ),
			(object) array(
				'title' => 'Marker Color',
				'key' => 'DEF_MARKERCOLOR', 
				'class' => 'dyerware-color',
				'help' => 'HTML Color code for markers (markercolor).' ),									
		)),
		
	(object) array(
		'title' => 'Group Color Attributes',
		'help' => 'These settings change default colors for groups.  You can override these within the shortcode itself by specifying the (groupcolors) shortcode directly.',
		'options' => (object)$groupcolors,
		),	
				
	(object) array(
		'title' => 'Styling Attributes',
		'help' => 'These settings change CSS-related default behaviors.  You can override these within the shortcode itself by specifying them directly.',
		'options' => array(
			(object) array(
				'title' => 'Data Table CSS',
				'key' => 'DEF_TABLECSS',
				'help' => 'CSS class attributes for the data table (datatablecss).' ),
			(object) array(
				'title' => 'Chart Image Style',
				'key' => 'DEF_IMGSTYLE', 
				'help' => 'Style for the Chart Image (imgstyle).' ),						
		)),	
				
	(object) array(
		'title' => 'SEO Attributes',
		'help' => 'These settings change SEO default behaviors.  You can override these within the shortcode itself by specifying them directly.',
		'options' => array(
			(object) array(
				'title' => 'Chart ALT Tag',
				'key' => 'DEF_IMAGEALT',
				'help' => 'Text for the ALT tag of the Chart image (imagealtattr).  End-users will likely never see this.' ),
			(object) array(
				'title' => 'Chart TITLE Tag',
				'key' => 'DEF_IMAGETITLE', 
				'help' => 'Text for the TITLE tag of the Chart image (imagetitleattr).  End-users will likely never see this.' ),						
		)),			
	);

?>
<?php // ------------------------------------------------------------------------------------ ?>
<style type="text/css">
<?php
	$R = '3px';
	$sideWidth = '13em';
?>
a.button { display: inline-block; margin: 5px 0 }

dl { padding: 0; margin: 10px 1em 20px 0; background-color: white; border: 1px solid #ddd; }
dt { font-size: 10pt; font-weight: bold; margin: 0; padding: 4px 10px 4px 10px;
	background: #dfdfdf url(<?php echo admin_url('images/gray-grad.png') ?>) repeat-x left top;
}
dd { margin: 0; padding: 10px 20px 10px 20px }
dl {<?php foreach (array('-moz-', '-khtml-', '-webkit-', '') as $pfx) echo " {$pfx}border-radius: $R;" ?> }

dd .caveat { font-weight: bold; color: #C00; text-align: center }

.box { border: 1px solid #ccc; padding: 5px; margin: 5px }
.help { background-color: whitesmoke }

</style>
<?php // ------------------------------------------------------------------------------------ ?>
<div class="wrap">
<div id="icon-options-general" class="icon32"><br /></div>
<h2>Easy Chart Builder by dyerware</h2>

<p><a href="http://itunes.apple.com/us/app/feedhopper-rss-reader/id361881998?mt=8"><img border="0" src="http://www.dyerware.com/images/624x58-Ad.jpg" height="50"></a></p>

<?php
include 'dyerware-adm.php';
$helpicon = 'http://www.dyerware.com/images/inspector.png';
?>

<?php // ------------------------------------------------------------------------------------ ?>
<?php if ($updated) : ?>
<div class="updated fade"><p>Plugin settings <?php echo ($status == 'reset') ? 'reset to default values and deleted from database. If you want to, you can safely remove the plugin now' : 'saved' ?>.</p></div>
<?php endif ?>

<?php // ------------------------------------------------------------------------------------ ?>
<?php if ( $updated && $status == 'reset') : ?>

<p class="submit" align="center">
	<a class="button" href="<?php echo $actionURL ?>">Back To Settings ...</a>
</p>

<?php // ------------------------------------------------------------------------------------ ?>
<?php else: ?>

<form method="post">
	<input type="hidden" name="action" value="update" />
	<?php wp_nonce_field($context); ?>

<?php foreach ($sections as $s) : $snr += 1; $shlpid = "shlp-$snr" ?>
<dl>
	<dt><?php echo $s->title ?><?php 
	if ($s->help) :
		?> <a href="javascript:;" onclick="jQuery('#<?php echo $shlpid ?>').slideToggle('fast')"><img src="<?php
			echo $helpicon ?>" /></a><?php
	endif ?></dt>
	<dd>
<?php if ($s->help) : ?>
	<div id="<?php echo $shlpid ?>" class="hidden help box"><?php echo $s->help ?></div>
<?php endif ?>

		<table class="form-table" style="clear:none">
<?php foreach ($s->options as $o) :
	$key = $o->key;	
	$v = $options->$key; $t = gettype($v);
	$name = ' name="'.$key.'"';
	$class = $o->class ? " class=\"$o->class\"" : "";
	
	$style = $o->style ? " style=\"$o->style;" : 'style="width:100%;';
	
	if ($o->class == 'dyerware-color')
	{
	   $style .= " background-color:#" . $v . ";"; 
	   $hsb = $this->RGBtoHSB($v);
	   
	   if ($hsb[2] < 50 || ($hsb[1] > 75 && $hsb[2] < 75))
	   {
	       $style .= " color:#FFF;";
	   }
	   else
	   {
	       $style .= " color:#000;";
	   }
	}	
	$style .= '"';	
	
	if ($o->pick)
	{ 
          $attr = '<select ' . $name . '>';
          foreach ($o->pick as $item)
    	  {
    	   $attr .= '<option value="' . $item .  '" ' . (($item == $v)?'SELECTED ':'') . $style .'>' . $item . '</option>';
    	  }
    	  $attr .= "</select>";  
	}
	else
	{
    	$type = ' type="' . (is_bool($v) ? 'checkbox' : 'text') . '" ';
    	$value = is_bool($v) ? ($v ? ' checked="checked"' : '') : ' value="'.$v.'"';
    	$attr = '<input ' . $type . $style . $class . $name . $value . '/>';
	}
    
	unset($type, $style, $name, $value, $class);   
	$text = $o->text ? " <span>$o->text</span>" : '';
?>
		<tr>
			<th scope="row"><?php echo $o->title ?></th>
			<td>
				<div style="vertical-align:bottom"><?php echo $attr ?><?php echo $text ?></div>
				<div><em><?php echo $o->help ?></em></div>
			</td>
		</tr>
<?php endforeach ?>
		</table>
	</dd>
</dl>
<?php endforeach ?>

	<p class="submit" align="center">
		<input type="submit" name="submit" value="<?php _e('Save Settings') ?>"  title="This will store the settings to the database." />
		<input type="submit" name="reset" value="<?php _e('Reset Settings') ?>" title="This will remove the settings from the database, giving you the factory defaults"/>
	</p>
</form>

<?php endif // if ($status) ... ?>
</div>
