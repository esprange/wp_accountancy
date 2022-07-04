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
	 * @param Business $business   The business.
	 * @param int      $taxcode_id The taxtcode id, if specified, the taxcode is retrieved from the db.
	 */
	public function __construct( Business $business, int $taxcode_id = 0 ) {
		$this->fetch(
			[
				'id'          => $taxcode_id,
				'business_id' => $business->id,
				'account_id'  => null,
				'name'        => '',
				'rate'        => 0.0,
				'active'      => true,
			],
			[
				'id'          => 'int',
				'business_id' => 'int',
				'taxcode_id'  => 'int',
				'name'        => 'string',
				'rate'        => 'float',
				'active'      => 'bool',
			]
		);
	}

	/**
	 * Return the table name
	 *
	 * @return string
	 */
	public function tablename() : string {
		return 'wpacc_taxcode';
	}

}
