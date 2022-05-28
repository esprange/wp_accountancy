<?php
/**
 * The purchase display handler.
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
class PurchaseDisplay extends Display {

	/**
	 * Render the existing business
	 *
	 * @return string
	 */
	public function overview() : string {
		ob_start();
		?>
		purchase
		<?php
		return ob_get_clean() . $this->form( $this->action_button( 'change', __( 'Change', 'wpacc' ) ) );
	}

}
