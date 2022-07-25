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
 * Returns the plugin version
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
 * Generic call for error reporting.
 *
 * @param string $object  Object at which the error occurs.
 * @param string $message The error message.
 */
function error( string $object, string $message ) : void {
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
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->define_addons_hooks();
		$this->loader->run();
	}

	/**
	 * Load dependencies
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() : void {
		$this->loader = new Loader();
	}

	/**
	 * Register all Admin hooks.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @noinspection PhpFullyQualifiedNameUsageInspection
	 */
	private function define_admin_hooks() : void {
		$plugin_actions = new \WP_Accountancy\Admin\Actions();

		$this->loader->add_action( 'admin_init', $plugin_actions, 'initialize' );
		$this->loader->add_action( 'admin_menu', $plugin_actions, 'add_plugin_admin_menu' );
	}

	/**
	 * Register all Public hooks.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @noinspection PhpFullyQualifiedNameUsageInspection
	 */
	private function define_public_hooks() : void {
		$plugin_actions = new \WP_Accountancy\Public\Actions();

		$this->loader->add_action( 'init', $plugin_actions, 'add_shortcode' );
		$this->loader->add_action( 'wp_ajax_wpacc_formhandler', $plugin_actions, 'formhandler' );
		$this->loader->add_action( 'wp_ajax_wpacc_menuhandler', $plugin_actions, 'menuhandler' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_actions, 'load_script' );
		$this->loader->add_action( 'wpacc_business_select', $plugin_actions, 'business_select' );
	}

	/**
	 * Register Addons hooks.
	 *
	 * @return void
	 */
	private function define_addons_hooks() : void {
		$addons = get_option( 'wpacc_addons', [] );
		foreach ( $addons as $addon ) {
			$addon_class = "WP_Accountancy\Addons\{$addon}";
			if ( class_exists( $addon_class ) ) {
				$addon_object = new $addon_class();
				$addon_object->define_hooks();
			}
		}
	}

	/**
	 * Load the textdomain.
	 *
	 * @internal Action for plugins_loaded
	 */
	public function load_plugin_textdomain() : void {
		load_plugin_textdomain(
			'wpacc',
			false,
			'wp_accountancy/languages/'
		);
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

	/**
	 * Generic action for localization
	 */
	private function set_locale() : void {
		$this->loader->add_action( 'plugins_loaded', $this, 'load_plugin_textdomain' );
	}


}
