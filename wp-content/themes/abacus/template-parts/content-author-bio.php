<?php
/**
 * The template for displaying author bio.
 *
 * @package Abacus
 * @since Abacus 1.0
 */
 
?>

	<div class="author-info">
		<div class="author-avatar">
			<?php
			/**
			 * Filter the author bio avatar size.
			 *
			 * @param int $size The avatar height and width size in pixels.
			 */
			echo get_avatar( get_the_author_meta( 'user_email' ), apply_filters( 'abacus_author_bio_avatar_size', 100 ), '', '', array( 'class' => 'img-circle' ) );
			?>
		</div>

		<div class="author-heading">
			<span><?php _e( 'Author', 'abacus' ); ?></span>
			<h3 class="author-title"><?php echo get_the_author(); ?></h3>
			<?php if ( $user_url = get_the_author_meta( 'user_url' ) ) { ?>
				<a class="author-website" href="<?php echo esc_url( $user_url ); ?>"><?php echo esc_url( $user_url ); ?></a>
			<?php } ?>
		</div>

		<div class="author-bio">
			<?php the_author_meta( 'description' ); ?>
			<a class="author-link" href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>" rel="author">
				<?php printf( __( 'View all posts by %s', 'abacus' ), get_the_author() ); ?>
			</a>
		</div>
	</div>
