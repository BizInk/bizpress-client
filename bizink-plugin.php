<?php
namespace codexpert\Bizink_Client;

/**
 * Main class for the plugin
 * @package Plugin
 * @author codexpert <hello@codexpert.io>
 */
final class Plugin {
	
	public static $_instance;
	public $plugin;

	public function __construct() {
		self::include();
		self::define();
		self::hook();
	}

	/**
	 * Includes files
	 */
	public function include() {
		require_once( dirname( __FILE__ ) . '/vendor/autoload.php' );
	}

	/**
	 * Define variables and constants
	 */
	public function define() {
		// constants
		if(!defined('CXBPC')){
			define( 'CXBPC', __FILE__ );
		}
		define( 'CXBPC_DIR', dirname( CXBPC ) );
		define( 'CXBPC_DEBUG', true );

		// plugin data
		$this->plugin				= get_plugin_data( CXBPC );
		$this->plugin['basename']	= plugin_basename( CXBPC );
		$this->plugin['file']		= CXBPC;
		$this->plugin['server']		= apply_filters( 'bizink-client_server', 'https://my.codexpert.io' );
		$this->plugin['min_php']	= '7.4';
		$this->plugin['min_wp']		= '5.6';
		
	}

	/**
	 * Hooks
	 */
	public function hook() {

		if( is_admin() ) :

			/**
			 * Admin facing hooks
			 *
			 * To add an action, use $admin->action()
			 * To apply a filter, use $admin->filter()
			 */
			$admin = new Admin( $this->plugin );
			$admin->filter( "plugin_action_links_{$this->plugin['basename']}", 'action_links' );
			$admin->action( 'admin_notices', 'suscription_expiry_notice' );
			$admin->action('init','add_rewrite_rules');
			$admin->activate('add_rewrite_rules');
			$admin->activate('bizpress_activate_deactivate');
			$admin->deactivate('bizpress_activate_deactivate');
			//$admin->action('wp_ajax_bizpress_page','bizpress_create_page');
			$admin->action('init','bizpress_edits_cpt');
			
			/**
			 * Settings related hooks
			 *
			 * To add an action, use $settings->action()
			 * To apply a filter, use $settings->filter()
			 */
			$settings = new Settings( $this->plugin );
			$settings->action( 'plugins_loaded', 'init_menu' );

		else: // !is_admin() ?

			/**
			 * Front facing hooks
			 *
			 * To add an action, use $front->action()
			 * To apply a filter, use $front->filter()
			 */
			$front = new Front( $this->plugin );
			$front->action( 'wp_enqueue_scripts', 'enqueue_scripts' );
			$front->action( 'admin_bar_menu', 'add_admin_bar', 70 );
			$front->filter( 'query_vars', 'query_vars');

			$disableTemplateRedirect = false;
			if(defined('BIZPRESS_DISABLE_TEMPLATE_REDIRECT') && constant('BIZPRESS_DISABLE_TEMPLATE_REDIRECT') == true){
				$disableTemplateRedirect = true;
			}
			if(function_exists('luca') && $disableTemplateRedirect == false){
				$front->action( 'template_redirect', 'template_redirect',1);
			}
			
			$front->action( 'pre_get_posts', 'bizpress_pre_get_posts',1);
			$front->filter( 'the_title', 'the_title',1);
			$front->filter( 'the_content', 'the_content',1);
			$front->filter( 'the_post' ,'the_post',1);

			$front->filter( 'wpseo_title', 'the_title');
			$front->filter( 'wpseo_opengraph_title', 'the_title');
			$front->filter( 'wpseo_canonical', 'wpseo_canonical');
			$front->filter( 'wpseo_opengraph_url', 'wpseo_canonical');
			
			$front->action( 'body_class', 'body_class' );


			/**
			 * Anylitics
			 */
			$front->action('wp_head','bizpress_anylitics_head');

			/**
			 * Shortcode hooks
			 *
			 * To enable a shortcode, use $shortcode->register()
			 */
			$shortcode = new Shortcode( $this->plugin );
			// Bizink Shortcode
			$shortcode->register( 'bizink-content', 'bizink_content' );
			$shortcode->register( 'bizink-landing', 'bizink_landing' );

			// Bizpress Shortcode
			$shortcode->register( 'bizpress-content', 'bizink_content' );
			$shortcode->register( 'bizpress-landing', 'bizink_landing' );

		endif;
	}

	/**
	 * Cloning is forbidden.
	 */
	private function __clone() { }

	/**
	 * Unserializing instances of this class is forbidden.
	 */
	public function __wakeup() { }

	/**
	 * Instantiate the plugin
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
}

Plugin::instance();