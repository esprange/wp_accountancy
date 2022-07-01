<?php
/**
 * Definition business query class
 *
 * @since      1.0.0
 *
 * @package    WP-Accountancy
 * @subpackage WP-Accountacy/Includes
 */

namespace WP_Accountancy\Includes;

/**
 * Business query class.
 */
class BusinessQuery {

	/**
	 * The query string
	 *
	 * @var string The query.
	 */
	protected string $query_where;

	/**
	 * The currently selected business
	 *
	 * @var int
	 */
	private int $selected;

	/**
	 * The constructor
	 *
	 * @param array $args The query arguments.
	 *
	 * @return void
	 */
	public function __construct( array $args = [] ) {
		global $wpdb;
		$defaults          = [
			'name'          => 0,
			'slug'          => '',
			'show_selected' => 0,
		];
		$query_vars        = wp_parse_args( $args, $defaults );
		$this->query_where = ' 1 = 1';
		if ( $query_vars['name'] ) {
			$this->query_where .= $wpdb->prepare( ' AND lower(name) = lower(%s)', $query_vars['name'] );
		}
		if ( $query_vars['slug'] ) {
			$this->query_where .= $wpdb->prepare( ' AND lower(slug) = lower(%s)', $query_vars['name'] );
		}
		$this->selected = intval( $query_vars['show_selected'] );
	}

	/**
	 * Get the raw account results.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_results() : array {
		global $wpdb;
		return $wpdb->get_results(
			"SELECT id AS business_id, name, country, logo, address, CONCAT( id, '|', id = $this->selected ) AS selected
				FROM {$wpdb->prefix}wpacc_business
                WHERE $this->query_where
                ORDER BY name",
			OBJECT_K
		);
	}

}
