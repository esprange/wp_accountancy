<?php
/**
 * The Public actions of the plugin.
 *
 * @since      1.0.0
 *
 * @package    WP-Accountancy
 * @subpackage WP-Accountancy/Public
 */

namespace WP_Accountancy\Public;

/**
 * The Public actions.
 */
class Actions {

	/**
	 * Load the translations.
	 *
	 * @internal Action for init.
	 */
	public function load_translations() {
		load_plugin_textdomain( 'wpacc' );
	}

}
