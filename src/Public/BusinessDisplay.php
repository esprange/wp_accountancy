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

use WP_Accountancy\Includes\Account;
use WP_Accountancy\Includes\Business;
use WP_Accountancy\Includes\BusinessQuery;
use WP_Accountancy\Includes\Country;
use WP_Accountancy\Includes\CountryQuery;

/**
 * The Public filters.
 */
class BusinessDisplay extends Display {

	/**
	 * Provide the top title
	 *
	 * @return string
	 */
	public function get_title() : string {
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
		$business_id = intval( $input['selected'] );
		if ( $business_id ) {
			$this->business = new Business( $business_id );
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
		$input                    = filter_input_array( INPUT_POST );
		$this->business           = new Business( intval( $input['id'] ?? 0 ) );
		$import                   = ! $this->business->id;
		$this->business->name     = sanitize_text_field( $input['name'] ?? '' );
		$this->business->address  = sanitize_textarea_field( $input['address'] ?? '' );
		$this->business->country  = strtok( sanitize_text_field( $input['language'] ?? '' ), '|' );
		$this->business->language = strtok( '|' );
		$this->business->slug     = sanitize_title( $input['name'] ?? '' );
		$logo                     = $this->upload_logo();
		if ( $logo ) {
			if ( isset( $logo['error'] ) ) {
				return $this->notify( 0, $logo['error'] );
			}
			$this->business->logo     = $logo['file'];
			$this->business->logo_url = $logo['url'];
		}
		$this->business->update();
		do_action( 'wpacc_business_select', $this->business->id );
		if ( $import ) {
			$this->import();
		}
		return $this->notify( 1, __( 'Business saved', 'wpacc' ) );
	}

	/**
	 * Delete the business
	 *
	 * @return string
	 */
	public function delete() : string {
		$business_id = filter_input( INPUT_POST, 'business_id', FILTER_SANITIZE_NUMBER_INT );

		if ( $business_id ) {
			$business = new Business( $business_id );
			if ( $business->delete() ) {
				if ( $this->business->id === $business_id ) {
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
		$business  = new Business( intval( filter_input( INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT ) ) );
		$countries = new CountryQuery();
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
					'name'     => 'language',
					'value'    => $business->country . '|' . $business->language,
					'label'    => __( 'Country', 'wpacc' ),
					'type'     => 'select',
					'list'     => $countries->get_results(),
					'optgroup' => true,
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
					'name'  => 'business_id',
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
					'items'   => ( new BusinessQuery( [ 'show_selected' => $this->business->id ] ) )->get_results(),
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
	 * @suppressWarnings(PHPMD.Superglobals)
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

	/**
	 * Import a chart of accounts.
	 *
	 * @return void
	 */
	private function import() : void {
		$country  = new Country( $this->business->country, $this->business->language );
		$coa_data = file_get_contents( __DIR__ . "\..\Templates\\$country->file.json" ); // phpcs:ignore
		if ( false === $coa_data ) {
			trigger_error( "Error loading coa, file $country->file cannot be opened", E_USER_ERROR ); // phpcs:ignore
		}
		$account_items = json_decode( $coa_data );
		if ( $account_items ) {
			foreach ( $account_items as $item ) {
				$account       = new Account( $this->business );
				$account->name = $item->name;
				$account->type = $item->type;
				$account->update();
			}
			return;
		}
		trigger_error( "Error loading coa, file $country->file, no data", E_USER_ERROR ); // phpcs:ignore
	}

}
