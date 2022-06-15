<?php
/**
 * Class Account Test
 *
 * @package WP-Accountancy
 */

namespace WP_Accountancy\Tests;

use WP_Accountancy\Includes\Account;
use WP_Accountancy\Includes\AccountQuery;

/**
 * Account test cases.
 */
class Test_Account extends UnitTestCase {

	/**
	 * Test creating an account.
	 *
	 * @return void
	 */
	public function test_create() : void {
		$account1                = new Account();
		$account1->name          = 'test-account';
		$account1->type          = 'test';
		$account1->initial_value = 123.4;
		$account_id              = $account1->update();

		$account2 = new Account( $account_id );
		$this->assertEquals( get_object_vars( $account1 ), get_object_vars( $account2 ), 'Account store incorrect' );
	}

	/**
	 * Test an update
	 *
	 * @return void
	 */
	public function test_update() : void {
		$account1                = new Account();
		$account1->name          = 'test-account';
		$account1->type          = 'test';
		$account1->initial_value = 123.4;
		$account_id              = $account1->update();

		$account2         = new Account( $account_id );
		$account2->active = false;
		$account2->update();

		$account3 = new Account( $account_id );
		$this->assertFalse( $account3->active, 'Account update incorrect' );
	}

	/**
	 * Test delete
	 *
	 * @return void
	 */
	public function test_delete() : void {
		$account1                = new Account();
		$account1->name          = 'test-account';
		$account1->type          = 'test';
		$account1->initial_value = 123.4;
		$account_id              = $account1->update();

		$account2 = new Account( $account_id );
		$account2->delete();

		$account3 = new Account( $account_id );
		$this->assertEquals( 0, $account3->id, 'Account delete incorrect' );
	}

	/**
	 * Test the query
	 *
	 * @return void
	 */
	public function test_query() : void {
		$amount = 5;
		for ( $index = 0; $index < $amount; $index++ ) {
			$account       = new Account();
			$account->name = "test-account_$index";
			$account->type = 'test';
			$account->update();
		}
		$query = ( new AccountQuery() )->get_results();
		$this->assertEquals( $amount, count( $query ), 'Account query count incorrect' );
	}
}
