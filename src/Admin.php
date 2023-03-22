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

	function bizpress_activate_deactivate(){
		flush_rewrite_rules();
	}

	public function bizpress_create_page(){
		check_ajax_referer('bizpress_page');
		if(empty($_REQUEST['page_details'])){
			return json_encode(array('status' => false, 'errors' => ['no_page_details']));
			die();
		}
		$page_id = wp_insert_post(json_decode($_REQUEST['page_details']));
		$error = is_wp_error($page_id);
		if($error){
			return json_encode(array('status' => false, 'errors' => $error['errors'], 'error_data' => $error['error_data']));
		}
		else{
			return json_encode(array('status' => true, 'page_id' => $page_id, 'page_details' => $_REQUEST['page_details']));
		}
		print_r($_REQUEST);
		die();
	}

	public function add_rewrite_rules(){

		global $wp;
		$wp->add_query_var( 'bizpress' );
		$wp->add_query_var( 'topic' );
		$wp->add_query_var( 'type' );

		add_rewrite_tag('%bizpress%', '([^&]+)', 'bizpress=');
		add_rewrite_tag('%topic%', '([^&]+)', 'topic=');
		add_rewrite_tag('%type%', '([^&]+)', 'type=');

		$business_page_id = cxbc_get_option( 'bizink-client_basic', 'business_content_page' );
		if(!empty($business_page_id) && $business_page_id != ''){
			$business_post = get_post( $business_page_id );
			if(!empty($business_post)){
				$business_slug = $business_post->post_name;
				add_rewrite_tag('%'.$business_slug.'%', '([^&]+)', 'bizpress=');
				add_rewrite_rule("^".$business_slug."/([a-z0-9-]+)[/]?$",'index.php?pagename=bizink-client-business&bizpress=$matches[1]','top');
				add_rewrite_rule("^".$business_slug."/topic/([a-z0-9-]+)[/]?$",'index.php?pagename=bizink-client-business&topic=$matches[1]','top');
				add_rewrite_rule("^".$business_slug."/type/([a-z0-9-]+)[/]?$" ,'index.php?pagename=bizink-client-business&type=$matches[1]','top');
			}
		}
		
		$xero_page_id = cxbc_get_option( 'bizink-client_basic', 'xero_content_page' );
		if(!empty($xero_page_id) && $xero_page_id != ''){
			$xero_post = get_post( $xero_page_id );
			if(!empty($xero_post)){
				$xero_slug = $xero_post->post_name;
				add_rewrite_tag('%'.$xero_slug.'%', '([^&]+)', 'bizpress=');
				add_rewrite_rule("^".$xero_slug."/([a-z0-9-]+)[/]?$",'index.php?pagename=xero-resources&bizpress=$matches[1]','top');
				add_rewrite_rule("^".$xero_slug."/topic/([a-z0-9-]+)[/]?$",'index.php?pagename=xero-resources&topic=$matches[1]','top');
				add_rewrite_rule("^".$xero_slug."/type/([a-z0-9-]+)[/]?$" ,'index.php?pagename=xero-resources&type=$matches[1]','top');
			}
		}

		$quickbooks_page_id	= cxbc_get_option( 'bizink-client_basic', 'quickbooks_content_page' );
		if(!empty($quickbooks_page_id) && $quickbooks_page_id != ''){	
			$quickbooks_post = get_post( $quickbooks_page_id );
			if(!empty($quickbooks_post)){
				$quickbooks_slug = $quickbooks_post->post_name;
				add_rewrite_tag('%'.$quickbooks_slug.'%', '([^&]+)', 'bizpress=');
				add_rewrite_rule("^".$quickbooks_slug."/([a-z0-9-]+)[/]?$",'index.php?pagename=quickbooks-resources&bizpress=$matches[1]','top');
				add_rewrite_rule("^".$quickbooks_slug."/topic/([a-z0-9-]+)[/]?$",'index.php?pagename=quickbooks-resources&topic=$matches[1]','top');
				add_rewrite_rule("^".$quickbooks_slug."/type/([a-z0-9-]+)[/]?$" ,'index.php?pagename=quickbooks-resources&type=$matches[1]','top');
			}
			
		}

		$keydates_page_id = cxbc_get_option( 'bizink-client_basic', 'keydates_content_page' );
		if(!empty($keydates_page_id) && $keydates_page_id != ''){
			$keydates_post = get_post( $keydates_page_id ); 
			if(!empty($keydates_post)){
				$keydates_slug = $keydates_post->post_name;
				add_rewrite_tag('%'.$keydates_slug.'%', '([^&]+)', 'bizpress=');
				add_rewrite_rule("^".$keydates_slug."/([a-z0-9-]+)[/]?$",'index.php?pagename=keydates&bizpress=$matches[1]','top');
				add_rewrite_rule("^".$keydates_slug."/topic/([a-z0-9-]+)[/]?$",'index.php?pagename=keydates&topic=$matches[1]','top');
				add_rewrite_rule("^".$keydates_slug."/type/([a-z0-9-]+)[/]?$" ,'index.php?pagename=keydates&type=$matches[1]','top');
			}
		}
	}
	
	public function suscription_expiry_notice() {
		$notice 	= get_option( '_cxbc_suscription_expiry' );
		if ( $notice == '' ) return;
		$message 	= __( 'Your Bizink suscription will be expired ', 'bizink-client' ) . strtolower( $notice );
		echo "<div class='notice notice-error'><p>{$message}</p></div>";
	}
}