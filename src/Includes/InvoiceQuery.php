<?php
/**
 * Definition invoice query class
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
class InvoiceQuery extends TransactionQuery {

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
				transaction.id AS transaction_id,
				transaction.date AS date,
				transaction.invoice_id AS invoice_id,
				actor.name AS name,
       			FORMAT( SUM( detail.unitprice * detail.quantity * ( 1.0 + COALESCE( taxcode.rate, 0.0 ) ) ), 2, '$locale' ) AS invoice_total,
       			FORMAT( 0.0, 2, '$locale' ) AS balance_due
			FROM
				{$wpdb->prefix}wpacc_transaction AS transaction
			INNER JOIN
				{$wpdb->prefix}wpacc_actor AS actor
			ON transaction.actor_id = actor.id
			INNER JOIN
				{$wpdb->prefix}wpacc_detail AS detail
			ON detail.transaction_id = transaction.id
			LEFT JOIN
				{$wpdb->prefix}wpacc_taxcode AS taxcode
			ON detail.taxcode_id = taxcode.id
			WHERE $this->query_where",
			OBJECT_K
		);
	}

}
