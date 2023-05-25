<?php 

extract($args);

if ( empty($responce) && empty($args['response']) ) {
	echo "<p>". __( 'Something went wrong. The data for this page could not be found.', 'bizink-client' ) ."</p>";
	if(defined('WP_DEBUG') && WP_DEBUG == true){
		_e('Got a Null for $responce in views/topics.php', 'bizink-client' );
	}
	return;
}

if(empty($response) == false){
	if ( $response->status == 0 ) {
		if(empty($response->message)){
			echo "<p>".__('Sorry there was an error. There was an error retreveing the data for this page.','bizink-client')."</p>";
		}
		else{
			echo "<p>{$response->message}</p>";
		}
		return;
	}
}


$topics 		= $response->topics;
$posts 			= $response->posts;
$post_type 		= $response->post_type;

$default_title 	= __( 'Business Resources', 'bizink-client' );
$default_desc 	= __( '', 'bizink-client' );

/** Return if [bizpress-content] or [bizink-content] is on page but this page has not be configured in settings */
if(empty($post_type)){
	echo '<p><b>';
	_e('Sorry this page has not been configured in the BizPress plugin.', 'bizink-client');
	echo '</b></p>';
	return;
}

if ( 'business-lifecycle' == $post_type ) {
	$default_title 	= cxbc_get_option( 'bizink-client_content', 'business_title' );
	$default_desc 	= cxbc_get_option( 'bizink-client_content', 'business_desc' );
}
elseif ( 'xero-content' == $post_type ) {
	$default_title 	= cxbc_get_option( 'bizink-client_content', 'xero_title' );
	$default_desc 	= cxbc_get_option( 'bizink-client_content', 'xero_desc' );
}
elseif ( 'quickbooks-content' == $post_type ) {
	$default_title 	= cxbc_get_option( 'bizink-client_content', 'quickbooks_title' );
	$default_desc 	= cxbc_get_option( 'bizink-client_content', 'quickbooks_desc' );
}
elseif ( 'payroll-content' == $post_type ) {
	$default_title 	= cxbc_get_option( 'bizink-client_content', 'payroll_title' );
	$default_desc 	= cxbc_get_option( 'bizink-client_content', 'payroll_desc' );
}
elseif ( 'qbo-content' == $post_type ) {
	$default_title 	= cxbc_get_option( 'bizink-client_content', 'qbo_title' );
	$default_desc 	= cxbc_get_option( 'bizink-client_content', 'qbo_desc' );
}
elseif (strpos($post_type, 'keydates') !== false) {
	$default_title 	= cxbc_get_option( 'bizink-client_content', 'keydates_title' );
	$default_desc 	= cxbc_get_option( 'bizink-client_content', 'keydates_desc' );
}

//dropdown after single topics
if(isset($_GET)){
	$query_value = $_GET;	
}

if (strpos($post_type, 'keydates') === false) {
	echo '<div class="topic-title"><h2>Browse by Topic</h2></div>';
	echo "<div class='cxbc-topics-list'>";
	$topic_coun = 0;
	if(empty($query_value)){
		foreach ( $topics as $topic ) {	
			if ( $topic_coun == 0 ) {
				$taxonomy 	= $topic->taxonomy;
				$first_term = $topic->slug;
			}
			$link = add_query_arg( $topic->taxonomy, $topic->slug, get_permalink( get_the_ID() ) );

			echo "<a href='{$link}'><div class='cxbc-single-topic'>";
			//echo "<img class='cxbc-item-thumbnail' src='{$topic->thumbnail}'>";
			echo "<div class='cxbc-topic-title'>{$topic->name}</div>";
			echo "</div></a>";
			$topic_coun++;
		}
	}
	else{
		global $wp;
		$current_url = home_url( $wp->request ).'/?'.$_SERVER['QUERY_STRING'];
		$selected = '';
		echo '<div class="topic-dropdown"><select onchange="this.options[this.selectedIndex].value && (window.location = this.options[this.selectedIndex].value);">';
		echo "<option value=''>Browse other topics...</option>";	
			foreach ( $topics as $topic ) {	
				if ( $topic_coun == 0 ) {
					$taxonomy 	= $topic->taxonomy;
					$first_term = $topic->slug;
				}
				$link = add_query_arg( $topic->taxonomy, $topic->slug, get_permalink( get_the_ID() ) );		
				$selected = ($current_url == $link) ? 'selected' : '';
				echo "<option value='{$link}'{$selected}>{$topic->name}</option>";		
				//echo "{$topic->name}";
				//echo "</option>";
			}
		echo '</select></div>';
		
	}
	
	echo "</div>";

}

$taxonomy_topics = 'business-topics';
if ( 'business-lifecycle' == $post_type ) {
	$taxonomy_topics = 'business-topics';
}
elseif ( 'xero-content' == $post_type ) {
	$taxonomy_topics = 'xero-topics';
}
elseif ( 'quickbooks-content' == $post_type ) {
	$taxonomy_topics = 'quickbooks-topics';
}
elseif ( 'payroll-content' == $post_type ) {
	$taxonomy_topics = 'payroll-topics';
}
elseif ( 'qbo-content' == $post_type ) {
	$taxonomy_topics = 'qbo-topics';
}
elseif (strpos($post_type, 'keydates') !== false) {
	$taxonomy_topics = 'keydates-topics';
}

/*
$term = isset( $_GET[ $taxonomy_topics ] ) ? $_GET[ $taxonomy_topics ] : $first_term;
$single_term 	= $topics->$term;
$term_name 		= isset( $_GET[ $taxonomy_topics ] ) ? $single_term->name : $default_title;
$term_desc 		= isset( $_GET[ $taxonomy_topics ] ) ? $single_term->description : $default_desc;
*/

if (strpos($post_type, 'keydates') === false) {

	$term = isset( $_GET[ $taxonomy_topics ] ) ? $_GET[ $taxonomy_topics ] : (isset($first_term) ? $first_term : 'business-topics'); 
	$single_term 	= $topics->$term;
	$term_name 		= isset( $_GET[ $taxonomy_topics ] ) ? $single_term->name : $default_title;
	$term_desc 		= isset( $_GET[ $taxonomy_topics ] ) ? $single_term->description : $default_desc;

	echo "<div class='cxbc-topics-heading'>";
	echo "<h2>{$term_name}</h2>";
	echo "<p>{$term_desc}</p>";
	echo "</div>";

	$next_icon 	= plugins_url( 'assets/img/next-icon.png', CXBPC );
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
		echo "<div class='learn-more'>Learn more</div>";
		echo "</div></a></div>";		
	}
	echo "</div>";

	//if ( !empty( $posts ) ) {
		//$term = isset( $_GET[ $taxonomy_topics ] ) ? $_GET[ $taxonomy_topics ] : 'all';
		//echo "<a href='topic/{$term}'><div class='cxbc-all-post-btn'>See All</a>";
	//}


}
else{
	echo "<div class='cxbc-topics-heading' style='text-align:left'>";
	echo "<h2>".__('Due Dates','bizpress-client')."</h2>";
	echo "<p>Key lodgement and payment dates for this financial year are: </p>";
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

echo '<div style="display:none;" class="bizpress-data" id="bizpress-data"
data-single="false"
data-siteid="'.(bizpress_anylitics_get_site_id() ? bizpress_anylitics_get_site_id() : "false").'"
data-title="'.$default_title.'" 
data-url="'. get_permalink( get_the_ID() ) .'" 
data-posttype="'.$post_type.'"
data-topics="'. (empty($topics) == false ? implode(',',$topics) : "false") .'"
data-types="'. $taxonomy_topics . '" ></div>';