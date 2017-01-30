<?php
/**
 * Custom template tags for Abacus
 *
 * Eventually, some of the functionality here could be replaced by core features.
 *
 * @package Abacus
 * @since Abacus 1.0
 */
 
function abacus_login_register_menu() {
	?>
	<ul class="pull-left">
		<?php
		$login_url = wp_login_url( get_permalink() );
		if ( class_exists( 'woocommerce' ) ) {
			$myaccount_page_id = get_option( 'woocommerce_myaccount_page_id' );
			if ( $myaccount_page_id ) {
				$login_url = get_permalink( $myaccount_page_id );
			}
		}

		if ( is_user_logged_in() ) {
			$current_user = wp_get_current_user();
			?>
			<li><p><?php printf( __( 'Welcome, %s', 'abacus' ), '<em>' . $current_user->display_name . '</em>' ); ?></p></li>
			<li><a href="<?php echo esc_url( wp_logout_url( get_permalink() ) ); ?>" title="<?php esc_attr_e( 'Logout', 'abacus' ); ?>"><?php _e( 'Logout', 'abacus' ); ?></a></li>
		<?php } else {
			$login_text = ( get_option( 'users_can_register' ) || 'yes' === get_option( 'woocommerce_enable_myaccount_registration' ) ) ? __( 'Login <span>or</span> Register', 'abacus' ) : __( 'Login', 'abacus' );
			?>
			<li><a href="<?php echo esc_url( $login_url ); ?>" title="<?php echo esc_attr( $login_text ); ?>"><?php echo $login_text; ?></a></li>
		<?php } ?>
	</ul>
	<?php
}

function abacus_top_menu() {
	?>
	<ul class="pull-right">
		<?php
		if ( has_nav_menu( 'top' ) ) {
			wp_nav_menu( array( 'theme_location' => 'top', 'items_wrap' => '%3$s', 'container' => '' ) );
		}
		if ( class_exists( 'woocommerce' ) ) {
			$shop_page_url = get_permalink( wc_get_page_id( 'shop' ) );
			$myaccount_page_id = get_option( 'woocommerce_myaccount_page_id' );
			?>
			<li><a href="<?php echo esc_url( $shop_page_url ); ?>"><?php _e( 'Shop', 'abacus' ); ?></a></li>
			<?php if ( $myaccount_page_id && is_user_logged_in() ) { ?>
				<li><a href="<?php echo esc_url( get_permalink( $myaccount_page_id ) ); ?>"><?php _e( 'My Account', 'abacus' ); ?></a></li>
			<?php }
			//abacus_cart_link();
		}
		?>
	</ul>
	<?php
}

function abacus_search_title( $query  ) {
    $num = $query->found_posts;
    $type = ( isset( $query->query['post_type'] ) ) ? $query->query['post_type'] : 'post';
	$paged = ( get_query_var('paged') ) ? get_query_var( 'paged' ) : 1;
	$posts_per_page = $query->query_vars['posts_per_page'];

	$current_page_count = ( 1 == $paged ) ?  '1-' . $paged * $posts_per_page : ( $paged - 1 ) * $posts_per_page + 1 . '-' . $paged * $posts_per_page;
	$current_page_count = ( $query->max_num_pages == $paged ) ? ( $paged - 1 ) * $posts_per_page + 1 . '-' . $query->found_posts : $current_page_count;

	printf( __( '<span class="pull-left">%1$s</span><span class="displaying pull-right">%2$s of %3$s results for "%4$s"</span>', 'abacus'),
	    esc_attr( ucfirst( $type . 's' ) ),
	    $current_page_count,
	    absint( $query->found_posts ),
	    esc_html( get_search_query() )
	);
}

function abacus_search_pagination( $query ) {
	$max_page = $query->max_num_pages;
	$paged = ( get_query_var('paged') ) ? get_query_var( 'paged' ) : 1;
    $type = ( isset( $query->query['post_type'] ) ) ? $query->query['post_type'] : 'post';
	$prev_search_post_type = ( 2 == $paged ) ? '' : '&#038;search_post_type=' . esc_attr( $type );

	$next_post_link = str_replace( '&#038;search_post_type=' . esc_attr( $type ), '', next_posts( $max_page, false ) );
	$prev_post_link = str_replace( '&#038;search_post_type=' . esc_attr( $type ), '', previous_posts( false ) );
	$prev_label = ( 2 == $paged ) ? __( 'All results &rarr;', 'abacus' ) : sprintf( __( 'Next %s results &rarr;', 'abacus' ), ucfirst( $type . 's' ) );

	// Don't print empty markup if there's only one page.
	if ( $max_page < 2 )
		return;

	$next_posts_link = ( intval($paged) + 1 <= $max_page ) ? '<a href="' . esc_url( $next_post_link ) . '&#038;search_post_type=' . esc_attr( $type ) . '">' . sprintf( __( '&larr; Previous %s results', 'abacus' ), ucfirst( $type . 's' ) ) . '</a>' : '&nbsp;';
	$prev_posts_link = ( $paged > 1 ) ? '<a href="' . esc_url( $prev_post_link ) . $prev_search_post_type . '">' . $prev_label . '</a>' : '&nbsp;';
	?>

	<nav id="posts-pagination" role="navigation">
		<div class="screen-reader-text"><?php _e( 'Posts navigation', 'abacus' ); ?></div>
		<?php if ( $next_posts_link ) : ?>
		<div class="previous"><?php echo $next_posts_link; ?></div>
		<?php endif; ?>

		<?php if ( $prev_posts_link ) : ?>
		<div class="next"><?php echo $prev_posts_link; ?></div>
		<?php endif; ?>
	</nav><!-- .navigation -->
	<?php
	wp_reset_query();
}

function abacus_word_count() {
	return sprintf(
		__( '%s words', 'abacus' ),
		str_word_count( strip_tags( get_post_field( 'post_content', get_the_ID() ) ) )
	);
}

add_filter( 'pre_get_posts', 'abacus_search' );
function abacus_search( $query ) {
	if ( ! is_admin() && $query->is_search && $query->is_main_query() ) {
		$query->set( 'post_type', array( 'post' ) );
	}
	
    return $query;
}

add_filter( 'excerpt_more', 'abacus_excerpt_more' );
if ( ! function_exists( 'abacus_excerpt_more' ) ) {
	function abacus_excerpt_more( $more ) {
		$abacus_default_theme_options = abacus_default_theme_options();

		return '&hellip; <div><a class="more-link" href="' . esc_url( get_permalink() ) . '">' . abacus_sanitize_text( get_theme_mod( 'read_more_text', $abacus_default_theme_options['read_more_text'] ) ) . ' <span class="screen-reader-text">' . get_the_title() . '</span></a></div>';
	}
}

add_filter( 'the_content_more_link', 'abacus_remove_more_link_scroll' );
if ( ! function_exists( 'abacus_remove_more_link_scroll' ) ) {
	function abacus_remove_more_link_scroll( $link ) {
		return preg_replace( '|#more-[0-9]+|', '', $link );
	}
}