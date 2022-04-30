<?php
/** The admin filters of the plugin.
 *
 * @since      1.0.0
 *
 * @package    WP_Accountancy
 * @subpackage WP_Accountancy/admin
 */

namespace WP_Accountancy\Admin;

/**
 * The filters class.
 */
class Filters {

	/**
	 * Register the exporter of privacy sensitive data.
	 *
	 * @since 1.0.0
	 *
	 * @param array $exporters The exporters.
	 *
	 * @internal Filter for wp_privacy_personal_data_exporters.
	 */
	public function register_exporter( array $exporters ) : array {
		$exporters['wpacc'] = [
			'exporter_friendly_name' => 'plugin folder wpacc',
			'callback'               => [ $this, 'exporter' ],
		];
		return $exporters;
	}

	/**
	 * Register the eraser of privacy sensitive data.
	 *
	 * @since 1.0.0
	 *
	 * @param array $erasers The erasers.
	 *
	 * @internal Filter for wp_privacy_personal_data_erasers.
	 */
	public function register_eraser( array $erasers ) : array {
		$erasers['wpacc'] = [
			'eraser_friendly_name' => 'wpacc',
			'callback'             => [ $this, 'eraser' ],
		];
		return $erasers;
	}

	/**
	 * The GDPR exporter
	 *
	 * @return string
	 */
	public function exporter() : string {
		return '';
	}

	/**
	 * The GDPR eraser
	 *
	 * @return string
	 */
	public function eraser() : string {
		return '';
	}

}
