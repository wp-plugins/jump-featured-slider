<?php
/*
Plugin Name: Jump Featured Image
Version: 1.2
Plugin URI: http://jumpr.me/widgets?featured
Description: Easy set a featured image for your community content slider.
Author: Ramon Souza
Author URI: http://jumpr.me
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/
 
function add_custom_meta_boxes() {
	// Define the custom attachment for posts
	add_meta_box(
		'wp_jump_featured',
		'Jump featured image',
		'wp_jump_featured',
		'post',
		'normal'
	);
	// Define the custom attachment for pages
	add_meta_box(
		'wp_jump_featured',
		'Jump featured image',
		'wp_jump_featured',
		'page',
		'normal'
	);
}

// end add_custom_meta_boxes
add_action('add_meta_boxes', 'add_custom_meta_boxes');

function wp_jump_featured() {
	$img = get_post_meta(get_the_ID(), 'wp_jump_featured', true);
	$caption = get_post_meta(get_the_ID(), 'wp_jump_featured_caption', true);
    
	wp_nonce_field(plugin_basename(__FILE__), 'wp_jump_featured_nonce');
	
	
	$html = '
	
	<style>
	#side-sortables #sortjumpsort img {
	    width:100% !important;
	    height:auto !important;
	    margin-bottom:15px;
	}
	
	#side-sortables #sortjumpsort .hspa {
	    margin:0 0 0 0 !important;
	}
	
	#side-sortables #sortjumpsort input {
	    width:99% !important;
	}

	</style>
	';
	
	$html .= '<div style="overflow:hidden;padding-top:4px;" id="sortjumpsort">'.((isset($img['url'])?'<img src="'.$img['url'].'" width="110px" style="float:left;margin-right:10px;">':'<img src="'.plugin_dir_url(__FILE__).'/assets/nocover.jpg" width="110px" height="110px" style="float:left;margin-right:10px;">')).'
	
	<div class="hspa" style="margin-left:120px"><div>';
		$html .= 'Upload the image that will appear in your <b>community slider.</b> <div style="margin:10px 0" class="description">The recommended size is <b>640x250</b> or <b>960x250</b>, depending of your community settings.</div>';
	$html .= '</div>';
	$html .= '<input type="file" id="wp_jump_featured" name="wp_jump_featured" value="" size="50">
	<div><div style="margin:10px 0 5px">Custom slide caption:</div> <input value="'.$caption.'" type="text" id="wp_jump_featured_caption" name="wp_jump_featured_caption" size="50"> </div></div>
	</div>';
	echo $html;
}

// end wp_jump_featured


function save_custom_meta_data($id) {
	/* --- security verification --- */
	if(!wp_verify_nonce($_POST['wp_jump_featured_nonce'], plugin_basename(__FILE__))) {
	  return $id;
	} // end if
	if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
	  return $id;
	} // end if
	if('page' == $_POST['post_type']) {
	  if(!current_user_can('edit_page', $id)) {
	    return $id;
	  } // end if
	} else {
   		if(!current_user_can('edit_page', $id)) {
	    	return $id;
	   	} // end if
	} // end if
	/* - end security verification - */
	// Make sure the file array isn't empty
	if(!empty($_FILES['wp_jump_featured']['name'])) {
		// Setup the array of supported file types. In this case, it's just PDF.
		$supported_types = array('image/png', 'image/jpg', 'image/jpeg', 'image/gif');
		// Get the file type of the upload
		$arr_file_type = wp_check_filetype(basename($_FILES['wp_jump_featured']['name']));
		$uploaded_type = $arr_file_type['type'];
		// Check if the type is supported. If not, throw an error.
		if(in_array($uploaded_type, $supported_types)) {
			// Use the WordPress API to upload the file
			$upload = wp_upload_bits($_FILES['wp_jump_featured']['name'], null, file_get_contents($_FILES['wp_jump_featured']['tmp_name']));
			if(isset($upload['error']) && $upload['error'] != 0) {
				wp_die('There was an error uploading your file. The error is: ' . $upload['error']);
			} else {
				add_post_meta($id, 'wp_jump_featured', $upload);
				update_post_meta($id, 'wp_jump_featured', $upload);
			} // end if/else
		} else {
			wp_die("The file type that you've uploaded is empty or is not an image.");
		} // end if/else
	} // end if
	
	if(!empty($_POST['wp_jump_featured_caption'])){
	    add_post_meta($id, 'wp_jump_featured_caption', $_POST['wp_jump_featured_caption']);
	    update_post_meta($id, 'wp_jump_featured_caption', $_POST['wp_jump_featured_caption']);
	}
}

// end save_custom_meta_data
add_action('save_post', 'save_custom_meta_data');

function update_edit_form() {
    echo ' enctype="multipart/form-data"';
} // end update_edit_form

add_action('post_edit_form_tag', 'update_edit_form');

add_action( 'rss2_item', 'add_post_featured_image_as_rss_item_enclosure' );

function add_post_featured_image_as_rss_item_enclosure() {
	$thumbnail = get_post_meta(get_the_ID(), 'wp_jump_featured', true);
	$caption = get_post_meta(get_the_ID(), 'wp_jump_featured_caption', true);
	    
	echo ( empty( $thumbnail ) )?'':'
	    <jump:slide>
		    <image>'.$thumbnail['url'].'</image>
		    '.((trim($caption)!='')?'<caption><![CDATA['.$caption.']]></caption>':'').'
	    </jump:slide>
	';
}

add_action('rss2_ns', 'wp_rss_add_xmls');

function wp_rss_add_xmls(){
	echo 'xmlns:jump="http://jumpr.me/"';
}

?>