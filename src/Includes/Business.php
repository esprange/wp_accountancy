<?php
/**
 * Definition business class
 *
 * @since      1.0.0
 *
 * @package    WP-Accountancy
 * @subpackage WP-Accountacy/Includes
 */

namespace WP_Accountancy\Includes;

/**
 * Business class.
 *
 * @property int    id
 * @property string name
 * @property string address
 * @property string country
 * @property string language
 * @property string currency
 * @property string decimals
 * @property string decimalsep
 * @property string thousandsep
 * @property string dateformat
 * @property string timeformat
 * @property string logo
 * @property string slug
 * @property bool   active,
 */
class Business extends Entity {

	/**
	 * Constructor
	 *
	 * @param int $business_id  The business id, if specified, the business is retrieved from the db.
	 */
	public function __construct( int $business_id = 0 ) {
		$this->fetch(
			[
				'id'          => $business_id,
				'name'        => null,
				'slug'        => '',
				'address'     => '',
				'country'     => '',
				'language'    => '',
				'currency'    => '',
				'decimals'    => 2,
				'decimalsep'  => '',
				'thousandsep' => '',
				'dateformat'  => 'd-m-Y',
				'timeformat'  => 'H:i',
				'logo'        => '',
				'logo_url'    => '',
			],
			[
				'id'          => 'int',
				'name'        => 'string',
				'slug'        => 'string',
				'address'     => 'string',
				'country'     => 'string',
				'language'    => 'string',
				'currency'    => 'string',
				'decimals'    => 'int',
				'decimalsep'  => 'string',
				'thousandsep' => 'string',
				'dateformat'  => 'string',
				'timeformat'  => 'string',
				'logo'        => 'string',
				'logo_url'    => 'string',
			]
		);
	}

	/**
	 * Return the table name
	 *
	 * @return string
	 */
	public function tablename() : string {
		return 'wpacc_business';
	}

}
