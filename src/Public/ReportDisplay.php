<?php
/**
 * The report display handler.
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
class ReportDisplay extends Display {

	/**
	 * Provide the top title
	 *
	 * @return string
	 */
	public function get_title() : string {
		return __( 'Reports', 'wpacc' );
	}

	/**
	 * Render the existing business
	 *
	 * @return string
	 */
	public function overview() : string {
		ob_start();
		?>
		report
		<?php
		return ob_get_clean() . $this->form( $this->button->action( 'change', __( 'Change', 'wpacc' ) ) );
	}

}
