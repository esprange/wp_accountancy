<?php
/**
 * Definition chart of account query class
 *
 * @since      1.0.0
 *
 * @package    WP-Accountancy
 * @subpackage WP-Accountacy/Includes
 */

namespace WP_Accountancy\Includes;

/**
 * Account query class.
 */
class ChartOfAccountsQuery {

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
		global $wpacc_business;
		$defaults          = [
			'business_id' => $wpacc_business->id,
			'type'        => '',
			'active'      => 0,
			'id'          => 0,
		];
		$query_vars        = wp_parse_args( $args, $defaults );
		$this->query_where = 'WHERE 1 = 1';
		if ( $query_vars['active'] ) {
			$this->query_where .= $wpdb->prepare( ' AND active = %d', (int) $query_vars['active'] );
		}
		if ( $query_vars['id'] ) {
			$this->query_where .= $wpdb->prepare( ' AND id = %d', $query_vars['id'] );
		}
		if ( $query_vars['type'] ) {
			$this->query_where .= $wpdb->prepare( ' AND name = %s', $query_vars['type'] );
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
				"SELECT sum( d.quantity * d.unitprice ) as value, a.type as type, a.name as name, a.id as id FROM {$wpdb->prefix}wpacc_account AS a
				LEFT JOIN {$wpdb->prefix}wpacc_detail as d ON a.id=d.account_id AND a.business_id=%d GROUP BY a.type",
				business()->id
			)
		);
	}

}
