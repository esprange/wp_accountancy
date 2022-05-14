<?php
/**
 * Definition transaction class
 *
 * @since      1.0.0
 *
 * @package    WP-Accountancy
 * @subpackage WP-Accountacy/Includes
 */

namespace WP_Accountancy\Includes;

/**
 * Transaction class.
 *
 * @property int    id
 * @property int    business_id
 * @property int    debtor_id
 * @property int    creditor_id
 * @property string reference
 * @property int    invoice_id
 * @property string address
 * @property string date
 * @property string type
 * @property string description
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
	 * Constructor
	 *
	 * @param int $transaction_id The transaction_id, if specified, the transaction is retrieved from the db.
	 */
	public function __construct( int $transaction_id = 0 ) {
		global $wpacc_business;
		$data = [
			'id'          => 0,
			'business_id' => $wpacc_business->id,
			'debtor_id'   => 0,
			'creditor_id' => 0,
			'reference'   => '',
			'invoice_id'  => 0,
			'address'     => '',
			'date'        => wp_date( 'Y-m-d' ),
			'type'        => '',
			'description' => '',
		];
		if ( $transaction_id ) {
			global $wpdb;
			$result = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT * FROM {$wpdb->prefix}wpacc_transaction where id = %d",
					$transaction_id
				)
			);
			if ( $result ) {
				$data = $result;
			}
		}
		foreach ( $data as $property => $value ) {
			$this->$property = $value;
		}
	}

	/**
	 * Update the transaction.
	 *
	 * @return int The transaction id.
	 */
	public function update() : int {
		global $wpdb;
		global $wpacc_business;
		$data = [
			'id'          => $this->id,
			'business_id' => $wpacc_business->id,
			'debtor_id'   => $this->debtor_id,
			'creditor_id' => $this->creditor_id,
			'reference'   => $this->reference,
			'invoice_id'  => $this->invoice_id,
			'address'     => $this->address,
			'date'        => $this->date,
			'type'        => $this->type,
			'description' => $this->description,
		];
		$wpdb->replace( "{$wpdb->prefix}wpacc_transaction", $data );
		$this->id = $wpdb->insert_id;
		return $this->id;
	}

	/**
	 * Delete the transaction entry.
	 *
	 * @return bool
	 */
	public function delete() : bool {
		global $wpdb;
		return (bool) $wpdb->delete( "{$wpdb->prefix}wpacc_transaction", [ 'id' => $this->id ] );
	}

	/**
	 * Get the lines of the transaction
	 *
	 * @return array
	 */
	public function get_lines() : array {
		$query = new DetailQuery( [ 'transaction_id' => $this->id ] );
		return $query->get_results();
	}

}
