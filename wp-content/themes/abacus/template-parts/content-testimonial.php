<?php
/**
 * The template used for displaying testimonials.
 *
 * @package Abacus
 * @since Abacus 1.0
 */
 
$abacus_default_theme_options = abacus_default_theme_options();
?>
	<article id="post-<?php the_ID(); ?>" <?php post_class( 'cols cols-2' ); ?>>
		<div class="entry-content">
			<i class="fa fa-quote-left"></i>
			<?php if ( is_front_page() )  { the_excerpt(); } else { the_content( abacus_sanitize_text( get_theme_mod( 'read_more_text', $abacus_default_theme_options['read_more_text'] ) ) . ' <span class="screen-reader-text">' . get_the_title() . '</span>' ); } ?>
		</div>

		<header class="entry-header">
			<?php if ( has_post_thumbnail() ) : ?>
				<div class="testimonial-thumbnail">
					<?php the_post_thumbnail( 'abacus-testimonial-thumbnail', array( 'class' => 'img-circle' ) ); ?>
				</div>
			<?php endif; ?>

			<?php the_title( '<h3 class="entry-title">', '</h3>' ); ?>
		</header>
	</article><!-- #post-## -->
