<?php
/**
 * Definition debtor class
 *
 * @since      1.0.0
 *
 * @package    WP-Accountancy
 * @subpackage WP-Accountacy/Includes
 */

namespace WP_Accountancy\Includes;

/**
 * Debtor class.
 */
class Debtor extends Actor {

	public const TYPE = 'debtor';

	/**
	 * Constructor
	 *
	 * @param Business $business The business.
	 * @param int      $actor_id The debtor id, if specified, the debtor is retrieved from the db.
	 */
	public function __construct( Business $business, int $actor_id = 0 ) {
		parent::__construct( $business, $actor_id );
		$this->type = self::TYPE;
	}
}
