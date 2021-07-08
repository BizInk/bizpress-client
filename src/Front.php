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

	public function template_redirect() {
		$type 		= get_query_var( 'type' );
		$topic 		= get_query_var( 'topic' );
		$content	= get_query_var( 'content');

		global $wp, $wp_query;
		$current_url 	= home_url( add_query_arg( array(), $wp->request ) );

	    if ( $topic ) {
	    	
	    	if(!function_exists('luca')) {
	    		get_header();
	    	}
	    	else{
	    		get_template_part('templates/head');
		    	do_action('luca/theme/before');
		    	do_action('get_header');
		    	echo '<div class="pageWrap">';
		    	do_action('luca/theme/content/before');
		    	echo '<main class="main"><div class="section"><div class="container">';
	    	}

	    	echo '<main id="main" role="main">';
	    	echo '<div class="container">';

	    	$main_slug 		= explode('topic', $current_url );

	    	$main_slug_id 	= url_to_postid( $main_slug[0] );

	    	$content_type   = bizink_get_content_type( $main_slug_id );
	        $data 			= bizink_get_content( $content_type, 'types', $topic );


	        if( isset( $data->subscriptions_expiry ) ) {
	        	update_option( '_cxbc_suscription_expiry', $data->subscriptions_expiry );
	        }
	        
	        echo cxbc_get_template( 'types', 'views', [ 'response' => $data ] );

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
	    	
	        die;
	    }
 
	    if ( $type ) {
	    	
	    	if(!function_exists('luca')) {
	    		get_header();
	    	}
	    	else{
	    		get_template_part('templates/head');
		    	do_action('luca/theme/before');
		    	do_action('get_header');
		    	echo '<div class="pageWrap">';
		    	do_action('luca/theme/content/before');
		    	echo '<main class="main"><div class="section"><div class="container">';
	    	}

	    	echo '<main id="main" role="main">';
	    	echo '<div class="container">';

	    	$main_slug 		= explode('type', $current_url );
	    	$main_slug_id 	= url_to_postid( $main_slug[0] );
	    	$content_type   = bizink_get_content_type( $main_slug_id );
	        $data 			= bizink_get_content( $content_type, 'posts', $type );

	        if( isset( $data->subscriptions_expiry ) ) {
	        	update_option( '_cxbc_suscription_expiry', $data->subscriptions_expiry );
	        }

	        echo cxbc_get_template( 'posts', 'views', [ 'response' => $data ] );

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

	        die;
	    }

	    if ( $content ) {
	    	if(!function_exists('luca')) {
	    		get_header();
	    	}
	    	else{
	    		get_template_part('templates/head');
		    	do_action('luca/theme/before');
		    	do_action('get_header');
		    	echo '<div class="pageWrap">';
		    	do_action('luca/theme/content/before');
		    	echo '<main class="main"><div class="section"><div class="container">';
	    	}

	    	echo '<main id="main" role="main">';
	    	echo '<div class="container">';

	        $data 			= bizink_get_single_content( 'content', $content );

	        bizink_update_views($data);

	        if( isset( $data->subscriptions_expiry ) ) {
	        	update_option( '_cxbc_suscription_expiry', $data->subscriptions_expiry );
	        }

	        echo cxbc_get_template( 'content', 'views', [ 'response' => $data ] );

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
	    	
	        die;
	    }
	}
}