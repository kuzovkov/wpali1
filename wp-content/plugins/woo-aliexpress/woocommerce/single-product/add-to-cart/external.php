<?php
/**
 * External product add to cart
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.1.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
$target = (get_option('wax_open_tab') == "yes") ? ' target="_blank"' : '';
?>
<?php do_action('woocommerce_before_add_to_cart_button'); ?>

<p class="cart">
	<a href="<?php echo $product_url; ?>" data-id="<?php the_ID(); ?>" rel="nofollow" class="single_add_to_cart_button button alt"<?php echo $target; ?>><?php echo apply_filters('single_add_to_cart_text', $button_text, 'external'); ?></a>
</p>

<!--
<form <?php echo $target; ?> method="get" action="<?php echo $product_url; ?>">
  <input class="single_add_to_cart_button button alt" data-id="<?php the_ID(); ?>" value="<?php echo apply_filters('single_add_to_cart_text', $button_text, 'external'); ?>" type="submit">
</form>
-->

<?php do_action('woocommerce_after_add_to_cart_button'); ?>