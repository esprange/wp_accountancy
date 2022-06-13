<?php
/**
 * Definition creditor query class
 *
 * @since      1.0.0
 *
 * @package    WP-Accountancy
 * @subpackage WP-Accountacy/Includes
 */

namespace WP_Accountancy\Includes;

/**
 * Creditor query class.
 */
class CreditorQuery extends ActorQuery {

	/**
	 * The constructor
	 *
	 * @param array $args The query arguments.
	 *
	 * @return void
	 */
	public function __construct( array $args = [] ) {
		$args['type'] = Creditor::TYPE;
		parent::__construct( $args );
	}
}
