<?php
/**
 * Plugin Name:       WP-Accountancy
 * Plugin URI:        http://github.com/esprnge/wp-accountancy
 * Description:       Accountancy plugin for WordPress.
 * Version:           1.0
 * Author:            Eric Sprangers
 * Author URI:        http://URI_Of_The_Plugin_Author
 * License:           GPL-3.0+
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       wpacc
 * Domain path:       /languages
 * Requires at least: 4.8.0
 * Requires PHP:      8.0
 * GitHub Plugin URI: https://github.com/esprange/wp-accountancy
 *
 * @package WP-Accountancy
 */

use WP_Accountancy\Includes\Activator;
use WP_Accountancy\Includes\Deactivator;
use WP_Accountancy\Includes\Accountancy;

if ( ! defined( 'WPINC' ) ) {
	die;
}

const WP_ACCOUNTANCY_API = 'wpacc_api';

/**
 * Add the autoloader.
 */
require_once __DIR__ . '/vendor/autoload.php';

/**
 * Plugin activation.
 */
register_activation_hook(
	__FILE__,
	function() {
		Activator::activate();
	}
);

/**
 * Plugin deactivation.
 */
register_deactivation_hook(
	__FILE__,
	function() {
		Deactivator::deactivate();
	}
);

/**
 * Define some constants.
 */
define( 'WPACC_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
const WPACC_SLUG     = 'wpacc';
const WPACC_BUSINESS = 'wpacc_business_';

/**
 * Plugin execution.
 */
( new Accountancy() );
