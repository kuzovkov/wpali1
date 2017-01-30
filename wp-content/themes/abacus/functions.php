<?php
/**
 * Abacus functions and definitions
 *
 * Set up the theme and provides some helper functions, which are used in the
 * theme as custom template tags. Others are attached to action and filter
 * hooks in WordPress to change core functionality.
 *
 * When using a child theme you can override certain functions (those wrapped
 * in a function_exists() call) by defining them first in your child theme's
 * functions.php file. The child theme's functions.php file is included before
 * the parent theme's file, so the child theme functions would be used.
 *
 * @link https://codex.wordpress.org/Theme_Development
 * @link https://codex.wordpress.org/Child_Themes
 *
 * Functions that are not pluggable (not wrapped in function_exists()) are
 * instead attached to a filter or action hook.
 *
 * For more information on hooks, actions, and filters,
 * {@link https://codex.wordpress.org/Plugin_API}
 *
 * @package Abacus
 * @since Abacus 1.0
 */

// Defining constants
$abacus_theme_data = wp_get_theme( 'abacus' );
define( 'ABACUS_THEME_URL', get_template_directory_uri() );
define( 'ABACUS_THEME_TEMPLATE', get_template_directory() );
define( 'ABACUS_THEME_VERSION', trim( $abacus_theme_data->Version ) );
define( 'ABACUS_THEME_NAME', $abacus_theme_data->Name );

if ( ! isset( $content_width ) ) {
	$content_width = 860;
}

foreach ( glob( ABACUS_THEME_TEMPLATE . '/inc/*' ) as $filename ) {
	include $filename;
}

add_action( 'after_setup_theme', 'abacus_setup' );
if ( ! function_exists( 'abacus_setup' ) ) {
	function abacus_setup() {
		load_theme_textdomain( 'abacus', ABACUS_THEME_TEMPLATE . '/languages' );

		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'woocommerce' );
		add_theme_support( 'html5', array(
			'comment-list',
			'comment-form',
			'search-form',
			'gallery',
			'caption'
		) );
		add_theme_support( 'custom-header', apply_filters( 'abacus_custom_header_args', array(
			'header-text' => false,
			'flex-height' => true,
			'flex-width' => true,
			'random-default' => true,
			'width' => apply_filters( 'abacus_header_image_width', 1400 ),
			'height' => apply_filters( 'abacus_header_image_width', 600 ),
			'wp-head-callback' => 'abacus_header_style',
		) ) );
		if ( function_exists( 'abc_premium_features' ) ) {
			add_theme_support( 'custom-background', apply_filters( 'abacus_custom_background_args', array(
				'default-color' => '2E3739',
			) ) );
		}
		add_theme_support( 'jetpack-testimonial' );
		add_theme_support( 'custom-logo' );

		add_editor_style( array( 'css/admin/editor-style.css', '/css/font-awesome.css', abacus_fonts_url() ) );

		register_nav_menu( 'top', __( 'Top Menu', 'abacus' ) );
		register_nav_menu( 'primary', __( 'Primary Menu', 'abacus' ) );

		add_image_size( 'abacus-testimonial-thumbnail', 60, 60, true );

		add_filter( 'use_default_gallery_style', '__return_false' );
	}
}

if ( ! function_exists( 'abacus_header_style' ) ) {
	function abacus_header_style() {
		$header_text_color = get_header_textcolor();

		// If no custom options for text are set, let's bail
		// get_header_textcolor() options: HEADER_TEXTCOLOR is default, hide text (returns 'blank') or any hex value
		if ( get_theme_support( 'custom-header', 'default-text-color' ) === $header_text_color ) {
			return;
		}

		// If we get this far, we have custom styles. Let's do this.
		?>
		<style type="text/css">
		<?php
			// Has the text been hidden?
			if ( ! display_header_text() ) :
		?>
			.site-title,
			.site-description {
				position: absolute;
				clip: rect(1px, 1px, 1px, 1px);
			}
		<?php endif; ?>
		</style>
		<?php
	}
}

add_action( 'wp_enqueue_scripts', 'abacus_enqueue' );
if ( ! function_exists( 'abacus_enqueue' ) ) {
	function abacus_enqueue() {
		wp_enqueue_script( 'theme', ABACUS_THEME_URL .'/js/theme.js', array( 'jquery' ), '', true );

		wp_enqueue_style( 'abacus-stylesheet', get_stylesheet_uri() );
		wp_enqueue_style( 'abacus-google-fonts', abacus_fonts_url(), array(), null );
		wp_enqueue_style( 'font-awesome', ABACUS_THEME_URL .'/css/font-awesome.css', false, '4.4.0', 'all' );

		if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
			wp_enqueue_script( 'comment-reply' );
		}

		if ( is_front_page() ) {
			$header_image = get_header_image();
			$custom_css = "
				.header-img {
					background-image: url(" . esc_url( $header_image ) . ");
					height: " . get_custom_header()->height . "px;
				}";
			wp_add_inline_style( 'abacus-stylesheet', $custom_css );
		}
	}
}

if ( ! function_exists( 'abacus_fonts_url' ) ) {
	function abacus_fonts_url() {
		$fonts     = array();
		$subsets   = 'latin,latin-ext';

		/*
		 * Translators: If there are characters in your language that are not supported
		 * by Roboto, translate this to 'off'. Do not translate into your own language.
		 */
		if ( 'off' !== _x( 'on', 'Roboto font: on or off', 'abacus' ) ) {
			$fonts[] = 'Roboto:300,400,400italic,700,700italic';
		}

		/*
		 * Translators: To add an additional character subset specific to your language,
		 * translate this to 'greek', 'cyrillic', 'devanagari' or 'vietnamese'. Do not translate into your own language.
		 */
		$subset = _x( 'no-subset', 'Add new subset (greek, cyrillic, devanagari, vietnamese)', 'abacus' );

		if ( 'cyrillic' == $subset ) {
			$subsets .= ',cyrillic,cyrillic-ext';
		} elseif ( 'greek' == $subset ) {
			$subsets .= ',greek,greek-ext';
		} elseif ( 'devanagari' == $subset ) {
			$subsets .= ',devanagari';
		} elseif ( 'vietnamese' == $subset ) {
			$subsets .= ',vietnamese';
		}

		return ( $fonts ) ? add_query_arg( array(
			'family' => urlencode( implode( '|', $fonts ) ),
			'subset' => urlencode( $subsets ),
		), 'https://fonts.googleapis.com/css' ) : '';
	}
}

add_action( 'widgets_init', 'abacus_widgets_init' );
if ( ! function_exists( 'abacus_widgets_init' ) ) {
	function abacus_widgets_init() {
		register_sidebar( array(
			'name' => __( 'Sidebar', 'abacus' ),
			'id' => 'sidebar',
			'description' => __( 'This section appears on the right of the main content on every page.', 'abacus' ),
			'before_widget' => '<aside id="%1$s" class="widget %2$s">',
			'after_widget' => '</aside>',
			'before_title' => '<h3 class="widget-title">',
			'after_title' => '</h3>',
		) );

		register_sidebar( array(
			'name' => __( 'Jumbo Headline', 'abacus' ),
			'id' => 'jumbo-headline',
			'description' => __( 'This section appears on the front page in the centre of the header image. Designed specifically for one Text widget. ', 'abacus' ),
			'before_widget' => '<aside id="%1$s" class="jumbo-headline %2$s">',
			'after_widget' => '</aside>',
			'before_title' => '<h3>',
			'after_title' => '</h3>',
		) );

		if ( function_exists( 'abc_premium_features' ) ) {
			register_sidebar( array(
				'name' => __( 'Shop Categories', 'abacus' ),
				'id' => 'shop-categories',
				'description' => __( 'This section appears on the Front Page template below the featured section. Designed specifically for three widgets. ', 'abacus' ),
				'before_widget' => '<aside id="%1$s" class="cols cols-3 %2$s">',
				'after_widget' => '</aside>',
				'before_title' => '<h3>',
				'after_title' => '</h3>',
			) );

			register_sidebar( array(
				'name' => __( 'Shop Banner', 'abacus' ),
				'id' => 'shop-banner',
				'description' => __( 'This section appears on the Front Page template above the trending section. Designed specifically for one widget. ', 'abacus' ),
				'before_widget' => '<aside id="%1$s" class="%2$s">',
				'after_widget' => '</aside>',
				'before_title' => '<h3>',
				'after_title' => '</h3>',
			) );
		}
	}
}