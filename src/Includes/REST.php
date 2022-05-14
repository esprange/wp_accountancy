<?php
/**
 * Fired during plugin deactivation
 *
 * @since      1.0.0
 *
 * @package    WP-Accountancy
 * @subpackage WP-Accountacy/Includes
 */

namespace WP_Accountancy\Includes;

use WP_REST_Request;
/**
 * The REST API class.
 */
class REST {

	private const RESOURCES = [
		[
			'class' => CreditorApi::class,
			'route' => '/creditors',
		],
		[
			'class' => DebtorApi::class,
			'route' => '/debtors',
		],
		[
			'class' => TransactionApi::class,
			'route' => '/transactions',
		],
	];

	/**
	 * Register the routes for the resource
	 *
	 * @return void
	 */
	public function register() {
		$namespace = WP_ACCOUNTANCY_API . '/V1';
		$args      = [
			'id' => [
				'required'          => true,
				'validate_callback' => function( mixed $param ) {
					return 0 < intval( $param );
				},
			],
		];
		foreach ( self::RESOURCES as $resource ) {
			register_rest_route(
				$namespace,
				$resource['route'],
				[
					'methods'             => 'GET',
					'callback'            => [ $resource['class'], 'callback_list' ],
					'permission_callback' => function() {
						return current_user_can( '' );
					},
				]
			);

			register_rest_route(
				$namespace,
				$resource['route'] . '/(?P<id>\d+)',
				[
					'methods'             => 'GET',
					'callback'            => [ $resource['class'], 'callback_get' ],
					'args'                => $args,
					'permission_callback' => function() {
						return current_user_can( '' );
					},
				]
			);
			register_rest_route(
				$namespace,
				$resource['route'] . '/(?P<id>\d+)',
				[
					'methods'             => 'DELETE',
					'callback'            => [ $resource['class'], 'callback_cancel' ],
					'args'                => $args,
					'permission_callback' => function() {
						return current_user_can( '' );
					},
				]
			);
			register_rest_route(
				$namespace,
				$resource['route'] . '/(?P<id>\d+)',
				[
					'methods'             => 'PATCH',
					'callback'            => [ $resource['class'], 'callback_update' ],
					'args'                => $args,
					'permission_callback' => function() {
						return current_user_can( '' );
					},
				]
			);
			register_rest_route(
				$namespace,
				$resource['route'],
				[
					'methods'             => 'POST',
					'callback'            => [ $resource['class'], 'callback_create' ],
					'permission_callback' => function() {
						return current_user_can( '' );
					},
				]
			);
		}
	}
}
