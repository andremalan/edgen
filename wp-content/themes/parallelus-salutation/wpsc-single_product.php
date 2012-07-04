<?php
	// Setup globals
	// @todo: Get these out of template
	global $wp_query, $purchlogs, $wpdb;

	// Setup image width and height variables
	// @todo: Investigate if these are still needed here
	$image_width  = get_option( 'single_view_image_width' );
	$image_height = get_option( 'single_view_image_height' );
?>

<div id="single_product_page_container">
	
	<?php
		// Breadcrumbs
		wpsc_output_breadcrumbs();

		// Plugin hook for adding things to the top of the products page, like the live search
		do_action( 'wpsc_top_of_products_page' );
	?>
	
	<div class="single_product_display group">
<?php
		/**
		 * Start the product loop here.
		 * This is single products view, so there should be only one
		 */





	if(isset($_GET['doitnow2']) && $_GET['doitnow2'] == 'yes' ){
	add_the_students();
}
	

	
		while ( wpsc_have_products() ) : wpsc_the_product(); ?>
					<div class="imagecol">
						<?php if ( wpsc_the_product_thumbnail() ) : ?>
								<a rel="<?php echo wpsc_the_product_title(); ?>" class="<?php echo wpsc_the_product_image_link_classes(); ?>" href="<?php echo wpsc_the_product_image(); ?>">
									<img class="product_image" id="product_image_<?php echo wpsc_the_product_id(); ?>" alt="<?php echo wpsc_the_product_title(); ?>" title="<?php echo wpsc_the_product_title(); ?>" src="<?php echo wpsc_the_product_thumbnail(get_option('product_image_width'),get_option('product_image_height'),'','single'); ?>"/>
								</a>
								<?php 
								if ( function_exists( 'gold_shpcrt_display_gallery' ) )
									echo gold_shpcrt_display_gallery( wpsc_the_product_id() );
								?>
						<?php else: ?>
									<a href="<?php echo wpsc_the_product_permalink(); ?>">
									<img class="no-image" id="product_image_<?php echo wpsc_the_product_id(); ?>" alt="No Image" title="<?php echo wpsc_the_product_title(); ?>" src="<?php echo WPSC_CORE_THEME_URL; ?>wpsc-images/noimage.png" width="<?php echo get_option('product_image_width'); ?>" height="<?php echo get_option('product_image_height'); ?>" />
									</a>
						<?php endif; ?>
						
						<?php //ideahack putting product button above description   ?>

							<?php
							/**
							 * Form data
							 */
							?>
					<?php if(( 'true' == get_post_meta(wpsc_the_product_id(), 'Active', true) ) or (get_post_meta(wpsc_the_product_id(), 'Active', true) == "")): ?>
							<form class="product_form" enctype="multipart/form-data" action="<?php echo wpsc_this_page_url(); ?>" method="post" name="1" id="product_<?php echo wpsc_the_product_id(); ?>">
								<?php do_action ( 'wpsc_product_form_fields_begin' ); ?>
								<?php if ( wpsc_product_has_personal_text() ) : ?>
									<fieldset class="custom_text">
										<legend><?php _e( 'Personalize Your Product', 'wpsc' ); ?></legend>
										<p><?php _e( 'Complete this form to include a personalized message with your purchase.', 'wpsc' ); ?></p>
										<textarea cols='55' rows='5' name="custom_text"></textarea>
									</fieldset>
								<?php endif; ?>


								<?php
								/**
								 * Quantity options - MUST be enabled in Admin Settings
								 */
								?>
								<?php if(wpsc_has_multi_adding()): ?>
						        	<fieldset><legend><?php _e('', 'wpsc'); ?></legend>
									<div class="wpsc_quantity_update">
					<?php ih_quantity_display( wpsc_the_product_id() ); ?>
									<input type="hidden" name="key" value="<?php echo wpsc_the_cart_item_key(); ?>"/>
									<input type="hidden" name="wpsc_update_quantity" value="true" />
						            </div><!--close wpsc_quantity_update-->
						            </fieldset>
								<?php endif ;?>


								<input type="hidden" value="add_to_cart" name="wpsc_ajax_action" />
								<input type="hidden" value="<?php echo wpsc_the_product_id(); ?>" name="product_id" />					
								<?php if( wpsc_product_is_customisable() ) : ?>
									<input type="hidden" value="true" name="is_customisable"/>
								<?php endif; ?>

								<?php
								/**
								 * Cart Options
								 */
								?>

								<?php if((get_option('hide_addtocart_button') == 0) &&  (get_option('addtocart_or_buynow') !='1')) : ?>
									<?php if(wpsc_product_has_stock()) : ?>
										<div class="wpsc_buy_button_container">
												<?php if(wpsc_product_external_link(wpsc_the_product_id()) != '') : ?>
												<?php $action = wpsc_product_external_link( wpsc_the_product_id() ); ?>
												<input class="wpsc_buy_button" type="submit" value="<?php echo wpsc_product_external_link_text( wpsc_the_product_id(), __( 'Buy Now', 'wpsc' ) ); ?>" onclick="return gotoexternallink('<?php echo $action; ?>', '<?php echo wpsc_product_external_link_target( wpsc_the_product_id() ); ?>')">
												<?php else: ?>
											<input type="submit" value="<?php _e('Donate', 'wpsc'); ?>" name="Buy" class="wpsc_buy_button" id="product_<?php echo wpsc_the_product_id(); ?>_submit_button"/>
												<?php endif; ?>
											<div class="wpsc_loading_animation">
												<img title="Loading" alt="Loading" src="<?php echo wpsc_loading_animation_url(); ?>" />
												<?php _e('Updating cart...', 'wpsc'); ?>
											</div><!--close wpsc_loading_animation-->
										</div><!--close wpsc_buy_button_container-->
									<?php else : ?>
										<p class="soldout"><?php _e('This product has sold out.', 'wpsc'); ?></p>
									<?php endif ; ?>
								<?php endif ; ?>

								<?php do_action ( 'wpsc_product_form_fields_end' ); ?>
							</form><!--close product_form-->
						<?php endif ; ?>
							<?php
								if ( (get_option( 'hide_addtocart_button' ) == 0 ) && ( get_option( 'addtocart_or_buynow' ) == '1' ) )
									echo wpsc_buy_now_button( wpsc_the_product_id() );

								echo wpsc_product_rater();

								echo wpsc_also_bought( wpsc_the_product_id() );

							?>
							<!--sharethis-->
							<?php if ( get_option( 'wpsc_share_this' ) == 1 ): ?>
							<div class="st_sharethis" displayText="ShareThis"></div>
							<?php endif; ?>
							<!--end sharethis-->
							<?php 
							if(wpsc_show_fb_like()): ?>
						        <div class="FB_like">
						        <iframe src="https://www.facebook.com/plugins/like.php?href=<?php echo wpsc_the_product_permalink(); ?>&amp;layout=standard&amp;show_faces=true&amp;width=435&amp;action=like&amp;font=arial&amp;colorscheme=light" frameborder="0"></iframe>
						        </div><!--close FB_like-->
						    <?php endif; ?>						

					

						<?php // ideahack end of add /remove from cart stuffs.?>						
						
						
					</div><!--close imagecol-->

					<div class="productcol">
						<?php
						/** Ideahack Meta custom
						 * Custom meta HTML and loop
						 */
						?>

                        <?php if (wpsc_have_custom_meta()) : ?>
                    						<div class="custom_meta">
												<?php
												ih_display_urgent();
												?>
                    								<?php


                    									$birthdate = get_post_meta(wpsc_the_product_id(), 'Birthdate (yyyy/mm/dd)', true);
                    									$country = get_post_meta(wpsc_the_product_id(), 'Country', true);
                    									$area_of_study = get_post_meta(wpsc_the_product_id(), 'Program Title', true);
                    									$funding_deadline = get_post_meta(wpsc_the_product_id(), 'Expiration Date (yyyy/mm/dd)', true);

                    									$funding_needed = get_post_meta(wpsc_the_product_id(), 'Funding Needed', true);					

                    									$student_age = get_post_meta(get_the_ID(), 'Birth Date (yyyy/mm/dd)', true); 
          												$student_age = age_from_birth_date($student_age); 

                    									if ( $birthdate != "" )
                    										echo "<div id='birthdate'><strong>Birthdate:</strong> " . $birthdate . "</div>";
                    									if ( $country != "" )
                    										echo "<div id='country'><strong>Country:</strong> " . $country . "</div>";
                    									if ( $student_age != "" )
                    										echo "<div id='age'><strong>Age:</strong> " . $student_age . "</div>";
                    									if ( $area_of_study != "" )
                    										echo "<div id='areaofstudy'><strong>Area of Study:</strong> " . $area_of_study . "</div>";
                    									if ( $funding_deadline != "" )
                    										echo "<div id='deadline'><strong>Funding Deadline:</strong> " . $funding_deadline . "</div>";
                    								?>

                                    		<?php 	ih_display_funding_bar(wpsc_the_product_id());	?>

                    						</div><!--close custom_meta-->


                                                  <?php endif; ?>						

                          <?php //ideahack end of custom meta?>					
						
						
									
						<?php do_action('wpsc_product_before_description', wpsc_the_product_id(), $wp_query->post); ?>
						<div class="product_description">
							<h4><?php echo wpsc_the_product_title(); ?>'s story:</h4>
								
							<?php echo wpsc_the_product_description(); ?>
						</div><!--close product_description -->
						<?php do_action( 'wpsc_product_addons', wpsc_the_product_id() ); ?>		
						<?php if ( wpsc_the_product_additional_description() ) : ?>
							<div class="single_additional_description">
								<p><?php echo wpsc_the_product_additional_description(); ?></p>
							</div><!--close single_additional_description-->
						<?php endif; ?>		
						<?php do_action( 'wpsc_product_addon_after_descr', wpsc_the_product_id() ); ?>

					
					</div><!--close productcol-->
		
					<form onsubmit="submitform(this);return false;" action="<?php echo wpsc_this_page_url(); ?>" method="post" name="product_<?php echo wpsc_the_product_id(); ?>" id="product_extra_<?php echo wpsc_the_product_id(); ?>">
						<input type="hidden" value="<?php echo wpsc_the_product_id(); ?>" name="prodid"/>
						<input type="hidden" value="<?php echo wpsc_the_product_id(); ?>" name="item"/>
					</form>
		</div><!--close single_product_display-->


<div><!-- ideahack users who are currently donating -->


	    <?php
	  	  $contributor_ids = get_post_meta( wpsc_the_product_id(), 'Current Contributions', true);
	  	    // var_dump($contributor_ids);
	    	  $contributors = explode(",", $contributor_ids);
			  $contributors = array_unique($contributors);
	    	// var_dump($contributor_id);
	    	  	if(!empty($contributor_ids)){
				echo "<h2> Current Contributors </h2>";
				foreach ($contributors as $contributor){ ?>
	    			<div class="donatedstudent" style="width:150px;float:left;">
	    				<a href="<?php echo bp_core_get_user_domain($contributor) ?>">
	    					<?php  
	    					  $size = 120;
	    					  // get the avatar image
	    					  $avatarURL = bp_theme_avatar_url($size,$size,'', bp_core_fetch_avatar(array( 'item_id' => $contributor, 'type' => 'full', 'html' => 'false', 'width' => $size, 'height' => $size )) );
	    					  echo '<div class="avatar" style="background-image: url(\''.$avatarURL.'\'); width:'.$size.'px; height:'.$size.'px; margin: 10px; "></div>';  
	    					?>
	    				</a>
	    				<!-- display names under avatars -->
	    				<div class="user-names" style="text-align:center;">
	    				<?php
	    					$getfullname = bp_core_get_user_displayname($contributor);
	    					$getfirstname = explode(" ", $getfullname);
	    					echo $getfirstname[0]; 
						?>
	    				</div>

	    			</div><!-- #item-header-avatar -->
	  	    <?php 
			}
		}
	  	   ?>

		</div><!-- ideahack close users who are currently donating -->

	<div><!-- ideahack users who have donated -->
		<div style="clear:both;"></div>
		

		<!-- display the avatar -->


    <?php
  	  $contributor_ids = get_post_meta( wpsc_the_product_id(), 'Previous Contributions', true);

		// $contributor_ids = get_post_meta( wpsc_the_product_id(), 'contributor');
		// var_dump($contributor_ids);

    	  $contributors = explode(",", $contributor_ids);
		  $contributors = array_unique($contributors);
    	  //var_dump($contributor_id);
    	  if(!empty($contributor_ids)){
		  echo "<h2>Past Contributors</h2>";
		  foreach ($contributors as $contributor){ ?>
    			<div class="donatedstudent" style="width:150px;float:left;">
    				<a href="<?php echo bp_core_get_user_domain($contributor) ?>">
    					<?php  
    					  $size = 120;
    					  // get the avatar image
    					  $avatarURL = bp_theme_avatar_url($size,$size,'', bp_core_fetch_avatar(array( 'item_id' => $contributor, 'type' => 'full', 'html' => 'false', 'width' => $size, 'height' => $size )) );
    					  echo '<div class="avatar" style="background-image: url(\''.$avatarURL.'\'); width:'.$size.'px; height:'.$size.'px; margin: 10px; "></div>';  
    					?>
    				</a>
    				<!-- display names under avatars -->
    				<div class="user-names" style="text-align:center;">
    				<?php
    					$getfullname = bp_core_get_user_displayname($contributor);
    					$getfirstname = explode(" ", $getfullname);
    					echo $getfirstname[0];
    				?>
    				</div>
    			</div><!-- #item-header-avatar -->
  	    <?php 
		}
	}
  	  ?>
		
	</div><!-- ideahack close users who have donated-->

<?php endwhile;

do_action( 'wpsc_theme_footer' ); ?> 	

</div><!--close single_product_page_container-->
