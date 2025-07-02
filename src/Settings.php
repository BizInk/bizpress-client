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
	public $slug;
	public $name;
	public $version;
	public $numberVersion;

	/**
	 * Constructor function
	 */
	public function __construct( $plugin ) {
		$this->plugin	= $plugin;
		$this->slug		= $this->plugin['TextDomain'];
		$this->name		= $this->plugin['Name'];
		$this->version	= $this->plugin['Version'];
		$this->numberVersion = intval(str_replace('.','',$this->version));
	}

	public function init_menu() {
		
		$luca = false;
		if(function_exists('luca')){
			$luca = true;
		}
		elseif(in_array('bizpress-luca-2/bizpress-luca-2.php', apply_filters('active_plugins', get_option('active_plugins')))){ 
			$luca = true;
		}

		$pluginInfo = '';
		$plugins = get_plugins();
		$bizpressPlugins = array(
			'bizpress-blogs' => false,
			'bizpress-accounting-glossary' => false, // Old plugin
			'bizpress-business-resources' => false,
			'bizpress-business-terms-glossary' => false,
			'bizpress-calculators' => false,
			'bizpress-forms' => false,
			'bizpress-key-dates' => false,
			'bizpress-myob-resources' => false,
			'bizpress-payroll' => false,
			'bizpress-quickbooks-resources' => false,
			'bizpress-xero-resources' => false,
			'bizpress-sage-resources' => false
		);
		foreach($plugins as $key=>$plugin){
			$pluginInfo .= "{$plugin['Name']} - Version:{$plugin['Version']} By:{$plugin['AuthorName']}\r\n";
			switch($key){
				case 'bizpress-blogs/bizpress-blogs.php':
					$bizpressPlugins['bizpress-blogs'] = true;
					break;
				case 'bizpress-accounting-glossary/bizpress-accounting-glossary.php':
					$bizpressPlugins['bizpress-accounting-glossary'] = true;
					break;
				case 'bizpress-business-resources/bizpress-business-resources.php':
					$bizpressPlugins['bizpress-business-resources'] = true;
					break;
				case 'bizpress-business-terms-glossary/bizpress-business-terms-glossary.php':
					$bizpressPlugins['bizpress-business-terms-glossary'] = true;
					break;
				case 'bizpress-calculators/bizpress-caculators.php':
					$bizpressPlugins['bizpress-calculators'] = true;
					break;
				case 'bizpress-forms/bizpress-forms.php':
					$bizpressPlugins['bizpress-forms'] = true;
					break;
				case 'bizpress-key-dates/bizpress-key-dates.php':
					$bizpressPlugins['bizpress-key-dates'] = true;
					break;
				case 'bizpress-myob-resources/bizpress-myob-resources.php':
					$bizpressPlugins['bizpress-myob-resources'] = true;
					break;
				case 'bizpress-payroll/bizpress-payroll.php':
					$bizpressPlugins['bizpress-payroll'] = true;
					break;
				case 'bizpress-quickbooks-resources/bizpress-quickbooks-resources.php':
					$bizpressPlugins['bizpress-quickbooks-resources'] = true;
					break;
				case 'bizpress-xero-resources/bizpress-xero-resources.php':
					$bizpressPlugins['bizpress-xero-resources'] = true;
					break;
				case 'bizpress-sage-resources/bizpress-sage-resources.php':
					$bizpressPlugins['bizpress-sage-resources'] = true;
					break;
			}
		}

		$options = get_option( 'bizink-client_basic' );
		if( (empty($options['user_email']) || empty($options['user_password'])) && $luca == false ):
			$current_user = wp_get_current_user();
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
						'label'     => __( 'Bizink Online - Login', 'bizink-client' ),
						'icon'      => 'dashicons-admin-generic',
						'color'		=> '#4c3f93',
						'sticky'	=> true,
						'page_load' => true,
						'submit_button' => __( 'Login', 'bizink-client' ),
						'reset_button'  => __( 'Reset', 'bizink-client' ),
						'fields'    => [
							'login_message' => [
								'id'      => 'login_message',
								'label'   => __( 'Login', 'bizink-client' ),
								'type'    => 'admin_message',
								'message' => __( 'Please login to your Bizink account to continue.', 'bizink-client' ),
							],
							'user_email' => [
								'id'          => 'user_email',
								'label'       => __( 'Email', 'bizink-client' ),
								'type'        => 'email',
								'placeholder' => 'hello@bizinkonline.com',
								'required'	  => true,
							],
							'user_password' => [
								'id'        => 'user_password',
								'label'     => __( 'Password', 'bizink-client' ),
								'type'      => 'password',
								'desc'      => __( 'Your Bizink account password.', 'bizink-client' ),
								'required'	=> true,
							],
						]
					]
				]
			];
			remove_filter('cx-settings-fields','xero_settings_fields');
			remove_filter('cx-settings-fields','myob_settings_fields');
			remove_filter('cx-settings-fields','quickbooks_settings_fields');
			remove_filter('cx-settings-fields','sage_settings_fields');
			remove_filter('cx-settings-fields','keydates_settings_fields');
			remove_filter('cx-settings-fields','payroll_settings_fields');
			remove_filter('cx-settings-fields','accounting_settings_fields');
			remove_filter('cx-settings-fields','business_settings_fields');
			remove_filter('cx-settings-fields','business_terms_settings_fields');

			remove_filter('cx-settings-sections','bizpress_caculator_settings');

		else:
		
		
		$landingpage_data = bizpress_landingpage_all();
		$pageItems = [];
		if(!empty($landingpage_data)):
			foreach($landingpage_data as $page){
				if(!empty($page)){
					if(!empty($page->slug) && !empty($page->title) && !empty($page->title->rendered)){
						$pageItems[$page->slug] = [
							'id' => $page->slug,
							'label' => $page->title->rendered,
							'type' => 'admin_shortcode',
							'shortcode' => '[bizpress-landing id="'.$page->slug.'"]',
							'copy' => true
						];
					}
				}
			}
		else:
			$pageItems = [
				'none' => [
					'id' => 'none',
					'label' => 'None Landing Pages',
					'type' => 'admin_message',
					'message' => __('Select a landing pages to show at this time.','bizpress-client'),
					'copy' => true
				]
			];
		endif;
		
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

		//$content_manager_fields = apply_filters('bizpress_content_manager_fields',array());
		//$content_manager_hide = empty($content_manager_fields);
		//$content_manager_hide = true; // Hidden - This is in BATA

		$bizpress_product = get_option('bizpress_product');
		if(empty($bizpress_product)){
			$bizpress_product = [
				'bizpress' => true,
				'bizpress_basic' => true,
				'bizpress_standard' => true
			];
		}
		else{
			$bizpress_product = (array) $bizpress_product;
		}
		$bispressPluginsScreen = [
			[
				'thumbnail' => plugin_dir_url(CXBPC).'assets/img/bizpress_glossary.svg',
				'name' => 'BizPress Accounting & Business Glossary',
				'description' => 'A glossary of accounting and business terms for your website. ',
				'url' => 'https://docs.google.com/uc?export=download&id=1dCQ6oYn3zKlYxth6jd8vQoL1aWHPBjyw',
				'plugin' => 'bizpress-business-terms-glossary/bizpress-business-terms-glossary.php',
				'installed' => $bizpressPlugins['bizpress-business-terms-glossary']
			],
			[
				'thumbnail' => plugin_dir_url(CXBPC).'assets/img/bizpress_keydates.svg',
				'name' => 'BizPress KeyDates',
				'description' => 'A glossary of Keydates.',
				'url' => 'https://docs.google.com/uc?export=download&id=16722aKAIFz2ANO4bcGDY5Xvm4H9BIKDG',
				'plugin' => 'bizpress-key-dates/bizpress-key-dates.php',
				'installed' => $bizpressPlugins['bizpress-key-dates']
			],
			[
				'thumbnail' => plugin_dir_url(CXBPC).'assets/img/bizpress_xero.svg',
				'name' => 'BizPress Xero Resources',
				'description' => 'A libary of resources for Xero.',
				'url' => 'https://docs.google.com/uc?export=download&id=1IIhd75FPrMgxC0fFa41FPo7b4sJJzt3L',
				'plugin' => 'bizpress-xero-resources/bizpress-xero-resources.php',
				'installed' => $bizpressPlugins['bizpress-xero-resources']
			],
			[
				'thumbnail' => plugin_dir_url(CXBPC).'assets/img/bizpress_quickbooks.svg',
				'name' => 'BizPress Quickbooks Resources',
				'description' => 'A libary of resources for Quickbooks.',
				'url' => 'https://docs.google.com/uc?export=download&id=1rBw2_13hh8vUnB7bS1LPzKSvVsQPOsu-',
				'plugin' => 'bizpress-quickbooks-resources/bizpress-quickbooks-resources.php',
				'installed' => $bizpressPlugins['bizpress-quickbooks-resources']
			],
			[
				'thumbnail' => plugin_dir_url(CXBPC).'assets/img/bizpress_sage.svg',
				'name' => 'BizPress Sage Resources',
				'description' => 'A libary of resources for Sage.',
				'url' => 'https://docs.google.com/uc?export=download&id=1hrD3OqVd74XgOBfFFerX6APoJ-kCtAz-',
				'plugin' => 'bizpress-sage-resources/bizpress-sage-resources.php',
				'installed' => $bizpressPlugins['bizpress-sage-resources']
			],
			[
				'thumbnail' => plugin_dir_url(CXBPC).'assets/img/bizpress_myob.svg',
				'name' => 'BizPress MYOB Resources',
				'description' => 'A libary of resources for MYOB.',
				'url' => 'https://docs.google.com/uc?export=download&id=1-kNdLNTRmXBvpb-NEWzCfselUmqSjxET',
				'plugin' => 'bizpress-myob-resources/bizpress-myob-resources.php',
				'installed' => $bizpressPlugins['bizpress-myob-resources']
			],
			
		];

		if($bizpress_product['bizpress_standard']){ // Either give Standard Items
			array_push($bispressPluginsScreen,[
				'thumbnail' => plugin_dir_url(CXBPC).'assets/img/bizpress_payroll.svg',
				'name' => 'BizPress Payroll Glossary',
				'description' => 'A libary of resources for your payroll company.',
				'url' => 'https://docs.google.com/uc?export=download&id=1vpd4pWseT0oR6Ie-SZyhiLVrXNmpGQ6L',
				'plugin' => 'bizpress-payroll/bizpress-payroll.php',
				'installed' => $bizpressPlugins['bizpress-payroll']
			]);
			array_push($bispressPluginsScreen,[
				'thumbnail' => plugin_dir_url(CXBPC).'assets/img/bizpress_caculators.svg',
				'name' => 'BizPress Calculators',
				'description' => 'A set of Calculators for your website.',
				'url' => 'https://docs.google.com/uc?export=download&id=1aN3beVHg2dyICNjnVgtJsq8r663TDDQo',
				'plugin' => 'bizpress-calculators/bizpress-caculators.php',
				'installed' => $bizpressPlugins['bizpress-calculators']
			]);
			array_push($bispressPluginsScreen,[
				'thumbnail' => plugin_dir_url(CXBPC).'assets/img/bizpress_blogs.svg',
				'name' => 'BizPress Blogs',
				'description' => 'A tool for importing Blogs to your website.',
				'url' => 'https://docs.google.com/uc?export=download&id=1Tz4JFRuD8ZRtluvtmPNUHpUKK7YOaBkM',
				'plugin' => 'bizpress-blogs/bizpress-blogs.php',
				'installed' => $bizpressPlugins['bizpress-blogs']
			]);
		}
		/**
		 * 
		 'bizink-client_contentmanager' => [
					'id'	=> 'bizink-client_contentmanager',
					'label' => __( 'Content Manager', 'bizink-client' ),
					'icon'	=> 'dashicons-media-document',
					'color'	=> '#4c3f93',
					'sticky'	=> false,
					'submit_button' => false,
					'reset_button' => false,
					'hide' => $content_manager_hide,
					'fields' => [
						'content_manager' => [
							'id' => 'content_manager',
							'hidelabel' => true,
							'label' => __( 'Content Manager', 'bizink-client' ),
							'type' => 'content_manager',
							'fields' => $content_manager_fields
						]
					]
				],
		 */
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
						'user_email' => [
							'id'      => 'user_email',
							'label'     => __( 'Email', 'bizink-client' ),
							'type'      => 'email',
							'desc'      => __( 'The email that you used to subscribe to Bizink.', 'bizink-client' ),
							'placeholder'   => 'hello@bizinkonline.com',
							'readonly' => true,
							'required'	=> true,
						],
						'user_password' => [
							'id'      => 'user_password',
							'label'     => __( 'Password', 'bizink-client' ),
							'type'      => 'password',
							'condition' => [],
							'desc'      => __( 'Your Bizink account password.', 'bizink-client' ),
							'required'	=> true,
						],

						'allow_editor' => [
							'id'      => 'allow-user',
							'label'     => __( 'Allow Editor role to access BizPress settings', 'bizink-client' ),
							'type'      => 'switch',
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
								'IE'=>'Ireland',
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
						],
						'cache' => [
							'id'      => 'cache',
							'label'     => __( 'Cache', 'bizink-client' ),
							'button'     => __( 'Clear Cache', 'bizink-client' ),
							'type'      => 'admin_button',
							'action'    => 'bizpress_clear_cache',
							'desc'      => __( 'Clear BizPress caches and load fresh content on next load.', 'bizink-client' ),
							'required'	=> false,
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
					'sticky'	=> false,
					'submit_button' => false,
					'reset_button' => false,
					'fields'    => $pageItems
				],
				'bizink-client_support' => [
					'id'        => 'bizink-client_support',
					'label'     => __( 'Support', 'bizink-client' ),
					'icon'      => 'dashicons-businesswoman',
					'color'		=> '#4c3f93',
					'sticky'	=> false,
					'submit_button' => false,
					'reset_button' => false,
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
				],
				'bizpress_plugins' => [
					'id'        => 'bizink-addons',
					'label'     => __( 'Addons', 'bizink-client' ),
					'icon'      => 'dashicons-admin-plugins',
					'color'		=> '#4c3f93',
					'sticky'	=> false,
					'submit_button' => false,
					'reset_button' => false,
					'fields'    => [
						'plugin_install_grid' => [
							'id' => 'plugin_install_grid',
							'label' => __( 'Bizink Addons', 'bizink-client' ),
							'type' => 'plugin_install_grid',
							'hidelabel' => true,
							'plugins' => $bispressPluginsScreen
						]
					]
				]
			],
		];

		if($luca) {
			unset($settings['sections']['bizink-client_basic']['fields']['user_email']);
			unset($settings['sections']['bizink-client_basic']['fields']['user_password']);
		}

		endif;

		new \codexpert\product\Settings( $settings );
	}
}