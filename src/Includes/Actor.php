<?php
/**
 * Definition actor class
 *
 * @since      1.0.0
 *
 * @package    WP-Accountancy
 * @subpackage WP-Accountacy/Includes
 */

namespace WP_Accountancy\Includes;

/**
 * Actor class.
 *
 * @property int    id
 * @property int    business_id
 * @property string name
 * @property string address
 * @property string billing_address
 * @property string email_address
 * @property string type
 * @property bool   active,
 */
class Actor {

	/**
	 * Constructor
	 *
	 * @param int $actor_id  The actor id, if specified, the actor is retrieved from the db.
	 */
	public function __construct( int $actor_id ) {
		global $wpacc_business;
		$data = [
			'id'              => $actor_id,
			'business_id'     => $wpacc_business->id,
			'name'            => '',
			'address'         => '',
			'billing_address' => '',
			'email_address'   => '',
			'active'          => true,
			'type'            => '',
		];
		global $wpdb;
		$result = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}wpacc_actor where id = %d",
				$actor_id
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
	 * Update the actor.
	 *
	 * @return int The actor id.
	 */
	public function update() : int {
		global $wpdb;
		global $wpacc_business;
		$data = [
			'id'              => $this->id,
			'business_id'     => $wpacc_business->id,
			'name'            => $this->name,
			'address'         => $this->address,
			'billing_address' => $this->billing_address,
			'email_address'   => $this->email_address,
			'active'          => (int) $this->active,
			'type'            => $this->type,
		];
		$wpdb->replace( "{$wpdb->prefix}wpacc_actor", $data );
		$this->id = $wpdb->insert_id;
		return $this->id;
	}

	/**
	 * Delete the actor entry.
	 *
	 * @return bool
	 */
	public function delete() : bool {
		global $wpdb;
		return (bool) $wpdb->delete( "{$wpdb->prefix}wpacc_actor", [ 'id' => $this->id ] );
	}

}
