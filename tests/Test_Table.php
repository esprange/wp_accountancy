<?php
/**
 * Class Table Test
 *
 * @package WP-Accountancy
 */

namespace WP_Accountancy\Tests;

use WP_Accountancy\Public\Table;

/**
 * Table test cases.
 */
class Test_Table extends UnitTestCase {

	/**
	 * Test an empty table.
	 */
	public function test_render_empty() {
		$table = new Table();
		$html  = $table->render(
			[
				'fields' => [
					[
						'name'  => 'id',
						'type'  => 'static',
						'label' => '',
					],
					[
						'name'  => 'test',
						'type'  => 'static',
						'label' => 'Test',
					],
				],
			]
		);
		$this->assertValidHtml( $html, 'Invalid html empty' );
		$this->assertEquals( 2, substr_count( $html, '<tr>' ), 'Number of rows incorrect' );
	}

	/**
	 * Test render of buttons function.
	 */
	public function test_buttons() {
		$table = new Table();
		$html  = $table->render(
			[
				'fields'  => [
					[
						'name'  => 'id',
						'type'  => 'static',
						'label' => '',
					],
					[
						'name'  => 'test',
						'type'  => 'static',
						'label' => 'Test',
					],
				],
				'options' => [
					'button_test_1' => 'Test 1',
					'button_test2'  => 'Test 2',
				],
			]
		);
		$this->assertValidHtml( $html, 'Invalid html empty' );
		$this->assertEquals( 2, substr_count( $html, '<button' ), 'Number of buttons incorrect' );
	}

	/**
	 * Test render functions.
	 */
	public function test_render_static() {
		$table = new Table();
		$html  = $table->render(
			[
				'fields' => [
					[
						'name'  => 'id',
						'type'  => 'static',
						'label' => '',
					],
					[
						'name'  => 'test',
						'type'  => 'static',
						'label' => 'Test',
					],
				],
				'items'  => [
					(object) [
						'id'   => 1,
						'test' => 'test1',
					],
					(object) [
						'id'   => 3,
						'test' => 'test3',
					],
				],
			],
		);
		$this->assertValidHtml( $html, 'Invalid html static' );
	}

	/**
	 * Test render with checkbox
	 *
	 * @return void
	 */
	public function test_render_checkbox(): void {
		$table = new Table();
		$html  = $table->render(
			[
				'fields' => [
					[
						'name'  => 'id',
						'type'  => 'radio',
						'label' => '',
					],
					[
						'name'  => 'test',
						'type'  => 'static',
						'label' => 'Test',
					],
				],
				'items'  => [
					1 => (object) [
						'id'   => 1,
						'test' => 'test1',
					],
					3 => (object) [
						'id'   => 3,
						'test' => 'test3',
					],
				],
			],
		);
		$this->assertEquals( 2, substr_count( $html, 'radio' ), 'Number of radio buttons incorrect' );
		$this->assertValidHtml( $html, 'Invalid html 2' );
	}

	/**
	 * Test render with zoom
	 *
	 * @return void
	 */
	public function test_render_zoom(): void {
		$table = new Table();
		$html  = $table->render(
			[
				'fields' => [
					[
						'name'  => 'id',
						'type'  => 'static',
						'label' => '',
					],
					[
						'name'  => 'name',
						'type'  => 'zoom',
						'label' => 'Test',
					],
				],
				'items'  => [
					1 => (object) [
						'id'   => 1,
						'name' => 'test1',
					],
					3 => (object) [
						'id'   => 3,
						'name' => 'test3',
					],
				],
			],
		);
		$this->assertEquals( 2, substr_count( $html, 'wpacc-zoom' ), 'Number of zooms incorrect' );
		$this->assertValidHtml( $html, 'Invalid html zoom' );
	}

	/**
	 * Test render with zoom
	 *
	 * @return void
	 */
	public function test_render_inputs(): void {
		$table = new Table();
		$html  = $table->render(
			[
				'fields' => [
					[
						'name'  => 'id',
						'type'  => 'static',
						'label' => '',
					],
					[
						'name'  => 'numbers',
						'type'  => 'number',
						'label' => 'Test1',
					],
					[
						'name'  => 'floats',
						'type'  => 'float',
						'label' => 'Test2',
					],
					[
						'name'  => 'currencies',
						'type'  => 'currency',
						'label' => 'Test3',
					],
					[
						'name'  => 'texts',
						'type'  => 'text',
						'label' => 'Test4',
					],
					[
						'name'  => 'hiddens',
						'type'  => 'hidden',
						'label' => 'Test5',
					],
					[
						'name'  => 'emails',
						'type'  => 'email',
						'label' => 'Test6',
					],
					[
						'name'  => 'dates',
						'type'  => 'date',
						'label' => 'Test7',
					],
					[
						'name'  => 'textareas',
						'type'  => 'textarea',
						'label' => 'Test8',
					],
					[
						'name'  => 'selects',
						'type'  => 'select',
						'label' => 'Test9',
						'list'  => [
							12 => (object) [ 'name' => 'first' ],
							23 => (object) [ 'name' => 'second' ],
						],
					],
				],
				'items'  => [
					1 => (object) [
						'id'         => 1,
						'numbers'    => 123,
						'floats'     => 123.45,
						'currencies' => '99.99',
						'texts'      => 'abcdef',
						'hiddens'    => 'xyz',
						'emails'     => 'test@example.com',
						'dates'      => date( 'Y-m-d' ),
						'textareas'  => "this is a multiline\ntext",
						'selects'    => 34,
					],
				],
			],
		);
		$this->assertValidHtml( $html, 'Invalid html inputs' );
	}

	/**
	 * Test Table addrow option.
	 *
	 * @return void
	 */
	public function test_addrow() : void {
		$table = new Table();
		$html  = $table->render(
			[
				'fields'  => [
					[
						'name'  => 'id',
						'type'  => 'static',
						'label' => '',
					],
					[
						'name'  => 'texts',
						'type'  => 'text',
						'label' => 'Test',
					],
				],
				'items'   => [
					1 => (object) [
						'id'    => 1,
						'texts' => 'abcdef',
					],
				],
				'options' => [ 'addrow' ],
			],
		);
		$this->assertValidHtml( $html, 'Invalid html addrow' );
		$this->assertEquals( 1, substr_count( $html, '<button' ), 'Add row fails' );
	}
}
