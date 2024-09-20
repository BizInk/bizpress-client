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
	if ( isset($response->status) == false || $response->status == 0 ) {
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
$page_id = null;
if ( 'business-lifecycle' == $post_type || 'business-content' == $post_type ) {
	$default_title 	= cxbc_get_option( 'bizink-client_content', 'business_title' );
	$default_desc 	= cxbc_get_option( 'bizink-client_content', 'business_desc' );
	$page_id = cxbc_get_option( 'bizink-client_basic', 'business_content_page' );
}
elseif ( 'xero-content' == $post_type ) {
	$default_title 	= cxbc_get_option( 'bizink-client_content', 'xero_title' );
	$default_desc 	= cxbc_get_option( 'bizink-client_content', 'xero_desc' );
	if(empty($default_title) && $default_title != ""){
		$default_title = __('Xero Resources', 'bizink-client');
	}
	$page_id =  cxbc_get_option( 'bizink-client_basic', 'xero_content_page' );
}
elseif ( 'quickbooks-content' == $post_type ) {
	$default_title 	= cxbc_get_option( 'bizink-client_content', 'quickbooks_title' );
	$default_desc 	= cxbc_get_option( 'bizink-client_content', 'quickbooks_desc' );
	if(empty($default_title) && $default_title != ""){
		$default_title = __('QuickBooks Resources', 'bizink-client');
	}
	$page_id =  cxbc_get_option( 'bizink-client_basic', 'quickbooks_content_page' );
}
elseif ( 'sage-content' == $post_type ) {
	$default_title 	= cxbc_get_option( 'bizink-client_content', 'sage_title' );
	$default_desc 	= cxbc_get_option( 'bizink-client_content', 'sage_desc' );
	if(empty($default_title) && $default_title != ""){
		$default_title = __('Sage Resources', 'bizink-client');
	}
	$page_id =  cxbc_get_option( 'bizink-client_basic', 'sage_content_page' );
}
elseif ( 'payroll-content' == $post_type ) {
	$default_title 	= cxbc_get_option( 'bizink-client_content', 'payroll_title' );
	$default_desc 	= cxbc_get_option( 'bizink-client_content', 'payroll_desc' );
	if(empty($default_title) && $default_title != ""){
		$default_title = __('Payroll Resources', 'bizink-client');
	}
	$page_id = cxbc_get_option( 'bizink-client_basic', 'payroll_content_page' );
}
elseif ( 'qbo-content' == $post_type ) {
	$default_title 	= cxbc_get_option( 'bizink-client_content', 'qbo_title' );
	$default_desc 	= cxbc_get_option( 'bizink-client_content', 'qbo_desc' );
	if(empty($default_title) && $default_title != ""){
		$default_title = __('QuickBooks Resources', 'bizink-client');
	}
	$page_id = cxbc_get_option( 'bizink-client_basic', 'quickbooks_content_page' );
}
elseif ( 'myob-content' == $post_type ) {
	$default_title 	= cxbc_get_option( 'bizink-client_content', 'myob_title' );
	$default_desc 	= cxbc_get_option( 'bizink-client_content', 'myob_desc' );
	if(empty($default_title) && $default_title != ""){
		$default_title = __('MYOB Resources', 'bizink-client');
	}
	$page_id = cxbc_get_option( 'bizink-client_basic', 'myob_content_page' );
}
elseif (strpos($post_type, 'keydates') !== false) {
	$default_title 	= cxbc_get_option( 'bizink-client_content', 'keydates_title' );
	$default_desc 	= cxbc_get_option( 'bizink-client_content', 'keydates_desc' );
	if(empty($default_title) && $default_title != ""){
		$default_title = __('Key Dates', 'bizink-client');
	}
	$page_id = cxbc_get_option( 'bizink-client_basic', 'keydates_content_page' );
}

//dropdown after single topics
if(isset($_GET)){
	$query_value = $_GET;	
}

if (strpos($post_type, 'keydates') === false) {
	?>
	<div class="topic-title">
		<h2><?php _e('Browse by Topic','bizink-client'); ?> </h2>
	</div>
	<div class='cxbc-topics-list'>
		<?php
		$topic_coun = 0;
		global $wp;
		$current_url = home_url( $wp->request ).'/?'.$_SERVER['QUERY_STRING'];
		$selected = '';
		?>
		<div class="topic-dropdown">
			<select onchange="this.options[this.selectedIndex].value && (window.location = this.options[this.selectedIndex].value);">
				<option value=''><?php _e('Browse all topics...','bizink-client'); ?></option>
				<?php
				foreach ( $topics as $topic ) {	
					if ( $topic_coun == 0 ) {
						$taxonomy 	= $topic->taxonomy;
						$first_term = $topic->slug;
					}
					$link = add_query_arg( $topic->taxonomy, $topic->slug, get_permalink( get_the_ID() ) );		
					$selected = ($current_url == $link) ? 'selected' : '';
					echo "<option value='{$link}'{$selected}>{$topic->name}</option>";
				}
				?>
			</select>
		</div>
	
	</div>
	<?php
}

$taxonomy_topics = 'business-article-topics';
if ( 'business-content' == $post_type ) {
	$taxonomy_topics = 'business-article-topics';
}
elseif ( 'business-lifecycle' == $post_type ) {
	$taxonomy_topics = 'business-topics';
}
elseif ( 'xero-content' == $post_type ) {
	$taxonomy_topics = 'xero-topics';
}
elseif ( 'quickbooks-content' == $post_type ) {
	$taxonomy_topics = 'quickbooks-topics';
}
elseif ( 'sage-content' == $post_type ) {
	$taxonomy_topics = 'sage-topics';
}
elseif ( 'myob-content' == $post_type ) {
	$taxonomy_topics = 'myob-topics';
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
$structure = get_option( 'permalink_structure' );

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
	$pages = ceil($post_count / 12);
	$item = 0;

	if($post_count <= 0):
		echo "<p>".__('No posts found for this topic.','bizink-client')."</p>";
	else:
		bizink_bizpress_display_pagnation($pages,1);

		foreach ( $posts as $post ) {
			if($item == 0){
				echo "<div class='cxbc-posts-list-page' data-page='1'>";
			}
			elseif($item % 12 == 0){
				echo "</div>";
				echo "<div class='cxbc-posts-list-page' data-page='".(($item/12) + 1)."'>";
			}
			elseif($item == $post_count){
				echo "</div>";
			}
			$item++;
			if(isset($post->hidden) && $post->hidden){
				continue; // Item is hidden move to next item
			}
			$postUrl = get_permalink($page_id) . $post->slug;
			if((defined('BIZINK_NOCONFLICTURL') && BIZINK_NOCONFLICTURL == true) || empty($structure)){
				$page = get_post($page_id);
				$postUrl = add_query_arg(array('bizpress' => $post->slug,'pagename' => $page->page_name),get_home_url());
			}
			?>
			<div class="cxbc-single-post cxbc-single-post-item-<?= $item ?> cxbc-single-post-count-<?= $post_count ?>">
				<a href="<?= $postUrl ?>">
					<div class="cxbc-single-post-content">
						<?php 
						$image = $post->thumbnail;
						if(empty($image)){
							$image = plugins_url( 'assets/img/default.png', CXBPC );
						}
						?>
						<img alt="<?= $post->title ?>" class="cxbc-item-thumbnail" src="<?= $image; ?>">
						<div class="cxbc-post-title">
							<h4><?= $post->title ?></h4>
						</div>
						<div class="learn-more"><?php _e('Learn more','bizink-client'); ?></div>
					</div>
				</a>
			</div>
			<?php	
		}
		echo "</div>";
		bizink_bizpress_display_pagnation($pages,1);
	endif;
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
				$postUrl =  get_permalink($page_id) . $post->slug;
				if((defined('BIZINK_NOCONFLICTURL') && BIZINK_NOCONFLICTURL == true) || empty($structure)){
					$page = get_post($page_id);
					$postUrl = add_query_arg(array('bizpress' => $post->slug,'pagename' => $page->page_name),get_home_url());
				}

				echo "<li class='cxbc-keydates-post-count-{$post_count}'>";
				echo "<a href='{$postUrl}'>{$post->title}</a>";
				echo "</li>";
			}
			echo "</ul>";
		echo "</div>";
	echo "</div>";
}

$dataTopic = isset($_GET[$taxonomy_topics]) ? trim($_GET[$taxonomy_topics]) : "false";
echo '<div style="display:none;" class="bizpress-data" id="bizpress-data"
data-siteid="'.(bizpress_anylitics_get_site_id() ? bizpress_anylitics_get_site_id() : "false").'"
data-title="'.$default_title.'" 
data-url="'. get_permalink( get_the_ID() ) .'" 
data-posttype="'.$post_type.'"
data-topics="'.$dataTopic.'"
data-types="'. $taxonomy_topics . '" ></div>';