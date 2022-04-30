<?php
/**
 * The admin actions of the plugin.
 *
 * @since      1.0.0
 *
 * @package    WP-Accountancy
 * @subpackage WP-Accountancy/admin
 */

namespace WP_Accountancy\Admin;

use WP_Accountancy\Includes;

/**
 * The admin actions of the plugin.
 */
class Actions {

	/**
	 *  Configuration management
	 *
	 * @since     1.0.0
	 * @access    private
	 * @var       object    $config_handler  Handler to manage the configuration.
	 */
	private object $config_handler;

	/**
	 * Background object
	 *
	 * @since   1.0.0
	 * @access  private
	 * @var     object|null $background The background object.
	 */
	private ?object $background = null;

	/**
	 * Initialize the object.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		$this->config_handler = new config_Handler();
	}

	/**
	 * Register admin stylesheets.
	 *
	 * @since    1.0.0
	 *
	 * @internal Action for admin_enqueue_scripts.
	 */
	public function enqueue_scripts_and_styles() {
		wp_enqueue_script( 'wpacc_admin', plugin_dir_url( __FILE__ ) . 'js/admin.js', [ 'jquery' ], \WP_Accountancy\Includes\version(), false );
	}

	/**
	 * Define the admin panel
	 *
	 * @since    1.0.0
	 *
	 * @internal Action for admin_menu.
	 */
	public function add_plugin_admin_menu() {
		add_menu_page( __( 'Configuration', 'wpacc' ), 'WP-Accountancy', 'manage_options', 'wpacc', [ $this->config_handler, 'display_settings_page' ], plugins_url( '/images/wpacc_icon.png', __FILE__ ), 30 );
		add_submenu_page( 'wpacc', __( 'Configuration', 'wpacc' ), __( 'Configuration', 'wpacc' ), 'manage_options', 'wpacc', null );
	}

	/**
	 * Bereid het background proces voor.
	 *
	 * @internal Action for plugins_loaded.
	 */
	public function instantiate_background() {
		if ( is_null( $this->background ) ) {
			// $this->background = new Background();
		}
	}

	/**
	 * Perform daily jobs
	 *
	 * @internal Action for WPAcc_daily_jobs.
	 */
	public function daily_jobs() {
		if ( is_null( $this->background ) ) {
			return;
		}
		// $this->background->push_to_queue( 'Shortcode::cleanup_downloads' );
		$this->background->save()->dispatch();
	}

	/**
	 * Perform gdpr cleaning (every last day of month).
	 *
	 * @internal Action for WPAcc_daily_gdpr.
	 */
	public function daily_gdpr() {
		if ( idate( 'd' ) === idate( 't' ) ) {
			// $gdpr = new GDPR_Erase();
			//     $gdpr->erase_old_privacy_data();
		}
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
		if ( ! wp_next_scheduled( 'wpacc_daily_gdpr' ) ) {
			wp_schedule_event( strtotime( '01:00' ), 'daily', 'wpacc_daily_gdpr' );
		}
		register_setting( 'wpacc-options', 'wpacc-options', [ 'sanitize_callback' => [ $this->config_handler, 'validate_settings' ] ] );
		register_setting( 'wpacc-setup', 'wpacc-setup', [ 'sanitize_callback' => [ $this->config_handler, 'validate_settings' ] ] );
	}

}
