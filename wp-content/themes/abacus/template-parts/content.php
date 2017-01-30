<?php
/**
 * The default template for displaying content
 *
 * Used for both single and front-page/index/archive/search.
 *
 * @package Abacus
 * @since Abacus 1.0
 */
 
?>
	<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	    <?php get_template_part( 'template-parts/content', 'header' ); ?>

	    <div class="entry-content">
		    <?php
			$abacus_default_theme_options = abacus_default_theme_options();

			if ( is_singular() ) {
			    the_content( abacus_sanitize_text( get_theme_mod( 'read_more_text', $abacus_default_theme_options['read_more_text'] ) ) . ' <span class="screen-reader-text">' . get_the_title() . '</span>' );
			} else {
				the_excerpt();
			}
			?>
	    </div><!-- .entry-content -->

		<?php
		// Author bio.
		if ( is_single() && get_the_author_meta( 'description' ) ) {
			get_template_part( 'template-parts/content', 'author-bio' );
		}
		?>

		<?php get_template_part( 'template-parts/content', 'footer' ); ?>

	</article> <!-- #post-<?php the_ID(); ?> -->