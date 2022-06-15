<?php
/**
 * Class Taxcode Test
 *
 * @package WP-Accountancy
 */

namespace WP_Accountancy\Tests;

use WP_Accountancy\Includes\Taxcode;
use WP_Accountancy\Includes\TaxcodeQuery;

/**
 * Taxcode test cases.
 */
class Test_Taxcode extends UnitTestCase {

	/**
	 * Test creating an taxcode.
	 *
	 * @return void
	 */
	public function test_create() : void {
		$taxcode1       = new Taxcode();
		$taxcode1->name = 'test-taxcode';
		$taxcode1->rate = 0.21;
		$taxcode_id     = $taxcode1->update();

		$taxcode2 = new Taxcode( $taxcode_id );
		$this->assertEquals( get_object_vars( $taxcode1 ), get_object_vars( $taxcode2 ), 'taxcode store incorrect' );
	}

	/**
	 * Test an update
	 *
	 * @return void
	 */
	public function test_update() : void {
		$taxcode1       = new Taxcode();
		$taxcode1->name = 'test-taxcode';
		$taxcode1->rate = 0.2;
		$taxcode_id     = $taxcode1->update();

		$taxcode2         = new taxcode( $taxcode_id );
		$taxcode2->active = false;
		$taxcode2->update();

		$taxcode3 = new Taxcode( $taxcode_id );
		$this->assertFalse( $taxcode3->active, 'taxcode update incorrect' );
	}

	/**
	 * Test delete
	 *
	 * @return void
	 */
	public function test_delete() : void {
		$taxcode1       = new Taxcode();
		$taxcode1->name = 'test-taxcode';
		$taxcode1->rate = 0.2;
		$taxcode_id     = $taxcode1->update();

		$taxcode2 = new Taxcode( $taxcode_id );
		$taxcode2->delete();

		$taxcode3 = new Taxcode( $taxcode_id );
		$this->assertEquals( 0, $taxcode3->id, 'taxcode delete incorrect' );
	}

	/**
	 * Test the query
	 *
	 * @return void
	 */
	public function test_query() : void {
		$amount = 5;
		for ( $index = 0; $index < $amount; $index++ ) {
			$taxcode       = new Taxcode();
			$taxcode->name = "test-taxcode_$index";
			$taxcode->update();
		}
		$query = ( new TaxcodeQuery() )->get_results();
		$this->assertEquals( $amount, count( $query ), 'taxcode query count incorrect' );
	}
}
