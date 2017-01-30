<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Insert product base on the input of product id, name, url, price and category
function wax_wc_insert_product($p_id, $p_name, $p_url, $p_originalPrice, $p_salePrice, $p_cat, $p_cats, $p_tags){
	
	$appKey = get_option('wax_appKey');
	$track = get_option('wax_track_id');
	$wax_currency_rate = get_option('wax_currency_rate');
	$wax_percent_price = get_option('wax_percent_price');
	$clean = get_option('wax_content_cleaner');
	$affiliate = get_option('wax_affiliate_fixer');
	
	if( in_array( trim($wax_percent_price) , array('', 0) ) ){
		$p_originalPrice = str_replace(array('US $', '.00'), '', $p_originalPrice);
		$p_salePrice = str_replace(array('US $', '.00'), '', $p_salePrice);
	}else{
		$p_originalPrice = str_replace(array('US $', '.00'), '', $p_originalPrice);
		$o_percentPrice = $p_originalPrice * ($wax_percent_price / 100);
		$p_originalPrice = $p_originalPrice + $o_percentPrice;
		
		$p_salePrice = str_replace(array('US $', '.00'), '', $p_salePrice);
		$s_percentPrice = $p_salePrice * ($wax_percent_price / 100);
		$p_salePrice = $p_salePrice + $s_percentPrice;
	}
	
	if( !in_array( trim($wax_currency_rate) , array('', 0, 1) ) ){
		$p_originalPrice = $p_originalPrice * $wax_currency_rate;
		$p_salePrice = $p_salePrice * $wax_currency_rate;
	}
	
	$fields = array('wax_import_url', 'wax_import_image', 'wax_import_gallery', 'wax_import_attributes', 'wax_import_price', 'wax_import_summary', 'wax_import_content', 'wax_import_title', 'wax_product_lang');
	
	/*$request_url = "http://gw.api.alibaba.com/openapi/param2/2/portals.open/";
	$request_api = "api.getPromotionProductDetail/{$appKey}?trackingId={$track}";
	$request_param = "&productId=$p_id";
	$request = wp_remote_get($request_url . $request_api . $request_param);*/



	$request_url = "http://gw.api.alibaba.com/openapi/param2/2/portals.open/";
	$request_api = "api.getPromotionProductDetail/{$appKey}?fields=";
	$request_fields = "productId,productTitle,productUrl,imageUrl,originalPrice,salePrice,discount,evaluateScore,commission,commissionRate,30daysCommission,volume,packageType,lotNum,validTime,storeName,storeUrl";
	$request_param = "&productId=$p_id";

	wax_debug($request_url . $request_api . $request_fields . $request_param);
	$request = wp_remote_get($request_url . $request_api . $request_fields . $request_param);

	wax_debug($request['body']);

	if ( is_wp_error($request) )
    	return 'alibaba.com not response!';
		
	# var_dump($request);
	
	$p_data = json_decode($request['body'], true);
	
	if( $p_data['errorCode'] == 20010000 && $p_data['result']['productId'] == $p_id ){
		
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
		//wax_debug($existing_product_query);
		$is_product_exist = get_posts($existing_product_query);
		
		if( $is_product_exist ) {
			// Product id $is_product_exist[0]->ID
			return 'This product already exist!';
		}else{
			
			//wax_debug($fields);
			foreach($fields as $field){
				$$field = get_option($field);
				//wax_debug(get_option($field));
			}

			$content = wax_get_content($p_data['result']['productUrl']);
			
			if( $wax_product_lang == 'en' ){

				$desc = wax_product_optimizer($content, $p_name, $appKey, $track, $clean, $affiliate);
				//$desc = wax_product_optimizer($p_data['result']['description'], $p_name, $appKey, $track, $clean, $affiliate);
				wax_debug($desc);
			}else{
				//wax_debug("http://{$wax_product_lang}.aliexpress.com/getSubsiteDescModuleAjax.htm?productId=$p_id");
				//$desc = wp_remote_get("http://{$wax_product_lang}.aliexpress.com/getSubsiteDescModuleAjax.htm?productId=$p_id");

				//$desc = str_replace(array("window.productDescription='", "';"), '', $desc['body']);

				//$desc = mb_convert_encoding($desc, 'HTML-ENTITIES', 'UTF-8');

				$desc = mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8');
				$desc = wax_product_optimizer($desc, $p_name, $appKey, $track, $clean, $affiliate);
				$p_name = wax_get_product_Title($wax_product_lang, $p_id);
			}
			
			$summary = ( isset($p_data['result']['summary']) ) ? $p_data['result']['summary'] : '';
			$post_status = (get_option('wax_default_status')) ? get_option('wax_default_status') : 'publish';
			$user_ID = get_current_user_id();
			$product_arg = array(
			  'post_title'    => ($wax_import_title == "yes") ? $p_name : $p_id,
			  'post_content'  => ($wax_import_content == "yes") ? $desc : '',
			  'post_excerpt'  => ($wax_import_summary == "yes") ? $summary : '',
			  'post_status'   => $post_status,
			  'post_author'   => $user_ID,
			  'post_type' => 'product',
			);
			$product_id = wp_insert_post( $product_arg , true);
			//wax_debug($product_id);
			if( $wax_import_url == "yes"){
				$sub = ($wax_product_lang == 'en') ? 'www.' : "$wax_product_lang.";
				$p_url = str_replace( array('www.', 'item//') , array($sub, 'item/-/') , $p_url);
				
				/*if( in_array($wax_product_lang, array('es', 'pt','ru') )){
					$p_url = str_replace('item//', 'item/-/', $p_url);
				}*/
				
				$request_url = "http://gw.api.alibaba.com/openapi/param2/2/portals.open/";
				$request_api = "api.getPromotionLinks/{$appKey}?trackingId={$track}&fields=promotionUrl";
				$request_param = "&urls=$p_url";
				
				$request = wp_remote_get($request_url . $request_api . $request_param);
				
				if ( is_wp_error($request) )
					return 'alibaba.com not response!';
			
				$promotionUrl = json_decode($request['body'], true);
				$p_url = $promotionUrl['result']['promotionUrls'][0]['promotionUrl'];
			}else{
				$p_url = '';
			}
			
			if($product_id){
				
				if( $wax_import_price == "yes"){
					if ($p_salePrice > 0 && $p_salePrice < $p_originalPrice) {
						$price = $p_salePrice;
					} else {
						$price = $p_originalPrice;
					}
					update_post_meta($product_id, '_regular_price', $p_originalPrice);
					update_post_meta($product_id, '_sale_price', $p_salePrice);
					update_post_meta($product_id, '_price', $price);
				}
				
				wax_product_meta($product_id);
				wax_product_cat($product_id, $p_cat, $p_cats);
				wax_product_tag($product_id, $p_data['result']['keywords'], $p_tags);
				update_post_meta($product_id, '_product_url', $p_url);
				
				update_post_meta($product_id, '_sku', $p_id);
				
				if( $wax_import_image == "yes"){
					$thumbnail_id = wax_image_attacher($p_data['result']['imageUrl'], $product_id);
					set_post_thumbnail( $product_id, $thumbnail_id );
				}
				
				if( $wax_import_attributes == "yes"){
					wax_wcproduct_set_attributes($product_id, $p_data['result']['attribute']);
				}
				
				if( $wax_import_gallery == "yes"){
					wax_image_poster($p_data['result']['subImageUrl'], $product_id);
				}
				
				return "Product inserted : Product id $product_id";
			}else{
				return "Can't insert product, please try again!";
			}
		}
		
	}elseif( $p_data['errorCode'] == 20010000 && $p_data['result']['productId'] != $p_id ){
		return "System Error";
	}
	elseif( $p_data['errorCode'] == 20130000 || $p_data['errorCode'] == 20030100 ){
		return "Input parameter Product ID is error";
	}
	/*
	elseif($p_data['error_code'] == 400 ){
		return "{$p_data['error_message']}";
	}
	*/
	elseif($p_data['errorCode'] == 20030000 ){
		return "Required parameters";
	}
	elseif($p_data['errorCode'] == 20030010 ){
		return "Keyword input parameter error";
	}
	elseif($p_data['errorCode'] == 20030020 ){
		return "Category ID input parameter error or formatting errors";
	}
	elseif($p_data['errorCode'] == 20030030 ){
		return "Commission rate input parameter error or formatting errors";
	}
	elseif($p_data['errorCode'] == 20030040 ){
		return "Unit input parameter error or formatting errors";
	}
	elseif($p_data['errorCode'] == 20030050 ){
		return "30 days promotion amount input parameter error or formatting errors";
	}
	elseif($p_data['errorCode'] == 20030060 ){
		return "Tracking ID input parameter error or limited length";
	}
	elseif($p_data['errorCode'] == 20030070 ){
		return "Unauthorized transfer request";
	}
	elseif($p_data['errorCode'] == 20020000 ){
		return "System Error";
	}
}

function wax_product_meta($product_id){
	
	$product_type = (get_option('wax_default_type')) ? get_option('wax_default_type') : 'external';
	$product_visibility = (get_option('wax_default_visibility')) ? get_option('wax_default_visibility') : 'visible';
	$product_featured = get_option('wax_default_featured');
	$button_text = (get_option('wax_button_text')) ? get_option('wax_button_text') : '';
	
	wp_set_object_terms($product_id, $product_type, 'product_type');
	update_post_meta($product_id, '_visibility', $product_visibility);
	update_post_meta($product_id, '_featured', $product_featured);
	update_post_meta($product_id, '_button_text', $button_text);
	// update_post_meta($product_id, '_sold_individually', 'yes');
}

function wax_product_cat($product_id, $p_cat, $p_cats){
	$wax_default_cat = get_option('wax_default_cats') .','. $p_cats;
	$wax_user_cat = explode(',', $wax_default_cat);
	$output = array();
	foreach ($wax_user_cat as $element) {
		if (is_numeric($element)) {
			$output[] = intval($element);
		} else {
			$output[] = trim($element);
		}
	}
	$output[] = get_option('wax_default_cat');
	if(get_option('wax_default_product_cat') == "yes"){
		$output[] = wax_id_to_cat_slug($p_cat);
	}
	wp_set_object_terms($product_id, $output, 'product_cat', true );
}

function wax_product_tag($product_id, $product_tag, $p_tags){
	$wax_default_tag = get_option('wax_product_tag') .','. $p_tags;
	$wax_user_tag = explode(',', $wax_default_tag);
	$output = array();
	foreach ($wax_user_tag as $element) {
		if (is_numeric($element)) {
			$output[] = intval($element);
		} else {
			$output[] = trim($element);
		}
	}
	if(get_option('wax_default_product_tag') == "yes"){
		$output[] = $product_tag;
	}
	wp_set_object_terms($product_id, $output, 'product_tag', true );
}

// Insert product on click product thumbnail in list table
add_action('wp_ajax_wax_ajax_insert_product', 'wax_ajax_insert_product');
function wax_ajax_insert_product(){
	$link = $_POST['data'];
	$p_id = $link['id'];
	$p_name = $link['name'];
	$p_url = $link['url'];
	$p_originalPrice = $link['originalprice'];
	$p_salePrice = $link['saleprice'];
	$p_cat = $link['cat'];
	$p_cats = (isset($link['cats'])) ? $link['cats'] : '';
	$p_tags = (isset($link['tag'])) ? $link['tag'] : '';
	$product_id = wax_wc_insert_product($p_id, $p_name, $p_url, $p_originalPrice, $p_salePrice, $p_cat, $p_cats, $p_tags);
	echo json_encode( array("msg" => $product_id) );
	exit;
}

// Set product attributes
function wax_wcproduct_set_attributes($post_id, $attributes) {
	
	$name = array_column($attributes, 'name');
	$count = array_count_values($name);
	$duplicate = get_duplicates( $name );
	$product_attributes = '';
	
	foreach ($attributes as $name => $value) {
		if( isset($duplicate[$name + 1 ]) ){
			$val = array();
			for ($i = 0; $i < $count[$value['name']]; $i++) {
				$val[] = $attributes[$name+$i]['value'] ;
			}
			$product_attributes[str_replace(' ', '-', $value['name'])] = array (
				'name' =>  $value['name'],
				'value' => implode(', ', $val),
				'position' => 0,
				'is_visible' => 1,
				'is_variation' => 0,
				'is_taxonomy' => 0
			);
		}elseif( !array_search($value['name'], $duplicate) ){
			$product_attributes[str_replace(' ', '-', $value['name'])] = array (
				'name' =>  $value['name'],
				'value' => $value['value'],
				'position' => 0,
				'is_visible' => 1,
				'is_variation' => 0,
				'is_taxonomy' => 0
			);
		}
	}
    update_post_meta($post_id, '_product_attributes', $product_attributes);
}

// Attach bulk uploded images for post
function wax_image_poster($image_urls, $post_id){
	$image_gallery_ids = '';
	foreach(array_slice($image_urls, 1) as $image_url){
		$image_gallery_ids .= wax_image_attacher($image_url, $post_id) . ',';
	}
	update_post_meta($post_id, '_product_image_gallery',  $image_gallery_ids);
}

// Attach uploaded image for post
function wax_image_attacher($image_url, $post_id){
	$image_url = wax_download_url($image_url);
	if( $image_url ){
		$file_array = array(
				'name' => basename( $image_url ),
				'size' => filesize($image_url),
				'tmp_name' => $image_url
			);
		$attachment_id = media_handle_sideload( $file_array, $post_id );
		return $attachment_id;
	}else{
		return false;
	}
}

// Download file remote url to wordperss upload dir
function wax_download_url($url){
	$wp_upload_dir = wp_upload_dir();
	$parsed_url = parse_url($url);
	$pathinfo = pathinfo($parsed_url['path']);
	
	$dest_filename = wp_unique_filename( $wp_upload_dir['path'], $pathinfo['basename'] );
	$dest_path = $wp_upload_dir['path'] . '/' . $dest_filename;
	$dest_url = $wp_upload_dir['url'] . '/' . $dest_filename;
	
	$response = wp_remote_get($url);
	if ( is_wp_error($response) ){
    	return false;
	}elseif( !in_array($response['response']['code'], array(404, 403)) ){
		file_put_contents($dest_path, $response['body']);
	}

	if(!file_exists($dest_path)) {
		return false;
	}else{
		return $dest_path;
	}
}

function wax_id_to_cat_slug($cat_id){
	switch($cat_id){
		case 3:
			return 'Apparel & Accessories';
		case 34:
			return 'Automobiles & Motorcycles';
		case 1501:
			return 'Baby Products';
		case 66:
			return 'Beauty & Health';
		case 7:
			return 'Computer & Networking';
		case 13:
			return 'Construction & Real Estate';
		case 44:
			return 'Consumer Electronics';
		case 100008578:
			return 'Customized Products';
		case 5:
			return 'Electrical Equipment & Supplies';
		case 502:
			return 'Electronic Components & Supplies';
		case 2:
			return 'Food';
		case 1503:
			return 'Furniture';
		case 200003655:
			return 'Hair & Accessories';
		case 42:
			return 'Hardware';
		case 15:
			return 'Home & Garden';
		case 6:
			return 'Home Appliances';
		case 200003590:
			return 'Industry & Business';
		case 36:
			return 'Jewelry & Watch';
		case 39:
			return 'Lights & Lighting';
		case 1524:
			return 'Luggage & Bags';
		case 21:
			return 'Office & School Supplies';
		case 509:
			return 'Phones & Telecommunications';
		case 30:
			return 'Security & Protection';
		case 322:
			return 'Shoes';
		case 200001075:
			return 'Special Category';
		case 18:
			return 'Sports & Entertainment';
		case 1420:
			return 'Tools';
		case 26:
			return 'Toys & Hobbies';
		case 1511:
			return 'Watches';
		default:
			return $cat_id;
	}
}

function get_duplicates( $array ) {
	return array_unique( array_diff_assoc( $array, array_unique( $array ) ) );
}

/**
 * This file is part of the array_column library
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Copyright (c) 2013 Ben Ramsey <http://benramsey.com>
 * @license http://opensource.org/licenses/MIT MIT
 */

if (!function_exists('array_column')) {

    /**
     * Returns the values from a single column of the input array, identified by
     * the $columnKey.
     *
     * Optionally, you may provide an $indexKey to index the values in the returned
     * array by the values from the $indexKey column in the input array.
     *
     * @param array $input A multi-dimensional array (record set) from which to pull
     *                     a column of values.
     * @param mixed $columnKey The column of values to return. This value may be the
     *                         integer key of the column you wish to retrieve, or it
     *                         may be the string key name for an associative array.
     * @param mixed $indexKey (Optional.) The column to use as the index/keys for
     *                        the returned array. This value may be the integer key
     *                        of the column, or it may be the string key name.
     * @return array
     */
    function array_column($input = null, $columnKey = null, $indexKey = null)
    {
        // Using func_get_args() in order to check for proper number of
        // parameters and trigger errors exactly as the built-in array_column()
        // does in PHP 5.5.
        $argc = func_num_args();
        $params = func_get_args();

        if ($argc < 2) {
            trigger_error("array_column() expects at least 2 parameters, {$argc} given", E_USER_WARNING);
            return null;
        }

        if (!is_array($params[0])) {
            trigger_error('array_column() expects parameter 1 to be array, ' . gettype($params[0]) . ' given', E_USER_WARNING);
            return null;
        }

        if (!is_int($params[1])
            && !is_float($params[1])
            && !is_string($params[1])
            && $params[1] !== null
            && !(is_object($params[1]) && method_exists($params[1], '__toString'))
        ) {
            trigger_error('array_column(): The column key should be either a string or an integer', E_USER_WARNING);
            return false;
        }

        if (isset($params[2])
            && !is_int($params[2])
            && !is_float($params[2])
            && !is_string($params[2])
            && !(is_object($params[2]) && method_exists($params[2], '__toString'))
        ) {
            trigger_error('array_column(): The index key should be either a string or an integer', E_USER_WARNING);
            return false;
        }

        $paramsInput = $params[0];
        $paramsColumnKey = ($params[1] !== null) ? (string) $params[1] : null;

        $paramsIndexKey = null;
        if (isset($params[2])) {
            if (is_float($params[2]) || is_int($params[2])) {
                $paramsIndexKey = (int) $params[2];
            } else {
                $paramsIndexKey = (string) $params[2];
            }
        }

        $resultArray = array();

        foreach ($paramsInput as $row) {

            $key = $value = null;
            $keySet = $valueSet = false;

            if ($paramsIndexKey !== null && array_key_exists($paramsIndexKey, $row)) {
                $keySet = true;
                $key = (string) $row[$paramsIndexKey];
            }

            if ($paramsColumnKey === null) {
                $valueSet = true;
                $value = $row;
            } elseif (is_array($row) && array_key_exists($paramsColumnKey, $row)) {
                $valueSet = true;
                $value = $row[$paramsColumnKey];
            }

            if ($valueSet) {
                if ($keySet) {
                    $resultArray[$key] = $value;
                } else {
                    $resultArray[] = $value;
                }
            }

        }

        return $resultArray;
    }

}