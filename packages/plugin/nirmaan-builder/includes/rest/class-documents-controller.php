<?php
/**
 * Builder documents REST controller.
 *
 * @package NirmaanBuilder
 */

namespace NirmaanBuilder\Rest;

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

defined( 'ABSPATH' ) || exit;

/**
 * Documents API.
 */
final class Documents_Controller {
	/**
	 * Maximum canonical document size, in bytes.
	 *
	 * @var int
	 */
	private const MAX_DOCUMENT_BYTES = 1048576;

	/**
	 * Register routes.
	 *
	 * @return void
	 */
	public function register_routes() {
		register_rest_route(
			'nirmaan-builder/v1',
			'/documents/(?P<id>\d+)',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_document' ),
					'permission_callback' => array( $this, 'can_edit_document' ),
					'args'                => array(
						'id' => array(
							'required'          => true,
							'validate_callback' => array( $this, 'validate_document_id' ),
						),
					),
				),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'save_document' ),
					'permission_callback' => array( $this, 'can_edit_document' ),
					'args'                => array(
						'id'       => array(
							'required'          => true,
							'validate_callback' => array( $this, 'validate_document_id' ),
						),
						'document' => array(
							'required'          => true,
							'validate_callback' => array( $this, 'validate_document_payload' ),
						),
					),
				),
			)
		);
	}

	/**
	 * Validate a positive document ID.
	 *
	 * @param mixed           $value   Candidate ID.
	 * @param WP_REST_Request $request Request.
	 * @param string          $param  Parameter name.
	 * @return bool
	 */
	public function validate_document_id( $value, $request, $param ) {
		return is_scalar( $value ) && absint( $value ) > 0 && (string) absint( $value ) === (string) $value;
	}

	/**
	 * Validate a bounded document JSON structure.
	 *
	 * @param mixed           $value   Candidate document.
	 * @param WP_REST_Request $request Request.
	 * @param string          $param  Parameter name.
	 * @return bool
	 */
	public function validate_document_payload( $value, $request, $param ) {
		if ( ! is_array( $value ) || ! isset( $value['schema_version'], $value['elements'] ) || ! is_array( $value['elements'] ) ) {
			return false;
		}

		$json = wp_json_encode( $value );

		return false !== $json && strlen( $json ) <= self::MAX_DOCUMENT_BYTES;
	}

	/**
	 * Check current user can edit a document.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return bool|WP_Error
	 */
	public function can_edit_document( $request ) {
		$post = get_post( absint( $request['id'] ) );

		if ( ! $post || 'nirmaan_layout' !== $post->post_type ) {
			return new WP_Error(
				'nirmaan_document_not_found',
				__( 'Builder document not found.', 'nirmaan-builder' ),
				array( 'status' => 404 )
			);
		}

		if ( ! current_user_can( 'edit_post', $post->ID ) ) {
			return new WP_Error(
				'nirmaan_forbidden',
				__( 'You are not allowed to access this document.', 'nirmaan-builder' ),
				array( 'status' => 403 )
			);
		}

		return true;
	}

	/**
	 * Retrieve document JSON.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public function get_document( $request ) {
		$post     = get_post( absint( $request['id'] ) );
		$document = get_post_meta( $post->ID, '_nirmaan_document_json', true );

		if ( ! is_array( $document ) ) {
			$document = array(
				'schema_version' => 1,
				'elements'       => array(),
			);
		}

		return new WP_REST_Response(
			array(
				'success' => true,
				'data'    => array(
					'id'       => $post->ID,
					'document' => $document,
					'modified' => $post->post_modified_gmt,
				),
			),
			200
		);
	}

	/**
	 * Validate and save document JSON.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public function save_document( $request ) {
		$post     = get_post( absint( $request['id'] ) );
		$document = $request->get_param( 'document' );

		/*
		 * JSON is canonical Builder data, not executable PHP or HTML.
		 * Individual renderers must validate and escape values at output time.
		 */
		update_post_meta( $post->ID, '_nirmaan_document_json', wp_slash( $document ) );

		return new WP_REST_Response(
			array(
				'success' => true,
				'data'    => array(
					'id'       => $post->ID,
					'modified' => get_post_field( 'post_modified_gmt', $post->ID ),
				),
			),
			200
		);
	}
}
