<?php
/**
 * Class Detail Test
 *
 * @package WP-Accountancy
 */

namespace WP_Accountancy\Tests;

use WP_Accountancy\Includes\Detail;
use WP_Accountancy\Includes\Transaction;
use WP_Accountancy\Includes\DetailQuery;

/**
 * Detail test cases.
 */
class Test_Detail extends UnitTestCase {

	/**
	 * Test creating an detail.
	 *
	 * @return void
	 */
	public function test_create() : void {
		$transaction_id = ( new Transaction() )->update();

		$detail1              = new Detail( $transaction_id );
		$detail1->description = 'test-detail';
		$detail1->unitprice   = 543.21;
		$detail_id            = $detail1->update();
		$detail2              = new Detail( $transaction_id, $detail_id );
		$this->assertEquals( get_object_vars( $detail1 ), get_object_vars( $detail2 ), 'detail store incorrect' );
	}

	/**
	 * Test an update
	 *
	 * @return void
	 */
	public function test_update() : void {
		$quantity       = 5;
		$transaction_id = ( new Transaction() )->update();

		$detail1              = new Detail( $transaction_id );
		$detail1->description = 'test-detail';
		$detail1->unitprice   = 543.21;
		$detail_id            = $detail1->update();

		$detail2           = new detail( $transaction_id, $detail_id );
		$detail2->quantity = $quantity;
		$detail2->update();

		$detail3 = new Detail( $transaction_id, $detail_id );
		$this->assertEquals( $quantity, $detail3->quantity, 'detail update incorrect' );
	}

	/**
	 * Test delete
	 *
	 * @return void
	 */
	public function test_delete() : void {
		$transaction_id       = ( new Transaction() )->update();
		$detail1              = new Detail( $transaction_id );
		$detail1->description = 'test-detail';
		$detail1->unitprice   = 543.21;
		$detail_id            = $detail1->update();

		$detail2 = new Detail( $transaction_id, $detail_id );
		$detail2->delete();

		$detail3 = new Detail( $transaction_id, $detail_id );
		$this->assertEquals( 0, $detail3->id, 'detail delete incorrect' );
	}

	/**
	 * Test the query
	 *
	 * @return void
	 */
	public function test_query() : void {
		$amount         = 5;
		$transaction_id = ( new Transaction() )->update();
		for ( $index = 0; $index < $amount; $index++ ) {
			$detail              = new Detail( $transaction_id );
			$detail->description = "test-detail_$index";
			$detail->update();
		}
		$query = ( new DetailQuery() )->get_results();
		$this->assertEquals( $amount, count( $query ), 'detail query count incorrect' );
	}
}
