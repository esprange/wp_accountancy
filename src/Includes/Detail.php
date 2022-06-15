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
 * @property int    actor_id
 * @property int    taxcode_id
 * @property float  quantity
 * @property float  unitprice
 * @property string description
 * @property int    order_number
 */
class Detail extends Entity {

	/**
	 * Constructor
	 *
	 * @param int $transaction_id The transaction_id, if specified, the transaction is retrieved from the db.
	 * @param int $detail_id      The transaction detail_id, if specified, the transactiondetail is retrieved from the db.
	 */
	public function __construct( int $transaction_id, int $detail_id = 0 ) {
		$this->fetch(
			[
				'id'             => $detail_id,
				'transaction_id' => $transaction_id,
				'account_id'     => null,
				'actor_id'       => null,
				'taxcode_id'     => null,
				'quantity'       => 1.0,
				'unitprice'      => 0.0,
				'description'    => '',
				'order_number'   => 0,
			]
		);
	}

	/**
	 * Return the table name
	 *
	 * @return string
	 */
	public function tablename(): string {
		return 'wpacc_detail';
	}

}
