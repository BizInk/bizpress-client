<?php
if( !function_exists( 'get_plugin_data' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}

/**
 * Bizpress Site ID
 */
if( ! function_exists( 'bizpress_anylitics_get_site_id' ) ) :
function bizpress_anylitics_get_site_id(){
	if(defined('BIZPRESS_ANALYTICS') && BIZPRESS_ANALYTICS == false){
		// Analytics disabled
		return false;
	}

	$bizpressOptions = get_option('bizink-client_basic',false);
	if($bizpressOptions == false){
		// Bizpress not setup
		return false;
	}
	$siteID = get_option('bizpress_site_id',false);
	if(empty($bizpressOptions['content_region'])){
		$bizpressOptions['content_region'] = 'all';
	}
	$version = '1.7.1';
	if(!defined('CXBPC')){
		$versionData = get_plugin_data(constant('CXBPC'))['Version'];
		if(!empty($versionData)) $version = $versionData;
	}
	$siteData = array(
		"siteName" => get_bloginfo('name'),
		"siteDescription" => get_bloginfo("description"),
		"url" => get_bloginfo("url"),
		"lang" => get_bloginfo("language"),
		"timezone" => wp_timezone_string(),
		"wpVersion" => get_bloginfo( 'version' ),
		"rtl" => is_rtl(),
		"bizpressVersion" => $version,
		"region" => $bizpressOptions['content_region'],
		"themeName" => get_stylesheet()
	);
	if($siteID == false){ // New Site
		$args = array(
			'body' => json_encode($siteData),
			'headers' => [
				'Cache-Control' => 'no-cache',
				'Content-Type'  => 'application/json',
				'Accept' 		=> 'application/json',
			],
			'timeout'     => 10,
			'redirection' => 3,
			'httpversion' => '1.1',
		);
		$responce = wp_remote_post(BIZINK_ANALYTICS_URL .'/site',$args);
		$code = wp_remote_retrieve_response_code($responce);
		if($code >= 200 && $code < 400){
			$data = json_decode(wp_remote_retrieve_body($responce));
			if($data->id){
				add_option('bizpress_site_id',$data->id);
				$siteID = $data->id;
			}
			else{
				return false; // Don't know how it would get here
			}
		}
		else{
			return false; // Server or request error
		}
	}

	return $siteID;
}
endif;

function bizpress_update_site($siteID,$siteData){
	if(defined('BIZPRESS_ANALYTICS') && BIZPRESS_ANALYTICS == false){
		// Analytics disabled
		//return false;
	}
	$args = array(
		'body' => json_encode($siteData),
		'method' => 'PUT',
		'headers' => [
			'Cache-Control' => 'no-cache',
			'Content-Type'  => 'application/json',
			'Accept' 		=> 'application/json',
		],
		'timeout'     => 10,
		'redirection' => 3,
		'httpversion' => '1.1',
	);
	$responce = wp_remote_request(BIZINK_ANALYTICS_URL ."/site/".$siteID ,$args);
	$code = wp_remote_retrieve_response_code($responce);
	if($code >= 200 && $code < 400){
		return true;
	}
	else{
		return false;
	}
}

function bizpress_schedule_siteupdate(){
	// Schedules the event if it's NOT already scheduled.
    if ( ! wp_next_scheduled ( 'bizpress_siteupdate_event' ) ) {
        wp_schedule_event( time(), '2day', 'bizpress_siteupdate_event' );
    }
}
add_action( 'init', 'bizpress_schedule_siteupdate' );

function bizpress_siteupdate_event_hook(){
	bizpress_update_site();
}
add_action( 'bizpress_siteupdate_event', 'bizpress_siteupdate_event_hook' );

if( ! function_exists( 'cxbc_pri' ) ) :
function cxbc_pri( $data ) {
	echo '<pre>';
	if( is_object( $data ) || is_array( $data ) ) {
		print_r( $data );
	}
	else {
		var_dump( $data );
	}
	echo '</pre>';
}
endif;

/**
 * @param bool $show_cached either to use a cached list of posts or not. If enabled, make sure to wp_cache_delete() with the `save_post` hook
 */
if( ! function_exists( 'cxbc_get_posts' ) ) :
function cxbc_get_posts( $args = [], $show_heading = true, $show_cached = true ) {

	//	'orderby' => 'title',
	//	'order'   => 'DESC',
	$defaults = [
		'post_type'         => 'post',
		'posts_per_page'    => -1,
		'post_status'		=> 'publish',
	];

	$_args = wp_parse_args( $args, $defaults );

	// use cache
	if( true === $show_cached && ( $cached_posts = wp_cache_get( "cxbc_{$_args['post_type']}", 'cxbc' ) ) ) {
		$posts = $cached_posts;
	}

	// don't use cache
	else {
		$queried = new WP_Query( $_args );
		$posts = [];
		foreach( $queried->posts as $post ) :
			$posts[ $post->ID ] = $post->post_title;
		endforeach;
		
		wp_cache_add( "cxbc_{$_args['post_type']}", $posts, 'cxbc', 3600 );
	}

	$posts = $show_heading ? [ '' => sprintf( __( '- Choose a %s -', 'cxbc' ), $_args['post_type'] ) ] + $posts : $posts;
	return apply_filters( 'cxbc_get_posts', $posts, $_args );
}
endif;

if( !function_exists( 'cxbc_get_option' ) ) :
function cxbc_get_option( $key, $section, $default = '' ) {

	$options = get_option( $key );

	if ( isset( $options[ $section ] ) ) {
		return $options[ $section ];
	}

	return $default;
}
endif;

if( !function_exists( 'cxbc_get_template' ) ) :
/**
 * Includes a template file resides in /views diretory
 *
 * It'll look into /bizink-client directory of your active theme
 * first. if not found, default template will be used.
 * can be overriden with bizink-client_template_override_dir hook
 *
 * @param string $slug slug of template. Ex: template-slug.php
 * @param string $sub_dir sub-directory under base directory
 * @param array $fields fields of the form
 */
function cxbc_get_template( $slug, $base = 'views', $args = null ) {

	// templates can be placed in this directory
	$override_template_dir = apply_filters( 'cxbc_template_override_dir', get_stylesheet_directory() . '/bizink-client/', $slug, $base, $args );
	
	// default template directory
	$plugin_template_dir = dirname( CXBPC ) . "/{$base}/";

	// full path of a template file in plugin directory
	$plugin_template_path =  $plugin_template_dir . $slug . '.php';
	
	// full path of a template file in override directory
	$override_template_path =  $override_template_dir . $slug . '.php';

	// if template is found in override directory
	if( file_exists( $override_template_path ) ) {
		ob_start();
		include $override_template_path;
		return ob_get_clean();
	}
	// otherwise use default one
	elseif ( file_exists( $plugin_template_path ) ) {
		ob_start();
		include $plugin_template_path;
		return ob_get_clean();
	}
	else {
		return __( 'Template not found!', 'bizink-client' );
	}
}
endif;

/**
 * Generates some action links of a plugin
 *
 * @since 1.0
 */
if( !function_exists( 'cxbc_action_link' ) ) :
function cxbc_action_link( $plugin, $action = '' ) {

	$exploded	= explode( '/', $plugin );
	$slug		= $exploded[0];

	$links = [
		'install'		=> wp_nonce_url( admin_url( "update.php?action=install-plugin&plugin={$slug}" ), "install-plugin_{$slug}" ),
		'update'		=> wp_nonce_url( admin_url( "update.php?action=upgrade-plugin&plugin={$plugin}" ), "upgrade-plugin_{$plugin}" ),
		'activate'		=> wp_nonce_url( admin_url( "plugins.php?action=activate&plugin={$plugin}&plugin_status=all&paged=1&s" ), "activate-plugin_{$plugin}" ),
		'deactivate'	=> wp_nonce_url( admin_url( "plugins.php?action=deactivate&plugin={$plugin}&plugin_status=all&paged=1&s" ), "deactivate-plugin_{$plugin}" ),
	];

	if( $action != '' && array_key_exists( $action, $links ) ) return $links[ $action ];

	return $links;
}
endif;

if( ! function_exists( 'ncrypt' ) ) :
function ncrypt() {
    $ncrypt = new \mukto90\Ncrypt;

    $secret_key = 'rd4jd874hey64t';
    $secret_iv  = '8su309fr7uj34';
    $ncrypt->set_secret_key( $secret_key );
    $ncrypt->set_secret_iv( $secret_iv );
    $ncrypt->set_cipher( 'AES-256-CBC' );

    return $ncrypt;
}
endif;

function bizpress_getoptions(){
	$options = get_option( 'bizink-client_basic' );
	if(empty($options)){
		$options = array(
			'user_email' => '',
			'user_password' => '',
			'content_region' => 'au',
		);
	}
	if(empty($options['user_email'])){
		$options['user_email'] = '';
	}
	if(empty($options['user_password'])){
		$options['user_password'] = '';
	}
	if(empty($options['content_region'])){
		$options['content_region'] = 'au';
	}
	return $options;
}

function bizpress_landingpage_all(){
	//$data = get_transient("bizpress_landingpages");
	$args = bizink_url_authontication();
	$base_url = bizink_get_master_site_url();
	$options = bizpress_getoptions();
	$url = add_query_arg( [ 
        'email'         => $options['user_email'],
        'password'      => ncrypt()->encrypt( $options['user_password'] ),
        'luca'		    => function_exists('luca') ? true : false
    ], wp_slash( $base_url.'wp-json/wp/v2/landing' ) );
    $response = wp_remote_get( $url, $args );
    if ( is_wp_error( $response ) ) {
        return $response;
    } 
    else {
		set_transient( "bizpress_landingpages", $response, (DAY_IN_SECONDS * 2) );
        return json_decode( wp_remote_retrieve_body( $response ) );
    }
}
function bizink_get_content_base($post_type, $api_endpoint, $slug = '', $paged = null, $term = 'business-article-topics'){
	$options = bizpress_getoptions();
	if(!empty($paged)){
		$paged = get_query_var( 'paged' ) ? absint( get_query_var( 'paged' ) ) : 1;
	}
    $base_url = bizink_get_master_site_url();
    $content_region = $options['content_region'];
	$country = 'AU';
	switch($options['content_region']){
		case 'ca':
			$country = 'CA';
			break;
		case 'us':
			$country = 'US';
			break;
		case 'nz':
			$country = 'NZ';
			break;
		case 'gb':
		case 'uk':
			$country = 'GB';
		break;
		case 'ie':
			$country = 'IE';
		break;
		case 'au':
		default:
			$country = 'AU';
			break;			
	}
	$country = apply_filters( 'bizink-keydates-country', $country );

	$luca = false;
	if(function_exists('luca')){
		$luca = true;
	}
	elseif(in_array('bizpress-luca-2/bizpress-luca-2.php', apply_filters('active_plugins', get_option('active_plugins')))){ 
		$luca = true;
	}

	$per_paage = -1;

	$args = [
		'rest_route'    => "/bizink-publisher/v1.1/{$api_endpoint}",
        'per_page'      => $per_paage,
		'status'        => 'publish',
		'post_type'     => $post_type,
		'country'		=> $country,
        'region'		=> $content_region,
	];

	if($luca){
		$args['luca'] = true;
	}
	else{
		$args['email'] = $options['user_email'];
		$args['password'] = ncrypt()->encrypt( $options['user_password'] );
		$args['luca'] = false;
	}

	if($per_paage > 0){
		$args['paged'] = $paged;
	}

	if(!empty($slug)){
		$args['slug'] = $slug;
	}

	if(!empty($term)){
		$args['term'] = isset( $_GET[ $term ] ) ? $_GET[ $term ] : $term;
		if ( 'resources' == $post_type || 'resources-content' == $post_type ) {
			$args['term'] = $term;
		}
	}

    $url = add_query_arg( $args, wp_slash( $base_url ) );
	$requestArgs = bizink_url_authontication();
    $request    = wp_remote_request( $url, $requestArgs );
	if ( !is_wp_error( $request ) && ($request['response']['code'] == 200 || $request['response']['code'] == 201) ) {
		$body = wp_remote_retrieve_body( $request );
		$data = json_decode( $body);
		if(!empty($data->product)){
			update_option('bizpress_product', $data->product);
		}
		if(!empty($data->subscriptions_expiry)){
			update_option('bizpress_subscriptions_expiry', $data->subscriptions_expiry);
		}
		return $data;
	}
	else{
		if(defined('WP_DEBUG') && WP_DEBUG == true){
			echo 'Error: ' . $request->get_error_message();
		}
		return null;
	}
    
}
function bizink_get_content_types($post_type, $api_endpoint, $slug = '', $paged = null){
	$taxonomy_topics = 'business-article-type';
	if ( 'business-content' == $post_type ) {
		$taxonomy_topics = 'business-article-type';
	}
	elseif ( 'business-lifecycle' == $post_type ) {
		$taxonomy_topics = 'business-type';
	}
	elseif ( 'xero-content' == $post_type ) {
		$taxonomy_topics = 'xero-type';
	}
	elseif ( 'myob-content' == $post_type ) {
		$taxonomy_topics = 'myob-type';
	}
	elseif ( 'sage-content' == $post_type ) {
		$taxonomy_topics = 'sage-type';
	}
	elseif ( 'quickbooks-content' == $post_type ) {
		$taxonomy_topics = 'quickbooks-type';
	}
	elseif ( 'freshbooks-content' == $post_type ) {
		$taxonomy_topics = 'freshbooks-type';
	}
	elseif ( 'payroll-content' == $post_type ) {
		$taxonomy_topics = 'payroll-type';
	}
	elseif ( 'resources' == $post_type || 'resources-content' == $post_type ) {
		$taxonomy_topics = 'resources-types';
	}
	elseif ( 'payroll-glossary' == $post_type || 'accounting-terms' == $post_type || 'business-terms' == $post_type || 'calculators' == $post_type ) {
		$taxonomy_topics = 'region';
	}
	return bizink_get_content_base( $post_type, $api_endpoint, $slug, $paged, $taxonomy_topics );
}

function bizink_get_content_new( $post_type, $api_endpoint, $slug = '', $paged = null ) {
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
	elseif ( 'myob-content' == $post_type ) {
		$taxonomy_topics = 'myob-topics';
	}
	elseif ( 'sage-content' == $post_type ) {
		$taxonomy_topics = 'sage-topics';
	}
	elseif ( 'quickbooks-content' == $post_type ) {
		$taxonomy_topics = 'quickbooks-topics';
	}
	elseif ( 'freshbooks-content' == $post_type ) {
		$taxonomy_topics = 'freshbooks-topics';
	}
	elseif ( 'payroll-content' == $post_type ) {
		$taxonomy_topics = 'payroll-topics';
	}
	elseif ( 'resource' == $post_type || 'resources' == $post_type || 'resources-content' == $post_type ) {
		$taxonomy_topics = 'resources-topics';
	}
	elseif ( 'payroll-glossary' == $post_type || 'accounting-terms' == $post_type || 'business-terms' == $post_type || 'calculators' == $post_type ) {
		$taxonomy_topics = 'region';
	}
	return bizink_get_content_base( $post_type, $api_endpoint, $slug, $paged, $taxonomy_topics );
}

function bizink_get_single_content( $api_endpoint, $slug = '' ) {
    $options        = bizpress_getoptions();
    $paged          = ( get_query_var( 'paged' ) ) ? absint( get_query_var( 'paged' ) ) : 1;
    $base_url 		= bizink_get_master_site_url();
	$keydate_country = 'AU';
	switch(strtolower($options['content_region'])){
		case 'ca':
			$keydate_country = 'CA';
			break;
		case 'us':
			$keydate_country = 'US';
			break;
		case 'nz':
			$keydate_country = 'NZ';
			break;
		case 'gb':
		case 'uk':
			$keydate_country = 'GB';
		break;
		case 'ie':
			$keydate_country = 'IE';
		break;
		case 'au':
		default:
			$keydate_country = 'AU';
			break;			
	}
	$keydate_country = apply_filters( 'bizink-keydates-country', $keydate_country );

	$luca = false;
	if(function_exists('luca')){
		$luca = true;
	}
	else if(in_array('bizpress-luca-2/bizpress-luca-2.php', apply_filters('active_plugins', get_option('active_plugins')))){ 
		$luca = true;
	}

	$args = [
		'rest_route'    => "/bizink-publisher/v1.1/{$api_endpoint}",
		'slug'         	=> $slug,
		'paged'         => $paged,
		'kd_region' 	=> strtolower($keydate_country),
	];

	if($luca){
		$args['luca'] = true;
	}
	else{
		$args['email'] = $options['user_email'];
		$args['password'] = ncrypt()->encrypt( $options['user_password'] );
		$args['luca'] = false;
	}

    $url = add_query_arg( $args, wp_slash( $base_url ) );

    $request = wp_remote_request( $url, bizink_url_authontication() );

	if ( !is_wp_error( $request ) && ($request['response']['code'] == 200 || $request['response']['code'] == 201) ) {
		$body = wp_remote_retrieve_body( $request );
		$data = json_decode( $body );
		if(!empty($data->product)){
			update_option('bizpress_product', $data->product);
		}
		if(!empty($data->subscriptions_expiry)){
			update_option('bizpress_subscriptions_expiry', $data->subscriptions_expiry);
		}
	}
	else {
		if(defined('WP_DEBUG') && WP_DEBUG == true){
			echo 'Error: ' . $request->get_error_message();
		}
		$data = null;
	}
    
    return $data;
}

/**
 * bizink content type
 */
function bizink_get_content_type( $curent_page_id ) {
	$content_type = [];
	$types = apply_filters( 'bizink-content-types', $content_type );
	foreach ( $types as $type ) {
    	$content_page_id   = cxbc_get_option( 'bizink-client_basic', $type['key'] );

    	if ( $curent_page_id == $content_page_id ) {
	        return $type['type'];
	    }
	}
	
}

/**
 * Master site url
 */
if( ! function_exists( 'bizink_get_master_site_url' ) ) :
function bizink_get_master_site_url() {
	return 'https://bizinkcontent.com/';
}
endif;

/**
 * API Authontication details
 *
 */
if( ! function_exists( 'bizink_url_authontication' ) ) :
function bizink_url_authontication()
{
	global $wp_version;
	return array(
		'timeout' => 10,
		'method' => 'GET',
		'sslverify' => false,
		'user-agent'  => 'WordPress/' . $wp_version . '; ' . home_url(),
		'httpversion' => '1.1',
		'headers' => array(
		  'Content-Type' => 'application/json',
		  'Authorization' => 'Bearer lumhWOCoZuYeMFGXfvHajcsuhMgCdLsRqsVQMbnD'
		)
	);
}
endif;

if( ! function_exists( 'bizink_update_views' ) ) :
	function bizink_update_views($data) {
		return; 
	}
endif;

function bizink_bizpress_display_pagnation($numPages = 2,$page = 1){
	ob_start();
	?>
	<div class="bizpress_pagnation" style="display: none;" data-page="<?php echo $page; ?>" data-totalpages="<?php echo $numPages; ?>">
		<div class="bizpress_pagnation_links">
			<a class="bizpress_pagnation_link bizpress_pagnation_link_prev" href="#prev" title="<?php _e('Previous','bizink-client'); ?>">&lt;</a>
			<?php
			for($i=0; $i < $numPages; $i++){
				if($numPages < 10){
				?>
					<a data-page="<?php echo ($i+1); ?>" title="<?= __('Page '.($i+1),'bizink-client') ?>" href="#page-<?= $i+1 ?>" class="bizpress_pagnation_link bizpress_pagnation_link_page <?= $i+1 == $page ? 'active' : '' ?>"><?= $i+1 ?></a>
				<?php
				}
				else{
					if($page == $i || $page == $i-1 || $page == $i+1 || $page == $i+2){
						?>
						<a data-page="<?php echo ($i+1); ?>" title="<?= __('Page '.($i+1),'bizink-client') ?>" href="#page-<?= $i+1 ?>" class="bizpress_pagnation_link bizpress_pagnation_link_page <?= $i+1 == $page ? 'active' : '' ?>"><?= $i+1 ?></a>
						<?php
					}
					elseif($page == $i-3 || $page == $i+3){
						?>
						<a data-page="<?php echo ($i+1); ?>" href="#page-<?= $i+1 ?>" class="bizpress_pagnation_link">...</a>
						<?php
					}
					elseif($i == $numPages-1){
						?>
						<a data-page="<?php echo ($i+1); ?>" title="<?= __('Page '.($i+1),'bizink-client') ?>" href="#page-<?= $i+1 ?>" class="bizpress_pagnation_link bizpress_pagnation_link_page <?= $i+1 == $page ? 'active' : '' ?>"><?= $i+1 ?></a>
						<?php
					}
				}
			}
			?>
			<a class="bizpress_pagnation_link bizpress_pagnation_link_next" href="#next" title="<?php _e('Next','bizink-client'); ?>">&gt;</a>
		</div>
	</div>
	<?php
	ob_end_flush();
}

if(!function_exists('bizpress_get_regons')){
	function bizpress_get_regons(){
		global $bizink_bace,$bizinkcontent_client;
		if(get_transient('bizpress_blog_regions')){
			return get_transient('bizpress_blog_regions');
		}
		$regionUrl = add_query_arg(array( '_fields' => 'id,name,slug','count' ),wp_slash($bizink_bace.'region'));
		$response = wp_remote_get($regionUrl,$bizinkcontent_client);
		$status = wp_remote_retrieve_response_code($response);
		if($status < 400){
			$body = json_decode(wp_remote_retrieve_body( $response ));
			set_transient('bizpress_blog_regions', $body, DAY_IN_SECONDS * 5);
			return $body;
		}
		else{
			return array(
				'status' => 'error',
				'type' => 'fetch_error_regions',
				'message' => 'There was an error fetching the regions.'
			);
		}
	}
}