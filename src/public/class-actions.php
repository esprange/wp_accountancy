<?php
/**
 * The public actions of the plugin.
 *
 * @since      1.0.0
 *
 * @package    WP-Accountancy
 * @subpackage WP-Accountancy/public
 */

namespace WP_Accountancy\Public;

/**
 * The public actions.
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
