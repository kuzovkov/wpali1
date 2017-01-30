<?php
/**
 * Woocommerce compatibility
 *
 * @package Abacus
 * @since Abacus 1.0
 */
 
remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );

add_action( 'woocommerce_before_main_content', 'abacus_wrapper_start', 10 );
function abacus_wrapper_start() {
	?>
	<div class="container">
		<div class="row">
			<div id="primary" class="cols">
	<?php
}

add_action( 'woocommerce_after_main_content', 'abacus_wrapper_end', 10 );
function abacus_wrapper_end() {
	?>
			</div>
			<?php get_sidebar(); ?>
		</div>
	</div>
	<?php
}

function abacus_woocommerce_activated() {
	return class_exists( 'woocommerce' ) ? true : false;
}

function woocommerce_button_proceed_to_checkout() {
	$checkout_url = wc_get_checkout_url();

	?>
	<a href="<?php echo esc_url( $checkout_url ); ?>" class="checkout-button btn btn-danger btn-lg"><?php _e( 'Proceed to Checkout', 'abacus' ); ?></a>
	<?php
}

function abacus_cart_link() {
	if ( is_cart() ) {
		$class = ' current-menu-item';
	} else {
		$class = '';
	}
	?>
	<li class="site-header-cart<?php echo esc_attr( $class ); ?>">
		<a class="cart-contents" href="<?php echo esc_url( wc_get_cart_url() ); ?>" title="<?php esc_attr_e( 'View your shopping cart', 'abacus' ); ?>">
			<?php echo wp_kses_data( WC()->cart->get_cart_total() ); ?> <span class="count"><?php echo wp_kses_data( sprintf( _n( '%d Item', '%d Items', WC()->cart->get_cart_contents_count(), 'abacus' ), WC()->cart->get_cart_contents_count() ) );?></span>
		</a>
		<ul><li><?php the_widget( 'WC_Widget_Cart', 'title=' ); ?></li></ul>
	</li>
	<?php
}

add_filter( 'woocommerce_order_button_html', 'abacus_woocommerce_order_button_html' );
function abacus_woocommerce_order_button_html() {
	$order_button_text = apply_filters( 'woocommerce_order_button_text', __( 'Place order', 'abacus' ) );
	return '<input type="submit" class="btn btn-danger btn-lg" name="woocommerce_checkout_place_order" id="place_order" value="' . esc_attr( $order_button_text ) . '" data-value="' . esc_attr( $order_button_text ) . '" />';
}

add_filter( 'woocommerce_output_related_products_args', 'abacus_output_related_products' );
function abacus_output_related_products() {
	return array(
		'posts_per_page' => 3,
		'columns' => 3,
		'orderby' => 'rand'
	);
}

remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15 );
add_action( 'woocommerce_after_single_product_summary', 'abacus_woocommerce_output_upsells', 15 );
function abacus_woocommerce_output_upsells() {
    woocommerce_upsell_display( 3,3 ); // Display 3 products in rows of 3
}

add_filter( 'woocommerce_product_single_add_to_cart_text', 'abacus_custom_cart_button_text' );
add_filter( 'woocommerce_product_add_to_cart_text', 'abacus_custom_cart_button_text' );
function abacus_custom_cart_button_text() {
	return '+';
}

remove_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10 );
remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5 );

add_action( 'woocommerce_shop_loop_item_title', 'abacus_woocommerce_template_loop_product_title', 10 );
function abacus_woocommerce_template_loop_product_title() {
	echo '</a><div class="shop-item-meta"><a href="' . esc_url( get_permalink() ) . '"><h3>' . get_the_title() . '</h3></a>';

    $product_cats = wp_get_post_terms( get_the_ID(), 'product_cat' );

    if ( $product_cats && ! is_wp_error ( $product_cats ) ){
        $single_cat = array_shift( $product_cats ); ?>
        <span class="product_category_title"><?php echo $single_cat->name; ?></span>
		<?php
	}
}

add_action( 'woocommerce_after_shop_loop_item', 'abacus_woocommerce_template_loop_product_link_close', 99 );
function abacus_woocommerce_template_loop_product_link_close() {
	echo '</div>';
}