<?php
/**
 * Definition transaction detail query class
 *
 * @since      1.0.0
 *
 * @package    WP-Accountancy
 * @subpackage WP-Accountacy/Includes
 */

namespace WP_Accountancy\Includes;

/**
 * Detail query class.
 */
class DetailQuery {

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
	public function __construct( array $args ) {
		global $wpdb;
		$defaults          = [
			'transaction_id' => 0,
			'account_id'     => 0,
			'taxcode_id'     => 0,
			'debtor_id'      => 0,
			'creditor_id'    => 0,
			'id'             => 0,
		];
		$query_vars        = wp_parse_args( $args, $defaults );
		$this->query_where = 'WHERE 1 = 1';
		if ( $query_vars['debtor_id'] ) {
			$this->query_where .= $wpdb->prepare( ' AND debtor_id = %d', $query_vars['debtor_id'] );
		}
		if ( $query_vars['creditor_id'] ) {
			$this->query_where .= $wpdb->prepare( ' AND creditor_id = %d', $query_vars['creditor_id'] );
		}
		if ( $query_vars['transaction_id'] ) {
			$this->query_where .= $wpdb->prepare( ' AND transaction_id = %d', $query_vars['transaction_id'] );
		}
		if ( $query_vars['taxcode_id'] ) {
			$this->query_where .= $wpdb->prepare( ' AND taxcode_id = %d', $query_vars['taxcode_id'] );
		}
		if ( $query_vars['id'] ) {
			$this->query_where .= $wpdb->prepare( ' AND id = %d', $query_vars['id'] );
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
				"SELECT * FROM {$wpdb->prefix}wpacc_detail %s ORDER BY order_number",
				$this->query_where
			)
		);
	}

}
