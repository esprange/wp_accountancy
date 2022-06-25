<?php
/**
 * Definition abstract query class
 *
 * @since      1.0.0
 *
 * @package    WP-Accountancy
 * @subpackage WP-Accountacy/Includes
 */

namespace WP_Accountancy\Includes;

use WP_REST_Request;
use WP_REST_Response;

/**
 * Query class.
 */
abstract class API {

	/**
	 * List function
	 *
	 * @param  WP_REST_Request $request The request.
	 * @return WP_REST_Response
	 */
	abstract public function list( WP_REST_Request $request ) : WP_REST_Response;

	/**
	 * Get function
	 *
	 * @param WP_REST_Request $request The request.
	 *
	 * @return WP_REST_Response
	 */
	abstract public function get( WP_REST_Request $request ) : WP_REST_Response;

	/**
	 * Update function
	 *
	 * @param WP_REST_Request $request The request.
	 *
	 * @return WP_REST_Response
	 */
	abstract public function update( WP_REST_Request $request ) : WP_REST_Response;

	/**
	 * Cancel function
	 *
	 * @param WP_REST_Request $request The request.
	 *
	 * @return WP_REST_Response
	 */
	abstract public function cancel( WP_REST_Request $request ) : WP_REST_Response;

	/**
	 * Create function
	 *
	 * @param WP_REST_Request $request The request.
	 *
	 * @return WP_REST_Response
	 */
	abstract public function create( WP_REST_Request $request ) : WP_REST_Response;
}
