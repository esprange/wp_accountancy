<?php
/**
 * PHPUnit bootstrap file
 *
 * Bootstrap voor uitvoering van unit testen van de kleistad plugin.
 *
 * @link       https://www.kleistad.nl
 * @since      6.16.6
 *
 * @package    Kleistad
 * @file Bootstrap.php
 */

/**
 * Een aantal opstart acties.
 */
const WPACC_TEST = true;

// disable xdebug backtrace.
if ( function_exists( 'xdebug_disable' ) ) {
	xdebug_disable();
}

if ( false !== getenv( 'WP_PLUGIN_DIR' ) ) {
	define( 'WP_PLUGIN_DIR', getenv( 'WP_PLUGIN_DIR' ) );
}

if ( false !== getenv( 'WP_DEVELOP_DIR' ) ) {
	/**
	 * Suppress de phpstorm foutmelding
	 *
	 * @noinspection PhpIncludeInspection
	 */
	require getenv( 'WP_DEVELOP_DIR' ) . 'tests/phpunit/includes/Bootstrap.php';
}

require dirname( __FILE__, 2 ) . '\wp-accountancy.php';

tests_add_filter(
	'plugins_loaded',
	function() {
		if ( ! is_plugin_active( 'wp-accountancy/wp-accountancy.php' ) ) {
			exit( 'Some Plugin must be active to run the tests.' . PHP_EOL );
		}
	}
);

do_action( 'init' );
