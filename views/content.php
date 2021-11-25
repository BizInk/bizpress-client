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

$type 		= ucwords( str_replace( [ '-', '_' ], [ ' ' ], $post_type ) );
$content 	= apply_filters( 'the_content', $post_content );

echo "
<div id='primary' class='cxbc-content-area content-area primary'>
	<header class='entry-header'><h1 class='entry-title'>{$type}</h1></header>
	<div class='ast-article-single'>
		<div class='cxbc-single-item cxbc-single-item-{$ID}'>
			<img class='cxbc-item-thumbnail' src='{$thumbnail}'>
			<h2 class='cxbc-item-title'>{$post_title}</h2>
			{$content}
		</div>
	</div>
</div>";