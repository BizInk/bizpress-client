<?php 

extract($args);

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

$posts 			= $response->posts;
$post_type 		= $response->post_type;

$default_title 	= __( 'Calculators', 'bizink-client' );
$default_desc 	= __( '', 'bizink-client' );

/** Return if [bizpress-content] or [bizink-content] is on page but this page has not be configured in settings */
if(empty($post_type)){
	echo '<p><b>';
	_e('Sorry this page has not been configured in the BizPress plugin.', 'bizink-client');
	echo '</b></p>';
	return;
}

$default_title 	= cxbc_get_option( 'bizink-client_content', 'calculator_title' );
$default_desc 	= cxbc_get_option( 'bizink-client_content', 'calculator_desc' );

//dropdown after single topics
if(isset($_GET)){
	$query_value = $_GET;	
}

echo "<div class='cxbc-topics-heading'>";
echo "<h2>{$default_title}</h2>";
echo "<p>{$default_desc}</p>";
echo "</div>";

//$next_icon 	= plugins_url( 'assets/img/next-icon.png', CXBPC );
echo "<div class='cxbc-posts-list'>";
echo "<div class='cxbc-posts-list-bottom'>";

$post_count = count($posts);
$item = 0;
foreach ( $posts as $post ) {
	$item++;
	if(isset($post->hidden) && $post->hidden){
		continue; // Item is hidden move to next item
	}
	$postUrl = $post->slug;
	if(defined('BIZINK_NOCONFLICTURL') && BIZINK_NOCONFLICTURL == true){
		$postUrl = add_query_arg('bizpress',$post->slug);
	}
	echo "<div class='cxbc-single-post cxbc-single-post-item-{$item} cxbc-single-post-count-{$post_count}'>";
		echo "<a href='{$postUrl}'><div class='cxbc-single-post-content'>";
		echo "<img class='cxbc-item-thumbnail' src='{$post->thumbnail}'>";
		echo "<div class='cxbc-post-title'><h4>{$post->title}</h4></div>";
		echo "<div class='learn-more'>View</div>";
	echo "</div></a></div>";		
}
echo "</div>";