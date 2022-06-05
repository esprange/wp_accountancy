<?php
/**
 * Class WP_accountancy_UnitTestCase
 *
 * @package WP-Accountancy
 */

namespace WP_Accountancy\Tests;

use PHPUnit\Framework\Constraint\Constraint;
use WP_UnitTestCase;
use WP_Accountancy\Includes\Accountancy;
use WP_Accountancy\Admin\Upgrade;

/**
 * Mock filter input array function
 *
 * @param int       $type      Type input Post of Get.
 * @param array|int $options   Filter opties.
 * @param bool      $add_empty Afwezige keys als null tonen.
 */
function filter_input_array( int $type, array|int $options = FILTER_DEFAULT, bool $add_empty = true ): bool|array|null {
	// @phpcs:disable
	if ( INPUT_GET === $type ) {
		return filter_var_array( $_GET, $options, $add_empty);
	}
	return filter_var_array( $_POST, $options, $add_empty );
	// @phpcs:enable
}

/**
 * Mock filter input function
 *
 * @param int       $type     Type input Post of Get.
 * @param string    $var_name Variable naam.
 * @param int       $filter   Filter.
 * @param array|int $options  Filter opties.
 *
 * @return mixed
 */
function filter_input( int $type, string $var_name, int $filter = FILTER_DEFAULT, array|int $options = 0 ): mixed {
	// @phpcs:disable
	if ( INPUT_GET === $type && isset( $_GET[ $var_name ] ) ) {
		return filter_var( $_GET[ $var_name ], $filter, $options );
	}
	if ( isset( $_POST[ $var_name ] ) ) {
		return filter_var( $_POST[ $var_name ], $filter, $options );
	}
	return null;
	// @phpcs:enable
}

/**
 * WP Accountancy Unit test case.
 *
 * phpcs:disable WordPress.NamingConventions
 */
abstract class UnitTestCase extends WP_UnitTestCase {

	/**
	 * Activate the plugin which includes the kleistad specific tables if not present.
	 */
	public function setUp(): void {
		parent::setUp();
		new Accountancy();
		$_GET  = [];
		$_POST = [];
	}

	/**
	 * Assert that the given HTML validates
	 *
	 * @param string $html The HTML to validate.
	 * @param string $message The error message to display.
	 */
	public static function assertValidHtml( string $html, string $message ) {
		$url    = add_query_arg(
			[
				'out'    => 'json',
				'parser' => 'html5',
			],
			'https://validator.w3.org/nu/?out=json&parser=html5'
		);
		$body   = "<!DOCTYPE html><html lang='en'><head><title>html test</title></head><body>$html</body></html>";
		$result = wp_remote_request(
			$url,
			[
				'headers' => [ 'Content-type' => 'text/html; charset=utf-8' ],
				'body'    => $body,
				'method'  => 'POST',
			]
		);
		if ( 'OK' === $result['response']['message'] ) {
			$body = json_decode( $result['body'] );
			self::assertThat(
				$body->messages,
				new Class( $html ) extends Constraint {

					/**
					 * The errors.
					 *
					 * @var string $analysis  The error description.
					 */
					private string $analysis;

					/**
					 * The html string to assert
					 *
					 * @var string The input html tekst.
					 */
					private string $html;

					/**
					 * Constructor
					 *
					 * @param string $html Validated html text.
					 */
					public function __construct( string $html ) {
						$this->html = $html;
					}

					/**
					 * Check if the constraint matches.
					 *
					 * @param mixed $other The validator output.
					 *
					 * @return bool
					 */
					protected function matches( mixed $other ): bool {
						return 0 === count( $other );
					}

					/**
					 * Get the failure description.
					 *
					 * @param mixed $other The validator output.
					 *
					 * @return string
					 */
					protected function failureDescription( mixed $other ): string {
						$this->analysis = "$this->html\n";
						foreach ( $other as $errors ) {
							$this->analysis .= "$errors->type $errors->message\n";
						}
						return $this->toString();
					}

					/**
					 * Echo the analysis.
					 *
					 * @return string
					 */
					public function toString(): string {
						return $this->analysis;
					}
				},
				$message
			);
		} else {
			self::markTestIncomplete( 'Issues checking HTML validity: ' . $result['response']['message'] );
		}
	}

}
