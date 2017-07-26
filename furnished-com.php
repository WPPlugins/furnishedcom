<?php
/**
 Plugin Name: Furnished.com
 Plugin URI: http://furnished.com/furnished-rental-widgets
 Description: The Furnished.com WordPress plugin creates a powerful search widget on your website that allows users to browse through thousands of furnished rentals right from your website.

 Version: 1.0
 Author: Furnished.com
 Author URI: http://furnished.com
*/

require(dirname(__FILE__) . '/lib.php');

if(is_admin()):
	require(dirname(__FILE__) . '/admin.php');
endif;

function furnished_jquery() {
	wp_enqueue_script('jquery');            
}    
 
add_action('wp_enqueue_scripts', 'furnished_jquery'); // For use on the Front end (ie. Theme)

add_shortcode( 'furnished_com', 'furnished_com_initialize' );

function furnished_com_initialize($atts){

	$display_location = "United States Furnished Rentals";
	$query_vars = $_GET;
	if(array_key_exists('number_of_results',$atts))
		$query_vars['number_of_results'] = $atts['number_of_results'];
	if(array_key_exists('city',$atts)):
		if(array_key_exists('state',$atts)):
			$display_location = $atts['city'].", ".$atts['state']." Furnished Rentals";
			$query_vars['location'] = $atts['city'].", ".$atts['state'];
		else:
			$display_location = $atts['city']." Furnished Rentals";
			$query_vars['location'] = $atts['city'];
		endif;
	endif;
	
	if(isset($_GET['location']) && strlen($_GET['location']) > 0):
		$display_location = $_GET['location']." Furnished Rentals";
		$query_vars['location'] = $_GET['location'];
	endif;
	
	if(isset($_GET['order']) || strlen($_GET['order']) > 0):
		$query_vars['order'] = $_GET['order'];
	endif;
	
	furnished_com_get_cities(); //Prime the cities to speed things up
	echo "<div class='furnished_rentals'>";
	echo "<h2>".$display_location."</h2>";
	
	$query_string = http_build_query($query_vars);
	
	$response = furnished_com_get_feed($query_string);
	
echo <<<EOT
<style>
.furnished_rentals{

}
.furnished_rentals #furnished_rental_location_results{
	list-style: none;
	margin: 0px;
	padding: 0px;
	display: block;
}
.furnished_rentals #furnished_rental_location_results li{
	padding: 5px 0px;
}
.furnished_rentals .furnished_rental{
	border-bottom: 1px dashed #ccc;
	margin-bottom: 30px;
	padding-bottom: 15px;
	clear: both;
}
.furnished_rentals .furnished_rental img{
	float: left;
	margin-right: 20px;
	margin-bottom: 20px;
	border: 1px solid #999;
	padding: 5px;
}
.furnished_rentals .furnished_rental h2, .furnished_rentals .furnished_rental p{
	clear: none;
}
.furnished_rentals .furnished_rental h2{
	padding: 0px;
	margin: 10px 0px;
}
.furnished_rentals .furnished_rental p{
	padding: 0px;
	margin: 0px 0px 10px;
}
.furnished_rentals .furnished_powered_by{

}
.furnished_rentals .furnished_powered_by img{
	float: right;
}
</style>
EOT;
?>
<form action='' method='GET'>
	<table width='100%' cellpadding='0' cellspacing='0'>
		<tr>
			<td width='33%'>
				Keywords: <input type='text' name='keyword' value='<?php echo $_GET['keyword']; ?>'>
			</td>
			<td width='33%'>
				Location: <input type='text' id='furnished_rental_location' name='location' placeholder='Select a city...' value='<?php echo $query_vars['location']; ?>'>
				<ul id='furnished_rental_location_results'></ul>
			</td>
			<td width='33%'>
				Sort: <select name='order'>
					<option value='created_new-old'<?php if($_GET['order'] == "created_new-old") echo " selected='selected'"; ?>>Created | Newest to Oldest</option>
					<option value='created_old-new'<?php if($_GET['order'] == "created_old-new") echo " selected='selected'"; ?>>Created | Oldest to Newest</option>
					<option value='price_low-high'<?php if($_GET['order'] == "price_low-high") echo " selected='selected'"; ?>>Price | Low to High</option>
					<option value='price_high-low'<?php if($_GET['order'] == "price_high-low") echo " selected='selected'"; ?>>Price | High to Low</option>
					<option value='square_feet_low-high'<?php if($_GET['order'] == "square_feet_low-high") echo " selected='selected'"; ?>>Square Feet | Low to High</option>
					<option value='square_feet_high-low'<?php if($_GET['order'] == "square_feet_high-low") echo " selected='selected'"; ?>>Square Feet | High to Low</option>
				</select>
			</td>
		</tr>
		<tr>
			<td>
				<select name="min_bedrooms" class="input-block-level">
					<option value="">Min bedrooms...</option>
					<?php for($i=1;$i<=8;$i++): ?>
					<option value="<?php echo $i; ?>" <?php if($i==$_GET['min_bedrooms']) echo 'selected="selected"'; ?>><?php echo $i; ?></option>
					<?php endfor; ?>
				</select>
				<select name="max_bedrooms" class="input-block-level">
					<option value="">Max bedrooms...</option>
					<?php for($i=1;$i<=8;$i++): ?>
					<option value="<?php echo $i; ?>" <?php if($i==$_GET['max_bedrooms']) echo 'selected="selected"'; ?>><?php echo $i; ?></option>
					<?php endfor; ?>
				</select>
			</td>
			<td>
				<select name="min_bathrooms" class="input-block-level">
					<option value="">Min bathrooms...</option>
					<?php for($i=1;$i<=8;$i++): ?>
					<option value="<?php echo $i; ?>" <?php if($i==$_GET['min_bathrooms']) echo 'selected="selected"'; ?>><?php echo $i; ?></option>
					<?php endfor; ?>
				</select>
				<select name="max_bathrooms" class="input-block-level">
					<option value="">Max bathrooms...</option>
					<?php for($i=1;$i<=8;$i++): ?>
					<option value="<?php echo $i; ?>" <?php if($i==$_GET['max_bathrooms']) echo 'selected="selected"'; ?>><?php echo $i; ?></option>
					<?php endfor; ?>
				</select>
			</td>
			<td>
				<select name="square_feet" class="input-block-level">
					<option value="">Min square feet...</option>
					<?php for($i=1;$i<=20;$i++): ?>
					<option value="<?php echo ($i*500); ?>" <?php if(($i*500)==$_GET['square_feet']) echo 'selected="selected"'; ?>><?php echo number_format(($i*500)); ?></option>
					<?php endfor; ?>
				</select>
			</td>
		</tr>
		<tr>
			<td>
				<select name="min_price" class="input-block-level">
					<option value="">Rent min...</option>
					<?php for($i=1;$i<=8;$i++): ?>
					<option value="<?php echo ($i*500); ?>" <?php if(($i*500)==$_GET['min_price']) echo 'selected="selected"'; ?>><?php echo "$".number_format(($i*500)); ?></option>
					<?php endfor; ?>
				</select>
				<select name="max_price" class="input-block-level">
					<option value="">Rent max...</option>
					<?php for($i=1;$i<=20;$i++): ?>
					<option value="<?php echo ($i*500); ?>" <?php if(($i*500)==$_GET['max_price']) echo 'selected="selected"'; ?>><?php echo "$".number_format(($i*500)); ?></option>
					<?php endfor; ?>
				</select>
			</td>
			<td colspan="2">
				<input type='submit' value='Search'>
			</td>
		</tr>
	</table>
</form>
<?php
	if($response->success):
		if(count($response->rentals) > 0):
			for($i=0; $i<= count($response->rentals); $i++):
				if(strlen($response->rentals[$i]->title) > 0 && strlen($response->rentals[$i]->link) > 0):
					$square_feet = $response->rentals[$i]->square_feet;
					$price = $response->rentals[$i]->price;
					if(strlen($square_feet) > 0 && strlen($price) > 0):
						$display_details = "<strong>".number_format($square_feet)." square feet</strong> for <strong>$".number_format($price)." per month</strong>";
					elseif(strlen($square_feet) > 0):
						$display_details = "<strong>".number_format($square_feet)." square feet</strong>";
					elseif(strlen($price) > 0):
						$display_details = "<strong>$".number_format($price)." per month</strong>";
					endif;
					echo "<div class='furnished_rental'>";
					if(strlen($response->rentals[$i]->thumbnail) > 0):
						echo "<a href='".$response->rentals[$i]->link."?ref=".urlencode(get_bloginfo('url'))."' target='_blank'><img src='".$response->rentals[$i]->thumbnail."' border='0'></a>";
					endif;
					echo "<h2><a href='".$response->rentals[$i]->link."?ref=".urlencode(get_bloginfo('url'))."' target='_blank'>".$response->rentals[$i]->title."</a></h2>";
					if(strlen($display_details) > 0):
						echo "<p>".$display_details."</p>";
					endif;
					echo "<p>".$response->rentals[$i]->description."</p>";
					echo "<div style='clear: both;'></div>";
					echo "</div>";
				endif;
			endfor;
			if(array_key_exists('city',$atts) && array_key_exists('state',$atts)):
				$city = str_ireplace(" ","-",strtolower($atts['city']));
				$state = str_ireplace(" ","-",strtolower($atts['state']));
				echo "<div class='furnished_powered_by'>Browse hundreds of <a href='http://furnished.com/loc/".$state."/".$city."/' title='".$atts['city']." Furnished Apartments' target='_blank'>".$atts['city']." Furnished Apartments</a> at Furnished.com <a href='http://furnished.com' target='_blank'><img src='http://furnished.com/images/furnished-logo-small.gif'></a></div>";
			else:
				echo "<div class='furnished_powered_by'>Browse thousands of <a href='http://furnished.com' title='Furnished Apartments' target='_blank'>Furnished Apartments</a> at Furnished.com <a href='http://furnished.com' target='_blank'><img src='http://furnished.com/images/furnished-logo-small.gif'></a></div>";
			endif;
		else:
			_e('<h2>No furnished rentals could be found.</h2>');
		endif;
	else:
		_e('<h2>There was a problem fetching furnished rentals. Please contact support@furnished.com.</h2>');
	endif;
	
	echo "</div>";
	$location_script = plugins_url( 'location.php' , __FILE__ );
echo <<<EOT
<script type='text/javascript'>
var get_location = {
	search_string: "",
	get_cities: function(){
		var search_string = jQuery("#furnished_rental_location").val();
		if(search_string.length > 2){
			if(search_string != get_location.search_string){

				jQuery.post("$location_script", { search_string: search_string },
			  	function(data) {
			  		jQuery('#furnished_rental_location_results').html('');
			  		var items = data.split("|");
			  		for(i=0;i<items.length;i++){
			  			if(items[i].length > 0){
			  				jQuery('#furnished_rental_location_results').append('<li><a href="#" onclick="jQuery(\'#furnished_rental_location\').val(\''+items[i]+'\'); jQuery(\'#furnished_rental_location_results\').html(\'\'); return false;">'+items[i]+'</a></li>');
			  			}
			  		}
			  		get_location.search_string = search_string;
			  	}
			  );
		  }
	  }
	}
}
jQuery(document).ready(function () {
	jQuery('#furnished_rental_location').keyup(function() {
		delay(function(){
      get_location.get_cities();
    }, 300 );
	});
});
var delay = (function(){
  var timer = 0;
  return function(callback, ms){
    clearTimeout (timer);
    timer = setTimeout(callback, ms);
  };
})();
</script>
EOT;
}
?>