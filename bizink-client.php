<?php
/**
 * Plugin Name: BizPress
 * Description: Display business content on your website that is automatically updated by the Bizink team.
 * Plugin URI: https://bizinkonline.com
 * Author: Bizink
 * Author URI: https://bizinkonline.com
 * Version: 1.8.9
 * Requires PHP: 7.4
 * Requires at least: 6.0
 * Text Domain: bizink-client
 * Domain Path: /languages
 */

/**
 * if accessed directly, exit.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if(!defined('CXBPC')){
	define( 'CXBPC', __FILE__ );
}

// Plugin Updater
require 'plugin-update-checker/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;
$myUpdateChecker = PucFactory::buildUpdateChecker('https://github.com/BizInk/bizpress-client',__FILE__,'bizpress-client');
$myUpdateChecker->setBranch('master');
$myUpdateChecker->setAuthentication('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');

define('BIZINK_ANALYTICS_URL', 'https://analytics.biz.press/api/v1');
/** Load The main plugin */
require 'bizink-plugin.php';