<?php
/**
 * The template for displaying 404 pages (Not Found).
 *
 * @package Abacus
 * @since Abacus 1.0
 */
 
get_header(); ?>

	<div class="container">
		<div class="row">
			<div id="primary" class="cols">
				<article id="post-0" class="post error404 not-found">
			    	<header>
						<h1 class="entry-title"><?php _e( 'Oops! That page can&rsquo;t be found.', 'abacus' ); ?></h1>
						<div class="archive-meta"><?php _e( 'It looks like nothing was found at this location. Perhaps try a search?', 'abacus' ); ?></div>
			        </header>

			        <div class="entry-content">
						<i class="fa fa-frown-o"></i>
			        </div>
			    </article>
			</div><!-- #primary -->

			<?php get_sidebar(); ?>
		</div>
	</div>

<?php get_footer(); ?>