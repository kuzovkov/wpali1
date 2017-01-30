<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

add_action('wp_ajax_wax_update_product_now', 'wax_update_product_now');
function wax_update_product_now(){
	$product_id = (isset($_POST['data'])) ? esc_sql($_POST['data']) : '';
	$u_title = (get_option('wax_update_title') == "yes") ? true : false;
	$u_content = (get_option('wax_update_content') == "yes") ? true : false;
	$u_price = (get_option('wax_update_price') == "yes") ? true : false;
	$u_attributes = (get_option('wax_update_attributes') == "yes") ? true : false;
	$u_url = (get_option('wax_update_url') == "yes") ? true : false;
	
	$appKey = get_option('wax_appKey');
	$track = get_option('wax_track_id');
	$rate = get_option('wax_currency_rate');
	$percent = get_option('wax_percent_price');
	$clean = get_option('wax_content_cleaner');
	$affiliate = get_option('wax_update_links');
	$lang = get_option('wax_product_lang');
	
	$sku = get_post_meta($product_id, '_sku', true );
	
	wax_update_product_product( $sku, $u_title, $u_content, $u_price, $u_attributes, $u_url, $appKey, $track, $rate, $percent, $clean, $affiliate, $lang);
	
	echo json_encode( array("msg" => 'Updated!') );
	exit;
}

add_action('wp_ajax_wax_external_link_click', 'wax_external_link_click');
add_action('wp_ajax_nopriv_wax_external_link_click', 'wax_external_link_click');
function wax_external_link_click(){
	$product_id = (isset($_POST['data'])) ? esc_sql($_POST['data']) : '';
	$clicks = get_post_meta( $product_id, 'wax_product_clicks', true);
	update_post_meta($product_id, 'wax_product_clicks', $clicks + 1);
	exit;
}

add_action('woocommerce_before_add_to_cart_button', 'wax_add_id_to_external_product', 10, 2);
function wax_add_id_to_external_product() {
	// global $product;
	$product_id = get_the_ID();
	$views = get_post_meta( $product_id, 'wax_product_views', true);
	update_post_meta($product_id, 'wax_product_views', $views + 1);
	/*if($product->product_type == 'external'){
		echo "<meta itemprop=\"productId\" content=\"$id\">";
	}*/
	// echo "<meta itemprop=\"productId\" content=\"$product_id\">";
}

add_filter( 'woocommerce_locate_template', 'wax_woocommerce_locate_template', 10, 3 );
function wax_woocommerce_locate_template( $template, $template_name, $template_path ) {
	 
	global $woocommerce;
	$_template = $template;

	//echo $template_name;
	if ( ! $template_path ) $template_path = $woocommerce->template_url;
 
	$plugin_path  = WAX_ROOT_PATH . '/woocommerce/';

	// Look within passed path within the theme - this is priority 
	$template = locate_template(
		array(
			$template_path . $template_name,
			$template_name 
		)
	);
	 
	// Modification: Get the template from this plugin, if it exists
	if ( ! $template && file_exists( $plugin_path . $template_name ) )
		$template = $plugin_path . $template_name; 
	
	// Use default template 
	if ( ! $template )
		$template = $_template;
	
	// Return what we found 
	return $template;

}

add_filter('manage_product_posts_columns', 'wax_product_table_head');
function wax_product_table_head( $columns ) {
    $columns['wooaliexpress'] = 'Products Status';
    return $columns;
}

add_action( 'manage_product_posts_custom_column', 'wax_product_table_content', 10, 2 );
function wax_product_table_content( $column_name, $product_id ) {
    if ($column_name == 'wooaliexpress') {
		$views = number_format((float)get_post_meta( $product_id, 'wax_product_views', true) );
		$clicks = number_format((float)get_post_meta( $product_id, 'wax_product_clicks', true) );
		$last_update = get_post_meta( $product_id, 'wax_last_update', true);
		$last_update = ( empty($last_update) ) ? 'Never!' : $last_update;
    	echo "View : $views <br>\n
		Click : $clicks <br>\n Updated : $last_update <br>\n
		<a href=\"#\" class=\"wax-update-now\" data-id=\"$product_id\">Update now!</a>";
    }
}

add_filter( 'manage_edit-product_sortable_columns', 'wax_product_table_sorting' );
function wax_product_table_sorting( $columns ) {
    $columns['wooaliexpress'] = 'wooaliexpress';
  	return $columns;
}

add_filter( 'request', 'wax_wooaliexpress_column_orderby' );
function wax_wooaliexpress_column_orderby( $vars ) {
    if ( isset( $vars['orderby'] ) && 'wooaliexpress' == $vars['orderby'] ) {
        $vars = array_merge( $vars, array(
            'meta_key' => 'wax_product_views',
            'orderby' => 'meta_value_num'
        ) );
    }
    return $vars;
}

add_action('wp_enqueue_scripts', 'wax_enqueue_external_file');
function wax_enqueue_external_file() {
	wp_register_script('wax-script', WAX_ROOT_URL . 'js/wooaliexpress.js', array('jquery') );
	wp_localize_script('wax-script', 'wax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'tab' => get_option('wax_open_tab') ));
	wp_enqueue_script( 'wax-script');
}

// Enqueue style & script to admib page header
add_action( 'admin_head', 'wax_admin_header' );
function wax_admin_header() {
	$jquery_ui_theme = "http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/themes/redmond/jquery-ui.min.css";
	$chosen_style = plugins_url() . '/woocommerce/assets/css/chosen.css';
	
    wp_enqueue_style('jquery-ui-redmond', $jquery_ui_theme, false, null);
	wp_enqueue_style('chosen', $chosen_style);
	wp_enqueue_style('wax-style', WAX_ROOT_URL . 'css/wax-style.css');
	
	wp_enqueue_script( 'jquery-ui-slider' );
	wp_enqueue_script( 'c-chosen', WAX_ROOT_URL . 'js/chosen-create-option.jquery.js', array( 'jquery' ));
	
	wp_register_script('wax-script', WAX_ROOT_URL . 'js/wax-script.js', array('jquery') );
	wp_localize_script('wax-script', 'wax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ));
	wp_enqueue_script( 'wax-script');
}