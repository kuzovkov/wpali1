<?php
/**
 * The template for displaying all pages.
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site will use a
 * different template.
 *
 * @package Abacus
 * @since Abacus 1.0
 */
 
get_header();
?>

	<?php if ( has_header_image() && is_front_page() ) : ?>
		<div class="parallax">
			<div class="header-img"></div>
			<?php if ( is_active_sidebar( 'jumbo-headline' ) ) { ?>
			<div class="container">
				<div class="row">
					<div class="home-top">
						<?php dynamic_sidebar( 'jumbo-headline' ); ?>
					</div>
				</div>
			</div>
			<?php } ?>
		</div>
	<?php endif; ?>

	<?php if ( is_front_page() ) { ?>
	<div class="home-container">
	<?php } ?>
	<div class="container">
		<div class="row">
			<div id="primary" class="cols">
				<?php
				while ( have_posts() ) : the_post();
					get_template_part( 'template-parts/content', 'page' );

					comments_template( '', true );
				endwhile;
				?>
			</div><!-- #primary -->

			<?php get_sidebar(); ?>
		</div>
	</div>
	<?php if ( is_front_page() ) { ?>
	</div>
	<?php } ?>

<?php get_footer(); ?>