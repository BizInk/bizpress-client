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

$post = (array)$response->post;
$thumbnail = $response->thumbnail;


extract( $post );

echo "<div id='primary' class='cxbc-content-area content-area primary'>";
echo "<div class='ast-article-single'>";
echo "<div class='cxbc-single-item cxbc-single-item-{$ID}'>";
echo "<img class='cxbc-item-thumbnail' src='{$thumbnail}'>";
echo "<h2 class='cxbc-item-title'>{$post_title}</h2>";
$content = apply_filters( 'the_content', $post_content );
echo $content ;
echo "</div>";
echo "</div>";
echo "</div>";