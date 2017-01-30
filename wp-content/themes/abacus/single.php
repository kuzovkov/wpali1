<?php
/**
 * The template for displaying all single posts and attachments
 *
 * @package Abacus
 * @since Abacus 1.0
 */
 
get_header(); ?>

	<div class="container">
		<div class="row">
			<div id="primary" class="cols">
				<?php
				while ( have_posts() ) : the_post();

					get_template_part( 'template-parts/content' );

					// If comments are open or we have at least one comment, load up the comment template.
					if ( comments_open() || get_comments_number() ) {
						comments_template();
					}

					// Previous/next post navigation.
					the_post_navigation( array(
						'next_text' => '<span class="meta-nav" aria-hidden="true">' . __( 'Next Post', 'abacus') . '</span> ' .
							'<span class="screen-reader-text">' . __( 'Next post:', 'abacus') . '</span> ' .
							'<span class="post-title">%title</span>',
						'prev_text' => '<span class="meta-nav" aria-hidden="true">' . __( 'Previous Post', 'abacus') . '</span> ' .
							'<span class="screen-reader-text">' . __( 'Previous post:', 'abacus') . '</span> ' .
							'<span class="post-title">%title</span>',
					) );

				endwhile;
				?>
			</div>

			<?php get_sidebar(); ?>
		</div>
	</div>

<?php get_footer(); ?>