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

	public function head() {
	}

	public function query_vars( $query_vars ) {
		$query_vars[] = 'topic';
		$query_vars[] = 'type';
		$query_vars[] = 'content';
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
		global $wp_query;
		if ( !$query->is_main_query() ){
			return;
		}
		$content = get_query_var( 'content');
		if ( $content ) {
			$data  = bizink_get_single_content( 'content', $content );
			$query->set('post_title',$data->post->post_title);
			$query->set('pagename',$data->post->post_name);
			$query->set('title',$data->post->post_title);
			$query->set('post_content',$data->post->post_content);
			$query->set('post_date',$data->post->post_date);
			$query->set('post_name',$data->post->post_name);
			$query->set('post_date_gmt',$data->post->post_date_gmt);
			$query->set('post_type',$data->post->post_type);
			$query->set('is_home',false);
			set_query_var('bizpress_data',$data);
		}
		return;
	}

	public function template_redirect($body) {
		global $wp, $wp_query;
		$type 		= get_query_var( 'type' );
		$topic 		= get_query_var( 'topic' );
		$content	= get_query_var( 'content'); // attachment
		
		$current_url = home_url( add_query_arg( array(), $wp->request ) );
		
	    if ( $topic ) {
			$wp_query->is_404 = false; 
	    	$main_slug 		= explode('topic', $current_url );
	    	$main_slug_id 	= url_to_postid( $main_slug[0] );
	    	$content_type   = bizink_get_content_type( $main_slug_id );
	        $data 			= bizink_get_content( $content_type, 'types', $topic );
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
	        $data 			= bizink_get_content( $content_type, 'posts', $type );
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