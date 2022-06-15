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
	 * @param array $args The query arguments.
	 *
	 * @return void
	 */
	public function __construct( array $args = [] ) {
		global $wpdb;
		global $wpacc_business;
		$defaults          = [
			'business_id' => $wpacc_business->id,
			'name'        => '',
			'active'      => 0,
			'id'          => 0,
		];
		$query_vars        = wp_parse_args( $args, $defaults );
		$this->query_where = $wpdb->prepare( ' business_id = %d', $wpacc_business->id );
		if ( $query_vars['active'] ) {
			$this->query_where .= $wpdb->prepare( ' AND active_id = %d', (int) $query_vars['active'] );
		}
		if ( $query_vars['id'] ) {
			$this->query_where .= $wpdb->prepare( ' AND id = %d', $query_vars['id'] );
		}
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
			"SELECT *, id AS asset_id
			FROM {$wpdb->prefix}wpacc_asset
			WHERE $this->query_where
			ORDER BY name"
		);
	}

}
