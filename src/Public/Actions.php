<?php
/**
 * The Public actions of the plugin.
 *
 * @since      1.0.0
 *
 * @package    WP-Accountancy
 * @subpackage WP-Accountancy/Public
 */

namespace WP_Accountancy\Public;

use WP_Accountancy\Includes\Business;
use WP_Accountancy\Includes\BusinessQuery;
use function WP_Accountancy\Includes\version;
use function WP_Accountancy\Includes\business;

/**
 * The Public actions.
 */
class Actions {

	/**
	 * Load the javascript.
	 *
	 * @internal Action for wp_enqueue_scripts.
	 */
	public function load_script() {
		$dev                       = 'development' === wp_get_environment_type() ? '' : '.min';
		$datatables_version        = '1.11.5';
		$datatables_select_version = '1.3.4';
		$jquery_ui_version         = wp_scripts()->registered['jquery-ui-core']->ver;
		wp_register_style( 'jquery-ui', sprintf( '//code.jquery.com/ui/%s/themes/smoothness/jquery-ui.css', $jquery_ui_version ), [], $jquery_ui_version );
		wp_register_style( 'datatables', sprintf( '//cdn.datatables.net/%s/css/jquery.dataTables.min.css', $datatables_version ), [], $datatables_version );
		wp_register_style( 'datatables_select', sprintf( '//cdn.datatables.net/select/%s/css/select.dataTables.min.css', $datatables_select_version ), [], $datatables_select_version );
		wp_register_style( 'wpacc', plugin_dir_url( __FILE__ ) . "/css/wpacc$dev.css", [ 'jquery-ui', 'datatables', 'datatables_select' ], version() );
		wp_register_script( 'datatables', sprintf( '//cdn.datatables.net/%s/js/jquery.dataTables.min.js', $datatables_version ), [ 'jquery' ], $datatables_version, true );
		wp_register_script( 'datatables_select', sprintf( '//cdn.datatables.net/select/%s/js/dataTables.select.min.js', $datatables_select_version ), [ 'jquery', 'datatables' ], $datatables_select_version, true );
		wp_register_script( 'wpacc', plugin_dir_url( __FILE__ ) . "/js/wpacc-ajax$dev.js", [ 'jquery', 'datatables', 'datatables_select', 'jquery-ui-datepicker' ], version(), true );
	}

	/**
	 * Add the shortcode.
	 *
	 * @internal Action for init.
	 */
	public function add_shortcode() {
		add_shortcode(
			WPACC_SLUG,
			function( mixed $atts ) : string {
				wp_enqueue_style( 'wpacc' );
				wp_enqueue_script( 'wpacc' );
				wp_add_inline_script(
					'wpacc',
					'const wpaccData = ' . wp_json_encode( [ 'ajaxurl' => admin_url( 'admin-ajax.php' ) ] ),
					'before'
				);
				$atts        = shortcode_atts( [ 'business' => '' ], $atts );
				$business_id = business()->id;
				if ( $atts['business'] ) {
					$business_name = $atts['business'];
					$businesses    = new BusinessQuery( [ 'name' => $business_name ] );
					$business_id   = count( $businesses ) ? $businesses[0]->id : 0;
				}
				if ( $business_id ) {
					do_action( 'wpacc_business_select', $business_id );
				}
				$display = $business_id ? new SummaryDisplay( business() ) : new BusinessDisplay(business() );
				return $display->container( $display->controller() );
			}
		);
	}

	/**
	 * Process the Ajax form request.
	 *
	 * @internal Action for wp_ajax_wpacc_formhandler
	 */
	public function formhandler() {
		$display_class = filter_input( INPUT_POST, 'display', FILTER_SANITIZE_STRING );
		if ( $display_class ) {
			if ( class_exists( $display_class ) ) {
				$display = new $display_class( business() );
				if ( $display->check_nonce() ) {
					wp_send_json_success( $display->controller() );
				}
				wp_send_json_error( __( 'Security error', 'wpacc' ) );
			}
		}
		wp_send_json_error( __( 'Something went wrong', 'wpacc' ) );
	}

	/**
	 * Process the Ajax menu request.
	 *
	 * @internal Action for wp_ajax_wpacc_menuhandler
	 */
	public function menuhandler() {
		$display_class = filter_input( INPUT_GET, 'menu', FILTER_SANITIZE_STRING );
		if ( $display_class ) {
			if ( class_exists( $display_class ) ) {
				$display = new $display_class( business() );
				wp_send_json_success( $display->controller() );
			}
		}
		wp_send_json_error( __( 'Something went wrong', 'wpacc' ) );
	}

	/**
	 * Change the business object
	 *
	 * @param int $business_id The business id.
	 *
	 * @internal Action for wpacc_business_select
	 */
	public function business_select( int $business_id ) {
		set_transient( WPACC_BUSINESS . get_current_user_id(), $business_id );
		global $wpacc_business;
		$wpacc_business = new Business( $business_id );
	}
}
