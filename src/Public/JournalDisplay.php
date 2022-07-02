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
use WP_Accountancy\Includes\Detail;
use WP_Accountancy\Includes\DetailQuery;
use WP_Accountancy\Includes\JournalQuery;
use WP_Accountancy\Includes\TaxCodeQuery;
use WP_Accountancy\Includes\Transaction;

/**
 * The Public filters.
 */
class JournalDisplay extends Display {

	/**
	 * Provide the top title
	 *
	 * @return string
	 */
	public function get_title(): string {
		return __( 'Journal transactions', 'wpacc' );
	}

	/**
	 * Create the journal entry.
	 *
	 * @return string
	 */
	public function create() : string {
		return $this->read();
	}

	/**
	 * Update the journal entry.
	 *
	 * @return string
	 */
	public function update() : string {
		$input                = filter_input_array( INPUT_POST );
		$journal              = new Transaction( $this->business, intval( $input['id'] ?? 0 ) );
		$journal->date        = sanitize_text_field( $input['date'] ?? wp_date( __( 'Y/m/d', 'wpacc' ) ) );
		$journal->description = sanitize_text_field( $input['description'] ?? '' );
		$journal->type        = Transaction::JOURNAL_ENTRY;
		$journal->update();
		foreach ( $input['detail_id'] ?? [] as $index => $detail_id ) {
			$detail               = new Detail( $journal, intval( $detail_id ) );
			$detail->account_id   = intval( $input['detail-account_id'][ $index ] ) ?: null;
			$detail->debit        = floatval( $input['detail-debit'][ $index ] ?? 0.0 );
			$detail->credit       = floatval( $input['detail-credit'][ $index ] ?? 0.0 );
			$detail->description  = sanitize_text_field( $input['detail-description'][ $index ] ?? '' );
			$detail->taxcode_id   = intval( $input['detail-taxcode_id'][ $index ] ) ?: null;
			$detail->order_number = $index;
			$detail->update();
		}
		return $this->notify( 1, __( 'Journal entry saved', 'wpacc' ) );
	}

	/**
	 * Delete the journal entry
	 *
	 * @return string
	 */
	public function delete() : string {
		$journal_id = filter_input( INPUT_POST, 'journal_id', FILTER_SANITIZE_NUMBER_INT );
		if ( $journal_id ) {
			$journal = new Transaction( $this->business, intval( $journal_id ) );
			if ( $journal->delete() ) {
				return $this->notify( - 1, __( 'Journal entry removed', 'wpacc' ) );
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
		$journal = new Transaction( $this->business, intval( filter_input( INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT ) ) );
		return $this->form(
			$this->field->render(
				[
					'name'     => 'date',
					'type'     => 'date',
					'value'    => $journal->date,
					'label'    => __( 'Issue date', 'wpacc' ),
					'required' => true,
				]
			) .
			$this->field->render(
				[
					'name'  => 'description',
					'value' => $journal->description,
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
					'items'   => ( new DetailQuery( $this->business, [ 'transaction_id' => $journal->id ] ) )->get_results(),
					'options' => [ 'addrow' ],
				]
			) .
			$this->field->render(
				[
					'name'  => 'id',
					'type'  => 'hidden',
					'value' => $journal->id,
				]
			) .
			$this->button->save( __( 'Save', 'wpacc' ) ) .
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
							'name'  => 'journal_id',
							'label' => 'id',
							'type'  => 'static',
						],
						[
							'name'  => 'date',
							'label' => __( 'Invoice date', 'wpacc' ),
							'type'  => 'static',
						],
						[
							'name'  => 'description',
							'label' => __( 'Description', 'wpacc' ),
							'type'  => 'zoom',
						],
						[
							'name'  => 'debit_total',
							'label' => __( 'Debit', 'wpacc' ),
							'type'  => 'static',
						],
						[
							'name'  => 'credit_total',
							'label' => __( 'Credit', 'wpacc' ),
							'type'  => 'static',
						],
					],
					'items'   => $journal->get_results(),
					'options' => [ 'button_create' => __( 'New journal entry', 'wpacc' ) ],
				]
			)
		);
	}

}
