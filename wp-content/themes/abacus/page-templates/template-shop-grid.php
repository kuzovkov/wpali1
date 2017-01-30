<?php
/**
 * Template Name: Shop Grid
 *
 * Description: A page template build to display your shop on the homepage
 *
 * @package Abacus
 * @since Abacus 1.0
 */
 
$abacus_default_theme_options = abacus_default_theme_options();
get_header(); ?>

	<?php if ( has_header_image() ) : ?>
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

	<div class="home-container">
	<div id="primary" class="container">

		<?php
		// Display page content if there is any
		while ( have_posts() ) : the_post();
			if ( '' != $post->post_content ) { ?>
				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				    <div class="entry-content description clearfix">
					    <?php the_content( '' ); ?>
				    </div><!-- .entry-content -->
				</article><!-- #post-<?php the_ID(); ?> -->
				<?php
			}
		endwhile;

		if ( abacus_woocommerce_activated() ) {
			$feature_args = apply_filters( 'abacus_featured_products_args', array(
				'limit' 			=> 4,
				'columns' 			=> 4,
				'title'				=> __( 'Featured', 'abacus' ),
				'description'		=> abacus_sanitize_text( get_theme_mod( 'featured_title_description', $abacus_default_theme_options['featured_title_description'] ) ),
				)
			);
			$featured_products = do_shortcode( '[featured_products per_page="' . intval( $feature_args['limit'] ) . '" columns="' . intval( $feature_args['columns'] ) . '"]' );

			if ( strpos( $featured_products, 'class="products"' ) !== false ) {
				?>
				<section class="product-section featured-products">

					<h2 class="entry-title page-header"><?php echo esc_attr( $feature_args['title'] ); ?></h2>
					<p class="entry-desc"><?php echo esc_attr( $feature_args['description'] ); ?></p>
					<?php echo $featured_products; ?>
				</section>
				<?php
			}
			?>

			<?php if ( is_active_sidebar( 'shop-categories' ) && function_exists( 'abc_premium_features' ) ) { ?>
				<div class="shop-categories row">
					<?php dynamic_sidebar( 'shop-categories' ); ?>
				</div>
			<?php } ?>

			<?php if ( is_active_sidebar( 'shop-banner' ) && function_exists( 'abc_premium_features' ) ) { ?>
				<div class="shop-banner">
					<?php dynamic_sidebar( 'shop-banner' ); ?>
				</div>
			<?php } ?>

			<?php
			$pop_args = apply_filters( 'abacus_popular_products_args', array(
				'limit' 			=> 4,
				'columns' 			=> 4,
				'title'				=> __( 'Trending', 'abacus' ),
				'description'		=> abacus_sanitize_text( get_theme_mod( 'popular_title_description', $abacus_default_theme_options['popular_title_description'] ) ),
				)
			);
			$pop_products = do_shortcode( '[top_rated_products per_page="' . intval( $pop_args['limit'] ) . '" columns="' . intval( $pop_args['columns'] ) . '"]' );

			if ( strpos( $pop_products, 'class="products"' ) !== false ) {
				?>
				<section class="product-section popular-products">
					<h2 class="entry-title page-header"><?php echo esc_attr( $pop_args['title'] ); ?></h2>
					<p class="entry-desc"><?php echo esc_attr( $feature_args['description'] ); ?></p>

					<?php echo $pop_products; ?>
				</section>
				<?php
			}
		}
		?>

		<?php
		$testimonials = new WP_Query( array(
			'post_type'      => 'jetpack-testimonial',
			'orderby'        => 'rand',
			'posts_per_page' => 2,
			'no_found_rows'  => true,
		) );
		?>

		<?php if ( $testimonials->have_posts() ) : ?>
			<section id="front-page-testimonials" class="front-testimonials testimonials">
				<h2 class="entry-title page-header"><?php _e( 'Reviews', 'abacus' ); ?></h2>

				<div class="row">
				<?php
					while ( $testimonials->have_posts() ) : $testimonials->the_post();
						 get_template_part( 'template-parts/content', 'testimonial' );
					endwhile;
					wp_reset_postdata();
				?>
				</div>
			</section><!-- .testimonials -->
		<?php endif; ?>
	</div>
	</div>

<?php get_footer(); ?>
