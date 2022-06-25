<?php
/**
 * Definition transaction query class
 *
 * @since      1.0.0
 *
 * @package    WP-Accountancy
 * @subpackage WP-Accountacy/Includes
 */

namespace WP_Accountancy\Includes;

/**
 * Transaction query class.
 */
class TransactionQuery {

	/**
	 * De query string
	 *
	 * @var string De query.
	 */
	protected string $query_where;

	/**
	 * De query string
	 *
	 * @var string De query.
	 */
	private string $query_order;

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
			'from'     => '',
			'until'    => '',
			'actor_id' => 0,
			'type'     => '',
			'order_by' => '',
		];
		$query_vars        = wp_parse_args( $args, $defaults );
		$this->query_where = $wpdb->prepare( ' transaction.business_id = %d', $business->id );
		if ( $query_vars['from'] ) {
			$this->query_where .= $wpdb->prepare( ' AND transaction.date >= %s', $query_vars['from'] );
		}
		if ( $query_vars['until'] ) {
			$this->query_where .= $wpdb->prepare( ' AND transaction.date <= %s', $query_vars['until'] );
		}
		if ( $query_vars['actor_id'] ) {
			$this->query_where .= $wpdb->prepare( ' AND transaction.actor_id = %d', $query_vars['actor_id'] );
		}
		if ( $query_vars['type'] ) {
			$this->query_where .= $wpdb->prepare( ' AND transaction.type = %s', $query_vars['type'] );
		}
		if ( $query_vars['order_by'] ) {
			$order_by          = strcasecmp( 'desc', $query_vars['order_by'] ) ? 'DESC' : 'ASC';
			$this->query_order = $wpdb->prepare( ' ORDER BY transaction.%s %s', $query_vars['order'], $order_by );
			return;
		}
		$this->query_order = 'ORDER BY date DESC';
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
			"SELECT id as transaction_id, business_id, actor_id, reference, invoice_id, address, date, type, description
			FROM {$wpdb->prefix}wpacc_transaction AS transaction
			WHERE $this->query_where
			$this->query_order",
			OBJECT_K
		);
	}

}
