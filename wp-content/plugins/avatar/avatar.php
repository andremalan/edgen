<?php 
/*
Plugin Name: Avatar
Plugin URI: http://emusic.com
Description: Like the movie, but actually good. Allows you to edit a user's Avatar in the Profile / Edit User page of the admin panel. Filter's get_avatar() to return your uploaded avatar instead of your Gravatar. This plugin borrows a lot of code from BuddyPress
Author: wonderboymusic (after all of the BuddyPress contributors)
Author URI: http://scotty-t.com
Version: 0.1.3
*/
require( 'templatetags.php' );

class Avatar {
    var $notices;
    var $transient_prefix;
    var $dir_transient;
    var $url_transient;
    var $loaded_defines;
    
    function init() {
        $this->loaded_defines = false;
        $this->notices = array();     
        add_action( 'load-profile.php', array( $this, 'load' ) ); 
        add_action( 'load-user-edit.php', array( $this, 'load' ) );            
        add_filter( 'get_avatar', array( $this, 'avatar_filter' ), 10, 5 );
    }
    
    function defines() {
        define( 'AVATAR_UPLOAD_PATH', $this->upload_path() );
        define( 'AVATAR_URL', $this->avatar_url() );
        define( 'AVATAR_THUMB_WIDTH', 50 );
        define( 'AVATAR_THUMB_HEIGHT', 50 );
        define( 'AVATAR_FULL_WIDTH', 150 );
        define( 'AVATAR_FULL_HEIGHT', 150 );
        define( 'AVATAR_ORIGINAL_MAX_WIDTH', 450 );
        define( 'AVATAR_ORIGINAL_MAX_FILESIZE', 5120000 ); /* 5mb */
        define( 'AVATAR_DEFAULT', WP_CONTENT_URL . '/plugins/avatar/mystery-man.jpg' );
        define( 'AVATAR_DEFAULT_THUMB', WP_CONTENT_URL .  '/plugins/avatar/mystery-man-50.jpg' );        
    }
    
    function get_redirect( $user_id ) {
        $current_user = wp_get_current_user();
        if ( $current_user->ID !== $user_id ) {
            return "user-edit.php?user_id=$user_id";
        } else {
            return "profile.php";
        }
    }
    
    function load() {
        if ( !$this->loaded_defines ) {
            $this->defines();
            $this->loaded_defines = true;
        }
        
        if ( isset( $_REQUEST['user_id'] ) ) {
            $user_id = $_REQUEST['user_id'];
        } else {
            $current_user = wp_get_current_user();
            $user_id = $current_user->ID;   
        }         
        $this->transient_prefix = "avatar_user_{$user_id}_";
        $this->dir_transient = $this->transient_prefix . 'dir';
        $this->url_transient = $this->transient_prefix . 'url';   
        
        if ( isset( $_GET['step'] ) ) {
            switch ( $_GET['step'] ) {
            case 'crop-image':
                $this->notices[] = array( 'updated', __( '<strong>Crop Your Image</strong> Your avatar was uploaded successfully!', 'avatar' ) );
                wp_enqueue_script( 'jcrop', array( 'jquery' ) );
                add_action( 'admin_print_scripts', array( $this, 'cropper_inline_js' ), 1000 );
                add_action( 'admin_print_styles', array( $this, 'cropper_inline_css' ), 1000 );                 
                break;
            case 'crop-success':
                $this->notices[] = array( 'updated', __( '<strong>Success</strong> Your avatar was cropped successfully!', 'avatar' ) );
                break;
            }        
        }             
        
        if ( isset( $_GET['avatar-deleted'] ) ) {
            $this->notices[] = array( 'updated', __( 'Your avatar was deleted successfully!', 'avatar' ) );
        }
        
        if ( isset( $_GET['crop-error'] ) ) {
           	$this->notices[] = array( 'error', __( 'There was a problem cropping your avatar, please try  again', 'avatar' ) );        
        }
        
        if ( isset( $_GET['delete-avatar'] ) ) {
            if ( $this->delete_existing_avatar( array( 'item_id' => $_GET['delete-avatar'] ) ) ) {
                wp_redirect( add_query_arg( 'avatar-deleted', 1, admin_url( $this->get_redirect( $user_id ) ) ) );
                exit();
            } else {
                $this->notices[] = array( 'error', __( 'There was a problem deleting that avatar, please try again.', 'avatar' ) );
            }    
        }
        
        if ( !empty( $_FILES ) ) {        
            /* Pass the file to the avatar upload handler */
            if ( $this->handle_upload( $_FILES, array( $this, 'upload_dir' ) ) ) {
                wp_redirect( add_query_arg( 'step', 'crop-image#go-to-avatar', admin_url( $this->get_redirect( $user_id ) ) ) );
                exit();
            } else {
                $this->notices[] = array( 'error', __( 'There was a problem uploading your avatar, please try uploading it again', 'avatar' ) );
            }
        }

        /* If the image cropping is done, crop the image and save a full/thumb version */
        if ( isset( $_POST['avatar-crop-submit'] ) ) {
        	//print_r( $_POST ); die();

            $data = array( 
                'item_id'       => $user_id, 
                'original_file' => $_POST['image_src'], 
                'crop_x'        => $_POST['x'], 
                'crop_y'        => $_POST['y'], 
                'crop_w'        => $_POST['w'], 
                'crop_h'        => $_POST['h'] 
            );
            if ( !$this->handle_crop( $data ) ) {
                wp_redirect( add_query_arg( 'step', 'crop-image&crop-error#go-to-avatar', admin_url( $this->get_redirect( $user_id ) ) ) );
                exit();                
            } else {
                delete_transient( $this->dir_transient );
                delete_transient( $this->url_transient );
                wp_redirect( add_query_arg( 'step', 'crop-success#go-to-avatar', admin_url( $this->get_redirect( $user_id ) ) ) );
                exit();
            }
        }
        
        add_action( 'user_edit_form_tag', array( $this, 'form_tag' ) );
        add_action( 'admin_notices', array( $this, 'add_notices' ) );
        
        // in templatetags.php
        add_action( 'personal_options', 'avatar_profile_admin', 100, 1 );        
    }   
    
    function form_tag() {
        echo 'enctype="multipart/form-data"';        
    }    
    
    function add_notices() {
        foreach ( $this->notices as $notice ) {
            printf( '<div class="%s"><p>%s</p></div>', $notice[0], $notice[1] );
        }
    }
    
    function upload_path() {
        $upload_dir = wp_upload_dir();
        return apply_filters( 'avatar_upload_path', $upload_dir['basedir'] );
    }    
    
    function avatar_url() {
        $upload_dir = wp_upload_dir();
        return apply_filters( 'avatar_url', $upload_dir['baseurl'] );
    }   
    
    function upload_dir( $directory = false, $user_id = false ) {
        if ( !$user_id ) {
            if ( isset( $_REQUEST['user_id'] ) ) {
                $user_id = $_REQUEST['user_id'];
            } else {
                $current_user = wp_get_current_user();
                $user_id = $current_user->ID;                
            }
        }

        if ( !$directory )
            $directory = 'avatars';

        $path  = AVATAR_UPLOAD_PATH . '/avatars/' . $user_id;
        $newbdir = $path;

        if ( !file_exists( $path ) )
            @wp_mkdir_p( $path );

        $newurl  = AVATAR_URL . '/avatars/' . $user_id;
        $newburl = $newurl;
        $newsubdir = '/avatars/' . $user_id;

        return apply_filters( 'avatar_upload_dir', array( 'path' => $path, 'url' => $newurl, 'subdir' => $newsubdir, 'basedir' => $newbdir, 'baseurl' => $newburl, 'error' => false ) );
    }    
    
    function cropper_inline_js() {
        $image = apply_filters( 'inline_cropper_image', getimagesize( AVATAR_UPLOAD_PATH . get_transient( $this->dir_transient ) ) );
        $aspect_ratio = 1;

        // Calculate Aspect Ratio
        if ( (int) constant( 'AVATAR_FULL_HEIGHT' ) && ( (int) constant( 'AVATAR_FULL_WIDTH' ) != (int) constant( 'AVATAR_FULL_HEIGHT' ) ) )
            $aspect_ratio = (int) constant( 'AVATAR_FULL_WIDTH' ) / (int) constant( 'AVATAR_FULL_HEIGHT' );
    ?>

        <script type="text/javascript"> 
            jQuery(window).load( function(){
                jQuery('#avatar-to-crop').Jcrop({
                    onChange: showPreview,
                    onSelect: showPreview,
                    onSelect: updateCoords,
                    aspectRatio: <?php echo $aspect_ratio ?>,
                    setSelect: [ 50, 50, <?php echo $image[0] / 2 ?>, <?php echo $image[1] / 2 ?> ]
                });
            });

            function updateCoords(c) {
                jQuery('#x').val(c.x);
                jQuery('#y').val(c.y);
                jQuery('#w').val(c.w);
                jQuery('#h').val(c.h);
            };

            function showPreview(coords) {
                <?php 
                    echo "if ( parseInt(coords.w) > 0 ) {  \n";

                    printf( "var rx = %d / coords.w; ", AVATAR_FULL_WIDTH );
                    printf( "var ry = %d / coords.h; ", AVATAR_FULL_HEIGHT );
                    echo "jQuery('#avatar-crop-preview').css({ \n";
                        if ( $image ) {
                            printf( "width: Math.round(rx * %d) + 'px',  \n", $image[0] );
                            printf( "height: Math.round(ry * %d) + 'px',  \n", $image[1] );
                        } 
                        echo "marginLeft: '-' + Math.round(rx * coords.x) + 'px',  \n";
                        echo "marginTop: '-' + Math.round(ry * coords.y) + 'px'  \n";                       
                    echo "});  \n"; 
                echo "}  \n";
                ?>
            }
        </script>
    <?php
    }

    function cropper_inline_css() {
    ?>
        <style type="text/css">
            .jcrop-holder { float: left; margin: 0 20px 20px 0; text-align: left; }
            .jcrop-vline, .jcrop-hline { font-size: 0; position: absolute; background: white top left repeat url(/wp-content/plugins/buddypress/bp-core/images/Jcrop.gif ); }
            .jcrop-vline { height: 100%; width: 1px !important; }
            .jcrop-hline { width: 100%; height: 1px !important; }
            .jcrop-handle { font-size: 1px; width: 7px !important; height: 7px !important; border: 1px #eee solid; background-color: #333}
            .jcrop-tracker { width: 100%; height: 100%; }
            .custom .jcrop-vline, .custom .jcrop-hline { background: yellow; }
            .custom .jcrop-handle { border-color: black; background-color: #C7BB00; -moz-border-radius: 3px; -webkit-border-radius: 3px; }
            #avatar-crop-pane { width: <?php echo AVATAR_FULL_WIDTH ?>px; height: <?php echo AVATAR_FULL_HEIGHT ?>px; overflow: hidden; }
            #avatar-crop-submit { margin: 20px 0; }
            #avatar-upload-form img, #create-group-form img, #group-settings-form img { border: none !important; }
        </style>
    <?php
    }    

    function check_avatar_upload( $file ) {
        if ( isset( $file['error'] ) && $file['error'] )
            return false;

        return true;
    }

    function check_avatar_size($file) {
        if ( $file['file']['size'] > AVATAR_ORIGINAL_MAX_FILESIZE )
            return false;

        return true;
    }

    function check_avatar_type($file) {
        if ( ( !empty( $file['file']['type'] ) && !preg_match('/(jpe?g|gif|png)$/', $file['file']['type'] ) ) || 
            !preg_match( '/(jpe?g|gif|png)$/', $file['file']['name'] ) )
            return false;

        return true;
    }    
    
    function handle_upload( $file, $upload_dir_filter ) {
        /***
         * You may want to hook into this filter if you want to override this function.
         * Make sure you return false.
         */
        if ( !apply_filters( 'pre_avatar_handle_upload', true, $file, $upload_dir_filter ) )
            return true;

        require_once( ABSPATH . '/wp-admin/includes/image.php' );
        require_once( ABSPATH . '/wp-admin/includes/file.php' );

        $uploadErrors = array(
            0 => __( "There is no error, the file uploaded with success", 'avatar' ),
            1 => __( "Your image was bigger than the maximum allowed file size of: ", 'avatar' ) . size_format( AVATAR_ORIGINAL_MAX_FILESIZE ),
            2 => __( "Your image was bigger than the maximum allowed file size of: ", 'avatar' ) . size_format( AVATAR_ORIGINAL_MAX_FILESIZE ),
            3 => __( "The uploaded file was only partially uploaded", 'avatar' ),
            4 => __( "No file was uploaded", 'avatar' ),
            6 => __( "Missing a temporary folder", 'avatar' )
        );

        if ( !$this->check_avatar_upload( $file ) ) {
            $this->notices[] = array( 'error', sprintf( __( 'Your upload failed, please try again. Error was: %s', 'avatar' ), $uploadErrors[$file['error']] ) );
            return false;
        }

        if ( !$this->check_avatar_size( $file ) ) {
            $this->notices[] = array( 'error', sprintf( __( 'The file you uploaded is too big. Please upload a file under %s', 'avatar' ), size_format( AVATAR_ORIGINAL_MAX_FILESIZE ) ) );
            return false;
        }

        if ( !$this->check_avatar_type( $file ) ) {
            $this->notices[] = array( 'error', __( 'Please upload only JPG, GIF or PNG photos.', 'avatar' ) );
            return false;
        }

        /* Filter the upload location */
        add_filter( 'upload_dir', $upload_dir_filter, 10, 0 );
        $upload = wp_handle_upload( $file['file'], array( 'test_form' => true, 'action' => $_POST['action'] ) );

        /* Move the file to the correct upload location. */
        if ( !empty( $upload['error'] ) ) {
            $this->notices[] = array( 'error', sprintf( __( 'Upload Failed! Error was: %s', 'avatar' ), $upload['error'] ) );
            return false;
        }

        /* Get image size */
        $size = @getimagesize( $upload['file'] );

        /* Check image size and shrink if too large */
        if ( $size[0] > AVATAR_ORIGINAL_MAX_WIDTH ) {
            $thumb = wp_create_thumbnail( $upload['file'], AVATAR_ORIGINAL_MAX_WIDTH );

            /* Check for thumbnail creation errors */
            if ( is_wp_error( $thumb ) ) {
                $this->notices[] = array( 'error', sprintf( __( 'Upload Failed! Error was: %s', 'avatar' ), $thumb->get_error_message() ) );
                return false;
            }

            /* Thumbnail is good so proceed */
            $resized = $thumb;
        }

        /* We only want to handle one image after resize. */
        if ( empty( $resized ) )
            set_transient ( $this->dir_transient, str_replace( AVATAR_UPLOAD_PATH, '', $upload['file'] ) );
        else {
            set_transient( $this->dir_transient, str_replace( AVATAR_UPLOAD_PATH, '', $resized ) );
            @unlink( $upload['file'] );
        }

        /* Set the url value for the image */
        set_transient( $this->url_transient, AVATAR_URL . get_transient( $this->dir_transient ) );
        return true;        
    }
    
    function handle_crop( $args = '' ) {
        $defaults = array(
            'object' => 'user',
            'avatar_dir' => 'avatars',
            'item_id' => false,
            'original_file' => false,
            'crop_w' => AVATAR_FULL_WIDTH,
            'crop_h' => AVATAR_FULL_HEIGHT,
            'crop_x' => 0,
            'crop_y' => 0
        );

        $r = wp_parse_args( $args, $defaults );

        /***
         * You may want to hook into this filter if you want to override this function.
         * Make sure you return false.
         */
        if ( !apply_filters( 'pre_avatar_handle_crop', true, $r ) )
            return true;

        extract( $r, EXTR_SKIP );

        if ( !$original_file )
            return false;

        $original_file = AVATAR_UPLOAD_PATH . $original_file;

        if ( !file_exists( $original_file ) )
            return false;

        if ( !$item_id )
            $avatar_folder_dir = apply_filters( 'avatar_folder_dir', dirname( $original_file ), $item_id, $object, $avatar_dir );
        else
            $avatar_folder_dir = apply_filters( 'avatar_folder_dir', AVATAR_UPLOAD_PATH . '/' . $avatar_dir . '/' . $item_id, $item_id, $object, $avatar_dir );

        if ( !file_exists( $avatar_folder_dir ) )
            return false;

        require_once( ABSPATH . '/wp-admin/includes/image.php' );
        require_once( ABSPATH . '/wp-admin/includes/file.php' );

        /* Delete the existing avatar files for the object */
        $this->delete_existing_avatar( array( 'object' => $object, 'item_id' => $item_id, 'avatar_path' => $avatar_folder_dir ) );

        /* Make sure we at least have a width and height for cropping */
        if ( !(int) $crop_w )
            $crop_w = AVATAR_FULL_WIDTH;

        if ( !(int) $crop_h )
            $crop_h = AVATAR_FULL_HEIGHT;

        /* Set the full and thumb filenames */
        $full_filename = wp_hash( $original_file . time() ) . '-bpfull.jpg';
        $thumb_filename = wp_hash( $original_file . time() ) . '-bpthumb.jpg';

        /* Crop the image */
        $full_cropped = wp_crop_image( $original_file, (int) $crop_x, (int) $crop_y, (int) $crop_w, (int) $crop_h, AVATAR_FULL_WIDTH, AVATAR_FULL_HEIGHT, false, $avatar_folder_dir . '/' . $full_filename );
        $thumb_cropped = wp_crop_image( $original_file, (int) $crop_x, (int) $crop_y, (int) $crop_w, (int) $crop_h, AVATAR_THUMB_WIDTH, AVATAR_THUMB_HEIGHT, false, $avatar_folder_dir . '/' . $thumb_filename );

        /* Remove the original */
        @unlink( $original_file );

        return true;
    }    
    
    function delete_existing_avatar( $args = '' ) {
        global $bp;

        $defaults = array(
            'item_id'   => false,
            'object'    => 'user', // user OR group OR blog OR custom type (if you use filters)
            'avatar_dir'=> false
        );

        $args = wp_parse_args( $args, $defaults );
        extract( $args, EXTR_SKIP );

        if ( !$item_id ) {
            return false;
        }

        if ( !$avatar_dir ) {
            $avatar_dir = apply_filters( 'avatar_dir', 'avatars', $object );

            if ( !$avatar_dir ) return false;
        }

        $avatar_folder_dir = apply_filters( 'avatar_folder_dir', AVATAR_UPLOAD_PATH . '/' . $avatar_dir . '/' . $item_id, $item_id, $object, $avatar_dir );

        if ( !file_exists( $avatar_folder_dir ) )
            return false;

        if ( $av_dir = opendir( $avatar_folder_dir ) ) {
            while ( false !== ( $avatar_file = readdir( $av_dir ) ) ) {
                if ( ( preg_match( "/-bpfull/", $avatar_file ) || preg_match( "/-bpthumb/", $avatar_file ) ) && '.' != $avatar_file && '..' != $avatar_file )
                    @unlink( $avatar_folder_dir . '/' . $avatar_file );
            }
        }
        closedir( $av_dir );

        @rmdir( $avatar_folder_dir );

        do_action( 'delete_existing_avatar', $args );

        return true;
    }    
    
    /**
     * avatar_filter()
     *
     * Attempts to filter get_avatar function and find an avatar that may have been uploaded locally.
     *
     * @global array $authordata
     * @param string $avatar The result of get_avatar from before-filter
     * @param int|string|object $user A user ID, email address, or comment object
     * @param int $size Size of the avatar image (thumb/full)
     * @param string $default URL to a default image to use if no avatar is available
     * @param string $alt Alternate text to use in image tag. Defaults to blank
     * @return <type>
     */    
    function avatar_filter( $avatar, $user, $size, $default, $alt ) {
        global $pagenow;
       
        // Do not filter if inside WordPress options page
        if ( 'options-discussion.php' == $pagenow )
            return $avatar;

        // If passed an object, assume $user->user_id
        if ( is_object( $user ) )
            $id = $user->user_id;

        // If passed a number, assume it was a $user_id
        else if ( is_numeric( $user ) )
            $id = $user;

        // If passed a string and that string returns a user, get the $id
        else if ( is_string( $user ) && ( $user_by_email = get_user_by_email( $user ) ) )
            $id = $user_by_email->ID;

        // If somehow $id hasn't been assigned, return the result of get_avatar
        if ( empty( $id ) )
            return !empty( $avatar ) ? $avatar : $default;

        if ( !$this->loaded_defines ) {
            $this->defines();
            $this->loaded_defines = true;
        }        
        
        // Let BuddyPress handle the fetching of the avatar
        $bp_avatar = $this->fetch( array( 'item_id' => $id, 'width' => $size, 'height' => $size, 'alt' => $alt ) );

        // If BuddyPress found an avatar, use it. If not, use the result of get_avatar
        return ( !$bp_avatar ) ? $avatar : $bp_avatar; 
    }    
    
    function fetch( $args = '' ) {
        if ( !$this->loaded_defines ) {
            $this->defines();
            $this->loaded_defines = true;
        }        
        // Set a few default variables
        $def_object		= 'user';
        $def_type		= 'thumb';
        $def_class		= 'avatar';
        $def_alt		= __( 'Avatar Image' );

        // Set the default variables array
        $defaults = array(
            'item_id'		=> false,
            'object'		=> $def_object,	// user/group/blog/custom type (if you use filters)
            'type'			=> $def_type,	// thumb or full
            'avatar_dir'	=> false,		// Specify a custom avatar directory for your object
            'width'			=> false,		// Custom width (int)
            'height'		=> false,		// Custom height (int)
            'class'			=> $def_class,	// Custom <img> class (string)
            'css_id'		=> false,		// Custom <img> ID (string)
            'alt'			=> $def_alt,	// Custom <img> alt (string)
            'email'			=> false,		// Pass the user email (for gravatar) to prevent querying the DB for it
            'no_grav'		=> false,		// If there is no avatar found, return false instead of a grav?
            'html'			=> true			// Wrap the return img URL in <img />
        );

        // Compare defaults to passed and extract
        $params = wp_parse_args( $args, $defaults );        
        extract( $params, EXTR_SKIP );

        // Set item_id if not passed
        if ( !$item_id ) {
            $current_user = wp_get_current_user();
            $user_id = $current_user->ID; 
            $item_id = apply_filters( 'avatar_item_id', $user_id, $object );            
            if ( !$item_id ) return false;
        }

        // Set avatar_dir if not passed (uses $object)
        if ( !$avatar_dir ) {
            $avatar_dir = apply_filters( 'avatar_dir', 'avatars', $object );
            if ( !$avatar_dir ) return false;
        }

        // Add an identifying class to each item
        $class .= ' ' . $object . '-' . $item_id . '-avatar';

        // Set CSS ID if passed
        if ( !empty( $css_id ) )
            $css_id = " id='{$css_id}'";

        // Set avatar width
        if ( $width )
            $html_width = " width='{$width}'";
        else
            $html_width = ( 'thumb' == $type ) ? ' width="' . AVATAR_THUMB_WIDTH . '"' : ' width="' . AVATAR_FULL_WIDTH . '"';

        // Set avatar height
        if ( $height )
            $html_height = " height='{$height}'";
        else
            $html_height = ( 'thumb' == $type ) ? ' height="' . AVATAR_THUMB_HEIGHT . '"' : ' height="' . AVATAR_FULL_HEIGHT . '"';

        // Set avatar URL and DIR based on prepopulated constants
        $avatar_folder_url = apply_filters( 'avatar_folder_url', AVATAR_URL . '/' . $avatar_dir . '/' . $item_id, $item_id, $object, $avatar_dir );
        $avatar_folder_dir = apply_filters( 'avatar_folder_dir', AVATAR_UPLOAD_PATH . '/' . $avatar_dir . '/' . $item_id, $item_id, $object, $avatar_dir );

        /****
         * Look for uploaded avatar first. Use it if it exists.
         * Set the file names to search for, to select the full size
         * or thumbnail image.
         */
        $avatar_size = ( 'full' == $type ) ? '-bpfull' : '-bpthumb';

        // Check for directory
        if ( file_exists( $avatar_folder_dir ) ) {

            // Open directory
            if ( $av_dir = opendir( $avatar_folder_dir ) ) {

                // Stash files in an array once to check for one that matches
                $avatar_files = array();
                while ( false !== ( $avatar_file = readdir($av_dir) ) ) {
                    // Only add files to the array (skip directories)
                    if ( 2 < strlen( $avatar_file ) )
                        $avatar_files[] = $avatar_file;
                }

                // Check for array
                if ( 0 < count( $avatar_files ) ) {

                    // Check for current avatar
                    foreach( $avatar_files as $key => $value ) {
                        if ( strpos ( $value, $avatar_size )!== false )
                            $avatar_url = $avatar_folder_url . '/' . $avatar_files[$key];
                    }
                }
            }

            // Close the avatar directory
            closedir( $av_dir );

            // If we found a locally uploaded avatar
            if ( $avatar_url ) {

                // Return it wrapped in an <img> element
                if ( true === $html ) {
                    return apply_filters( 'fetch_avatar', '<img src="' . $avatar_url . '" alt="' . $alt . '" class="' . $class . '"' . $css_id . $html_width . $html_height . ' />', $params, $item_id, $avatar_dir, $css_id, $html_width, $html_height, $avatar_folder_url, $avatar_folder_dir );

                // ...or only the URL
                } else {
                    return apply_filters( 'fetch_avatar_url', $avatar_url );
                }
            }
        }

        // If no avatars could be found, try to display a gravatar

        // Skips gravatar check if $no_grav is passed
        if ( !$no_grav ) {

            // Set gravatar size
            if ( $width )
                $grav_size = $width;
            else if ( 'full' == $type )
                $grav_size = AVATAR_FULL_WIDTH;
            else if ( 'thumb' == $type )
                $grav_size = AVATAR_THUMB_WIDTH;

            $default_grav = apply_filters( 'core_mysteryman_src', AVATAR_DEFAULT, $grav_size );

            // Set gravatar object
            if ( ! $email ) {
                $data = get_userdata( $item_id );
                $email = $data->user_email;
            }

            // Set host based on if using ssl
            if ( is_ssl() )
                $host = 'https://secure.gravatar.com/avatar/';
            else
                $host = 'http://www.gravatar.com/avatar/';

            // Filter gravatar vars
            $email		= apply_filters( 'avatar_gravatar_email', $email, $item_id, $object );
            $gravatar	= apply_filters( 'avatar_gravatar_url', $host ) . md5( strtolower( $email ) ) . '?d=' . $default_grav . '&amp;s=' . $grav_size;

            // Return gravatar wrapped in <img />
            if ( true === $html )
                return apply_filters( 'fetch_avatar', '<img src="' . $gravatar . '" alt="' . $alt . '" class="' . $class . '"' . $css_id . $html_width . $html_height . ' />', $params, $item_id, $avatar_dir, $css_id, $html_width, $html_height, $avatar_folder_url, $avatar_folder_dir );

            // ...or only return the gravatar URL
            else
                return apply_filters( 'fetch_avatar_url', $gravatar );

        } else {
            return apply_filters( 'fetch_avatar', false, $params, $item_id, $avatar_dir, $css_id, $html_width, $html_height, $avatar_folder_url, $avatar_folder_dir );
        }
    }    
}

global $_avatar;
$_avatar = new Avatar();
$_avatar->init();