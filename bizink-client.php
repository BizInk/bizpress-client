<?php
/**
 * Plugin Name: BizPress
 * Description: Display business content on your website that is automatically updated by the Bizink team.
 * Plugin URI: https://bizinkonline.com
 * Author: Bizink
 * Author URI: https://bizinkonline.com
 * Version: 1.7.7
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
// Set the branch that contains the stable release.
$myUpdateChecker->setBranch('master');
// Using a private repository, specify the access token 
$myUpdateChecker->setAuthentication('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');

define('BIZINK_ANALYTICS_URL', 'https://analytics.biz.press/api/v1');
/** Load The main plugin */
require 'bizink-plugin.php';