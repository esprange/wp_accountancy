<?php
/**
 * The Display base handler.
 *
 * @since      1.0.0
 *
 * @package    WP-Accountancy
 * @subpackage WP-Accountancy/Public
 */

namespace WP_Accountancy\Public;

/**
 * The Display class.
 */
class Display {

	/**
	 * The controller.
	 *
	 * @return string
	 */
	public function controller() : string {
		$action = filter_input( INPUT_POST, 'wpacc_action' ) ?: filter_input( INPUT_GET, 'wpacc_action' );
		if ( $action && method_exists( $this, $action ) ) {
			return $this->$action();
		}
		return $this->overview();
	}

	/**
	 * Check the nonce
	 *
	 * @return bool
	 */
	public function check_nonce() : bool {
		return ( check_ajax_referer( 'wpacc_nonce', false ) );
	}

	/**
	 * Overview function, to be rewritten in child class
	 *
	 * @return string
	 */
	public function overview() : string {
		return '';
	}

	/**
	 * Build a form submit button
	 *
	 * @param string $action The button action.
	 * @param string $text   The button label.
	 *
	 * @return string
	 */
	protected function action_button( string $action, string $text ) : string {
		$display = get_class( $this );
		$nonce   = wp_create_nonce( 'wpacc_nonce' );
		return <<<EOT
		<button name="wpacc_action" type="button" value="$action" data-display="$display" data-nonce="$nonce" >$text</button>
		EOT;
	}

	/**
	 * Build a form container
	 *
	 * @param string $contents The contents of the form.
	 *
	 * @return string
	 */
	protected function form( string $contents ) : string {
		return <<<EOT
		<form id="wpacc-form" method="post">$contents</form>
		EOT;
	}

	/**
	 * The display container.
	 *
	 * @param string $contents The contents of the container.
	 *
	 * @return string
	 */
	protected function container( string $contents ) : string {
		return <<<EOT
		<div id="wpacc">$contents</div>
		EOT;
	}

}
