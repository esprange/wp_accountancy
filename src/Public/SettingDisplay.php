<?php
/**
 * The setting display handler.
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
class SettingDisplay extends Display {


	/**
	 * Provide the top title
	 *
	 * @return string
	 */
	public function get_title() : string {
		return __( 'Settings', 'wpacc' );
	}

	/**
	 * Show the form for currency selection
	 *
	 * @return string
	 */
	public function currency() : string {
		return 'currency';
	}

	/**
	 * Show the form for taxcode management
	 *
	 * @return string
	 */
	public function taxcode() : string {
		$display = new TaxcodeDisplay( $this->business );
		return $display->overview();
	}

	/**
	 * Show the form for coa management
	 *
	 * @return string
	 */
	public function coa() : string {
		$display = new CoaDisplay( $this->business );
		return $display->overview();
	}

	/**
	 * Show the form for i18n management
	 *
	 * @return string
	 */
	public function i18n() : string {
		return 'i18n';
	}

	/**
	 * Show the form for lock management
	 *
	 * @return string
	 */
	public function lock() : string {
		return 'lock';
	}

	/**
	 * Render the existing business
	 *
	 * @return string
	 */
	public function overview() : string {
		$settings = [
			'currency' => [
				'title' => __( 'Base currency', 'wpacc' ),
				'icon'  => 'dashicons-money',
			],
			'taxcode'  => [
				'title' => __( 'Tax codes', 'wpacc' ),
				'icon'  => '',
			],
			'coa'      => [
				'title' => __( 'Chart of Accounts', 'wpacc' ),
				'icon'  => 'dashicons-products',
			],
			'i18n'     => [
				'title' => __( 'Date and number format', 'wpacc' ),
				'icon'  => 'dashicons-calendar-alt',
			],
			'lock'     => [
				'title' => __( 'Lock data', 'wpacc' ),
				'icon'  => 'dashicons-lock',
			],
		];

		$html[1] = '';
		$html[2] = '';
		$index   = 0;
		foreach ( $settings as $action => $setting ) {
			$html[ 1 + $index % 2 ] .= $this->button->action( $action, '<span class="dashicons ' . $setting['icon'] . '"></span>' . $setting['title'], 'full' );
			$index++;
		}
		$html = <<<EOT
		<div class="wpacc-split">
			<div style="grid-column: 1">
				{$html[1]}
			</div>
			<div style="grid-column: 2">
				{$html[2]}
			</div>
		</div>
		EOT;
		return $this->form( $html );

	}
}
