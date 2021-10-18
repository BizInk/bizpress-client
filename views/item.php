<?php 

extract( $args );

if ( is_null( $response ) ) {
	echo "<p>". __( 'Something went wrong', 'bizink-client' ) ."</p>"; 
	return;
}

if ( $response->status == 0 ) {
	echo "<p>{$response->message}</p>"; 
	return;
}

extract( (array)$response->item );

echo "<div class='cxbc-single-item cxbc-single-item-{$ID}'>";
echo "<img class='cxbc-item-thumbnail' src='{$thumbnail}'>";
echo "<h2 class='cxbc-item-title'>{$title}</h2>";
$content = apply_filters( 'the_content', $content );
echo $content ;
echo "</div>";