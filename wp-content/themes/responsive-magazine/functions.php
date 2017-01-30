<?php
/**
 * Function describe for Responsive Mag
 * 
 * @package responsive-magazine
 */

include_once( trailingslashit( get_stylesheet_directory() ) . 'lib/custom-config.php' );

add_action( 'wp_enqueue_scripts', 'responsive_magazine_enqueue_styles', 999 );
function responsive_magazine_enqueue_styles() {
  $parent_style = 'responsive-magazine-style';

    wp_enqueue_style( $parent_style, get_template_directory_uri() . '/style.css' );
  
  wp_enqueue_script( 'responsive-magazine-script', get_stylesheet_directory_uri() . '/js/customscript-responsive-magazine.js', array('jquery'), '1' );          

}

function responsive_magazine_theme_setup() {
    
    load_child_theme_textdomain( 'responsive-magazine', get_stylesheet_directory() . '/languages' );
    
    add_image_size( 'responsive-magazine-carousel', 278, 430, true );
}
add_action( 'after_setup_theme', 'responsive_magazine_theme_setup' );

function responsive_magazine_custom_remove( $wp_customize ) {
    
    $wp_customize->remove_control( 'featured-categories' );
    $wp_customize->remove_control( 'get-featured' );
    
}

add_action( 'customize_register', 'responsive_magazine_custom_remove', 100);

////////////////////////////////////////////////////////////////////
// Excerpt functions
////////////////////////////////////////////////////////////////////
function responsive_magazine_excerpt_length( $length ) {
	return ( is_front_page() ) ? 10 : 20;
}

add_filter( 'excerpt_length', 'responsive_magazine_excerpt_length', 999 );

function responsive_magazine_excerpt_more( $more ) {
	return '&hellip;';
}

add_filter( 'excerpt_more', 'responsive_magazine_excerpt_more' );

