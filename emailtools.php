<?php

/*
Plugin Name: Emailtools
Plugin URI: https://emailtools.ru/blog/emailtools-for-wordpress
Description:  Плагин автоматической интеграции интернет-магазина на woocommerce с сервисом брошенных корзин и триггерных рассылок https://emailtools.ru/
Version: 1.1
Author: Alex
Author URI: https://emailtools.ru/kontakty
License: A "Slug" license name e.g. GPL2
Text Domain: emailtools-for-wordpress
Domain Path: /languages
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'EMAILTOOLS_VERSION', '1.1' );
define ('EMAILTOOLS_URL', plugin_dir_url( __FILE__ ));
define ('EMAILTOOLS_PATH', plugin_dir_path( __FILE__ ));

define ('TERMS_URL', 'https://emailtools.ru/konfidentsialnost');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-emailtools-activator.php
 */
function activate_emailtools() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-emailtools-activator.php';
	Emailtools_Activator::activate();
}
register_activation_hook( __FILE__, 'activate_emailtools' );


/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-emailtools.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_emailtools() {
	
	$plugin = new Emailtools();
	$plugin->run();
}


run_emailtools();
