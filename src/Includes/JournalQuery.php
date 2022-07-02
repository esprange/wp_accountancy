<?php
/**
 * Definition journal query class
 *
 * @since      1.0.0
 *
 * @package    WP-Accountancy
 * @subpackage WP-Accountacy/Includes
 */

namespace WP_Accountancy\Includes;

/**
 * Invoice query class.
 */
class JournalQuery extends TransactionQuery {

	/**
	 * The constructor
	 *
	 * @param Business $business The business.
	 * @param array    $args     The query arguments.
	 *
	 * @return void
	 */
	public function __construct( Business $business, array $args = [] ) {
		$args['type'] = Transaction::JOURNAL_ENTRY;
		parent::__construct( $business, $args );
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
		$locale = get_locale();
		return $wpdb->get_results(
			"SELECT
				transaction.id AS journal_id,
				transaction.date AS date,
				transaction.description AS description,
       			FORMAT( SUM( detail.debit ), 2, '$locale' ) AS debit_total,
       			FORMAT( SUM( detail.credit ), 2, '$locale' ) AS credit_total
			FROM
				{$wpdb->prefix}wpacc_transaction AS transaction
			INNER JOIN
				{$wpdb->prefix}wpacc_detail AS detail
			ON detail.transaction_id = transaction.id
			WHERE $this->query_where",
			OBJECT_K
		);
	}

}
