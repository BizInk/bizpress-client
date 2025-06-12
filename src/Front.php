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
	public $slug;
	public $name;
	public $version;
	public $ncrypt;

	/**
	 * Constructor function
	 */
	public function __construct( $plugin ) {
		$this->plugin		= $plugin;
		$this->slug			= $this->plugin['TextDomain'];
		$this->name			= $this->plugin['Name'];
		$this->version		= $this->plugin['Version'];
		$this->ncrypt   	= ncrypt();
	}

	public function bizpress_anylitics_head(){
		$id = bizpress_anylitics_get_site_id();
		if($id){
			echo '<meta title="bizpresssiteid" content="'.$id.'" />';
		}
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
		//$min = defined( 'CXBPC_DEBUG' ) && CXBPC_DEBUG ? '' : '.min'; // {$min}
		wp_enqueue_style( $this->slug, plugins_url( "/assets/css/bizpress_front.css", CXBPC ), '', $this->version, 'all' );
		wp_enqueue_script( $this->slug.'-front', plugins_url( "/assets/js/bizpress_front.js", CXBPC ), array( 'jquery' ), $this->version, false );
		wp_enqueue_script( $this->slug.'-dataprocess', plugins_url( "/assets/js/dataprocess.js", CXBPC ), array(), $this->version, true );
		$localized = [
			'ajaxurl'	=> admin_url( 'admin-ajax.php' )
		];
		wp_localize_script( $this->slug.'-front', 'CXBPC', apply_filters( "{$this->slug}-localized", $localized ) );
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
		$resource = get_query_var( 'resource',false);
		$attachment = get_query_var( 'attachment',false);

		$business_page_id = cxbc_get_option( 'bizink-client_basic', 'business_content_page' );
		$xero_page_id = cxbc_get_option( 'bizink-client_basic', 'xero_content_page' );
		$quickbooks_page_id	= cxbc_get_option( 'bizink-client_basic', 'quickbooks_content_page' );
		$freshbooks_page_id	= cxbc_get_option( 'bizink-client_basic', 'freshbooks_content_page' );
		$sage_page_id	= cxbc_get_option( 'bizink-client_basic', 'sage_content_page' );
		$myob_page_id	= cxbc_get_option( 'bizink-client_basic', 'myob_content_page' );
		$keydates_page_id = cxbc_get_option( 'bizink-client_basic', 'keydates_content_page' );
		$payroll_page_id = cxbc_get_option( 'bizink-client_basic', 'payroll_content_page' );
		$payroll_glossary_id = cxbc_get_option( 'bizink-client_basic', 'payroll_glossary_page' );
		$resource_page_id = cxbc_get_option( 'bizink-client_basic', 'resources_content_page' );

		if($attachment && ( !$content || !$resource )){
			$post = null;

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
			if(!empty($myob_page_id)){
				$post = get_post( $myob_page_id );
			}
			if(!empty($quickbooks_page_id)){
				$post = get_post( $quickbooks_page_id );
			}
			if(!empty($freshbooks_page_id)){
				$post = get_post( $freshbooks_page_id );
			}
			if(!empty($sage_page_id)){
				$post = get_post( $sage_page_id );
			}
			if(!empty($keydates_page_id)){
				$post = get_post( $keydates_page_id );
			}
			if(!empty($resource_page_id)){
				$post = get_post( $resource_page_id );
			}
			if(!empty($post)){
				$slug = $post->post_name;
			}
		}

		if( $resource ){
			$pageType = get_query_var('pagename','page');
			$query->set( 'post_type', 'page' );
			if(!empty($resource_page_id) && $pageType = 'resources'){
				$post = get_post( $resource_page_id );
				$query->set( 'p', $resource_page_id );
				$query->set( 'page_id', $resource_page_id );
			}

			$data = get_transient("bizinkresource_".md5($resource));
			if(empty($data->status)){
				$data = null;
			}
			if(empty($data)){
				$data = bizink_get_content_types( 'resource','topics', $resource );
				set_transient( "bizinkresource_".md5($resource), $data, (DAY_IN_SECONDS * 2) );
			}
			
			if(!empty($data) && !empty($data->post)){
				
				$query->set('post_title',$data->post->post_title ?? '');
				$query->set('title',$data->post->post_title ?? '');
				$query->set('post_content',$data->post->post_content ?? '');
				$query->set('post_date',$data->post->post_date ?? '');
				$query->set('post_name',$data->post->post_name ?? '');
				$query->set('post_date_gmt',$data->post->post_date_gmt ?? '');
				set_query_var('bizpress_data',$data ?? '');
			}
		}
		else if ( $content ) {
			$query->set( 'post_type', 'page' );
			$pageType = get_query_var('pagename','page');
			if($xero_page_id && $pageType == 'xero-resources'){
				$query->set( 'p', $xero_page_id );
				$query->set( 'page_id', $xero_page_id );
			}
			else if($myob_page_id  && $pageType == 'myob-resources'){
				$query->set( 'p', $myob_page_id );
				$query->set( 'page_id', $myob_page_id );
			}
			else if($quickbooks_page_id  && $pageType == 'quickbooks-resources'){
				$query->set( 'p', $quickbooks_page_id );
				$query->set( 'page_id', $quickbooks_page_id );
			}
			else if($freshbooks_page_id  && $pageType == 'freshbooks-resources'){
				$query->set( 'p', $freshbooks_page_id );
				$query->set( 'page_id', $freshbooks_page_id );
			}
			else if($sage_page_id  && $pageType == 'sage-resources'){
				$query->set( 'p', $sage_page_id );
				$query->set( 'page_id', $sage_page_id );
			}
			else if($business_page_id && $pageType = 'business-resources'){
				$query->set( 'p', $business_page_id );
				$query->set( 'page_id', $business_page_id );
			}
			else if($keydates_page_id && $pageType == 'bizink-client-keydates'){
				$query->set( 'p', $keydates_page_id );
				$query->set( 'page_id', $keydates_page_id );
			}
			else if($keydates_page_id && $pageType == 'keydates'){
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
			else if($resource_page_id && $pageType = 'resources'){
				$query->set( 'p', $resource_page_id );
				$query->set( 'page_id', $resource_page_id );
			}
			else{
				return;
			}
			
			$data = get_transient("bizinkcontent_".md5($content));
			if(!empty($data->status) && ($data->status == 500 || $data->status == 403) ){
				$data = null;
			}
			if(empty($data)){
				$data = bizink_get_single_content( 'content', $content );
				set_transient( "bizinkcontent_".md5($content), $data, (DAY_IN_SECONDS * 2) );
			}

			if(!empty($data) && !empty($data->post)){
				$query->set('post_title',$data->post->post_title ?? '');
				$query->set('title',$data->post->post_title ?? '');
				$query->set('post_content',$data->post->post_content ?? '');
				$query->set('post_date',$data->post->post_date ?? '');
				$query->set('post_name',$data->post->post_name ?? '');
				$query->set('post_date_gmt',$data->post->post_date_gmt ?? '');
				set_query_var('bizpress_data',$data ?? '');
			}
		}
		
		
	}

	public function the_post( $post ){
		global $wp, $wp_query;
		$current_url = home_url( add_query_arg( array(), $wp->request ) );
		$pagename = get_query_var('pagename');
		$content = get_query_var( 'bizpress');
		$resource = get_query_var( 'resource');
		$type = get_query_var( 'type' );
		$topic = get_query_var( 'topic' );
		$calculator = get_query_var('calculator');
		$attachment = get_query_var( 'attachment',false);
		$type = '';

		if(empty($post) ){
			$business_page_id = cxbc_get_option( 'bizink-client_basic', 'business_content_page' );
			$xero_page_id = cxbc_get_option( 'bizink-client_basic', 'xero_content_page' );
			$quickbooks_page_id	= cxbc_get_option( 'bizink-client_basic', 'quickbooks_content_page' );
			$freshbooks_page_id	= cxbc_get_option( 'bizink-client_basic', 'freshbooks_content_page' );
			$sage_page_id	= cxbc_get_option( 'bizink-client_basic', 'sage_content_page' );
			$myob_page_id	= cxbc_get_option( 'bizink-client_basic', 'myob_content_page' );
			$keydates_page_id = cxbc_get_option( 'bizink-client_basic', 'keydates_content_page' );
			$payroll_page_id = cxbc_get_option( 'bizink-client_basic', 'payroll_content_page' );
			$payroll_glossary_id = cxbc_get_option( 'bizink-client_basic', 'payroll_glossary_page' );
			$resource_page_id = cxbc_get_option( 'bizink-client_basic', 'resources_content_page' );

			if($attachment && ( !$content || !$resource )){
				$post = null;

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
				if(!empty($myob_page_id)){
					$post = get_post( $myob_page_id );
				}
				if(!empty($quickbooks_page_id)){
					$post = get_post( $quickbooks_page_id );
				}
				if(!empty($freshbooks_page_id)){
					$post = get_post( $freshbooks_page_id );
				}
				if(!empty($sage_page_id)){
					$post = get_post( $sage_page_id );
				}
				if(!empty($keydates_page_id)){
					$post = get_post( $keydates_page_id );
				}
				if(!empty($resource_page_id)){
					$post = get_post( $resource_page_id );
				}
			}
		}
		
		

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
		else if($resource && !$content){
			$d = $resource;
			$type = 'topics';
			return $post;
		}
		else if($resource && $content){
			$d = $resource;
			$type = 'resource';
		}
		
		
		if( $type != '' && (
		$pagename == 'keydates' ||
		$pagename == 'bizink-client-keydates' ||
		$pagename == 'xero-resources' ||
		$pagename == 'freshbooks-resources' ||
		$pagename == 'myob-resources' || 
		$pagename == 'quickbooks-resources' || 
		$pagename == 'sage-resources' ||
		$pagename == 'business-resources' ||
		$pagename == 'payroll-resources' ||
		$pagename == 'payroll-glossary' ||
		$pagename == 'businessterms' ||
		$pagename == 'business-terms' ||
		$pagename == 'resources' ||
		$pagename == 'calculators') ){

			$main_slug 		= explode($type, $current_url );
			$main_slug_id 	= url_to_postid( $main_slug[0] );
			$content_type   = bizink_get_content_type( $main_slug_id );


			$data = get_transient("bizink'.$type.'_".md5($d));
			if(empty($data)){
				$data = bizink_get_content( $content_type, $type, $d );
				set_transient( "bizink'.$type.'_".md5($d), $data, (DAY_IN_SECONDS * 2) );
			}

			if( isset( $data->subscriptions_expiry ) ) {
				update_option( '_cxbc_suscription_expiry', $data->subscriptions_expiry );
			}

			if(BIZPRESS_ANALYTICS == true){
				$anyliticsData = '<div style="display:none;" class="bizpress-data" id="bizpress-data"
				data-id="'.$data->post->ID.'"
				data-siteid="'.(bizpress_anylitics_get_site_id() ? bizpress_anylitics_get_site_id() : "false").'"
				data-single="true"
				data-title="'.$data->post->post_title.'" 
				data-slug="'.$data->post->post_name.'" 
				data-posttype="'.$data->post->post_type.'"
				data-topics="'. (empty($data->post->topics) == false ? implode(',',$data->post->topics) : "false") .'"
				data-types="'. (empty($data->post->types) == false ? implode(',',$data->post->types) : "false") . '" ></div>';
			}
			else{
				$anyliticsData = '';
			}
			if(!empty($data->post->post_title)){
				$post->post_title = $data->post->post_title;
			}
			
			$buttonData = '';
			if(!empty($data->post->post_type)){
				if($data->post->post_type == 'resources'){
					foreach($data->types as $key => $type){
						if( ($type == 'Templates' || $type == 'Template' || $type == 'template' || $type == 'templates') && !empty($data->post->document_download_url)){
							$buttonData = '<div class="bizpress_template_container"><a target="_blank" href="'.$data->post->document_download_url.'" class="bizpress_template_link" data-id="'.$data->post->ID.'" data-slug="'.$data->post->post_name.'">'.__('Download Resource','bizink-client').'</a></div>';
						}
					}
				}
			}
			
			if(!empty($post) && !empty($post->post_content)){
				$post->post_content =  apply_filters('the_content',$buttonData . $data->post->post_content). $anyliticsData;
			}
			
			$post->post_type = 'page';
		}

		return $post;
	}

	public function bizpress_wpseo_title($title){
		$content = get_query_var( 'bizpress');
		$resource = get_query_var( 'resource');
		if($content && is_singular()){
			$data = get_transient("bizinkcontent_".md5($content));
			if(empty($data)){
				$data = bizink_get_single_content( 'content', $content );
				set_transient( "bizinkcontent_".md5($content), $data, (DAY_IN_SECONDS * 2) );
			}
			$title = $data->post->post_title;
		}
		return $title;
	}

	public function bizpress_wpseo_metadesc($desc){
		$content = get_query_var( 'bizpress');
		$resource = get_query_var( 'resource');
		if($content && is_singular()){
			$data = get_transient("bizinkcontent_".md5($content));
			if(empty($data)){
				$data = bizink_get_single_content( 'content', $content );
				set_transient( "bizinkcontent_".md5($content), $data, (DAY_IN_SECONDS * 2) );
			}
			$desc = ($data->post->post_excerpt && mb_strlen($data->post->post_excerpt) < 2) ? $data->post->post_excerpt : $data->post->post_title;
			
		}
		return $desc;
	}

	public function the_title($post_title) {
		global $wp, $wp_query;
		if ( is_singular() && in_the_loop() ) {
			$pagename = get_query_var('pagename',false);
			$content = get_query_var( 'bizpress');
			$type = '/';
			if($pagename == 'resources'){
				$resource = get_query_var('resource');

				$wp_query->is_404 = false;
				$current_url = home_url( add_query_arg( array(), $wp->request ) );
				$main_slug 		= explode($type, $current_url );
				$main_slug_id 	= url_to_postid( $main_slug[0] );
				$content_type   = bizink_get_content_type( $main_slug_id );
				
				if($resource && $content){
					$d = $content;
					$type = 'type';

					$data = get_transient("bizink'.$type.'_".md5($d));
					if(empty($data)){
						$data = bizink_get_content( $content_type, $type, $d );
						set_transient( "bizink'.$type.'_".md5($d), $data, (DAY_IN_SECONDS * 2) );
					}
		
					if( isset( $data->subscriptions_expiry ) ) {
						update_option( '_cxbc_suscription_expiry', $data->subscriptions_expiry );
					}

					if(!empty($data) && !empty($data->post) && !empty($data->post->post_title)){
						$post_title = $data->post->post_title;
					}

				}
				else if($resource && !$content){
					$type = 'resource';

					$data = get_transient("bizinkresource_".md5($resource));
					if(empty($data->status)){
						$data = null;
					}
					if(empty($data)){
						$data = bizink_get_content_types( 'resource','topics', $resource );
						set_transient( "bizinkresource_".md5($resource), $data, (DAY_IN_SECONDS * 2) );
					}
					
					print_r($data);

					if(!empty($data) && !empty($data->post) && !empty($data->post->post_title)){
						return $data->post->post_title;
					}
				}
				
			}
			else if($pagename == 'xero-resources' || 
			$pagename == 'keydates' ||
			$pagename == 'bizink-client-keydates' ||
			$pagename == 'quickbooks-resources' ||
			$pagename == 'freshbooks-resources' ||
			$pagename == 'sage-resources' ||
			$pagename == 'myob-resources' || 
		    $pagename == 'business-resources' ||
			$pagename == 'payroll-resources' ||
			//$pagename == 'payroll-glossary' ||
			//$pagename == 'businessterms' ||
			//$pagename == 'business-terms' ||
			$pagename == 'calculators'){

				$type = get_query_var( 'type' );
				$topic = get_query_var( 'topic' );
				$calculator = get_query_var('calculator');
				$current_url = home_url( add_query_arg( array(), $wp->request ) );
				if ( $topic || $type || $content || $calculator) {
					
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
						set_transient( "bizink'.$type.'_".md5($d), $data, (DAY_IN_SECONDS * 2) );
					}
		
					if( isset( $data->subscriptions_expiry ) ) {
						update_option( '_cxbc_suscription_expiry', $data->subscriptions_expiry );
					}

					if(!empty($data) && !empty($data->post) && !empty($data->post->post_title)){
						$post_title = $data->post->post_title;
					}
				}
			}
		}
		return $post_title;
	}

	public function the_content($contentData){
		// && in_the_loop() && is_main_query() 
		if ( is_singular() ) {
			global $wp, $wp_query;
			$pagename = get_query_var('pagename');
			if($pagename == 'calculators' ||
			$pagename == 'keydates' ||
			$pagename == 'bizink-client-keydates' ||
			$pagename == 'xero-resources' ||
			$pagename == 'freshbooks-resources' ||
			$pagename == 'quickbooks-resources' ||
			$pagename == 'sage-resources' ||
			$pagename == 'myob-resources' ||
			$pagename == 'business-resources' ||
			//$pagename == 'payroll-resources' ||
			//$pagename == 'payroll-glossary' ||
			//$pagename == 'business-terms' ||
			//$pagename == 'resources' ||
			$pagename == 'businessterms'
			){
				$resource = get_query_var('resource');
				$type = get_query_var( 'type' );
				$topic = get_query_var( 'topic' );
				$content = get_query_var( 'bizpress');
				$calculator = get_query_var('calculator');
				$current_url = home_url( add_query_arg( array(), $wp->request ) );
				if ( $topic || $type || $content || $calculator || $resource ) {
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
					else if($resource && !$content){
						$d = $content;
						$type = 'type';
					}
					else if($resource){
						$d = $resource;
						$type = 'resource';
					}
					
					$wp_query->is_404 = false; 
					$main_slug 		= explode($type, $current_url );
					$main_slug_id 	= url_to_postid( $main_slug[0] );
					$content_type   = bizink_get_content_type( $main_slug_id );

					$data = get_transient("bizink'.$type.'_".md5($d));
					if(empty($data)){
						$data = bizink_get_content( $content_type, $type, $d );
						set_transient( "bizink'.$type.'_".md5($d), $data, (DAY_IN_SECONDS * 2) );
					}

					if( isset( $data->subscriptions_expiry ) ) {
						update_option( '_cxbc_suscription_expiry', $data->subscriptions_expiry );
					}

					if(BIZPRESS_ANALYTICS == true){
						$anyliticsData = '<div style="display:none;" class="bizpress-data" id="bizpress-data" 
						data-id="'.$data->post->ID.'"
						data-siteid="'.(bizpress_anylitics_get_site_id() ? bizpress_anylitics_get_site_id() : "false").'"
						data-single="true"
						data-title="'.$data->post->post_title.'" 
						data-slug="'.$data->post->post_name.'" 
						data-posttype="'.$data->post->post_type.'"
						data-topics="'. (empty($data->post->topics) == false ? implode(',',$data->post->topics) : "false") .'"
						data-types="'. (empty($data->post->types) == false ? implode(',',$data->post->types) : "false") . '" ></div>';
					}
					else{
						$anyliticsData = '';
					}
					$buttonData = '';
					if($data->post->post_type == 'resources'){
						foreach($data->types as $key => $type){

							if( ($type == 'Templates' || $type == 'Template' || $type == 'template' || $type == 'templates') && !empty($data->post->document_download_url)){
								// 
								$buttonData = '<div class="bizpress_template_container"><a href="'.$data->post->document_download_url.'" class="bizpress_template_link" data-id="'.$data->post->ID.'" data-slug="'.$data->post->post_name.'">'.__('Download Resource','bizink-client').'</a></div>';
							}
						}
					}

					$contentData = $data->post->post_content ? $data->post->post_content : $contentData;
					if(($pagename == 'payroll-glossary' || $pagename == 'businessterms' || $pagename == 'business-terms') && !empty($content)){
						$contentData = '<div class="bizpress_card_container"><div class="bizpress_card">'.$contentData.'</div></div>' . $anyliticsData;
					}
					else{
						$contentData = $buttonData . $contentData . $anyliticsData;
					}
				}
			}
		}
		return $contentData;
	}

	public function template_redirect($body) {
		global $wp, $wp_query;
		$resource = get_query_var('resource');
		$type 		= get_query_var( 'type' );
		$topic 		= get_query_var( 'topic' );
		$content	= get_query_var( 'bizpress');   // attachment
		$calculator = get_query_var('calculator');

		$current_url = home_url( add_query_arg( array(), $wp->request ) );
		
		if( $calculator ){
			$wp_query->is_404 = false; 
	    	$main_slug 		= explode('topic', $current_url );
	    	$main_slug_id 	= url_to_postid( $main_slug[0] );
	    	$content_type   = bizink_get_content_type( $main_slug_id );

			$data = get_transient("bizinktopic_".md5($topic));
			if(empty($data)){
				$data = bizink_get_content( $content_type, 'post', $topic );
				set_transient( "bizinktopic_".md5($topic), $data, (DAY_IN_SECONDS * 2) );
			}

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
				set_transient( "bizinktopic_".md5($topic), $data, (DAY_IN_SECONDS * 2) );
			}

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
				set_transient( "bizinktype_".md5($type), $data, (DAY_IN_SECONDS * 2) );
			}

	        if( isset( $data->subscriptions_expiry ) ) {
	        	update_option( '_cxbc_suscription_expiry', $data->subscriptions_expiry );
	        }
	        echo cxbc_get_template( 'posts', 'views', [ 'response' => $data ] );
	        die;
	    }
		
		if ( $resource ) {
			$main_slug 		= explode('resource', $current_url );
	    	$main_slug_id 	= url_to_postid( $main_slug[0] );
			$content_type   = bizink_get_content_type( $main_slug_id );
			$data = bizink_get_single_content( 'content', $content );
	    	add_filter('body_class', function( $classes ){
	    		$classes[] = 'bizink-page';
	    		return $classes;
	    	});
	        if( isset( $data->subscriptions_expiry ) ) {
	        	update_option( '_cxbc_suscription_expiry', $data->subscriptions_expiry );
	        }
			echo apply_filters('the_content', cxbc_get_template( 'content', 'views', [ 'response' => $data ] ) );
	        die;
	    }

		if ( $content ) {
			$main_slug 		= explode('type', $current_url );
	    	$main_slug_id 	= url_to_postid( $main_slug[0] );
			$content_type   = bizink_get_content_type( $main_slug_id );
			$data = bizink_get_single_content( 'content', $content );
	    	add_filter('body_class', function( $classes ){
	    		$classes[] = 'bizink-page';
	    		return $classes;
	    	});
	        if( isset( $data->subscriptions_expiry ) ) {
	        	update_option( '_cxbc_suscription_expiry', $data->subscriptions_expiry );
	        }
			echo apply_filters('the_content', cxbc_get_template( 'content', 'views', [ 'response' => $data ] ) );
	        die;
	    }
		
	    return $body;
	}

	public function bizpress_wpseo_canonical($canonical){
		global $wp;
		$ending = '';
		$type 		= get_query_var( 'type' );
		$topic 		= get_query_var( 'topic' );
		$calculator = get_query_var('calculator');
		$content	= get_query_var( 'bizpress');  // attachment
		// $bizpressData = get_query_var('bizpress_data');
		$current_url = home_url( add_query_arg( array(), $wp->request ) );
		if($type || $topic || $calculator || $content){
			$canonical = $current_url;
			$ending = substr(get_option('permalink_structure'), -1) == '/' ? '/':'';
		}
		return $canonical.$ending;
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