<?php
namespace codexpert\product;

/**
 * if accessed directly, exit.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * @package Plugin
 * @subpackage Update
 * @author codexpert <hello@codexpert.io>
 */
class Update extends Base {

	public function __construct( $plugin ) {
		$this->plugin	= $plugin;
		$this->slug		= $this->plugin['TextDomain'];
		$this->version	= $this->plugin['Version'];
		$this->license	= $plugin['license'];
		$this->server	= untrailingslashit( $this->plugin['server'] );
		$this->basename	= str_replace( [ '/', '.' ], [ '-', '-' ], plugin_basename( $this->plugin['file'] ) );
		$this->package	= add_query_arg( [ 'action' => 'get_metadata', 'slug' => $this->slug ], "{$this->server}/wp-products/" );

		$this->filter( 'plugins_api', 'add_plugin_info', 20, 3 );
		// $this->filter( 'site_transient_update_plugins', 'push_update' );
		$this->filter( 'pre_set_site_transient_update_plugins', 'push_update' );
		$this->action( 'upgrader_process_complete', 'delete_transient', 10, 2 );
	}

	public function add_plugin_info( $res, $action, $args ) {

		if( 'plugin_information' !== $action || $this->slug !== $args->slug ) {
			return false;
		}

		if( false == $remote = get_transient( "cx_update_{$this->slug}" ) ) {

			$remote = wp_remote_get( $this->package, [
				'timeout' => 10,
				'headers' => [
					'Accept' => 'application/json'
				]
			] );

			if ( ! is_wp_error( $remote ) && isset( $remote['response']['code'] ) && $remote['response']['code'] == 200 && ! empty( $remote['body'] ) ) {
				set_transient( "cx_update_{$this->slug}", $remote, apply_filters( "{$this->slug}_update_check_interval", HOUR_IN_SECONDS ) );
			}

		}

		if( $remote ) {

			$remote	= json_decode( $remote['body'] );
			$res	= new \stdClass();

			$res->name				= $remote->name;
			$res->slug				= $remote->slug;
			$res->version			= $remote->version;
			// $res->tested			= $remote->tested;
			// $res->requires			= $remote->requires;
			$res->author			= $remote->author;
			$res->author_profile	= $this->license->_is_active() ? $remote->download_url : false;
			$res->download_link		= $this->license->_is_active() ? $remote->download_url : false;
			$res->trunk				= $this->license->_is_active() ? $remote->download_url : false;
			// $res->requires_php		= '5.3';
			$res->last_updated		= $remote->last_updated;
			$res->sections			= [
				'description'	=> $remote->sections->description,
				'installation'	=> $remote->sections->installation,
				'changelog'		=> $remote->sections->changelog
				// you can add your custom sections (tabs) here
			];

			// in case you want the screenshots tab, use the following HTML format for its content:
			// <ol><li><a href="IMG_URL" target="_blank"><img src="IMG_URL" alt="CAPTION" /></a><p>CAPTION</p></li></ol>
			if( !empty( $remote->sections->screenshots ) ) {
				$res->sections['screenshots'] = $remote->sections->screenshots;
			}

			$res->banners = [
				// 'low'	=> '',
				// 'high'	=> ''
			];

			return $res;

		}

		return false;

	}


	public function push_update( $transient ){

		if ( empty( $transient->checked ) ) {
			return $transient;
		}

		// don't loop
		if( did_action( "{$this->slug}_update_checked" ) ) {
			return $transient;
		}
		
		do_action( "{$this->slug}_update_checked" );

		if( ( isset( $_GET['cx-recheck'] ) && $_GET['cx-recheck'] == $this->slug ) || false == $remote = get_transient( "cx_upgrade_{$this->slug}" ) ) {

			$remote = wp_remote_get( $this->package, [
				'timeout' => 10,
				'headers' => [
					'Accept' => 'application/json'
				]
			] );

			if ( !is_wp_error( $remote ) && isset( $remote['response']['code'] ) && $remote['response']['code'] == 200 && !empty( $remote['body'] ) ) {
				set_transient( "cx_upgrade_{$this->slug}", $remote, apply_filters( "{$this->slug}_update_check_interval", HOUR_IN_SECONDS ) );
			}

		}

		if( !is_wp_error( $remote ) && isset( $remote['body'] ) ) {

			$remote = json_decode( $remote['body'] );

			if( $remote ) {
				// echo $remote->download_url;
				$res				= new \stdClass();
				$res->slug			= $remote->slug;
				$res->plugin		= "{$remote->slug}/{$remote->slug}.php";
				$res->new_version	= $remote->version;
				// $res->tested		= $remote->tested;
				$res->package		= $this->license->_is_active() ? $remote->download_url : false;

				$transient->response[ $res->plugin ] = $res;
				//$transient->checked[$res->plugin] = $remote->version;
			}

		}

		return $transient;
	}
	 
	public function delete_transient( $upgrader_object, $options ) {
		if ( $options['action'] == 'update' && $options['type'] === 'plugin' )  {
			delete_transient( "cx_upgrade_{$this->slug}" );
		}
	}
}