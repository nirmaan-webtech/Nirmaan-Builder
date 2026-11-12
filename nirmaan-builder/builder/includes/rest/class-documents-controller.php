<?php
/**
 * Builder documents REST controller.
 *
 * @package NirmaanBuilder
 */
namespace NirmaanBuilder\Rest;

use NirmaanBuilder\Document_Validator;
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

		register_rest_route( 'nirmaan-builder/v1', '/documents/(?P<id>\\d+)', array(
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
		if ( ! Document_Validator::is_valid( $value ) ) {
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

	private function get_preview_url( $post ) {
		return 'publish' === $post->post_status ? get_permalink( $post ) : get_preview_post_link( $post );
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
				$data[] = array(
					'id' => $post->ID,
					'title' => get_the_title( $post ),
					'status' => $post->post_status,
					'template_type' => get_post_meta( $post->ID, '_nirmaan_template_type', true ),
				);
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
		update_post_meta( $post_id, '_nirmaan_template_type', 'none' );
		update_post_meta( $post_id, '_nirmaan_template_condition', array( 'type' => 'entire_site', 'value' => '' ) );
		update_post_meta( $post_id, '_nirmaan_template_priority', 10 );
		$post = get_post( $post_id );
		return new WP_REST_Response( array( 'success' => true, 'data' => array(
			'id' => $post_id,
			'title' => get_the_title( $post_id ),
			'document' => $document,
			'template_type' => 'none',
			'template_condition' => array( 'type' => 'entire_site', 'value' => '' ),
			'template_priority' => 10,
			'preview_url' => $this->get_preview_url( $post ),
		) ), 201 );
	}

	public function get_document( $request ) {
		$post = get_post( absint( $request['id'] ) );
		$document = get_post_meta( $post->ID, '_nirmaan_document_json', true );
		if ( ! Document_Validator::is_valid( $document ) ) {
			$document = array( 'schema_version' => 1, 'elements' => array() );
		}
		return new WP_REST_Response( array( 'success' => true, 'data' => array(
			'id' => $post->ID,
			'title' => get_the_title( $post ),
			'document' => $document,
			'modified' => $post->post_modified_gmt,
			'status' => $post->post_status,
			'template_type' => get_post_meta( $post->ID, '_nirmaan_template_type', true ),
			'template_condition' => get_post_meta( $post->ID, '_nirmaan_template_condition', true ),
			'template_priority' => absint( get_post_meta( $post->ID, '_nirmaan_template_priority', true ) ),
			'preview_url' => $this->get_preview_url( $post ),
		) ), 200 );
	}

	/**
	 * Validate condition data without mutating the document.
	 *
	 * @param mixed $condition Display condition.
	 * @return array|WP_Error
	 */
	private function validate_template_condition( $condition ) {
		$allowed = array( 'entire_site', 'front_page', 'singular', 'post_type', 'page_id', 'category_id', 'search', '404' );
		if ( ! is_array( $condition ) || array_diff( array_keys( $condition ), array( 'type', 'value' ) ) || empty( $condition['type'] ) || ! is_string( $condition['type'] ) || ! in_array( $condition['type'], $allowed, true ) ) {
			return new WP_Error( 'nirmaan_invalid_template_condition', __( 'Unsupported template display condition.', 'nirmaan-builder' ), array( 'status' => 422 ) );
		}

		$type  = $condition['type'];
		$value = isset( $condition['value'] ) ? $condition['value'] : '';
		if ( 'page_id' === $type ) {
			$value = absint( $value );
			$page  = $value ? get_post( $value ) : null;
			if ( ! $page || 'page' !== $page->post_type ) {
				return new WP_Error( 'nirmaan_invalid_condition_value', __( 'A valid page ID is required.', 'nirmaan-builder' ), array( 'status' => 422 ) );
			}
		} elseif ( 'category_id' === $type ) {
			$value = absint( $value );
			if ( ! $value || ! term_exists( $value, 'category' ) ) {
				return new WP_Error( 'nirmaan_invalid_condition_value', __( 'A valid category ID is required.', 'nirmaan-builder' ), array( 'status' => 422 ) );
			}
		} elseif ( 'post_type' === $type ) {
			$value = is_string( $value ) ? sanitize_key( $value ) : '';
			if ( '' === $value || ! post_type_exists( $value ) ) {
				return new WP_Error( 'nirmaan_invalid_post_type', __( 'The selected post type does not exist.', 'nirmaan-builder' ), array( 'status' => 422 ) );
			}
		} else {
			$value = '';
		}
		return array( 'type' => $type, 'value' => $value );
	}

	public function save_document( $request ) {
		$post     = get_post( absint( $request['id'] ) );
		$document = $request->get_param( 'document' );
		if ( ! Document_Validator::is_valid( $document ) ) {
			return new WP_Error( 'nirmaan_invalid_document', __( 'The document payload does not match the supported schema.', 'nirmaan-builder' ), array( 'status' => 422 ) );
		}
		$json = wp_json_encode( $document );
		if ( false === $json || strlen( $json ) > self::MAX_DOCUMENT_BYTES ) {
			return new WP_Error( 'nirmaan_document_too_large', __( 'The document exceeds the maximum allowed size.', 'nirmaan-builder' ), array( 'status' => 413 ) );
		}

		// Validate every optional field before the first database mutation.
		$title = $request->get_param( 'title' );
		if ( null !== $title && ( ! is_string( $title ) || strlen( $title ) > 200 ) ) {
			return new WP_Error( 'nirmaan_invalid_title', __( 'The document title is invalid.', 'nirmaan-builder' ), array( 'status' => 422 ) );
		}
		$status = $request->get_param( 'status' );
		if ( null !== $status && ! in_array( $status, array( 'draft', 'publish' ), true ) ) {
			return new WP_Error( 'nirmaan_invalid_status', __( 'Unsupported document status.', 'nirmaan-builder' ), array( 'status' => 422 ) );
		}
		if ( 'publish' === $status && ! current_user_can( 'publish_posts' ) ) {
			return new WP_Error( 'nirmaan_publish_forbidden', __( 'You are not allowed to publish layouts.', 'nirmaan-builder' ), array( 'status' => 403 ) );
		}

		$template_type = $request->get_param( 'template_type' );
		if ( null !== $template_type && ! in_array( $template_type, array( 'none', 'header', 'footer' ), true ) ) {
			return new WP_Error( 'nirmaan_invalid_template_type', __( 'Unsupported template type.', 'nirmaan-builder' ), array( 'status' => 422 ) );
		}
		$condition = $request->get_param( 'template_condition' );
		$validated_condition = null;
		if ( null !== $condition ) {
			$validated_condition = $this->validate_template_condition( $condition );
			if ( is_wp_error( $validated_condition ) ) {
				return $validated_condition;
			}
		}
		$priority = $request->get_param( 'template_priority' );
		if ( null !== $priority && ( ! is_numeric( $priority ) || (string) (int) $priority !== (string) $priority || (int) $priority < 0 || (int) $priority > 999 ) ) {
			return new WP_Error( 'nirmaan_invalid_template_priority', __( 'Template priority must be an integer from 0 to 999.', 'nirmaan-builder' ), array( 'status' => 422 ) );
		}

		$update = array( 'ID' => $post->ID );
		if ( is_string( $title ) && '' !== trim( $title ) ) {
			$update['post_title'] = sanitize_text_field( $title );
		}
		if ( null !== $status ) {
			$update['post_status'] = $status;
		}
		if ( count( $update ) > 1 ) {
			$updated = wp_update_post( $update, true );
			if ( is_wp_error( $updated ) ) {
				return $updated;
			}
		}

		update_post_meta( $post->ID, '_nirmaan_document_json', wp_slash( $document ) );
		if ( null !== $template_type ) {
			update_post_meta( $post->ID, '_nirmaan_template_type', $template_type );
		}
		if ( null !== $validated_condition ) {
			update_post_meta( $post->ID, '_nirmaan_template_condition', $validated_condition );
		}
		if ( null !== $priority ) {
			update_post_meta( $post->ID, '_nirmaan_template_priority', (int) $priority );
		}

		$updated_post = get_post( $post->ID );
		return new WP_REST_Response( array( 'success' => true, 'data' => array(
			'id' => $post->ID,
			'title' => get_the_title( $updated_post ),
			'modified' => get_post_field( 'post_modified_gmt', $post->ID ),
			'status' => $updated_post->post_status,
			'template_type' => get_post_meta( $post->ID, '_nirmaan_template_type', true ),
			'preview_url' => $this->get_preview_url( $updated_post ),
		) ), 200 );
	}
}
