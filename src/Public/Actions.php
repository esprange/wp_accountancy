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

/**
 * The Public actions.
 */
class Actions {

	/**
	 * Load the javascript.
	 *
	 * @internal Action for wp_enqueue_scripts.
	 */
	public function load_script() : void {
		$dev    = 'development' === wp_get_environment_type() ? '' : '.min';
		$cleave = '1.6.0';
		wp_register_style( 'wpacc', plugin_dir_url( __FILE__ ) . "/css/wpacc$dev.css", [], version() );
		wp_register_script( 'cleave', "https://cdnjs.cloudflare.com/ajax/libs/cleave.js/$cleave/cleave.min.js", [], $cleave, true );
		wp_register_script( 'wpacc', plugin_dir_url( __FILE__ ) . "/js/wpacc$dev.js", [ 'jquery', 'cleave' ], version(), true );
	}

	/**
	 * Add the shortcode.
	 *
	 * @internal Action for init.
	 */
	public function add_shortcode() : void {
		add_shortcode(
			WPACC_SLUG,
			function( mixed $atts ) : string {
				if ( ! is_user_logged_in() ) {
					return __( 'This function is only accessible for logged in users', 'wpacc' );
				}
				wp_enqueue_style( 'wpacc' );
				wp_enqueue_script( 'wpacc' );
				wp_add_inline_script(
					'wpacc',
					'const wpaccData = ' . wp_json_encode( [ 'ajaxurl' => admin_url( 'admin-ajax.php' ) ] ),
					'before'
				);
				$translations = [
					'create' => __( 'Create', 'wpacc' ),
					'delete' => __( 'Delete', 'wpacc' ),
					'change' => __( 'Change', 'wpacc' ),
				];
				wp_localize_script( 'wpacc', 'wpacc_i18n', $translations );
				$atts     = shortcode_atts( [ 'business' => '' ], $atts );
				$business = $atts['business'] ? new Business( intval( $atts['business'] ) ) : $this->get_business();
				if ( $business->id ) {
					do_action( 'wpacc_business_select', $business->id );
					$display = new SummaryDisplay( $business );
					return $display->container( $display->controller() );
				}
				$display = new BusinessDisplay( $business );
				return $display->container( $display->controller() );
			}
		);
	}

	/**
	 * Process the Ajax form request.
	 *
	 * @internal Action for wp_ajax_wpacc_formhandler
	 */
	public function formhandler() : void {
		$business      = $this->get_business();
		$display_class = filter_input( INPUT_POST, 'display', FILTER_UNSAFE_RAW );
		if ( $display_class ) {
			if ( class_exists( $display_class ) ) {
				$display = new $display_class( $business );
				if ( $display->check_nonce() ) {
					wp_send_json_success(
						[
							'main'     => $display->controller(),
							'business' => (array) $business,
						]
					);
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
	public function menuhandler() : void {
		$business      = $this->get_business();
		$display_class = filter_input( INPUT_GET, 'menu', FILTER_UNSAFE_RAW );
		if ( $display_class ) {
			if ( class_exists( $display_class ) ) {
				$display = new $display_class( $business );
				wp_send_json_success(
					[
						'main'     => $display->controller(),
						'business' => (array) $business,
					]
				);
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
	public function business_select( int $business_id ) : void {
		update_user_meta( get_current_user_id(), WPACC_BUSINESS, $business_id );
	}

	/**
	 * Get the business to use.
	 *
	 * @return Business
	 */
	private function get_business() : Business {
		$business = new Business( intval( get_user_meta( get_current_user_id(), WPACC_BUSINESS, true ) ) );
		if ( $business->id ) {
			return $business;
		}
		$businesses = ( new BusinessQuery() )->get_results();
		if ( count( $businesses ) ) {
			$business_id = ( reset( $businesses ) )->business_id;
			do_action( 'wpacc_business_select', $business_id );
			return new Business( $business_id );
		}
		$business->name = __( 'Default company', 'wpacc' );
		$business->update();
		do_action( 'wpacc_business_select', $business->id );
		return $business;
	}
}
