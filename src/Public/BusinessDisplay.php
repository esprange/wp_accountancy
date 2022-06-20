<?php
/**
 * The business display handler.
 *
 * @since      1.0.0
 *
 * @package    WP-Accountancy
 * @subpackage WP-Accountancy/Public
 */

namespace WP_Accountancy\Public;

use WP_Accountancy\Includes\Business;
use WP_Accountancy\Includes\BusinessQuery;
use WP_Accountancy\Includes\ChartOfAccounts;

/**
 * The Public filters.
 */
class BusinessDisplay extends Display {

	/**
	 * Provide the top title
	 *
	 * @return string
	 */
	public function get_title(): string {
		return __( 'Businesses', 'wpacc' );
	}

	/**
	 * Create the business.
	 *
	 * @return string
	 */
	public function create() : string {
		return $this->read();
	}

	/**
	 * Select the business
	 *
	 * @return string
	 */
	public function select() : string {
		$input       = filter_input_array( INPUT_POST );
		$selected    = sanitize_text_field( $input['selected'] ?? 0 );
		$business_id = intval( strtok( $selected, '|' ) );
		if ( $business_id ) {
			do_action( 'wpacc_business_select', $business_id );
			return $this->notify( 1, __( 'Business selected', 'wpacc' ) );
		}
		return $this->notify( 0, __( 'Internal error' ) );
	}

	/**
	 * Update the business.
	 *
	 * @return string
	 */
	public function update() : string {
		$input             = filter_input_array( INPUT_POST );
		$business          = new Business( intval( $input['id'] ?? 0 ) );
		$import            = ! $business->id;
		$business->name    = sanitize_text_field( $input['name'] ?? '' );
		$business->address = sanitize_textarea_field( $input['address'] ?? '' );
		$business->country = sanitize_text_field( $input['country'] ?? '' );
		$business->slug    = sanitize_title( $input['name'] ?? '' );
		$logo              = $this->upload_logo();
		if ( $logo ) {
			if ( isset( $logo['error'] ) ) {
				return $this->notify( 0, $logo['error'] );
			}
			$business->logo     = $logo['file'];
			$business->logo_url = $logo['url'];
		}
		$business->update();
		do_action( 'wpacc_business_select', $business->id );
		if ( $import ) {
			$coa = new ChartOfAccounts();
			$coa->import( WPACC_PLUGIN_PATH . 'Templates\\' . Business::COUNTRIES[ $business->country ]['template'] );
		}
		return $this->notify( 1, __( 'Business saved', 'wpacc' ) );
	}

	/**
	 * Delete the business
	 *
	 * @return string
	 */
	public function delete() : string {
		global $wpacc_business;
		$business_id = filter_input( INPUT_GET, 'business_id', FILTER_SANITIZE_NUMBER_INT );
		if ( $business_id ) {
			$business = new Business( $business_id );
			if ( $business->delete() ) {
				if ( $wpacc_business->id === $business_id ) {
					do_action( 'wpacc_business_select', 0 );
				}
				return $this->notify( - 1, __( 'Business removed', 'wpacc' ) );
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
		$business = new Business( intval( filter_input( INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT ) ) );
		return $this->form(
			$this->field->render(
				[
					'name'     => 'name',
					'value'    => $business->name,
					'label'    => __( 'Name', 'wpacc' ),
					'required' => true,
				]
			) . $this->field->render(
				[
					'name'  => 'country',
					'value' => $business->country,
					'label' => __( 'Country', 'wpacc' ),
					'type'  => 'select',
					'list'  => iterator_to_array( $business->countries() ),
				]
			) . $this->field->render(
				[
					'name'  => 'logo',
					'value' => $business->logo_url,
					'type'  => 'image',
					'label' => __( 'Logo', 'wpacc' ),
				]
			) . $this->field->render(
				[
					'name'  => 'address',
					'value' => $business->address,
					'label' => __( 'Address', 'wpacc' ),
					'type'  => 'textarea',
				]
			) . $this->field->render(
				[
					'name'  => 'id',
					'value' => $business->id,
					'label' => '',
					'type'  => 'hidden',
				]
			) .
			$this->button->save( __( 'Save', 'wpacc' ) ) . ( $business->id ? $this->button->delete( __( 'Delete', 'wpacc' ) ) : '' )
		);
	}

	/**
	 * Render the existing business
	 *
	 * @return string
	 */
	public function overview() : string {
		return $this->form(
			$this->table->render(
				[
					'fields'  => [
						[
							'name'  => 'business_id',
							'type'  => 'static',
							'label' => '',
						],
						[
							'name'  => 'selected',
							'type'  => 'radio',
							'label' => __( 'Selected', 'wpacc' ),
						],
						[
							'name'  => 'name',
							'type'  => 'zoom',
							'label' => __( 'Name', 'wpacc' ),
						],
					],
					'items'   => ( new BusinessQuery() )->get_results(),
					'options' => [
						'button_create' => __( 'New business', 'wpacc' ),
						'button_select' => __( 'Select business', 'wpacc' ),
					],
				],
			)
		);
	}

	/**
	 * Upload the logo file
	 *
	 * @return array
	 */
	private function upload_logo() : array {
		/**
		 * Include voor image file upload.
		 */
		require_once ABSPATH . 'wp-admin/includes/file.php';
		$logo = $_FILES['logo'];
		if ( empty( $logo ) || 4 === $logo['error'] ) {
			return [];
		}
		if ( $logo['error'] ) {
			return [ 'error' => $logo['error'] ];
		}
		if ( $logo['size'] > wp_max_upload_size() ) {
			return [ 'error' => __( 'Logo file size is too large', 'wpacc' ) ];
		}
		if ( ! in_array( mime_content_type( $logo['tmp_name'] ), get_allowed_mime_types(), true ) ) {
			return [ 'error' => __( 'WordPress doesn\'t allow this type of uploads.', 'wpacc' ) ];
		}
		return wp_handle_upload(
			$logo,
			[ 'test_form' => false ]
		);
	}
}
