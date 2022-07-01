<?php
/**
 * The buttons.
 *
 * @since      1.0.0
 *
 * @package    WP-Accountancy
 * @subpackage WP-Accountancy/Public
 */

namespace WP_Accountancy\Public;

/**
 * The Button class.
 */
class Button {

	/**
	 * Build a form submit button
	 *
	 * @param string $action   The button action.
	 * @param string $text     The button label.
	 * @param string $position Horizontal position in form.
	 *
	 * @return string
	 */
	public function action( string $action, string $text, string $position = 'left' ) : string {
		return <<<EOT
		<button name="wpacc_action" type="button" class="wpacc-btn" style="float: $position;" value="$action" >$text</button>
		EOT;
	}

	/**
	 * Show a save button
	 *
	 * @param string $text The button label.
	 *
	 * @return string
	 */
	public function save( string $text ) : string {
		return <<<EOT
		<button name="wpacc_action" type="button" class="wpacc-btn wpacc-btn-save" style="float: left;" value="update" >$text</button>
		EOT;
	}

	/**
	 * Show a delete button
	 *
	 * @param `string $text The button label.
	 *
	 * @return string
	 */
	public function delete( $text ) : string {
		return <<<EOT
		<button name="wpacc_action" type="button" class="wpacc-btn wpacc-btn-delete" style="float: right;" value="delete" >$text</button>
		EOT;
	}

}
