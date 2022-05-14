<?php
/**
 * Definition transaction detail class
 *
 * @since      1.0.0
 *
 * @package    WP-Accountancy
 * @subpackage WP-Accountacy/Includes
 */

namespace WP_Accountancy\Includes;

/**
 * TransactionDetail class.
 *
 * @property int    id
 * @property int    transaction_id
 * @property int    account_id
 * @property int    debtor_id
 * @property int    creditor_id
 * @property int    taxcode_id
 * @property float  quantity
 * @property float  unitprice
 * @property string description
 * @property int    order_number
 */
class Detail {

	/**
	 * Constructor
	 *
	 * @param int $transaction_id The transaction_id, if specified, the transaction is retrieved from the db.
	 * @param int $detail_id      The transaction detail_id, if specified, the transactiondetail is retrieved from the db.
	 */
	public function __construct( int $transaction_id, int $detail_id = 0 ) {
		$data = [
			'id'             => $detail_id,
			'transaction_id' => $transaction_id,
			'account_id'     => 0,
			'debtor_id'      => 0,
			'creditor_id'    => 0,
			'taxcode_id'     => 0,
			'quantity'       => 1,
			'unitprice'      => 0,
			'description'    => '',
			'order_number'   => 0,
		];
		if ( $transaction_id ) {
			global $wpdb;
			$result = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT * FROM {$wpdb->prefix}wpacc_detail where id = %d",
					$detail_id
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
	 * Update the transactiondetail.
	 *
	 * @return int The transaction id.
	 */
	public function update() : int {
		if ( $this->transaction_id ) {
			global $wpdb;
			$data = [
				'id'             => $this->id,
				'transaction_id' => $this->transaction_id,
				'account_id'     => $this->account_id,
				'debtor_id'      => $this->debtor_id,
				'creditor_id'    => $this->creditor_id,
				'taxcode_id'     => $this->taxcode_id,
				'quantity'       => $this->quantity,
				'unitprice'      => $this->unitprice,
				'description'    => $this->description,
				'order_number'   => $this->order_number,
			];
			$wpdb->replace( "{$wpdb->prefix}wpacc_detail", $data );
			$this->id = $wpdb->insert_id;
			return $this->id;
		}
		return 0;
	}

}
