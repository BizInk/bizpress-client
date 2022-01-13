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

$posts = $response->posts;

$post_type	= str_replace('-', '_', $response->post_type);
$page_id 	= cxbc_get_option( 'bizink-client_basic', "{$post_type}_page" );
$slug 		= get_permalink( $page_id );

$type_name = $response->type_name;

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
echo "<div class='cxbc-type-panel'>";
echo "<h4>{$type_name}</h4>";
echo "<div class='cxbc-type-list'>";
foreach ( $posts as $post ) {

	echo "<a href='{$slug}{$post->slug}'><div class='cxbc-single-type'>";
	echo "<img class='cxbc-item-thumbnail' src='{$post->thumbnail}'>";
	echo "<div class='cxbc-type-title'>{$post->title}</div>";
	echo "</div></a>";
}
echo "</div>";
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


// $list = $response->posts;

// if ( !is_array( $list ) || count( $list ) <= 0 ) {
// 	_e( 'Invalid Data', 'bizink-client' );
// 	return;
// }

// $content_page_id   = cxbc_get_option( 'bizink-client_basic', 'content_page' );
// $page_url = get_the_permalink( $content_page_id );
// echo "<div class='cxbc-list'>";
// 	foreach ( $list as $item ) {
// 		$post_id 	= $item->ID;
// 		$post_title = $item->title;
// 		$post_slug 	= $item->slug;
// 		$thumbnail 	= $item->thumbnail;
// 		$excerpt 	= $item->excerpt;

// 		echo "<div class='cxbc-item cxbc-item-{$post_id}'>";
// 		echo "<img class='cxbc-item-thumbnail' src='{$thumbnail}'>";
// 		echo "<a class='cxbc-item-title' href='{$page_url}{$post_slug}'>{$post_title}</a>";
// 		echo "<p class='cxbc-item-excerpt'>". wp_trim_words( $excerpt, 20 ) ."</p>";
// 		echo "</div>";
// 	}
// echo "</div>";

// $total_pages 	= $response->post_count;
// $per_page   = cxbc_get_option( 'bizink-client_basic', 'post_per_page', 10 );
// $content_page_id   = cxbc_get_option( 'bizink-client_basic', 'content_page' );
// $page_url = get_the_permalink( $content_page_id );

// echo "<div class='cxbc-pagination'>";
// $big = 999999999; // need an unlikely integer
// echo paginate_links( array(
//     'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
//     'format' => '?paged=%#%',
//     'current' => max( 1, get_query_var('paged') ),
//     'total' => $total_pages/$per_page
// ) );
// echo "</div>";