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
class Asset extends Entity {

	/**
	 * Constructor
	 *
	 * @param int $asset_id  The asset id, if specified, the asset is retrieved from the db.
	 */
	public function __construct( int $asset_id = 0 ) {
		global $wpacc_business;
		$this->fetch(
			[
				'id'          => $asset_id,
				'business_id' => $wpacc_business->id,
				'name'        => '',
				'description' => '',
				'rate'        => 0.0,
				'cost'        => 0.0,
				'provision'   => 0.0,
				'active'      => true,
			]
		);
	}
}
