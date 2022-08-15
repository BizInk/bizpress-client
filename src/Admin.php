<?php
/**
 * All admin facing functions
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
 * @subpackage Admin
 * @author codexpert <hello@codexpert.io>
 */
class Admin extends Base {

	public $plugin;

	/**
	 * Constructor function
	 */
	public function __construct( $plugin ) {
		$this->plugin	= $plugin;
		$this->slug		= $this->plugin['TextDomain'];
		$this->name		= $this->plugin['Name'];
		$this->server	= $this->plugin['server'];
		$this->version	= $this->plugin['Version'];
	}

	/**
	 * Internationalization
	 */
	public function i18n() {
		load_plugin_textdomain( 'bizink-client', false, CXBPC_DIR . '/languages/' );
	}

	public function action_links( $links ) {
		$this->admin_url = admin_url( 'admin.php' );

		$new_links = [
			'settings'	=> sprintf( '<a href="%1$s">' . __( 'Settings', 'bizink-client' ) . '</a>', add_query_arg( 'page', $this->slug, $this->admin_url ) )
		];
		
		return array_merge( $new_links, $links );
	}

	public function add_rewrite_rules(){
		$business_page_id	= cxbc_get_option( 'bizink-client_basic', 'business_content_page' );
		if(!empty($business_page_id)){
			$business_post 		= get_post( $business_page_id ); 
			$business_slug 		= $business_post->post_name;
			add_rewrite_rule("{$business_slug}/([a-z0-9-]+)[/]?$",'index.php?content=$matches[1]','top');
			add_rewrite_rule("{$business_slug}/topic/([a-z0-9-]+)[/]?$",'index.php?topic=$matches[1]','top');
			add_rewrite_rule("{$business_slug}/type/([a-z0-9-]+)[/]?$" ,'index.php?type=$matches[1]','top');
		}
		
		$xero_page_id	= cxbc_get_option( 'bizink-client_basic', 'xero_content_page' );
		if(!empty($xero_page_id)){	
			$xero_post 		= get_post( $xero_page_id ); 
			$xero_slug 		= $xero_post->post_name;
			add_rewrite_rule("{$xero_slug}/([a-z0-9-]+)[/]?$",'index.php?content=$matches[1]','top');
			add_rewrite_rule("{$xero_slug}/topic/([a-z0-9-]+)[/]?$",'index.php?topic=$matches[1]','top');
			add_rewrite_rule("{$xero_slug}/type/([a-z0-9-]+)[/]?$" ,'index.php?type=$matches[1]','top');
		}

		$keydates_page_id	= cxbc_get_option( 'bizink-client_basic', 'keydates_content_page' );
		if(!empty($keydates_page_id)){
			$keydates_post 		= get_post( $keydates_page_id ); 
			$keydates_slug 		= $keydates_post->post_name;
			add_rewrite_rule("{$keydates_slug}/([a-z0-9-]+)[/]?$",'index.php?content=$matches[1]','top');
			add_rewrite_rule("{$keydates_slug}/topic/([a-z0-9-]+)[/]?$",'index.php?topic=$matches[1]','top');
			add_rewrite_rule("{$keydates_slug}/type/([a-z0-9-]+)[/]?$" ,'index.php?type=$matches[1]','top');
		}
		
	}

	public function rewrite_rule( $wp_rewrite ) {
		$business_page_id	= cxbc_get_option( 'bizink-client_basic', 'business_content_page' );
		if(!empty($business_page_id)){
			$business_post 		= get_post( $business_page_id ); 
			$business_slug 		= $business_post->post_name;
			$wp_rewrite->rules = array_merge(
				[
					"{$business_slug}/([a-z0-9-]+)[/]?$" 		=> 'index.php?content=$matches[1]',
					"{$business_slug}/topic/([a-z0-9-]+)[/]?$" => 'index.php?topic=$matches[1]',
					"{$business_slug}/type/([a-z0-9-]+)[/]?$" 	=> 'index.php?type=$matches[1]',
				],
				$wp_rewrite->rules
			);
		}

		$xero_page_id	= cxbc_get_option( 'bizink-client_basic', 'xero_content_page' );
		if(!empty($xero_page_id)){	
			$xero_post 		= get_post( $xero_page_id ); 
			$xero_slug 		= $xero_post->post_name;
			$wp_rewrite->rules = array_merge(
				[
					"{$xero_slug}/([a-z0-9-]+)[/]?$" 		=> 'index.php?content=$matches[1]',
					"{$xero_slug}/topic/([a-z0-9-]+)[/]?$" => 'index.php?topic=$matches[1]&cpt=$matches[1]',
					"{$xero_slug}/type/([a-z0-9-]+)[/]?$" 	=> 'index.php?type=$matches[1]',
				],
				$wp_rewrite->rules
			);
		}
		
		$keydates_page_id	= cxbc_get_option( 'bizink-client_basic', 'keydates_content_page' );
		if(!empty($keydates_page_id)){
			$keydates_post 		= get_post( $keydates_page_id ); 
			$keydates_slug 		= $keydates_post->post_name;
			$wp_rewrite->rules = array_merge(
				[
					"{$keydates_slug}/([a-z0-9-]+)[/]?$" 		=> 'index.php?content=$matches[1]',
					"{$keydates_slug}/topic/([a-z0-9-]+)[/]?$" => 'index.php?topic=$matches[1]&cpt=$matches[1]',
					"{$keydates_slug}/type/([a-z0-9-]+)[/]?$" 	=> 'index.php?type=$matches[1]',
				],
				$wp_rewrite->rules
			);
		}
		
	}

	public function suscription_expiry_notice() {
		$notice 	= get_option( '_cxbc_suscription_expiry' );
		if ( $notice == '' ) return;
		$message 	= __( 'Your Bizink suscription will be expired ', 'bizink-client' ) . strtolower( $notice );
		echo "<div class='notice notice-error'><p>{$message}</p></div>";
	}
}