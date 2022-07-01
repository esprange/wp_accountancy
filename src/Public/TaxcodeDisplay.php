<?php
/**
 * The taxcode display handler.
 *
 * @since      1.0.0
 *
 * @package    WP-Accountancy
 * @subpackage WP-Accountancy/Public
 */

namespace WP_Accountancy\Public;

use WP_Accountancy\Includes\AccountQuery;
use WP_Accountancy\Includes\TaxCode;
use WP_Accountancy\Includes\TaxCodeQuery;

/**
 * The Public filters.
 */
class TaxcodeDisplay extends Display {

	/**
	 * Provide the top title
	 *
	 * @return string
	 */
	public function get_title(): string {
		return __( 'Taxcodes', 'wpacc' );
	}

	/**
	 * Create the purchase.
	 *
	 * @return string
	 */
	public function create() : string {
		return $this->read();
	}

	/**
	 * Update the purchase.
	 *
	 * @return string
	 */
	public function update() : string {
		$input           = filter_input_array( INPUT_POST );
		$taxcode         = new Taxcode( $this->business, intval( $input['id'] ?? 0 ) );
		$taxcode->name   = sanitize_text_field( $input['name'] ?? '' );
		$taxcode->rate   = floatval( $input['rate'] ?? '' );
		$taxcode->active = boolval( $input['active'] ?? true );
		$taxcode->update();
		return $this->notify( 1, __( 'Taxcode saved', 'wpacc' ) );
	}

	/**
	 * Delete the taxcode
	 *
	 * @return string
	 */
	public function delete() : string {
		$taxcode_id = filter_input( INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT );
		if ( $taxcode_id ) {
			$taxcode = new TaxCode( $this->business, intval( $taxcode_id ) );
			if ( $taxcode->delete() ) {
				return $this->notify( - 1, __( 'Taxcode removed', 'wpacc' ) );
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
		$taxcode = new TaxCode( $this->business, intval( filter_input( INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT ) ) );
		return $this->form(
			$this->field->render(
				[
					'name'     => 'name',
					'type'     => 'text',
					'value'    => $taxcode->name,
					'label'    => __( 'Name', 'wpacc' ),
					'required' => true,
				]
			) .
			$this->field->render(
				[
					'name'     => 'rate',
					'type'     => 'float',
					'value'    => $taxcode->rate,
					'label'    => __( 'Rate', 'wpacc' ),
					'required' => true,
				]
			) .
			$this->field->render(
				[
					'name'     => 'account_id',
					'type'     => 'select',
					'list'     => ( new AccountQuery( $this->business ) )->get_results(),
					'required' => true,
					'label'    => __( 'Account', 'wpacc' ),
				]
			) .
			$this->field->render(
				[
					'name'  => 'active',
					'type'  => 'checkbox',
					'value' => $taxcode->active,
					'label' => __( 'Active', 'wpacc' ),
				]
			) .
			$this->field->render(
				[
					'name'  => 'id',
					'type'  => 'hidden',
					'value' => $taxcode->id,
				]
			) .
			$this->button->save( __( 'Save', 'wpacc' ) ) .
			( $taxcode->id ? $this->button->delete( __( 'Delete', 'wpacc' ) ) : '' )
		);
	}

	/**
	 * Render the existing purchases
	 *
	 * @return string
	 */
	public function overview() : string {
		$taxcodes = new TaxCodeQuery( $this->business );
		return $this->form(
			$this->table->render(
				[
					'fields'  => [
						[
							'name'  => 'taxcode_id',
							'label' => 'id',
							'type'  => 'static',
						],
						[
							'name'  => 'name',
							'label' => __( 'Name', 'wpacc' ),
							'type'  => 'zoom',
						],
					],
					'items'   => $taxcodes->get_results(),
					'options' => [ 'button_create' => __( 'New tax code', 'wpacc' ) ],
				]
			)
		);
	}

}
