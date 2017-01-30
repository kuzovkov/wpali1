<?php
/**
 * The template for displaying pages
 *
 * @package Abacus
 * @since Abacus 1.0
 */
 
?>
	<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

		<header>
			<?php
			if ( has_post_thumbnail() ) {
				?>
				<div class="break-out fadeInUp">
					<a href="<?php echo esc_url( get_permalink() ); ?>">
						<?php the_post_thumbnail( 'large' ); ?>
					</a>
				</div>
				<?php
			}
			?>

			<?php
			if ( is_search() ) :
			the_title( sprintf( '<h2 class="entry-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h2>' );
			elseif ( is_front_page() ) :
				the_title( '<h2 class="entry-title">', '</h2>' );
			else :
				the_title( '<h1 class="entry-title">', '</h1>' );
			endif;
			?>
		</header>

	    <div class="entry-content">
		    <?php
			$abacus_default_theme_options = abacus_default_theme_options();

			if ( is_search() ) {
				the_excerpt();
			} else {
			    the_content( abacus_sanitize_text( get_theme_mod( 'read_more_text', $abacus_default_theme_options['read_more_text'] ) ) . ' <span class="screen-reader-text">' . get_the_title() . '</span>' );
			}
			?>
	    </div><!-- .entry-content -->

	    <?php get_template_part( 'template-parts/content', 'footer' ); ?>

	</article><!-- #post-<?php the_ID(); ?> -->