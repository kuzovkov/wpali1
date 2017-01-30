<?php

function wpr_kontentmachinerequest($keyword) {
	global $wpdb,$wpr_table_posts;
	
	libxml_use_internal_errors(true);
	$options = unserialize(get_option("wpr_options"));	

	$api = $options['wpr_km_api'];
	$camps = explode(";", $options['wpr_km_camp']);	
	$rand_key = array_rand($camps, 1);
	$camp = $camps[$rand_key];

	$tpl = $options['wpr_km_temp'];	

	$keyword = str_replace( '"',"",$keyword );	
	$keyword = urlencode($keyword);
	
	if(substr( $keyword, 0, 4 ) === "CID:") {
		$camp = str_replace( 'CID:',"",$keyword );	
	}
	
	$request = "http://api.kontentmachine.com/";
	
	$data = "apikey=" . $api . "&";
	$data .= "method=getcontent&";
	$data .= "campaignid=" . $camp . "&";
	$data .= "template=wprobot";
	
	if ( function_exists('curl_init') ) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (compatible; Konqueror/4.0; Microsoft Windows) KHTML/4.0.80 (like Gecko)");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_URL, $request);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$response = curl_exec($ch);
		if (!$response) {
			$return["error"]["module"] = "Kontent Machine";
			$return["error"]["reason"] = "cURL Error";
			$return["error"]["message"] = __("cURL Error Number ","wprobot").curl_errno($ch).": ".curl_error($ch);	
			return $return;
		}		
		curl_close($ch);
	} else { 				
		$response = @file_get_contents($request);
		if (!$response) {
			$return["error"]["module"] = "Kontent Machine";
			$return["error"]["reason"] = "cURL Error";
			$return["error"]["message"] = __("cURL is not installed on this server!","wprobot");	
			return $return;		
		}
	}
	
	$pxml = json_decode($response);	

    //print_r($pxml);
	if ($pxml === False) {
		$return["error"]["module"] = "Kontent Machine";
		$return["error"]["reason"] = "JSON Error";
		$return["error"]["message"] = "JSON Response could not be loaded.";	
		return $return;				
	} else {
		return $pxml;
	}
}

function wpr_kontentmachinepost($keyword,$num,$start) {
	
	if(empty($keyword)) {
		$return["error"]["module"] = "Big Content Search";
		$return["error"]["reason"] = "No keyword";
		$return["error"]["message"] = __("No keyword specified.","wprobot");
		return $return;	
	}	
	
	$options = unserialize(get_option("wpr_options"));	
	$template = "{article}";

	$x = 0;
	$bcontent = array();

		$pxml = wpr_kontentmachinerequest($keyword);

		if($pxml->status == "error") {
			$bcontent["error"]["module"] = "Kontent Machine";
			$bcontent["error"]["reason"] = "Request fail";
			$bcontent["error"]["message"] = $pxml->message;	
			return $bcontent;		
		} elseif ($pxml === False) {
			$bcontent["error"]["module"] = "Kontent Machine";
			$bcontent["error"]["reason"] = "Request fail";
			$bcontent["error"]["message"] = __("API request could not be sent.","wprobot");	
			return $bcontent;	
		} elseif (is_array($pxml) && !empty($pxml["error"]["message"])) {
			return $pxml;				
		} elseif ($pxml->status == "success") {
		
			foreach($pxml->result as $article) {
			
				$article2 = explode("[/WP-TITLE]", $article);
				
				$title = str_replace("[WP-TITLE]", "", $article2[0]);
				$article2 = $article2[1];

				$content = $template;
				$content = wpr_random_tags($content);
				$content = str_replace("{article}", $article2, $content);		
					if(function_exists("wpr_translate_partial")) {
						$content = wpr_translate_partial($content);
					}
					if(function_exists("wpr_rewrite_partial")) {
						$content = wpr_rewrite_partial($content,$options);
					}			
			
				$bcontent[$x]["unique"] = rand(0,9999999);
				$bcontent[$x]["title"] = $title;
				$bcontent[$x]["content"] = $content;
				$x++;				
			}
		}

	if(empty($bcontent)) {
		$bcontent["error"]["module"] = "Kontent Machine";
		$bcontent["error"]["reason"] = "No content";
		$bcontent["error"]["message"] = __("No (more) Kontent Machine items found.","wprobot");	
		return $bcontent;		
	} else {
		return $bcontent;	
	}		
}

function wpr_kontentmachine_options_default() {
	$options = array(
		"wpr_km_api" => "",	
		"wpr_km_camp" => "",
		"wpr_km_temp" => "wprobot",
	);
	return $options;
}

function wpr_kontentmachine_options($options) {
	?>
	<h3 style="text-transform:uppercase;border-bottom: 1px solid #ccc;"><?php _e("Kontent Machine Options","wprobot") ?></h3>	
	
	<p><i><?php _e('Important: <a href="https://linkonym.appspot.com/?http://wprobot.net/go/bigcontentsearch" target="_blank">Kontent Machine Account required</a>',"wprobot") ?></i></p>	
	
		<table class="addt" width="100%" cellspacing="2" cellpadding="5" class="editform"> 
			<tr <?php if($options['wpr_km_api'] == "") {echo 'style="background:#F8E0E0;"';} ?> valign="top"> 
				<td width="40%" scope="row"><?php _e("Kontent Machine API Key:","wprobot") ?></td> 
				<td><input size="40" name="wpr_km_api" type="text" id="wpr_km_api" value="<?php echo $options['wpr_km_api'] ;?>"/>
				<!--Tooltip--><a target="_blank" class="tooltip" href="http://jvz9.com/c/55577/18783">?<span><?php _e('This setting is required for the Big Content Search module to work!<br/><br/><b>Click to go to the Big Content Search sign up page!</b>',"wprobot") ?></span></a>
			</td> 
			</tr>	
			<tr <?php if($options['wpr_km_camp'] == "") {echo 'style="background:#F8E0E0;"';} ?> valign="top"> 
				<td width="40%" scope="row"><?php _e("Campaign ID:","wprobot") ?></td> 
				<td><input size="40" name="wpr_km_camp" type="text" id="wpr_km_camp" value="<?php echo $options['wpr_km_camp'] ;?>"/> <br/><span style="font-size: 85%;">You can enter multiple campaigns separated by ";" to select one randomly for each post. Alternatively you can use keywords as campaign ID by starting them with "CID:", e.g. CID:12345.</span>
			</td> 
			</tr>				
			<!--<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Template ID:","wprobot") ?></td> 
				<td><input size="40" name="wpr_km_temp" type="text" id="wpr_km_temp" value="<?php echo $options['wpr_km_temp'] ;?>"/> <br/><span style="font-size: 85%;">Contact Kontent Machine support to get custom templates set up.</span>
			</td> 
			</tr>	-->
		</table>	
	<?php
}
?>