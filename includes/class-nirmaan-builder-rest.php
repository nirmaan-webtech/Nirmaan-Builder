<?php
/**
 * REST API namespace and health endpoint.
 *
 * @package NirmaanBuilder
 */

namespace NirmaanBuilder;

use WP_REST_Request;
use WP_REST_Server;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST routes.
 */
class Rest {

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register_hooks() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Register minimal diagnostic route.
	 *
	 * @return void
	 */
	public function register_routes() {
		register_rest_route(
			'nirmaan-builder/v1',
			'/status',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'status' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	/**
	 * Return non-sensitive plugin status.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function status( WP_REST_Request $request ) {
		unset( $request );

		return rest_ensure_response(
			array(
				'name'    => 'Nirmaan Builder',
				'version' => NIRMAAN_BUILDER_VERSION,
				'status'  => 'foundation',
			)
		);
	}
}
