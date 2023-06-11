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

    public function bizink_content() {

        $curent_page_id = get_the_ID();
        $content_type   = bizink_get_content_type( $curent_page_id );
        if($content_type == 'business-terms' || $content_type == 'accounting-terms' || $content_type == 'payroll-glossary' ){
            /*
            $data = get_transient("bizinktype_".md5($content_type));
			if(empty($data)){
				$data = bizink_get_content( $content_type, 'topics');
				set_transient( "bizinktype_".md5($content_type), $data, (DAY_IN_SECONDS * 2) );
			}
            */
            $data = bizink_get_content( $content_type, 'topics' );
            return cxbc_get_template( 'account', 'views', [ 'response' => $data ] );
        }
        else if($content_type == 'calculator-content'){
            /*
            $data = get_transient("bizinktype_".md5($content_type));
			if(empty($data)){
				$data = bizink_get_content( $content_type, 'calculators');
				set_transient( "bizinktype_".md5($content_type), $data, (DAY_IN_SECONDS * 2) );
			}
            */
            $data = bizink_get_content( $content_type, 'calculators' );
            return cxbc_get_template( 'calculators', 'views', [ 'response' => $data ] );
        }
        else{
            /*
            $data = get_transient("bizinktype_".md5($content_type));
			if(empty($data)){
				$data = bizink_get_content( $content_type, 'type');
				set_transient( "bizinktype_".md5($content_type), $data, (DAY_IN_SECONDS * 2) );
			}
            */
            $data = bizink_get_content( $content_type, 'topics' );
            return cxbc_get_template( 'topics', 'views', [ 'response' => $data ] );
        }
        /*
         else if($content_type == 'keydates' || $content_type == 'bizink-client-keydates' ||
            $content_type == 'keydates-au' ||
            $content_type == 'keydates-ca' ||
            $content_type == 'keydates-us' ||
            $content_type == 'keydates-nz' ||
            $content_type == 'keydates-gb' ){
            $data = bizink_get_content( $content_type, 'topics' );
            return cxbc_get_template( 'content', 'views', [ 'response' => $data ] );
        }
        */
        // accounting-terms business-terms
        //$accounting_content_page_id = cxbc_get_option( 'bizink-client_basic', 'accounting_content_page' );
        //$business_content_page_id = cxbc_get_option( 'bizink-client_basic', 'business_content_page' );
        //$business_terms_content_page_id = cxbc_get_option( 'bizink-client_basic', 'business_terms_content_page' );

        
    }

    public function bizink_landing( $args ) {
        $base_url = bizink_get_master_site_url();
        if ( empty( $args['id'] ) ) return;
        $url    = trailingslashit( "{$base_url}" ) . 'landing/' . $args['id'];
        $html   = '<iframe area-title="Landing Page" id="myframe" src="'. $url .'" width="100%" scrolling="no" onload="setMasterHeight(this)"></iframe>';
        return $html;
    }

    public function bizink_calculators( $args ){
        if ( empty( $args['id'] ) ) return;
        //$base_url = bizink_get_master_site_url();

    }
}