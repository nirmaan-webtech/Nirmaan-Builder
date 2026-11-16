<?php
namespace NirmaanBuilder;
use WP_REST_Server;
if ( ! defined( 'ABSPATH' ) ) { exit; }
class Rest {
	public function register_hooks() { add_action( 'rest_api_init', array( $this, 'register_routes' ) ); }
	public function register_routes() {
		register_rest_route( 'nirmaan-builder/v1', '/status', array( 'methods' => WP_REST_Server::READABLE, 'callback' => array( $this, 'status' ), 'permission_callback' => '__return_true' ) );
		register_rest_route( 'nirmaan-builder/v1', '/content/(?P<id>\d+)', array(
			array( 'methods' => WP_REST_Server::READABLE, 'callback' => array( $this, 'get_content' ), 'permission_callback' => array( $this, 'can_edit_content' ) ),
			array( 'methods' => WP_REST_Server::CREATABLE, 'callback' => array( $this, 'save_content' ), 'permission_callback' => array( $this, 'can_edit_content' ) ),
		) );
		register_rest_route( 'nirmaan-builder/v1', '/templates', array(
			array( 'methods' => WP_REST_Server::READABLE, 'callback' => array( $this, 'list_templates' ), 'permission_callback' => array( $this, 'can_manage_templates' ) ),
			array( 'methods' => WP_REST_Server::CREATABLE, 'callback' => array( $this, 'create_template' ), 'permission_callback' => array( $this, 'can_manage_templates' ) ),
		) );
		register_rest_route( 'nirmaan-builder/v1', '/templates/(?P<id>\d+)', array(
			array( 'methods' => WP_REST_Server::READABLE, 'callback' => array( $this, 'get_template' ), 'permission_callback' => array( $this, 'can_manage_templates' ) ),
			array( 'methods' => 'POST', 'callback' => array( $this, 'save_template' ), 'permission_callback' => array( $this, 'can_manage_templates' ) ),
			array( 'methods' => WP_REST_Server::DELETABLE, 'callback' => array( $this, 'delete_template' ), 'permission_callback' => array( $this, 'can_manage_templates' ) ),
		) );
	}
	public function status() { return rest_ensure_response( array( 'name' => 'Nirmaan Builder', 'version' => NIRMAAN_BUILDER_VERSION, 'status' => 'visual-builder-mvp' ) ); }
	public function can_edit_content( $request ) { $id = absint( $request['id'] ); return $id && current_user_can( 'edit_post', $id ); }
	public function can_manage_templates() { return current_user_can( 'edit_theme_options' ); }
	public function get_content( $request ) {
		$id = absint( $request['id'] );
		return rest_ensure_response( array( 'id' => $id, 'document' => get_post_meta( $id, '_nirmaan_builder_document', true ) ?: array( 'nodes' => array() ) ) );
	}
	public function save_content( $request ) {
		$id = absint( $request['id'] ); $document = $this->sanitize_document( $request->get_json_params() );
		update_post_meta( $id, '_nirmaan_builder_document', $document );
		return rest_ensure_response( array( 'saved' => true, 'id' => $id, 'document' => $document ) );
	}
	public function list_templates() {
		$items = get_posts( array( 'post_type' => 'nirmaan_template', 'post_status' => array( 'publish', 'draft' ), 'numberposts' => 100 ) ); $data = array();
		foreach ( $items as $item ) { $data[] = array( 'id' => $item->ID, 'title' => $item->post_title, 'status' => $item->post_status, 'conditions' => get_post_meta( $item->ID, '_nirmaan_conditions', true ) ?: array(), 'parts' => get_post_meta( $item->ID, '_nirmaan_template_parts', true ) ?: array() ); }
		return rest_ensure_response( $data );
	}
	public function create_template( $request ) {
		$p = $request->get_json_params(); $title = isset( $p['title'] ) ? sanitize_text_field( $p['title'] ) : __( 'New Template', 'nirmaan-builder' );
		$id = wp_insert_post( array( 'post_type' => 'nirmaan_template', 'post_status' => 'publish', 'post_title' => $title ), true );
		if ( is_wp_error( $id ) ) { return $id; }
		update_post_meta( $id, '_nirmaan_conditions', array( 'include' => array( array( 'type' => 'entire_site' ) ), 'exclude' => array() ) );
		update_post_meta( $id, '_nirmaan_template_parts', array( 'header' => array( 'nodes' => array() ), 'body' => array( 'nodes' => array() ), 'footer' => array( 'nodes' => array() ) ) );
		return rest_ensure_response( array( 'id' => $id, 'title' => $title ) );
	}
	public function get_template( $request ) {
		$id = absint( $request['id'] );
		return rest_ensure_response( array( 'id' => $id, 'title' => get_the_title( $id ), 'conditions' => get_post_meta( $id, '_nirmaan_conditions', true ) ?: array(), 'parts' => get_post_meta( $id, '_nirmaan_template_parts', true ) ?: array() ) );
	}
	public function save_template( $request ) {
		$id = absint( $request['id'] ); $p = $request->get_json_params();
		if ( isset( $p['title'] ) ) { wp_update_post( array( 'ID' => $id, 'post_title' => sanitize_text_field( $p['title'] ) ) ); }
		if ( isset( $p['conditions'] ) && is_array( $p['conditions'] ) ) { update_post_meta( $id, '_nirmaan_conditions', $this->sanitize_conditions( $p['conditions'] ) ); }
		if ( isset( $p['part'] ) && in_array( $p['part'], array( 'header', 'body', 'footer' ), true ) && isset( $p['document'] ) ) {
			$parts = get_post_meta( $id, '_nirmaan_template_parts', true ); $parts = is_array( $parts ) ? $parts : array();
			$parts[ $p['part'] ] = $this->sanitize_document( $p['document'] ); update_post_meta( $id, '_nirmaan_template_parts', $parts );
		}
		return rest_ensure_response( array( 'saved' => true, 'id' => $id ) );
	}
	public function delete_template( $request ) { wp_delete_post( absint( $request['id'] ), true ); return rest_ensure_response( array( 'deleted' => true ) ); }
	private function sanitize_conditions( $conditions ) {
		$out = array( 'include' => array(), 'exclude' => array() );
		foreach ( array( 'include', 'exclude' ) as $group ) {
			foreach ( array_slice( $conditions[ $group ] ?? array(), 0, 30 ) as $rule ) {
				if ( ! is_array( $rule ) || empty( $rule['type'] ) ) { continue; }
				$type = sanitize_key( $rule['type'] );
				if ( ! in_array( $type, array( 'entire_site', 'front_page', 'singular', 'all_pages', 'all_posts', 'post_type', 'archive', 'search', '404' ), true ) ) { continue; }
				$out[ $group ][] = array( 'type' => $type, 'value' => absint( $rule['value'] ?? 0 ), 'post_type' => sanitize_key( $rule['post_type'] ?? '' ) );
			}
		}
		return $out;
	}
	private function sanitize_document( $data ) {
		$nodes = is_array( $data ) && isset( $data['nodes'] ) && is_array( $data['nodes'] ) ? $data['nodes'] : array();
		return array( 'nodes' => $this->sanitize_nodes( $nodes, 0 ) );
	}
	private function sanitize_nodes( $nodes, $depth ) {
		if ( $depth > 8 ) { return array(); } $clean = array();
		$allowed = array( 'heading', 'text', 'image', 'button', 'divider', 'spacer', 'container', 'columns' );
		foreach ( array_slice( $nodes, 0, 100 ) as $node ) {
			if ( ! is_array( $node ) || empty( $node['type'] ) ) { continue; }
			$type = sanitize_key( $node['type'] ); if ( ! in_array( $type, $allowed, true ) ) { continue; }
			$s = is_array( $node['settings'] ?? null ) ? $node['settings'] : array();
			$item = array( 'id' => sanitize_key( $node['id'] ?? wp_generate_uuid4() ), 'type' => $type, 'settings' => array(
				'content' => sanitize_textarea_field( $s['content'] ?? '' ), 'url' => esc_url_raw( $s['url'] ?? '' ),
				'alt' => sanitize_text_field( $s['alt'] ?? '' ), 'level' => min( 6, max( 1, absint( $s['level'] ?? 2 ) ) ),
				'height' => min( 400, absint( $s['height'] ?? 32 ) ),
			) );
			if ( in_array( $type, array( 'container', 'columns' ), true ) ) { $item['children'] = $this->sanitize_nodes( $node['children'] ?? array(), $depth + 1 ); }
			$clean[] = $item;
		}
		return $clean;
	}
}
