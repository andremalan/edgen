<?php

global $wpsc_cart, $wpdb, $wpsc_checkout, $wpsc_gateway, $wpsc_coupons,$row,$current_user,$knc_co;

 
$wpsc_checkout = new wpsc_checkout();
$wpsc_gateway = new wpsc_gateways();
$alt = 0;
if(isset($_SESSION['coupon_numbers']))
   $wpsc_coupons = new wpsc_coupons($_SESSION['coupon_numbers']);

if(wpsc_cart_item_count() < 1) :
   _e('Oops, there is nothing in your cart.', 'wpsc') . "<a href=".get_option("product_list_url").">" . __('Please visit our shop', 'wpsc') . "</a>";
   return;
endif;

?>
<div id="checkout_page_container">
<h3><?php _e('Please review your order', 'wpsc'); ?></h3>
<table class="checkout_cart">
   <!--tr class="header">
      <th colspan="2" ><?php _e('Product', 'wpsc'); ?></th>
      <th><?php _e('Quantity', 'wpsc'); ?></th>
      <th><?php _e('Price', 'wpsc'); ?></th>
      <th><?php _e('Total', 'wpsc'); ?></th>
        <th>&nbsp;</th>
   </tr-->

	<tr>
	<?php $our_cart_total = wpsc_cart_total(false); //we need this for donation ?>
	<?php //wpsc_donations(wpsc_cart_total(false) ) ; ?>
	<?php //var_dump($_POST); ?>
	</tr>

	<?php $has_donation = false; //to check if any of the items are donations  ?>
	
	
	  <?php while (wpsc_have_cart_items()) : wpsc_the_cart_item(); ?>
		<?php
		//check to see if the item is a donation, if it is, set the donation flag.
			$is_donation = get_post_meta( wpsc_cart_item_product_id(), '_wpsc_is_donation', true );
			if (1 == $is_donation)
				$has_donation = true;
		?>
	<?php endwhile; ?>
	
	
	
	<?php // Add a donation if there are no donations
		if(!$has_donation && !(isset($_POST['quantity']) && $_POST['quantity'] == 0)){
			
			
			$meta = array(
				'is_donation' => 1
			);
			
			//This code will check to see if there is any gift card in the cart, if there is donation will be removed. 
			if(wpsc_cart_item_product_id() == 3441 || wpsc_cart_item_product_id() == 4975 || wpsc_cart_item_product_id() == 4959 || wpsc_cart_item_product_id() == 4976)
		{
		
		$quantity = 0;
	
		}
			
			else
			{
			$quantity = (int) round($our_cart_total / 10.0);
			//$provided_price = 6;
			
			$default_parameters['variation_values'] = null;
			$default_parameters['quantity'] = $quantity;
			$default_parameters['provided_price'] = 1;
			$default_parameters['comment'] = null;
			$default_parameters['time_requested'] = null;
			$default_parameters['custom_message'] = null;
			$default_parameters['file_data'] = null;
			$default_parameters['is_customisable'] = false;
			$default_parameters['meta'] = $meta;
			
			$result = $wpsc_cart->set_item(3457, $default_parameters);
			//echo $result;
			//wp_redirect("/");
			//wpsc_add_to_cart();


			}


		}
	
	?>
	
	
	
	<?php $our_cart_total = wpsc_cart_total(false); //we need to recalculate after adding ?>
	
	
	
   <?php while (wpsc_have_cart_items()) : wpsc_the_cart_item(); ?>
      

	<?php
       $alt++;
       if ($alt %2 == 1)
         $alt_class = 'alt';
       else
         $alt_class = '';
       ?>

      <?php  //this displays the confirm your order html ?>

	  <?php do_action ( "wpsc_before_checkout_cart_row" ); ?>
      <tr class="product_row product_row_<?php echo wpsc_the_cart_item_key(); ?> <?php echo $alt_class;?>">

         <td class="firstcol wpsc_product_image wpsc_product_image_<?php echo wpsc_the_cart_item_key(); ?>">
         <?php if('' != wpsc_cart_item_image()): ?>
			<?php do_action ( "wpsc_before_checkout_cart_item_image" ); ?>
            <img src="<?php echo wpsc_cart_item_image(); ?>" alt="<?php echo wpsc_cart_item_name(); ?>" title="<?php echo wpsc_cart_item_name(); ?>" class="product_image" />
			<?php do_action ( "wpsc_after_checkout_cart_item_image" ); ?>
         <?php else:
         /* I dont think this gets used anymore,, but left in for backwards compatibility */
         ?>
            <div class="item_no_image">
               <a href="<?php echo wpsc_the_product_permalink(); ?>">
               <span><?php _e('No Image','wpsc'); ?></span>

               </a>
            </div>
         <?php endif; ?>
         </td>

         <td class="wpsc_product_name wpsc_product_name_<?php echo wpsc_the_cart_item_key(); ?>">
			<?php do_action ( "wpsc_before_checkout_cart_item_name" ); ?>
            <a href="<?php echo wpsc_cart_item_url();?>"><?php echo wpsc_cart_item_name(); ?></a>
			<?php do_action ( "wpsc_after_checkout_cart_item_name" ); ?>
         </td>

         <td class="wpsc_product_quantity wpsc_product_quantity_<?php echo wpsc_the_cart_item_key(); ?>">
            <form action="/students/checkout" method="post" class="adjustform qty">
               <!--input type="text" name="quantity" size="2" value="<?php echo wpsc_cart_item_quantity(); ?>" /-->
<?php ih_quantity_display( wpsc_cart_item_product_id(), wpsc_cart_item_quantity(), "cart" , wpsc_cart_item_name(), $our_cart_total); ?>

               <input type="hidden" name="key" value="<?php echo wpsc_the_cart_item_key(); ?>" />
               <input type="hidden" name="wpsc_update_quantity" value="true" />
               <input type="submit" value="<?php _e('Update', 'wpsc'); ?>" name="submit" />
            </form>
         </td>

       
            <!--td><?php //echo wpsc_cart_single_item_price(); ?></td>
         <td class="wpsc_product_price wpsc_product_price_<?php //echo wpsc_the_cart_item_key(); ?>"><span class="pricedisplay"><?php //echo wpsc_cart_item_price(); ?></span></td-->

         <td class="wpsc_product_remove wpsc_product_remove_<?php echo wpsc_the_cart_item_key(); ?>">
            <form action="/students/checkout" method="post" class="adjustform remove">
               <input type="hidden" name="quantity" value="0" />
               <input type="hidden" name="key" value="<?php echo wpsc_the_cart_item_key(); ?>" />
               <input type="hidden" name="wpsc_update_quantity" value="true" />
               <input type="submit" value="<?php _e('Remove', 'wpsc'); ?>" name="submit" />
            </form>
         </td>
      </tr>


	  <?php do_action ( "wpsc_after_checkout_cart_row" ); ?>
   <?php endwhile; ?>
	



   <?php //this HTML displays coupons if there are any active coupons to use ?>



   <?php

   if(wpsc_uses_coupons()): ?>

      <?php if(wpsc_coupons_error()): ?>
         <!--tr class="wpsc_coupon_row wpsc_coupon_error_row"><td><?php _e('Coupon is not valid.', 'wpsc'); ?></td></tr-->
      <?php endif; ?>
      <!--tr class="wpsc_coupon_row">
         <td colspan="2"><?php _e('Enter gift card code', 'wpsc'); ?> :</td>
         <td  colspan="4" class="coupon_code">
            <form  method="post" action="<?php echo get_option('shopping_cart_url'); ?>">
               <input type="text" name="coupon_num" id="coupon_num" value="<?php echo $wpsc_cart->coupons_name; ?>" />
               <input type="submit" value="<?php _e('Update', 'wpsc') ?>" />
            </form>
         </td>
      </tr-->
	<?php if(wpsc_cart_item_product_id() == 3441 || wpsc_cart_item_product_id() == 4975 || wpsc_cart_item_product_id() == 4959 || wpsc_cart_item_product_id() == 4976){ ?>
	<!-- Textbox get the coupon receiver email and then unpload the receiver’s email -->
	 <td colspan="2"><?php _e('Enter the coupon receiver email', 'wpsc'); ?> :</td>      
	<form  method="post" action="<?php echo get_option('shopping_cart_url'); ?>">	
         <td  colspan="4" class="coupon_code">
            	           
			

<input type="text" style="width:250px;" name="test_mail" id="test_mail" <?php if ($knc_gc_options['test_mode'] == "false")  { _e('disabled="true"', 'knc_gift_coupon'); }?> value="<?php _e($testMail, 'knc_gift_coupon'); ?>" />
		<input type='hidden' name="update_knc_gc_settings" value="<?php _e($testMail, 'knc_gift_coupon'); ?>"/>
			<input type="submit" name="update_knc_gc_settings" value="<?php _e('Upload', 'knc_gift_coupon') ?>" />
</form>
	<?php echo $_POST['use_email'];?>
         </td>
      </tr-->
<?php } ?>

	  <?php if(wpsc_uses_coupons() && (wpsc_coupon_amount(false) > 0)): ?>
	      <tr class="total_price">
	         <td class='wpsc_totals'>
	            <?php _e('Gift card credit', 'wpsc'); ?>:
	         </td>
	         <td class='wpsc_totals'>
	            <span id="coupons_amount" class="pricedisplay"><?php echo wpsc_coupon_amount(); ?></span>
	          </td>
	         </tr>
	     <?php endif ?>



	   <tr class='total_price'>
	      <td class='wpsc_totals'>
	      <?php _e('Total', 'wpsc'); ?>:
	      </td>
	      <td class='wpsc_totals'>
	         <span id='checkout_total' class="pricedisplay checkout-total"><?php echo wpsc_cart_total(); ?></span>
	      </td>
	   </tr>   <?php endif; ?>
   </table>
   <!-- cart contents table close -->
<?php do_shortcode('[knc-coupon-form]'); ?>

  <?php if(wpsc_uses_shipping()): ?>
	   <p class="wpsc_cost_before"></p>
   <?php endif; ?>
   <?php  //this HTML dispalys the calculate your order HTML   ?>

   <?php if(wpsc_has_category_and_country_conflict()): ?>
      <p class='validation-error'><?php echo $_SESSION['categoryAndShippingCountryConflict']; ?></p>
      <?php unset($_SESSION['categoryAndShippingCountryConflict']);
   endif;

   if(isset($_SESSION['WpscGatewayErrorMessage']) && $_SESSION['WpscGatewayErrorMessage'] != '') :?>
      <p class="validation-error"><?php echo $_SESSION['WpscGatewayErrorMessage']; ?></p>
   <?php
   endif;
   ?>

   <?php do_action('wpsc_before_shipping_of_shopping_cart'); ?>

   <div id="wpsc_shopping_cart_container">
   <?php if(wpsc_uses_shipping()) : ?>
      <h2><?php _e('Calculate Shipping Price', 'wpsc'); ?></h2>
      <table class="productcart">
         <tr class="wpsc_shipping_info">
            <td colspan="5">
               <?php _e('Please choose a country below to calculate your shipping costs', 'wpsc'); ?>
            </td>
         </tr>

         <?php if (!wpsc_have_shipping_quote()) : // No valid shipping quotes ?>
            <?php if (wpsc_have_valid_shipping_zipcode()) : ?>
                  <tr class='wpsc_update_location'>
                     <td colspan='5' class='shipping_error' >
                        <?php _e('Please provide a Zipcode and click Calculate in order to continue.', 'wpsc'); ?>
                     </td>
                  </tr>
            <?php else: ?>
               <tr class='wpsc_update_location_error'>
                  <td colspan='5' class='shipping_error' >
                     <?php _e('Sorry, online ordering is unavailable to this destination and/or weight. Please double check your destination details.', 'wpsc'); ?>
                  </td>
               </tr>
            <?php endif; ?>
         <?php endif; ?>
         <tr class='wpsc_change_country'>
            <td colspan='5'>
               <form name='change_country' id='change_country' action='' method='post'>
                  <?php echo wpsc_shipping_country_list();?>
                  <input type='hidden' name='wpsc_update_location' value='true' />
                  <input type='submit' name='wpsc_submit_zipcode' value='Calculate' />
               </form>
            </td>
         </tr>

         <?php if (wpsc_have_morethanone_shipping_quote()) :?>
            <?php while (wpsc_have_shipping_methods()) : wpsc_the_shipping_method(); ?>
                  <?php    if (!wpsc_have_shipping_quotes()) { continue; } // Don't display shipping method if it doesn't have at least one quote ?>
                  <tr class='wpsc_shipping_header'><td class='shipping_header' colspan='5'><?php echo wpsc_shipping_method_name().__(' - Choose a Shipping Rate', 'wpsc'); ?> </td></tr>
                  <?php while (wpsc_have_shipping_quotes()) : wpsc_the_shipping_quote();  ?>
                     <tr class='<?php echo wpsc_shipping_quote_html_id(); ?>'>
                        <td class='wpsc_shipping_quote_name wpsc_shipping_quote_name_<?php echo wpsc_shipping_quote_html_id(); ?>' colspan='3'>
                           <label for='<?php echo wpsc_shipping_quote_html_id(); ?>'><?php echo wpsc_shipping_quote_name(); ?></label>
                        </td>
                        <td class='wpsc_shipping_quote_price wpsc_shipping_quote_price_<?php echo wpsc_shipping_quote_html_id(); ?>' style='text-align:center;'>
                           <label for='<?php echo wpsc_shipping_quote_html_id(); ?>'><?php echo wpsc_shipping_quote_value(); ?></label>
                        </td>
                        <td class='wpsc_shipping_quote_radio wpsc_shipping_quote_radio_<?php echo wpsc_shipping_quote_html_id(); ?>' style='text-align:center;'>
                           <?php if(wpsc_have_morethanone_shipping_methods_and_quotes()): ?>
                              <input type='radio' id='<?php echo wpsc_shipping_quote_html_id(); ?>' <?php echo wpsc_shipping_quote_selected_state(); ?>  onclick='switchmethod("<?php echo wpsc_shipping_quote_name(); ?>", "<?php echo wpsc_shipping_method_internal_name(); ?>")' value='<?php echo wpsc_shipping_quote_value(true); ?>' name='shipping_method' />
                           <?php else: ?>
                              <input <?php echo wpsc_shipping_quote_selected_state(); ?> disabled='disabled' type='radio' id='<?php echo wpsc_shipping_quote_html_id(); ?>'  value='<?php echo wpsc_shipping_quote_value(true); ?>' name='shipping_method' />
                                 <?php wpsc_update_shipping_single_method(); ?>
                           <?php endif; ?>
                        </td>
                     </tr>
                  <?php endwhile; ?>
            <?php endwhile; ?>
         <?php endif; ?>

         <?php wpsc_update_shipping_multiple_methods(); ?>


         <?php if (!wpsc_have_shipping_quote()) : // No valid shipping quotes ?>
               </table>
               </div>
			</div>
            <?php return; ?>
         <?php endif; ?>
      </table>
   <?php endif;  ?>

   <?php
      $wpec_taxes_controller = new wpec_taxes_controller();
      if($wpec_taxes_controller->wpec_taxes_isenabled()):
   ?>
      <table class="productcart">
         <tr class="total_price total_tax">
            <td colspan="3">
               <?php echo wpsc_display_tax_label(true); ?>
            </td>
            <td colspan="2">
               <span id="checkout_tax" class="pricedisplay checkout-tax"><?php echo wpsc_cart_tax(); ?></span>
            </td>
         </tr>
      </table>
   <?php endif; ?>
   <?php do_action('wpsc_before_form_of_shopping_cart'); ?>
                 
	<?php if(!empty($_SESSION['wpsc_checkout_user_error_messages'])): ?>
		<p class="validation-error">
		<?php
		foreach($_SESSION['wpsc_checkout_user_error_messages'] as $user_error )
		echo $user_error."<br />\n";
		
		$_SESSION['wpsc_checkout_user_error_messages'] = array();
		?>
	<?php endif; ?>

	<?php if ( wpsc_show_user_login_form() && !is_user_logged_in() ): ?>
			<p><?php _e('You must sign in or register with us to continue with your donation', 'wpsc');?></p>
			<div class="wpsc_registration_form">
				
				<fieldset class='wpsc_registration_form'>
					 <?php sidebarlogin(); ?> 
					 <?php widgets_on_template("checkout_login");?>
					 
					<div class="wpsc_signup_text">If you've donated on Education Generation before, please sign in to finish donating.</div>
				</fieldset>
			</div>
	<?php endif; ?>	
	<form class='wpsc_checkout_forms' action='/students/checkout' method='post' enctype="multipart/form-data">
				
      <?php
      /**
       * Both the registration forms and the checkout details forms must be in the same form element as they are submitted together, you cannot have two form elements submit together without the use of JavaScript.
      */
      ?>

    <?php if(wpsc_show_user_login_form()):
          global $current_user;
          get_currentuserinfo();   ?>

		<div class="wpsc_registration_form">
			
	        <fieldset class='wpsc_registration_form wpsc_right_registration'>
	        	<h2><?php _e('Join up now', 'wpsc');?></h2>
	      
				<label><?php _e('Username', 'wpsc'); ?>:</label>
				<input type="text" name="log" id="log" value="" size="20"/><br/>
				
				<label><?php _e('Password', 'wpsc'); ?>:</label>
				<input type="password" name="pwd" id="pwd" value="" size="20" /><br />
				
				<label><?php _e('E-mail', 'wpsc'); ?>:</label>
	            <input type="text" name="user_email" id="user_email" value="<?php echo attribute_escape(stripslashes($user_email)); ?>" size="20" /><br />
	            
	            <div class="wpsc_signup_text"><?php _e('Signing up is free and easy! please fill out your details your registration will happen automatically as you checkout. Don\'t forget to use your details to login with next time!', 'wpsc');?></div>
	        </fieldset>
	        
        </div>
        <div class="clear"></div>
   <?php endif; // closes user login form

      if(!empty($_SESSION['wpsc_checkout_misc_error_messages'])): ?>
         <div class='login_error'>
            <?php foreach((array)$_SESSION['wpsc_checkout_misc_error_messages'] as $user_error ){?>
               <p class='validation-error'><?php echo $user_error; ?></p>
               <?php } ?>
         </div>

      <?php
      endif;
       $_SESSION['wpsc_checkout_misc_error_messages'] = array(); ?>
<?php ob_start(); ?>
   <table class='wpsc_checkout_table table-1'>
      <?php $i = 0;
      while (wpsc_have_checkout_items()) : wpsc_the_checkout_item(); ?>

        <?php if(wpsc_checkout_form_is_header() == true){
               $i++;
               //display headers for form fields ?>
               <?php if($i > 1):?>
                  </table>
                  <table class='wpsc_checkout_table table-<?php echo $i; ?>'>
               <?php endif; ?>

               <tr <?php echo wpsc_the_checkout_item_error_class();?>>
                  <td <?php wpsc_the_checkout_details_class(); ?> colspan='2'>
                     <h4><?php echo wpsc_checkout_form_name();?></h4>
                  </td>
               </tr>
               <?php if(wpsc_is_shipping_details()):?>
               <tr class='same_as_shipping_row'>
                  <td colspan ='2'>
                  <?php $checked = '';
                  if(isset($_POST['shippingSameBilling']) && $_POST['shippingSameBilling'])
                  	$_SESSION['shippingSameBilling'] = true;
                  elseif(isset($_POST['submit']) && !isset($_POST['shippingSameBilling']))
                  	$_SESSION['shippingSameBilling'] = false;

                  	if( isset( $_SESSION['shippingSameBilling'] ) && $_SESSION['shippingSameBilling'] == 'true' )
                  		$checked = 'checked="checked"';
                   ?>
					<label for='shippingSameBilling'><?php _e('Same as billing address:','wpsc'); ?></label>
					<input type='checkbox' value='true' name='shippingSameBilling' id='shippingSameBilling' <?php echo $checked; ?> />
					<br/><span id="shippingsameasbillingmessage"><?php _e('Your order will be shipped to the billing address', 'wpsc'); ?></span>
                  </td>
               </tr>
               <?php endif;

            // Not a header so start display form fields
            }elseif(wpsc_disregard_shipping_state_fields()){
            ?>
               <tr class='wpsc_hidden'>
                  <td class='<?php echo wpsc_checkout_form_element_id(); ?>'>
                     <label for='<?php echo wpsc_checkout_form_element_id(); ?>'>
                     <?php echo wpsc_checkout_form_name();?>
                     </label>
                  </td>
                  <td>
                     <?php echo wpsc_checkout_form_field();?>
                      <?php if(wpsc_the_checkout_item_error() != ''): ?>
                             <p class='validation-error'><?php echo wpsc_the_checkout_item_error(); ?></p>
                     <?php endif; ?>
                  </td>
               </tr>
            <?php
            }elseif(wpsc_disregard_billing_state_fields()){
            ?>
               <tr class='wpsc_hidden'>
                  <td class='<?php echo wpsc_checkout_form_element_id(); ?>'>
                     <label for='<?php echo wpsc_checkout_form_element_id(); ?>'>
                     <?php echo wpsc_checkout_form_name();?>
                     </label>
                  </td>
                  <td>
                     <?php echo wpsc_checkout_form_field();?>
                      <?php if(wpsc_the_checkout_item_error() != ''): ?>
                             <p class='validation-error'><?php echo wpsc_the_checkout_item_error(); ?></p>
                     <?php endif; ?>
                  </td>
               </tr>
            <?php
            }elseif( $wpsc_checkout->checkout_item->unique_name == 'billingemail'){ ?>
               <?php $email_markup =
               "<div class='wpsc_email_address'>
                  <p class='" . wpsc_checkout_form_element_id() . "'>
                     <label class='wpsc_email_address' for='" . wpsc_checkout_form_element_id() . "'>
                     " . __('Enter your email address', 'wpsc') . "
                     </label>
                  <p class='wpsc_email_address_p'>
                  <img src='https://secure.gravatar.com/avatar/empty?s=60&amp;d=mm' id='wpsc_checkout_gravatar' />
                  " . wpsc_checkout_form_field();
                  
                   if(wpsc_the_checkout_item_error() != '')
                      $email_markup .= "<p class='validation-error'>" . wpsc_the_checkout_item_error() . "</p>";
               $email_markup .= "</div>";
             }else{ ?>
			<tr>
               <td class='<?php echo wpsc_checkout_form_element_id(); ?>'>
                  <label for='<?php echo wpsc_checkout_form_element_id(); ?>'>
                  <?php echo wpsc_checkout_form_name();?>
                  </label>
               </td>
               <td>
                  <?php echo wpsc_checkout_form_field();?>
                   <?php if(wpsc_the_checkout_item_error() != ''): ?>
                          <p class='validation-error'><?php echo wpsc_the_checkout_item_error(); ?></p>
                  <?php endif; ?>
               </td>
            </tr>

         <?php }//endif; ?>

      <?php endwhile; ?>
 
<?php 
	$buffer_contents = ob_get_contents();
	ob_end_clean();
	if(isset($email_markup))
		echo $email_markup;
	echo $buffer_contents;
?>

      <?php if (wpsc_show_find_us()) : ?>
      <tr>
         <td><label for='how_find_us'><?php _e('How did you find us' , 'wpsc'); ?></label></td>
         <td>
            <select name='how_find_us'>
               <option value='Word of Mouth'><?php _e('Word of mouth' , 'wpsc'); ?></option>
               <option value='Advertisement'><?php _e('Advertising' , 'wpsc'); ?></option>
               <option value='Internet'><?php _e('Internet' , 'wpsc'); ?></option>
               <option value='Customer'><?php _e('Existing Customer' , 'wpsc'); ?></option>
            </select>
         </td>
      </tr>
      <?php endif; ?>
      <?php do_action('wpsc_inside_shopping_cart'); ?>

      <?php  //this HTML displays activated payment gateways   ?>
      <?php if(wpsc_gateway_count() > 1): // if we have more than one gateway enabled, offer the user a choice ?>
         <tr>
         <td colspan='2' class='wpsc_gateway_container'>
            <h3><?php _e('Payment Type', 'wpsc');?></h3>
            <?php while (wpsc_have_gateways()) : wpsc_the_gateway(); ?>
               <div class="custom_gateway">
                     <label><input type="radio" value="<?php echo wpsc_gateway_internal_name();?>" <?php echo wpsc_gateway_is_checked(); ?> name="custom_gateway" class="custom_gateway"/><?php echo wpsc_gateway_name(); ?> 
                     	<?php if( wpsc_show_gateway_image() ): ?>
                     	<img src="<?php echo wpsc_gateway_image_url(); ?>" alt="<?php echo wpsc_gateway_name(); ?>" style="position:relative; top:5px;" />
                     	<?php endif; ?>
                     </label>

                  <?php if(wpsc_gateway_form_fields()): ?>
                     <table class='wpsc_checkout_table <?php echo wpsc_gateway_form_field_style();?>'>
                        <?php echo wpsc_gateway_form_fields();?>
                     </table>
                  <?php endif; ?>
               </div>
            <?php endwhile; ?>
            </td></tr>
         <?php else: // otherwise, there is no choice, stick in a hidden form ?>
            <tr><td colspan="2" class='wpsc_gateway_container'>
            <?php while (wpsc_have_gateways()) : wpsc_the_gateway(); ?>
               <input name='custom_gateway' value='<?php echo wpsc_gateway_internal_name();?>' type='hidden' />

                  <?php if(wpsc_gateway_form_fields()): ?>
                     <table class='wpsc_checkout_table <?php echo wpsc_gateway_form_field_style();?>'>
                        <?php echo wpsc_gateway_form_fields();?>
                     </table>
                  <?php endif; ?>
            <?php endwhile; ?>
         </td>
         </tr>
         <?php endif; ?>

      <?php if(wpsc_has_tnc()) : ?>
         <tr>
            <td colspan='2'>
                <label for="agree"><input id="agree" type='checkbox' value='yes' name='agree' /> <?php printf(__("I agree to The <a class='thickbox' target='_blank' href='%s' class='termsandconds'>Terms and Conditions</a>", "wpsc"), site_url("?termsandconds=true&amp;width=360&amp;height=400'")); ?></label>
               </td>
         </tr>
      <?php endif; ?>
      </table>

   <table  class='wpsc_checkout_table table-4'>
      <?php if(wpsc_uses_shipping()) : ?>
	      <tr>
	         <td class='wpsc_total_price_and_shipping'colspan='2'>
	            <h4><?php _e('Review and purchase','wpsc'); ?></h4>
	         </td>
	      </tr>
	
	      <tr class="total_price total_shipping">
	         <td class='wpsc_totals'>
	            <?php _e('Total Shipping', 'wpsc'); ?>:
	         </td>
	         <td class='wpsc_totals'>
	            <span id="checkout_shipping" class="pricedisplay checkout-shipping"><?php echo wpsc_cart_shipping(); ?></span>
	         </td>
	      </tr>
      <?php endif; ?>

     <?php if(wpsc_uses_coupons() && (wpsc_coupon_amount(false) > 0)): ?>
      <tr class="total_price">
         <td class='wpsc_totals'>
            <?php _e('Discount', 'wpsc'); ?>:
         </td>
         <td class='wpsc_totals'>
            <span id="coupons_amount" class="pricedisplay"><?php echo wpsc_coupon_amount(); ?></span>
          </td>
         </tr>
     <?php endif ?>



   <tr class='total_price'>
      <td class='wpsc_totals'>
      <?php _e('Total Price', 'wpsc'); ?>:
      </td>
      <td class='wpsc_totals'>
         <span id='checkout_total' class="pricedisplay checkout-total"><?php echo wpsc_cart_total(); ?></span>
      </td>
   </tr>
   </table>

<!-- div for make purchase button -->
<!-- ideahack getting rid of donation button for logged in useres-->
<?php if ( is_user_logged_in() ): ?>
      <div class='wpsc_make_purchase'>
         <span>
            <?php if(!wpsc_has_tnc()) : ?>
               <input type='hidden' value='yes' name='agree' />
            <?php endif; ?>
               <input type='hidden' value='submit_checkout' name='wpsc_action' />
               <input type='submit' value='<?php _e('Purchase', 'wpsc');?>' name='submit' class='make_purchase wpsc_buy_button' />
         </span>
      </div>
<?php endif; ?>
<div class='clear'></div>
</form>
</div>
</div><!--close checkout_page_container-->
<?php
do_action('wpsc_bottom_of_shopping_cart');


?>

<?php
/* 
 * Calling  KNC_GC_TABLE_ADMIN_OPTIONS table from database. 
 * The table is serialized, so after calling it, the table needs to be unsterilized
*/	
global $wpdb;
			
$row = $wpdb->get_row( $wpdb->prepare( "SELECT admin_options FROM ". KNC_GC_TABLE_ADMIN_OPTIONS ) );
$knc_gc_options = unserialize($row->admin_options);
			
// if found previously stored data then use that (basically re-update that)
if (!empty($knc_gc_options)) {
	foreach ($knc_gc_options as $key => $option)
		$knc_gc_admin_options[$key] = $option;
}
if (isset($_POST['test_mail'])) {
	$knc_gc_options['test_mail'] = $_POST['test_mail'];
}	

$knc_result = $wpdb->update( KNC_GC_TABLE_ADMIN_OPTIONS, array('admin_options' => serialize($knc_gc_options) ), array( 'blog_id' => 0 ) );// update the table after change email address


?>
