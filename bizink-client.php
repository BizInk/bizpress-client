<?php
/**
 * Plugin Name: BizPress
 * Description: Display business content on your website that is automatically updated by the Bizink team.
 * Plugin URI: https://bizinkonline.com
 * Author: Bizink
 * Author URI: https://bizinkonline.com
 * Version: 0.9
 * Text Domain: bizink-client
 * Domain Path: /languages
 */

/**
 * if accessed directly, exit.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin Updater
require 'plugin-update-checker/plugin-update-checker.php';

$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker('https://github.com/BizInk/bizpress-client',__FILE__,'bizpress-client');

// Set the branch that contains the stable release.
$myUpdateChecker->setBranch('jayden_updates');

// Using a private repository, specify the access token 
$myUpdateChecker->setAuthentication('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');

// Using the Release Assets
$myUpdateChecker->getVcsApi()->enableReleaseAssets();

/** Load The main plugin */
require 'bizink-plugin.php';