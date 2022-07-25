<?php
/**
 * The sales display handler.
 *
 * @since      1.0.0
 *
 * @package    WP-Accountancy
 * @subpackage WP-Accountancy/Public
 */

namespace WP_Accountancy\Public;

use WP_Accountancy\Includes\AccountQuery;
use WP_Accountancy\Includes\DebtorQuery;
use WP_Accountancy\Includes\SalesQuery;
use WP_Accountancy\Includes\TaxCodeQuery;
use WP_Accountancy\Includes\Transaction;

/**
 * The Sales Display class.
 */
class SalesDisplay extends TransactionDisplay {

	/**
	 * Provide the top title
	 *
	 * @return string
	 */
	public function get_title() : string {
		return __( 'Sales Invoices', 'wpacc' );
	}

	/**
	 * Update the sales.
	 *
	 * @return string
	 */
	public function update() : string {
		return $this->update_transaction( Transaction::SALES_INVOICE, __( 'Sales transaction saved', 'wpacc' ) );
	}

	/**
	 * Delete the sales
	 *
	 * @return string
	 */
	public function delete() : string {
		return $this->delete_transaction( __( 'Sales transaction removed', 'wpacc' ) );
	}

	/**
	 * Display the form
	 *
	 * @return string
	 */
	public function read() : string {
		$sales = new Transaction( $this->business, intval( filter_input( INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT ) ) );
		return $this->form(
			$this->field->render(
				[
					'name'  => 'transaction_id',
					'type'  => 'hidden',
					'value' => $sales->id,
				]
			) . $this->field->render(
				[
					'name'     => 'date',
					'type'     => 'date',
					'value'    => $sales->date,
					'label'    => __( 'Issue date', 'wpacc' ),
					'required' => true,
				]
			) . $this->field->render(
				[
					'name'     => 'actor_id',
					'type'     => 'select',
					'value'    => $sales->actor_id,
					'label'    => __( 'Customer', 'wpacc' ),
					'required' => true,
					'list'     => ( new DebtorQuery( $this->business ) )->get_results(),
				]
			) . $this->field->render(
				[
					'name'  => 'reference',
					'value' => $sales->reference,
					'label' => __( 'Reference', 'wpacc' ),
				]
			) . $this->field->render(
				[
					'name'  => 'address',
					'type'  => 'textarea',
					'value' => $sales->address,
					'label' => __( 'Billing address', 'wpacc' ),
				]
			) . $this->field->render(
				[
					'name'  => 'description',
					'value' => $sales->description,
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
					'items'   => $sales->details(),
					'options' => [ 'addrow' ],
				]
			) . $this->button->save( __( 'Save', 'wpacc' ) ) .
			( $sales->id ? $this->button->delete( __( 'Delete', 'wpacc' ) ) : '' )
		);
	}

	/**
	 * Render the existing sales
	 *
	 * @return string
	 */
	public function overview() : string {
		$sales = new SalesQuery( $this->business );
		return $this->form(
			$this->table->render(
				[
					'fields'  => [
						[
							'name'   => 'transaction_id',
							'label'  => 'id',
							'type'   => 'number',
							'static' => 'true',
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
							'label'  => __( 'Customer', 'wpacc' ),
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
					'items'   => $sales->get_results(),
					'options' => [ 'button_create' => __( 'New sales invoice', 'wpacc' ) ],
				]
			)
		);
	}

}
