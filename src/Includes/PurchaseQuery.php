<?php
/**
 * Definition sales query class
 *
 * @since      1.0.0
 *
 * @package    WP-Accountancy
 * @subpackage WP-Accountacy/Includes
 */

namespace WP_Accountancy\Includes;

/**
 * Account query class.
 */
class PurchaseQuery extends InvoiceQuery {

	/**
	 * The constructor
	 *
	 * @param Business $business The business.
	 * @param array    $args     The query arguments.
	 *
	 * @return void
	 */
	public function __construct( Business $business, array $args = [] ) {
		$args['type'] = Transaction::PURCHASE_INVOICE;
		parent::__construct( $business, $args );
	}

}
