<?php
/**
 * Class Field Test
 *
 * @package WP-Accountancy
 */

namespace WP_Accountancy\Tests;

use WP_Accountancy\Public\Field;

/**
 * Field test cases.
 */
class Test_Field extends UnitTestCase {

	/**
	 * Test an empty field.
	 *
	 * @return void
	 */
	public function test_render_static() : void {
		$field = new Field( $this->business );
		$html  = $field->render(
			[
				'name'   => 'test',
				'type'   => 'text',
				'static' => true,
				'label'  => 'Test',
				'value'  => 'testvalue',
			]
		);
		$this->assertValidHtml( $html, 'Invalid html static' );
		$this->assertStringContainsString( 'Test', $html, 'label missing' );
		$this->assertStringContainsString( 'testvalue', $html, 'value missing' );
	}

	/**
	 * Test input fields
	 *
	 * @return void
	 */
	public function test_input_fields() : void {
		$field = new Field( $this->business );
		$html  = '';
		foreach (
			[
				'float'    => 123.45,
				'currency' => 789.01,
				'number'   => 3,
				'text'     => 'test',
				'date'     => '2022-02-01',
				'email'    => 'test@test.tst',
				'hidden'   => 'test',
				'image'    => '',
			] as $type => $value ) {
			$html .= $field->render(
				[
					'name'  => 'test',
					'type'  => $type,
					'label' => "Test_$type",
					'value' => $value,
				]
			);
		}
		$this->assertValidHtml( $html, 'Invalid html input fields' );
	}

	/**
	 * Test a select field
	 *
	 * @return void
	 */
	public function test_select_field() : void {
		$field = new Field( $this->business );
		$html  = $field->render(
			[
				'name'  => 'test',
				'type'  => 'select',
				'label' => 'Test_select',
				'value' => 'option_2',
				'list'  => [
					'option_1' => (object) [ 'name' => 'test_1' ],
					'option_2' => (object) [ 'name' => 'test_2' ],
					'option_3' => (object) [ 'name' => 'test_3' ],
				],
			]
		);
		$this->assertValidHtml( $html, 'Invalid html select' );
	}

	/**
	 * Test a select optgroup field
	 *
	 * @return void
	 */
	public function test_select_optgroup_field() : void {
		$field = new Field( $this->business );
		$html  = $field->render(
			[
				'name'     => 'test',
				'type'     => 'select',
				'label'    => 'Test_select',
				'value'    => 'option_2',
				'list'     => [
					'group_1|option_1' => (object) [ 'name' => 'test_1' ],
					'group_1|option_2' => (object) [ 'name' => 'test_2' ],
					'group_2|option_3' => (object) [ 'name' => 'test_3' ],
				],
				'optgroup' => true,
			]
		);
		$this->assertValidHtml( $html, 'Invalid html select' );
	}

	/**
	 * Test a textarea field
	 *
	 * @return void
	 */
	public function test_textarea_field() : void {
		$field = new Field( $this->business );
		$html  = $field->render(
			[
				'name'  => 'test',
				'type'  => 'textarea',
				'label' => 'Test_textarea',
				'value' => "This ia a multiline text\nto be continued next line",
			]
		);
		$this->assertValidHtml( $html, 'Invalid html textarea' );
	}

}
