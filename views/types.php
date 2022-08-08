<?php 
extract( $args );
// cxbc_pri($args);

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

$types 		= $response->types;
$post_type	= str_replace('-', '_', $response->post_type);
$page_id 	= cxbc_get_option( 'bizink-client_basic', "{$post_type}_page" );
$slug 		= get_permalink( $page_id );

$taxonomy  	= 'type';

if(!function_exists('luca')) {
	get_header();
}
else{
	get_template_part('templates/head');
	do_action('luca/theme/before');
	do_action('get_header');
	echo '<div class="pageWrap">';
	do_action('luca/theme/content/before');
	if ( array_key_exists( 'bizink-client-luca-header' , $GLOBALS['wp_filter']) ) {
		echo apply_filters( 'bizink-client-luca-header', $post_type );
	}
	echo '<main class="main"><div class="section">';
	echo '<div class="container">';
}

echo '<main id="main" role="main">';
echo '<div class="container">';


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


echo '</div></div>';

if(!function_exists('luca')) {
	get_footer();
}
else{
	echo '</div></div></div>';
    do_action('luca/theme/content/after');
    echo '</div>';
	do_action('get_footer');
	wp_footer();
	do_action('luca/theme/after');
}