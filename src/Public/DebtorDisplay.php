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
	public function get_title() : string {
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
		$input                   = filter_input_array( INPUT_POST );
		$debtor                  = new Debtor( $this->business, intval( $input['actor_id'] ?? 0 ) );
		$debtor->name            = sanitize_text_field( $input['name'] ?? '' );
		$debtor->address         = sanitize_textarea_field( $input['address'] ?? '' );
		$debtor->billing_address = sanitize_textarea_field( $input['billing_address'] ?? '' );
		$debtor->email_address   = sanitize_email( $input['email_address'] ?? '' );
		$debtor->active          = boolval( $input['active'] ?? true );
		$debtor->update();
		return $this->notify( 1, __( 'Customer saved', 'wpacc' ) );
	}

	/**
	 * Delete the debtor
	 *
	 * @return string
	 */
	public function delete() : string {
		$actor_id = filter_input( INPUT_POST, 'actor_id', FILTER_SANITIZE_NUMBER_INT );
		if ( $actor_id ) {
			$debtor = new Debtor( $this->business, intval( $actor_id ) );
			if ( $debtor->delete() ) {
				return $this->notify( - 1, __( 'Customer removed', 'wpacc' ) );
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
		$debtor = new Debtor( $this->business, intval( filter_input( INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT ) ) );
		return $this->form(
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
					'label' => __( 'Address', 'wpacc' ),
				]
			) .
			$this->field->render(
				[
					'name'  => 'billing_address',
					'type'  => 'textarea',
					'value' => $debtor->billing_address,
					'label' => __( 'Billing address', 'wpacc' ),
				]
			) .
			$this->field->render(
				[
					'name'  => 'email_address',
					'type'  => 'email',
					'value' => $debtor->email_address,
					'label' => __( 'Email', 'wpacc' ),
				]
			) .
			$this->field->render(
				[
					'name'  => 'active',
					'type'  => 'checkbox',
					'value' => $debtor->active,
					'label' => __( 'Active', 'wpacc' ),
				]
			) .
			$this->field->render(
				[
					'name'  => 'actor_id',
					'type'  => 'hidden',
					'value' => $debtor->id,
				]
			) .
			$this->button->save( __( 'Save', 'wpacc' ) ) . ( $debtor->id ? $this->button->delete( __( 'Delete', 'wpacc' ) ) : '' )
		);
	}

	/**
	 * Render the existing debtor
	 *
	 * @return string
	 */
	public function overview() : string {
		return $this->form(
			$this->table->render(
				[
					'fields'  => [
						[
							'name'   => 'actor_id',
							'type'   => 'number',
							'static' => true,
							'label'  => '',
						],
						[
							'name'  => 'name',
							'type'  => 'text',
							'zoom'  => true,
							'label' => __( 'Name', 'wpacc' ),
						],
					],
					'items'   => ( new DebtorQuery( $this->business ) )->get_results(),
					'options' => [ 'button_create' => __( 'New customer', 'wpacc' ) ],
				]
			)
		);
	}
}
