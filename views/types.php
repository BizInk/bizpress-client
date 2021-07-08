<?php 
extract( $args );
// cxbc_pri($args);

if ( is_null( $response ) ) {
	echo "<p>". __( 'Something went wrong', 'bizink-client' ) ."</p>"; 
	return;
}

if ( $response->status == 0 ) {
	echo "<p>{$response->message}</p>"; 
	return;
}

$types 		= $response->types;
$post_type	= str_replace('-', '_', $response->post_type);
$page_id 	= cxbc_get_option( 'bizink-client_basic', "{$post_type}_page" );
$slug 		= get_permalink( $page_id );

$taxonomy  	= 'type';



echo "<div id='primary' class='cxbc-content-area content-area primary'>";
echo "<div class='ast-article-single'>";
echo "<div class='cxbc-types-list'>";
foreach ( $types as $type => $posts ) {

	echo "<div class='cxbc-single-type-post-content'>";
 	echo "<a class='cxbc-type-title' href='{$slug}{$taxonomy}/{$type}'>{$posts->name}</a>";
	echo "<div class='cxbc-single-type-post-list'>";
	foreach ( $posts->posts as $post ) {
		echo "<a href='{$slug}{$post->slug}'><div class='cxbc-single-post'>";
		echo "<img class='cxbc-item-thumbnail' src='{$post->thumbnail}'>";
		echo "<div class='cxbc-post-title'>{$post->title}</div>";
		echo "</div></a>";
	}
	echo "</div>";
	echo "</div>";
}
echo "</div>";
echo "</div>";
echo "</div>";