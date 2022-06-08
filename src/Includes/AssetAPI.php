<?php
/**
 * Definition asset api class
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
 * Asset API class.
 *
 * @noinspection PhpUnused
 */
class AssetAPI extends API {

	/**
	 * List function
	 *
	 * @param WP_REST_Request $request The request.
	 *
	 * @return WP_REST_Response
	 */
	public function list( WP_REST_Request $request ) : WP_REST_Response {
		$assets = ( new AssetQuery() )->get_results();
		return new WP_REST_Response( array_walk( $assets, 'get_object_vars' ) );
	}

	/**
	 * Get function
	 *
	 * @param WP_REST_Request $request The request.
	 *
	 * @return WP_REST_Response
	 */
	public function get( WP_REST_Request $request ) : WP_REST_Response {
		$id    = intval( $request->get_param( 'id' ) );
		$asset = new Asset( $id );
		if ( $asset->id ) {
			return new WP_REST_Response( get_object_vars( $asset ) );
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
		$id    = intval( $request->get_param( 'id' ) );
		$asset = new Asset( $id );
		if ( $asset->id ) {
			$update = $request->get_body_params();
			foreach ( $update as $key => $value ) {
				if ( property_exists( $asset, $key ) && gettype( $asset->$key ) === gettype( $value ) ) {
					$asset->$key = $value;
				}
			}
			$asset->update();
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
		$id    = intval( $request->get_param( 'id' ) );
		$asset = new Asset( $id );
		if ( $asset->id ) {
			if ( $asset->delete() ) {
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
		$asset = new Asset();
		foreach ( $request->get_body_params() as $key => $value ) {
			if ( property_exists( $asset, $key ) && gettype( $asset->$key ) === gettype( $value ) ) {
				$asset->$key = $value;
			}
		}
		$id = $asset->update();
		if ( $id ) {
			return new WP_REST_Response( [ 'id' => $id ] );
		}
		return new WP_REST_Response( null, 400 );
	}

}
