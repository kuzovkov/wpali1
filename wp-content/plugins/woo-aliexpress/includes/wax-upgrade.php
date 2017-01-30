<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function wax_update_args(){
	return array(
		'path' => 'http://update.brazuka.ir/',
		'slug' => 'wooaliexpress'
	);
}

add_filter( 'pre_set_site_transient_update_plugins', 'wax_check_update' );
function wax_check_update( $transient ){
	
	if ( empty( $transient->checked ) ) {
		return $transient;
	}
	
	$plugin_slug = WAX_PLUGIN_NAME;
	$args = wax_update_args();
	$current_version = $transient->checked[$plugin_slug];
	$remote_version = wax_getRemote_information('version', get_option('wax_purchase_code'));
	
	/*
	if( !$remote_version->valid ){
		update_option('wax_purchase_valid', 'invalid');
	}
	*/
	
	if ( version_compare( $current_version, $remote_version->new_version, '<' ) ) {
		$obj = new stdClass();
		$obj->slug = $args['slug'];
		$obj->new_version = $remote_version->new_version;
		$obj->plugin = $plugin_slug;
		$obj->url = $remote_version->url;
		if( isset($remote_version->package) ){
			$obj->package = $remote_version->package;
		}
		$transient->response[$plugin_slug] = $obj;
	}
	
	return $transient;
}

add_filter( 'plugins_api', 'wax_check_info', 10, 3 );
function wax_check_info($false, $action, $arg){
	$args = wax_update_args();
	
	if ( isset($arg->slug) && $arg->slug === $args['slug'] ) {
		$information = wax_getRemote_information('info', get_option('wax_purchase_code'));
		/*
		if( !$information->valid ){
			update_option('wax_purchase_valid', 'invalid');
		}
		*/
		return $information;
	}
	
	return false;
}

function wax_getRemote_information($action, $purchase){
	global $wp_version;
	
	$args = wax_update_args();
	$params = array(
		'body' => array(
			'action' => $action,
			'code' => $purchase,
			'slug' => $args['slug'],
			'site' => get_bloginfo('url'),
		),
		'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo('url')
	);
	$request = wp_remote_post( $args['path'], $params );
	
	if (!is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) === 200 ) {
		return @unserialize( $request['body'] );
	}
	
	return false;
}

add_action( "in_plugin_update_message-" . WAX_PLUGIN_NAME, 'wax_print_notice', 10, 0 );
function wax_print_notice() {
	if( get_option('wax_purchase_valid') == 'invalid' ){
	?>
	<tr class="plugin-update-tr">
		<td class="plugin-update" colspan="3">
			<div class="update-message">
            	<?php
				$link = '<a href="' . admin_url('admin.php?page=wc-settings&tab=wooaliexpress') . '">Enter Purchase Code</a>';
				echo "Purchase Code is not set. Please enter your purchase code to enable automatic updates.";
				?>
			</div>
		</td>
	<?php
	}
}

add_filter('plugin_action_links_' . WAX_PLUGIN_NAME, 'wax_plugin_action_links');
function wax_plugin_action_links($links){
	return array_merge(array('<a href="' . admin_url('admin.php?page=wc-settings&tab=wooaliexpress') . '">' . 'Settings' . '</a>'), $links);

	return $links;
}