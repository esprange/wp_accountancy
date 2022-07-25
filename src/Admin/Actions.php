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
	 * Define the Admin panel
	 *
	 * @since    1.0.0
	 *
	 * @internal Action for admin_menu.
	 */
	public function add_plugin_admin_menu() : void {
		add_menu_page( __( 'Configuration', 'wpacc' ), 'WP-Accountancy', 'manage_options', 'wpacc', [ $this->confighandler, 'display_settings_page' ], plugins_url( '/images/wpacc.png', __FILE__ ), 30 );
		add_submenu_page( 'wpacc', __( 'Configuration', 'wpacc' ), __( 'Configuration', 'wpacc' ), 'manage_options', 'wpacc', null );
	}

	/**
	 * Register settings.
	 *
	 * @since   1.0.0
	 *
	 * @internal Action for admin_init.
	 */
	public function initialize() : void {
		$upgrade = new Upgrade();
		$upgrade->run();
		if ( ! wp_next_scheduled( 'wpacc_daily_jobs' ) ) {
			wp_schedule_event( strtotime( '08:00' ), 'daily', 'wpacc_daily_jobs' );
		}
		register_setting( 'wpacc-options', 'wpacc-options', [ 'sanitize_callback' => [ $this->confighandler, 'validate_settings' ] ] );
		register_setting( 'wpacc-setup', 'wpacc-setup', [ 'sanitize_callback' => [ $this->confighandler, 'validate_settings' ] ] );
	}

}
