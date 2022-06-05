<?php
/**
 * The interaccount display handler.
 *
 * @since      1.0.0
 *
 * @package    WP-Accountancy
 * @subpackage WP-Accountancy/Public
 */

namespace WP_Accountancy\Public;

use function WP_Accountancy\Includes\notify;

/**
 * The Public filters.
 */
class InteraccountDisplay extends Display {

	/**
	 * Provide the top title
	 *
	 * @return string
	 */
	public function get_title(): string {
		return __( 'Interaccount transactions', 'wpacc' );
	}

	/**
	 * Render the existing business
	 *
	 * @return string
	 */
	public function overview() : string {
		ob_start();
		?>
		interaccount
		<?php
		return ob_get_clean() . $this->form( $this->action_button( 'change', __( 'Change', 'wpacc' ) ) );
	}

}
