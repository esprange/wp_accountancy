<?php
/**
 * Configuration handler
 *
 * @since      1.0.0
 *
 * @package    WP-Accountancy
 * @subpackage WP-Accountancy/Admin
 */

namespace WP_Accountancy\Admin;

/**
 * Configuration handler class
 */
class ConfigHandler {

	/**
	 * Show the configuration.
	 *
	 * @since    1.0.0
	 */
	public function display_settings_page(): void {
		$display    = new ConfigDisplay();
		$active_tab = filter_input( INPUT_GET, 'tab' ) ?: __( 'Options', 'wpacc' );
		?>
		<div class="wrap">
			<h2 class="nav-tab-wrapper">
				<a href="?page=wpacc&tab=options" class="nav-tab <?php echo 'options' === $active_tab ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Options', 'wpacc' ); ?></a>
				<a href="?page=wpacc&tab=setup" class="nav-tab <?php echo 'setup' === $active_tab ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Setup', 'wpacc' ); ?></a>
			</h2>
			<?php $display->$active_tab(); ?>
		</div>
		<?php
	}

	/**
	 * Validate input
	 *
	 * @since    1.0.0
	 *
	 * @param array $input The raw input.
	 * @return array Validated input
	 */
	public function validate_settings( array $input ) : array {
		foreach ( $input as &$element ) {
			if ( is_string( $element ) ) {
				$element = sanitize_text_field( $element );
				continue;
			}
			if ( is_array( $element ) ) {
				$element = $this->validate_settings( $element );
			}
		}
		return $input;
	}

}
