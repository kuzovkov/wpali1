<?php
/**
 * The Header for our theme.
 *
 * Displays all of the <head> section and everything up till <main>
 *
 * @package Abacus
 * @since Abacus 1.0
 */
 
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">

	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
	<div id="page">
		<header id="masthead" role="banner">
			<nav class="top-navigation menus" role="navigation">
				<div class="container">
					<?php abacus_login_register_menu(); ?>

					<?php
					// Requires ABC Premium Features plugin
					if ( function_exists( 'abc_social_icons_output' ) ) {
						abc_social_icons_output();
					}
					?>

					<?php abacus_top_menu(); ?>
				</div>
			</nav>

			<div class="container">
				<div class="site-meta">
					<?php
					if ( function_exists( 'the_custom_logo' ) )  {
						the_custom_logo();
					}
					?>

					<?php
					$tag = ( is_front_page() && is_home() ) ? 'h1' : 'div';
					// Requires ABC Premium Features plugin
					$hide_title_tagline = abacus_sanitize_checkbox( get_theme_mod( 'abc_hide_title_tagline' ) );
					$class = ( $hide_title_tagline ) ? 'screen-reader-text' : '';
					?>
					<<?php echo $tag; ?> class="site-title pull-left <?php echo esc_attr( $class ); ?>">
						<a href="<?php echo esc_url( home_url() ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a>
					</<?php echo $tag; ?>>

					<div class="site-description <?php echo esc_attr( $class ); ?>"><?php bloginfo( 'description' ); ?></div>
				</div>

				<?php if ( has_nav_menu( 'primary' ) ) { ?>
				<nav class="main-navigation menus pull-left" role="navigation">
					<h3 class="screen-reader-text"><?php _e( 'Main menu', 'abacus' ); ?></h3>
					<ul class="primary-menu">
						<?php wp_nav_menu( array( 'theme_location' => 'primary', 'items_wrap' => '%3$s', 'container' => '' ) ); ?>
					</ul>
				</nav>
				<?php }	?>

				<nav class="nav-icons menus" role="navigation">
					<ul>
						<li class="nav-open-top-menu"><i class="fa fa-bars"></i><span><?php _e( 'Menu', 'abacus' ); ?></span></li>
						<li class="nav-search"><i class="fa fa-search"></i></li>
						<?php
						if ( class_exists( 'woocommerce' ) ) {
							global $woocommerce;
							?>
							<li class="nav-cart"><a href="<?php echo esc_url( $woocommerce->cart->get_cart_url() ); ?>" class="cart-button-mobile"><i class="fa fa-shopping-cart"></i></a></li>
							<?php
						}
						?>
					</ul>
				</nav>
			</div>
		</header>

		<main id="content" class="site-content">
			<a class="screen-reader-text" href="#primary" title="<?php esc_attr_e( 'Skip to content', 'abacus' ); ?>"><?php _e( 'Skip to content', 'abacus' ); ?></a>