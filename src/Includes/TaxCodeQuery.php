<?php
/**
 * Definition tax code query class
 *
 * @since      1.0.0
 *
 * @package    WP-Accountancy
 * @subpackage WP-Accountacy/Includes
 */

namespace WP_Accountancy\Includes;

/**
 * TaxCode query class.
 */
class TaxCodeQuery {

	/**
	 * De query string
	 *
	 * @var string De query.
	 */
	private string $query_where;

	/**
	 * The constructor
	 *
	 * @param array $args The query arguments.
	 *
	 * @return void
	 */
	public function __construct( array $args = [] ) {
		global $wpdb;
		$defaults          = [
			'business_id' => 1,
			'name'        => '',
			'active'      => 0,
			'id'          => 0,
		];
		$query_vars        = wp_parse_args( $args, $defaults );
		$this->query_where = 'WHERE 1 = 1';
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
	 * Get the raw account results.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_results() : array {
		global $wpdb;
		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}wpacc_taxcode %s ORDER BY name",
				$this->query_where
			)
		);
	}

}
