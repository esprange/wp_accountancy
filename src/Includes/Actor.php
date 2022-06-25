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
class Actor extends Entity {

	/**
	 * Constructor
	 *
	 * @param Business $business The business.
	 * @param int      $actor_id The actor id, if specified, the actor is retrieved from the db.
	 */
	public function __construct( Business $business, int $actor_id = 0 ) {
		$this->fetch(
			[
				'id'              => $actor_id,
				'business_id'     => $business->id,
				'name'            => '',
				'address'         => '',
				'billing_address' => '',
				'email_address'   => '',
				'active'          => true,
				'type'            => '',
			],
			[
				'id'              => 'int',
				'business_id'     => 'int',
				'name'            => 'string',
				'address'         => 'string',
				'billing_address' => 'string',
				'email_address'   => 'string',
				'active'          => 'bool',
				'type'            => 'string',
			]
		);
	}

	/**
	 * Return the table name
	 *
	 * @return string
	 */
	public function tablename(): string {
		return 'wpacc_actor';
	}

}
