<?php
/**
 * Definition abstract entity class
 *
 * @since      1.0.0
 *
 * @package    WP-Accountancy
 * @subpackage WP-Accountacy/Includes
 */

namespace WP_Accountancy\Includes;

use Generator;
use stdClass;

/**
 * Entity class.
 */
abstract class Entity extends stdClass {

	/**
	 * The record id.
	 *
	 * @var int $id The record id.
	 */
	public int $id = 0;

	/**
	 * The original entity data.
	 *
	 * @var array|null $original
	 */
	private ?array $original = null;

	/**
	 * The default data.
	 *
	 * @var array $default
	 */
	protected array $default = [];

	/**
	 * Remove the record from the table.
	 *
	 * @return bool
	 */
	final public function delete() : bool {
		global $wpdb;
		return (bool) $wpdb->delete( $this->tablename(), [ 'id' => $this->id ] );
	}

	/**
	 * Store the record into the database
	 *
	 * @return int
	 */
	final public function update() : int {
		global $wpdb;
		$wpdb->replace(
			$this->tablename(),
			$this->original ? iterator_to_array( $this->compare() ) : $this->data
		);
		$this->id = $wpdb->insert_id;
		return $this->id;
	}

	/**
	 * Fetch the record from the database
	 *
	 * @param array $default Default data.
	 *
	 * @return void
	 */
	final protected function fetch( array $default ) : void {
		global $wpdb;
		$this->default = $default;
		if ( $this->default['id'] ) {
			$this->original = $wpdb->get_row(
				'SELECT * FROM ' . $this->tablename() . ' WHERE id = ' . $this->default['id'], // phpcs:ignore
				ARRAY_A
			);
		}
		/**
		 * This object is derived from stdClass, the properties are created dynamically. Typecasting is done using the default as template.
		 */
		foreach ( $this->original ?: $this->default as $property => $value ) {
			$this->$property = $value;
			settype( $this->$property, gettype( $this->default[ $property ] ) );
		}
	}

	/**
	 * Create the array of changed values.
	 * Returns only the changed values as well as the id if already known.
	 *
	 * @return Generator
	 */
	private function compare(): Generator {
		foreach ( array_keys( $this->default ) as $property ) {
			if ( ! $this->original ||
				$this->original[ $property ] !== $this->$property ||
				( 'id' === $property && $this->$property )
			) {
				yield $property => $this->$property;
			}
		}
	}

	/**
	 * Deduct the table name from the class.
	 *
	 * @return string
	 */
	private function tablename() : string {
		global $wpdb;
		$classname = get_class( $this );
		return "{$wpdb->prefix}wpacc_" . strtolower( substr( $classname, strrpos( $classname, '\\' ) + 1 ) );
	}
}
