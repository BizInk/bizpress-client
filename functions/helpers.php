<?php
if( !function_exists( 'get_plugin_data' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}

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
	//print_r($posts);
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

if( ! function_exists( 'cxbc_get_option' ) ) :
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

if( ! function_exists( 'bizink_get_content' ) ) :
function bizink_get_content( $post_type, $api_endpoint, $slug = '' ) {
    $options = get_option( 'bizink-client_basic' );
	if(empty($options['content_region'])){
		$options['content_region'] = 'au';
	}
	if(empty($options['user_email'])){
		$options['user_email'] = '';
	}
	if(empty($options['user_password'])){
		$options['user_password'] = '';
	}

    $paged          = ( get_query_var( 'paged' ) ) ? absint( get_query_var( 'paged' ) ) : 1;
    $base_url = bizink_get_master_site_url();
    $content_region = $options['content_region'];
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
	elseif ( 'accounting-terms' == $post_type ) {
		$taxonomy_topics = 'region';
	}
	elseif ( 'business-terms' == $post_type ) {
		$taxonomy_topics = 'region';
	}

    $term = isset( $_GET[ $taxonomy_topics ] ) ? $_GET[ $taxonomy_topics ] : '';

	//$country = strpos($post_type, 'keydates') !== false ? apply_filters( 'bizink-keydates-country', 'country' ) : 'AU';
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
		case 'au':
		default:
			$country = 'AU';
			break;			
	}
	if(apply_filters( 'bizink-keydates-country', 'country' )){
		$country = apply_filters( 'bizink-keydates-country', 'country' );
	}

    $url = add_query_arg( [ 
        'rest_route'    => "/bizink-publisher/v1.1/{$api_endpoint}",
        'per_page'      => -1,
        'email'         => $options['user_email'],
        'password'      => ncrypt()->encrypt( $options['user_password'] ),
        'paged'         => $paged,
        'post_type'     => $post_type,
        'slug'         	=> $slug,
        'term'         	=> $term,
		'country'		=> $country,
        'region'		=> $content_region,
        'luca'		=> function_exists('luca') ? true : false
    ], wp_slash( $base_url ) );

    $args = array(
      'timeout' => 300,
      'httpversion' => '1.1',
      'headers' => array(
        'Content-Type' => 'application/json',
        'Authorization' => 'Bearer tkAVTdsSQGyKJifrsoyeeuEQuDQqqkbRjgRqQOxO')
  	);

    $request    = wp_remote_get( $url, $args );
    $body       = wp_remote_retrieve_body( $request );
    $data       = json_decode( $body);
	if(defined('WP_DEBUG') && WP_DEBUG == true){
		if(!empty($data->data)){
			if($data->data->status > 399){
				echo '<p><b>Bizpress Error:</b> '.$data->code.' - '.$data->data->status.'</p>';
				echo '<p><b>Message:</b> '.$data->message.'</p>';
			}
		}
	}
    return $data;
}
endif;

if( ! function_exists( 'bizink_get_single_content' ) ) :
function bizink_get_single_content( $api_endpoint, $slug = '' ) {
    $key            = 'bizink-client_basic';
    $options        = get_option( $key );
    $paged          = ( get_query_var( 'paged' ) ) ? absint( get_query_var( 'paged' ) ) : 1;
    $base_url 		= bizink_get_master_site_url();
	//$options = get_option( 'bizink-client_basic' );
	if(empty($options['content_region'])){
		$options['content_region'] = 'au';
	}
	if(empty($options['user_email'])){
		$options['user_email'] = '';
	}
	if(empty($options['user_password'])){
		$options['user_password'] = '';
	}
    //$keydate_country = apply_filters( 'bizink-keydates-country', 'country' );
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
		case 'au':
		default:
			$keydate_country = 'AU';
			break;			
	}
	if(apply_filters( 'bizink-keydates-country', 'country' )){
		$keydate_country = apply_filters( 'bizink-keydates-country', 'country' );
	}


	//'per_page'      => -1,
    $url = add_query_arg( [ 
        'rest_route'    => "/bizink-publisher/v1.1/{$api_endpoint}",
        'email'         => $options['user_email'],
        'password'      => ncrypt()->encrypt( $options['user_password'] ),
        'paged'         => $paged,
        'slug'         	=> $slug,
        'kd_region' 	=> strtolower($keydate_country),
        'luca'		=> function_exists('luca') ? true : false
    ], wp_slash( $base_url ) );

    $args = array(
      'timeout' => 120,
      'httpversion' => '1.1',
      'headers' => array(
        	'Content-Type' => 'application/json',
        	'Authorization' => 'Bearer tkAVTdsSQGyKJifrsoyeeuEQuDQqqkbRjgRqQOxO'
		)
  	);

    $request    = wp_remote_get( $url, $args );
    $body       = wp_remote_retrieve_body( $request );
    $data       = json_decode( $body );
	if(defined('WP_DEBUG') && WP_DEBUG == true){
		if(!empty($data->data)){
			if($data->data->status > 399){
				echo '<p><b>Bizpress Error:</b> '.$data->code.' - '.$data->data->status.'</p>';
				echo '<p><b>Message:</b> '.$data->message.'</p>';
			}
		}
	}
    return $data;
}
endif;

/**
 * bizink content type
 *
 * @return type
 * @author akash <alimranakash.bd@gmail.com>
 */
if( ! function_exists( 'bizink_get_content_type' ) ) :
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
endif;

/**
 * Master site url
 *
 * @return url
 * @author akash <alimranakash.bd@gmail.com>
 */
if( ! function_exists( 'bizink_get_master_site_url' ) ) :
function bizink_get_master_site_url() {
	return 'https://bizinkcontent.com/';
}
endif;

/**
 * Reporting API
 *
 * @return url
 * @author ace <ace@bizinkonline.com>
 */

if( ! function_exists( 'bizink_update_views' ) ) :
function bizink_update_views($data) {

	$url = 'http://contentreport.bizinkonline.com/api/update_views.php';

	if(!empty($data->post)){
		$country = $data->post->post_type == 'business-lifecycle' ? $data->region : 'N/A';
		$topics = $data->post->post_type == 'business-lifecycle' ? $data->topic : 'N/A';
		$types = $data->post->post_type == 'business-lifecycle' ? $data->type : 'N/A';
	}
	else{
		$country = 'N/A';
		$topics = 'N/A';
		$types = 'N/A';
	}
	

	$data = array(
		'title' => $data->post->post_title,
		'url' => $data->post->guid,
		'type' => $data->post->post_type,
		'country' => $country,
		'topics' => $topics,
		'types' => $types
	);

	// use key 'http' even if you send the request to https://...
	$options = array(
	    'http' => array(
	        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
	        'method'  => 'POST',
	        'content' => http_build_query($data)
	    )
	);
	$context  = stream_context_create($options);
	$result = file_get_contents($url, false, $context);
}
endif;


/**
 * API Authontication details
 *
 * @return array
 * @author ace <ace@bizinkonline.com>
 */
if( ! function_exists( 'bizink_url_authontication' ) ) :
function bizink_url_authontication()
{
	return array(
		'timeout' => 120,
		'httpversion' => '1.1',
		'headers' => array(
		  'Content-Type' => 'application/json',
		  'Authorization' => 'Bearer tkAVTdsSQGyKJifrsoyeeuEQuDQqqkbRjgRqQOxO'
		)
	);
}
endif;