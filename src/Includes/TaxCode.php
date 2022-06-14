<?php
/**
 * Definition transaction tax code class
 *
 * @since      1.0.0
 *
 * @package    WP-Accountancy
 * @subpackage WP-Accountacy/Includes
 */

namespace WP_Accountancy\Includes;

/**
 * TaxCode class.
 *
 * @property int    id
 * @property int    business_id
 * @property string name
 * @property float  rate
 * @property bool   active,
 */
class TaxCode extends Entity {

	/**
	 * Constructor
	 *
	 * @param int $taxcode_id  The taxtcode id, if specified, the taxcode is retrieved from the db.
	 */
	public function __construct( int $taxcode_id = 0 ) {
		global $wpacc_business;
		$this->fetch(
			[
				'id'          => $taxcode_id,
				'business_id' => $wpacc_business->id,
				'name'        => '',
				'rate'        => 0.0,
				'active'      => true,
			]
		);
	}
}
