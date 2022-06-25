<?php
/**
 * Definition account query class
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
class AccountQuery {

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
			'type'   => '',
			'active' => false,
		];
		$query_vars        = wp_parse_args( $args, $defaults );
		$this->query_where = $wpdb->prepare( ' business_id = %d', $business->id );
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
		return $wpdb->get_results(
			"SELECT id AS account_id, name, business_id, taxcode_id, group_id, type, active, order_number
			FROM {$wpdb->prefix}wpacc_account
			WHERE $this->query_where
			ORDER BY order_number",
			OBJECT_K
		);
	}

}
