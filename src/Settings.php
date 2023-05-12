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
		
		$pluginInfo = '';
		$plugins = get_plugins();
		foreach($plugins as $plugin){
			$pluginInfo .= "{$plugin['Name']} - Version:{$plugin['Version']} By:{$plugin['AuthorName']}\r\n";
		}

		$supportData = [
			'siteUrl' => get_bloginfo('wpurl'),
			'homeUrl' => get_bloginfo('url'),
			'phpVersion' => phpversion(),
			'pluginVersion' => $this->version,
			'wpVersion' => get_bloginfo('version'),
			'wpLang' => get_bloginfo('language'),
			'user' => wp_get_current_user(),
			'siteTitle' => get_bloginfo('name'),
			'plugins' => $pluginInfo
		];

		$settings = [
			'id'            => $this->slug,
			'label'         => $this->name,
			'title'         => $this->name,
			'header'        => $this->name,
			'icon'			=> 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIGlkPSJmNjU0YmNiZS03MzYwLTQxZGUtOWM3ZC1lYjE3ODcwYmRjOGYiIGRhdGEtbmFtZT0iTGF5ZXIgMSIgdmlld0JveD0iMTc5Ljc3IDE1MC4xOSAyOTcuNDEgMjM0LjQ1Ij48ZGVmcz48c3R5bGU+LmE2OTM0ZWRiLWVkZGItNDlhYi1iNjljLTg0NDllMzkzODBlZXtmaWxsOiMzMzNiNjE7fS5lYzBhYzQ1OS05ZDU5LTQ2ZDItODU5NC05ZGY1ZDcwM2Q2MDV7ZmlsbDojZjdhODAwO308L3N0eWxlPjwvZGVmcz48cGF0aCBjbGFzcz0iYTY5MzRlZGItZWRkYi00OWFiLWI2OWMtODQ0OWUzOTM4MGVlIiBkPSJNMjM5LjI1LDIzMy42MmM0Ny43LTI4Ljc3LDEwNywzLjY0LDEwOC42NSw1Ny44My42OCwyMS44Mi0xLjUsNDIuOTMtMTUuMTYsNjAuODEtMTkuMzgsMjUuMzktNDUuNTQsMzUuNTQtNzcsMzAtNy41MS0xLjMyLTE0LjM1LTYuNDctMjIuODctMTAuNTEtOC4yMywxMS42NC0yMS4yMywxNS4yNS0zNy42NywxMS40MXYtOS4zOHEwLTg5LjkzLDAtMTc5Ljg1YzAtMTAuMDYtLjEtMTkuNTItMTIuMzQtMjMuNTMtMS44My0uNi0xLjkyLTYuNDQtMy4wOS0xMC45LDE4Ljc4LDAsMzYtLjE1LDUzLjE4LjA4LDUuNTUuMDgsNi4yOSw0LjQsNi4yOCw4LjkzcS0uMDYsMjcuNDgsMCw1NC45NVptMCw3MC40YzAsMTYuMzYtLjE0LDMyLjcyLjEzLDQ5LjA4LjA1LDMuMDcuODksNi43OSwyLjc1LDkuMDgsMTEuODksMTQuNTksMzEuNDMsMTMuODYsNDIuMjItMS40Nyw4Ljc2LTEyLjQ0LDExLjUtMjYuODIsMTIuMzYtNDEuNjIsMS4wOC0xOC42Ny40MS0zNy4xOC04LjgzLTU0LjE1LTktMTYuNDUtMjYuNDctMjMuODktNDIuNzctMTktNC40OSwxLjM1LTYsMy40OC02LDguMThDMjM5LjQyLDI3MC43NSwyMzkuMjUsMjg3LjM5LDIzOS4yNSwzMDRaIi8+PHBhdGggY2xhc3M9ImVjMGFjNDU5LTlkNTktNDZkMi04NTk0LTlkZjVkNzAzZDYwNSIgZD0iTTQxNy4xOCwyMTcuNDRhNjUuNzksNjUuNzksMCwwLDAsMjMuNjctNC4zNGMxMC42LTQsMTkuOTMtMTAuMTIsMjguNjItMTcuMzMsNS43Ni00Ljc4LDguMDctMTAuODUsNy42Ni0xOC4xMmEyMC4wOSwyMC4wOSwwLDAsMC00LjQ0LTEyQTIyLjA2LDIyLjA2LDAsMCwwLDQ0MiwxNjIuMzFhODQuMjcsODQuMjcsMCwwLDEtMTQuMzIsOS4yLDIxLjU2LDIxLjU2LDAsMCwxLTEzLjQzLDIuMjUsNDAuNjYsNDAuNjYsMCwwLDEtMTQuODEtNS4yNGMtNS4zNi0zLjMxLTEwLjczLTYuNjItMTYuMjQtOS42OGE2NS43OSw2NS43OSwwLDAsMC0zMC40OS04LjYxLDQ4LjYyLDQ4LjYyLDAsMCwwLTE5LjczLDRjLTguODEsMy41Ny0xNi43LDguNzUtMjQuNTYsMTRhMjYuMDcsMjYuMDcsMCwwLDAtNy41OCw3LjM2Yy0zLjI2LDQuOTMtNC43NCwxMC4zLTMuNjcsMTYuMmEyMC4xNSwyMC4xNSwwLDAsMCw3LjU0LDEyLjEyYzcuMTIsNS44NSwxNy41OCw3LjgzLDI2LjE3LDEuNjNhMTE4LjE0LDExOC4xNCwwLDAsMSwxOC44NS0xMS4wNyw2LjU0LDYuNTQsMCwwLDEsMy4wNi0uNjcsMTcuNTEsMTcuNTEsMCwwLDEsNy44NCwyLjM2YzUuMjIsMy4wNywxMC4zNyw2LjI3LDE1LjYsOS4zMkE4OS4yNyw4OS4yNywwLDAsMCw0MTcuMTgsMjE3LjQ0WiIvPjxwYXRoIGNsYXNzPSJlYzBhYzQ1OS05ZDU5LTQ2ZDItODU5NC05ZGY1ZDcwM2Q2MDUiIGQ9Ik00MTcuMTgsMjE3LjQ0YTg5LjI3LDg5LjI3LDAsMCwxLTQxLTEyYy01LjIzLTMuMDUtMTAuMzgtNi4yNS0xNS42LTkuMzJhMTcuNTEsMTcuNTEsMCwwLDAtNy44NC0yLjM2LDYuNTQsNi41NCwwLDAsMC0zLjA2LjY3LDExOC4xNCwxMTguMTQsMCwwLDAtMTguODUsMTEuMDdjLTguNTksNi4yLTE5LDQuMjItMjYuMTctMS42M2EyMC4xNSwyMC4xNSwwLDAsMS03LjU0LTEyLjEyYy0xLjA3LTUuOS40MS0xMS4yNywzLjY3LTE2LjJhMjYuMDcsMjYuMDcsMCwwLDEsNy41OC03LjM2YzcuODYtNS4yMiwxNS43NS0xMC40LDI0LjU2LTE0YTQ4LjYyLDQ4LjYyLDAsMCwxLDE5LjczLTQsNjUuNzksNjUuNzksMCwwLDEsMzAuNDksOC42MWM1LjUxLDMuMDYsMTAuODgsNi4zNywxNi4yNCw5LjY4YTQwLjY2LDQwLjY2LDAsMCwwLDE0LjgxLDUuMjQsMjEuNTYsMjEuNTYsMCwwLDAsMTMuNDMtMi4yNSw4NC4yNyw4NC4yNywwLDAsMCwxNC4zMi05LjIsMjIuMDYsMjIuMDYsMCwwLDEsMzAuNzMsMy4zOCwyMC4wOSwyMC4wOSwwLDAsMSw0LjQ0LDEyYy40MSw3LjI3LTEuOSwxMy4zNC03LjY2LDE4LjEyLTguNjksNy4yMS0xOCwxMy4zMS0yOC42MiwxNy4zM0E2NS43OSw2NS43OSwwLDAsMSw0MTcuMTgsMjE3LjQ0WiIvPjwvc3ZnPg==',
			'position'		=> 6,
			'sections'      => [
				'bizink-client_basic'	=> [
					'id'        => 'bizink-client_basic',
					'label'     => __( 'Settings', 'bizink-client' ),
					'icon'      => 'dashicons-admin-generic',
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
						// 'post_per_page' => [
						// 	'id'      => 'post_per_page',
						// 	'label'     => __( 'Posts Per Page', 'bizink-client' ),
						// 	'type'      => 'number',
						// 	'default'	=> 8,
						// 	'required'	=> true,
						// ],
						'user_email' => [
							'id'      => 'user_email',
							'label'     => __( 'Bizink Email', 'bizink-client' ),
							'type'      => 'email',
							'desc'      => __( 'The email that you used to subscribe to Bizink.', 'bizink-client' ),
							'placeholder'   => 'hello@codexpert.io',
							'required'	=> true,
						],
						'user_password' => [
							'id'      => 'user_password',
							'label'     => __( 'Password', 'bizink-client' ),
							'type'      => 'password',
							'desc'      => __( 'Your Bizink password.', 'bizink-client' ),
							'required'	=> true,
						],
						'allow_editor' => [
							'id'      => 'allow-user',
							'label'     => __( 'Allow Editor role to access BizPress settings', 'bizink-client' ),
							'type'      => 'checkbox',
							'desc'      => __( 'Allow Editor role to access BizPress settings.', 'bizink-client' ),
							'required'	=> false,
						],
						'content_region' => [
							'id'      => 'content_region',
							'label'     => __( 'Content Region', 'bizink-client' ),
							'type'      => 'select',
							'options'   => [
								''=>' - Choose a region - ',
								'AU'=>'Australia',
								'CA'=>'Canada',
								'NZ'=>'New Zealand',
								'GB'=>'United Kingdom',
								'IE'=>'Irland',
								'US'=>'United States of America',
								'all'=>'Other'
							],
							'default'	=> '',
							'desc'      => __( 'Select you content region.', 'bizink-client' ),
							'required'	=> true,
						],
						'content_shortcode' => [
							'id' => 'landingpage_xero',
							'label' => __( 'Display Content using the shortcode', 'bizink-client' ),
							'type' => 'admin_shortcode',
							'shortcode' => '[bizpress-content]',
							'copy' => true
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
												
					]
				],
				'bizink-client_landingpages' => [
					'id' 	=> 'bizink-client_landingpages',
					'label' => __( 'Landing Pages', 'bizink-client' ),
					'icon'	=> 'dashicons-media-document',
					'color'	=> '#4c3f93',
					'sticky'	=> true,
					'fields'    => [
						'xero_shortcode' => [
							'id' => 'landingpage_xero',
							'label' => __( 'Xero landing page', 'bizink-client' ),
							'type' => 'admin_shortcode',
							'shortcode' => '[bizpress-landing id="xero-landing-page"]',
							'copy' => true
						]
					]
				],
				'bizink-client_support' => [
					'id'        => 'bizink-client_support',
					'label'     => __( 'Support', 'bizink-client' ),
					'icon'      => 'dashicons-businesswoman',
					'color'		=> '#4c3f93',
					'sticky'	=> true,
					'fields'    => [
						'admin_support_message' => [
							'id' => 'support_message',
							'label' => __( 'Bizpress Support', 'bizink-client' ),
							'type' => 'admin_message',
							'message' => 'Please feel free to contact us with any support quries you may have about BizPress. Read our <a target="_blank" href="https://support.bizinkonline.com/">support documentation</a> or contact our support team through the <a target="_blank" href="https://support.bizinkonline.com/">Bizink knowledge base.</a>'
						],
						'admin_html' => [
							'id' => 'support_form',
							'label' => __( 'Contact Our Team', 'bizink-client' ),
							'type' => 'admin_html',
							'html' => '<script charset="utf-8" type="text/javascript" src="//js.hsforms.net/forms/v2.js"></script>
							<script>
							  var supportData = '.json_encode($supportData).';
							  //console.log("Support Data",supportData);
							  hbspt.forms.create({
								region: "na1",
								portalId: "5917474",
								formId: "eeade17d-948d-4c0f-94a0-d5393b7598e8",
								onFormReady: function($form) {
									$form.find(\'input[name="company"]\').val(supportData["siteTitle"]).change();
									$form.find(\'input[name="email"]\').val(supportData["user"]["data"]["user_email"]).change();
								},							
								onFormSubmit: function($form) {
									$form.find(\'input[name="bizpress_version"]\').val(supportData["pluginVersion"]).change();
									$form.find(\'input[name="bizpress_home_url"]\').val(supportData["homeUrl"]).change();
									$form.find(\'input[name="bizpress_url"]\').val(supportData["siteUrl"]).change();
									$form.find(\'input[name="bizpress_wordpress_version"]\').val(supportData["wpVersion"]).change();
									$form.find(\'input[name="bizpress_language"]\').val(supportData["wpLang"]).change();
									$form.find(\'input[name="bizpress_sitename"]\').val(supportData["siteTitle"]).change();
									$form.find(\'input[name="bizpress_website_plugins"]\').val(supportData["plugins"]).change();
								} 
							  });
							</script>'
						]
					]
				]
			],
		];

		$luca = false;
		if(function_exists('luca')){
			$luca = true;
		}
		elseif(in_array('bizpress-luca-2/bizpress-luca-2.php', apply_filters('active_plugins', get_option('active_plugins')))){ 
			$luca = true;
		}

		if($luca) {
			unset($settings['sections']['bizink-client_basic']['fields']['user_email']);
			unset($settings['sections']['bizink-client_basic']['fields']['user_password']);
		}

		new \codexpert\product\Settings( $settings );
	}
}