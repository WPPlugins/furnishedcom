<?php
define('FURNISHED_COM_PLUGIN_DIR', dirname(__FILE__));

function furnished_com_get_feed($query_string){
	$hash = md5($query_string);
	$feed_file = FURNISHED_COM_PLUGIN_DIR."/cache/".$hash;
	if(file_exists($feed_file)):
		$timestamp = filectime($feed_file);
		$expire_time = time() - (86400 * 2);
		if($timestamp > $expire_time):
			$fh = fopen($feed_file, 'r');
			$request_feed = fread($fh, filesize($feed_file));
			fclose($fh);
			$feed = json_decode($request_feed);
			if($feed):
				return $feed;
			endif;
		endif;
	endif;
	
	$request = furnished_com_remote_request("http://furnished.com/api/feed",$query_string);
	$response = json_decode($request);
	if($response):
		$fh = fopen($feed_file, 'w');
		fwrite($fh, $request);
		fclose($fh);
		return $response;
	endif;
	
	return false;
}

function furnished_com_get_cities(){
	$cities_file = FURNISHED_COM_PLUGIN_DIR."/cache/cities.txt";
	if(file_exists($cities_file)):
		$timestamp = filectime($cities_file);
		$expire_time = time() - (86400 * 5);
		if($timestamp > $expire_time):
			$fh = fopen($cities_file, 'r');
			$request_cities = fread($fh, filesize($cities_file));
			fclose($fh);
			$cities = json_decode($request_cities, true);
			if($cities):
				return $cities;
			endif;
		endif;
	endif;
	
	$request_cities = furnished_com_remote_request("http://furnished.com/api/cities","give_me_data=true");
	$cities = json_decode($request_cities, true);
	if($cities):
		$fh = fopen($cities_file, 'w');
		fwrite($fh, $request_cities);
		fclose($fh);
		
		return $cities;
	endif;
	
	return false;
}

/*
Perform CURL
*/
function furnished_com_remote_request($host,$path){
	$fp = curl_init($host);
	curl_setopt($fp, CURLOPT_POST, true);
	curl_setopt($fp, CURLOPT_POSTFIELDS, $path);
	curl_setopt($fp, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($fp, CURLOPT_CONNECTTIMEOUT, 5);
	$page = curl_exec($fp);
	curl_close($fp);
	
	return $page;
}
?>