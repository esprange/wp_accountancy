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
 * @property string logo
 * @property string slug
 * @property bool   active,
 */
class Business {

	/**
	 * Constructor
	 *
	 * @param int $business_id  The business id, if specified, the business is retrieved from the db.
	 */
	public function __construct( int $business_id = 0 ) {
		$data = [
			'id'      => $business_id,
			'name'    => '',
			'slug'    => '',
			'address' => '',
			'country' => '',
			'logo'    => '',
		];
		if ( $business_id ) {
			global $wpdb;
			$result = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT * FROM {$wpdb->prefix}wpacc_business where id = %d",
					$business_id
				)
			);
			if ( $result ) {
				$data = $result;
			}
		}
		foreach ( $data as $property => $value ) {
			$this->$property = $value;
		}
	}

	/**
	 * Update the business.
	 *
	 * @return int The business id.
	 */
	public function update() : int {
		global $wpdb;
		$data = [
			'id'      => $this->id,
			'name'    => $this->name,
			'address' => $this->address,
			'country' => $this->country,
			'logo'    => $this->logo,
			'slug'    => $this->slug,
		];
		$wpdb->replace( "{$wpdb->prefix}wpacc_business", $data );
		$this->id = $wpdb->insert_id;
		return $this->id;
	}

	/**
	 * Delete the business entry.
	 *
	 * @return bool
	 */
	public function delete() : bool {
		global $wpdb;
		return (bool) $wpdb->delete( "{$wpdb->prefix}wpacc_business", [ 'id' => $this->id ] );
	}
}
