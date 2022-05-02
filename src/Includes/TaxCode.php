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
class TaxCode {

	/**
	 * Constructor
	 *
	 * @param int $business_id The business_id, required.
	 * @param int $taxcode_id  The taxtcode id, if specified, the taxcode is retrieved from the db.
	 */
	public function __construct( int $business_id, int $taxcode_id = 0 ) {
		$data = [
			'id'          => $taxcode_id,
			'business_id' => $business_id,
			'name'        => '',
			'rate'        => 0.0,
			'active'      => true,
		];
		if ( $business_id ) {
			global $wpdb;
			$result = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT * FROM {$wpdb->prefix}wpacc_taxcode where id = %d",
					$taxcode_id
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
	 * Update the taxcode.
	 *
	 * @return int The taxcode id.
	 */
	public function update() : int {
		if ( $this->business_id ) {
			global $wpdb;
			$data = [
				'id'          => $this->id,
				'business_id' => $this->business_id,
				'name'        => $this->name,
				'rate'        => $this->rate,
				'active'      => $this->active,
			];
			$wpdb->replace( "{$wpdb->prefix}wpacc_taxcode", $data );
			$this->id = $wpdb->insert_id;
			return $this->id;
		}
		return 0;
	}

}
