<?php
/**
 * Activation of the plugin.
 *
 * @since      1.0.0
 *
 * @package    WP-Accountancy
 * @subpackage WP-Accountacy/includes
 */

namespace WP_Accountancy\Includes;

use WP_Accountancy\Admin;

/**
 * De activator class
 */
class Activator {

	/**
	 * Activeer de plugin.
	 */
	public static function activate() {
		$upgrade = new \WP_Accountancy\Admin\Upgrade();
		$upgrade->run();
	}
}
