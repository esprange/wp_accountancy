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
	 * De query string
	 *
	 * @var string De query.
	 */
	protected string $query_where;

	/**
	 * The constructor
	 *
	 * @param array $args        The query arguments.
	 *
	 * @return void
	 */
	public function __construct( array $args = [] ) {
		global $wpdb;
		$defaults          = [
			'name' => 0,
			'slug' => '',
		];
		$query_vars        = wp_parse_args( $args, $defaults );
		$this->query_where = ' 1 = 1';
		if ( $query_vars['name'] ) {
			$this->query_where .= $wpdb->prepare( ' AND lower(name) = lower(%s)', $query_vars['name'] );
		}
		if ( $query_vars['slug'] ) {
			$this->query_where .= $wpdb->prepare( ' AND lower(slug) = lower(%s)', $query_vars['name'] );
		}
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
			"SELECT id AS business_id, name, country, logo, address, slug AS selected
				FROM {$wpdb->prefix}wpacc_business
                WHERE $this->query_where
                ORDER BY name",
			OBJECT_K
		);
	}

}
