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

$topics 		= $response->topics;
$posts 			= $response->posts;
$post_type 		= $response->post_type;

$default_title 	= __( 'Business Resources', 'bizink-client' );
$default_desc 	= __( 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Natus delectus officiis quae facere rerum exercitationem labore aperiam ducimus soluta debitis, magni aspernatur tempore omnis similique eum illo dolorum corrupti accusantium?', 'bizink-client' );

if ( 'business-content' == $post_type ) {
	$default_title 	= cxbc_get_option( 'bizink-client_content', 'business_title' );
	$default_desc 	= cxbc_get_option( 'bizink-client_content', 'business_desc' );
}
elseif ( 'xero-content' == $post_type ) {
	$default_title 	= cxbc_get_option( 'bizink-client_content', 'xero_title' );
	$default_desc 	= cxbc_get_option( 'bizink-client_content', 'xero_desc' );
}
elseif ( 'keydates-content' == $post_type ) {
	$default_title 	= cxbc_get_option( 'bizink-client_content', 'keydates_title' );
	$default_desc 	= cxbc_get_option( 'bizink-client_content', 'keydates_desc' );
}

if ($post_type != 'keydates-content') {

	echo "<div class='cxbc-topics-list'>";
	$topic_coun = 0;
	foreach ( $topics as $topic ) {
		if ( $topic_coun == 0 ) {
			$taxonomy 	= $topic->taxonomy;
			$first_term = $topic->slug;
		}
		$link = add_query_arg( $topic->taxonomy, $topic->slug, get_permalink( get_the_ID() ) );
		echo "<a href='{$link}'><div class='cxbc-single-topic'>";
		echo "<img class='cxbc-item-thumbnail' src='{$topic->thumbnail}'>";
		echo "<div class='cxbc-topic-title'>{$topic->name}</div>";
		echo "</div></a>";
		$topic_coun++;
	}
	echo "</div>";

}

// cxbc_pri($response);

$taxonomy_topics = 'business-topics';
if ( 'business-content' == $post_type ) {
	$taxonomy_topics = 'business-topics';
}
elseif ( 'xero-content' == $post_type ) {
	$taxonomy_topics = 'xero-topics';
}
elseif ( 'keydates-content' == $post_type ) {
	$taxonomy_topics = 'keydates-topics';
}

$term = isset( $_GET[ $taxonomy_topics ] ) ? $_GET[ $taxonomy_topics ] : $first_term;

$single_term 	= $topics->$term;
$term_name 		= isset( $_GET[ $taxonomy_topics ] ) ? $single_term->name : $default_title;
$term_desc 		= isset( $_GET[ $taxonomy_topics ] ) ? $single_term->description : $default_desc;


if ($post_type != 'keydates-content') {

	echo "<div class='cxbc-topics-heading'>";
	echo "<h2>{$term_name}</h2>";
	echo "<p>{$term_desc}</p>";
	echo "</div>";

	$next_icon 	= plugins_url( 'assets/img/next-icon.png', CXBPC );
	echo "<div class='cxbc-posts-list'>";
	echo "<div class='cxbc-posts-list-top'>";

	$post_count = 1;
	foreach ( $posts as $post ) {
		echo "<div class='cxbc-single-post cxbc-single-post-count-{$post_count}'>";
		echo "<a href='{$post->slug}'><div class='cxbc-single-post-content'>";
		echo "<img class='cxbc-item-thumbnail' src='{$post->thumbnail}'>";

		// if ( $post_count == 4 ) {
		// 	echo "<div class='cxbc-post-title'><h4>{$post->title}</h4></div>";
		// 	echo "<a class='cxbc-learn-more-btn' href='{$post->slug}'>". __( 'Learn More', 'bizink-client' ) ."</a>";
		// }
		// else {
		echo "<div class='cxbc-post-title'><h4>{$post->title}<span><img class='cxbc-next-icon' src='{$next_icon}'></span></h4></div>";
		// }
		echo "</div></a></div>";

		if ( $post_count == 3 && count( $posts ) > 3 ) {
			echo "</div><div class='cxbc-posts-list-mid'>";
		}
		if ( $post_count == 5 ) {
			echo "</div><div class='cxbc-posts-list-bottom'>";
		}
		$post_count++;
	}
	echo "</div>";
	echo "</div>";

	if ( !empty( $posts ) ) {
		$term = isset( $_GET[ $taxonomy_topics ] ) ? $_GET[ $taxonomy_topics ] : 'all';
		echo "<a href='topic/{$term}'><div class='cxbc-all-post-btn'>See All</a>";
	}

}
else{
	echo "<div class='cxbc-topics-heading' style='text-align:left'>";
	echo "<h2>Due Dates</h2>";
	echo "<p>Key lodgement and payment dates for ".date("Y")."-".date("Y", strtotime("+1 year"))." are: </p>";
	echo "</div>";

	$next_icon 	= plugins_url( 'assets/img/next-icon.png', CXBPC );
	echo "<div class='cxbc-posts-list'>";
	echo "<div class='cxbc-posts-list-top'>";
	echo "<ul>";
	$post_count = 1;
	foreach ( $posts as $post ) {
		echo "<li class='cxbc-keydates-post-count-{$post_count}'>";
		echo "<a href='{$post->slug}'>{$post->title}</a>";
		echo "</li>";
	}
	echo "</ul>";
	echo "</div>";
	echo "</div>";
}
