<?php
/**
 * All Shortcode related functions
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
 * @subpackage Shortcode
 * @author codexpert <hello@codexpert.io>
 */
class Shortcode extends Base {

    public $plugin;
    public $slug;
    public $name;
    public $version;
    public $ncrypt;
    

    /**
     * Constructor function
     */
    public function __construct( $plugin ) {
        $this->plugin   = $plugin;
        $this->slug     = $this->plugin['TextDomain'];
        $this->name     = $this->plugin['Name'];
        $this->version  = $this->plugin['Version'];
        $this->ncrypt   = ncrypt();
    }

    public function bizpress_title($args = array(), $content = null, $tag = ''){
        $atts = array_change_key_case( (array) $atts, CASE_LOWER );
        $bizpress_title_atts = shortcode_atts(
            array(
                'title' => get_the_title(),
                'use_default' => 'true',
            ), $atts, $tag
        );

        $pagename = get_query_var('pagename');
		$content = get_query_var( 'bizpress');
		$type = get_query_var( 'type' );
		$topic = get_query_var( 'topic' );
		$calculator = get_query_var('calculator');
        $resource = get_query_var('resource');
		$type = '';
		if($content){
			$type = 'content';
		}
		else if($topic){
			$type = 'topic';
		}
		else if($type){
			$type = 'type';
		}
		else if($calculator){
			$type = 'calculator';
		}
        else if($resource && !$content){
			$type = 'type';
		}
        else if($resource){
            $type = 'resource';
        }

		if( !empty($content) && !empty($type) && (
		$pagename == 'keydates' ||
		$pagename == 'bizink-client-keydates' ||
		$pagename == 'xero-resources' ||
		$pagename == 'myob-resources' || 
		$pagename == 'quickbooks-resources' || 
        $pagename == 'freshbooks-resources' ||
		$pagename == 'sage-resources' ||
		$pagename == 'business-resources' ||
		$pagename == 'payroll-resources' ||
		$pagename == 'payroll-glossary' ||
        $pagename == 'businessterms' ||
        $pagename == 'resources' ||
		$pagename == 'calculators') ){
            $data = get_transient("bizinkcontent_".md5($content));
			if(empty($data)){
				$data = bizink_get_single_content( 'content', $content );
				set_transient( "bizinkcontent_".md5($content), $data, (DAY_IN_SECONDS * 2) );
			}
			return apply_filters('the_title',$data->post->post_title);
        }
        if($bizpress_title_atts['use_default'] == true || $bizpress_title_atts['use_default'] == 'true'){
            return apply_filters('the_title',$bizpress_title_atts['title']);
        }
        else{
            return '';
        }
    }

    public function bizink_content() {

		$pagename = get_query_var('pagename');
		$content = get_query_var( 'bizpress');
		$type = get_query_var( 'type' );
		$topic = get_query_var( 'topic' );
		$calculator = get_query_var('calculator');
        $resource = get_query_var('resource');
		$type = '';
		if($content){
			$type = 'content';
		}
		else if($topic){
			$type = 'topic';
		}
		else if($type){
			$type = 'type';
		}
		else if($calculator){
			$type = 'calculator';
		}
        else if($resource && !$content){
			$type = 'type';
		}
        else if($resource){
            $type = 'resource';
        }

		if( !empty($content) && !empty($type) && (
		$pagename == 'keydates' ||
		$pagename == 'bizink-client-keydates' ||
		$pagename == 'xero-resources' ||
		$pagename == 'myob-resources' || 
		$pagename == 'quickbooks-resources' ||
        $pagename == 'freshbooks-resources' || 
		$pagename == 'sage-resources' ||
		$pagename == 'business-resources' ||
		$pagename == 'payroll-resources' ||
		$pagename == 'payroll-glossary' ||
        $pagename == 'businessterms' ||
        $pagename == 'resources' ||
		$pagename == 'calculators') ){

            $data = get_transient("bizinkcontent_".md5($content));
			if(empty($data)){
				$data = bizink_get_single_content( 'content', $content );
				set_transient( "bizinkcontent_".md5($content), $data, (DAY_IN_SECONDS * 2) );
			}

            $anyliticsData = '<div style="display:none;" class="bizpress-data" id="bizpress-data"
			data-id="'.$data->post->ID.'"
			data-siteid="'.(bizpress_anylitics_get_site_id() ? bizpress_anylitics_get_site_id() : "false").'"
			data-single="true"
			data-title="'.$data->post->post_title.'" 
			data-slug="'.$data->post->post_name.'" 
			data-posttype="'.$data->post->post_type.'"
			data-topics="'. (empty($data->post->topics) == false ? implode(',',$data->post->topics) : "false") .'"
			data-types="'. (empty($data->post->types) == false ? implode(',',$data->post->types) : "false") . '" ></div>';

			return apply_filters('the_content',$data->post->post_content). $anyliticsData;
        }
        else{
            $curent_page_id = get_the_ID();
            $content_type   = bizink_get_content_type( $curent_page_id );

            if($content_type == 'business-terms' || $content_type == 'accounting-terms'){
                $data = get_transient("bizinkterms_".$content_type);
                if(empty($data)){
                    $data = bizink_get_content( $content_type, 'topics' );
                    set_transient( "bizinkterms_".$content_type, $data, DAY_IN_SECONDS);
                }
                return  cxbc_get_template( 'account', 'views', [ 'response' => $data ] );
            }
            else if($content_type == 'payroll' || $content_type == 'payroll-resources'){
                $data = get_transient("bizinkpayrolltype_".$content_type);
                if(empty($data)){
                    $data = bizink_get_content( $content_type, 'topics');
                    set_transient( "bizinkpayrolltype_".$content_type, $data, DAY_IN_SECONDS);
                }
                return cxbc_get_template( 'topics', 'views', [ 'response' => $data ] );
            }
            else if($content_type == 'payroll-glossary'){
                $data = get_transient("bizinkpayroll_".$content_type);
                if(empty($data)){
                    $data = bizink_get_content( $content_type, 'topics' );
                    set_transient( "bizinkpayroll_".$content_type, $data, DAY_IN_SECONDS);
                }
                return cxbc_get_template( 'topics', 'views', [ 'response' => $data ] );
            }
            else if($content_type == 'resources'){
                if($resource){
                    $data = get_transient("bizinkresourcetype_".$content_type);
                    if(empty($data)){
                        $data = bizink_get_content( $content_type, 'types', $resource );
                        set_transient( "bizinkresourcetype_".$content_type, $data, DAY_IN_SECONDS);
                    }
                    if(!empty($data->types)){
                        $data->topics = (array)$data->types;
                    }
                    if(!empty($data->posts)){
                        $data->posts = $data->topics[$resource]->posts;
                    }
                    return cxbc_get_template( 'topics', 'views', [ 'response' => $data ] );
                }
                else{
                    $data = bizink_get_content( $content_type, 'types' );
                    return cxbc_get_template( 'resources', 'views', [ 'response' => $data ] );
                }
                
            }
            else if($content_type == 'calculator-content'){
                $data = bizink_get_content( $content_type, 'calculators' );
                return cxbc_get_template( 'calculators', 'views', [ 'response' => $data ] );
            }
            else{
                $data = get_transient("bizinktopics_".$content_type);
                if(empty($data) || ( empty($data->topics) && empty($data->types) && empty($data->posts) )){
                    $data = bizink_get_content( $content_type, 'topics'); // Type
                    set_transient( "bizinktopics_".$content_type, $data, DAY_IN_SECONDS);
                }
                
                //$data = bizink_get_content( $content_type, 'topics' );
                return cxbc_get_template( 'topics', 'views', [ 'response' => $data ] );
            }
        }

    }

    public function bizink_landing( $args ) {
        $base_url = bizink_get_master_site_url();
        $atts = shortcode_atts( array(
            'id' => '',
            'height' => '800px'
        ), $args,'bizink_landing');
        if ( empty( $args['id'] ) ) return;

        $url    = trailingslashit( "{$base_url}" ) . 'landing/' . $atts['id'];
        $html   = '<iframe area-title="Landing Page" style="border:none; background:transparent; height:'.$atts['height'].';" id="bizpress_landingpage_'.$atts['id'].'" 
        class="bizpress_landingpage bizpress_landingpage_'.$args['id'].'" src="'. $url .'" width="100%" scrolling="no"></iframe>';
        $jsID = 'bizpress_landingpage_'.str_replace('-','_',$atts['id']);
        $html  .= '<script>
        (function() {
            var bizpress_landingpage_'.$jsID.'_doc = document.getElementById("bizpress_landingpage_'.$args['id'].'");
            window.addEventListener("message", function (e) {
                if (e.data.hasOwnProperty("masterHeight") && e.source === bizpress_landingpage_'.$jsID.'_doc.contentWindow) { bizpress_landingpage_'.$jsID.'_doc.style.height = e.data.masterHeight + "px"; }
            });
            bizpress_landingpage_'.$jsID.'_doc.contentWindow.postMessage("masterHeight", "*");
        })();
        </script>';
        return $html;
    }
}