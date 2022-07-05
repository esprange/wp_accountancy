<?php
/**
 * The transaction display handler.
 *
 * @since      1.0.0
 *
 * @package    WP-Accountancy
 * @subpackage WP-Accountancy/Public
 */

namespace WP_Accountancy\Public;

use WP_Accountancy\Includes\Detail;
use WP_Accountancy\Includes\Transaction;

/**
 * The Transaction Display class.
 */
abstract class TransactionDisplay extends Display {

	/**
	 * Update the transaction
	 *
	 * @return string
	 */
	abstract public function update() : string;

	/**
	 * Show the transaction form
	 *
	 * @return string
	 */
	abstract public function read() : string;

	/**
	 * Delete the transaction
	 *
	 * @return string
	 */
	abstract public function delete() : string;

	/**
	 * Show the overview of transactions
	 *
	 * @return string
	 */
	abstract public function overview() : string;

	/**
	 * Create the transaction.
	 *
	 * @return string
	 */
	final public function create() : string {
		return $this->read();
	}

	/**
	 * Update the transaction.
	 *
	 * @param string $type   The transaction type.
	 * @param string $notify Notification message.
	 * @return string
	 */
	protected function update_transaction( string $type, string $notify ) : string {
		$input                    = filter_input_array( INPUT_POST );
		$transaction              = new Transaction( $this->business, intval( $input['transaction_id'] ?? 0 ) );
		$transaction->actor_id    = intval( $input['actor_id'] ?? 0 ) ?: null;
		$transaction->reference   = sanitize_text_field( $input['reference'] ?? '' );
		$transaction->address     = sanitize_text_field( $input['address'] ?? '' );
		$transaction->invoice_id  = sanitize_text_field( $input['invoice_id'] ?? '' );
		$transaction->date        = sanitize_text_field( $input['date'] ?? wp_date( __( 'Y/m/d', 'wpacc' ) ) );
		$transaction->description = sanitize_text_field( $input['description'] ?? '' );
		$transaction->type        = $type;
		$transaction->update();
		foreach ( $input['detail_id'] ?? [] as $index => $detail_id ) {
			$detail               = new Detail( $transaction, intval( $detail_id ) );
			$detail->account_id   = intval( $input['detail-account_id'][ $index ] ?? 0 ) ?: null;
			$detail->quantity     = floatval( $input['detail-quantity'][ $index ] ?? 1.0 );
			$detail->unitprice    = floatval( $input['detail-unitprice'][ $index ] ?? 0.0 );
			$detail->description  = sanitize_text_field( $input['detail-description'][ $index ] ?? '' );
			$detail->taxcode_id   = intval( $input['detail-taxcode_id'][ $index ] ?? 0 ) ?: null;
			$detail->order_number = $index;
			switch ( $type ) {
				case Transaction::JOURNAL_ENTRY:
					$detail->debit  = floatval( $input['detail-debit'][ $index ] ?? 0.0 );
					$detail->credit = floatval( $input['detail-credit'][ $index ] ?? 0.0 );
					break;
				case Transaction::PURCHASE_INVOICE:
					$detail->debit  = $detail->unitprice * $detail->quantity;
					$detail->credit = 0.0;
					break;
				case Transaction::SALES_INVOICE:
					$detail->debit  = 0.0;
					$detail->credit = $detail->unitprice * $detail->quantity;
					break;
			}
			$detail->update();
		}
		return $this->notify( 1, $notify );
	}

	/**
	 * Delete the transaction
	 *
	 * @param string $notify Notification message.
	 * @return string
	 */
	protected function delete_transaction( string $notify ) : string {
		$transaction_id = filter_input( INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT );
		if ( $transaction_id ) {
			$transaction = new Transaction( $this->business, intval( $transaction_id ) );
			if ( $transaction->delete() ) {
				return $this->notify( - 1, $notify );
			}
			return $this->notify( 0, __( 'Remove not allowed', 'wpacc' ) );
		}
		return $this->notify( 0, __( 'Internal error' ) );
	}

}
