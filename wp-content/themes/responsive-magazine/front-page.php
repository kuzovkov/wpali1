<?php get_header(); ?>

<?php get_template_part('template-part', 'head'); ?>

<?php get_template_part('template-part', 'topnav'); ?>

<?php //carousel section ?>
<?php if ( get_theme_mod( 'responsive-magazine-featured-categories', '' ) != '' && get_theme_mod( 'responsive-magazine-get-featured', '0' ) == '1' ) : ?>
  <?php get_template_part('template-part', 'slider'); ?>
<?php endif; ?>
<!-- start content container -->
<div class="row rsrc-content">

    <?php //left sidebar ?>
    <?php get_sidebar( 'left' ); ?>

    <div class="col-md-<?php first_mag_main_content_width(); ?> rsrc-main">
        
  				<div id="content-top-section" class="clearfix">
    				<?php 
    					// Calling the sidebar if it exists.
    					if ( !dynamic_sidebar( 'first-mag-front-page' ) ):
    					 the_widget( 'first_mag_featured_posts_widget_second', 'title=' . __('Widget Title #1', 'responsive-magazine') . '&meta=on', 'before_title=<h3 class="widget-title"><div class="title-text">&after_title=</div><div class="widget-line"></div></h3>' );
    					 the_widget( 'first_mag_featured_posts_widget', 'title=' . __('Widget Title #2', 'responsive-magazine') . '&meta=on', 'before_title=<h3 class="widget-title"><div class="title-text">&after_title=</div><div class="widget-line"></div></h3>' );
    					endif; 
    				?>
  				</div>
				  <div class="front-page-content">
            <?php
    
                //if this was a search we display a page header with the results count. If there were no results we display the search form.
                if (is_search()) :
                    if ( function_exists( 'first_mag_breadcrumb' ) && get_theme_mod( 'breadcrumbs-check', 1 ) != 0 ) { first_mag_breadcrumb(); } 
                     $total_results = $wp_query->found_posts;
    
                     echo "<h2 class='text-center'>" . sprintf( __('%s Search Results for "%s"', 'responsive-magazine'),  $total_results, get_search_query() ) . "</h2>";
    
                     if ($total_results == 0) :
                         get_search_form(true);
                     endif;
    
                endif;
            ?>   
         
            <?php // theloop  
                if ( have_posts() ) : while ( have_posts() ) : the_post();?>

                   <?php
              				get_template_part( 'content', get_post_format() ); 
              			?>

                <?php endwhile; ?>
                  <div class="footer-pagination"><?php the_posts_pagination(); ?></div>
                <?php else: ?>

                    <?php get_404_template(); ?>

            <?php endif; ?>
         </div>   
   </div>

   <?php //get the right sidebar ?>
   <?php get_sidebar( 'right' ); ?>

</div>
<!-- end content container -->

<?php get_footer(); ?>

