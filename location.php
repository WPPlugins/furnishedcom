<?php
require(dirname(__FILE__) . '/lib.php');
$searchable_text = $_POST['search_string'];
$cities = furnished_com_get_cities();
$results = "";

if(is_array($cities)):
	if(is_array($cities['cities']) && count($cities['cities']) > 0):
		foreach($cities['cities'] as $city):
			if(stripos($city['city'],$searchable_text) !== false):
				$results .= $city['city'].", ".$city['state']."|";
			endif;
		endforeach;
	endif;
endif;

echo $results;
exit();
?>