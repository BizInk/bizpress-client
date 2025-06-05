<?php

extract($args);

if (empty($responce) && empty($args['response'])) {
	echo "<p>" . __('Something went wrong. The data for this page could not be found.', 'bizink-client') . "</p>";
	if (defined('WP_DEBUG') && WP_DEBUG == true) {
		_e('Got a Null for $responce in views/resources.php', 'bizink-client');
	}
	return;
} else if (!empty($response->data) && ($response->data->status > 299 || $response->data->status < 200)) {
	echo "<p>" . __('Sorry there was an error. There was an error retrieving the data for this page.', 'bizink-client') . "</p>";
	return;
}

$types = $response->types;
$page_id 	= cxbc_get_option( 'bizink-client_basic', "resources_content_page" );
$slug 		= get_permalink( $page_id );

echo '<main id="main" role="main" class="bizpress-content-layout">';
echo '<div class="container">';
echo "<div id='primary' class='cxbc-content-area content-area primary'>";

if (!empty($types)) {
	echo "<div class='bizpress-resource-grid'>";
	foreach ($types as $type => $posts) {
		$postUrl = $slug . $type;
		echo "<a href='" . esc_url($postUrl) . "' class='bizpress-resource-type-link'>";
		echo "<div class='bizpress-resource-type'>";
		echo "<div class='bizpress-resource-type-icon'>";
		if(isset($posts->image_or_svg) && $posts->image_or_svg == 'svg' && !empty($posts->svg)) {
			echo $posts->svg;
		} 
		else if (isset($posts->icon) && !empty($posts->icon)) {
			echo "<img src='" . esc_url($posts->icon) . "' alt='" . esc_attr($posts->name) . "' />";
		}
		echo "</div><h2 class='bizpress-resource-type-title'>" . ucwords($posts->name) . "</h2></div></a>";
	}
	echo "</div>";
}

echo '</div></div>';
