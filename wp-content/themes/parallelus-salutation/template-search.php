<?php

/*  
	Search Page Content
	------------------
	The search page content will retrieve the results and apply them using the blog template file.
	
	Search Page Layout
	-----------------
	The search page layout can be set in the "Appearance > Layouts" area. The content source can be 
	set from "Settings > Theme Settings".
	
*/ 

// Search term entered


// $student_sorting = get_post_meta(wpsc_the_product_id(), 'Expiration Date (yyyy/mm/dd)', true);
// var_dump($student_sorting);

//ideahack playing with the query
global $wp_query;	
	

global $query_string;

$query_args = explode("&", $query_string);

$search_query = array();

foreach($query_args as $key => $string) {
	$query_split = explode("=", $string);
	$search_query[$query_split[0]] = urldecode($query_split[1]);
	

} // foreach


$search = new WP_Query($search_query);
//var_dump($search);
global $query_string;


$image_width = get_option('product_image_width');
/*
 * Most functions called in this page can be found in the wpsc_query.php file
 */
?>




<div id="default_products_page_container" class="wrap wpsc_container">

<?php wpsc_output_breadcrumbs(); ?>
	
	<?php do_action('wpsc_top_of_products_page'); // Plugin hook for adding things to the top of the products page, like the live search ?>
	<?php if(wpsc_display_categories()): ?>
	  <?php if(wpsc_category_grid_view()) :?>
			<div class="wpsc_categories wpsc_category_grid group">
				<?php wpsc_start_category_query(array('category_group'=> get_option('wpsc_default_category'), 'show_thumbnails'=> 1)); ?>
					<a href="<?php wpsc_print_category_url();?>" class="wpsc_category_grid_item  <?php wpsc_print_category_classes_section(); ?>" title="<?php wpsc_print_category_name(); ?>">
						<?php wpsc_print_category_image(get_option('category_image_width'),get_option('category_image_height')); ?>
					</a>
					<?php wpsc_print_subcategory("", ""); ?>
				<?php wpsc_end_category_query(); ?>
				
			</div><!--close wpsc_categories-->
	  <?php else:?>
			<ul class="wpsc_categories">
			
				<?php wpsc_start_category_query(array('category_group'=>get_option('wpsc_default_category'), 'show_thumbnails'=> get_option('show_category_thumbnails'))); ?>
						<li>
							<?php wpsc_print_category_image(get_option('category_image_width'), get_option('category_image_height')); ?>
							
							<a href="<?php wpsc_print_category_url();?>" class="wpsc_category_link <?php wpsc_print_category_classes_section(); ?>" title="<?php wpsc_print_category_name(); ?>"><?php wpsc_print_category_name(); ?></a>
							<?php if(wpsc_show_category_description()) :?>
								<?php wpsc_print_category_description("<div class='wpsc_subcategory'>", "</div>"); ?>				
							<?php endif;?>
							
							<?php wpsc_print_subcategory("<ul>", "</ul>"); ?>
						</li>
				<?php wpsc_end_category_query(); ?>
			</ul>
		<?php endif; ?>
	<?php endif; ?>
<?php // */ ?>
	
	<?php // add in custom search functionality ideahack ?>
	<?php if(function_exists('wp_custom_fields_search')) 
		wp_custom_fields_search(); ?>
	<?php if(wpsc_display_products()): ?>
		
	
		<div class="wpsc_default_product_list">
		<?php /** start the product loop here */?>
		
		<?php
			//ideahack, modify query to only choose active students.

			// $args = array(
			// 	'post_type'=> 'wpsc-product',
			// 	'meta_query' => array(
			// 			array(
			// 				'key' => 'Active',
			// 				'value' => 'true',
			// 				'compare' => '='
			// 			)
			// 	),
			// 	'posts_per_page' => 3
			// );
			// 
			// query_posts($args);
		?>
		
		
		
		<?php while (wpsc_have_products()) :  wpsc_the_product(); ?>
		<?php //unpublish and ignore products where expiration date has past.
		$expiration_date = get_post_meta(wpsc_the_product_id(), 'Expiration Date (yyyy/mm/dd)', true);
		
		$expiration_date = strtotime($expiration_date);
		$current_date = strtotime(date("Y/m/d"));
    if(($current_date > $expiration_date) && (get_post_meta(wpsc_the_product_id(),"Active", true) == "true")){
      ih_deactivate_student(wpsc_the_product_id());
    }
		?>	
			<div class="default_product_display product_view_<?php echo wpsc_the_product_id(); ?> <?php echo wpsc_category_class(); ?> group">   
				<h2 class="prodtitle entry-title">
							<?php if(get_option('hide_name_link') == 1) : ?>
								<?php echo wpsc_the_product_title(); ?>
							<?php else: ?> 
								<a class="wpsc_product_title" href="<?php echo wpsc_the_product_permalink(); ?>"><?php echo wpsc_the_product_title(); ?></a>
								<?php
								ih_display_urgent();
								?>
								
							<?php endif; ?>
						</h2>   
				<?php if(wpsc_show_thumbnails()) :?>
					<div class="imagecol" style="width:<?php echo $image_width; ?>;" id="imagecol_<?php echo wpsc_the_product_id(); ?>">
						<?php if(wpsc_the_product_thumbnail()) :
						?>
							<a rel="<?php echo wpsc_the_product_title(); ?>" class="<?php echo wpsc_the_product_image_link_classes(); ?>" href="<?php echo wpsc_the_product_permalink(); ?>">

						<?php echo get_the_post_thumbnail( wpsc_the_product_id() );	?>
							</a>
						<?php else: ?>
								<a href="<?php echo wpsc_the_product_permalink(); ?>">
								<img class="no-image" id="product_image_<?php echo wpsc_the_product_id(); ?>" alt="No Image" title="<?php echo wpsc_the_product_title(); ?>" src="<?php echo WPSC_CORE_THEME_URL; ?>wpsc-images/noimage.png" width="<?php echo get_option('product_image_width'); ?>" height="<?php echo get_option('product_image_height'); ?>" />	
								</a>
								
								
								
						<?php 						
						endif; ?>
						

						
						<?php
						if(gold_cart_display_gallery()) :					
							echo gold_shpcrt_display_gallery(wpsc_the_product_id(), true);
						endif;
						?>	
					</div><!--close imagecol-->
				<?php endif; ?>
				
				
					<div class="productcol" style="margin-left:<?php echo $image_width + 20; ?>px;" >
				
						
						<?php							
							do_action('wpsc_product_before_description', wpsc_the_product_id(), $wp_query->post);
							do_action('wpsc_product_addons', wpsc_the_product_id());
						?>
						
						
						<div class="wpsc_description">
							
                        </div><!--close wpsc_description-->
				
						<?php if(wpsc_the_product_additional_description()) : ?>
						<div class="additional_description_container">
							
								<img class="additional_description_button"  src="<?php echo WPSC_CORE_THEME_URL; ?>wpsc-images/icon_window_expand.gif" alt="Additional Description" /><a href="<?php echo wpsc_the_product_permalink(); ?>" class="additional_description_link"><?php _e('More Details', 'wpsc'); ?>
							</a>
							<div class="additional_description">
								<p><?php echo wpsc_the_product_additional_description(); ?></p>
							 
							</div><!--close additional_description-->
						</div><!--close additional_description_container-->
						<?php endif; ?>
						
						<?php if(wpsc_product_external_link(wpsc_the_product_id()) != '') : ?>
							<?php $action =  wpsc_product_external_link(wpsc_the_product_id()); ?>
						<?php else: ?>
						<?php $action = htmlentities(wpsc_this_page_url(), ENT_QUOTES, 'UTF-8' ); ?>					
						<?php endif; ?>					
						<form class="product_form"  enctype="multipart/form-data" action="<?php echo $action; ?>" method="post" name="product_<?php echo wpsc_the_product_id(); ?>" id="product_<?php echo wpsc_the_product_id(); ?>" >
						<?php do_action ( 'wpsc_product_form_fields_begin' ); ?>
						<?php /** the variation group HTML and loop */?>
                        <?php if (wpsc_have_variation_groups()) { ?>
                        <fieldset><legend><?php _e('Product Options', 'wpsc'); ?></legend>
						<div class="wpsc_variation_forms">
                        	<table>
							<?php while (wpsc_have_variation_groups()) : wpsc_the_variation_group(); ?>
								<tr><td class="col1"><label for="<?php echo wpsc_vargrp_form_id(); ?>"><?php echo wpsc_the_vargrp_name(); ?>:</label></td>
								<?php /** the variation HTML and loop */?>
								<td class="col2"><select class="wpsc_select_variation" name="variation[<?php echo wpsc_vargrp_id(); ?>]" id="<?php echo wpsc_vargrp_form_id(); ?>">
								<?php while (wpsc_have_variations()) : wpsc_the_variation(); ?>
									<option value="<?php echo wpsc_the_variation_id(); ?>" <?php echo wpsc_the_variation_out_of_stock(); ?>><?php echo wpsc_the_variation_name(); ?></option>
								<?php endwhile; ?>
								</select></td></tr> 
							<?php endwhile; ?>
                            </table>
						</div><!--close wpsc_variation_forms-->
                        </fieldset>
						<?php } ?>
						<?php /** the variation group HTML and loop ends here */?>
							
							
								<!-- ideahack custom student meta-->
								<div id="studentmeta">
									<?php
										$birthdate = get_post_meta(wpsc_the_product_id(), 'Birthdate (yyyy/mm/dd)', true);
										$country = get_post_meta(wpsc_the_product_id(), 'Country', true);
										$area_of_study = get_post_meta(wpsc_the_product_id(), 'Program Title', true);
										$funding_deadline = get_post_meta(wpsc_the_product_id(), 'Expiration Date (yyyy/mm/dd)', true);
										$funding_needed = get_post_meta(wpsc_the_product_id(), 'Funding Needed', true);					
										if ( $birthdate != "" )
											echo "<div id='birthdate'><strong>Birthdate:</strong> " . $birthdate . "</div>";
										if ( $country != "" )
											echo "<div id='country'><strong>Country:</strong> " . $country . "</div>";
										if ( $area_of_study != "" )
											echo "<div id='areaofstudy'><strong>Area of Study:</strong> " . $area_of_study . "</div>";
										if ( $funding_deadline != "" )
											echo "<div id='deadline'><strong>Funding Deadline:</strong> " . $funding_deadline . "</div>";
									

										ih_display_funding_bar(wpsc_the_product_id()); // display the progress bar

              			?>
									
        						<?php if ( 'true' == get_post_meta(wpsc_the_product_id(), 'Active', true) ): ?>
        							<?php if((get_option('hide_addtocart_button') == 0) &&  (get_option('addtocart_or_buynow') !='1')) : ?>
        								<?php if(wpsc_product_has_stock()) : ?>
        									<div class="wpsc_buy_button_container">
        										<div class="wpsc_loading_animation">
        											<img title="Loading" alt="Loading" src="<?php echo wpsc_loading_animation_url(); ?>" />
        											<?php _e('Updating cart...', 'wpsc'); ?>
        										</div><!--close wpsc_loading_animation-->
        										<fieldset style="float:  left; margin-right: 20px;">
              							  <div class="wpsc_quantity_update">
              								  <?php ih_quantity_display( wpsc_the_product_id() ); ?>
              									<input type="hidden" name="key" value="<?php echo wpsc_the_cart_item_key(); ?>"/>
              									<input type="hidden" name="wpsc_update_quantity" value="true" />
              							  </div><!--close wpsc_quantity_update-->
                            </fieldset>
        										<input type="submit" value="<?php _e('Donate', 'wpsc'); ?>" name="Buy" class="wpsc_buy_button" id="product_<?php echo wpsc_the_product_id(); ?>_submit_button"/> 
														<!--// ideahack: changed add to cart to donate -->
              							<?php echo wpsc_buy_now_button(wpsc_the_product_id()); ?>
        									</div><!--close wpsc_buy_button_container-->
        								<?php endif ; ?>
        							<?php endif ; ?>					
										<?php endif ; ?>														
								</div>
								
								<div id="desctext">
									<?php the_excerpt(); ?> 
									<p><a id='readstory' href="<?php echo wpsc_the_product_permalink(); ?>">LEARN MORE</a></p>
								</div>
							
													

							<div class="entry-utility wpsc_product_utility">
								<?php edit_post_link( __( 'Edit', 'wpsc' ), '<span class="edit-link">', '</span>' ); ?>
							</div>						
							
							
							




						
						


						<div class="wpsc_product_price">
							<?php if( wpsc_show_stock_availability() ): ?>
								<?php if(wpsc_product_has_stock()) : ?>
									<div id="stock_display_<?php echo wpsc_the_product_id(); ?>" class="in_stock"><?php _e('Product in stock', 'wpsc'); ?></div>
								<?php else: ?>
									<div id="stock_display_<?php echo wpsc_the_product_id(); ?>" class="out_of_stock"><?php _e('Product not in stock', 'wpsc'); ?></div>
								<?php endif; ?>
							<?php endif; ?>
							<?php if(wpsc_product_is_donation()) : ?>
								<label for="donation_price_<?php echo wpsc_the_product_id(); ?>"><?php _e('Donation', 'wpsc'); ?>: </label>
								<input type="text" id="donation_price_<?php echo wpsc_the_product_id(); ?>" name="donation_price" value="<?php echo wpsc_calculate_price(wpsc_the_product_id()); ?>" size="6" />

							<?php else : ?>
								<p class="pricedisplay product_<?php echo wpsc_the_product_id(); ?>"><?php _e('Price', 'wpsc'); ?>: <span id='product_price_<?php echo wpsc_the_product_id(); ?>' class="currentprice pricedisplay"><?php echo wpsc_the_product_price(); ?></span></p>

								
						
							<?php endif; ?>
						</div><!--close wpsc_product_price-->
						
						<input type="hidden" value="add_to_cart" name="wpsc_ajax_action"/>
						<input type="hidden" value="<?php echo wpsc_the_product_id(); ?>" name="product_id"/>
				
						<!-- END OF QUANTITY OPTION -->
					<?php do_action ( 'wpsc_product_form_fields_end' ); ?>
					</form><!--close product_form-->							
						
					<?php // */ ?>
				</div><!--close productcol-->
			<?php if(wpsc_product_on_special()) : ?><span class="sale"><?php _e('Sale', 'wpsc'); ?></span><?php endif; ?>
		</div><!--close default_product_display-->

		<?php endwhile; ?>
		<?php /** end the product loop here */?>
		</div>
		<?php if(wpsc_product_count() == 0):?>
			<h3><?php  _e('There are no products in this group.', 'wpsc'); ?></h3>
		<?php endif ; ?>
	    <?php do_action( 'wpsc_theme_footer' ); ?> 	

		<?php if(wpsc_has_pages_bottom()) : ?>
			<div class="wpsc_page_numbers_bottom">
			<?php  previous_posts_link(); ?>
				<?php next_posts_link("Next Page");?>
			</div><!--close wpsc_page_numbers_bottom-->
		<?php endif; ?>
	<?php endif; ?>
</div><!--close default_products_page_container-->
