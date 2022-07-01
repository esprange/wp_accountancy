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
	protected string $query_where;

	/**
	 * The constructor
	 *
	 * @param Business $business The business id.
	 * @param array    $args     The query arguments.
	 *
	 * @return void
	 */
	public function __construct( Business $business, array $args = [] ) {
		global $wpdb;
		$defaults          = [
			'name'       => '',
			'active'     => 0,
			'account_id' => 0,
		];
		$query_vars        = wp_parse_args( $args, $defaults );
		$this->query_where = $wpdb->prepare( 'WHERE business_id = %d', $business->id );
		if ( $query_vars['active'] ) {
			$this->query_where .= ' AND active';
		}
		if ( $query_vars['name'] ) {
			$this->query_where .= $wpdb->prepare( ' AND name = %s', $query_vars['name'] );
		}
		if ( $query_vars['account_id'] ) {
			$this->query_where .= $wpdb->prepare( ' AND account_id = %d', $query_vars['account_id'] );
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
			"SELECT id as taxcode_id, name, business_id, account_id, rate, active
			FROM {$wpdb->prefix}wpacc_taxcode $this->query_where
			ORDER BY name",
			OBJECT_K
		);
	}

}
