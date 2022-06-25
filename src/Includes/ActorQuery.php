<?php
/**
 * Definition actor query class
 *
 * @since      1.0.0
 *
 * @package    WP-Accountancy
 * @subpackage WP-Accountacy/Includes
 */

namespace WP_Accountancy\Includes;

/**
 * Debtor query class.
 */
class ActorQuery {

	/**
	 * De query string
	 *
	 * @var string De query.
	 */
	protected string $query_where;

	/**
	 * The constructor
	 *
	 * @param Business $business The business.
	 * @param array    $args     The query arguments.
	 *
	 * @return void
	 */
	public function __construct( Business $business, array $args = [] ) {
		global $wpdb;
		$defaults          = [
			'name'   => '',
			'active' => 0,
			'type'   => '',
		];
		$query_vars        = wp_parse_args( $args, $defaults );
		$this->query_where = $wpdb->prepare( ' business_id = %d', $business->id );
		if ( $query_vars['active'] ) {
			$this->query_where .= ' AND active';
		}
		if ( $query_vars['name'] ) {
			$this->query_where .= $wpdb->prepare( ' AND name = %s', $query_vars['name'] );
		}
		if ( $query_vars['type'] ) {
			$this->query_where .= $wpdb->prepare( ' AND type = %s', $query_vars['type'] );
		}
	}

	/**
	 * Get the debtor results.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_results() : array {
		global $wpdb;
		return $wpdb->get_results(
			"SELECT id AS actor_id, name, type, business_id, address, billing_address, email_address, active
			FROM {$wpdb->prefix}wpacc_actor
			WHERE $this->query_where
			ORDER BY name",
			OBJECT_K
		);
	}

}
