<?php
/**
 * Class Transaction Test
 *
 * @package WP-Accountancy
 */

namespace WP_Accountancy\Tests;

use WP_Accountancy\Includes\Transaction;
use WP_Accountancy\Includes\TransactionQuery;

/**
 * Transaction test cases.
 */
class Test_Transaction extends UnitTestCase {

	/**
	 * Test creating an transaction.
	 *
	 * @return void
	 */
	public function test_create() : void {
		$transaction1            = new Transaction();
		$transaction1->reference = 'test-transaction';
		$transaction1->type      = 'test';
		$transaction_id          = $transaction1->update();

		$transaction2 = new Transaction( $transaction_id );
		$this->assertEquals( get_object_vars( $transaction1 ), get_object_vars( $transaction2 ), 'transaction store incorrect' );
	}

	/**
	 * Test an update
	 *
	 * @return void
	 */
	public function test_update() : void {
		$date = '2022-01-02';

		$transaction1            = new Transaction();
		$transaction1->reference = 'test-transaction';
		$transaction1->type      = 'test';
		$transaction_id          = $transaction1->update();

		$transaction2       = new transaction( $transaction_id );
		$transaction2->date = $date;
		$transaction2->update();

		$transaction3 = new Transaction( $transaction_id );
		$this->assertEquals( $date, $transaction3->date, 'transaction update incorrect' );
	}

	/**
	 * Test delete
	 *
	 * @return void
	 */
	public function test_delete() : void {
		$transaction1            = new Transaction();
		$transaction1->reference = 'test-transaction';
		$transaction1->type      = 'test';
		$transaction_id          = $transaction1->update();

		$transaction2 = new Transaction( $transaction_id );
		$transaction2->delete();

		$transaction3 = new Transaction( $transaction_id );
		$this->assertEquals( 0, $transaction3->id, 'transaction delete incorrect' );
	}

	/**
	 * Test the query
	 *
	 * @return void
	 */
	public function test_query() : void {
		$amount = 5;
		for ( $index = 0; $index < $amount; $index++ ) {
			$transaction            = new Transaction();
			$transaction->reference = "test-transaction_$index";
			$transaction->update();
		}
		$query = ( new TransactionQuery() )->get_results();
		$this->assertEquals( $amount, count( $query ), 'transaction query count incorrect' );
	}
}
