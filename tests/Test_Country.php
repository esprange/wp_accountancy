<?php
/**
 * Class Country Test
 *
 * @package WP-Accountancy
 */

namespace WP_Accountancy\Tests;

use WP_Accountancy\Includes\Country;

/**
 * Country test cases.
 */
class Test_Country extends UnitTestCase {

	/**
	 * Test creating an account.
	 *
	 * @return void
	 */
	public function test_create() : void {
		$country1       = new Country( 'Test', 'english' );
		$country1->file = 'test';
		$country1->insert();

		$country2 = new Country( 'Test', 'english' );
		$this->assertEquals( 'test', $country2->file, 'Country store incorrect' );
	}

	/**
	 * Test an update
	 *
	 * @return void
	 */
	public function test_update() : void {
		$country1       = new Country( 'Test', 'english' );
		$country1->file = 'test';
		$country1->insert();

		$country2       = new Country( 'Test', 'english' );
		$country2->file = 'test_again';
		$country2->insert();

		$country3 = new Country( 'Test', 'english' );
		$this->assertEquals( 'test_again', $country3->file, 'Country update incorrect' );
	}

}
