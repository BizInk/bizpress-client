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
 * @subpackage License
 * @author codexpert <hello@codexpert.io>
 */
class License extends Base {
	
	public $plugin;

	public function __construct( $plugin, $activator_path = '', $secret_key = '580cc082161006.41870101' ) {
		$this->plugin 			= $plugin;
		$this->license_server 	= $this->plugin['server'];
		$this->secret_key 		= $secret_key;
		$this->slug 			= $this->plugin['TextDomain'];
		$this->basename 		= str_replace( array( '/', '.' ), array( '-', '-' ), plugin_basename( $this->plugin['file'] ) );
		$this->icon 			= isset( $this->plugin['icon'] ) ? $this->plugin['icon'] : '<span class="dashicons dashicons-admin-network"></span>';
		$this->activator_path	= $activator_path == '' ? "admin.php?page={$this->slug}#{$this->slug}_license" : $activator_path;
		$this->activator_url	= admin_url( "{$this->activator_path}" );

		self::hooks();
	}

	public function hooks() {

		/**
		 * Don't hook twice
		 */
		if( did_action( "cx-license-loaded-{$this->slug}" ) ) return;
		do_action( "cx-license-loaded-{$this->slug}" );

		/**
		 * Initialize hooks
		 */
		$this->activate( 'activation' );
		$this->deactivate( 'deactivation' );
		$this->filter( 'cron_schedules', 'cron_schedules' );
		$this->action( "cron_{$this->slug}", 'check' );
		$this->action( 'admin_enqueue_scripts', 'enqueue_scripts', 99 );
		$this->action( 'admin_notices', 'admin_notice' );
		$this->priv( "license-activator-{$this->basename}", 'verify' );
	}

	public function activation() {
	    if ( !wp_next_scheduled ( "cron_{$this->slug}" ) ) {
			$interval = apply_filters( "{$this->slug}_cron_interval", 'daily' );
			wp_schedule_event( time(), $interval, "cron_{$this->slug}" );
	    }
	}

	public function deactivation() {
		wp_clear_scheduled_hook( "cron_{$this->slug}" );
	}

	public function cron_schedules( $schedules ) {
		$schedules['weekly'] = array(
			'interval'	=> WEEK_IN_SECONDS,
			'display'	=> __( 'weekly', 'codexpert' )
		);
		$schedules['biweekly'] = array(
			'interval'	=> 2 * WEEK_IN_SECONDS,
			'display'	=> __( 'biweekly', 'codexpert' )
		);
		$schedules['monthly'] = array(
			'interval'	=> MONTH_IN_SECONDS,
			'display'	=> __( 'monthly', 'codexpert' )
		);
		return $schedules;
	}

	public function enqueue_scripts() {
		wp_enqueue_style( 'codexpert-product-license', plugins_url( 'assets/css/license.css', __FILE__ ), [], $this->plugin['Version'] );
		wp_enqueue_script( 'codexpert-product-license', plugins_url( 'assets/js/license.js', __FILE__ ), [ 'jquery' ], $this->plugin['Version'], true );
	}

	public function activator_form() {
		$key = $this->basename;
		$value = get_option( $key );

		$_disabled = $this->_is_active() ? 'disabled' : '';
		$_type = $this->_is_active() ? 'password' : 'text';

		$html = "
		<div id='div_{$key}' class='{$this->slug}-activation-div cx-activation-panel'>
			<input type='{$_type}' id='{$key}' name='{$key}' value='{$value}' class='key-field' placeholder='" . __( 'Input your license key', 'codexpert' ) . "' {$_disabled} >
			<input type='hidden' name='plugin_key' value='{$key}' />
			<input type='button' name='activate_license' value='" . __( 'Activate', 'codexpert' ) . "' class='{$key}-btn cx-license-btn button-primary' data-slug='{$this->slug}' data-basename='{$key}' data-name='{$this->plugin['Name']}' />
			<input type='button' name='deactivate_license' value='" . __( 'Deactivate', 'codexpert' ) . "' class='{$key}-btn cx-license-btn button' data-slug='{$this->slug}' data-basename='{$key}' data-name='{$this->plugin['Name']}' />
			<span class='{$this->slug}-message cx-activation-message'></span>
		</div>
		";

		return $html;
	}

	public function verify() {
		$license_key = $_POST['key'];
        if ( isset( $_REQUEST['operation'] ) ) {
            $api_params = array(
                'slm_action'		=> ( $_REQUEST['operation'] != 'deactivate_license' ) ? 'slm_activate' : 'slm_deactivate',
                'secret_key'		=> $this->secret_key,
                'license_key'		=> $license_key,
                'registered_domain'	=> get_bloginfo( 'url' ),
                'item_reference'	=> urlencode( $this->plugin['Name'] ),
            );

            $query = esc_url_raw( add_query_arg( $api_params, $this->license_server ) );
            $response = wp_remote_get( $query, array( 'timeout' => 20, 'sslverify' => false ) );

            $data = array();

            if ( is_wp_error( $response ) ){
            	$data['status'] = 0;
                $data['message'] = __( 'Unexpected Error! Please try again or contact us.', 'codexpert' );
            }

            $license_data = json_decode( wp_remote_retrieve_body( $response ) );
            if( $license_data->result == 'success' ) {
            	$data['status'] = 1;
                $data['message'] = '<span style="color:#07811a">' . $license_data->message . '</span>';
                update_option( $this->basename, ( $_REQUEST['operation'] == 'deactivate_license' ) ? '' : $license_key );
				update_option( "{$this->basename}-status", 'active' );

				if( isset( $license_data->expiry ) ) {
					update_option( "{$this->basename}-expiry", strtotime( $license_data->expiry ) );
				}
            }
            else{
            	$data['status'] = 0;
                $data['message'] = '<span style="color:#C8080E">' . $license_data->message . '</span>';
            }

            wp_send_json( $data );
        }
	}

	public function check() {
		if( ( $lic_key = get_option( $this->basename ) ) == '' ) return;
		
		$args = array(
			'slm_action'	=> 'slm_check',
			'secret_key'	=> $this->secret_key,
			'license_key'	=> $lic_key
		);

		$api = add_query_arg( $args, $this->license_server );
		$response = wp_remote_get( $api, array( 'timeout' => 20, 'sslverify' => false ) );
		$data = json_decode( wp_remote_retrieve_body( $response ) );

		if( $data->result == 'error' ) {
			delete_option( $this->basename );
		}
		elseif( isset( $data->status ) && in_array( $data->status,  array( 'expired', 'blocked' ) ) ) {
			delete_option( $this->basename );
			update_option( "{$this->basename}-status", $data->status );
		}
		elseif( isset( $data->date_expiry ) && strtotime( $data->date_expiry ) < time() ) {
			delete_option( $this->basename );
			update_option( "{$this->basename}-status", 'expired' );
		}
		elseif( isset( $data->date_expiry ) && strtotime( $data->date_expiry ) > time() ) {
			update_option( "{$this->basename}-expiry", strtotime( $data->date_expiry ) );
		}
	}

    public function license_tab( $form ) {
        echo $this->activator_form();
    }

	public function admin_notice() {
		if( !current_user_can( 'manage_options' ) ) return;

		// not activated
		if( get_option( $this->basename ) == '' ) {
		    ?>
		    <div class="notice notice-error cx-notice">
		    	<div class="cx-notice-inner">
					<div class="cx-notice-icon">
						<div class="cx-logo-wrapper">
							<?php echo $this->icon; ?>
						</div>
					</div>
					<div class="cx-notice-content">
						<strong><?php printf( __( 'Welcome to %s', 'codexpert' ), $this->plugin['Name']); ?></strong>
						<?php 
							printf( __( '<p>Please activate your license for <strong>%s</strong>! Sorry, but the plugin won\'t work without activation!</p>', 'codexpert' ), $this->plugin['Name'] );
						?>
					</div>
					<div class="cx-notice-action">
						<?php echo "<a href='{$this->activator_url}' class='cx-button'>" . __( 'Activate Now', 'codexpert' ) . '</a>'; ?>
					</div>
				</div>
		    </div>
		    <?php
		}

		// about to expire?
		$expiry = (int)get_option( "{$this->basename}-expiry" );
		$day_left = round( ( $expiry - time() ) / DAY_IN_SECONDS );
		if( $expiry != '' && $day_left <= 30 ) :

		    ?>
		    <div class="notice cx-notice">
		    	<div class="cx-notice-inner">
					<div class="cx-notice-icon">
						<div class="cx-logo-wrapper">
							<?php echo $this->icon; ?>
						</div>
					</div>
					<div class="cx-notice-content">
						<strong><?php printf( __( 'Welcome to %s', 'codexpert' ), $this->plugin['Name'] ); ?></strong>
						<?php printf( __( '<p>The license for <strong>%1$s</strong> is about to expire in %2$d days! Please renew to get uninterrupted service!</p>', 'codexpert' ), $this->plugin['Name'], $day_left ); ?>
					</div>
					<div class="cx-notice-action">
						<?php echo "<a href='{$this->license_server}' class='cx-button' target='_blank'>" . __( 'Renew Now', 'codexpert' ) . '</a>'; ?>
					</div>
				</div>
		    </div>
		    <?php

		endif;

		// expired or blocked?
		$status = get_option( "{$this->basename}-status" );
		if( in_array( $status, array( 'expired', 'blocked' ) ) ) :

		    ?>
		    <div class="notice cx-notice">
		    	<div class="cx-notice-inner">
					<div class="cx-notice-icon">
						<div class="cx-logo-wrapper">
							<?php echo $this->icon; ?>
						</div>
					</div>
					<div class="cx-notice-content">
						<strong><?php printf( __( 'Welcome to %s!', 'codexpert' ), $this->plugin['Name'] ); ?></strong>
						<?php 
							printf( __( '<p><strong>%s</strong> was %s!</p>', 'codexpert' ), $this->plugin['Name'], $status );
						?>
					</div>
					<div class="cx-notice-action">
						<?php echo "<a href='{$this->license_server}' class='cx-button' target='_blank'>" . __( 'Renew Now', 'codexpert' ) . '</a>'; ?>
					</div>
				</div>
		    </div>
		    <?php

		endif;
	}

	public function _is_active() {
		return get_option( $this->basename ) != '';
	}

}