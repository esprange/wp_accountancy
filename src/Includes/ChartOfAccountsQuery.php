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
	 * The query string
	 *
	 * @var string The query.
	 */
	protected string $query_where;

	/**
	 * Determine if a detail query is required.
	 *
	 * @var bool If a detail query is needed.
	 */
	private bool $detail = false;

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
			'type'       => '',
			'active'     => false,
			'from'       => '',
			'until'      => '',
			'account_id' => 0,
		];
		$query_vars        = wp_parse_args( $args, $defaults );
		$this->query_where = $wpdb->prepare( 'account.business_id = %d', $business->id );
		if ( $query_vars['active'] ) {
			$this->query_where .= ' AND active';
		}
		if ( $query_vars['type'] ) {
			$this->query_where .= $wpdb->prepare( ' AND account.type = %s', $query_vars['type'] );
		}
		if ( $query_vars['from'] ) {
			$this->query_where .= $wpdb->prepare( ' AND DATE( transaction.date ) >= %s', $query_vars['from'] );
		}
		if ( $query_vars['until'] ) {
			$this->query_where .= $wpdb->prepare( ' AND DATE( transaction.date ) <= %s', $query_vars['until'] );
		}
		if ( $query_vars['account_id'] ) {
			$this->query_where .= $wpdb->prepare( ' AND account.id = %d', $query_vars['account_id'] );
			$this->detail       = true;
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
		if ( $this->detail ) {
			return $wpdb->get_results(
				"SELECT transaction.id AS transaction_id, transaction.date AS date, transaction.type AS transaction, transaction.description AS description, IFNULL( actor.name, '' ) AS actor, sum( detail.debit ) AS debit, sum( detail.credit ) AS credit
				FROM {$wpdb->prefix}wpacc_account AS account
				LEFT JOIN {$wpdb->prefix}wpacc_detail AS detail ON account.id = detail.account_id
				LEFT JOIN {$wpdb->prefix}wpacc_transaction AS transaction ON transaction.id = detail.transaction_id
				LEFT JOIN {$wpdb->prefix}wpacc_actor AS actor ON actor.id = detail.actor_id
				WHERE $this->query_where
				GROUP BY transaction.id",
				OBJECT_K
			);
		}
		return $wpdb->get_results(
			"SELECT sum( debit - credit ) AS value, account.type AS type, name, account.id AS account_id
			FROM {$wpdb->prefix}wpacc_account AS account
			LEFT JOIN {$wpdb->prefix}wpacc_detail AS detail ON account.id = detail.account_id
			LEFT JOIN {$wpdb->prefix}wpacc_transaction AS transaction ON transaction.id = detail.transaction_id
			WHERE $this->query_where
			GROUP BY account.id"
		);
	}

}
