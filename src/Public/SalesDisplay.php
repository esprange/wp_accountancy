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
use WP_Accountancy\Includes\Detail;
use WP_Accountancy\Includes\DetailQuery;
use WP_Accountancy\Includes\SalesQuery;
use WP_Accountancy\Includes\TaxCodeQuery;
use WP_Accountancy\Includes\Transaction;

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
		$sales->actor_id    = intval( $input['actor_id'] ?? null );
		$sales->reference   = sanitize_text_field( $input['reference'] ?? '' );
		$sales->address     = sanitize_text_field( $input['address'] ?? '' );
		$sales->invoice_id  = sanitize_text_field( $input['invoice_id'] ?? '' );
		$sales->date        = sanitize_text_field( $input['date'] ?? wp_date( __( 'Y/m/d', 'wpacc' ) ) );
		$sales->description = sanitize_text_field( $input['description'] ?? '' );
		$sales->type        = Transaction::SALES_INVOICE;
		$sales->business_id = $wpacc_business->id;
		$sales->update();
		foreach ( $input['detail_id'] ?? [] as $index => $detail_id ) {
			$detail                 = new Detail( intval( $detail_id ) );
			$detail->transaction_id = $sales->id;
			$detail->account_id     = intval( $input['account_id'][ $index ] ?? null );
			$detail->quantity       = floatval( $input['quantity'][ $index ] ?? 1.0 );
			$detail->unitprice      = floatval( $input['unitprice'][ $index ] ?? 0.0 );
			$detail->description    = sanitize_text_field( $input['description'][ $index ] ?? '' );
			$detail->taxcode_id     = intval( $input['taxcode_id'][ $index ] ?? null );
			$detail->order_number   = $index;
			$detail->update();
		}
		return $this->notify( -1, __( 'Sales transaction saved', 'wpacc' ) );
	}

	/**
	 * Delete the sales
	 *
	 * @return string
	 */
	public function delete() : string {
		$sales_id = filter_input( INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT );
		if ( $sales_id ) {
			$sales = new Transaction( intval( $sales_id ) );
			if ( $sales->delete() ) {
				return $this->notify( - 1, __( 'Sales transaction removed', 'wpacc' ) );
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
		$sales = new Transaction( intval( filter_input( INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT ) ) );
		return $this->form(
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
					'name'     => 'actor_id',
					'type'     => 'select',
					'value'    => $sales->actor_id,
					'label'    => __( 'Customer', 'wpacc' ),
					'required' => true,
					'list'     => ( new DebtorQuery() )->get_results(),
				]
			) .
			$this->field->render(
				[
					'name'  => 'reference',
					'value' => $sales->reference,
					'label' => __( 'Reference', 'wpacc' ),
				]
			) .
			$this->field->render(
				[
					'name'  => 'address',
					'type'  => 'textarea',
					'value' => $sales->address,
					'label' => __( 'Billing address', 'wpacc' ),
				]
			) .
			$this->field->render(
				[
					'name'  => 'description',
					'value' => $sales->description,
					'label' => __( 'Description', 'wpacc' ),
				]
			) .
			$this->table->render(
				[
					'fields'  => [
						[
							'name' => 'detail_id',
							'type' => 'hidden',
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
							'name'  => 'quantity',
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
					'items'   => ( new DetailQuery( [ 'transaction_id' => $sales->id ] ) )->get_results(),
					'options' => [ 'addrow' ],
				]
			) .
			$this->field->render(
				[
					'name'  => 'id',
					'type'  => 'hidden',
					'value' => $sales->id,
				]
			) .
			$this->button->save( __( 'Save', 'wpacc' ) ) .
			( $sales->id ? $this->button->delete( __( 'Delete', 'wpacc' ) ) : '' )
		);
	}

	/**
	 * Render the existing sales
	 *
	 * @return string
	 */
	public function overview() : string {
		$sales = new SalesQuery();
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
					'options' => [ 'button_create' => __( 'New sales invoice', 'wpacc' ) ],
				]
			)
		);
	}

}
