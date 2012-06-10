<?php

//ideahack functions.


//edgen_email

define("EDGEN_EMAIL", "raden.andre_gmail.com");
//list all subpages (for partner pages)
add_shortcode('list_all_subpages', 'ih_list_all_subpages');


// ideahack redirecting students page to search page.

add_action('get_header', 'ih_redirect_students');
add_action('get_header', 'ih_redirect_gift_cards');
add_action('get_header', 'ih_redirect_partners');

function get_student_id($old_id) {
     $args = array(    
     'post_type' => 'wpsc-product',
     'meta_key' => 'old_id',
     'meta_value'=> $old_id
     ); 
     query_posts($args);
     while ( have_posts() ) : the_post();
           $our_id = get_the_ID();
     endwhile;
     return $our_id;
}
function get_user_id($old_user_id) {
          $our_user = get_users(array('meta_key' => 'old_user_id', 'meta_value' => $old_user_id));
     return $our_user[0] ->ID;
}

function add_the_products($student){
  $student_post_meta = get_post_meta($student, 'contributor');
	delete_post_meta($student, 'contributor');
  if(!is_array($student_post_meta[0])) $student_post_meta = array(array($student_post_meta));
	$student_year_meta = 0;
	$student_post_meta[0][$student_year_meta][] = '785';
	add_post_meta($student, 'contributor', $student_post_meta[0]);
}


function add_the_students(){
  $student1 = get_student_id('1290372876');
  $student2 = get_student_id('1292893205');
  $student3 = get_student_id('1238069150');
  $student4 = get_student_id('1234133027');
  $student5 = get_student_id('1309998632');
  $student6 = get_student_id('1310170390');
  add_user_meta(785, 'items_purchased', $student1);
  add_user_meta(785, 'items_purchased', $student2);
  add_user_meta(785, 'items_purchased', $student3);
  add_user_meta(785, 'items_purchased', $student4);
  add_user_meta(785, 'items_purchased', $student5);
  add_user_meta(785, 'items_purchased', $student6);
  add_the_products($student1);
  add_the_products($student2);
  add_the_products($student3);
  add_the_products($student4);
  add_the_products($student5);
  add_the_products($student6);
}



function ih_redirect_students() {
	if ( "/students/" == $_SERVER['REQUEST_URI'] ){
				wp_redirect('/?search-class=DB_CustomSearch_Widget-db_customsearch_widget&widget_number=preset-default&all-1=wpsc-product&cs-Country-0=&cs-Gender-2=&cs-Active-3=true&search=Search');
		exit;
	} 
}

function ih_redirect_gift_cards(){
  if ( "/gift-cards/" == $_SERVER['REQUEST_URI'] && $_GET['view_type'] != 'grid'){
				wp_redirect('/gift-cards/?view_type=grid');
		exit;
	}
}

// temporary function to fix the partner page with the "sold out" student - issue seems to have resolved itself but leaving it in for now

function ih_redirect_partners(){
  if ( "/partners/mosqoy/" == $_SERVER['REQUEST_URI'] && $_GET['view_type'] != 'default'){
				wp_redirect('/partners/mosqoy/?view_type=default');
		exit;
	}
}




// Function that will return our Wordpress menu
function list_menu($atts, $content = null) {
	extract(shortcode_atts(array(  
		'menu'            => '', 
		'container'       => 'div', 
		'container_class' => '', 
		'container_id'    => '', 
		'menu_class'      => 'menu', 
		'menu_id'         => '',
		'echo'            => true,
		'fallback_cb'     => 'wp_page_menu',
		'before'          => '',
		'after'           => '',
		'link_before'     => '',
		'link_after'      => '',
		'depth'           => 0,
		'walker'          => '',
		'theme_location'  => ''), 
		$atts));
 
 
	return wp_nav_menu( array( 
		'menu'            => $menu, 
		'container'       => $container, 
		'container_class' => $container_class, 
		'container_id'    => $container_id, 
		'menu_class'      => $menu_class, 
		'menu_id'         => $menu_id,
		'echo'            => false,
		'fallback_cb'     => $fallback_cb,
		'before'          => $before,
		'after'           => $after,
		'link_before'     => $link_before,
		'link_after'      => $link_after,
		'depth'           => $depth,
		'walker'          => $walker,
		'theme_location'  => $theme_location));
}
//Create the shortcode
add_shortcode("listmenu", "list_menu");


//disable admin bar
define ( 'BP_DISABLE_ADMIN_BAR', true );


/**
 * helper function for ih_content to close tags
 */

function ih_close_tags($text) {

    $patt_open    = "%((?<!</)(?<=<)[\s]*[^/!>\s]+(?=>|[\s]+[^>]*[^/]>)(?!/>))%";

    $patt_close    = "%((?<=</)([^>]+)(?=>))%";

    if (preg_match_all($patt_open,$text,$matches))

    {

        $m_open = $matches[0];

        if(!empty($m_open))

        {

            preg_match_all($patt_close,$text,$matches2);

            $m_close = $matches2[1];

            if (count($m_open) > count($m_close))

            {

                $m_open = array_reverse($m_open);

                foreach ($m_close as $tag) $c_tags[$tag]++;

                foreach ($m_open as $k => $tag)    if ($c_tags[$tag]--<=0) $text.='</'.$tag.'>';

            }

        }

    }

    return $text;

}

/**
 * Seeing as WordPress doesn't support product id in excerpt, we had to create 
 * our own excerpt using a custom function. 
 */

	function ih_content($theContent, $num, $more_link_text = 'LEARN MORE', $post_id = 0) {  

	//$theContent = get_the_content($more_link_text);  
	$return_value = "";
	$output = preg_replace('/<img[^>]+./','', $theContent);  

	$limit = $num + 1;  

	$content = explode(' ', $output, $limit);  

	array_pop($content);  

	$content = implode(" ",$content);  

    $content = strip_tags($content, '<p><a><address><a><abbr><acronym><b><big><blockquote><br><caption><cite><class><code><col><del><dd><div><dl><dt><em><font><h1><h2><h3><h4><h5><h6><hr><i><img><ins><kbd><li><ol><p><pre><q><s><span><strike><strong><sub><sup><table><tbody><td><tfoot><tr><tt><ul><var>');

      $return_value .= ih_close_tags($content) . "...";

      $return_value .= "<p><a id='readstory' href='";

      $return_value .= get_permalink($post_id);

      $return_value .= "'>".$more_link_text."</a></p>";

	return $return_value;
}

//list sub pages for partner page
function ih_list_all_subpages( $atts ) {
	extract( shortcode_atts( array(
		'page_id' => '1'
	), $atts ) );

	$args = array(
	    'child_of'     => 4879
 		);
	
	// $output = "<ul> <li> hello world </li>";
	$pages_array = get_pages( $args ); 
	//var_dump($pages_array);
	foreach( $pages_array as $page ) {
		$output .= "<div id='partner'><a id='partnerlink' href='". get_page_link( $page->ID ) . "'>" . $page->post_title . "</a>" . "<br />" . ih_content($page->post_content, 60,  $more_link_text = 'SEE THEIR STUDENTS', $page->ID) . "</div>"; // 
	}
	// $output .= "</ul>";	 
	return $output ;
}

add_image_size('frontpage', 100, 100, true); // thumbnails


function ih_display_funding_bar($product_id) {
	
	$funding_needed = get_post_meta($product_id, 'Funding Needed', true);					
	$funding_raised = get_post_meta($product_id, 'Funding Received', true);						
		
	if ( $funding_needed != ""  && $funding_needed != "0"  && $funding_needed != NULL) {			
		$progress = (($funding_raised + 1.0) /$funding_needed) *100;
		if($progress > 100)  $progress = 100;
		
		echo "<div class='meter'><span style='width: $progress%'></span></div>" ;
		echo "<div id='raised'> $" . $funding_raised . " raised out of $" . $funding_needed . "</div>"  ;
	} else if ($funding_raised == "" || funding_raised == NULL) {
	
	}
}

function myavatar_add_default_avatar( $url )
{

return get_stylesheet_directory_uri() .'/img/edgenlogo.png';
}
add_filter( 'bp_core_mysteryman_src', 'myavatar_add_default_avatar' );

function ih_display_urgent() {
	$studentexpdate = get_post_meta(wpsc_the_product_id(), 'Expiration Date (yyyy/mm/dd)', true);						
	$today_date = date('Y/m/d');
	$days_to_expiration = (strtotime($studentexpdate) - strtotime($today_date)) / 86400;

	if ($days_to_expiration <= 3 && $days_to_expiration > 0) {
		echo "<div class='urgent' style='float:right;background:#FF9933;padding:10px;color:#f5f5f5;text-transform:uppercase;'>Urgent - Deadline Approaching</div>";
	}
}

add_action( 'login_enqueue_scripts', 'w4_login_enqueue_scripts' );

function w4_login_enqueue_scripts(){
echo '<style type="text/css" media="screen">';
echo '#login h1 a{background-image:url(http://educationgeneration.org/wp-content/uploads/2011/10/logo.png);';
echo 'width: 369px;';
echo 'height: 87px;';
echo '</style>';
}

function ih_update_students($purchase_id){
  global $wpdb;
  $purchase_log = $wpdb->get_row( "SELECT * FROM `" . WPSC_TABLE_PURCHASE_LOGS . "` WHERE `id` = " . absint( $purchase_id ) . " LIMIT 1" , ARRAY_A );
  
  $cart = $wpdb->get_results( "SELECT * FROM `" . WPSC_TABLE_CART_CONTENTS . "` WHERE `purchaseid` = '{$purchase_log['id']}'" , ARRAY_A );
  
 foreach ($cart as $row){
   $student_post_meta = get_post_meta($row['prodid'], 'Current Contributions', true);  
   if (strlen($student_post_meta > 0)) $student_post_meta .= ",";
   $student_post_meta .= $purchase_log['user_ID'];
   update_post_meta($row['prodid'], 'Current Contributions', $student_post_meta);
   add_user_meta( $purchase_log['user_ID'], 'items_purchased', $row['prodid'], false );
 
   //ideahack increment funding received.
   $funding_needed = get_post_meta($row['prodid'], 'Funding Needed', true)  ;
   if ($funding_needed){
     $funding_received = get_post_meta($row['prodid'], 'Funding Received', true)  ;
     $price = get_post_meta( $row['prodid'], '_wpsc_price', true );
     $quantity = $row['quantity'];
     $new_funding = $funding_received + (($price * $quantity));
     if ($new_funding >= $funding_needed){
       ih_email_contributors($row['prodid'], "fully_funded");
       ih_deactivate_student($row['prodid']);
     }
     if ($new_funding > $funding_needed){
       wp_mail('info@educationgeneration.org', 'Warning: student overfunded', "$row[name] has recieved $ $new_funding. She needed $ $funding_needed");
     }
     update_post_meta($row['prodid'],  'Funding Received', $new_funding);
     $user_info = get_userdata($purchase_log['user_ID']);
     $username = $user_info->user_login;
     $user_email = $user_info->user_email;
     wp_mail('raden.andre@gmail.com', 'Student received new donation', "$row[name] has recieved $ $new_funding. She was donated to  $row[quantity] time. She needed $ $funding_needed. The contributor was user ID $purchase_log[user_ID] and username of $username and email of $user_email");
     //end ideahack
   }
 }
}

function ih_combine_contributors($student_id){
   $current_contributions = get_post_meta($student_id, 'Current Contributions', true);
   $previous_contributions = get_post_meta($student_id, 'Previous Contributions', true);
   $previous_contributions .= "," . $current_contributions;
   update_post_meta($student_id, 'Current Contributions', "");
   update_post_meta($student_id, 'Previous Contributions', $previous_contributions);
}

function ih_deactivate_student($student_id){
   update_post_meta($student_id,  'Active', 'false');
   wp_mail("raden.andre@gmail.com", '[edgen_system] Student Deactivated', get_the_title($student_id) . " has been deactivated");
}

function ih_disable_validation( $user_id ) {
  global $wpdb;
  $wpdb->query( $wpdb->prepare( "UPDATE $wpdb->users SET user_status = 0 WHERE ID = %d", $user_id ) );
  wp_set_auth_cookie($user_id);
}
add_action( 'bp_core_signup_user', 'ih_disable_validation' );

function ih_fix_signup_form_validation_text() {
  return false;
}
add_filter( 'bp_registration_needs_activation', 'ih_fix_signup_form_validation_text' );
//This function should be "update contributors" or someting.
function ih_email_contributors($post_id, $email_status = "") {
  if ($email_status == "") $email_status = get_post_meta($post_id, 'Send Email', true);
  $name = get_the_title($post_id);
  if($email_status == "updated"){
    $recipients = get_post_meta($post_id, 'Current Contributions', true);
    $content = ih_get_email_content(15);
  }elseif($email_status == "fully_funded"){
    $recipients = get_post_meta($post_id, 'Current Contributions', true);
    $content = ih_get_email_content(16);
  } elseif($email_status == "reposted"){
    ih_combine_contributors($post_id);
    $recipients = get_post_meta($post_id, 'Previous Contributions', true);
    $recipients.= "," . get_post_meta($post_id, 'Current Contributions', true);
    $content = ih_get_email_content(17);
  }
  $recipients = explode(",", $recipients);
  $recipients = array_unique($recipients);
  foreach ($recipients as $recipient_id){
    $replaced_content = ih_replace_email_content($content[1], $post_id, $recipient_id);
    wp_mail("raden.andre@gmail.com", $content[0] , $replaced_content, "content-type: text/html");
  }
  update_post_meta($post_id,  'Send Email', 'none');
}

function ih_replace_email_content($content, $post_id, $recipient){
  $the_post = get_post($post_id);
  $the_user = get_userdata($recipient);
  $name = $the_post->post_title;
  $site_url = site_url();
  $permalink = $site_url . "/?p=" .$post_id; 
  $string1 = "<a href='$permalink'>$name</a>";
  $string2 = $the_user->display_name;
  $string3 = "<a href='$site_url/students'>educationgeneration.org";
  $content = str_replace("%s1", $string1, $content);
  $content = str_replace("%s2", $string2, $content);
  $content = str_replace("%s3", $string3, $content);
  return $content;
}
function ih_get_email_content($number){
  $post_array = get_posts(array("post_type" => "dpw_email", "meta_key" => "welcomepack_type", "meta_value" => $number));
  return array( $post_array[0]-> post_title, $post_array[0] -> post_content);
}

add_action('wp_insert_post', 'ih_email_contributors');

function ih_add_email_meta_boxes($current_email_type){
echo  '<option value="15"' . selected( $current_email_type, 15 ) . '>Student Updated</option>';
echo  '<option value="16"' . selected( $current_email_type, 16 ) . '>Student Fully Funded</option>';
echo  '<option value="17"' . selected( $current_email_type, 17 ) . '>Student Reposted</option>';
}

function ih_add_email_types($emails){
  
  $emails['Student Updated']  = 15;
  $emails['Student Fully Funded']  = 16;
  $emails['Student Reposted']  = 17;
  return $emails;
 }

function ih_add_email_fields($message){
  global $current_user;
  $message = str_replace( '%user_name%', $current_user->display_name, $message );
  return $message;
}
add_action('dpw_email_meta_box', 'ih_add_email_meta_boxes');
add_filter('dpw_email_get_types', 'ih_add_email_types');
add_filter('wpsc_transaction_result_message_html', 'ih_add_email_fields');
add_filter('wpsc_transaction_result_message', 'ih_add_email_fields');
