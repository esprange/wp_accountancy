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
	private string $query_where;

	/**
	 * The constructor
	 *
	 * @param int   $business_id Always required.
	 * @param array $args The query arguments.
	 *
	 * @return void
	 */
	public function __construct( int $business_id, array $args ) {
		global $wpdb;
		$defaults          = [
			'business_id' => $business_id,
			'type'        => '',
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
				"SELECT * FROM {$wpdb->prefix}wpacc_account %s ORDER BY order_number",
				$this->query_where
			)
		);
	}

}
