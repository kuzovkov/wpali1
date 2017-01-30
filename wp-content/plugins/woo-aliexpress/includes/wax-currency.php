<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

wp_schedule_single_event( time() + 18000, 'wax_currency_schedule' );

add_action( 'wax_currency_schedule','wax_update_remote_currency_rate' );
function wax_update_remote_currency_rate() {
	$remote_rate = wax_get_remote_rate();
	update_option('wax_currency_remote_rate', $remote_rate);
	
	if( get_option('wax_use_remote_currency_rate') == "yes" ){
		update_option('wax_currency_rate', $remote_rate);
	}else{
		$default = get_option('wax_default_currency_rate');
		update_option('wax_currency_rate', $default);
	}
}

function wax_get_remote_rate(){
	$server = get_option('wax_currency_server', 'google');
	$default = get_option('wax_default_currency_rate');
	$currency = get_option('wax_currency_name');
	$amount = urlencode(1);
	$from = 'usd';
	
	switch ($server){

		case 'google':
			$from_Currency = urlencode($from);
			$to_Currency = urlencode($currency);
			$url = "http://www.google.com/finance/converter?a=$amount&from=$from_Currency&to=$to_Currency";
			$request = wp_remote_get($url);
			
			preg_match_all('/<span class=bld>(.*?)<\/span>/s', $request['body'], $matches);
			if (isset($matches[1][0])){
				$default = floatval($matches[1][0]);
			}
			break;
			
		case 'yahoo':
			$yql_base_url = "http://query.yahooapis.com/v1/public/yql";
			$yql_query = 'select * from yahoo.finance.xchange where pair in ("' . $from . $currency . '")';
			$url = $yql_base_url . "?q=" . urlencode($yql_query);
			$url .= "&format=json&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys";
			$request = wp_remote_get($url);
			
			$yql_json = json_decode($request['body'], true);
			$default = (float) $yql_json['query']['results']['rate']['Rate'];
			break;

		case 'appspot':
			$url = 'http://rate-exchange.appspot.com/currency?from=' . $from . '&to=' . $currency;
			$request = wp_remote_get($url);
			
			$res = json_decode($request['body']);
			if (isset($res->rate)){
				$default = floatval($res->rate);
			}
			break;

		default:
			break;
	}
	
	return $default;
}