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
use WP_Accountancy\Includes\ChartOfAccounts;
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
	public function load_script(): void {
		$dev = 'development' === wp_get_environment_type() ? '' : '.min';
		wp_register_style( 'wpacc', plugin_dir_url( __FILE__ ) . "/css/wpacc$dev.css", [], version() );
		wp_register_script( 'wpacc', plugin_dir_url( __FILE__ ) . "/js/wpacc-ajax$dev.js", [ 'jquery' ], version(), true );
	}

	/**
	 * Add the shortcode.
	 *
	 * @internal Action for init.
	 */
	public function add_shortcode(): void {
		add_shortcode(
			WPACC_SLUG,
			function( mixed $atts ) : string {
				global $wpacc_business;
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
				$atts        = shortcode_atts( [ 'business' => '' ], $atts );
				$business_id = $wpacc_business->id;
				if ( $atts['business'] ) {
					$business_id = intval( $atts['business'] );
				}
				if ( $business_id ) {
					do_action( 'wpacc_business_select', $business_id );
				}
				$display = $wpacc_business->id ? new SummaryDisplay() : new BusinessDisplay();
				return $display->container( $display->controller() );
			}
		);
	}

	/**
	 * Process the Ajax form request.
	 *
	 * @internal Action for wp_ajax_wpacc_formhandler
	 */
	public function formhandler(): void {
		global $wpacc_business;
		$display_class = filter_input( INPUT_POST, 'display', FILTER_UNSAFE_RAW );
		if ( $display_class ) {
			if ( class_exists( $display_class ) ) {
				$display = new $display_class();
				if ( $display->check_nonce() ) {
					wp_send_json_success(
						[
							'main'     => $display->controller(),
							'business' => $wpacc_business->name,
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
	public function menuhandler(): void {
		global $wpacc_business;
		$display_class = filter_input( INPUT_GET, 'menu', FILTER_UNSAFE_RAW );
		if ( $display_class ) {
			if ( class_exists( $display_class ) ) {
				$display = new $display_class();
				wp_send_json_success(
					[
						'main'     => $display->controller(),
						'business' => $wpacc_business->name,
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
	public function business_select( int $business_id ): void {
		update_user_meta( get_current_user_id(), WPACC_BUSINESS, $business_id );
		global $wpacc_business;
		$wpacc_business = new Business( $business_id );
	}

	/**
	 * Determine the business the user will work with.
	 * - from previous session, as stored in usermeta.
	 * - the first business in the list, if no history exists.
	 * - a default business.
	 *
	 * @internal Action for init
	 */
	public function init_business(): void {
		global $wpacc_business;
		$wpacc_business = new Business( intval( get_user_meta( get_current_user_id(), WPACC_BUSINESS, true ) ) );
		if ( $wpacc_business->id ) {
			return;
		}
		$businesses = ( new BusinessQuery() )->get_results();
		if ( count( $businesses ) ) {
			$wpacc_business = new Business( ( reset( $businesses ) )->business_id );
			return;
		}
		$wpacc_business->name = __( 'Default business', 'wpacc' );
		$wpacc_business->slug = 'default';
		$wpacc_business->update();
		$coa = new ChartOfAccounts();
		$coa->import( WPACC_PLUGIN_PATH . 'Templates\\' . Business::COUNTRIES[ $wpacc_business->country ]['template'] );
	}
}
