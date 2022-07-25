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
use WP_Accountancy\Includes\PurchaseQuery;
use WP_Accountancy\Includes\TaxCodeQuery;
use WP_Accountancy\Includes\Transaction;

/**
 * The Public filters.
 */
class PurchaseDisplay extends TransactionDisplay {

	/**
	 * Provide the top title
	 *
	 * @return string
	 */
	public function get_title() : string {
		return __( 'Purchase Invoices', 'wpacc' );
	}

	/**
	 * Update the purchase.
	 *
	 * @return string
	 */
	public function update() : string {
		return $this->update_transaction( Transaction::PURCHASE_INVOICE, __( 'Purchase transaction saved', 'wpacc' ) );
	}

	/**
	 * Delete the purchase
	 *
	 * @return string
	 */
	public function delete() : string {
		return $this->delete_transaction( __( 'Purchase transaction removed', 'wpacc' ) );
	}

	/**
	 * Display the form
	 *
	 * @return string
	 */
	public function read() : string {
		$purchase = new Transaction( $this->business, intval( filter_input( INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT ) ) );
		return $this->form(
			$this->field->render(
				[
					'name'  => 'transaction_id',
					'type'  => 'hidden',
					'value' => $purchase->id,
				]
			) . $this->field->render(
				[
					'name'     => 'date',
					'type'     => 'date',
					'value'    => $purchase->date,
					'label'    => __( 'Issue date', 'wpacc' ),
					'required' => true,
				]
			) . $this->field->render(
				[
					'name'     => 'actor_id',
					'type'     => 'select',
					'value'    => $purchase->actor_id,
					'label'    => __( 'Supplier', 'wpacc' ),
					'required' => true,
					'list'     => ( new CreditorQuery( $this->business ) )->get_results(),
				]
			) . $this->field->render(
				[
					'name'  => 'reference',
					'value' => $purchase->reference,
					'label' => __( 'Reference', 'wpacc' ),
				]
			) . $this->field->render(
				[
					'name'  => 'address',
					'type'  => 'textarea',
					'value' => $purchase->address,
					'label' => __( 'Address', 'wpacc' ),
				]
			) . $this->field->render(
				[
					'name'  => 'description',
					'value' => $purchase->description,
					'label' => __( 'Description', 'wpacc' ),
				]
			) . $this->table->render(
				[
					'fields'  => [
						[
							'name' => 'detail_id',
							'type' => 'hidden',
						],
						[
							'name'     => 'detail-account_id',
							'type'     => 'select',
							'list'     => ( new AccountQuery( $this->business ) )->get_results(),
							'required' => true,
							'label'    => __( 'Account', 'wpacc' ),
						],
						[
							'name'  => 'detail-description',
							'type'  => 'text',
							'label' => __( 'Description', 'wpacc' ),
						],
						[
							'name'  => 'detail-quantity',
							'type'  => 'float',
							'label' => __( 'Amount', 'wpacc' ),
						],
						[
							'name'     => 'detail-unitprice',
							'type'     => 'currency',
							'label'    => __( 'Unitprice', 'wpacc' ),
							'required' => true,
						],
						[
							'name'  => 'detail-taxcode_id',
							'type'  => 'select',
							'list'  => ( new TaxCodeQuery( $this->business ) )->get_results(),
							'label' => __( 'Taxcode', 'wpacc' ),
						],
					],
					'items'   => $purchase->details(),
					'options' => [ 'addrow' ],
				]
			) . $this->button->save( __( 'Save', 'wpacc' ) ) .
			( $purchase->id ? $this->button->delete( __( 'Delete', 'wpacc' ) ) : '' )
		);
	}

	/**
	 * Render the existing purchases
	 *
	 * @return string
	 */
	public function overview() : string {
		$purchases = new PurchaseQuery( $this->business );
		return $this->form(
			$this->table->render(
				[
					'fields'  => [
						[
							'name'   => 'transaction_id',
							'label'  => 'id',
							'type'   => 'number',
							'static' => true,
						],
						[
							'name'   => 'date',
							'label'  => __( 'Invoice date', 'wpacc' ),
							'type'   => 'date',
							'static' => true,
						],
						[
							'name'   => 'invoice_id',
							'label'  => '#',
							'type'   => 'number',
							'static' => true,
						],
						[
							'name'   => 'name',
							'label'  => __( 'Supplier', 'wpacc' ),
							'type'   => 'text',
							'static' => true,
						],
						[
							'name'   => 'invoice_total',
							'label'  => __( 'Invoice total', 'wpacc' ),
							'type'   => 'currency',
							'static' => true,
						],
						[
							'name'  => 'balance_due',
							'label' => __( 'Balance due', 'wpacc' ),
							'type'  => 'currency',
							'zoom'  => true,
						],
					],
					'items'   => $purchases->get_results(),
					'options' => [ 'button_create' => __( 'New purchase invoice', 'wpacc' ) ],
				]
			)
		);
	}

}
