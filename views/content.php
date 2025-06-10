<?php 
extract( $args );

if ( empty($response) && empty($args['response']) ) {
	echo "<p>". __( 'Something went wrong. The data for this page could not be found.', 'bizink-client' ) ."</p>";
	if(defined('WP_DEBUG') && WP_DEBUG == true){
		_e('Got a Null for $response in views/content.php', 'bizink-client' );
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

global $post;
$postArray = (array)$response->post;
$thumbnail = $response->thumbnail;

extract( $postArray );

$type = ucwords( str_replace( [ '-', '_' ], [ ' ' ], $post_type ) );
$content = apply_filters( 'the_content', $post_content );
$content = do_shortcode($content);

if(!function_exists('luca')) {
	get_header();
	echo '<main id="main" role="main" class="bizpress-content-layout">';
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
	echo '<main class="main"><div class="section bizpress-content-layout">';
	echo '<div class="container">';
}
?>
	<div class="container">
		<div id='primary' class='cxbc-content-area content-area primary'>
			<?php
			if(!function_exists('luca')) {
				echo "<header class='entry-header'><h1 class='entry-title'>{$type}</h1></header>";
			}
			?>

			<div class='ast-article-single'>
				<div class='cxbc-single-item cxbc-single-item-<?php echo $ID; ?>'>
					<h2 class='cxbc-item-title'><?php echo $post_title; ?></h2>
					<?php echo $content; ?>
				</div>
			</div>
		</div>
	</div>
<?php
if(!function_exists('luca')) {
	echo '</main>';
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