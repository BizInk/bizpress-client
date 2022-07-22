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
$myUpdateChecker->setBranch('master');

// Using a private repository, specify the access token 
$myUpdateChecker->setAuthentication('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'); // Token under Jayden's Account may need to change

// Using the Release Assets
$myUpdateChecker->getVcsApi()->enableReleaseAssets();

/** Load The main plugin */
require 'bizink-plugin.php';