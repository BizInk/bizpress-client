<?php 

extract( $args );

if ( is_null( $response ) || empty($responce) ) {
	echo "<p>". __( 'Something went wrong. The data for this page could not be found.', 'bizink-client' ) ."</p>";
	if(defined('WP_DEBUG') && WP_DEBUG == true){
		_e('Got a Null for $responce', 'bizink-client' );
	}
	return;
}

if ( empty($response->status) || $response->status == 0 ) {
	if(defined('WP_DEBUG') && WP_DEBUG == true){
		print_r($response);
	}
	if(empty($response->message)){
		echo "<p>".__('Sorry there was an error. There was an error retreveing the data for this page.','bizink-client')."</p>";
	}
	else{
		echo "<p>{$response->message}</p>";
	}
	return;
}
extract( (array)$response->item );

echo "<div class='cxbc-single-item cxbc-single-item-{$ID}'>";
echo "<img class='cxbc-item-thumbnail' src='{$thumbnail}'>";
echo "<h2 class='cxbc-item-title'>{$title}</h2>";
$content = apply_filters( 'the_content', $content );
echo $content ;
echo "</div>";