<?php
/**
 * Plugin Name: BizPress
 * Description: Bizink content for accountants.
 * Plugin URI: https://bizinkonline.com
 * Author: Bizink
                    * Author URI: https://bizinkonline.com
 * Version: 1.0
 * Text Domain: bizink-client
 * Domain Path: /languages
 */

namespace codexpert\Bizink_Client;

/**
 * if accessed directly, exit.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main class for the plugin
 * @package Plugin
 * @author codexpert <hello@codexpert.io>
 */
final class Plugin {
	
	public static $_instance;

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
		define( 'CXBPC', __FILE__ );
		define( 'CXBPC_DIR', dirname( CXBPC ) );
		define( 'CXBPC_DEBUG', true );

		// plugin data
		$this->plugin				= get_plugin_data( CXBPC );
		$this->plugin['basename']	= plugin_basename( CXBPC );
		$this->plugin['file']		= CXBPC;
		$this->plugin['server']		= apply_filters( 'bizink-client_server', 'https://my.codexpert.io' );
		$this->plugin['min_php']	= '5.6';
		$this->plugin['min_wp']		= '4.0';
		$this->plugin['depends']	= [];
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
			$admin->filter( 'generate_rewrite_rules', 'rewrite_rule' );
			$admin->action( 'admin_notices', 'suscription_expiry_notice' );

			/**
			 * Settings related hooks
			 *
			 * To add an action, use $settings->action()
			 * To apply a filter, use $settings->filter()
			 */
			$settings = new Settings( $this->plugin );
			$settings->action( 'plugins_loaded', 'init_menu' );

		else : // !is_admin() ?

			/**
			 * Front facing hooks
			 *
			 * To add an action, use $front->action()
			 * To apply a filter, use $front->filter()
			 */
			$front = new Front( $this->plugin );
			$front->action( 'init', 'custom_rewrite_basic');
			$front->action( 'wp_enqueue_scripts', 'enqueue_scripts' );
			$front->action( 'admin_bar_menu', 'add_admin_bar', 70 );
			$front->action( 'wp_head', 'head' );
			$front->filter( 'query_vars', 'query_vars' );
			$front->action( 'template_redirect', 'template_redirect' );
			$front->action( 'body_class', 'body_class' );

			/**
			 * Shortcode hooks
			 *
			 * To enable a shortcode, use $shortcode->register()
			 */
			$shortcode = new Shortcode( $this->plugin );
			$shortcode->register( 'bizink-content', 'bizink_content' );
			$shortcode->register( 'bizink-landing', 'bizink_landing' );

		endif;
	}

	/**
	 * Cloning is forbidden.
	 */
	private function __clone() { }

	/**
	 * Unserializing instances of this class is forbidden.
	 */
	private function __wakeup() { }

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