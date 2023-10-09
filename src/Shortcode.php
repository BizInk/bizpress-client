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

    public function bizink_content() {

        $curent_page_id = get_the_ID();
        $content_type   = bizink_get_content_type( $curent_page_id );
        if($content_type == 'business-terms' || $content_type == 'accounting-terms' || $content_type == 'payroll-glossary' ){
            $data = bizink_get_content( $content_type, 'topics' );
            return cxbc_get_template( 'account', 'views', [ 'response' => $data ] );
        }
        else if($content_type == 'calculator-content'){
            $data = bizink_get_content( $content_type, 'calculators' );
            return cxbc_get_template( 'calculators', 'views', [ 'response' => $data ] );
        }
        else{
            
            $data = get_transient("bizinktype_".$content_type);
			if(empty($data)){
				$data = bizink_get_content( $content_type, 'type');
				set_transient( "bizinktype_".$content_type, $data, DAY_IN_SECONDS);
			}
            
            $data = bizink_get_content( $content_type, 'topics' );
            return cxbc_get_template( 'topics', 'views', [ 'response' => $data ] );
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