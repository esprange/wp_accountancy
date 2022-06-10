<?php
/**
 * Definition transaction api class
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
 * Transaction API class.
 *
 * @noinspection PhpUnused
 */
class TransactionAPI extends API {

	/**
	 * List function
	 *
	 * @param WP_REST_Request $request The request.
	 *
	 * @return WP_REST_Response
	 */
	public function list( WP_REST_Request $request ) : WP_REST_Response {
		$transactions = ( new TransactionQuery() )->get_results();
		return new WP_REST_Response( array_walk( $transactions, 'get_object_vars' ) );
	}

	/**
	 * Get function
	 *
	 * @param WP_REST_Request $request The request.
	 *
	 * @return WP_REST_Response
	 */
	public function get( WP_REST_Request $request ) : WP_REST_Response {
		$transaction = new Transaction( intval( $request->get_param( 'id' ) ) );
		if ( $transaction->id ) {
			$data           = get_object_vars( $transaction );
			$details        = ( new DetailQuery( [ 'transaction_id' => $transaction->id ] ) )->get_results();
			$data['detail'] = array_walk( $details, 'get_object_vars' );
			return new WP_REST_Response( $data );
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
		$transaction = new Transaction( intval( $request->get_param( 'id' ) ) );
		if ( $transaction->id ) {
			$update = $request->get_body_params();
			foreach ( $update as $key => $value ) {
				if ( property_exists( $transaction, $key ) && gettype( $transaction->$key ) === gettype( $value ) ) {
					$transaction->$key = $value;
				}
			}
			$transaction->update();
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
		$transaction = new Transaction(intval( $request->get_param( 'id' ) ) );
		if ( $transaction->id ) {
			if ( $transaction->delete() ) {
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
		$transaction = new Transaction();
		foreach ( $request->get_body_params() as $key => $value ) {
			if ( property_exists( $transaction, $key ) && gettype( $transaction->$key ) === gettype( $value ) ) {
				$transaction->$key = $value;
			}
		}
		$transaction_id = $transaction->update();
		if ( $transaction_id ) {
			return new WP_REST_Response( [ 'id' => $transaction_id ] );
		}
		return new WP_REST_Response( null, 400 );
	}

}
