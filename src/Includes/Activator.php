<?php
/**
 * Activation of the plugin.
 *
 * @since      1.0.0
 *
 * @package    WP-Accountancy
 * @subpackage WP-Accountacy/Includes
 */

namespace WP_Accountancy\Includes;

use WP_Accountancy\Admin\Upgrade;

/**
 * De activator class
 */
class Activator {

	/**
	 * Activeer de plugin.
	 */
	public static function activate(): void {
		$upgrade = new Upgrade();
		$upgrade->run();
	}
}
