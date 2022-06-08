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

/**
 * The Public filters.
 */
class PurchaseDisplay extends Display {

	/**
	 * Provide the top title
	 *
	 * @return string
	 */
	public function get_title(): string {
		return __( 'Purchases', 'wpacc' );
	}

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
		return ob_get_clean() . $this->form( $this->button->action( 'change', __( 'Change', 'wpacc' ) ) );
	}

}
