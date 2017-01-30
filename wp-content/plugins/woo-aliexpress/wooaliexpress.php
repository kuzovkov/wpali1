<?php
/*
Plugin Name: Woo Aliexpress (shared on wplocker.com)
Plugin URI: http://codecanyon.net/item/woo-aliexpress/9455663?ref=wpfollow
Description: Woo Aliexpress allows you to add thousands of AliExpress products to your woocommerce shop just by one click!
Version: 1.2
Author: <a href="http://codecanyon.net/user/wpfollow">wpfollow</a>
*/

define('WAX_ROOT_URL', plugin_dir_url(__FILE__) );
define('WAX_ROOT_PATH', plugin_dir_path(__FILE__) );
define('WAX_PLUGIN_NAME', plugin_basename(__FILE__));

// Include required files to access upload & image function
require_once(ABSPATH . 'wp-admin/includes/media.php');
require_once(ABSPATH . 'wp-admin/includes/file.php');
require_once(ABSPATH . 'wp-admin/includes/image.php');
require_once(ABSPATH . "wp-includes/pluggable.php");

// Include Plugin main files
require_once( 'includes/wax-content.php' );
require_once( 'includes/wax-core.php' );
require_once( 'includes/wax-cron.php' );
require_once( 'includes/wax-currency.php' );
require_once( 'includes/wax-files.php' );
require_once( 'includes/wax-list-table.php' );
require_once( 'includes/wax-search.php' );
require_once( 'includes/wax-settings.php' );
require_once( 'includes/wax-upgrade.php' );

register_activation_hook( __FILE__, 'wax_plugin_activated' );
function wax_plugin_activated(){
	update_option( 'wax_default_status', 'publish' );
	update_option( 'wax_default_type', 'external' );
	update_option( 'wax_default_visibility', 'visible' );
	update_option( 'wax_update_schedule', 'off' );
	update_option( 'wax_product_num', 20 );
	update_option( 'wax_product_lang', 'en' );
	
	$fields = array('wax_default_product_cat', 'wax_default_product_tag', 'wax_import_url', 'wax_import_image', 'wax_import_gallery', 'wax_import_attributes', 'wax_import_price', 'wax_import_summary', 'wax_import_content', 'wax_import_title', 'wax_content_cleaner', 'wax_affiliate_fixer');
	
	foreach($fields as $field){
		update_option($field, 'yes');
	}
	
	wax_update_product_setup_schedule();
}

register_deactivation_hook( __FILE__, 'wax_schedule_deactivation' );
function wax_schedule_deactivation() {
	wp_clear_scheduled_hook( 'wax_update_schedule' );
	wp_clear_scheduled_hook( 'wax_currency_schedule' );
}

add_action( 'wpmu_new_blog', 'wax_wpmu_create_new_site' );
function wax_wpmu_create_new_site( $blog_id ){
	switch_to_blog( $blog_id );
    wax_plugin_activated();
	restore_current_blog();
}

function wax_debug($msg){
	$file = ABSPATH . 'wax-debug.log';
	ob_start();
	var_dump($msg);
	$msg = ob_get_flush();
	ob_clean();
	$str = date('d.m.Y H:i:s', time()) . '   ' . $msg . "\n";
	file_put_contents($file, $str, FILE_APPEND);
}