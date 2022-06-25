<?php
/**
 * Definition debtor api class
 *
 * @since      1.0.0
 *
 * @package    WP-Accountancy
 * @subpackage WP-Accountacy/Includes
 */

namespace WP_Accountancy\Includes;

use WP_REST_Response;
use WP_REST_Request;

/**
 * Debtor API class.
 *
 * @noinspection PhpUnused
 */
class DebtorAPI extends API {

	/**
	 * List function
	 *
	 * @param  WP_REST_Request $request The request.
	 * @return WP_REST_Response
	 */
	public function list( WP_REST_Request $request ) : WP_REST_Response {
		$business_id = intval( $request->get_param( 'business_id' ) );
		$debtors     = ( new DebtorQuery( new Business( $business_id ) ) )->get_results();
		return new WP_REST_Response( array_walk( $debtors, 'get_object_vars' ) );
	}

	/**
	 * Get function
	 *
	 * @param WP_REST_Request $request The request.
	 * @return WP_REST_Response
	 */
	public function get( WP_REST_Request $request ) : WP_REST_Response {
		$business_id = intval( $request->get_param( 'business_id' ) );
		$debtor      = new Debtor( new Business( $business_id ), intval( $request->get_param( 'id' ) ) );
		if ( $debtor->id ) {
			return new WP_REST_Response( get_object_vars( $debtor ) );
		}
		return new WP_REST_Response( null, 404 );
	}

	/**
	 * Update function
	 *
	 * @param WP_REST_Request $request The request.
	 *
	 * @return WP_REST_Response
	 */
	public function update( WP_REST_Request $request ) : WP_REST_Response {
		$business_id = intval( $request->get_param( 'business_id' ) );
		$debtor      = new Debtor( new Business( $business_id ), intval( $request->get_param( 'id' ) ) );
		if ( $debtor->id ) {
			$update = $request->get_body_params();
			foreach ( $update as $key => $value ) {
				if ( property_exists( $debtor, $key ) && gettype( $debtor->$key ) === gettype( $value ) ) {
					$debtor->$key = $value;
				}
			}
			$debtor->update();
			return new WP_REST_Response( null, 204 );
		}
		return new WP_REST_Response( null, 404 );
	}

	/**
	 * Cancel function
	 *
	 * @param WP_REST_Request $request The request.
	 *
	 * @return WP_REST_Response
	 */
	public function cancel( WP_REST_Request $request ) : WP_REST_Response {
		$business_id = intval( $request->get_param( 'business_id' ) );
		$debtor      = new Debtor( new Business( $business_id ), intval( $request->get_param( 'id' ) ) );
		if ( $debtor->id ) {
			if ( $debtor->delete() ) {
				return new WP_REST_Response( null, 204 );
			}
			return new WP_REST_Response( null, 409 );
		}
		return new WP_REST_Response( null, 404 );
	}

	/**
	 * Create function
	 *
	 * @param WP_REST_Request $request The request.
	 *
	 * @return WP_REST_Response
	 */
	public function create( WP_REST_Request $request ) : WP_REST_Response {
		$business_id = intval( $request->get_param( 'business_id' ) );
		$debtor      = new Debtor( new Business( $business_id ) );
		foreach ( $request->get_body_params() as $key => $value ) {
			if ( property_exists( $debtor, $key ) && gettype( $debtor->$key ) === gettype( $value ) ) {
				$debtor->$key = $value;
			}
		}
		$actor_id = $debtor->update();
		if ( $actor_id ) {
			return new WP_REST_Response( [ 'id' => $actor_id ] );
		}
		return new WP_REST_Response( null, 400 );
	}

}
