<?php
/**
 * The debtor display handler.
 *
 * @since      1.0.0
 *
 * @package    WP-Accountancy
 * @subpackage WP-Accountancy/Public
 */

namespace WP_Accountancy\Public;

use WP_Accountancy\Includes\Debtor;
use WP_Accountancy\Includes\DebtorQuery;

/**
 * The Debtor display class.
 */
class DebtorDisplay extends Display {

	/**
	 * Provide the top title
	 *
	 * @return string
	 */
	public function get_title(): string {
		return __( 'Customers', 'wpacc' );
	}

	/**
	 * Create the debtor.
	 *
	 * @return string
	 */
	public function create() : string {
		return $this->read();
	}

	/**
	 * Update the debtor.
	 *
	 * @return string
	 */
	public function update() : string {
		global $wpacc_business;
		$input                   = filter_input_array( INPUT_POST );
		$debtor                  = new Debtor( intval( $input['debtor_id'] ?? 0 ) );
		$debtor->name            = sanitize_text_field( $input['name'] ?? '' );
		$debtor->address         = sanitize_textarea_field( $input['address'] ?? '' );
		$debtor->billing_address = sanitize_textarea_field( $input['billing_address'] ?? '' );
		$debtor->email_address   = sanitize_email( $input['email_address'] ?? '' );
		$debtor->active          = boolval( $input['active'] ?? false );
		$debtor->business_id     = $wpacc_business->id;
		$debtor->update();
		return $this->notify( -1, __( 'Customer saved', 'wpacc' ) );
	}

	/**
	 * Delete the debtor
	 *
	 * @return string
	 */
	public function delete() : string {
		$debtor_id = filter_input( INPUT_POST, 'debtor_id', FILTER_SANITIZE_NUMBER_INT );
		if ( $debtor_id ) {
			$debtor = new Debtor( intval( $debtor_id ) );
			if ( $debtor->delete() ) {
				return $this->notify( - 1, __( 'Customer removed', 'wpacc' ) );
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
		$debtor_id = filter_input( INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT );
		$debtor    = new Debtor( intval( $debtor_id ) );
		$html      =
			$this->field->render(
				[
					'name'     => 'name',
					'value'    => $debtor->name,
					'label'    => __( 'Name', 'wpacc' ),
					'required' => true,
				]
			) .
			$this->field->render(
				[
					'name'  => 'address',
					'type'  => 'textarea',
					'value' => $debtor->address,
					'label' => __(
						'Address',
						'wpacc'
					),
				]
			) .
			$this->field->render(
				[
					'name'  => 'billing_address',
					'type'  => 'textarea',
					'value' => $debtor->billing_address,
					'label' => __(
						'Billing address',
						'wpacc'
					),
				]
			) .
			$this->field->render(
				[
					'name'  => 'email_address',
					'type'  => 'email',
					'value' => $debtor->email_address,
					'label' => __(
						'Email',
						'wpacc'
					),
				]
			) .
			$this->field->render(
				[
					'name'  => 'active',
					'type'  => 'checkbox',
					'value' => $debtor->active,
					'label' => __(
						'Active',
						'wpacc'
					),
				]
			) .
			$this->field->render(
				[
					'name'  => 'debtor_id',
					'type'  => 'hidden',
					'value' => $debtor->id,
				]
			) .
			$this->button->action_save( __( 'Save', 'wpacc' ) ) .
			( $debtor->id ? $this->button->action_delete( __( 'Delete', 'wpacc' ) ) : '' );
		return $this->form( $html );
	}

	/**
	 * Render the existing debtor
	 *
	 * @return string
	 */
	public function overview() : string {
		return $this->form(
			( new Table() )->render(
				[
					'fields'  => [
						[
							'name'  => 'debtor_id',
							'type'  => 'static',
							'label' => '',
						],
						[
							'name'  => 'name',
							'type'  => 'zoom',
							'label' => __(
								'Name',
								'wpacc'
							),
						],
					],
					'items'   => ( new DebtorQuery() )->get_results(),
					'options' => [ 'create' => __( 'New customer', 'wpacc' ) ],
				]
			)
		);
	}
}
