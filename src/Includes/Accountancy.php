<?php
/**
 * Definition of the core WP Accountancy plugin class
 *
 * @since      1.0.0
 *
 * @package    WP-Accountancy
 * @subpackage WP-Accountacy/Includes
 */

namespace WP_Accountancy\Includes;

/**
 * Plugin options.
 *
 * @since     1.0.0
 * @return    array    The options.
 */
function options() : array {
	static $options = [];
	if ( empty( $options ) ) {
		$options = get_option( 'wpacc-options', [] );
	}
	return $options;
}

/**
 * Technical setup parameters of the plugin.
 *
 * @since     1.0.0
 * @return    array    Setup data.
 */
function setup() : array {
	static $setup = [];
	if ( empty( $setup ) ) {
		$setup = get_option( 'wpacc-setup', [] );
	}
	return $setup;
}

/**
 * Plugin version
 *
 * @return string The version.
 */
function version() : string {
	static $version = '';
	if ( empty( $version ) ) {
		$version = get_option( 'wpacc-plugin-version', '1.0.0' );
	}
	return $version;
}

/**
 * Create notification for the user.
 *
 * @param int    $status  1 success, 0 error, -1 information.
 * @param string $message The message.
 * @return string Html text.
 * @noinspection PhpUnnecessaryCurlyVarSyntaxInspection
 */
function notify( int $status, string $message ) : string {
	$levels = [
		-1 => 'wpacc-inform',
		0  => 'wpacc-fout',
		1  => 'wpacc-succes',
	];
	return "<div class=\"{$levels[$status]}\"><p>$message</p></div>";
}

/**
 * Generic call for error reporting.
 *
 * @param string $object  Object at which the error occurs.
 * @param string $message The error message.
 */
function error( string $object, string $message ) {
	error_log( "wpacc $object: $message" ); // phpcs:ignore
}

/**
 * Return base url terug for endpoints.
 *
 * @return string url for endpoints
 */
function base_url() : string {
	return rest_url( WP_ACCOUNTANCY_API );
}

/**
 * The Accountancy plugin class.
 *
 * @since      1.0.0
 */
class Accountancy {

	/**
	 * The loader for registering all hooks.
	 *
	 * @since    1.0.0
	 *
	 * @access   protected
	 * @var      Loader    $loader    Manage all hooks.
	 */
	protected Loader $loader;

	/**
	 * Constructor
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		$this->load_dependencies();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->loader->run();
	}

	/**
	 * Load dependencies
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {
		$this->loader = new Loader();
	}

	/**
	 * Register all Admin hooks.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @noinspection PhpFullyQualifiedNameUsageInspection
	 */
	private function define_admin_hooks() {
		$plugin_filters = new \WP_Accountancy\Admin\Filters();
		$plugin_actions = new \WP_Accountancy\Admin\Actions();

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_actions, 'enqueue_scripts_and_styles' );
		$this->loader->add_action( 'admin_menu', $plugin_actions, 'add_plugin_admin_menu' );
		$this->loader->add_action( 'admin_init', $plugin_actions, 'initialize' );
	}

	/**
	 * Register all Public hooks.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @noinspection PhpFullyQualifiedNameUsageInspection
	 */
	private function define_public_hooks() {
		$plugin_filters = new \WP_Accountancy\Public\Filters();
		$plugin_actions = new \WP_Accountancy\Public\Actions();

		$this->loader->add_action( 'init', $plugin_actions, 'load_translations' );
	}

	/**
	 * Referentie to the loading class.
	 *
	 * @since     1.0.0
	 * @return    Loader    de loader.
	 */
	public function get_loader() : Loader {
		return $this->loader;
	}

}
