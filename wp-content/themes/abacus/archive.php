<?php
/**
 * The template for displaying Archive pages.
 *
 * Used to display archive-type pages if nothing more specific matches a query.
 * For example, puts together date-based pages if no date.php file exists.
 *
 * If you'd like to further customize these archive views, you may create a
 * new template file for each specific one.
 *
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package Abacus
 * @since Abacus 1.0
 */
 
get_header(); ?>

	<div class="container">
		<div class="row">
			<section id="primary" class="cols">
				<?php if ( have_posts() ) : ?>
					<header id="archive-header">
						<?php
						the_archive_title( '<h1 class="entry-title archive-title">', '</h1>' );
						the_archive_description( '<div class="archive-meta">', '</div>' );
						?>
					</header><!-- #archive-header -->

					<?php
					while ( have_posts() ) : the_post();

						/* Include the template for the content. If you want to change
						 * this in a child theme then include a file called content-blocks.php
						 * in the /template-parts folder and that will be used instead.
						 */
						get_template_part( 'template-parts/content' );

					endwhile;
					?>

					<?php the_posts_navigation(); ?>
					<?php
				else :
					get_template_part( 'template-parts/content', 'none' );
				endif;
				?>
			</section><!-- #primary -->

			<?php get_sidebar(); ?>
		</div>
	</div>

<?php get_footer(); ?>