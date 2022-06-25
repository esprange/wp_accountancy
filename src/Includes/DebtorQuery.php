<?php
/**
 * Definition debtor query class
 *
 * @since      1.0.0
 *
 * @package    WP-Accountancy
 * @subpackage WP-Accountacy/Includes
 */

namespace WP_Accountancy\Includes;

/**
 * Debtor query class.
 */
class DebtorQuery extends ActorQuery {

	/**
	 * The constructor
	 *
	 * @param Business $business The business.
	 * @param array    $args     The query arguments.
	 *
	 * @return void
	 */
	public function __construct( Business $business, array $args = [] ) {
		$args['type'] = Debtor::TYPE;
		parent::__construct( $business, $args );
	}
}
