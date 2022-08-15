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
		$query_vars[] = 'content';
		$query_vars[] = 'topic';
		$query_vars[] = 'type';
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

	public function template_redirect($body) {
		$type 		= get_query_var( 'type' );
		$topic 		= get_query_var( 'topic' );
		$content	= get_query_var( 'content');
		global $wp, $wp_query;
		$current_url 	= home_url( add_query_arg( array(), $wp->request ) );
		
	    if ( $topic ) {

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
		else if ( $type ) {

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
		else if ( $content ) {
	    	add_filter('body_class', function( $classes ){
	    		$classes[] = 'bizink-page';
	    		return $classes;
	    	});

	    	$data  = bizink_get_single_content( 'content', $content );
	        bizink_update_views($data);
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
		if(isset($post->post_content)){
			if ( has_shortcode( $post->post_content, 'bizink-content' ) ) {
				$classes[] = 'bizink-page';
			}
		}
		return $classes;
	}
}