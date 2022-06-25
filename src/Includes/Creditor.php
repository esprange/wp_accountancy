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
 */
class Creditor extends Actor {

	public const TYPE = 'creditor';

	/**
	 * Constructor
	 *
	 * @param Business $business The business.
	 * @param int      $actor_id The creditor id, if specified, the creditor is retrieved from the db.
	 */
	public function __construct( Business $business, int $actor_id = 0 ) {
		parent::__construct( $business, $actor_id );
		$this->type = self::TYPE;
	}
}
