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
	 * @suppressWarnings(PHPMD.ShortVariable)
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
		$row = iterator_to_array( $this->get_values() );
		if ( $this->id ) {
			$wpdb->update( $wpdb->prefix . $this->tablename(), $row, [ 'id' => $this->id ] );
			return $this->id;
		}
		$wpdb->insert( $wpdb->prefix . $this->tablename(), $row );
		$this->id = $wpdb->insert_id;
		return $this->id;
	}

	/**
	 * Fetch the record from the database
	 *
	 * @param array $default Default data.
	 * @param array $types   The field types, default = text.
	 *
	 * @return void
	 */
	final protected function fetch( array $default, array $types ) : void {
		global $wpdb;
		$this->default = $default;
		if ( $this->default['id'] ) {
			$tablename      = $this->tablename();
			$this->original = $wpdb->get_row(
				$wpdb->prepare(
					"# noinspection SqlResolve
					SELECT * FROM $wpdb->prefix$tablename WHERE id = %d",
					$this->default['id']
				),
				ARRAY_A
			);
			if ( ! $this->original ) {
				$this->default['id'] = 0;
			}
		}
		/**
		 * This object is derived from stdClass, the properties are created dynamically.
		 */
		foreach ( $this->original ?: $this->default as $property => $value ) {
			$this->$property = $value;
			if ( ! is_null( $value ) ) {
				settype( $this->$property, $types[ $property ] );
			}
		}
	}

	/**
	 * Create the array of changed values.
	 * Returns only the changed values as well as the id if already known.
	 *
	 * @return Generator
	 */
	private function get_values() : Generator {
		foreach ( array_keys( $this->default ) as $property ) {
			yield $property => $this->$property;
		}
	}

}
