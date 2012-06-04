<?php
function comment_author_avatar() {
	global $comment, $_avatar;
	echo apply_filters( 'comment_author_avatar', $_avatar->fetch( array( 'item_id' => $comment->user_id, 'type' => 'thumb' ) ) );
}

function post_author_avatar( $args ) {
	global $post, $_avatar;
    $defaults = array( 'item_id' => $post->post_author, 'type' => 'thumb' );
    $params = wp_parse_args( $args, $defaults );
	echo apply_filters( 'post_author_avatar', $_avatar->fetch( $params ) );
}

function loggedin_user_avatar( $args = '' ) {
	echo get_loggedin_user_avatar( $args );
}

function get_loggedin_user_avatar( $args = '' ) {
    global $_avatar;
    $current_user = wp_get_current_user();
    $user_id = $current_user->ID;          
    
    $defaults = array(
        'type'		=> 'thumb',
        'width'		=> false,
        'height'	=> false,
        'html'		=> true
    );

    $r = wp_parse_args( $args, $defaults );
    extract( $r, EXTR_SKIP );

    return apply_filters( 'get_loggedin_user_avatar', $_avatar->fetch( array( 'item_id' => $user_id, 'type' => $type, 'width' => $width, 'height' => $height, 'html' => $html ) ) );
}

function avatar_to_crop() {
	echo get_avatar_to_crop();
}

function get_avatar_to_crop() {
    global $_avatar;
    return apply_filters( 'get_avatar_to_crop', get_transient( $_avatar->url_transient ) );
}

function avatar_to_crop_src() {
	echo get_avatar_to_crop_src();
}

function get_avatar_to_crop_src() {
    global $_avatar;    
    return apply_filters( 'get_avatar_to_crop_src', str_replace( WP_CONTENT_DIR, '', get_transient( $_avatar->dir_transient ) ) );
}

function avatar_cropper() {
    global $_avatar;    
	echo '<img id="avatar-to-crop" class="avatar" src="' . get_transient( $_avatar->url_transient ) . '" />';
}

function avatar_delete_link( $id = 0 ) {
	echo get_avatar_delete_link( $id );
}

function get_avatar_delete_link( $id = 0 ) {
    global $_avatar;
    return apply_filters( 'get_avatar_delete_link', add_query_arg( 'delete-avatar', $id, admin_url( $_avatar->get_redirect( $id ) ) ) );
}

function get_user_has_avatar() {
    global $_avatar;
    if ( isset( $_GET['user_id'] ) ) {
        $item_id = $_GET['user_id'];
    } else {
        $current_user = wp_get_current_user();
        $item_id = $current_user->ID;  
    }
	if ( !$_avatar->fetch( array( 'item_id' => $item_id, 'no_grav' => true ) ) )
		return false;

	return true;
}

function avatar_profile_admin( $profileuser ) {
    global $_avatar;
?>
<tr class="profile-avatar-editor" id="go-to-avatar">
    <th scope="row"><?php _e( 'Change Avatar' ) ?></th>
    <td>
        <style type="text/css"> 
        .profile-avatar-editor p {margin-bottom: 20px; width: 75%}
        #the-profile-image img {border: 1px solid #b1b1b1; padding: 7px; background: #fefefe; display: block; margin: 20px 0}
        </style>
    <p><?php _e( 'Your avatar will be used on your profile and throughout the site. If there is a <a href="http://gravatar.com">Gravatar</a> associated with your account email we will use that, or you can upload an image from your computer.', 'buddypress') ?></p>

    <?php if ( isset( $_GET['step'] ) && 'crop-image' === $_GET['step'] ) : ?>

        <h5><?php _e( 'Crop Your New Avatar' ) ?></h5>

        <img src="<?php avatar_to_crop() ?>" id="avatar-to-crop" class="avatar" alt="<?php _e( 'Avatar to crop' ) ?>" />

        <div id="avatar-crop-pane">
            <img src="<?php avatar_to_crop() ?>" id="avatar-crop-preview" class="avatar" alt="<?php _e( 'Avatar preview' ) ?>" />
        </div>

        <input type="submit" name="avatar-crop-submit" id="avatar-crop-submit" value="<?php _e( 'Crop Image' ) ?>" />

        <input type="hidden" name="image_src" id="image_src" value="<?php avatar_to_crop_src() ?>" />
        <input type="hidden" id="x" name="x" />
        <input type="hidden" id="y" name="y" />
        <input type="hidden" id="w" name="w" />
        <input type="hidden" id="h" name="h" />

    <?php else : ?>
        <div id="the-profile-image">        
        <?php echo $_avatar->fetch( array( 'item_id' => $profileuser->ID, 'type' => 'full' ) ) ?>
        </div>
        <p><?php _e( 'Click below to select a JPG, GIF or PNG format photo from your computer and then click \'Upload Image\' to proceed.' ) ?></p>

        <p id="avatar-upload">
            <input type="file" name="file" id="file" />
            <input type="submit" name="upload" id="upload" value="<?php _e( 'Upload Image') ?>" />
        </p>

        <?php if ( get_user_has_avatar() ) : ?>
            <p><?php _e( "If you'd like to delete your current avatar but not upload a new one, please use the delete avatar button." ) ?></p>
            <p><a class="button edit" href="<?php avatar_delete_link( $profileuser->ID ) ?>" title="<?php _e( 'Delete Avatar' ) ?>"><?php _e( 'Delete My Avatar' ) ?></a></p>
        <?php endif; ?>

    <?php endif; ?>        
    </td>
</tr>
<?php
}