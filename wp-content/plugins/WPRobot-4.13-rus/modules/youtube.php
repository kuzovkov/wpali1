<?php

function wpr_youtube_getfulldesc($vid, $key) {

	$request = "https://www.googleapis.com/youtube/v3/videos?part=snippet&id=".$vid."&key=".$key;

	if ( function_exists('curl_init') ) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (compatible; Konqueror/4.0; Microsoft Windows) KHTML/4.0.80 (like Gecko)");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, $request);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		$response = curl_exec($ch);
		if (!$response) {
			return false;
		}		
		curl_close($ch);
	} else { 				
		$response = @file_get_contents($request);
		if (!$response) {
			return false;
		}
	}
    
	$pxml = json_decode($response);
	//echo "<pre>";print_r($pxml);echo "</pre>";
	
	if ($pxml === False) {
		return false;
	} else {
		if(isset($pxml->items)) {
			foreach ($pxml->items as $tttt) {
				if (isset($tttt->snippet->description)) {
					return $tttt->snippet->description;
				}	
			}
		}	

		return false;
	}
}

function wpr_youtuberequest($keyword,$num,$start) {	
	libxml_use_internal_errors(true);
	$start++;
	$options = unserialize(get_option("wpr_options"));	
	$sort = $options['wpr_yt_sort'];
	$safesearch = $options['wpr_yt_safe'];
	$lang = $options['wpr_yt_lang'];
	$author = $options['wpr_yt_author'];
	$key = $options['wpr_yt_api'];
    if($lang == "zh-cn") {$lang = "zh-Hans";}
    if($lang == "zh-tw") {$lang = "zh-Hant";}	
	//$keyword = '"'.$keyword.'"'; 
	$keyword = urlencode($keyword);
    //$request = "http://gdata.youtube.com/feeds/api/videos?q=$keyword&orderby=$sort&start-index=$start&max-results=$num&format=5&safeSearch=$safesearch&v=2";
    $request = "https://www.googleapis.com/youtube/v3/search?key=$key&part=snippet&q=$keyword&order=$sort&maxResults=50&safeSearch=$safesearch";
	if($lang != "") {
	$request .= "&lr=$lang";
	}	
	if(!empty($author)) {
	$request .= "&channelId=$author";
	}	
	
	if ( function_exists('curl_init') ) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (compatible; Konqueror/4.0; Microsoft Windows) KHTML/4.0.80 (like Gecko)");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, $request);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		$response = curl_exec($ch);
		if (!$response) {
			$return["error"]["module"] = "Youtube";
			$return["error"]["reason"] = "cURL Error";
			$return["error"]["message"] = __("cURL Error Number ","wprobot").curl_errno($ch).": ".curl_error($ch);	
			return $return;
		}		
		curl_close($ch);
	} else { 				
		$response = @file_get_contents($request);
		if (!$response) {
			$return["error"]["module"] = "Youtube";
			$return["error"]["reason"] = "cURL Error";
			$return["error"]["message"] = __("cURL is not installed on this server!","wprobot");	
			return $return;		
		}
	}
	
	$pxml = json_decode($response);
    
	//$pxml = simplexml_load_string($response);
	
	//echo "<pre>";print_r($pxml);echo "</pre>";
	
	if ($pxml === False) {
		$emessage = __("Failed loading XML, errors returned: ","wprobot");
		foreach(libxml_get_errors() as $error) {
			$emessage .= $error->message . ", ";
		}	
		libxml_clear_errors();
		$return["error"]["module"] = "Youtube";
		$return["error"]["reason"] = "XML Error";
		$return["error"]["message"] = $emessage;	
		return $return;			
	} else {
		return $pxml;
	}
}

function wpr_yt_getcomments($vid, $key) {

    $request = "https://www.googleapis.com/youtube/v3/commentThreads?part=snippet&videoId=$vid&key=".$key;

	if ( function_exists('curl_init') ) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (compatible; Konqueror/4.0; Microsoft Windows) KHTML/4.0.80 (like Gecko)");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, $request);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		$response = curl_exec($ch);
		curl_close($ch);
	} else { 				
		$response = @file_get_contents($request);
	}

    if ($response === False) {
    } else {
        $commentsFeed = json_decode($response);
    }  
	
	//echo "<pre>";print_r($commentsFeed);echo "</pre>";

	$i = 0;
	$comments = array();
	if(isset($commentsFeed->items)) {
		foreach ($commentsFeed->items as $comment) {
			$comments[$i]["author"] = $comment->snippet->topLevelComment->snippet->authorDisplayName;
			$comments[$i]["content"] = $comment->snippet->topLevelComment->snippet->textDisplay;	
			$i++;	
		}
	}
	
	return $comments;
}

function wpr_youtubepost($keyword,$num,$start,$optional="",$getcomments) {
	global $wpdb,$wpr_table_templates;
	
	if($keyword == "") {
		$return["error"]["module"] = "Youtube";
		$return["error"]["reason"] = "No keyword";
		$return["error"]["message"] = __("No keyword specified.","wprobot");
		return $return;	
	}		
	
	$template = $wpdb->get_var("SELECT content FROM " . $wpr_table_templates . " WHERE type = 'youtube'");
	if($template == false || empty($template)) {
		$return["error"]["module"] = "Youtube";
		$return["error"]["reason"] = "No template";
		$return["error"]["message"] = __("Module Template does not exist or could not be loaded.","wprobot");
		return $return;	
	}			
	$options = unserialize(get_option("wpr_options"));	
	$key = $options['wpr_yt_api'];
	$pxml = wpr_youtuberequest($keyword,$num,$start);

	if(is_array($pxml) && !empty($pxml["error"])) {return $pxml;}
	
	if(!empty($pxml->error->errors)) {
		$errs = "";
		foreach($pxml->error->errors as $err) {
			$errs .= "Error: " . $err->reason . " - " . $err->message . " (" . $err->domain . ")<br/>";
		}
		
		$return["error"]["module"] = "Youtube";
		$return["error"]["reason"] = "No template";
		$return["error"]["message"] = $errs;
		return $return;			
	}
	
	$videos = array();
	$x = 0;
	$scount = 0;
	
	if($start > 50) {
		$videos["error"]["module"] = "Youtube";
		$videos["error"]["reason"] = "API fail";
		$videos["error"]["message"] = __("Error: The Youtube module can only return the first 50 videos per keyword. Please choose a different keyword or reset your post counter.","wprobot");	
		return $videos;		
	}
	
	if ($pxml === False) {
		$videos["error"]["module"] = "Youtube";
		$videos["error"]["reason"] = "API fail";
		$videos["error"]["message"] = __("Youtube API request did not work.","wprobot");	
		return $videos;	
	} else {
		if (isset($pxml->items)) {
			foreach($pxml->items as $entry) {
			
				$scount++;
				if($start > $scount) {continue;}
				
				if($entry->id->kind == "youtube#channel") {
					continue;
				}				
				
				$videoid = $entry->id->videoId;	
				
				$description = $entry->snippet->description;	
				$title = $entry->snippet->title;
				
				$thumbnailurl = $entry->snippet->thumbnails->default->url;	
				$thumbnail = '<img alt="'.$title.'" src="'.$thumbnailurl.'" />'; 
				
				$thumbnailurl_med = $entry->snippet->thumbnails->medium->url;	
				$thumbnail_med = '<img alt="'.$title.'" src="'.$thumbnailurl_med.'" />'; 				
	
				$thumbnailurl_lrg = $entry->snippet->thumbnails->high->url;	
				$thumbnail_lrg = '<img alt="'.$title.'" src="'.$thumbnailurl_lrg.'" />'; 			

				$fulldesc = wpr_youtube_getfulldesc($videoid, $key);
				if(!empty($fulldesc)) {$description = $fulldesc;}

				/*
				$media = $entry->children('http://search.yahoo.com/mrss/');		
				$title = $media->group->title;
				$description = $media->group->description;	

				$attrs = $media->group->thumbnail[1]->attributes();
				$thumbnail = '<img alt="'.$title.'" src="'.$attrs['url'].'" />'; 
				$thumbnailurl = $attrs['url'];		
			
				$attrs = $media->group->thumbnail[2]->attributes();
				$thumbnail_med = '<img alt="'.$title.'" src="'.$attrs['url'].'" />'; 
				$thumbnailurl_med = $attrs['url'];
	
				$attrs = $media->group->thumbnail[3]->attributes();
				$thumbnail_lrg = '<img alt="'.$title.'" src="'.$attrs['url'].'" />'; 
				$thumbnailurl_lrg = $attrs['url'];	
				
				$yt = $media->children('http://gdata.youtube.com/schemas/2007');
				$videoid = $yt->videoid;		
		
				$gd = $entry->children('http://schemas.google.com/g/2005'); 
				if ($gd->rating) {
					$attrs = $gd->rating->attributes();
					$rating = round($attrs['average'], 2); 
				} else {
					$rating = 0; 
				} 
				
				$attrs = $media->group->player->attributes();
				$playerUrl = $attrs['url'];

				$gd = $entry->children('http://schemas.google.com/g/2005');
				if ($gd->comments->feedLink) { 
					$attrs = $gd->comments->feedLink->attributes();
					$commentsUrl = $attrs['href']; 
					$commentsCount = $attrs['countHint']; 
				}
				*/
				
				if(empty($options['wpr_yt_width'])) {$options['wpr_yt_width'] = "425";}
				if(empty($options['wpr_yt_height'])) {$options['wpr_yt_height'] = "355";}			
				
				 // 425 // 355
				$video ='
				<object width="'.$options['wpr_yt_width'].'" height="'.$options['wpr_yt_height'].'">
				<param name="movie" value="http://www.youtube.com/v/'.$videoid.'?fs=1"></param>
				<param name="allowFullScreen" value="true"></param>
				<embed src="http://www.youtube.com/v/'.$videoid.'?fs=1&rel=0" type="application/x-shockwave-flash" width="'.$options['wpr_yt_width'].'" height="'.$options['wpr_yt_height'].'" allowfullscreen="true" allowNetworking="internal"></embed>
				</object>';

				$video ='<object type="application/x-shockwave-flash" style="width:'.$options['wpr_yt_width'].'px;height:'.$options['wpr_yt_height'].'px;" data="http://www.youtube.com/v/'.$videoid.'">
				<param name="movie" value="http://www.youtube.com/v/'.$videoid.'" />
				</object>';
				
				$video ='<object type="application/x-shockwave-flash" style="width:'.$options['wpr_yt_width'].'px;height:'.$options['wpr_yt_height'].'px;" data="//www.youtube.com/v/'.$videoid.'?color2=FBE9EC&amp;version=3&amp;modestbranding=1">
					<param name="movie" value="//www.youtube.com/v/'.$videoid.'?color2=FBE9EC&amp;version=3&amp;modestbranding=1" />
					<param name="allowFullScreen" value="true" />
					<param name="allowscriptaccess" value="always" />
					</object>';		

				if ($options['wpr_yt_striplinks_desc']=='yes') {$description = wpr_strip_selected_tags($description, array('a','iframe','script'));}
				
				$vid = $template;	
				$vid = wpr_random_tags($vid);
				
				// Comments
				$commentspost = "";
				preg_match('#\{comments(.*)\}#iU', $vid, $rmatches);
				if ($rmatches[0] != false || $getcomments == 1 || $options['wpr_show_comments_settings'] != "Yes") {	
					$comments = wpr_yt_getcomments($videoid, $key);				
				}
				if ($rmatches[0] != false && !empty($comments)) {
					$cnum = substr($rmatches[1], 1);
					for ($i = 0; $i < $commentsCount; $i++) {
						if($i == $cnum) {break;} else {	
							$commentspost .= "<p><b>Comment by ".$comments[$i]["author"]."</b><br/>".$comments[$i]["content"]."</p>";
						}
					}
					$vid = str_replace($rmatches[0], $commentspost, $vid);				
				}	
		
				$maxres = "http://i.ytimg.com/vi/".$videoid."/maxresdefault.jpg";	
				$thumbnail_large = '<img alt="'.$title.'" src="'.$maxres.'" />'; 				

				$vid = str_replace("{thumbnail_medium}", $thumbnail_med, $vid);				
				$vid = str_replace("{thumbnail_large}", $thumbnail_lrg, $vid);
				$vid = str_replace("{thumbnail_max}", $thumbnail_large, $vid);
				$vid = str_replace("{description}", $description, $vid);
				$vid = str_replace("{thumbnail}", $thumbnail, $vid);
				//$vid = str_replace("{viewcount}", $viewCount, $vid);
				$vid = str_replace("{rating}", $rating, $vid);	
				$noqkeyword = str_replace('"', '', $keyword);
				$vid = str_replace("{keyword}", $noqkeyword, $vid);
				$vid = str_replace("{Keyword}", ucwords($noqkeyword), $vid);					
				$vid = str_replace("{video}", $video, $vid);	
				$vid = str_replace("{title}", $title, $vid);
				$vid = str_replace("{url}", "http://www.youtube.com/watch?v=".$videoid, $vid);		
					if(function_exists("wpr_translate_partial")) {
						$vid = wpr_translate_partial($vid);
					}
					if(function_exists("wpr_rewrite_partial")) {
						$vid = wpr_rewrite_partial($vid,$options);
					}					
					$customfield = array();
					$customfield["youtubetitle"] = $title;
					$customfield["video"] = $videoid;
					$customfield["youtubethumbnail"] = $thumbnailurl;	
					$customfield["youtubethumbnail_medium"] = $thumbnailurl_med;	
					$customfield["youtubethumbnail_large"] = $thumbnailurl_lrg;
					$customfield["youtubethumbnail_max"] = $maxres;		
					$customfield["youtuberating"] = $rating;			
					
				$videos[$x]["unique"] = $videoid;
				$videos[$x]["title"] = $title;
				$videos[$x]["content"] = $vid;	
				$videos[$x]["comments"] = $comments;	
				$videos[$x]["customfield"] = $customfield;

				$x++;
				
				if($num == count($videos)) {break;}
			}			
			if(empty($videos)) {
				$videos["error"]["module"] = "Youtube";
				$videos["error"]["reason"] = "No content";
				$videos["error"]["message"] = __("No (more) Youtube videos found.","wprobot");	
				return $videos;		
			} else {
				return $videos;	
			}
		} else {
			$videos["error"]["module"] = "Youtube";
			$videos["error"]["reason"] = "No content";
			$videos["error"]["message"] = __("No (more) Youtube videos found.","wprobot");	
			return $videos;		
		}
	}	
}
	
function wpr_youtube_options_default() {
	$options = array(
		"wpr_yt_api" => "",
		"wpr_yt_lang" => "",
		"wpr_yt_width" => "425",
		"wpr_yt_height" => "355",
		"wpr_yt_safe" => "moderate",
		"wpr_yt_sort" => "relevance",
		"wpr_yt_striplinks_desc" => "no",
		"wpr_yt_striplinks_comm" => "yes",
		"wpr_yt_author" => ""	
	);
	return $options;
}

function wpr_youtube_options($options) {
	?>
	<h3 style="text-transform:uppercase;border-bottom: 1px solid #ccc;"><?php _e("Youtube Options","wprobot") ?></h3>	
	
		<table class="addt" width="100%" cellspacing="2" cellpadding="5" class="editform"> 
			<tr <?php if($options['wpr_yt_api'] == "") {echo 'style="background:#F8E0E0;"';} ?> valign="top"> 
				<td width="40%" scope="row"><?php _e("Youtube API Key:","wprobot") ?></td> 
				<td><input size="40" name="wpr_yt_api" type="text" id="wpr_yt_api" value="<?php echo $options['wpr_yt_api'] ;?>"/>
				<!--Tooltip--><a class="tooltip" href="https://www.youtube.com/watch?v=Im69kzhpR3I">?<span><?php _e('This key is required for posting videos from Youtube. Sign up for a free API key for Youtube in your Google Account and enter it here. Click the link to see a tutorial on how to get the API key. Note: You need a so-called "Simple Key" only.',"wprobot") ?></span></a>
			</td> 
			</tr>	
			
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Language:","wprobot") ?></td> 
				<td>
				<select name="wpr_yt_lang" id="wpr_yt_lang">
							<option value="" <?php if($options['wpr_yt_lang']==""){_e('selected');}?>><?php _e("Any Language","wprobot") ?></option>
							<option value="ar" <?php if($options['wpr_yt_lang']=="ar"){_e('selected');}?>><?php _e("Arabic","wprobot") ?></option>
							<option value="bg" <?php if($options['wpr_yt_lang']=="bg"){_e('selected');}?>><?php _e("Bulgarian","wprobot") ?></option>
							<option value="ca" <?php if($options['wpr_yt_lang']=="ca"){_e('selected');}?>><?php _e("Catalan","wprobot") ?></option>
							<option value="zh-cn" <?php if($options['wpr_yt_lang']=="zh-cn"){_e('selected');}?>><?php _e("Chinese (Simplified)","wprobot") ?></option>
							<option value="zh-tw" <?php if($options['wpr_yt_lang']=="zh-tw"){_e('selected');}?>><?php _e("Chinese (Traditional)","wprobot") ?></option>
							<option value="hr" <?php if($options['wpr_yt_lang']=="hr"){_e('selected');}?>><?php _e("Croatian","wprobot") ?></option>
							<option value="cs" <?php if($options['wpr_yt_lang']=="cs"){_e('selected');}?>><?php _e("Czech","wprobot") ?></option>
							<option value="da" <?php if($options['wpr_yt_lang']=="da"){_e('selected');}?>><?php _e("Danish","wprobot") ?></option>
							<option value="nl" <?php if($options['wpr_yt_lang']=="nl"){_e('selected');}?>><?php _e("Dutch","wprobot") ?></option>
							<option value="en" <?php if($options['wpr_yt_lang']=="en"){_e('selected');}?>><?php _e("English","wprobot") ?></option>
							<option value="et" <?php if($options['wpr_yt_lang']=="et"){_e('selected');}?>><?php _e("Estonian","wprobot") ?></option>
							<option value="fi" <?php if($options['wpr_yt_lang']=="fi"){_e('selected');}?>><?php _e("Finnish","wprobot") ?></option>
							<option value="fr" <?php if($options['wpr_yt_lang']=="fr"){_e('selected');}?>><?php _e("French","wprobot") ?></option>
							<option value="de" <?php if($options['wpr_yt_lang']=="de"){_e('selected');}?>><?php _e("German","wprobot") ?></option>
							<option value="er" <?php if($options['wpr_yt_lang']=="er"){_e('selected');}?>><?php _e("Greek","wprobot") ?></option>
							<option value="iw" <?php if($options['wpr_yt_lang']=="iw"){_e('selected');}?>><?php _e("Hebrew","wprobot") ?></option>
							<option value="hu" <?php if($options['wpr_yt_lang']=="hu"){_e('selected');}?>><?php _e("Hungarian","wprobot") ?></option>
							<option value="is" <?php if($options['wpr_yt_lang']=="is"){_e('selected');}?>><?php _e("Icelandic","wprobot") ?></option>
							<option value="it" <?php if($options['wpr_yt_lang']=="it"){_e('selected');}?>><?php _e("Italian","wprobot") ?></option>
							<option value="ja" <?php if($options['wpr_yt_lang']=="ja"){_e('selected');}?>><?php _e("Japanese","wprobot") ?></option>
							<option value="ko" <?php if($options['wpr_yt_lang']=="ko"){_e('selected');}?>><?php _e("Korean","wprobot") ?></option>
							<option value="lv" <?php if($options['wpr_yt_lang']=="lv"){_e('selected');}?>><?php _e("Latvian","wprobot") ?></option>
							<option value="lt" <?php if($options['wpr_yt_lang']=="lt"){_e('selected');}?>><?php _e("Lithuanian","wprobot") ?></option>
							<option value="no" <?php if($options['wpr_yt_lang']=="no"){_e('selected');}?>><?php _e("Norwegian","wprobot") ?></option>
							<option value="pl" <?php if($options['wpr_yt_lang']=="pl"){_e('selected');}?>><?php _e("Polish","wprobot") ?></option>
							<option value="pt" <?php if($options['wpr_yt_lang']=="pt"){_e('selected');}?>><?php _e("Portuguese","wprobot") ?></option>
							<option value="ro" <?php if($options['wpr_yt_lang']=="ro"){_e('selected');}?>><?php _e("Romanian","wprobot") ?></option>
							<option value="ru" <?php if($options['wpr_yt_lang']=="ru"){_e('selected');}?>><?php _e("Russian","wprobot") ?></option>
							<option value="sr" <?php if($options['wpr_yt_lang']=="sr"){_e('selected');}?>><?php _e("Serbian","wprobot") ?></option>
							<option value="sk" <?php if($options['wpr_yt_lang']=="sk"){_e('selected');}?>><?php _e("Slovak","wprobot") ?></option>
							<option value="sl" <?php if($options['wpr_yt_lang']=="sl"){_e('selected');}?>><?php _e("Slovenian","wprobot") ?></option>
							<option value="es" <?php if($options['wpr_yt_lang']=="es"){_e('selected');}?>><?php _e("Spanish","wprobot") ?></option>
							<option value="sv" <?php if($options['wpr_yt_lang']=="sv"){_e('selected');}?>><?php _e("Swedish","wprobot") ?></option>
							<option value="tr" <?php if($options['wpr_yt_lang']=="tr"){_e('selected');}?>><?php _e("Turkish","wprobot") ?></option>		
							<option value="hi" <?php if($options['wpr_yt_lang']=="hi"){_e('selected');}?>><?php _e("Hindi","wprobot") ?></option>		
				</select>
			</td> 
			</tr>	
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Safe Search:","wprobot") ?></td> 
				<td>
				<select name="wpr_yt_safe" id="wpr_yt_safe">
							<option value="none" <?php if($options['wpr_yt_safe']=="none"){_e('selected');}?>><?php _e("None","wprobot") ?></option>
							<option value="moderate" <?php if($options['wpr_yt_safe']=="moderate"){_e('selected');}?>><?php _e("Moderate","wprobot") ?></option>
							<option value="strict" <?php if($options['wpr_yt_safe']=="strict"){_e('selected');}?>><?php _e("Strict","wprobot") ?></option>
				</select>
			</td> 
			</tr>				
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Sort Videos by:","wprobot") ?></td> 
				<td>
				<select name="wpr_yt_sort" id="wpr_yt_sort">
							<option value="relevance" <?php if($options['wpr_yt_sort']=="relevance"){_e('selected');}?>><?php _e("Relevance","wprobot") ?></option>
							<option value="viewcount" <?php if($options['wpr_yt_sort']=="viewcount"){_e('selected');}?>><?php _e("View Count","wprobot") ?></option>
							<option value="rating" <?php if($options['wpr_yt_sort']=="rating"){_e('selected');}?>><?php _e("Rating","wprobot") ?></option>
							<option value="date" <?php if($options['wpr_yt_sort']=="date"){_e('selected');}?>><?php _e("Date Published","wprobot") ?></option>
				</select>
			</td> 
			</tr>				
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Video Size:","wprobot") ?></td> 
				<td>
				<input id="wpr_yt_width" size="7" class="small-text" type="text" value="<?php echo $options['wpr_yt_width']; ?>" name="wpr_yt_width"/> x <input id="wpr_yt_height" size="7" class="small-text" type="text" value="<?php echo $options['wpr_yt_height']; ?>" name="wpr_yt_height"/>
				</td> 
			</tr>	
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Channel ID:","wprobot") ?></td> 
				<td><input size="40" name="wpr_yt_author" type="text" id="wpr_yt_author" value="<?php echo $options['wpr_yt_author'] ;?>"/>
				<!--Tooltip--><a class="tooltip" href="#">?<span><?php _e('This option restricts the search to videos uploaded to a particular YouTube channel. You can enter any YouTube channel exactly as displayed on the site.',"wprobot") ?></span></a>
			</td> 
			</tr>			
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Strip All Links from...","wprobot") ?></td> 
				<td><input name="wpr_yt_striplinks_desc" type="checkbox" id="wpr_yt_striplinks_desc" value="yes" <?php if ($options['wpr_yt_striplinks_desc']=='yes') {echo "checked";} ?>/> <?php _e("Video Description","wprobot") ?><br/>
				<input name="wpr_yt_striplinks_comm" type="checkbox" id="wpr_yt_striplinks_comm" value="yes" <?php if ($options['wpr_yt_striplinks_comm']=='yes') {echo "checked";} ?>/> <?php _e("Comments","wprobot") ?></td> 
			</tr>				
		</table>	
	<?php
}

?>