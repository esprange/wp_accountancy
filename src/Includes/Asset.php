<?php
/**
 * Definition asset class
 *
 * @since      1.0.0
 *
 * @package    WP-Accountancy
 * @subpackage WP-Accountacy/Includes
 */

namespace WP_Accountancy\Includes;

/**
 * Asset class.
 *
 * @property int    id
 * @property int    business_id
 * @property string name
 * @property string rate
 * @property string billing_address
 * @property string description
 * @property float  cost
 * @property float  provision
 * @property bool   active
 */
class Asset {

	/**
	 * Constructor
	 *
	 * @param int $asset_id  The asset id, if specified, the asset is retrieved from the db.
	 */
	public function __construct( int $asset_id = 0 ) {
		global $wpacc_business;
		$data = [
			'id'          => $asset_id,
			'business_id' => $wpacc_business->id,
			'name'        => '',
			'description' => '',
			'rate'        => 0.0,
			'cost'        => 0.0,
			'provision'   => 0.0,
			'active'      => true,
		];
		global $wpdb;
		$result = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}wpacc_asset where id = %d",
				$asset_id
			)
		);
		if ( $result ) {
			$data = $result;
		}
		foreach ( $data as $property => $value ) {
			$this->$property = $value;
		}
	}

	/**
	 * Update the asset.
	 *
	 * @return int The asset id.
	 */
	public function update() : int {
		global $wpdb;
		global $wpacc_business;
		$data = [
			'id'          => $this->id,
			'business_id' => $wpacc_business->id,
			'name'        => $this->name,
			'description' => $this->description,
			'rate'        => $this->rate,
			'cost'        => $this->cost,
			'provision'   => $this->provision,
			'active'      => (int) $this->active,
		];
		$wpdb->replace( "{$wpdb->prefix}wpacc_asset", $data );
		$this->id = $wpdb->insert_id;
		return $this->id;
	}

	/**
	 * Delete the asset entry.
	 *
	 * @return bool
	 */
	public function delete() : bool {
		global $wpdb;
		return (bool) $wpdb->delete( "{$wpdb->prefix}wpacc_asset", [ 'id' => $this->id ] );
	}

}
