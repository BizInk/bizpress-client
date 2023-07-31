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
 * @subpackage Settings
 * @author codexpert <hello@codexpert.io>
 */
class Settings extends Fields {
	
	/**
	 * @var array $config
	 */
	public $config;

	/**
	 * @var array $sections
	 */
	public $sections;

	public function __construct( $args = [] ) {

		// default values
		$psettings = get_option('bizink-client_basic', 'blank');
		if($psettings != 'blank' && isset($psettings['allow-user']) && $psettings['allow-user'] == 'on'){
			$defaults = [
				'id'			=> 'cx-settings',
				'label'			=> __( 'Settings' ),
				'priority'      => 10,
				'capability'    => 'edit_pages',
				'icon'          => 'dashicons-wordpress',
				'position'      => 25,
				'sections'		=> [],
			];
		} else {
			$defaults = [
				'id'			=> 'cx-settings',
				'label'			=> __( 'Settings' ),
				'priority'      => 10,
				'capability'    => 'manage_options',
				'icon'          => 'dashicons-wordpress',
				'position'      => 25,
				'sections'		=> [],
			];
		}

		$this->config = wp_parse_args( apply_filters( 'cx-settings-args', $args ), $defaults );
		$this->sections	= apply_filters( 'cx-settings-sections', $this->config['sections'] );

		parent::hooks();
		self::hooks();
	}

	public function hooks() {
		$this->action( 'admin_enqueue_scripts', 'enqueue_scripts', 99 );
		$this->action( 'admin_menu', 'admin_menu', $this->config['priority'] );
		$this->priv( 'cx-settings', 'save_settings' );
		$this->priv( 'cx-reset', 'reset_settings' );
		$this->priv( 'cx-installplugin', 'install_plugin' );
		$this->priv( 'cx-createpage', 'create_page' );
	}

	public function create_page(){
		if( !wp_verify_nonce( $_POST['_wpnonce'], 'cx-createpage' ) ) {
			wp_send_json( array( 'status' => 0, 'message' => __( 'Unauthorized!' ) ) );
		}
		$page_id = wp_insert_post(array(
			'post_title' => $_REQUEST['post_title'] ? $_REQUEST['post_title'] : 'Untitled',
			'post_content' => $_REQUEST['post_content'] ? $_REQUEST['post_content'] : '',
			'post_status' => $_REQUEST['post_status'] ? $_REQUEST['post_status'] : 'publish',
			'post_type' => $_REQUEST['post_type'] ? $_REQUEST['post_type'] :'page',
		));
		$error = is_wp_error($page_id);
		if($error){
			wp_send_json(array('status' => false, 'errors' => $error['errors'], 'error_data' => $error['error_data']));
		}
		else{
			wp_send_json(array('status' => true, 'page_id' => $page_id, 'page' => array(
				'post_title' => $_REQUEST['post_title'] ? $_REQUEST['post_title'] : 'Untitled',
				'post_content' => $_REQUEST['post_content'] ? $_REQUEST['post_content'] : '',
				'post_status' => $_REQUEST['post_status'] ? $_REQUEST['post_status'] : 'publish',
				'post_type' => $_REQUEST['post_type'] ? $_REQUEST['post_type'] :'page',
			)));
		}
		wp_die();
	}
	
	public function install_plugin(){
		if( !wp_verify_nonce( $_POST['_wpnonce'], 'cx-plugin-install' ) ) {
			wp_send_json( array( 'status' => 0, 'message' => __( 'Unauthorized!' ) ) );
		}
		if(!empty($_POST['pluginUrl'])){			
			
			
			// WP_PLUGIN_DIR 
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $_POST['pluginUrl']);
			/*
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Authorization: Bearer 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',
				'Accept: application/vnd.github.v3+json'
			));
			*/
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			$zipContents = curl_exec($ch);

			// Check for errors during the request
			if ($zipContents === false) {
				wp_send_json( array( 'status' => 0, 'message' => 'Error: ' . curl_error($ch) ) );
				wp_die();
			}
			else if($zipContents == 'Not Found'){
				wp_send_json( array( 'status' => 0, 'message' => 'Error: Plugin file not found!' ) );
				wp_die();
			}

			curl_close($ch);
			$zipFilePath = tempnam(sys_get_temp_dir(), 'downloaded_zip_');
			file_put_contents($zipFilePath, $zipContents);

			// Extract the contents of the zip file to the plugins folder
			$zip = new \ZipArchive();
			if ($zip->open($zipFilePath) === true) {
				$zip->extractTo(WP_PLUGIN_DIR);
				$zip->close();

				if(isset($_POST['plugin'])){
					wp_cache_flush();
					$result = activate_plugin($_POST['plugin']);
					if ( is_wp_error( $result ) ) {
						// Process Error
						wp_send_json( array( 'status' => 0, 'message' => __( 'Plugin installed but failed to activate!') , 'error' => $result ) );
					}
					else{
						// Process Success
						wp_send_json( array( 'status' => 1, 'message' => __( 'Plugin installed!' ) ) );
					}
				}
				else{
					wp_send_json( array( 'status' => 0, 'message' => __( 'Plugin installed but no plugin path supplied!' ) ) );
				}
				
			} else {
				wp_send_json( array( 'status' => 0, 'message' => 'Error: Failed to extract the zip file.' ) );
			}

			// Remove the downloaded zip file
			unlink($zipFilePath);
			wp_die();
		}
		else{
			wp_send_json( array( 'status' => 0, 'message' => __( 'Plugin URL is empty!' ) ) );
		}

		wp_die();
	}

	public function enqueue_scripts() {

		if( !isset( $_GET['page'] ) || $_GET['page'] != $this->config['id'] ) return;

		parent::enqueue_scripts();
    }

	public function admin_menu() {	
		if( isset( $this->config['parent'] ) && $this->config['parent'] != '' ) {
			add_submenu_page( $this->config['parent'], $this->config['label'], $this->config['label'], $this->config['capability'], $this->config['id'], array( $this, 'callback_fields' ) );
		}
		else {
			add_menu_page( $this->config['label'], $this->config['label'], $this->config['capability'], $this->config['id'], array( $this, 'callback_fields' ), $this->config['icon'], $this->config['position'] );
		}
	}

	public function save_settings() {
		if( !wp_verify_nonce( $_POST['_wpnonce'] ) ) {
			wp_send_json( array( 'status' => 0, 'message' => __( 'Unauthorized!' ) ) );
		}
		$option_name = $_POST['option_name'];

		$is_savable = apply_filters( 'cx-settings-savable', true, $option_name, $_POST );

		if( !$is_savable ) wp_send_json( apply_filters( 'cx-settings-response', array( 'status' => -1, 'message' => __( 'Ignored' ) ), $_POST ) );

		$page_load = $_POST['page_load'];
		unset( $_POST['action'] );
		unset( $_POST['option_name'] );
		unset( $_POST['page_load'] );
		unset( $_POST['_wpnonce'] );
		unset( $_POST['_wp_http_referer'] );

		update_option( $option_name, $_POST );
		wp_send_json( apply_filters( 'cx-settings-response', array( 'status' => 1, 'message' => __( 'Settings Saved!' ), 'page_load' => $page_load ), $_POST ) );
	}

	public function reset_settings() {
		if( !wp_verify_nonce( $_POST['_wpnonce'] ) ) {
			wp_send_json( array( 'status' => 0, 'message' => __( 'Unauthorized!' ) ) );
		}
		$option_name = $_POST['option_name'];

		$is_savable = apply_filters( 'cx-settings-resetable', true, $option_name, $_POST );

		if( !$is_savable ) wp_send_json( apply_filters( 'cx-settings-response', array( 'status' => -1, 'message' => __( 'Ignored' ) ), $_POST ) );

		delete_option( $_POST['option_name'] );
		wp_send_json( apply_filters( 'cx-settings-response', array( 'status' => 1, 'message' => __( 'Settings Reset!' ) ), $_POST ) );
	}
}