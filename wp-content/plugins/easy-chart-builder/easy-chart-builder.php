<?php
/*
Plugin Name: Easy Chart Builder
Version: 1.2
Plugin URI: http://www.dyerware.com/main/easy-chart-builder
Description: Creates a chart directly in your post or page via shortcut.  Manages sizing of chart to support wptouch and other mobile themes.
Author: dyerware
Author URI: http://www.dyerware.com
*/
/*  Copyright © 2009, 2010, 2011  dyerware
    Support: http://www.dyerware.com/forum

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
  
class wpEasyCharts
{
    private $chartNum = 0;
    private $installScripts = false;
    
    var $numColorGroups = 12;
    
	// Database Settings
    var $DEF_TYPE = "horizbar";
	var $DEF_WIDTH = 200;
	var $DEF_HEIGHT = 200;
	var $DEF_TITLE = "";
	var $DEF_MARKERCOLOR = "FFFF00";
	var $DEF_CHARTCOLOR = "FFFFFF";
	var $DEF_CHARTFADECOLOR = "DDDDDD";
	var $DEF_TABLECSS = "hentry easyChartDataTable";
	var $DEF_IMGSTYLE = "text-align:center;float:center;";
	var $DEF_WATERMARKCOLOR = "A0BAE9";
	var $DEF_CURRENCY = "";
	var $DEF_PRECISION = "";
	var $DEF_HIDECHART = false;
	var $DEF_IMAGEALT = "dyerware.com";
	var $DEF_IMAGETITLE = "";
	var $DEF_COLORS_1 = "0070C0";
	var $DEF_COLORS_2 = "FFFF00";
	var $DEF_COLORS_3 = "FF0000";
	var $DEF_COLORS_4 = "00CC00";
	var $DEF_COLORS_5 = "A3A3A3";
	var $DEF_COLORS_6 = "007070";
	var $DEF_COLORS_7 = "00FFFF";
	var $DEF_COLORS_8 = "CC7000";
	var $DEF_COLORS_9 = "00CC70";
	var $DEF_COLORS_10 = "CC0070";
	var $DEF_COLORS_11 = "7000CC";
	var $DEF_COLORS_12 = "A370CC";
	var $DEF_GRID = false;
	var $DEF_DATATABLE_TITLE = "Show/Hide Table Data";
	var $DEF_TABLEALWAYSOPEN = false;
	var $DEF_AXISOPTS = "both";
	
	var $op; 
	    
	public function __construct()
    {   
        $jsDir = plugins_url ( plugin_basename ( dirname ( __FILE__ ) ) ) . '/js/';
        wp_register_script('wpEasyCharts', "{$jsDir}easy-chart-builder.js", false); 
        
        $this->init_options_map();
        $this->load_options();
 
        if (is_admin()) 
        {
            add_action('admin_head', array(&$this,'add_admin_files'));
        	add_action('admin_menu', array(&$this, 'add_admin_menu'));
        }        
    }
    
    function CTXID() 
    { 
        return get_class($this); 
    }   

   function add_admin_files() 
    {	
        $plgDir = plugins_url ( plugin_basename ( dirname ( __FILE__ ) ) ); 
        	
    	if ( isset( $_GET['page'] ) && $_GET['page'] == 'easy-chart-builder/easy-chart-builder.php' ) 
    	{
    	    echo "<link rel='stylesheet' media='screen' type='text/css' href='" . $plgDir . "/colorpicker/code/colorpicker.css' />\n";
    		echo "<script type='text/javascript' src='" . $plgDir . "/colorpicker/code/colorpicker.js'></script>\n";
  
	
        $cmt = '// <![CDATA[';
        $cmte = '// ]]>';
        echo '
<script type="text/javascript">
' . $cmt . '
	jQuery(document).ready(function($){       		
        jQuery(".dyerware-color").each(function(index, obj){
			$(obj).ColorPicker({
			 	onShow: function (colpkr) {
              		$(colpkr).fadeIn(200);
              		return false;
	            },
            	onHide: function (colpkr) {
            		$(colpkr).fadeOut(200);
            		return false;
            	},
            	onChange: function (hsb, hex, rgb) {
            		jQuery(obj).css("backgroundColor", "#" + hex);
            		jQuery(obj).css("color", (hsb.b < 50 || (hsb.s > 75 && hsb.b < 75)) ? "#fff" : "#000");
            		jQuery(obj).val(hex.toUpperCase()); 
            	},
        		onSubmit: function(hsb, hex, rgb, el) 
        		  { jQuery(obj).css("backgroundColor", "#" + hex);
        		    jQuery(obj).css("color", (hsb.b < 50 || (hsb.s > 75 && hsb.b < 75)) ? "#fff" : "#000");
        		    jQuery(el).val(hex.toUpperCase()); 
        		    jQuery(el).ColorPickerHide(); },
        		onBeforeShow: function () 
        		  { jQuery(this).ColorPickerSetColor( jQuery(this).attr("value") ); }
        		});
		}); 	     	
	});	
' . $cmte . '
</script>';	
    	}	       	
    }
    
	function addCSS() 
	{
        if (true) //$this->installScripts)
	    {  
		  echo '<link type="text/css" rel="stylesheet" href="' . plugins_url ( plugin_basename ( dirname ( __FILE__ ) ) ) .'/easy-chart-builder.css" />';
	    }
	}
	   
	public function output_scripts ()
    {
        if ($this->installScripts)
        {
            wp_enqueue_script('wpEasyCharts'); 
        }   
    }
 
 	function add_admin_menu() 
 	{
		$title = 'Easy Chart Builder';
		add_options_page($title, $title, 10, __FILE__, array(&$this, 'handle_options'));
	}

	function init_options_map() 
	{
		$opnames = array(
			'DEF_TYPE', 'DEF_WIDTH', 'DEF_HEIGHT', 'DEF_TITLE', 'DEF_MARKERCOLOR', 'DEF_CHARTCOLOR', 'DEF_CHARTFADECOLOR', 'DEF_TABLECSS', 'DEF_IMGSTYLE', 'DEF_WATERMARKCOLOR', 'DEF_CURRENCY', 'DEF_PRECISION','DEF_HIDECHART', 'DEF_IMAGEALT', 'DEF_IMAGETITLE', 'DEF_COLORS_1', 
'DEF_COLORS_2', 'DEF_COLORS_3', 'DEF_COLORS_4', 'DEF_COLORS_5', 'DEF_COLORS_6', 'DEF_COLORS_7', 'DEF_COLORS_8', 'DEF_COLORS_9', 'DEF_COLORS_10', 'DEF_COLORS_11', 'DEF_COLORS_12', 'DEF_GRID', 'DEF_DATATABLE_TITLE', 'DEF_TABLEALWAYSOPEN', 'DEF_AXISOPTS',
		);
		$this->op = (object) array();
		foreach ($opnames as $name)
			$this->op->$name = &$this->$name;
	}
	
	function load_options() 
	{
		$context = $this->CTXID();
		$options = $this->op;
		$saved = get_option($context);
		if ($saved) foreach ( (array) $options as $key => $val ) 
		{
			if (!isset($saved->$key)) continue;
			$this->assign_to($options->$key, $saved->$key);
		}
		// Backward compatibility hack, to be removed in a future version
		//$this->migrateOptions($options, $context);
	}
		
	function handle_options() 
	{
		$actionURL = $_SERVER['REQUEST_URI'];
		$context = $this->CTXID();
		$options = $this->op;
		$updated = false;
		$status = '';
		if ( $_POST['action'] == 'update' ):
			check_admin_referer($context);
			if (isset($_POST['submit'])):
				foreach ($options as $key => $val):
					$bistate = is_bool($val);
					if ($bistate):
						$newval = isset($_POST[$key]);
					else:
						if ( !isset($_POST[$key]) ) continue;
						$newval = trim( $_POST[$key] );
					endif;
					if ( $newval == $val ) continue;
					$this->assign_to($options->$key, $newval);
					$updated = true; $status = 'updated';
				endforeach;
				if ($updated): update_option($context, $options); endif;
			elseif (isset($_POST['reset'])):
				delete_option($context);
				$updated = true; $status = 'reset';
			endif;
		endif;
		include 'easy-chart-builder-settings.php';
	}
	
	private function assign_to(&$var, $value) 
	{
		settype($value, gettype($var));
		$var = $value;
	}
	
 
    private function translate_numerics(&$value, $key) 
    {     
        if ($value == 'false') 
        {
        	$value = false;
        } elseif ($value == 'true') 
        {
            $value = true;
        }       
    }        
            
	public function process_shortcode($atts, $content=null, $code="") 
	{  
	   	$haveIssue = FALSE;
	    $nearKey = "";
	    $nearValue = "";
	    $header = "";

	    if ($this->installScripts == false)
	    {
	       $plgDir = plugins_url ( plugin_basename ( dirname ( __FILE__ ) ) ); 
	       $this->installScripts = true;
	       $header = "<script type='text/javascript' src='" . $plgDir . "/js/easy-chart-builder.js'></script>\n";
	    }
	    
	    if ($atts)
	    {
    	    foreach ($atts as $key => $att)
    	    {
    	       $keyval = (int)$key;
    	       if ($keyval != 0 || strpos($key, "0") === 0)
    	       {
                    $haveIssue = TRUE;
                    $nearKey = $keyval;
                    $nearValue = $att;
                    break;
    	       }
    	    }
	    }
	    	
	    if ($haveIssue === TRUE)
	       return "<p><b>EASY CHART BUILDER SHORTCODE ERROR</b><lu><li>Check for misspelled parameters (case matters)</li><li>Check for new lines (all must reside on one long line)</li><li>Error near [" . $key . "], [" . $att . "]</li></lu><br/>For assistance, please visit <a>http://www.dyerware.com/main/products/easy-chart-builder</a></p>";
	   
	   
	    $colors = "";
        for ($index=1;$index<=$this->numColorGroups;$index++)
        {
            $var = 'DEF_COLORS_' . $index;
            $colors .= $this->$var;
            if ($index < $this->numColorGroups)
            {
                $colors .= ',';
            }
        }
        
        $chartConfig = shortcode_atts( array(
                'type' => $this->DEF_TYPE,
                'width' => $this->DEF_WIDTH,
                'height' => $this->DEF_HEIGHT,
                'title' => $this->DEF_TITLE,
                'minaxis' => '',
                'groupnames' => 'Group 1,Group 2,Group 3',
                'groupcolors' => $colors,
                'valuenames' => 'test1,test2,test3,test4,test5',
                'group1values' => '0,0,0',
                'group2values' => '0,0,0',
                'group3values' => '0,0,0',
                'group4values' => '0,0,0',
                'group5values' => '0,0,0',
                'group6values' => '0,0,0',
                'group7values' => '0,0,0',
                'group8values' => '0,0,0',
                'group9values' => '0,0,0',
                'group10values' => '0,0,0',
                'group11values' => '0,0,0',
                'group12values' => '0,0,0',
                'group1markers' => '',
                'group2markers' => '',
                'group3markers' => '',
                'group4markers' => '',
                'group5markers' => '',
                'group6markers' => '',
                'group7markers' => '',
                'group8markers' => '',
                'group9markers' => '',
                'group10markers' => '',
                'group11markers' => '',
                'group12markers' => '',
                'markercolor' => $this->DEF_MARKERCOLOR,              
                'imagealtattr' => $this->DEF_IMAGEALT,
                'imagetitleattr' => $this->DEF_IMAGETITLE,
                'hidechartdata' => ($this->DEF_HIDECHART == true)?'true':'false',
                'chartcolor' => $this->DEF_CHARTCOLOR,
                'chartfadecolor' => $this->DEF_CHARTFADECOLOR,
                'datatablecss' => $this->DEF_TABLECSS,
                'imgstyle' => $this->DEF_IMGSTYLE,
                'watermark' => '',
                'watermarkvert' => '',
                'watermarkcolor' => $this->DEF_WATERMARKCOLOR,
                'currency' => $this->DEF_CURRENCY,
                'precision' => $this->DEF_PRECISION,
                'grid' => $this->DEF_GRID,
                'axis' => $this->DEF_AXISOPTS)
			    , $atts ); 

	    // Translate strings to numerics
	    array_walk($chartConfig, array($this, 'translate_numerics'));
	   
	    // Work some default SEO stuff for the user
	    if ($chartConfig['imagealtattr'] == '')
	       $chartConfig['imagealtattr'] = $chartConfig['title'];
	    if ($chartConfig['imagealtattr'] == '')
	       $chartConfig['imagealtattr'] = "dyerware";       
	       	       
	    $this->chartNum++;
	    
	    $randomatic = mt_rand(0,0x7fff);
	    $randomatic = $randomatic << 16;
	    global $post;
	    if ($post)
	    {
            $randomatic = $randomatic | 0x8000;            
	    }	    
	    
	    $r = $randomatic | $this->chartNum;
        $chartDiv = 'easyChartDiv' . base_convert($r, 10, 16);	
		
			    
		//$chartDiv = 'easyChartDiv' . $this->chartNum;
		$chartImg = $chartDiv . '_img';
		$tableDiv = $chartDiv . '_data';


		$names = explode(",", $chartConfig['groupnames']);
		foreach ($names as $i => $value) 
		{
			$names[$i] = html_entity_decode($names[$i], ENT_NOQUOTES,'UTF-8');
		}
		$chartConfig['groupnames'] = implode(",", $names);
		
		$names = explode(",", $chartConfig['valuenames']);
		foreach ($names as $i => $value) 
		{
			$names[$i] = html_entity_decode($names[$i], ENT_NOQUOTES,'UTF-8');
		}
		$chartConfig['valuenames'] = implode(",", $names);


		if (function_exists('json_encode')) {
        	$json = json_encode($chartConfig);
        	$chartDiv = json_encode($chartDiv);
        	$chartImg = json_encode($chartImg);
        	$tableDiv = json_encode($tableDiv);
        } else {
			require_once('json_encode.php');
        	$json = Zend_Json_Encoder::encode($chartConfig);
        	$chartDiv = Zend_Json_Encoder::encode($chartDiv);
        	$tableDiv = Zend_Json_Encoder::encode($tableDiv);
        	$chartImg = Zend_Json_Encoder::encode($chartImg);
		}
			
					 	
	    if ($chartConfig['hidechartdata'] == false)
	    {
	    	if ($this->DEF_TABLEALWAYSOPEN == true)
	    	{
	    		$dataShow = "<div class='easyChartBuilder' id="
		        .$tableDiv
		        ." style='text-align:center;display:block;' align='center'></div>";
	    	}
	    	else
	    	{
		        $dataShow = "<br/><br/><INPUT type='button' value='" 
		        .$this->DEF_DATATABLE_TITLE 
		        ."' onclick='wpEasyChartToggle("
		        .$tableDiv
		        .");' style='text-align:center;' align='center' ><br/><div class='easyChartBuilder' id="
		        .$tableDiv
		        ." style='text-align:center;display:none;' align='center'></div>";
	    	}
	    }
	    else
	       $dataShow = "";
		

	
        return <<<ecbCode
{$header}
<div id={$chartDiv} style='width:100%;'  style='text-align:center;' align='center'>
<!-- Easy Chart Builder by dyerware -->
<img id={$chartImg} style='{$chartConfig['imgstyle']}' alt='{$chartConfig['imagealtattr']}' title='{$chartConfig['imagetitleattr']}'  align='center' border='0' />
{$dataShow}
</div>
<script type="text/javascript">
//<![CDATA[
wpEasyChart.wpNewChart({$chartDiv}, {$json});
//]]>
</script>
ecbCode;
   }
   
   
   // This is for support in widgets
   public function do_shortcode($content) 
   {
    	global $shortcode_tags;
    
    	if (empty($shortcode_tags) || !is_array($shortcode_tags))
    		return $content;
    	$pattern = '(.?)\[(easychart)\b(.*?)(?:(\/))?\](?:(.+?)\[\/\2\])?(.?)';
    	return preg_replace_callback('/'.$pattern.'/s', 'do_shortcode_tag', $content);
    }
   
	public function query_install($posts) 
	{
	    $content = '';
        foreach ($posts as $post) {
            $content .= $post->post_content;
        }
        
        $this->installScripts = (bool)preg_match("/\[easychart(.*)\]/U", $content);

        return $posts;
	}  
	
	
    function RGBtoHSB ($rgb)
    {
        sscanf ($rgb, "%02x%02x%02x", $r, $g, $b);
     
        $h = 0;
        $s = 0;
        
        $min = min($r, $g, $b);
        $max = max($r, $g, $b);
        $delta = $max - $min;
        $b = $max;
        if ($max != 0) {
        	
        }
        $s = $max != 0 ? 255 * $delta / $max : 0;
        if ($s != 0) {
        	if ($r == $max) {
        		$h = ($g - $b) / $delta;
        	} else if ($g == $max) {
        		$h = 2 + ($b - $r) / $delta;
        	} else {
        		$h = 4 + ($r - $g) / $delta;
        	}
        } else {
        	$h = -1;
        }
        $h *= 60;
        if ($h < 0) {
        	$h += 360;
        }
        $s *= 100/255;
        $b *= 100/255;
 
        return array($h, $s, $b);
    }
}  

// Instantiate our class
$wpEasyCharts = new wpEasyCharts();

/**
 * Add filters and actions
 */

add_action('wp_head', array($wpEasyCharts, 'addCSS'));
add_action('wp_print_scripts', array($wpEasyCharts, 'output_scripts'));

add_shortcode('easychart',array($wpEasyCharts, 'process_shortcode'));
add_filter('widget_text', array($wpEasyCharts, 'do_shortcode'));
add_filter('the_posts', array($wpEasyCharts, 'query_install'));
?>
