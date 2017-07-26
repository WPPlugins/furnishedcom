<?php
add_action('admin_menu', 'furnished_com_menu');

function furnished_com_menu(){
	add_menu_page( __( 'Furnished.com', 'Furnished.com' ), __( '<span style="font-size:12px;">'.__('Furnished.com').'</span>', 'Furnished.com' ), 8, 'furnished_com_instructions', 'furnished_com_instructions');
}

function furnished_com_instructions(){
	$cities = furnished_com_get_cities(); //Prime the cities to speed things up
?>
<div class="wrap">
	<h1><?php echo esc_html( __("Furnished.com Integration Instructions") ); ?></h1>
	<p><?php _e('Place the short code <strong>[furnished_com number_of_results="20"]</strong> on any page or post.') ?></p>
	<ul style="margin-left: 20px;">
		<li><?php _e('<strong>number_of_results</strong> is an optional value that allows you to control how many results are shown per page. The default is 20.') ?></li>
		<li><?php _e('<strong>city</strong> is an optional value that allows you to specify the city to show furnished rentals.') ?></li>
		<li><?php _e('<strong>state</strong> is an optional value that allows you to specify the state to show furnished rentals.') ?></li>
	</ul>
	<?php if(count($cities['cities']) > 0): ?>
	<p><?php _e('The following cities are supported:') ?></p>
	<ul style="margin-left: 20px;">
	<?php foreach($cities['cities'] as $city): ?>
	<li><?php _e($city['city'].", ".$city['state']); ?></li>
	<?php endforeach; ?>
	</ul>
	<?php endif; ?>
</div>
<?php
}
?>