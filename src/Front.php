<?php
/**
 * All public facing functions
 */
namespace codexpert\Bizink_Client;
use codexpert\product\Base;

/**
 * if accessed directly, exit.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @package Plugin
 * @subpackage Front
 * @author codexpert <hello@codexpert.io>
 */
class Front extends Base {

	public $plugin;

	/**
	 * Constructor function
	 */
	public function __construct( $plugin ) {
		$this->plugin	= $plugin;
		$this->slug		= $this->plugin['TextDomain'];
		$this->name		= $this->plugin['Name'];
		$this->version	= $this->plugin['Version'];
		$this->ncrypt   = ncrypt();
	}

	public function add_admin_bar( $admin_bar ) {
		if( !current_user_can( 'manage_options' ) ) return;

		$admin_bar->add_menu( [
			'id'    => $this->slug,
			'title' => $this->name,
			'href'  => add_query_arg( 'page', $this->slug, admin_url( 'admin.php' ) ),
			'meta'  => [
				'title' => $this->name,            
			],
		] );
	}
	
	/**
	 * Enqueue JavaScripts and stylesheets
	 */
	public function enqueue_scripts() {
		$min = defined( 'CXBPC_DEBUG' ) && CXBPC_DEBUG ? '' : '.min';
		wp_enqueue_style( $this->slug, plugins_url( "/assets/css/front{$min}.css", CXBPC ), '', $this->version, 'all' );
		wp_enqueue_script( $this->slug, plugins_url( "/assets/js/front{$min}.js", CXBPC ), array( 'jquery' ), $this->version, true );
		$localized = [
			'ajaxurl'	=> admin_url( 'admin-ajax.php' )
		];
		wp_localize_script( $this->slug, 'CXBPC', apply_filters( "{$this->slug}-localized", $localized ) );
	}

	public function query_vars( $query_vars ) {
		$query_vars[] = 'topic';
		$query_vars[] = 'type';
		//$query_vars[] = 'content';
		$query_vars[] = 'bizpress';
    	return $query_vars;
	}

	public function single_page_template($single_template) {

        $single_template = get_stylesheet_directory() . '/page.php';
		if(!file_exists($single_template)){
			$single_template = get_stylesheet_directory() . '/single.php';
			if(!file_exists($single_template)){
				$single_template = get_stylesheet_directory() . '/index.php';
			}
		}
	    return $single_template;
	}

	
	public function bizpress_pre_get_posts( $query ){
		if ( !$query->is_main_query() ){
			return;
		}

		$content = get_query_var( 'bizpress',false);
		$attachment = get_query_var( 'attachment',false);
		$business_page_id = cxbc_get_option( 'bizink-client_basic', 'business_content_page' );
		$xero_page_id = cxbc_get_option( 'bizink-client_basic', 'xero_content_page' );
		$quickbooks_page_id	= cxbc_get_option( 'bizink-client_basic', 'quickbooks_content_page' );
		$keydates_page_id = cxbc_get_option( 'bizink-client_basic', 'keydates_content_page' );
		$payroll_page_id = cxbc_get_option( 'bizink-client_basic', 'payroll_content_page' );
		$payroll_glossary_id = cxbc_get_option( 'bizink-client_basic', 'payroll_glossary_page' );

		if($attachment && !$content){
			if(!empty($business_page_id)){
				$post = get_post( $business_page_id );
			}
			if(!empty($payroll_page_id)){
				$post = get_post( $payroll_page_id );
			}
			if(!empty($payroll_glossary_id)){
				$post = get_post( $payroll_glossary_id );
			}
			if(!empty($xero_page_id)){
				$post = get_post( $xero_page_id );
			}
			if(!empty($quickbooks_page_id)){
				$post = get_post( $quickbooks_page_id );
			}
			if(!empty($keydates_page_id)){
				$post = get_post( $keydates_page_id );
			}
			$slug = $post->post_name;
		}

		if ( $content ) {
			$query->set( 'post_type', 'page' );
			$pageType = get_query_var('pagename','page');
			if($xero_page_id && $pageType == 'xero-resources'){
				$query->set( 'p', $xero_page_id );
				$query->set( 'page_id', $xero_page_id );
			}
			else if($quickbooks_page_id  && $pageType == 'quickbooks-resources'){
				$query->set( 'p', $quickbooks_page_id );
				$query->set( 'page_id', $quickbooks_page_id );
			}
			else if($business_page_id && $pageType = 'bizink-client-business'){
				$query->set( 'p', $business_page_id );
				$query->set( 'page_id', $business_page_id );
			}
			else if($keydates_page_id && $pageType == 'bizink-client-keydates'){
				$query->set( 'p', $keydates_page_id );
				$query->set( 'page_id', $keydates_page_id );
			}
			else if($payroll_page_id && $pageType = 'payroll-resources'){
				$query->set( 'p', $payroll_page_id );
				$query->set( 'page_id', $payroll_page_id );
			}
			else if($payroll_glossary_id && $pageType = 'payroll-glossary'){
				$query->set( 'p', $payroll_glossary_id );
				$query->set( 'page_id', $payroll_glossary_id );
			}
			else{
				return;
			}
			
			$data = get_transient("bizinkcontent_".md5($content));
			if(empty($data)){
				$data = bizink_get_single_content( 'content', $content );
				set_transient( "bizinkcontent_".md5($content), $data, DAY_IN_SECONDS );
			}
			
			$query->set('post_title',$data->post->post_title);
			$query->set('title',$data->post->post_title);
			$query->set('post_content',$data->post->post_content);
			$query->set('post_date',$data->post->post_date);
			$query->set('post_name',$data->post->post_name);
			$query->set('post_date_gmt',$data->post->post_date_gmt);
			set_query_var('bizpress_data',$data);
		}
	}

	public function the_post( $post ){
		global $wp, $wp_query;
		$current_url = home_url( add_query_arg( array(), $wp->request ) );
		$pagename = get_query_var('pagename');
		$content = get_query_var( 'bizpress');
		$type = get_query_var( 'type' );
		$topic = get_query_var( 'topic' );
		$calculator = get_query_var('calculator');
		$type = '';
		if($content){
			$d = $content;
			$type = 'content';
		}
		else if($topic){
			$d = $topic;
			$type = 'topic';
		}
		else if($type){
			$d = $type;
			$type = 'type';
		}
		else if($calculator){
			$d = $type;
			$type = 'calculator';
		}
		if( $type != '' && (
		$pagename == 'keydates' ||
		$pagename == 'xero-resources' || 
		$pagename == 'quickbooks-resources' || 
		$pagename == 'bizink-client-business' ||
		$pagename == 'payroll-resources' ||
		$pagename == 'payroll-glossary' ||
		$pagename == 'calculators') ){

			$main_slug 		= explode($type, $current_url );
			$main_slug_id 	= url_to_postid( $main_slug[0] );
			$content_type   = bizink_get_content_type( $main_slug_id );
			$data = get_transient("bizink'.$type.'_".md5($d));
			if(empty($data)){
				$data = bizink_get_content( $content_type, $type, $d );
				set_transient( "bizink'.$type.'_".md5($d), $data, DAY_IN_SECONDS );
			}

			if( isset( $data->subscriptions_expiry ) ) {
				update_option( '_cxbc_suscription_expiry', $data->subscriptions_expiry );
			}
			$post->post_title = $data->post->post_title;
			$post->post_content = $data->post->post_content;
			$post->post_type = 'page';
		}
		return $post;
	}

	public function the_title($post_title) {
		if ( is_singular() && in_the_loop() && is_main_query() ) {
			global $wp, $wp_query;
			$pagename = get_query_var('pagename',false);
			if($pagename == 'xero-resources' || 
			$pagename == 'keydates' ||
			$pagename == 'quickbooks-resources' || 
			$pagename == 'bizink-client-business' || 
			$pagename == 'payroll-resources' ||
			$pagename == 'payroll-glossary' ||
			$pagename == 'calculators'){

				$type = get_query_var( 'type' );
				$topic = get_query_var( 'topic' );
				$content = get_query_var( 'bizpress');
				$calculator = get_query_var('calculator');
				$current_url = home_url( add_query_arg( array(), $wp->request ) );
				if ( $topic || $type || $content || $calculator) {
					
					$type = '';
					if($content){
						$d = $content;
						$type = 'content';
					}
					else if($topic){
						$d = $topic;
						$type = 'topic';
					}
					else if($type){
						$d = $type;
						$type = 'type';
					}
					else if($calculator){
						$d = $type;
						$type = 'calculator';
					}
					$wp_query->is_404 = false; 
					$main_slug 		= explode($type, $current_url );
					$main_slug_id 	= url_to_postid( $main_slug[0] );
					$content_type   = bizink_get_content_type( $main_slug_id );
		
					$data = get_transient("bizink'.$type.'_".md5($d));
					if(empty($data)){
						$data = bizink_get_content( $content_type, $type, $d );
						set_transient( "bizink'.$type.'_".md5($d), $data, DAY_IN_SECONDS );
					}
					//$data = bizink_get_content( $content_type, $type, $d );
		
					if( isset( $data->subscriptions_expiry ) ) {
						update_option( '_cxbc_suscription_expiry', $data->subscriptions_expiry );
					}
					$post_title =  $data->post->post_title ? $data->post->post_title : $post_title;
				}
			}
		}
		return $post_title;
	}

	public function the_content($contentData){
		if ( is_singular() && in_the_loop() && is_main_query() ) {
			global $wp, $wp_query;
			$pagename = get_query_var('pagename');
			if
			($pagename == 'calculators' ||
			$pagename == 'keydates' ||
			$pagename == 'xero-resources' ||
			$pagename == 'quickbooks-resources' ||
			$pagename == 'myob-resources' ||
			$pagename == 'bizink-client-business' ||
			$pagename == 'payroll-resources' ||
			$pagename == 'payroll-glossary'
			){
				$type = get_query_var( 'type' );
				$topic = get_query_var( 'topic' );
				$content = get_query_var( 'bizpress');
				$calculator = get_query_var('calculator');
				$current_url = home_url( add_query_arg( array(), $wp->request ) );
				if ( $topic || $type || $content || $calculator) {
					$type = '';
					if($topic){
						$d = $topic;
						$type = 'topic';
					}
					else if($type){
						$d = $type;
						$type = 'type';
					}
					else if($content){
						$d = $content;
						$type = 'content';
					}
					else if($calculator){
						$d = $type;
						$type = 'calculator';
					}
					$wp_query->is_404 = false; 
					$main_slug 		= explode($type, $current_url );
					$main_slug_id 	= url_to_postid( $main_slug[0] );
					$content_type   = bizink_get_content_type( $main_slug_id );

					$data = get_transient("bizink'.$type.'_".md5($d));
					if(empty($data)){
						$data = bizink_get_content( $content_type, $type, $d );
						set_transient( "bizink'.$type.'_".md5($d), $data, DAY_IN_SECONDS );
					}
					//$data = bizink_get_content( $content_type, $type, $d );

					if( isset( $data->subscriptions_expiry ) ) {
						update_option( '_cxbc_suscription_expiry', $data->subscriptions_expiry );
					}
					$contentData =  $data->post->post_content ? $data->post->post_content : $contentData;
				}
			}
		}
		return $contentData;
	}

	public function template_redirect($body) {
		global $wp, $wp_query;
		$type 		= get_query_var( 'type' );
		$topic 		= get_query_var( 'topic' );
		$content	= get_query_var( 'bizpress');   // attachment
		$calculator = get_query_var('calculator');
		$pagename = get_query_var('pagename');

		$current_url = home_url( add_query_arg( array(), $wp->request ) );
		
		if( $calculator ){
			$wp_query->is_404 = false; 
	    	$main_slug 		= explode('topic', $current_url );
	    	$main_slug_id 	= url_to_postid( $main_slug[0] );
	    	$content_type   = bizink_get_content_type( $main_slug_id );

			$data = get_transient("bizinktopic_".md5($topic));
			if(empty($data)){
				$data = bizink_get_content( $content_type, 'post', $topic );
				set_transient( "bizinktopic_".md5($topic), $data, DAY_IN_SECONDS );
			}
	        //$data = bizink_get_content( $content_type, 'types', $topic );

	        if( isset( $data->subscriptions_expiry ) ) {
	        	update_option( '_cxbc_suscription_expiry', $data->subscriptions_expiry );
	        }
	        echo cxbc_get_template( 'posts', 'views', [ 'response' => $data ] );
	        die;
		}

	    if ( $topic ) {
			$wp_query->is_404 = false; 
	    	$main_slug 		= explode('topic', $current_url );
	    	$main_slug_id 	= url_to_postid( $main_slug[0] );
	    	$content_type   = bizink_get_content_type( $main_slug_id );

			$data = get_transient("bizinktopic_".md5($topic));
			if(empty($data)){
				$data = bizink_get_content( $content_type, 'types', $topic );
				set_transient( "bizinktopic_".md5($topic), $data, DAY_IN_SECONDS );
			}
	        //$data = bizink_get_content( $content_type, 'types', $topic );

	        if( isset( $data->subscriptions_expiry ) ) {
	        	update_option( '_cxbc_suscription_expiry', $data->subscriptions_expiry );
	        }
	        echo cxbc_get_template( 'types', 'views', [ 'response' => $data ] );
	        die;
	    }
		
		if ( $type ) {
			$wp_query->is_404 = false; 
	    	$main_slug 		= explode('type', $current_url );
	    	$main_slug_id 	= url_to_postid( $main_slug[0] );
	    	$content_type   = bizink_get_content_type( $main_slug_id );

			$data = get_transient("bizinktype_".md5($type));
			if(empty($data)){
				$data = bizink_get_content( $content_type, 'type', $type );
				set_transient( "bizinktype_".md5($type), $data, DAY_IN_SECONDS );
			}
	        //$data = bizink_get_content( $content_type, 'type', $type );

	        if( isset( $data->subscriptions_expiry ) ) {
	        	update_option( '_cxbc_suscription_expiry', $data->subscriptions_expiry );
	        }
	        echo cxbc_get_template( 'posts', 'views', [ 'response' => $data ] );
	        die;
	    }
		
		if ( $content ) {
			$main_slug 		= explode('type', $current_url );
	    	$main_slug_id 	= url_to_postid( $main_slug[0] );
			$content_type   = bizink_get_content_type( $main_slug_id );
			
			$d = get_query_var('bizpress_data');
			if($d){
				$data = $d;
			}
			else{
				$data = bizink_get_single_content( 'content', $content );
			}

	    	add_filter('body_class', function( $classes ){
	    		$classes[] = 'bizink-page';
	    		return $classes;
	    	});
	        // bizink_update_views($data);
	        if( isset( $data->subscriptions_expiry ) ) {
	        	update_option( '_cxbc_suscription_expiry', $data->subscriptions_expiry );
	        }
			if($pagename == 'keydates'){
				echo cxbc_get_template( 'item', 'views', [ 'response' => $data ] );
			}
			else{
				echo cxbc_get_template( 'content', 'views', [ 'response' => $data ] );
			}
	        die;
	    }
	    return $body;
	}

	public function body_class( $classes ){

		global $post;
		if(isset($post) && isset($post->post_content)){
			if ( has_shortcode( $post->post_content, 'bizink-content' ) ) {
				$classes[] = 'bizink-page';
			}
		}
		return $classes;
	}
}