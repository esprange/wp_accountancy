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
			'type'        => '',
			'active'      => 0,
			'id'          => 0,
		];
		$query_vars        = wp_parse_args( $args, $defaults );
		$this->query_where = '1 = 1';
		if ( $query_vars['active'] ) {
			$this->query_where .= ' AND active';
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
		global $wpacc_business;
		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT sum( d.quantity * d.unitprice ) AS value, a.type AS type, a.name AS name, a.id AS id
				FROM {$wpdb->prefix}wpacc_account AS a
				LEFT JOIN {$wpdb->prefix}wpacc_detail AS d ON a.id=d.account_id AND a.business_id=%d
				GROUP BY a.type",
				$wpacc_business->id
			)
		);
	}

}
