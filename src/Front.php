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

		if($attachment && !$content){
			if(!empty($business_page_id)){
				$post = get_post( $business_page_id );
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
			// echo $attachment;
		}

		if ( $content ) {
			$query->set( 'post_type', 'page' );
			if($xero_page_id){
				$query->set( 'p', $xero_page_id );
				$query->set( 'page_id', $xero_page_id );
			}
			else if($quickbooks_page_id){
				$query->set( 'p', $quickbooks_page_id );
				$query->set( 'page_id', $quickbooks_page_id );
			}
			else if($business_page_id){
				$query->set( 'p', $business_page_id );
				$query->set( 'page_id', $business_page_id );
			}
			else if($keydates_page_id){
				$query->set( 'p', $keydates_page_id );
				$query->set( 'page_id', $keydates_page_id );
			}
			
			$data = get_transient("bizink_".md5($content));
			if(empty($data)){
				$data = bizink_get_single_content( 'content', $content );
				set_transient( "bizink_".md5($content), $data, DAY_IN_SECONDS );
			}
			
			$query->set('post_title',$data->post->post_title);
			$query->set('title',$data->post->post_title);
			$query->set('post_content',$data->post->post_content);
			$query->set('post_date',$data->post->post_date);
			$query->set('post_name',$data->post->post_name);
			$query->set('post_date_gmt',$data->post->post_date_gmt);
			set_query_var('bizpress_data',$data);
		}
		return;
	}

	public function template_redirect($body) {
		global $wp, $wp_query;
		$type 		= get_query_var( 'type' );
		$topic 		= get_query_var( 'topic' );
		$content	= get_query_var( 'bizpress'); // attachment
		
		$current_url = home_url( add_query_arg( array(), $wp->request ) );
		
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
	        echo cxbc_get_template( 'content', 'views', [ 'response' => $data ] );
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