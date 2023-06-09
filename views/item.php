<?php 

extract( $args );

if ( empty($responce) && empty($args['response']) ) {
	echo "<p>". __( 'Something went wrong. The data for this page could not be found.', 'bizink-client' ) ."</p>";
	if(defined('WP_DEBUG') && WP_DEBUG == true){
		_e('Got a Null for $responce in views/topics.php', 'bizink-client' );
	}
	return;
}


if ( $response->status == 0 ) {
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
	echo "<img alt='{$title}' class='cxbc-item-thumbnail' src='{$thumbnail}'>";
	echo "<h2 class='cxbc-item-title'>{$title}</h2>";
	echo apply_filters( 'the_content', $content );
echo "</div>";