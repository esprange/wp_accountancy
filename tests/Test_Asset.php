<?php
/**
 * Class Asset Test
 *
 * @package WP-Accountancy
 */

namespace WP_Accountancy\Tests;

use WP_Accountancy\Includes\Asset;
use WP_Accountancy\Includes\AssetQuery;

/**
 * Asset test cases.
 */
class Test_Asset extends UnitTestCase {

	/**
	 * Test creating an asset.
	 *
	 * @return void
	 */
	public function test_create() : void {
		$asset1       = new Asset();
		$asset1->name = 'test-asset';
		$asset1->cost = 123.45;
		$asset_id     = $asset1->update();

		$asset2 = new Asset( $asset_id );
		$this->assertEquals( get_object_vars( $asset1 ), get_object_vars( $asset2 ), 'asset store incorrect' );
	}

	/**
	 * Test an update
	 *
	 * @return void
	 */
	public function test_update() : void {
		$rate = 0.2;

		$asset1       = new Asset();
		$asset1->name = 'test-asset';
		$asset1->cost = 123.45;
		$asset_id     = $asset1->update();

		$asset2       = new asset( $asset_id );
		$asset2->rate = $rate;
		$asset2->update();

		$asset3 = new Asset( $asset_id );
		$this->assertEquals( $rate, $asset3->rate, 'asset update incorrect' );
	}

	/**
	 * Test delete
	 *
	 * @return void
	 */
	public function test_delete() : void {
		$asset1       = new Asset();
		$asset1->name = 'test-asset';
		$asset1->type = 'test';
		$asset_id     = $asset1->update();

		$asset2 = new Asset( $asset_id );
		$asset2->delete();

		$asset3 = new Asset( $asset_id );
		$this->assertEquals( 0, $asset3->id, 'asset delete incorrect' );
	}

	/**
	 * Test the query
	 *
	 * @return void
	 */
	public function test_query() : void {
		$amount = 5;
		for ( $index = 0; $index < $amount; $index++ ) {
			$asset       = new Asset();
			$asset->name = "test-asset_$index";
			$asset->update();
		}
		$query = ( new AssetQuery() )->get_results();
		$this->assertEquals( $amount, count( $query ), 'asset query count incorrect' );
	}
}
