<?php
/**
 * Class Actor Test
 *
 * @package WP-Accountancy
 */

namespace WP_Accountancy\Tests;

use WP_Accountancy\Includes\Actor;
use WP_Accountancy\Includes\ActorQuery;

/**
 * Actor test cases.
 */
class Test_Actor extends UnitTestCase {

	/**
	 * Test creating an actor.
	 *
	 * @return void
	 */
	public function test_create() : void {
		$actor1       = new Actor( $this->business );
		$actor1->name = 'test-actor';
		$actor1->type = 'test';
		$actor_id     = $actor1->update();

		$actor2 = new Actor( $this->business, $actor_id );
		$this->assertEquals( get_object_vars( $actor1 ), get_object_vars( $actor2 ), 'actor store incorrect' );
	}

	/**
	 * Test an update
	 *
	 * @return void
	 */
	public function test_update() : void {
		$address = 'test address';

		$actor1       = new Actor( $this->business );
		$actor1->name = 'test-actor';
		$actor1->type = 'test';
		$actor_id     = $actor1->update();

		$actor2          = new Actor( $this->business, $actor_id );
		$actor2->address = $address;
		$actor2->update();

		$actor3 = new Actor( $this->business, $actor_id );
		$this->assertEquals( $address, $actor3->address, 'actor update incorrect' );
	}

	/**
	 * Test delete
	 *
	 * @return void
	 */
	public function test_delete() : void {
		$actor1       = new Actor( $this->business );
		$actor1->name = 'test-actor';
		$actor1->type = 'test';
		$actor_id     = $actor1->update();

		$actor2 = new Actor( $this->business, $actor_id );
		$actor2->delete();

		$actor3 = new Actor( $this->business, $actor_id );
		$this->assertEquals( 0, $actor3->id, 'actor delete incorrect' );
	}

	/**
	 * Test the query
	 *
	 * @return void
	 */
	public function test_query() : void {
		$amount = 5;
		for ( $index = 0; $index < $amount; $index++ ) {
			$actor       = new Actor( $this->business );
			$actor->name = "test-actor_$index";
			$actor->type = 'test';
			$actor->update();
		}
		$query = ( new ActorQuery( $this->business ) )->get_results();
		$this->assertEquals( $amount, count( $query ), 'actor query count incorrect' );
	}
}
