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
		echo apply_filters( 'bizink-client-luca-header', $type);
	}
	echo '<main class="main"><div class="section">';
	echo '<div class="container">';
}

echo '<main id="main" role="main">';
echo '<div class="container">';


echo "
<div id='primary' class='cxbc-content-area content-area primary'>";
if(!function_exists('luca')) {
	echo "<header class='entry-header'><h1 class='entry-title'>{$type}</h1></header>";
}
echo "
	<div class='ast-article-single'>
		<div class='cxbc-single-item cxbc-single-item-{$ID}'>
			<img class='cxbc-item-thumbnail' src='{$thumbnail}'>
			<h2 class='cxbc-item-title'>{$post_title}</h2>
			{$content}
		</div>
	</div>
</div>";


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