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
        $data           = bizink_get_content( $content_type, 'topics' );

        return cxbc_get_template( 'topics', 'views', [ 'response' => $data ] );
    }

    public function bizink_landing( $args ) {
        $base_url = bizink_get_master_site_url();

        if ( empty( $args['id'] ) ) return;

        $url    = trailingslashit( "{$base_url}" ) . 'landing/' . $args['id'];

        $html   = '<iframe id="myframe" src="'. $url .'" width="100%" scrolling="no" onload="setMasterHeight(this)"></iframe>';
        /* $html   .= '<script>
        window.addEventListener(\'message\', function (e) {
            if (e.data.hasOwnProperty("masterHeight")) {
                jQuery("#myframe").css("height", e.data.masterHeight);
                console.log("iFrame Height",e.data.masterHeight);      
            }
        });

        function setMasterHeight(iframe) {
           iframe.contentWindow.postMessage("masterHeight", "*");   
        }
        </script>';
        */
        return $html;
    }
}