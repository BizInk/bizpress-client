<?php 

extract( $args );

if ( empty($responce) && empty($args['response']) ) {
	echo "<p>". __( 'Something went wrong. The data for this page could not be found.', 'bizink-client' ) ."</p>";
	if(defined('WP_DEBUG') && WP_DEBUG == true){
		_e('Got a Null for $responce in views/content.php', 'bizink-client' );
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
/*
$anyliticsData = '<div style="display:none;" class="bizpress-data" 
id="bizpress-'.$data->post->id.'" 
data-id="'.$data->post->id.'" 
data-title="'.$data->post->post_title.'" 
data-slug="'.$data->post->post_name.'" 
data-post-type="'.$data->post->post_type.'"
data-topics="'. (empty($data->post->topics) == false ? implode(',',$data->post->topics) : "false") .'"
data-types="'. (empty($data->post->types) == false ? implode(',',$data->post->types) : "false") . '" ></div>';
*/
echo '<main id="main" role="main" class="bizpress-content-layout">';
echo '<div class="container">';
echo "<div id='primary' class='cxbc-content-area content-area primary'>";
if(!function_exists('luca')) {
	echo "<header class='entry-header'><h1 class='entry-title'>{$type}</h1></header>";
}
//<img class='cxbc-item-thumbnail' src='{$thumbnail}'>
echo "
	<div class='ast-article-single'>
		<div class='cxbc-single-item cxbc-single-item-{$ID}'>
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