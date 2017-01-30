<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function wax_built_product_categories(){
	// wp_dropdown_categories('taxonomy=product_cat&type=product&hide_empty=0&name=product_categories' );
	$sel = (isset($_GET['product_categories'])) ? $_GET['product_categories'] : array() ;
	$categories = get_categories('taxonomy=product_cat&type=product&hide_empty=0');
	$option = '';
	foreach ($categories as $category) {
		$select =  ( in_array($category->cat_ID, $sel)) ? ' selected="selected"' : '' ;
		$option .= "<option value=\"{$category->cat_ID}\"{$select}>";
		$option .= $category->cat_name;
		$option .= "</option>\n\t\t\t\t\t\t\t";
	}
	echo "<select multiple=\"\" name=\"product_categories[]\" id=\"product_categories\">\n\t\t\t\t\t\t\t";
	echo $option;
	echo "</select>\n\t\t\t\t\t";
}

function wax_get_product_Title($lang, $p_id){
	$title = $p_id;
	$url = "http://{$lang}.aliexpress.com/item/-/{$p_id}.html";
	
	/*if( in_array($lang, array('es', 'pt','ru')) ){
		$url = str_replace('item//', 'item/-/', $url);
	}*/
	
	$html = wp_remote_get($url);
	$dom = new DOMDocument();
	@$dom->loadHTML(mb_convert_encoding($html['body'], 'HTML-ENTITIES', 'UTF-8'));
	$dom->preserveWhiteSpace = false;
	$h1_tags = $dom->getElementsByTagName('h1');
	foreach($h1_tags as $h1_tag){
		if ( $h1_tag->getAttribute('class') == 'product-name'){
			$title = $h1_tag->nodeValue;
		}
	}
	return $title;
}

function wax_product_optimizer($content, $title, $appKey, $track, $clean, $affiliate){
	if( $clean == "yes" ){
		$content = wax_product_clean_contents($content, $title);
	}
	if( $affiliate == "yes" ){
		$content = wax_product_affiliate_fixer($content, $appKey, $track);
	}
	return $content;
}

function wax_product_affiliate_fixer($content, $appKey, $track){
	$lang = get_option('wax_product_lang');
	if( $lang != 'en' ){
		$content = preg_replace("/www/", $lang, $content);
	}
	$links = implode(',', wax_getUrls($content));
	
	$request_url = "http://gw.api.alibaba.com/openapi/param2/2/portals.open/";
	$request_api = "api.getPromotionLinks/{$appKey}?trackingId={$track}&fields=promotionUrl";
	$request_param = "&urls=$links";
	//wax_debug($request_url . $request_api . $request_param);
	$request = wp_remote_get($request_url . $request_api . $request_param);
	//wax_debug($request['body']);
	if ( !is_wp_error($request) ){
		$promotionUrl = json_decode($request['body'], true);
		wax_debug($promotionUrl);
		if( $promotionUrl != '' && isset($promotionUrl['result']) ){
			foreach($promotionUrl['result']['promotionUrls'] as $link){
				$content = str_replace($link['url'], $link['promotionUrl'], $content);
			}
		}
	}
	return $content;
}

function wax_product_clean_contents($html, $title){
    if (!is_string ($html) || trim ($html) == ""){
        return $html;
	}else{
		$html = str_replace('&nbsp;', '', $html);
		$doc = new DOMDocument(); 
		@$doc->loadHTML($html);
		foreach($doc->getElementsByTagName("*") as $node) {
			if( in_array($node->localName, array('p', 'span', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'img', 'strong') ) ){
				$node->removeAttribute("style");
			}
			if( trim($node->nodeValue) == '' && $node->localName == 'p' && $node->childNodes != 'img' ){
				$node->parentNode->removeChild($node);
			}
			if( $node->localName == 'img' ){
				$node->setAttribute("alt", $title);
				$node->setAttribute("title", $title);
			}
		}
		$html = preg_replace("~<(?:!DOCTYPE|/?(?:html|body))[^>]*>\s*~i", "", $doc->saveHTML());
		$html = str_replace(array('&nbsp;', '<span>', '</span>', '<strong>', '</strong>'), '', $html);
		$html = preg_replace('/<(\w+)\b(?:\s+[\w\-.:]+(?:\s*=\s*(?:"[^"]*"|"[^"]*"|[\w\-.:]+))?)*\s*\/?>\s*<\/\1\s*>/','',$html);
		
		return preg_replace( "/<([^<\/>]*)>([\s]*?|(?R))<\/\1>/imsU", "", $html);
	}
}

function wax_getUrls($html){
    $hrefs = array();
	$dom = new DOMDocument();
	@$dom->loadHTML($html);
    $dom->formatOutput = true;
	$tags = $dom->getElementsByTagName('a');
	foreach ($tags as $tag) {
		$hrefs[] =  $tag->getAttribute('href');
	}
	return $hrefs;
}

function wax_get_content($url){
	$res = wp_remote_get($url);
	$content = $res['body'];
	$dom = new DOMDocument();
	$dom->loadHTML($content);
	$desc = $dom->getElementById('j-product-desc');
	return $desc->textContent;

}