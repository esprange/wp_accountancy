<?php
/**
 * Definition creditor class
 *
 * @since      1.0.0
 *
 * @package    WP-Accountancy
 * @subpackage WP-Accountacy/Includes
 */

namespace WP_Accountancy\Includes;

/**
 * Creditor class.
 *
 * @property int    id
 * @property int    business_id
 * @property string name
 * @property string address
 * @property string email_address
 * @property bool   active,
 */
class Creditor {

	/**
	 * Constructor
	 *
	 * @param int $business_id The business_id, required.
	 * @param int $creditor_id  The creditor id, if specified, the creditor is retrieved from the db.
	 */
	public function __construct( int $business_id, int $creditor_id = 0 ) {
		$data = [
			'id'            => $creditor_id,
			'business_id'   => $business_id,
			'name'          => '',
			'address'       => '',
			'email_address' => '',
			'active'        => true,
		];
		if ( $business_id ) {
			global $wpdb;
			$result = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT * FROM {$wpdb->prefix}wpacc_creditor where id = %d",
					$creditor_id
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
	 * Update the debtor.
	 *
	 * @return int The debtor id.
	 */
	public function update() : int {
		if ( $this->business_id ) {
			global $wpdb;
			$data = [
				'id'            => $this->id,
				'business_id'   => $this->business_id,
				'name'          => $this->name,
				'address'       => $this->address,
				'email_address' => $this->email_address,
				'active'        => (int) $this->active,
			];
			$wpdb->replace( "{$wpdb->prefix}wpacc_creditor", $data );
			$this->id = $wpdb->insert_id;
			return $this->id;
		}
		return 0;
	}

}
