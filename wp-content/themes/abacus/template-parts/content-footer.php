<?php
/**
 * The template for displaying article footers
 *
 * @package Abacus
 * @since Abacus 1.0
 */
 
if ( is_singular() ) {
	?>
	<footer>
	    <?php
		wp_link_pages( array(
			'before'      => '<div class="page-links"><span class="page-links-title">' . __( 'Pages:', 'abacus' ) . '</span>',
			'after'       => '</div>',
			'link_before' => '<span>',
			'link_after'  => '</span>',
			'pagelink'    => '<span class="screen-reader-text">' . __( 'Page', 'abacus' ) . ' </span>%',
			'separator'   => '<span class="screen-reader-text">, </span>',
		) );

		the_tags( '<p class="tags"><span> ' . __( 'Tags:', 'abacus' ) . '</span>', ' ', '</p>' );

		edit_post_link( __( 'Edit', 'abacus' ), '<p class="edit-link">', '</p>' );
		?>

	</footer><!-- .entry -->
	<?php
}