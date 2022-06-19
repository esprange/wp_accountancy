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

use Generator;

/**
 * Business class.
 *
 * @property int    id
 * @property string name
 * @property string address
 * @property string country
 * @property string logo
 * @property string slug
 * @property bool   active,
 */
class Business extends Entity {

	const COUNTRIES = [
		'United Kingdom' => [
			'language' => 'English',
			'template' => 'english.json',
		],
		'United States'  => [
			'language' => 'English',
			'template' => 'english.json',
		],
		'Nederland'      => [
			'language' => 'Nederlands',
			'template' => 'dutch.json',
		],
		'Belgium'        => [
			'language' => 'Nederlands',
			'template' => 'dutch.json',
		],
	];

	/**
	 * Constructor
	 *
	 * @param int $business_id  The business id, if specified, the business is retrieved from the db.
	 */
	public function __construct( int $business_id = 0 ) {
		$this->fetch(
			[
				'id'       => $business_id,
				'name'     => '',
				'slug'     => '',
				'address'  => '',
				'country'  => '',
				'logo'     => '',
				'logo_url' => '',
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

	/**
	 * Return the countries
	 *
	 * @return Generator
	 */
	public function countries() : Generator {
		foreach ( self::COUNTRIES as $country => $details ) {
			yield  strtolower( $country ) => (object) [ 'name' => $country ];
		}
	}
}
