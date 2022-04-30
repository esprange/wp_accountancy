<?php
/**
 * Definition transaction class
 *
 * @since      1.0.0
 *
 * @package    WP-Accountancy
 * @subpackage WP-Accountacy/includes
 */

namespace WP_Accountancy\Includes;

/**
 * Transaction class.
 */
class Transaction {
	const SALES_INVOICE          = 'sales invoice';
	const PURCHASE_INVOICE       = 'purchase invoice';
	const JOURNAL_ENTRY          = 'journal entry';
	const CREDIT_NOTE            = 'credit note';
	const INTER_ACCOUNT_TRANSFER = 'inter account transfer';
	const RECEIPT                = 'receipt';
	const PAYMENT                = 'payment';
	const FIXED_ASSET            = 'fixed asset';

	/**
	 * Get the raw account.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $business_id The company id.
	 * @param string $type        The transaction type.
	 * @param array  $selection   The selection criteria.
	 *
	 * @return array
	 */
	protected function get( int $business_id, string $type, array $selection ) : array {
		global $wpdb;
		$query_vars  = $this->query_vars( $selection );
		$query_from  = "FROM {$wpdb->prefix}wpacc_lines AS l
                INNER JOIN {$wpdb->prefix}wpacc_transaction AS t
                    ON l.transaction_id = t.id";
		$query_where = $wpdb->prepare( 'WHERE t.id = %d AND t.$type = %s', $business_id, $type );
		if ( $query_vars['from'] ) {
			$query_where .= $wpdb->prepare( ' AND t.date >= %s', date( 'd-m-Y', $query_vars['from'] ) );
		}
		if ( $query_vars['until'] ) {
			$query_where .= $wpdb->prepare( ' AND t.date <= %s', date( 'd-m-Y', $query_vars['until'] ) );
		}
		if ( $query_vars['debtor_id'] ) {
			$query_where .= $wpdb->prepare( ' AND t.debtor_id <= %s', date( 'd-m-Y', $query_vars['debtor_id'] ) );
		}
		if ( $query_vars['creditor_id'] ) {
			$query_where .= $wpdb->prepare( ' AND t.debtor_id <= %s', date( 'd-m-Y', $query_vars['creditor_id'] ) );
		}
		return $wpdb->get_results(
			$wpdb->prepare(
				'SELECT lines.*, transactions.* %s %s',
				$query_from,
				$query_where
			)
		);
	}

	/**
	 * Determine the selection criteria.
	 *
	 * @param array $selection The raw criteria.
	 *
	 * @return array
	 */
	private function query_vars( array $selection ) : array {
		$defaults = [
			'from'        => 0,
			'until'       => 0,
			'debtor_id'   => 0,
			'creditor_id' => 0,
		];
		return wp_parse_args( $selection, $defaults );
	}
}
