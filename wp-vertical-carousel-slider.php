<?php
/**
 * Plugin Name: Vertical Carousel slider
 * Plugin URI: https://aftabhusain.wordpress.com/
 * Description: Display vertical carousel slider with the help of a shortcode.
 * Version: 1.0.0
 * Author: Aftab Husain
 * Author URI: https://aftabhusain.wordpress.com/
 * License: GPLv2
 */
 
//Register Post type Vertical Carousel
add_action('init', 'wpvc_vertical_carouesel_register');
function wpvc_vertical_carouesel_register() {

	$labels = array(
		'name' => _x('Vertical Carousel', 'post type general name'),
		'singular_name' => _x('Vertical Carousel', 'post type singular name'),
		'add_new' => _x('Add New Image', 'Vertical1 Carousel1'),
		'add_new_item' => __('Add New Image'),
		'edit_item' => __('Edit Image'),
		'new_item' => __('New Image'),
		'view_item' => __('View Carousel Image'),
		'search_items' => __('Search Carousel Image'),
		'not_found' =>  __('Nothing found'),
		'not_found_in_trash' => __('Nothing found in Trash'),
		'parent_item_colon' => ''
	);

	$args = array(
		'labels' => $labels,
		'public' => true,
		'publicly_queryable' => true,
		'show_ui' => true,
		'query_var' => true,
		'menu_icon' => 'dashicons-format-image',
		'rewrite' => true,
		'capability_type' => 'post',
		'hierarchical' => false,
		'menu_position' => null,
		'supports' => array( 'title', 'thumbnail' )
	  ); 

	register_post_type( 'vertical-carousel' , $args );
} 

// Add the posts and pages columns filter. They can both use the same function.
add_filter('manage_posts_columns', 'wpvc_add_post_thumbnail_column', 2);
	
// Add the column
function wpvc_add_post_thumbnail_column($cols){
  $cols['wpvc_logo_thumb'] = __('Carousel Image');
  return $cols;
}

// Hook into the posts an pages column managing. Sharing function callback again.
add_action('manage_posts_custom_column', 'wpvc_display_post_thumbnail_column', 5, 2);
	
// Grab featured-thumbnail size post thumbnail and display it.
function wpvc_display_post_thumbnail_column($col, $id){
  switch($col){
	case 'wpvc_logo_thumb':
	  if( function_exists('the_post_thumbnail') ){
	
		$post_thumbnail_id = get_post_thumbnail_id($id);
		$post_thumbnail_img = wp_get_attachment_image_src($post_thumbnail_id, 'featured_preview');
		$post_thumbnail_img= $post_thumbnail_img[0];
		if($post_thumbnail_img !='')
		  echo '<img width="120" height="120" src="' . $post_thumbnail_img . '" />';
		else
		  echo 'No logo added.';	
	  }
	  else{
		echo 'No logo added.';
	  }	
		   
	  break;
 
  }
}

// carousel Image Meta Box
function wpvc_clientlogo_add_meta_box(){
// add meta Box
 remove_meta_box( 'postimagediv', 'post', 'side' );
 add_meta_box('postimagediv', __('Carousel Image'), 'post_thumbnail_meta_box', 'vertical-carousel', 'normal', 'high');
 add_meta_box('wpvc_clientlogo_meta_id', __('Link Url to Image'), 'wpvc_meta_callback', 'vertical-carousel', 'normal', 'high');
}
add_action('add_meta_boxes' , 'wpvc_clientlogo_add_meta_box');


// client Carousel Meta Box Call Back Funtion
function wpvc_meta_callback($post){

    wp_nonce_field( basename( __FILE__ ), 'wpvc_nonce' );
    $aft_stored_meta = get_post_meta( $post->ID );
    ?>

    <p>
        <label for="wpvc_link_meta_url" class="wpvc_link_meta_url"><?php _e( 'Link Url to Image', '' )?></label>
        <input class="widefat" type="text" name="wpvc_link_meta_url" id="wpvc_link_meta_url" value="<?php if ( isset ( $aft_stored_meta['wpvc_link_meta_url'] ) ) echo $aft_stored_meta['wpvc_link_meta_url'][0]; ?>" /> <br>
		<em>(For Example: http://put-website-url.com)</em>
    </p>

<?php

}

//client Carousel Save Meta Box 
function wpvc_clientlogo_meta_save( $post_id ) {

    // Checks save status
    $is_autosave = wp_is_post_autosave( $post_id );
    $is_revision = wp_is_post_revision( $post_id );
    $is_valid_nonce = ( isset( $_POST[ 'wpvc_nonce' ] ) && wp_verify_nonce( $_POST[ 'wpvc_nonce' ], basename( __FILE__ ) ) ) ? 'true' : 'false';

    // Exits script depending on save status
    if ( $is_autosave || $is_revision || !$is_valid_nonce ) {
        return;
    }

    // Checks for input and sanitizes/saves if needed
    if( isset( $_POST[ 'wpvc_link_meta_url' ] ) ) {
        update_post_meta( $post_id, 'wpvc_link_meta_url', sanitize_text_field( $_POST[ 'wpvc_link_meta_url' ] ) );
    }

}
add_action( 'save_post', 'wpvc_clientlogo_meta_save' );


//client Carousel admin style
function wpvc_verticalcarousel_dashboard_icon(){
?>
 <style>
#toplevel_page_vertical-carousel-slider {
 display:none; 
}
</style>
<?php
}
add_action( 'admin_head', 'wpvc_verticalcarousel_dashboard_icon' );
 

// Setup the shortcode
function wpvc_carousel_slider_callback( $atts ) {
	
//include carousel css and js
wp_enqueue_style("wpvc_caro_css_and_js", plugins_url('includes/carousel_style.css', __FILE__), false, "1.0", "all"); 
wp_register_script( 'wpvc_caro_css_and_js', plugins_url('includes/carousel-js.js', __FILE__ ) );
wp_enqueue_script('wpvc_caro_css_and_js');

	ob_start();
    extract( shortcode_atts( array (
        'type' => 'vertical-carousel',
        'order' => 'date',
        'orderby' => 'title',
        'posts' => -1,
    
    ), $atts ) );
    $options = array(
        'post_type' => $type,
        'order' => $order,
        'orderby' => $orderby,
        'posts_per_page' => $posts,
  		
    );
    $query = new WP_Query( $options );?>
	
    <?php if ( $query->have_posts() ) { ?>
	 <div class="wpvc-jcarousel-skin">
     <ul id="wpvc-carousel">
	<?php while ( $query->have_posts() ) : $query->the_post(); ?>
	 <li>
	 <a target="_blank" href="<?php echo get_post_meta(get_the_ID(),'wpvc_link_meta_url',true);?>">
	 <?php the_post_thumbnail('full'); ?>
	 </a>
	 </li>
	
	<?php endwhile;
      wp_reset_postdata(); ?>
	     </ul> 
    </div>
	<?php 
	
	}else{
		
		echo "No Image is added.";
	}
	?>
	  

<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery('#wpvc-carousel').jcarousel({
		start: 1,
	    //wrap: "circular",
		scroll: 1,
		//auto:3,
		vertical:true,
		});
	});
</script>
<?php
	
	
	return ob_get_clean();
}
add_shortcode( 'vertical-carousel-slider', 'wpvc_carousel_slider_callback' );

?>