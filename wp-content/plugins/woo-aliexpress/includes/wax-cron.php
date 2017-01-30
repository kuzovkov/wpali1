<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function wax_update_product_setup_schedule() {
	$schedule = get_option('wax_update_schedule');
	if( $schedule == 'off' && wp_next_scheduled( 'wax_update_product_event') ){
		$timestamp = wp_next_scheduled( 'wax_update_product_event' );
		wp_unschedule_event( $timestamp, 'wax_update_product_event' );
	}
	elseif( $schedule != 'off' && !wp_next_scheduled( 'wax_update_product_event' ) ){
		wp_schedule_event( time(), $schedule, 'wax_update_product_event');
	}
	elseif( $schedule != 'off' && wp_next_scheduled( 'wax_update_product_event' ) ){
		$timestamp = wp_next_scheduled( 'wax_update_product_event' );
		wp_unschedule_event( $timestamp, 'wax_update_product_event' );
		wp_schedule_event( time(), $schedule, 'wax_update_product_event');
	}
}

add_action( 'wax_update_product_event', 'wax_update_product_info' );
function wax_update_product_info(){
	set_time_limit(0);
	
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
	
	$products = get_posts(array(
			'post_type' => 'product',
			'showposts' => -1,
			'fields'    => 'ids'
	));
	
	foreach($products as $product){
		$sku = get_post_meta($product, '_sku', true );
		wax_update_product_product( $sku, $u_title, $u_content, $u_price, $u_attributes, $u_url, $appKey, $track, $rate, $percent, $clean, $affiliate, $lang);
	}
	
}

function wax_update_product_product($p_id, $u_title, $u_content, $u_price, $u_attributes, $u_url, $appKey, $track, $rate, $percent, $clean, $affiliate, $lang){
	
	$existing_product_query = array(
		'numberposts' => 1,
		'meta_key' => '_sku',
		'meta_query' => array(
			array(
				'key'=>'_sku',
				'value'=> $p_id,
				'compare' => '='
			)
		),
		'post_status' => array('publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash'),
		'post_type' => 'product');
		
	$is_product_exist = get_posts($existing_product_query);
	
	if( $is_product_exist ) {
			
		$product_id = $is_product_exist[0]->ID;
		
		if( $u_price ){

			$request_url 	= "http://gw.api.alibaba.com/openapi/param2/2/portals.open/";
			$request_api 	= "api.getPromotionProductDetail/{$appKey}?fields=";
			$request_fields = "originalPrice,salePrice&productId=$p_id";
			
			$request = wp_remote_get($request_url . $request_api . $request_fields);
			$p_data = json_decode($request['body'], true);
			
			if ( !is_wp_error($request) && isset($p_data['result']['originalPrice']) ){
			
				$originalPrice = str_replace(array('US $', '.00'), '', $p_data['result']['originalPrice']);
				$salePrice = str_replace(array('US $', '.00'), '', $p_data['result']['salePrice']);
				$o_percentPrice = 0;
				$s_percentPrice = 0;
				
				if( !in_array( trim($percent) , array('', 0) ) ){
					$o_percentPrice = $originalPrice * ($percent / 100);
					$s_percentPrice = $salePrice * ($percent / 100);
				}
				
				if( !in_array( trim($rate) , array('', 0, 1) ) ){
					$originalPrice = $originalPrice * $rate;
					$salePrice = $salePrice * $rate;
				}
				
				$originalPrice = $originalPrice + $o_percentPrice;
				$salePrice = $salePrice + $s_percentPrice;
				
				if ($salePrice > 0 && $salePrice < $originalPrice) {
					$price = $salePrice;
				} else {
					$price = $originalPrice;
				}
				
				update_post_meta($product_id, '_regular_price', $originalPrice);
				update_post_meta($product_id, '_sale_price', $salePrice);
				update_post_meta($product_id, '_price', $price);
			}
		}
			
		if( $u_title && $lang == 'en' ){
			$title_url  	= "http://gw.api.alibaba.com/openapi/param2/2/portals.open/";
			$title_api 		= "api.getPromotionProductDetail/{$appKey}?fields=";
			$title_fields 	= "productTitle&productId=$p_id";
			$title_request 	= wp_remote_get($title_url . $title_api . $title_fields);
			
			if ( !is_wp_error($title_request) && isset($title_request['body']) ){
				$title_content = json_decode($title_request['body'], true);
				if ( isset($title_content['result']['productTitle']) ){
					$product = array(
						'ID'            => $product_id,
						'post_title'    => $title_content['result']['productTitle'],
						/*'post_name'     => $title_content['result']['productTitle'],*/
					);
					wp_update_post( $product );
				}
			}
		}elseif( $u_title && $lang != 'en' ){
			$title = wax_get_product_Title($lang, $p_id);
			if ( $title != $p_id ){
				$product = array(
					'ID'            => $product_id,
					'post_title'    => $title,
					/*'post_name'     => $title,*/
				);
				wp_update_post( $product );
			}
		}
			
		$title 			= get_post_field('post_title', $product_id);
		$request_url 	= "http://gw.api.alibaba.com/openapi/param2/1/portals.open/";
		$request_api 	= "api.getPromotionProductDetail/{$appKey}?trackingId={$track}";
		$request_param 	= "&productId=$p_id";
		$request 		= wp_remote_get($request_url . $request_api . $request_param);
		$p_content 		= json_decode($request['body'], true);
		
		if( $u_content && $lang == 'en' && isset($p_content['result']['description']) ){
			$product = array(
				'ID'            => $product_id,
				'post_content'  => wax_product_optimizer($p_content['result']['description'], $title, $appKey, $track, $clean, 'no'),
				'post_excerpt'  => $p_content['result']['summary'],
			);
			wp_update_post( $product );
		}elseif( $u_content && $lang != 'en'){
			$desc_request = wp_remote_get("http://$lang.aliexpress.com/getSubsiteDescModuleAjax.htm?productId=$p_id");
			if ( !is_wp_error($desc_request) && isset($desc_request['body']) ){
				$desc = str_replace(array("window.productDescription='", "';"), '', $desc_request['body']);
				$desc = mb_convert_encoding($desc, 'HTML-ENTITIES', 'UTF-8');
				$desc = wax_product_optimizer($desc, $title, $appKey, $track, $clean, 'no');
				$product = array(
					'ID'            => $product_id,
					'post_content'  => $desc,
				);
				wp_update_post( $product );
			}
		}
			
		if( $u_attributes && isset($p_content['result']['attribute']) ){
			wax_wcproduct_set_attributes($product_id, $p_content['result']['attribute']);
		}
		
		if( $affiliate == 'yes' ){
			$content = get_post_field('post_content', $product_id);
			$product = array(
				'ID'            => $product_id,
				'post_content'  => wax_product_affiliate_fixer($content, $appKey, $track)
			);
			wp_update_post( $product );
		}
		
		if( $u_url ){
			
			if($lang == 'en'){
				$sub = 'www';
			}else{
				$sub = $lang;
			}
			
			/*$fix = '';
			if( in_array($lang, array('es', 'pt','ru') )){
				$fix = '-';
			}*/
			
			$request_url 	= "http://gw.api.alibaba.com/openapi/param2/2/portals.open/";
			$request_api 	= "api.getPromotionLinks/{$appKey}?trackingId={$track}&fields=promotionUrl";
			$request_param 	= "&urls=http://$sub.aliexpress.com/item/-/{$p_id}.html";
			$request = wp_remote_get($request_url . $request_api . $request_param);
			
			if ( !is_wp_error($request) ){
				$promotionUrl = json_decode($request['body'], true);
				$p_url = $promotionUrl['result']['promotionUrls'][0]['promotionUrl'];
				update_post_meta($product_id, '_product_url', $p_url);
			}
		}
		
		update_post_meta($product_id, 'wax_last_update', current_time('mysql') );
	}
}