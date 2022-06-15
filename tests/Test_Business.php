<?php
/**
 * Class Business Test
 *
 * @package WP-Accountancy
 */

namespace WP_Accountancy\Tests;

use WP_Accountancy\Includes\Business;
use WP_Accountancy\Includes\BusinessQuery;

/**
 * Business test cases.
 */
class Test_Business extends UnitTestCase {

	/**
	 * Test creating an business.
	 *
	 * @return void
	 */
	public function test_create() : void {
		$business1       = new Business();
		$business1->name = 'test business';
		$business1->slug = 'test';
		$business_id     = $business1->update();

		$business2 = new Business( $business_id );
		$this->assertEquals( get_object_vars( $business1 ), get_object_vars( $business2 ), 'business store incorrect' );
	}

	/**
	 * Test an update
	 *
	 * @return void
	 */
	public function test_update() : void {
		$country = 'UK';

		$business1       = new Business();
		$business1->name = 'test-business';
		$business1->slug = 'test';
		$business_id     = $business1->update();

		$business2          = new business( $business_id );
		$business2->country = $country;
		$business2->update();

		$business3 = new Business( $business_id );
		$this->assertEquals( $country, $business3->country, 'business update incorrect' );
	}

	/**
	 * Test delete
	 *
	 * @return void
	 */
	public function test_delete() : void {
		$business1       = new Business();
		$business1->name = 'test-business';
		$business1->slug = 'test';
		$business_id     = $business1->update();

		$business2 = new Business( $business_id );
		$business2->delete();

		$business3 = new Business( $business_id );
		$this->assertEquals( 0, $business3->id, 'business delete incorrect' );
	}

	/**
	 * Test the query
	 *
	 * @return void
	 */
	public function test_query() : void {
		$amount = 5;
		// Start with 1 as there is already a default business.
		for ( $index = 1; $index < $amount; $index++ ) {
			$business       = new Business();
			$business->name = "test-business_$index";
			$business->update();
		}
		$query = ( new BusinessQuery() )->get_results();
		$this->assertEquals( $amount, count( $query ), 'business query count incorrect' );
	}
}
