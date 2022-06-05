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

use WP_Accountancy\Includes\Account;
use WP_Accountancy\Includes\AccountQuery;
use WP_Accountancy\Includes\DebtorQuery;
use WP_Accountancy\Includes\Detail;
use WP_Accountancy\Includes\DetailQuery;
use WP_Accountancy\Includes\TaxCodeQuery;
use WP_Accountancy\Includes\Transaction;
use WP_Accountancy\Includes\TransactionQuery;

/**
 * The Sales Display class.
 */
class SalesDisplay extends Display {

	/**
	 * Provide the top title
	 *
	 * @return string
	 */
	public function get_title(): string {
		return __( 'Sales Invoices', 'wpacc' );
	}

	/**
	 * Create the sales.
	 *
	 * @return string
	 */
	public function create() : string {
		return $this->read();
	}

	/**
	 * Update the sales.
	 *
	 * @return string
	 */
	public function update() : string {
		global $wpacc_business;
		$input              = filter_input_array( INPUT_POST );
		$sales              = new Transaction( intval( $input['id'] ?? 0 ) );
		$sales->debtor_id   = intval( $input['debtor_id'] ?? 0 );
		$sales->reference   = sanitize_text_field( $input['reference'] ?? '' );
		$sales->address     = sanitize_text_field( $input['address'] ?? '' );
		$sales->invoice_id  = sanitize_text_field( $input['invoice_id'] ?? '' );
		$sales->date        = sanitize_text_field( $input['date'] ?? '' );
		$sales->description = sanitize_text_field( $input['description'] ?? '' );
		$sales->type        = Transaction::SALES_INVOICE;
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
		return $this->notify( -1, __( 'Transaction saved', 'wpacc' ) );
	}

	/**
	 * Delete the sales
	 *
	 * @return string
	 */
	public function delete() : string {
		$sales_id = filter_input( INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT );
		if ( $sales_id ) {
			$sales = new Account( intval( $sales_id ) );
			if ( $sales->delete() ) {
				return $this->notify( - 1, __( 'Sales transaction removed', 'wpacc' ) );
			}
			return $this->notify( 1, __( 'Remove not allowed', 'wpacc' ) );
		}
		return $this->notify( 1, __( 'Internal error' ) );
	}

	/**
	 * Display the form
	 *
	 * @return string
	 */
	public function read() : string {
		$sales_id    = filter_input( INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT );
		$sales       = new Transaction( intval( $sales_id ) );
		$debtors     = ( new DebtorQuery() )->get_results();
		$details     = ( new DetailQuery( [ 'transaction_id' => $sales->id ] ) )->get_results();
		$accounts    = ( new AccountQuery() )->get_results();
		$taxcode_ids = ( new TaxCodeQuery() )->get_results();
		$html        =
			$this->field->render(
				[
					'name'     => 'date',
					'type'     => 'date',
					'value'    => $sales->date,
					'label'    => __( 'Issue date', 'wpacc' ),
					'required' => true,
				]
			) .
			$this->field->render(
				[
					'name'     => 'debtor_id',
					'type'     => 'select',
					'value'    => $sales->debtor_id,
					'label'    => __( 'Customer', 'wpacc' ),
					'required' => true,
					'list'     => $debtors,
				]
			) .
			$this->field->render(
				[
					'name'  => 'reference',
					'value' => $sales->reference,
					'label' => __(
						'Reference',
						'wpacc'
					),
				]
			) .
			$this->field->render(
				[
					'name'  => 'address',
					'textarea',
					'value' => $sales->address,
					'label' => __(
						'Billing address',
						'wpacc'
					),
				]
			) .
			$this->field->render(
				[
					'name'  => 'description',
					'value' => $sales->description,
					'label' => __(
						'Description',
						'wpacc'
					),
				]
			);
		$html       .= $this->table->render(
			[
				'fields'  => [
					[
						'name' => 'detail_id',
						'type' => 'static',
					],
					[
						'name'     => 'account_id',
						'type'     => 'select',
						'list'     => $accounts,
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
						'list'  => $taxcode_ids,
						'label' => __( 'Taxcode', 'wpacc' ),
					],
				],
				'items'   => $details,
				'options' => [ 'addrow' ],
			]
		);
		$html       .=
			$this->field->render(
				[
					'name'  => 'id',
					'type'  => 'hidden',
					'value' => $sales->id,
				]
			) .
			$this->button->action_button( 'update', __( 'Save', 'wpacc' ) ) .
			( $sales->id ? $this->button->action_button( 'delete', __( 'Delete', 'wpacc' ), false ) : '' );
		return $this->form( $html );
	}

	/**
	 * Render the existing sales
	 *
	 * @return string
	 */
	public function overview() : string {
		$sales = new TransactionQuery( [ 'type' => Transaction::SALES_INVOICE ] );
		return $this->form(
			( new Table() )->render(
				[
					'fields'  => [
						[
							'name'  => 'transaction_id',
							'label' => 'id',
							'type'  => 'static',
						],
						[
							'name'  => 'date',
							'label' => __( 'Invoice date' ),
							'type'  => 'static',
						],
						[
							'name'  => 'invoice_id',
							'label' => '#',
							'type'  => 'static',
						],
						[
							'name'  => 'name',
							'label' => __( 'Customer', 'wpacc' ),
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
					'items'   => $sales->get_results(),
					'options' => [ 'create' => __( 'New sales invoice', 'wpacc' ) ],
				]
			)
		);
	}

}
