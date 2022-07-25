<?php
/**
 * The journal display handler.
 *
 * @since      1.0.0
 *
 * @package    WP-Accountancy
 * @subpackage WP-Accountancy/Public
 */

namespace WP_Accountancy\Public;

use WP_Accountancy\Includes\AccountQuery;
use WP_Accountancy\Includes\JournalQuery;
use WP_Accountancy\Includes\TaxCodeQuery;
use WP_Accountancy\Includes\Transaction;

/**
 * The Public filters.
 */
class JournalDisplay extends TransactionDisplay {

	/**
	 * Provide the top title
	 *
	 * @return string
	 */
	public function get_title() : string {
		return __( 'Journal transactions', 'wpacc' );
	}

	/**
	 * Update the journal entry.
	 *
	 * @return string
	 */
	public function update() : string {
		return $this->update_transaction( Transaction::JOURNAL_ENTRY, __( 'Journal entry saved', 'wpacc' ) );
	}

	/**
	 * Delete the journal entry
	 *
	 * @return string
	 */
	public function delete() : string {
		return $this->delete_transaction( __( 'Journal entry removed', 'wpacc' ) );
	}

	/**
	 * Display the form
	 *
	 * @return string
	 */
	public function read() : string {
		$journal = new Transaction( $this->business, intval( filter_input( INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT ) ) );
		return $this->form(
			$this->field->render(
				[
					'name'  => 'transaction_id',
					'type'  => 'hidden',
					'value' => $journal->id,
				]
			) . $this->field->render(
				[
					'name'     => 'date',
					'type'     => 'date',
					'value'    => $journal->date,
					'label'    => __( 'Issue date', 'wpacc' ),
					'required' => true,
				]
			) . $this->field->render(
				[
					'name'  => 'description',
					'value' => $journal->description,
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
							'name'  => 'detail-debit',
							'type'  => 'currency',
							'label' => __( 'Debit', 'wpacc' ),
						],
						[
							'name'  => 'detail-credit',
							'type'  => 'currency',
							'label' => __( 'Credit', 'wpacc' ),
						],
						[
							'name'  => 'detail-taxcode_id',
							'type'  => 'select',
							'list'  => ( new TaxCodeQuery( $this->business ) )->get_results(),
							'label' => __( 'Taxcode', 'wpacc' ),
						],
					],
					'items'   => $journal->details(),
					'options' => [
						'addrow',
						'totals' => [
							'detail-debit',
							'detail-credit',
						],
					],
				]
			) . $this->button->save( __( 'Save', 'wpacc' ) ) .
			( $journal->id ? $this->button->delete( __( 'Delete', 'wpacc' ) ) : '' )
		);
	}

	/**
	 * Render the existing journal entry
	 *
	 * @return string
	 */
	public function overview() : string {
		$journal = new JournalQuery( $this->business );
		return $this->form(
			$this->table->render(
				[
					'fields'  => [
						[
							'name'   => 'journal_id',
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
							'name'  => 'description',
							'label' => __( 'Description', 'wpacc' ),
							'type'  => 'text',
							'zoom'  => true,
						],
						[
							'name'  => 'debit_total',
							'label' => __( 'Debit', 'wpacc' ),
							'type'  => 'currency',
							'total' => true,
						],
						[
							'name'  => 'credit_total',
							'label' => __( 'Credit', 'wpacc' ),
							'type'  => 'currency',
							'total' => true,
						],
					],
					'items'   => $journal->get_results(),
					'options' => [ 'button_create' => __( 'New journal entry', 'wpacc' ) ],
				]
			)
		);
	}

}
