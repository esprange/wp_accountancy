<?php
/**
 * The creditor display handler.
 *
 * @since      1.0.0
 *
 * @package    WP-Accountancy
 * @subpackage WP-Accountancy/Public
 */

namespace WP_Accountancy\Public;

use WP_Accountancy\Includes\Creditor;
use WP_Accountancy\Includes\CreditorQuery;

/**
 * The Creditor display class.
 */
class CreditorDisplay extends Display {

	/**
	 * Provide the top title
	 *
	 * @return string
	 */
	public function get_title(): string {
		return __( 'Suppliers', 'wpacc' );
	}

	/**
	 * Create the creditor.
	 *
	 * @return string
	 */
	public function create() : string {
		return $this->read();
	}

	/**
	 * Update the creditor.
	 *
	 * @return string
	 */
	public function update() : string {
		$input                   = filter_input_array( INPUT_POST );
		$creditor                = new Creditor( $this->business, intval( $input['actor_id'] ?? 0 ) );
		$creditor->name          = sanitize_text_field( $input['name'] ?? '' );
		$creditor->address       = sanitize_textarea_field( $input['address'] ?? '' );
		$creditor->email_address = sanitize_email( $input['email_address'] ?? '' );
		$creditor->active        = boolval( $input['active'] ?? true );
		$creditor->update();
		return $this->notify( 1, __( 'Supplier saved', 'wpacc' ) );
	}

	/**
	 * Delete the creditor
	 *
	 * @return string
	 */
	public function delete() : string {
		$actor_id = filter_input( INPUT_POST, 'actor_id', FILTER_SANITIZE_NUMBER_INT );
		if ( $actor_id ) {
			$creditor = new Creditor( $this->business, intval( $actor_id ) );
			if ( $creditor->delete() ) {
				return $this->notify( - 1, __( 'Supplier removed', 'wpacc' ) );
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
		$creditor = new Creditor( $this->business, intval( filter_input( INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT ) ) );
		$html     =
			$this->field->render(
				[
					'name'     => 'name',
					'value'    => $creditor->name,
					'label'    => __( 'Name', 'wpacc' ),
					'required' => true,
				]
			) .
			$this->field->render(
				[
					'name'  => 'address',
					'type'  => 'textarea',
					'value' => $creditor->address,
					'label' => __(
						'Address',
						'wpacc'
					),
				]
			) .
			$this->field->render(
				[
					'name'  => 'email_address',
					'type'  => 'email',
					'value' => $creditor->email_address,
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
					'value' => $creditor->active,
					'label' => __(
						'Active',
						'wpacc'
					),
				]
			) .
			$this->field->render(
				[
					'name'  => 'actor_id',
					'type'  => 'hidden',
					'value' => $creditor->id,
				]
			) .
			$this->button->save( __( 'Save', 'wpacc' ) ) .
			( $creditor->id ? $this->button->delete( __( 'Delete', 'wpacc' ) ) : '' );
		return $this->form( $html );
	}

	/**
	 * Render the existing creditor
	 *
	 * @return string
	 */
	public function overview() : string {
		return $this->form(
			( new Table() )->render(
				[
					'fields'  => [
						[
							'name'  => 'actor_id',
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
					'items'   => ( new CreditorQuery( $this->business ) )->get_results(),
					'options' => [ 'button_create' => __( 'New supplier', 'wpacc' ) ],
				]
			)
		);

	}

}
