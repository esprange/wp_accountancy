<?php
/**
 * The shortcode handler of the plugin.
 *
 * @since      1.0.0
 *
 * @package    WP-Accountancy
 * @subpackage WP-Accountancy/Public
 */

namespace WP_Accountancy\Public;

/**
 * The Public filters.
 */
class Handler {

	/**
	 * Shortcode handler
	 *
	 * @param array $atts The shortcode attributes, at least action is required.
	 *
	 * @return string
	 */
	public function run( array $atts ) : string {
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
}
