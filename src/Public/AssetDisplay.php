<?php
/**
 * The asset display handler.
 *
 * @since      1.0.0
 *
 * @package    WP-Accountancy
 * @subpackage WP-Accountancy/Public
 */

namespace WP_Accountancy\Public;

use WP_Accountancy\Includes\Asset;
use WP_Accountancy\Includes\AssetQuery;

/**
 * The Public filters.
 */
class AssetDisplay extends Display {

	/**
	 * Provide the top title
	 *
	 * @return string
	 */
	public function get_title(): string {
		return __( 'Assets', 'wpacc' );
	}

	/**
	 * Create the asset.
	 *
	 * @return string
	 */
	public function create() : string {
		return $this->read();
	}

	/**
	 * Update the asset.
	 *
	 * @return string
	 */
	public function update() : string {
		global $wpacc_business;
		$input              = filter_input_array( INPUT_POST );
		$asset              = new Asset( $input['asset_id'] ?? 0 );
		$asset->name        = sanitize_text_field( $input['name'] ?? '' );
		$asset->description = sanitize_textarea_field( $input['description'] ?? '' );
		$asset->rate        = floatval( $input['rate'] ?? 0.0 );
		$asset->cost        = floatval( $input['cost'] ?? 0.0 );
		$asset->provision   = floatval( $input['provision'] ?? 0.0 );
		$asset->business_id = $wpacc_business->id;
		$asset->update();
		return $this->notify( 1, __( 'Asset saved', 'wpacc' ) );
	}

	/**
	 * Delete the asset
	 *
	 * @return string
	 */
	public function delete() : string {
		$asset_id = filter_input( INPUT_GET, 'asset_id', FILTER_SANITIZE_NUMBER_INT );
		if ( $asset_id ) {
			$asset = new Asset( $asset_id );
			if ( $asset->delete() ) {
				return $this->$this->notify( - 1, __( 'Asset removed', 'wpacc' ) );
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
		$asset = new Asset( intval( filter_input( INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT ) ) );
		return $this->form(
			$this->field->render(
				[
					'name'     => 'name',
					'value'    => $asset->name,
					'label'    => __( 'Name', 'wpacc' ),
					'required' => true,
				]
			) .
			$this->field->render(
				[
					'name'     => 'description',
					'type'     => 'textarea',
					'value'    => $asset->description,
					'label'    => __( 'Description', 'wpacc' ),
					'required' => true,
				]
			) .
			$this->field->render(
				[
					'name'     => 'rate',
					'type'     => 'float',
					'value'    => $asset->rate,
					'label'    => __( 'Depreciation rate', 'wpacc' ),
					'required' => true,
				]
			) .
			$this->field->render(
				[
					'name'     => 'cost',
					'type'     => 'currency',
					'value'    => $asset->cost,
					'label'    => __( 'Acquisition cost', 'wpacc' ),
					'required' => true,
				]
			) .
			$this->field->render(
				[
					'name'     => 'provision',
					'type'     => 'currency',
					'value'    => $asset->provision,
					'label'    => __( 'Accumulated depreciation', 'wpacc' ),
					'required' => true,
				]
			) .
			$this->field->render(
				[
					'name'  => 'asset_id',
					'type'  => 'hidden',
					'value' => $asset->id,
				]
			) .
			$this->button->save( __( 'Save', 'wpacc' ) ) . ( $asset->id ? $this->button->delete( __( 'Delete', 'wpacc' ) ) : '' )
		);
	}

	/**
	 * Render the existing assets
	 *
	 * @return string
	 */
	public function overview() : string {
		return $this->form(
			$this->table->render(
				[
					'fields'  => [
						[
							'name'  => 'asset_id',
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
					'items'   => ( new AssetQuery() )->get_results(),
					'options' => [ 'button_create' => __( 'New fixed asset', 'wpacc' ) ],
				]
			)
		);
	}

}
