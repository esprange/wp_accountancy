<?php
/**
 * The purchase display handler.
 *
 * @since      1.0.0
 *
 * @package    WP-Accountancy
 * @subpackage WP-Accountancy/Public
 */

namespace WP_Accountancy\Public;

use WP_Accountancy\Includes\AccountQuery;
use WP_Accountancy\Includes\CreditorQuery;
use WP_Accountancy\Includes\Detail;
use WP_Accountancy\Includes\DetailQuery;
use WP_Accountancy\Includes\PurchaseQuery;
use WP_Accountancy\Includes\TaxCodeQuery;
use WP_Accountancy\Includes\Transaction;

/**
 * The Public filters.
 */
class PurchaseDisplay extends Display {

	/**
	 * Provide the top title
	 *
	 * @return string
	 */
	public function get_title(): string {
		return __( 'Purchase Invoices', 'wpacc' );
	}

	/**
	 * Create the purchase.
	 *
	 * @return string
	 */
	public function create() : string {
		return $this->read();
	}

	/**
	 * Update the purchase.
	 *
	 * @return string
	 */
	public function update() : string {
		global $wpacc_business;
		$input              = filter_input_array( INPUT_POST );
		$sales              = new Transaction( intval( $input['id'] ?? 0 ) );
		$sales->actor_id    = intval( $input['actor_id'] ?? null );
		$sales->reference   = sanitize_text_field( $input['reference'] ?? '' );
		$sales->address     = sanitize_text_field( $input['address'] ?? '' );
		$sales->invoice_id  = sanitize_text_field( $input['invoice_id'] ?? '' );
		$sales->date        = sanitize_text_field( $input['date'] ?? wp_date( __( 'Y/m/d', 'wpacc' ) ) );
		$sales->description = sanitize_text_field( $input['description'] ?? '' );
		$sales->type        = Transaction::PURCHASE_INVOICE;
		$sales->business_id = $wpacc_business->id;
		$sales->update();
		foreach ( $input['detail_id'] ?? [] as $index => $detail_id ) {
			$detail                 = new Detail( intval( $detail_id ) );
			$detail->transaction_id = $sales->id;
			$detail->account_id     = intval( $input['detail.account_id'][ $index ] );
			$detail->quantity       = floatval( $input['detail.quantity'][ $index ] );
			$detail->unitprice      = floatval( $input['detail.unitprice'][ $index ] );
			$detail->description    = sanitize_text_field( $input['detail.description'][ $index ] );
			$detail->taxcode_id     = intval( $input['detail.description'][ $index ] );
			$detail->order_number   = $index;
			$detail->update();
		}
		return $this->notify( 1, __( 'Purchase transaction saved', 'wpacc' ) );
	}

	/**
	 * Delete the purchase
	 *
	 * @return string
	 */
	public function delete() : string {
		$purchase_id = filter_input( INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT );
		if ( $purchase_id ) {
			$purchase = new Transaction( intval( $purchase_id ) );
			if ( $purchase->delete() ) {
				return $this->notify( - 1, __( 'Purchase transaction removed', 'wpacc' ) );
			}
			return $this->notify( 0, __( 'Remove not allowed', 'wpacc' ) );
		}
		return $this->notify( 0, __( 'Internal error' ) );
	}

	/**
	 * Display the form
	 *
	 * @return string
	 */
	public function read() : string {
		$purchase = new Transaction( intval( filter_input( INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT ) ) );
		return $this->form(
			$this->field->render(
				[
					'name'     => 'date',
					'type'     => 'date',
					'value'    => $purchase->date,
					'label'    => __( 'Issue date', 'wpacc' ),
					'required' => true,
				]
			) .
			$this->field->render(
				[
					'name'     => 'actor_id',
					'type'     => 'select',
					'value'    => $purchase->actor_id,
					'label'    => __( 'Customer', 'wpacc' ),
					'required' => true,
					'list'     => ( new CreditorQuery() )->get_results(),
				]
			) .
			$this->field->render(
				[
					'name'  => 'reference',
					'value' => $purchase->reference,
					'label' => __( 'Reference', 'wpacc' ),
				]
			) .
			$this->field->render(
				[
					'name'  => 'address',
					'textarea',
					'value' => $purchase->address,
					'label' => __( 'Address', 'wpacc' ),
				]
			) .
			$this->field->render(
				[
					'name'  => 'description',
					'value' => $purchase->description,
					'label' => __( 'Description', 'wpacc' ),
				]
			) .
			$this->table->render(
				[
					'fields'  => [
						[
							'name' => 'detail_id',
							'type' => 'static',
						],
						[
							'name'     => 'account_id',
							'type'     => 'select',
							'list'     => ( new AccountQuery() )->get_results(),
							'required' => true,
							'label'    => __( 'Account', 'wpacc' ),
						],
						[
							'name'  => 'description',
							'type'  => 'text',
							'label' => __( 'Description', 'wpacc' ),
						],
						[
							'name'  => 'amount',
							'type'  => 'float',
							'label' => __( 'Amount', 'wpacc' ),
						],
						[
							'name'     => 'unitprice',
							'type'     => 'currency',
							'label'    => __( 'Unitprice', 'wpacc' ),
							'required' => true,
						],
						[
							'name'  => 'taxcode_id',
							'type'  => 'select',
							'list'  => ( new TaxCodeQuery() )->get_results(),
							'label' => __( 'Taxcode', 'wpacc' ),
						],
					],
					'items'   => ( new DetailQuery( [ 'transaction_id' => $purchase->id ] ) )->get_results(),
					'options' => [ 'addrow' ],
				]
			) .
			$this->field->render(
				[
					'name'  => 'id',
					'type'  => 'hidden',
					'value' => $purchase->id,
				]
			) .
			$this->button->save( __( 'Save', 'wpacc' ) ) .
			$purchase->id ? $this->button->delete( __( 'Delete', 'wpacc' ) ) : ''
		);
	}

	/**
	 * Render the existing purchases
	 *
	 * @return string
	 */
	public function overview() : string {
		$purchases = new PurchaseQuery();
		return $this->form(
			$this->table->render(
				[
					'fields'  => [
						[
							'name'  => 'transaction_id',
							'label' => 'id',
							'type'  => 'static',
						],
						[
							'name'  => 'date',
							'label' => __( 'Invoice date', 'wpacc' ),
							'type'  => 'static',
						],
						[
							'name'  => 'invoice_id',
							'label' => '#',
							'type'  => 'static',
						],
						[
							'name'  => 'name',
							'label' => __( 'Supplier', 'wpacc' ),
							'type'  => 'static',
						],
						[
							'name'  => 'invoice_total',
							'label' => __( 'Invoice total', 'wpacc' ),
							'type'  => 'static',
						],
						[
							'name'  => 'balance_due',
							'label' => __( 'Balance due', 'wpacc' ),
							'type'  => 'zoom',
						],
					],
					'items'   => $purchases->get_results(),
					'options' => [ 'button_create' => __( 'New purchase invoice', 'wpacc' ) ],
				]
			)
		);
	}

}
