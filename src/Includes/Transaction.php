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
 * @property int    actor_id
 * @property string reference
 * @property string invoice_id
 * @property string address
 * @property string date
 * @property string type
 * @property string description
 * @property bool   tax_include
 */
class Transaction extends Entity {
	const SALES_INVOICE          = 'sales invoice';
	const PURCHASE_INVOICE       = 'purchase invoice';
	const JOURNAL_ENTRY          = 'journal entry';
	const CREDIT_NOTE            = 'credit note';
	const INTER_ACCOUNT_TRANSFER = 'inter account transfer';
	const RECEIPT                = 'receipt';
	const PAYMENT                = 'payment';
	const FIXED_ASSET            = 'fixed asset';
	const START_BALANCE          = 'start balance';

	/**
	 * Constructor
	 *
	 * @param Business $business       The business.
	 * @param int      $transaction_id The transaction_id, if specified, the transaction is retrieved from the db.
	 */
	public function __construct( Business $business, int $transaction_id = 0 ) {
		$this->fetch(
			[
				'id'          => $transaction_id,
				'business_id' => $business->id,
				'actor_id'    => null,
				'reference'   => '',
				'invoice_id'  => '',
				'address'     => '',
				'date'        => wp_date( 'Y-m-d' ),
				'type'        => '',
				'description' => '',
				'tax_include' => true,
			],
			[
				'id'          => 'int',
				'business_id' => 'int',
				'actor_id'    => 'int',
				'reference'   => 'string',
				'invoice_id'  => 'string',
				'address'     => 'string',
				'date'        => 'string',
				'type'        => 'string',
				'description' => 'string',
				'tax_include' => 'bool',
			],
		);
	}

	/**
	 * Return the table name
	 *
	 * @return string
	 */
	public function tablename() : string {
		return 'wpacc_transaction';
	}

}
