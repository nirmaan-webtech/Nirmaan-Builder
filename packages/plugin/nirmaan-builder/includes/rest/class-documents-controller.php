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

final class Documents_Controller {
	private const MAX_DOCUMENT_BYTES = 1048576;

	public function register_routes() {
		register_rest_route( 'nirmaan-builder/v1', '/documents', array(
			array(
				'methods' => WP_REST_Server::READABLE,
				'callback' => array( $this, 'list_documents' ),
				'permission_callback' => array( $this, 'can_create_document' ),
			),
			array(
				'methods' => WP_REST_Server::CREATABLE,
				'callback' => array( $this, 'create_document' ),
				'permission_callback' => array( $this, 'can_create_document' ),
			),
		) );

		register_rest_route( 'nirmaan-builder/v1', '/documents/(?P<id>\d+)', array(
			array(
				'methods' => WP_REST_Server::READABLE,
				'callback' => array( $this, 'get_document' ),
				'permission_callback' => array( $this, 'can_edit_document' ),
				'args' => array( 'id' => array( 'required' => true, 'validate_callback' => array( $this, 'validate_document_id' ) ) ),
			),
			array(
				'methods' => WP_REST_Server::EDITABLE,
				'callback' => array( $this, 'save_document' ),
				'permission_callback' => array( $this, 'can_edit_document' ),
				'args' => array(
					'id' => array( 'required' => true, 'validate_callback' => array( $this, 'validate_document_id' ) ),
					'document' => array( 'required' => true, 'validate_callback' => array( $this, 'validate_document_payload' ) ),
				),
			),
		) );
	}

	public function can_create_document() {
		return current_user_can( 'edit_posts' );
	}

	public function validate_document_id( $value, $request, $param ) {
		return is_scalar( $value ) && absint( $value ) > 0 && (string) absint( $value ) === (string) $value;
	}

	public function validate_document_payload( $value, $request, $param ) {
		if ( ! is_array( $value ) || ! isset( $value['schema_version'], $value['elements'] ) || ! is_array( $value['elements'] ) ) {
			return false;
		}
		$json = wp_json_encode( $value );
		return false !== $json && strlen( $json ) <= self::MAX_DOCUMENT_BYTES;
	}

	public function can_edit_document( $request ) {
		$post = get_post( absint( $request['id'] ) );
		if ( ! $post || 'nirmaan_layout' !== $post->post_type ) {
			return new WP_Error( 'nirmaan_document_not_found', __( 'Builder document not found.', 'nirmaan-builder' ), array( 'status' => 404 ) );
		}
		if ( ! current_user_can( 'edit_post', $post->ID ) ) {
			return new WP_Error( 'nirmaan_forbidden', __( 'You are not allowed to access this document.', 'nirmaan-builder' ), array( 'status' => 403 ) );
		}
		return true;
	}

	public function list_documents() {
		$posts = get_posts( array(
			'post_type' => 'nirmaan_layout',
			'post_status' => array( 'draft', 'publish', 'private' ),
			'author' => get_current_user_id(),
			'posts_per_page' => 100,
			'orderby' => 'modified',
			'order' => 'DESC',
		) );
		$data = array();
		foreach ( $posts as $post ) {
			if ( current_user_can( 'edit_post', $post->ID ) ) {
				$data[] = array( 'id' => $post->ID, 'title' => get_the_title( $post ) );
			}
		}
		return new WP_REST_Response( array( 'success' => true, 'data' => $data ), 200 );
	}

	public function create_document( $request ) {
		$title = sanitize_text_field( (string) $request->get_param( 'title' ) );
		if ( '' === $title ) {
			$title = __( 'Untitled Layout', 'nirmaan-builder' );
		}
		$post_id = wp_insert_post( array(
			'post_type' => 'nirmaan_layout',
			'post_status' => 'draft',
			'post_title' => $title,
			'post_author' => get_current_user_id(),
		), true );
		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}
		$document = array( 'schema_version' => 1, 'elements' => array() );
		update_post_meta( $post_id, '_nirmaan_document_json', wp_slash( $document ) );
		return new WP_REST_Response( array( 'success' => true, 'data' => array(
			'id' => $post_id,
			'title' => get_the_title( $post_id ),
			'document' => $document,
		) ), 201 );
	}

	public function get_document( $request ) {
		$post = get_post( absint( $request['id'] ) );
		$document = get_post_meta( $post->ID, '_nirmaan_document_json', true );
		if ( ! is_array( $document ) ) {
			$document = array( 'schema_version' => 1, 'elements' => array() );
		}
		return new WP_REST_Response( array( 'success' => true, 'data' => array(
			'id' => $post->ID,
			'title' => get_the_title( $post ),
			'document' => $document,
			'modified' => $post->post_modified_gmt,
		) ), 200 );
	}

	public function save_document( $request ) {
		$post = get_post( absint( $request['id'] ) );
		$document = $request->get_param( 'document' );
		$title = $request->get_param( 'title' );
		if ( is_string( $title ) && '' !== trim( $title ) ) {
			$updated = wp_update_post( array(
				'ID' => $post->ID,
				'post_title' => sanitize_text_field( $title ),
			), true );
			if ( is_wp_error( $updated ) ) {
				return $updated;
			}
		}
		update_post_meta( $post->ID, '_nirmaan_document_json', wp_slash( $document ) );
		return new WP_REST_Response( array( 'success' => true, 'data' => array(
			'id' => $post->ID,
			'title' => get_the_title( $post ),
			'modified' => get_post_field( 'post_modified_gmt', $post->ID ),
		) ), 200 );
	}
}
