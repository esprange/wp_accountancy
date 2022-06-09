<?php
/**
 * Definition sales query class
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
class SalesQuery extends AccountQuery {

	/**
	 * Get the raw account results.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_results() : array {
		global $wpdb;
		$this->query_where .= $wpdb->prepare( ' AND type = %s', Transaction::SALES_INVOICE );
		$locale             = get_locale();
		return $wpdb->get_results(
			"SELECT
				transaction.id AS transaction_id,
				transaction.date as date,
				transaction.invoice_id AS invoice_id,
				debtor.name as name,
       			FORMAT( SUM( detail.unitprice * detail.quantity * ( 1.0 + COALESCE( taxcode.rate, 0.0 ) ) ), 2, '$locale' ) AS invoice_total,
       			FORMAT( 0.0, 2, '$locale' ) AS balance_due
			FROM
				{$wpdb->prefix}wpacc_transaction AS transaction
			INNER JOIN
				{$wpdb->prefix}wpacc_debtor AS debtor
			ON transaction.debtor_id = debtor.id
			INNER JOIN
				{$wpdb->prefix}wpacc_detail AS detail
			ON detail.transaction_id = transaction.id
			LEFT JOIN
				{$wpdb->prefix}wpacc_taxcode AS taxcode
			ON detail.taxcode_id = taxcode.id
			",
			OBJECT_K
		);
	}

}
