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
		global $wpacc_business;
		$input                   = filter_input_array( INPUT_POST );
		$creditor                = new Creditor( intval( $input['id'] ?? 0 ) );
		$creditor->name          = sanitize_text_field( $input['name'] ?? '' );
		$creditor->address       = sanitize_textarea_field( $input['address'] ?? '' );
		$creditor->email_address = sanitize_email( $input['email_address'] ?? '' );
		$creditor->active        = boolval( $input['active'] ?? false );
		$creditor->business_id   = $wpacc_business->id;
		$creditor->update();
		return $this->notify( -1, __( 'Supplier saved', 'wpacc' ) );
	}

	/**
	 * Delete the creditor
	 *
	 * @return string
	 */
	public function delete() : string {
		$creditor_id = filter_input( INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT );
		if ( $creditor_id ) {
			$creditor = new Creditor( intval( $creditor_id ) );
			if ( $creditor->delete() ) {
				return $this->notify( - 1, __( 'Supplier removed', 'wpacc' ) );
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
		$creditor_id = filter_input( INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT );
		$creditor    = new Creditor( intval( $creditor_id ) );
		$forms       = new Forms();
		$html        =
			$forms->form_field( [ 'name' => 'name', 'value' => $creditor->name, 'label' => __( 'Name', 'wpacc' ), 'required' => true ] ) .
			$forms->form_field( [ 'name' => 'address', 'type' => 'textarea', 'value' => $creditor->address, 'label'=> __( 'Address', 'wpacc' ) ] ) .
			$forms->form_field( [ 'name' => 'email_address', 'type' => 'email', 'value' => $creditor->email_address, 'label' => __( 'Email', 'wpacc' ) ] ) .
			$forms->form_field( [ 'name' => 'active', 'type' => 'checkbox', 'value' => $creditor->active, 'label' => __( 'Active', 'wpacc' ) ] ) .
			$forms->form_field( [ 'name' => 'id', 'type' => 'hidden', 'value' => $creditor->id ] ) .
			$forms->action_button( 'update', __( 'Save', 'wpacc' ) ) .
			( $creditor->id ? $forms->action_button( 'delete', __( 'Delete', 'wpacc' ), false ) : '' );
		return $this->form( $html );
	}

	/**
	 * Render the existing creditor
	 *
	 * @return string
	 */
	public function overview() : string {
		$creditors = new CreditorQuery();
		$forms     = new Forms();
		return $this->form( $forms->table( ['id' => 'id', 'name' => __( 'Name', 'wpacc' ) ], $creditors->get_results() ) );
	}

}
