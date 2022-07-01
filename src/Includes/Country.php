<?php
/**
 * Definition country class
 *
 * @since      1.0.0
 *
 * @package    WP-Accountancy
 * @subpackage WP-Accountacy/Includes
 */

namespace WP_Accountancy\Includes;

/**
 * Country class.
 *
 * @property string name
 * @property string language
 * @property string file
 */
class Country {

	/**
	 * Constructor
	 *
	 * @param string $name     The country.
	 * @param string $language The language.
	 */
	public function __construct( string $name, string $language ) {
		global $wpdb;
		$default = [
			'name'     => $name,
			'language' => $language,
			'file'     => '',
		];
		$result  = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * from {$wpdb->prefix}wpacc_country WHERE name = %s AND language = %s",
				$name,
				$language
			),
			ARRAY_A
		);
		if ( ! $result ) {
			$result = $default;
		}
		foreach ( $result as $key => $value ) {
			$this->$key = $value;
		}
	}

	/**
	 * Store the record
	 *
	 * @return void
	 */
	public function insert(): void {
		global $wpdb;
		$wpdb->query(
			$wpdb->prepare(
				"INSERT INTO {$wpdb->prefix}wpacc_country (name, language, file ) VALUES ( %s, %s, %s ) ON DUPLICATE KEY UPDATE file = %s",
				$this->name,
				$this->language,
				$this->file,
				$this->file
			)
		);
	}

}