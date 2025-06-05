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
	public $slug;
	public $name;
	public $server;
	public $version;
	public $admin_url;

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

	public function add_rewrite_rules(){

		global $wp;
		$wp->add_query_var( 'bizpress' );
		$wp->add_query_var( 'topic' );
		$wp->add_query_var( 'type' );

		add_rewrite_tag('%bizpress%', '([^&]+)', 'bizpress=');
		add_rewrite_tag('%topic%', '([^&]+)', 'topic=');
		add_rewrite_tag('%type%', '([^&]+)', 'type=');

		
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
	}
	
	public function suscription_expiry_notice() {
		$notice = get_option( 'bizpress_subscriptions_expiry' ); //_cxbc_suscription_expiry
		if ( $notice == '' ) return;
		
		$compareYear = date( 'Y' );
		$compareMonth = date( 'm' );
		$compareDay = date( 'd' );
		if( $compareMonth > 1){
			$compareMonth = $compareMonth - 1;
		}
		else{
			$compareMonth = 12;
			$compareYear = $compareYear - 1;
		}
		$compareDate = strtotime($compareYear.'-'.$compareMonth.'-'.$compareDay);
		if( $compareDate > strtotime($notice) ) return;
		$message = __( 'Your BizPress Suscription will renew on', 'bizink-client' ) .' '. date( get_option('date_format') ,strtotime($notice));
		echo "<div class='notice notice-info is-dismissible'><p>{$message}</p></div>";
	}

	public function bizpress_edits_cpt() {
		$labels = array(
			'name'                  => _x( 'BizPress Resources', 'Post Type General Name', 'bizink-client' ),
			'singular_name'         => _x( 'BizPress Resource', 'Post Type Singular Name', 'bizink-client' ),
			'menu_name'             => __( 'Post Types', 'bizink-client' ),
			'name_admin_bar'        => __( 'BizPress Resource', 'bizink-client' ),
			'archives'              => __( 'BizPress Resource Archives', 'bizink-client' ),
			'attributes'            => __( 'BizPress Resource Attributes', 'bizink-client' ),
			'parent_item_colon'     => __( 'Parent Resource:', 'bizink-client' ),
			'all_items'             => __( 'All BizPress Resources', 'bizink-client' ),
			'add_new_item'          => __( 'Add New BizPress Resource', 'bizink-client' ),
			'add_new'               => __( 'Add New', 'bizink-client' ),
			'new_item'              => __( 'New Resource', 'bizink-client' ),
			'edit_item'             => __( 'Edit Resource', 'bizink-client' ),
			'update_item'           => __( 'Update Resource', 'bizink-client' ),
			'view_item'             => __( 'View Resource', 'bizink-client' ),
			'view_items'            => __( 'View Resources', 'bizink-client' ),
			'search_items'          => __( 'Search BizPress Resources', 'bizink-client' ),
			'not_found'             => __( 'Not found', 'bizink-client' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'bizink-client' ),
			'featured_image'        => __( 'Featured Image', 'bizink-client' ),
			'set_featured_image'    => __( 'Set featured image', 'bizink-client' ),
			'remove_featured_image' => __( 'Remove featured image', 'bizink-client' ),
			'use_featured_image'    => __( 'Use as featured image', 'bizink-client' ),
			'insert_into_item'      => __( 'Insert into Resource', 'bizink-client' ),
			'uploaded_to_this_item' => __( 'Uploaded to this Resource', 'bizink-client' ),
			'items_list'            => __( 'BizPress Resources list', 'bizink-client' ),
			'items_list_navigation' => __( 'BizPress Resources list navigation', 'bizink-client' ),
			'filter_items_list'     => __( 'Filter Resources list', 'bizink-client' ),
		);
		$args = array(
			'label'                 => __( 'BizPress Resource', 'bizink-client' ),
			'description'           => __( 'A BizPress Resource video or other BizPress Content used for Bizpress', 'bizink-client' ),
			'labels'                => $labels,
			'supports'              => array( 'title', 'editor', 'thumbnail' ),
			'taxonomies'            => array( 'category', 'post_tag' ),
			'hierarchical'          => false,
			'public'                => false,
			'show_ui'               => false,
			'show_in_menu'          => false,
			'menu_position'         => 5,
			'show_in_admin_bar'     => false,
			'show_in_nav_menus'     => false,
			'can_export'            => false,
			'has_archive'           => false,
			'exclude_from_search'   => true,
			'publicly_queryable'    => false,
			'rewrite'               => false,
			'capability_type'       => 'page',
			'show_in_rest'          => false,
		);
		register_post_type( 'bizpress_resource', $args );
	}

	function bizpress_clear_cache(){
		
	}
}