<?php
/**
 * Plugin Name:       WP-Accountancy
 * Plugin URI:        http://github.com/esprnge/wp-accountancy
 * Description:       Accountancy plugin for Wordpress.
 * Version:           1.0
 * Author:            Eric Sprangers
 * Author URI:        http://URI_Of_The_Plugin_Author
 * License:           GPL-3.0+
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       wp-accountancy
 * Requires at least: 4.8.0
 * Requires PHP:      8.0
 * GitHub Plugin URI: https://github.com/esprange/wp-accountancy
*/

namespace WP_Accountancy;

if ( ! defined( 'WPINC' ) ) {
	die;
}

const WP_ACCOUNTANCY_API = 'wpacc_api';

/**
 * De autoloader toevoegen.
 *
 * @noinspection PhpIncludeInspection
 */
require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

/**
 * Plugin activering.
 */
register_activation_hook(
	__FILE__,
	function() {
		Activator::activate();
	}
);

/**
 * Plugin deactivering.
 */
register_deactivation_hook(
	__FILE__,
	function() {
		Deactivator::deactivate();
	}
);

/**
 * Start uitvoering van de plugin.
 */
$wpacc_plugin = new WPAcc();
$wpacc_plugin->run();
