<?php
/**
 * The Public filters of the plugin.
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
class Filters {

	/**
	 * Load the translations.
	 *
	 * @param string $mofile The compiled file with translations.
	 * @param string $domain The text domain.
	 * @internal Filter for load_textdomain_mofile.
	 */
	public function load_translations( string $mofile, string $domain ) : string {
		if ( 'wpacc' === $domain && ! str_contains( $mofile, WP_LANG_DIR . '/plugins/' ) ) {
			$locale = apply_filters( 'plugin_locale', determine_locale(), $domain );
			$mofile = WP_PLUGIN_DIR . '/' . dirname( plugin_basename( __FILE__ ) ) . '/languages/' . $domain . '-' . $locale . '.mo';
			error_log( $mofile );
		}
		return $mofile;
	}

}
