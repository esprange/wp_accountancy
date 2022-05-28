<?php
/**
 * The creditnote display handler.
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
class CreditnoteDisplay extends Display {

	/**
	 * Render the existing business
	 *
	 * @return string
	 */
	public function overview() : string {
		ob_start();
		?>
		creditnote
		<?php
		return ob_get_clean() . $this->form( $this->action_button( 'change', __( 'Change', 'wpacc' ) ) );
	}

}
