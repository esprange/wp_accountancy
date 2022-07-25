<?php
/**
 * Class ChartOfAccounts Test
 *
 * @package WP-Accountancy
 */

namespace WP_Accountancy\Tests;

use WP_Accountancy\Includes\Business;
use WP_Accountancy\Includes\ChartOfAccounts;

/**
 * Chart Of Accounts test cases.
 */
class Test_ChartOfAccounts extends UnitTestCase {

	/**
	 * Test creating an account.
	 *
	 * @return void
	 */
	public function test_nothing() : void {
		$business           = new Business();
		$business->name     = 'test business';
		$business->slug     = 'test';
		$business->country  = 'Nederland';
		$business->language = 'dutch';
		$business->update();

		new ChartOfAccounts( $this->business );

		$this->assertTrue( true, 'Dummy test Coa' );
	}
}
