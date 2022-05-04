<?php
/**
 * The Admin actions of the plugin.
 *
 * @since      1.0.0
 *
 * @package    WP-Accountancy
 * @subpackage WP-Accountancy/Admin
 */

namespace WP_Accountancy\Admin;

use function WP_Accountancy\Includes\version;

/**
 * The Admin actions of the plugin.
 */
class Actions {

	/**
	 *  Configuration management
	 *
	 * @since     1.0.0
	 * @access    private
	 * @var       object    $confighandler  Handler to manage the configuration.
	 */
	private object $confighandler;

	/**
	 * Initialize the object.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		$this->confighandler = new ConfigHandler();
	}

	/**
	 * Register Admin stylesheets.
	 *
	 * @since    1.0.0
	 *
	 * @internal Action for admin_enqueue_scripts.
	 */
	public function enqueue_scripts_and_styles() {
		wp_enqueue_script( 'wpacc_admin', plugin_dir_url( __FILE__ ) . 'js/admin.js', [ 'jquery' ], version(), false );
	}

	/**
	 * Define the Admin panel
	 *
	 * @since    1.0.0
	 *
	 * @internal Action for admin_menu.
	 */
	public function add_plugin_admin_menu() {
		add_menu_page( __( 'Configuration', 'wpacc' ), 'WP-Accountancy', 'manage_options', 'wpacc', [ $this->confighandler, 'display_settings_page' ], plugins_url( '/images/wpacc_icon.png', __FILE__ ), 30 );
		add_submenu_page( 'wpacc', __( 'Configuration', 'wpacc' ), __( 'Configuration', 'wpacc' ), 'manage_options', 'wpacc', null );
	}

	/**
	 * Register settings.
	 *
	 * @since   1.0.0
	 *
	 * @internal Action for admin_init.
	 */
	public function initialize() {
		$upgrade = new Upgrade();
		$upgrade->run();
		if ( ! wp_next_scheduled( 'wpacc_daily_jobs' ) ) {
			wp_schedule_event( strtotime( '08:00' ), 'daily', 'wpacc_daily_jobs' );
		}
		register_setting( 'wpacc-options', 'wpacc-options', [ 'sanitize_callback' => [ $this->confighandler, 'validate_settings' ] ] );
		register_setting( 'wpacc-setup', 'wpacc-setup', [ 'sanitize_callback' => [ $this->confighandler, 'validate_settings' ] ] );
	}

}