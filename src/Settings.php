<?php
/**
 * All settings related functions
 */
namespace codexpert\Bizink_Client;
use codexpert\product\Base;

/**
 * @package Plugin
 * @subpackage Settings
 * @author codexpert <hello@codexpert.io>
 */
class Settings extends Base {

	public $plugin;

	/**
	 * Constructor function
	 */
	public function __construct( $plugin ) {
		$this->plugin	= $plugin;
		$this->slug		= $this->plugin['TextDomain'];
		$this->name		= $this->plugin['Name'];
		$this->version	= $this->plugin['Version'];
	}
	
	public function init_menu() {
		
		$settings = [
			'id'            => $this->slug,
			'label'         => $this->name,
			'title'         => $this->name,
			'header'        => $this->name,
			'icon'			=> 'dashicons-image-filter',
			'position'		=> 6,
			'sections'      => [
				'bizink-client_basic'	=> [
					'id'        => 'bizink-client_basic',
					'label'     => __( 'Settings', 'bizink-client' ),
					'icon'      => 'dashicons-admin-tools',
					'color'		=> '#4c3f93',
					'sticky'	=> true,
					'fields'    => [
						// 'base_url' => [
						// 	'id'      	=> 'base_url',
						// 	'label'     => __( 'Master Site', 'bizink-client' ),
						// 	'type'      => 'url',
						// 	'desc'      => __( 'Input the base/home URL of the master site.', 'bizink-client' ),
						// 	'placeholder'   => 'https://codexpert.io',
						// 	'required'	=> true,
						// ],						
						'post_per_page' => [
							'id'      => 'post_per_page',
							'label'     => __( 'Posts Per Page', 'bizink-client' ),
							'type'      => 'number',
							'default'	=> 8,
							'required'	=> true,
						],
						'user_email' => [
							'id'      => 'user_email',
							'label'     => __( 'Bizink Email', 'bizink-client' ),
							'type'      => 'email',
							'desc'      => __( 'The email that you used to subscribe to Bizink.', 'bizink-client' ),
							'placeholder'   => 'hi@codexpert.io',
							'required'	=> true,
						],
						'user_password' => [
							'id'      => 'user_password',
							'label'     => __( 'Password', 'bizink-client' ),
							'type'      => 'password',
							'desc'      => __( 'Your Bizink password.', 'bizink-client' ),
							'required'	=> true,
						]
					]
				],
				'bizink-client_content'	=> [
					'id'        => 'bizink-client_content',
					'label'     => __( 'Default Content', 'bizink-client' ),
					'icon'      => 'dashicons-admin-tools',
					'color'		=> '#4c3f93',
					'sticky'	=> true,
					'fields'    => [
						[
	                        'id'		=> 'business',
	                        'label'		=> __( 'Bizink Client Business', 'bizink-client' ),
	                        'type'		=> 'divider',
	                    ],
						'business_title' => [
							'id'      	=> 'business_title',
							'label'     => __( 'Business Title', 'bizink-client' ),
							'type'      => 'text',
							'default'   => __( 'Business Resources', 'bizink-client' ),
							'required'	=> true,
						],
						'business_desc' => [
							'id'      	=> 'business_desc',
							'label'     => __( 'Business Description', 'bizink-client' ),
							'type'      => 'textarea',
							'default'   => __( 'Free resources to help you grow your business.', 'bizink-client' ),
							'required'	=> true,
						],
						[
	                        'id'		=> 'xero',
	                        'label'		=> __( 'Bizink Client Xero', 'bizink-client' ),
	                        'type'		=> 'divider',
	                    ],
						'xero_title' => [
							'id'      	=> 'xero_title',
							'label'     => __( 'Xero Title', 'bizink-client' ),
							'type'      => 'text',
							'default'   => __( 'Xero Resources', 'bizink-client' ),
							'required'	=> true,
						],
						'xero_desc' => [
							'id'      	=> 'xero_desc',
							'label'     => __( 'Xero Description', 'bizink-client' ),
							'type'      => 'textarea',
							'default'   => __( 'Free resources to help you use Xero.', 'bizink-client' ),
							'required'	=> true,
						],
					]
				],
			],
		];

		new \codexpert\product\Settings( $settings );
	}
}