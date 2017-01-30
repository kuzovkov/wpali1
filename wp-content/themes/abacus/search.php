<?php
/**
 * The template for displaying Search Results pages.
 *
 * @package Abacus
 * @since Abacus 1.0
 */
 
get_header(); ?>

	<div class="container">
		<div class="row">
			<section id="primary" class="cols">
			    <?php
				$search_post_type = ( isset( $_GET['search_post_type'] ) ) ? esc_attr( $_GET['search_post_type'] ) : '';
				$search_query = ( isset( $_GET['s'] ) ) ? esc_attr( $_GET['s'] ) : '';
				if ( abacus_woocommerce_activated() && 'post' != $search_post_type ) {
					$args = array(
						's' => $search_query,
						'post_type' => 'product',
						'posts_per_page' => 12
					);
					$product_search_query = new WP_Query( $args );
					?>
					<?php if ( $product_search_query->have_posts() ) : ?>
					<header id="archive-header">
						<h2 class="entry-title archive-title"><?php abacus_search_title( $product_search_query ); ?></h2>
					</header>

					<div class="woocommerce columns-4">
						<ul class="products">
							<?php
							while ( $product_search_query->have_posts() ) : $product_search_query->the_post();
								wc_get_template_part( 'template-parts/content', 'product' );
						    endwhile;
							?>
						</ul>
					</div>
					<?php endif; ?>
					<?php
					abacus_search_pagination( $product_search_query );

				    wp_reset_query();
			    }
				?>

				<?php
				if ( 'product' != $search_post_type ) {
					global $wp_query;
					if ( have_posts() ) : ?>
						<header id="archive-header">
							<h2 class="entry-title archive-title"><?php abacus_search_title( $wp_query ); ?></h2>
						</header>
						<?php
						while ( have_posts() ) : the_post();
							get_template_part( 'template-parts/content' );
						endwhile;

						abacus_search_pagination( $wp_query );
					else :
						get_template_part( 'template-parts/content', 'none' );
					endif;
				}
				?>
			</section><!-- #primary -->

		<?php get_sidebar(); ?>
		</div>
	</div>

<?php get_footer(); ?>