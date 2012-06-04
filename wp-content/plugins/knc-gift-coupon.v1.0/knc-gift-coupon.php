<?php
/*
Plugin Name: KNC Gift Coupon Multiuse
Plugin URI: http://www.keysnclicks.com/webrnd/knc-gift-coupon/
Description: A gift coupon add-on for WP e-Commerce v3.7.6.7+ , a shopping cart plugin by <a href="http://www.getshopped.org">Getshopped</a>.
Version: 1.0.0
Author: Amit Khanna
Author URI: http://www.keysnclicks.com
*/

/****************************************************************************************************************  
 	
 	Copyright 2010-2011 Amit Khanna (email: amitkhanna15 at gmail.com)

	The Licensee may not sell,  resell, charge or accept payment for this plugin.  Licensee  may use the  plug-in
	for commercial ( profit ) and/or non-commercial ( non-profit ) applications. Licensee may  not  distribute or 
	redistribute the plug-in as is or by altering the plug-in or any part of it without prior  written permission
	from Amit Khanna. Altering includes but is  not limited to substitution,  removal or addition of any  file or
	part of files included in the package.

	This  program  is distributed in the hope that it will be useful,  but WITHOUT ANY WARRANTY; without even the
	implied  warranty of MERCHANTABILITY or FITNESS  FOR A PARTICULAR PURPOSE.  See  the license for more details.
	
	You should have received  a  copy of  the  License along with this program;  if not,  write to the the author,
	Amit Khanna at amitkhanna15@gmail.com.
	
*****************************************************************************************************************/

/*
 *	CONSTANTS
 */
include_once('knc-gift-coupon-constants.php');
knc_gc_core_const();
knc_gc_error_const();

/*
 *	GLOBALS
 */
$knc_gc_dmy = array("day" => "Day(s)", "month" => "Month(s)", "year" => "Year(s)",);
$_SESSION['knc_user_disc_status'] = 0;
global	$knc_db_version; 
		$knc_db_version = '1.0';

/*
 *	MAIN PLUG-IN CLASS
 */
if (!class_exists("knc_gift_coupon")) {
	class knc_gift_coupon 
	{	
		// VARIABLE DECLARATIOINS
		// var $knc_gc_admin_table_name = "knc_gift_coupon_options";
		
		var $ourGateway = array('wpsc_merchant_paypal_standard_knc_mu');
		
		// CONSTRUCTOR FUNCTION
		function knc_gift_coupon(){ 
		}
		
		// GET STORED SETTINGS FOR ADMIN PAGE
		function get_admin_options(){
	
			global $wpdb;
			
			$row = $wpdb->get_row( $wpdb->prepare( "SELECT admin_options FROM ". KNC_GC_TABLE_ADMIN_OPTIONS ) );
			$knc_gc_options = unserialize($row->admin_options);
			
			// if found previously stored data then use that (basically re-update that)
			if (!empty($knc_gc_options)) {
				foreach ($knc_gc_options as $key => $option)
					$knc_gc_admin_options[$key] = $option;
			}	

			return $knc_gc_admin_options;
		}
		

		/*
		 *	Admin Page
		 */
		function options_page() {
			
			global $wpdb, $knc_gc_dmy;
			
			$_POST      = array_map( 'stripslashes_deep', $_POST );
			$_GET       = array_map( 'stripslashes_deep', $_GET );
			$_COOKIE    = array_map( 'stripslashes_deep', $_COOKIE );
			$_REQUEST   = array_map( 'stripslashes_deep', $_REQUEST );
			
			$knc_gc_options = $this->get_admin_options();
			
			if (isset($_POST['update_knc_gc_settings'])) {

				if (isset($_POST['knc_gc_name'])) {
					$knc_gc_options['coupon_details']['coupon_name'] = (array)$_POST['knc_gc_name'];
					$knc_gc_options['coupon_details']['coupon_validity'] = (array)$_POST['knc_gc_validity'];
					$knc_gc_options['coupon_details']['coupon_dmy'] = (array)$_POST['knc_gc_dmy'];
				}
				
				if (isset($_POST['content'])) {
					$knc_gc_options['mail_from'] = $_POST['mail_from'];
					$knc_gc_options['mail_subject'] = $_POST['mail_subject'];
					$knc_gc_options['mail_message'] = $_POST['content'];
				}
				
				if (isset($_POST['mail_message_txt'])) {
					$knc_gc_options['mail_message_txt'] = $_POST['mail_message_txt'];
				}
				
				if (isset($_POST['test_mode'])) {
					if($_POST['test_mode']=='on'){
						$knc_gc_options['test_mode'] = 'true';
					}else{
						$knc_gc_options['test_mode'] = 'false';
					}
				}else{
					$knc_gc_options['test_mode'] = 'false';
				}
				
				if (isset($_POST['test_mail'])) {
					$knc_gc_options['test_mail'] = $_POST['test_mail'];
				}
				
				if (isset($_POST['knc_gc_use_coupon_gateway'])) {
					
					$knc_gc_options['use_coupon_gateway'] = $_POST['knc_gc_use_coupon_gateway'];
					
					if($_POST['knc_gc_use_coupon_gateway']=='true'){
						if(get_option('custom_gateway_options') != $knc_gc_options['our_gateway']){
							$knc_gc_options['their_gateways'] = (array)get_option('custom_gateway_options');
						}
						update_option('custom_gateway_options', (array)$knc_gc_options['our_gateway']);
						
						// KNC-PAYPAL-GATEWAY PATH
						$file = realpath(str_replace('//','/',dirname(__FILE__).'/merchant/paypal-standard.merchant.knc.mu.php'));
						$newfile = realpath(str_replace('//','/',dirname(__FILE__).'/../wp-e-commerce/wpsc-merchants'));
						$newfile .= '/paypal-standard.merchant.knc.mu.php';
						
						// COPY KNC-PAYPAL-GATEWAY TO WP-ECOMMERCE PLUGIN'S MERCHANT DIR 
						if(!file_exists($newfile)){
							$copyResult = $this->smart_copy($file,$newfile);
							
							if($copyResult=='1'){
								$copyMessage = "<p style='color:#4d4d4d;'>Knc coupon processing payment gateway successfully copied to WP-eCommerce > Merchants directory</p>";
							}else{
								$copyMessage = "<p style='color:#ff0000;'><strong>ERROR:</strong> Knc payment gateway move failed: Either wp-e-commerce plugin is not installed or is not in the plugins folder.<br />Please manually copy <strong>paypal-standard.merchant.knc.php</strong> from <strong>knc-gift-coupon/merchant</strong> directory to <strong>wp-e-commerce/merchants</strong> directory.</p>";
							}
						}
					}else if($_POST['knc_gc_use_coupon_gateway']=='false'){
						if(get_option('custom_gateway_options') == $knc_gc_options['our_gateway']){
							update_option('custom_gateway_options', (array)$knc_gc_options['their_gateways']);
						}
					}
				}
				
				//update_option($this->knc_gc_admin_table_name, $knc_gc_options);
				// UPDATE ADMIN OPTIONS IN DATABASE	
				$knc_result = $wpdb->update( KNC_GC_TABLE_ADMIN_OPTIONS, array('admin_options' => serialize($knc_gc_options) ), array( 'blog_id' => 0 ) );
							
				?>
				<div class="updated"><p><strong><?php _e("Settings Updated.", 'knc_gift_coupon');?></strong></p>
				<?php _e($copyMessage, 'knc_gift_coupon');  ?>
				</div>
				<?php
			} ?>
			
			<div class="wrap">
				<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
				<h2>KNC Gift Coupon Multiuse</h2>
				<br/>
				<!-- COUPON NAME -->
				<input title='Add product names which will be used as coupons/vouchers' class='button' type='submit' onclick='return add_form_field();' value='<?php echo __('Add Coupons', 'knc_gift_coupon');?>'></input><br/><br/>
				<table id="coupon_name_list" class="widefat page fixed"  cellspacing="0">
					<thead>
						<tr>
							<?php 
								$columns = array(
									'cName' => 'Coupon Name',
									'cValid' => 'Validity',
									'cDate' => '',
									'trash' => 'Remove'
								);
								register_column_headers("knc-coupon-list", $columns); 
								print_column_headers('knc-coupon-list'); 
							?>
						</tr>
					</thead>
					
					<tbody id="coupon_name_list_body">

					<?php
						
						$couponName =  $knc_gc_options['coupon_details']['coupon_name'];
						$couponDmy 	=  $knc_gc_options['coupon_details']['coupon_dmy'];
						$mailFrom 	=  $knc_gc_options['mail_from'];
						$mailSub 	=  $knc_gc_options['mail_subject'];
						$testMode	=  $knc_gc_options['test_mode'];
						$testMail	=  $knc_gc_options['test_mail'];
						
						
						foreach($couponName as $key => $value){

						$couponValdity =  $knc_gc_options['coupon_details']['coupon_validity'][$key];
						
							echo "<tr id='form_id_" . $key . "' class='coupon_fields'>\n\r";
							echo "<td class='namecol'><input type='text' name='knc_gc_name[" . $key . "]' value='". $value ."' /></td>\n\r";
							echo "<td class='valcol'><input type='text' name='knc_gc_validity[" . $key . "]' value='". $couponValdity ."' /></td>\n\r";

							echo "<td class='dmycol'>";
								echo "<select name='knc_gc_dmy[". $key ."]'>";
								
								foreach($knc_gc_dmy as $k => $dmy){
									$selected = '';
									if($couponDmy[$key] === $k) {
										$selected = "selected='selected'";
									}
									echo "<option value='".$k."'". $selected ." >".__($dmy, 'knc_gift_coupon')."</option>";
								}
								echo "</select>";
							echo "</td>\n\r";

							echo "<td class='trash_td'><a class='image_link' href='#' onclick='return remove_new_form_field(\"form_id_". $key ."\");'><img src='" . KNC_GC_URL ."/images/trash.gif' alt='trash_can' title='delete' /></a></td>\n\r";
							echo "</tr>";
							//echo($key ."-".$value);
						}
						?>
					</tbody> 
				</table>
				<br />
				<!-- MAIL TEMPLATE -->
				<h3>HTML email template</h3>
				
				From:&nbsp;&nbsp;&nbsp;&nbsp;
				<input type="text" style="width:350px;" name="mail_from" id="mail_from" value="<?php _e($mailFrom, 'knc_gift_coupon'); ?>" />
				<br />
				Subject:
				<input type="text" style="width:350px;" name="mail_subject" id="mail_subject" value="<?php _e($mailSub, 'knc_gift_coupon'); ?>" /><br />
				<div style="margin-top:5px; border-top:1px solid #e2e2e2; width:700px;">&nbsp;</div>
				<div class="tinyMceStyles">
					<div id="poststuff"> <?php 
							$message = $knc_gc_options['mail_message'];
							the_editor($message); 
						?>
					</div>
					<br /><br />
					Use the following tags in email templates ( html-above, text-below ) to substitue with the related data in email sent to the buyer.<br/> 
					<code>{COUPON CODES} {FIRST NAME} {LAST NAME} {SITE NAME}</code>
				<br/> 
				<br/> 
				<h3>Text email template</h3>
				<textarea name="mail_message_txt"><?php _e($knc_gc_options['mail_message_txt'], 'knc_gift_coupon'); ?></textarea>	
				</div>
				<br />
				
				<!-- NEW GATEWAY -->
				<h3>Use KNC payment gateway</h3>
				<p>Select "Yes" to use knc payment gateway for coupon processing.</p>
				<label for="knc_gc_use_coupon_gateway_yes"><input type="radio" id="knc_gc_use_coupon_gateway_yes" name="knc_gc_use_coupon_gateway" value="true"  <?php if ($knc_gc_options['use_coupon_gateway'] == "true")  { _e('checked="checked"', 'knc_gift_coupon'); }?> /> Yes</label>
				<label for="knc_gc_use_coupon_gateway_no"> <input type="radio" id="knc_gc_use_coupon_gateway_no"  name="knc_gc_use_coupon_gateway" value="false" <?php if ($knc_gc_options['use_coupon_gateway'] == "false") { _e('checked="checked"', 'knc_gift_coupon'); }?>/> No</label>
				
				<!-- TEST MODE -->
				<h3>Use test mode</h3>
				<label for="test_mode"><input id="test_mode" onClick="document.getElementById('test_mail').disabled=!this.checked;" type="checkbox" name="test_mode" <?php if ($knc_gc_options['test_mode'] == "true")  { _e('checked="checked"', 'knc_gift_coupon'); }?>/> Test mode</label>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Email: <input type="text" style="width:250px;" name="test_mail" id="test_mail" <?php if ($knc_gc_options['test_mode'] == "false")  { _e('disabled="true"', 'knc_gift_coupon'); }?> value="<?php _e($testMail, 'knc_gift_coupon'); ?>" /><br />
				
				<!-- SUBMIT BUTTON -->
				<div class="submit">
				<input class="button-primary" type="submit" name="update_knc_gc_settings" value="<?php _e('Save Settings', 'knc_gift_coupon') ?>" />
				</div>
				</form>
			</div>
			<?php
		}
		
		// Elaborate copy paste function - only partly used at the moment
		function smart_copy($source, $dest, $options=array('folderPermission'=>0755,'filePermission'=>0644))
		{
			$result=false;
		   
			if (is_file($source)) {
				if ($dest[strlen($dest)-1]=='/') {
					if (!file_exists($dest)) {
						cmfcDirectory::makeAll($dest,$options['folderPermission'],true);
					}
					$__dest=$dest."/".basename($source);
				} else {
					$__dest=$dest;
				}
				@$result=copy($source, $__dest);
				@chmod($__dest,$options['filePermission']);
			   
			} elseif(is_dir($source)) {
				if ($dest[strlen($dest)-1]=='/') {
					if ($source[strlen($source)-1]=='/') {
						//Copy only contents
					} else {
						//Change parent itself and its contents
						$dest=$dest.basename($source);
						@mkdir($dest);
						chmod($dest,$options['filePermission']);
					}
				} else {
					if ($source[strlen($source)-1]=='/') {
						//Copy parent directory with new name and all its content
						@mkdir($dest,$options['folderPermission']);
						chmod($dest,$options['filePermission']);
					} else {
						//Copy parent directory with new name and all its content
						@mkdir($dest,$options['folderPermission']);
						chmod($dest,$options['filePermission']);
					}
				}

				$dirHandle=opendir($source);
				while($file=readdir($dirHandle))
				{
					if($file!="." && $file!="..")
					{
						 if(!is_dir($source."/".$file)) {
							$__dest=$dest."/".$file;
						} else {
							$__dest=$dest."/".$file;
						}
						//echo "$source/$file ||| $__dest<br />";
						$result=smart_copy($source."/".$file, $__dest, $options);
					}
				}
				closedir($dirHandle);
			   
			} else {
				$result=false;
			}
			return $result;
		} 

		/*
		 *	Loads all necessary code to properly show rich text editor
		 */
		function show_tiny_mce() {
			wp_enqueue_script( 'common' );
			wp_enqueue_script( 'jquery-color' );
			wp_print_scripts('editor');
			if (function_exists('add_thickbox')) add_thickbox();
			wp_print_scripts('media-upload');
			if (function_exists('wp_tiny_mce')) wp_tiny_mce();
			wp_admin_css();
			wp_enqueue_script('utils');
			do_action("admin_print_styles-post-php");
			do_action('admin_print_styles');
		}
		
		/*
		 *  Callback function for ob_start called from remove_wpsc_coupon_form
		 */
		function remove_wpsc_coupon_form_callback(){
					
			// remove wpsc coupon form
			$buffer_content = ob_get_contents();
			
			// logic 1 - removes wpsc coupon form
			$new_content = preg_replace('%<tr class="wpsc_coupon_row wpsc_coupon_error_row">(.*?)</tr>%s', '', $buffer_content);
			
			// logic 2 - removes wpsc generated coupon errors
			$new_content = preg_replace('%<tr class="wpsc_coupon_row">(.*?)</tr>%s', '', $new_content);
			
			/* add knc coupon form - does nothing at the moment - but have kept it for later use - JUST INCASE
			 function replace_function() {
    			return '[knc-coupon-form]' . ' </br><p class="wpsc_cost_before"></p>';
			}$new_content = preg_replace_callback('%<p class="wpsc_cost_before">(.*?)</p>%s', 'replace_function', $new_content);
			*/
			return do_shortcode($new_content);
		}
		
		/*
		 *  Removes wp-ecommerce coupon form
		 */
		function remove_wpsc_coupon_form($content){

			if(!wpsc_have_cart_items()){
				knc_gc_reset_discount_value();
			}
			
			//if (is_page('checkout')) 
			//{
				
				ob_start(array($this ,'remove_wpsc_coupon_form_callback'));
				
			//}
			return $content;
		}
	}// class end
} // class if end


/*
 *	INITIALIZE MAIN CLASS 
 */ 
if (class_exists("knc_gift_coupon")) {
	$knc_gc_class = new knc_gift_coupon();
}

/*
 *	Frontend gift coupon form
 */
function knc_process_coupons() {

	global $wpdb, $wpsc_cart;


	$knc_current_page_url = "http://" . $_SERVER['HTTP_HOST']  . $_SERVER['REQUEST_URI'];
	
	// check disc values on page reload	
	// if discount is more than cart subtotal then make discount = subtotal
	if($_SESSION['knc_disc_value'] > $wpsc_cart->calculate_subtotal()){
		
		$_SESSION['knc_disc_value'] = $wpsc_cart->calculate_subtotal();
		$wpsc_cart->coupons_amount = $wpsc_cart->calculate_subtotal();
		
	}else{
		// else re-apply user fed discount
		$wpsc_cart->coupons_amount = $_SESSION['knc_disc_value'];
	}
	
	
	//if (is_page('checkout')) 
	//{
		if(wpsc_have_cart_items()) 
		{
			knc_gc_set_coupon();
			knc_gc_check_apply_discount();
			?>
			<script type="text/javascript">
				jQuery(document).ready(function(){
				 	jQuery("#knc_gc_cd_coupon_details_btn").click(function(){
						data = 
						{ 
							beforeSend: function() { jQuery("#knc_gc_cd_loading").fadeIn('slow'); } //,
							//success: function(html){ jQuery("#knc_gc_cd_loading").fadeOut('slow'); }
						}
						jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>', data, function(){});
					});
				});	
			</script>
			<div id="knc_gift_coupon_wrap">
				<form action="<?php echo $knc_current_page_url; ?>" method='post' id='knc_gc_cd_form'>
					Enter your gift card code: 
					<input type='text' name="knc_gc_cd_coupon_code" value="<?php echo $_SESSION['coupon_numbers']; ?>" />
					<input type="hidden" name="knc_update_coupon_code" value="true" />
					<button type="submit" id="knc_gc_cd_coupon_details_btn"><?php _e("Check Gift Card Details");?></button>	
					<div id='knc_gc_cd_loading'>Loading Details</div>	
				</form>
				<?php knc_gc_fetch_coupon_details(); ?>
				<div style="display:<?php echo $_SESSION['knc_coupon_error_display']; ?>;" id="knc_invalid_coupon_err"><?php echo $_SESSION['knc_coupon_error']; ?></div>
				<div id="knc_query_result">
					<?php knc_gc_apply_discount(); ?>
				</div>						
					<?php 
					knc_show_apply_discount_form();
					?>
			</div>
			<?php 
		}
	//}
}


/*
 *	Shows discount application form on the basis 
 *	of type of discount a coupon provides
 */
function knc_show_apply_discount_form(){
	
	$discount_type = $_SESSION['discount_form_type'];
	if($_SESSION['coupon_status'] == 'active'){
		if($discount_type=='percent'){
			
			// show % percent discount application form
		?>
		<form style="display:<?php echo $_SESSION['knc_disc_form_display']; ?>;" id="knc_gc_apply_discount" method="post" action="<?php echo $knc_current_page_url; ?>">
			<div class="knc_apply_disc_btn">
				<input type="hidden" name="knc_update_percent_disc_val" value="true" />
				<input type="submit" id="knc_gc_apply_disc_btn" value="<?php _e('Apply Gift Card', 'knc_gift_coupon');?>" />
			</div>
		</form>
		<?php 
		}else{
			
			// show regular discount application form
		?>
		<form style="display:<?php echo $_SESSION['knc_disc_form_display']; ?>;" id="knc_gc_apply_discount" method="post" action="<?php echo $knc_current_page_url; ?>">
			Enter discount amount for this transaction:
			<input type="text" name="knc_gc_apply_disc_val" id="knc_gc_apply_disc_val" value="<?php echo $_SESSION['knc_disc_value']; ?>" />
			<input type="hidden" name="knc_update_disc_val" value="true" />
			<div style="display:<?php echo $_SESSION['knc_disc_error_display']; ?>;" id="knc_invalid_disc_err"><?php echo $_SESSION['knc_disc_error']; ?>
				<?php knc_gc_apply_discount(); ?>
			</div>
			<div class="knc_apply_disc_btn">
				<input type="submit" id="knc_gc_apply_disc_btn" value="<?php _e('Apply Gift Card', 'knc_gift_coupon');?>" />
			</div>
		</form>
		<?php 	
		}
	}
}
		
		
/*
 *	DYNAMIC JAVASCRIPT USING PHP VARS
 */
function knc_gc_admin_dynamic_js() { 
 	
	global $knc_gc_dmy; 
	foreach($knc_gc_dmy as $tOptions) {
		$dmy_options .= "<option value='".$tOptions."'>".__($tOptions, 'knc_gift_coupon')."</option>";
	}
	
	echo "<script language=\"JavaScript\">\n";
	echo "<!-- hide from older browsers\n";
	echo "var KNC_GC_URL = '". KNC_GC_URL ."';\n\r";
	echo "var HTML_FORM_FIELD_TYPES =\" ".$dmy_options."; \" \n\r"; 
	echo "// -->\n";
	echo "</script>\n";
}


/*
 *	INCLUDE JAVASCRIPTS
 */
function knc_gc_enqueue_scripts(){
	
	$jsPath = WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__)).'js/knc-admin.js';
	if (function_exists('wp_enqueue_script')) {
		wp_enqueue_script('knc-admin-js', $jsPath, array('jquery','jquery-ui-core'), '1.0');
	}
}


/*
 *	INCLUDE STYLESHEET
 */
function knc_gc_stylesheet() {
	$myStyleUrl = WP_PLUGIN_URL .'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__)).'css/knc-styles.css';
	$myStyleFile = WP_PLUGIN_DIR .'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__)).'css/knc-styles.css';
	if ( file_exists($myStyleFile) ) {
		wp_register_style('myStyleSheets', $myStyleUrl);
		wp_enqueue_style( 'myStyleSheets');
	}
}


/*
 *	DISPLAY COUPON NUMBER AGAIN IN THE COUPON FIELD ON PAGE RELOAD
 */
function knc_gc_set_coupon( ) {
	
	global $wpdb, $wpsc_coupons;

	if (isset($_POST['knc_gc_cd_coupon_code'])){
		$coupon = $wpdb->escape( $_POST['knc_gc_cd_coupon_code'] );
		$_SESSION['coupon_numbers'] = $coupon;
		$wpsc_coupons = new wpsc_coupons( $coupon );
	}
}


/*
 *	GET COUPON DETAILS FROM DATABASE ON THE BASIS OF USER FED COUPON NUMBER
 */
 function knc_gc_fetch_coupon_details(){

	global $wpdb, $wpsc_cart;
	$_SESSION['knc_coupon_error_display'] = 'none';
	$_SESSION['knc_coupon_error'] = '' ;
	
	// get user fed coupon code
	$coupon_code = $_SESSION["coupon_numbers"];
	
	// if user provided some coupon cupon_code 
	if($coupon_code!=''){
		
		// if user enters a different coupon code 
		// check it with previously entered code
		// reset discount field if codes doesnt match
		if($coupon_code!=$_SESSION['current_coupon']){
			knc_gc_reset_discount_value();
		}
	
		// get details of the provided coupon code from the wpsc database
		$wpsc_coupon_details = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_COUPON_CODES."` WHERE coupon_code='$coupon_code' LIMIT 1");
		
		// if user fed coupon code matchs with the code in DB
		if($wpsc_coupon_details!=null){
			// store it temporarily
			// this will be used if user enters a different coupon code
			$_SESSION['current_coupon'] = $coupon_code ;			
		}
		
		// get details from knc database as well for coupon code cross checking
		$knc_coupon_details = $wpdb->get_row("SELECT * FROM `".KNC_GC_TABLE_COUPON_CODES."` WHERE coupon_code='$coupon_code' LIMIT 1");
		
		// get coupon cupon_code from wpsc db
		$wpsc_coupon_code = $wpsc_coupon_details->coupon_code;
		$wpsc_coupon_value = $wpsc_coupon_details->value;
		
		$wpsc_coupon_is_active = $wpsc_coupon_details->active;
		$knc_iu = 'is-used';
		$wpsc_coupon_is_used = $wpsc_coupon_details->$knc_iu;
		
		// had to put this in a separate variable as there is a "- hyphen" in place of "_ underscore"
		$knc_ip = 'is-percentage';
		$wpsc_coupon_is_percent = $wpsc_coupon_details->$knc_ip;
		

		// get coupon code from knc db
		$knc_coupon_code = $knc_coupon_details->coupon_code;
		
		//echo($coupon_code);
		// if there is some coupon code in the wpsc db
		
		$knc_result = strcmp($wpsc_coupon_details->coupon_code, $coupon_code);

		if($knc_result == 0){
			
			// check if coupon code is not already in knc db 
			if($wpsc_coupon_code != $knc_coupon_code){
				// add USER fed COUPON CODE to knc db for later use
				$rows_affected = $wpdb->insert( KNC_GC_TABLE_COUPON_CODES , array('coupon_code' => $wpsc_coupon_code, 'coupon_value' => $wpsc_coupon_value,'amount_left' => $wpsc_coupon_value ) );
			}
			
			// get details from knc database again for updated output
			$knc_coupon_details = $wpdb->get_row("SELECT * FROM `".KNC_GC_TABLE_COUPON_CODES."` WHERE coupon_code='$coupon_code'");
			
			// get amount left
			$knc_amount_left = $knc_coupon_details->amount_left;
			
			// check when was coupon last used
			if( $knc_coupon_details->last_used == '0000-00-00 00:00:00' ){ 
				$knc_last_used = "Never" ;
			}else {
				$knc_last_used = substr($knc_coupon_details->last_used, 0, -8);
				
			}
			
		
			// check if coupon is ACTIVE, USED, DEACTIVATED or EXPIRED
			if($wpsc_coupon_is_active == '1' && $wpsc_coupon_is_used == '0'){
				$knc_coupon_status = '<span style="color:green;">Active</span>';
				$_SESSION['coupon_status'] = 'active';
			}
			
			if($wpsc_coupon_is_active == '0' && $wpsc_coupon_is_used == '1' || $knc_amount_left == '0'){
				$knc_coupon_status = '<span style="color:red;">Used</span>';
				$_SESSION['coupon_status'] = 'inactive';
			}
			
			if($wpsc_coupon_is_active == '1' && $wpsc_coupon_is_used == '1'){
				$knc_coupon_status = '<span style="color:orange;">Reactivated</span>';
				$_SESSION['coupon_status'] = 'active';
			}
			
			if($wpsc_coupon_is_active == '0' && $wpsc_coupon_is_used == '0'){
				$knc_coupon_status = '<span style="color:red;">Deactivated</span>';
				$_SESSION['coupon_status'] = 'inactive';
			}
			
			// check expiry date
			$knc_now = strtotime(date("Y-m-d H:i:s")); 
			$knc_coupon_expiry = strtotime($wpsc_coupon_details->expiry);
			if((int)$knc_coupon_expiry < (int)$knc_now){
				$knc_coupon_status = '<span style="color:red;">Expired</span>';
				$_SESSION['coupon_status'] = 'inactive';
			}
			
			// check if coupon applies a % PERCENTAGE discount
			if($wpsc_coupon_is_percent=='1'){
				
				$_SESSION['discount_form_type'] = 'percent';
				
				$knc_coupon_value = esc_attr($knc_coupon_details->coupon_value) . "%";
				$knc_amount_left .=  "%";
			}else{
				
				$_SESSION['discount_form_type'] = 'value';
				
				$knc_coupon_value = wpsc_currency_display( esc_attr($knc_coupon_details->coupon_value));
				$knc_amount_left = wpsc_currency_display($knc_amount_left);
			}
			
			?>
			<table id="knc_coupon_details_table">
			   <tbody>
				<tr class="knc_coupon_header">
					<th>Total Gift Card Value</th>
					<th>Amount Left</th>
					<th>Last Used</th>
					<th>Expiry</th>
					<th>Status</th>
				</tr>
			               
				<tr class="knc_coupon_details">
					<td><?php echo $knc_coupon_value; ?></td>
					<td><?php echo $knc_amount_left; ?></td>
					<td><?php _e($knc_last_used, 'knc_gift_coupon'); ?></td>
					<td><?php echo substr($wpsc_coupon_details->expiry, 0, -8); ?></td>
					<td><?php _e($knc_coupon_status, 'knc_gift_coupon'); ?></td>
				</tr>
			    </tbody>
			</table>
			<?php 
			$_SESSION['knc_disc_form_display'] = 'block';
			
		}else{
			// empty coupon field
			$_SESSION["coupon_numbers"]='';
			
			// hide apply discount form
			$_SESSION['knc_disc_form_display'] = 'none';
			
			// reset user fed value & hide discount error div 
			$_SESSION['knc_disc_value'] = ''; 
			$_SESSION['knc_disc_error_display'] = 'none';
						
			if ( isset( $_REQUEST['knc_update_coupon_code'] ) && ($_REQUEST['knc_update_coupon_code'] == 'true')) {
				knc_gc_reset_discount_value();
				$_SESSION['knc_coupon_error_display'] = 'block';
				$_SESSION['knc_coupon_error'] = KNC_ERR_INCORRECT_CODE ;
			}
		}
	}else{
		$_SESSION['knc_disc_form_display'] = 'none';

		if ( isset( $_REQUEST['knc_update_coupon_code'] ) && ($_REQUEST['knc_update_coupon_code'] == 'true')) {
			knc_gc_reset_discount_value();
			$_SESSION['knc_coupon_error_display'] = 'block';
			$_SESSION['knc_coupon_error'] = KNC_ERR_EMPTY_FIELD;
		}
	}
}


/*
 *	CHECK USER FED DISCOUNT VALUE
 */
function knc_gc_check_apply_discount(){
	global $wpsc_cart;
	if($_SESSION['knc_disc_error_display'] == 'none'){
		//set discount value on refresh
		//$wpsc_cart->apply_coupons( $_SESSION['knc_disc_value'], $_SESSION["coupon_numbers"] );
	}else{ 
		// if disc is > cart val, we dont want disc field to empty, rather
		// we wnt it to fill with max disc possible, thus dont reset disc field
		if($_SESSION['knc_disc_error'] != KNC_ERR_DISC_MORETHAN_CART){
			//knc_gc_reset_discount_value();
		}
	}
	if(!is_numeric($_POST["knc_gc_apply_disc_val"])){
		if ( isset( $_REQUEST['knc_update_disc_val'] ) && ($_REQUEST['knc_update_disc_val'] == 'true')) {
			knc_gc_reset_discount_value();
		}
	}
}


/*
 *	RESETS USER FED DISCOUNT VALUE
 */
function knc_gc_reset_discount_value(){
	global $wpsc_cart;
	
	$wpsc_cart->coupons_name = '';
	$wpsc_cart->coupons_amount = null;
	$_SESSION['knc_disc_value'] = null;
	$_SESSION['percent_applied'] = null;
	$wpsc_cart->apply_coupons( null, null );
	//$_SESSION['knc_disc_form_display'] = 'none';
}

function knc_gc_apply_discount(){
	
	global $wpdb, $wpsc_cart;

	// get discount type | percent, free shipping or value
	$discount_type = $_SESSION['discount_form_type'];
	
	// get user entered discount value
	$knc_discount_value = $_POST["knc_gc_apply_disc_val"];
	
	
	// get coupon code
	//$coupon_code = $_POST["knc_gc_cd_coupon_code"];
	$coupon_code = $_SESSION["coupon_numbers"];
	
	// get details from knc database 
	// " .  KNC_GC_TABLE_COUPON_CODES . "  = $wpdb->prefix . 'knc_coupon_details';
	$knc_coupon_details = $wpdb->get_row("SELECT * FROM  " .  KNC_GC_TABLE_COUPON_CODES . "  WHERE coupon_code='$coupon_code'");
	$knc_coupon_balance = $knc_coupon_details->amount_left;
	?>
		<script>//jQuery('#knc_gift_coupon_wrap').append('NOTE: <?php echo($knc_coupon_details->amount_left);?>');</script>
	<?php
	if ( isset( $_REQUEST['knc_update_percent_disc_val'] ) && ($_REQUEST['knc_update_percent_disc_val'] == 'true')) {
		$_SESSION['percent_applied']='true';
	}
	if($_SESSION['percent_applied']=='true'){
		// apply discount
		$knc_total_price = $wpsc_cart->calculate_subtotal();
		$knc_discount_available = $knc_coupon_details->coupon_value;
		$knc_discount_value = $knc_total_price * $knc_discount_available/100;
		$wpsc_cart->apply_coupons( $knc_discount_value, $coupon_code );
	}
	
	if ( isset( $_REQUEST['knc_update_disc_val'] ) && ($_REQUEST['knc_update_disc_val'] == 'true')) {
	
		// re-add user entered value to discount field after page reload
		$_SESSION['knc_disc_value'] = $knc_discount_value;
		
		if($discount_type=='percent'){
			// apply discount
			$knc_total_price = $wpsc_cart->calculate_subtotal();
			$knc_discount_available = $knc_coupon_details->coupon_value;
			$knc_discount_value = $knc_total_price * $knc_discount_available/100;
			$wpsc_cart->apply_coupons( $knc_discount_value, $coupon_code );
		}
		elseif($discount_type=='value' && is_numeric($knc_discount_value) && $knc_discount_value >= 0){
			
			// check if discount is less than or equal to balance left
			if($knc_discount_value <= $knc_coupon_balance){
				
				if($knc_discount_value <=  $wpsc_cart->calculate_subtotal()){
					// hide error div
					$_SESSION['knc_disc_error_display'] = 'none';
					
					// apply discount 
					$wpsc_cart->apply_coupons( $knc_discount_value, $coupon_code );

					// set this incase user updates qty & page refreshes
					// this is used to set the discount again after page refresh
					// in function knc_process_coupons()
					//$_SESSION['knc_disc_value'] = $knc_discount_value;
				
				}else{
					// show discount value is more than total purchase - applying max disc possible
					$_SESSION['knc_disc_value'] = $wpsc_cart->calculate_subtotal();
					$wpsc_cart->coupons_amount =  $wpsc_cart->calculate_subtotal();
					
					$_SESSION['knc_disc_error_display'] = 'block';
					$_SESSION['knc_disc_error'] = _e(KNC_ERR_DISC_MORETHAN_CART, 'knc_gift_coupon');
				}

			}else{
				// show balance limit error & unhide error div
				knc_gc_reset_discount_value();
				$_SESSION['knc_disc_error_display'] = 'block';
				$_SESSION['knc_disc_error'] = _e("Discount amount can not exceed the remaining balance of " . wpsc_currency_display($knc_coupon_balance), 'knc_gift_coupon');
			}
		}else{
			// show invalid disc value error & unhide error div
			knc_gc_reset_discount_value();
			$_SESSION['knc_disc_error_display'] = 'block';
			$_SESSION['knc_disc_error'] = _e(KNC_ERR_INVALID_DISC_VALUE, 'knc_gift_coupon');
		}
	}else{
		$_SESSION['knc_disc_error_display'] = 'none';
	}
}


/*
 *	CREATE KNC COUPON STORING TABLE IN DATABASE
 */
function knc_gc_create_coupon_table(){
	
	global $wpdb;
	$knc_current_time = date( 'Y-m-d H:i:s' );
	
	if($wpdb->get_var("SHOW TABLES LIKE " . KNC_GC_TABLE_COUPON_CODES ) !=  KNC_GC_TABLE_COUPON_CODES ) {
		$sql = "CREATE TABLE " . KNC_GC_TABLE_COUPON_CODES . " (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				coupon_code VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci,
				coupon_value decimal(11,2),
				amount_left decimal(11,2),
				first_check datetime DEFAULT '". $knc_current_time ."' NOT NULL,
				last_used datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
				UNIQUE KEY id (id)
				);";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}
}


/*
 *	CREATE "OPTIONS" TABLE ON PLUGIN ACTIVATION
 */
function knc_gc_activate(){
		
	global $wpdb, $knc_db_version;
	$knc_site_id = $wpdb->siteid;
	
	// create database table
	if($wpdb->get_var("SHOW TABLES LIKE " . KNC_GC_TABLE_ADMIN_OPTIONS ) !=  KNC_GC_TABLE_ADMIN_OPTIONS )
	{
		$sql = "CREATE TABLE " . KNC_GC_TABLE_ADMIN_OPTIONS . " (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			blog_id int(11) NOT NULL DEFAULT '". $knc_site_id ."',
			admin_options longtext,
			UNIQUE KEY id (id)
			);";
		
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}
	
	add_option(KNC_GC_TABLE_ADMIN_DB_VERSION, $knc_db_version );
	
	// create knc coupon storing table in database
	knc_gc_create_coupon_table();
}


/*
 *	INSERT INITIAL DATA INTO "OPTIONS" TABLE ON PLUGIN ACTIVATION
 */
function knc_gc_activation_data(){
 	
 	global $wpdb, $knc_db_version;
 	
	$knc_installed_version = get_option(KNC_GC_TABLE_ADMIN_DB_VERSION);
 	
	$knc_gc_admin_options = array(
		'coupon_details' 	 => array('coupon_name'=>array(), 'coupon_validity'=>array(), 'coupon_dmy'=>array()),
		'mail_from' 		 => get_option('admin_email'),
		'mail_subject' 		 => get_option('blogname'),
		'mail_message' 		 => '',
		'mail_message_txt'	 => '',	
		'use_coupon_gateway' => 'false',
		'their_gateways' 	 => (array)get_option('custom_gateway_options'),
		'our_gateway' 		 => KNC_PAYMENT_GATEWAY,
		'test_mode'			 => 'true',
		'test_mail'		 	 => get_option('admin_email')
	);
	
	$knc_gc_admin_options_check = $wpdb->get_var($wpdb->prepare("SELECT 'id' FROM " . KNC_GC_TABLE_ADMIN_OPTIONS . ";"));
	
	if($knc_gc_admin_options_check == null || $knc_gc_admin_options_check == false ){
		$wpdb->insert(KNC_GC_TABLE_ADMIN_OPTIONS, array('admin_options' => serialize($knc_gc_admin_options)));
	}
}


/*
 *	INITIALIZE ADMIN PANEL
 */
if (!function_exists("knc_gc_admin_menu")) {
	function knc_gc_admin_menu() {
		global $knc_gc_class;
		if (!isset($knc_gc_class)) {
			return;
		}
		if (function_exists('add_options_page')) {
			$mypage = add_options_page('KNC gift coupon multiuse plugin for wp-e-commerce', 'Knc Gift Coupon', 9, basename(__FILE__), array(&$knc_gc_class, 'options_page'));
			
			add_action("admin_print_scripts-$mypage", 'knc_gc_enqueue_scripts');
			add_action("admin_print_scripts-$mypage", 'knc_gc_admin_dynamic_js'); 
			//remove_all_filters('mce_external_plugins');
		}
	}	
}


/*
 *	ACTIONS AND FILTERS	
 */
if (isset($knc_gc_class)) {
	
	// Activation hooks
	register_activation_hook(__FILE__,'knc_gc_activate');
	register_activation_hook(__FILE__,'knc_gc_activation_data');
	
	// Shortcode
	add_shortcode('knc-coupon-form', 'knc_process_coupons');
	
	// Actions
	add_action('wp_ajax_my_ajax_hook', 'knc_gc_fetch_coupon_details');//ajax hook if admin logged in
	add_action('wp_ajax_nopriv_my_ajax_hook', 'knc_gc_fetch_coupon_details');// ajax hook for (no-privilages) general users
	add_action('wp_ajax_knc_gc_apply_discount_hook', 'knc_gc_apply_discount');//ajax hook if admin looged in
	add_action('wp_ajax_nopriv_knc_gc_apply_discount_hook', 'knc_gc_apply_discount');//ajax hook for (no-privilages) general users
	
	add_action('admin_menu', 'knc_gc_admin_menu');
	add_action('knc-gift-coupon/knc-gift-coupon.php',  array(&$knc_gc_class, 'init'));
	add_action('wp_print_styles', 'knc_gc_stylesheet');
	
	// Filters
	//add_filter('the_content', array(&$knc_gc_class, 'knc_process_coupons'),10);
	//add_filter('the_content', array(&$knc_gc_class, 'remove_wpsc_coupon_form'));
	add_filter('admin_head', array(&$knc_gc_class,'show_tiny_mce'));
}
?>