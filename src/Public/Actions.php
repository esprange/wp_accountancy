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

/**
 * The Public actions.
 */
class Actions {

	/**
	 * Load the translations.
	 *
	 * @internal Action for init.
	 */
	public function load_translations() {
		load_plugin_textdomain( 'wpacc' );
	}

	/**
	 * Add the shortcode.
	 *
	 * @internal Action for init.
	 */
	public function add_shortcode() {
		add_shortcode(
			WPACC_SLUG,
			function( array $atts ) : string {
				$atts = shortcode_atts( [ 'action' => '' ], $atts );
				if ( $atts['action'] ) {
					$class = '\\' . __NAMESPACE__ . '\\' . ucfirst( $atts['action'] . 'Display' );
					if ( class_exists( $class ) ) {
						$display = new $class();
						return $display->controller();
					}
				}
				return '';
			}
		);
	}

	/**
	 * Load the javascript.
	 *
	 * @internal Action for wp_enqueue_scripts.
	 */
	public function load_script() {
		wp_enqueue_script( 'wpacc-ajax-handle', plugin_dir_url( __FILE__ ) . '/js/wpacc-ajax.js', [ 'jquery' ], '1.0.0', true );
		wp_add_inline_script(
			'wpacc-ajax-handle',
			'const wpaccData = ' . wp_json_encode( [ 'ajaxurl' => admin_url( 'admin-ajax.php' ) ] ),
			'before'
		);
	}

	/**
	 * Process the Ajax request.
	 */
	public function formhandler() {
		$display_class = filter_input( INPUT_POST, 'display', FILTER_SANITIZE_STRING );
		if ( $display_class ) {
			if ( class_exists( $display_class ) ) {
				$display = new $display_class();
				if ( $display->check_nonce() ) {
					wp_send_json_success( $display->controller() );
				}
				wp_send_json_error( __( 'Security error', 'wpacc' ) );
			}
		}
		wp_send_json_error( __( 'Something went wrong', 'wpacc' ) );
	}
}
