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
	 * Return the table name string, excluding the prefix.
	 *
	 * @return string
	 */
	abstract public function tablename() : string;

	/**
	 * Remove the record from the table.
	 *
	 * @return bool
	 */
	final public function delete() : bool {
		global $wpdb;
		return (bool) $wpdb->delete( $wpdb->prefix . $this->tablename(), [ 'id' => $this->id ] );
	}

	/**
	 * Store the record into the database
	 *
	 * @return int
	 */
	final public function update() : int {
		global $wpdb;
		$wpdb->replace(
			$wpdb->prefix . $this->tablename(),
			iterator_to_array( $this->get_changes() )
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
				"SELECT * FROM $wpdb->prefix" . $this->tablename() . " WHERE id = {$this->default['id']}", // phpcs:ignore
				ARRAY_A
			);
			if ( ! $this->original ) {
				$this->default['id'] = 0;
			}
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
	private function get_changes(): Generator {
		foreach ( array_keys( $this->default ) as $property ) {
			if ( $this->original ) {
				if ( 'id' === $property || $this->original[ $property ] !== $this->$property ) {
					yield $property => $this->$property;
				}
				continue;
			}
			yield $property => $this->$property;
		}
	}

}
