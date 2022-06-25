<?php
/**
 * Definition asset query class
 *
 * @since      1.0.0
 *
 * @package    WP-Accountancy
 * @subpackage WP-Accountacy/Includes
 */

namespace WP_Accountancy\Includes;

/**
 * Asset query class.
 */
class AssetQuery {

	/**
	 * De query string
	 *
	 * @var string De query.
	 */
	protected string $query_where;

	/**
	 * The constructor
	 *
	 * @param Business $business The business.
	 * @param array    $args     The query arguments.
	 *
	 * @return void
	 */
	public function __construct( Business $business, array $args = [] ) {
		global $wpdb;
		$defaults          = [
			'name' => '',
		];
		$query_vars        = wp_parse_args( $args, $defaults );
		$this->query_where = $wpdb->prepare( ' business_id = %d', $business->id );
		if ( $query_vars['name'] ) {
			$this->query_where .= $wpdb->prepare( ' AND name = %s', $query_vars['name'] );
		}
	}

	/**
	 * Get the asset results.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_results() : array {
		global $wpdb;
		return $wpdb->get_results(
			"SELECT id AS asset_id, name, description, rate, cost, provision
			FROM {$wpdb->prefix}wpacc_asset
			WHERE $this->query_where
			ORDER BY name",
			OBJECT_K
		);
	}

}
