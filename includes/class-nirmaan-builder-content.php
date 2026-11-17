<?php
namespace NirmaanBuilder;
if ( ! defined( 'ABSPATH' ) ) { exit; }
class Content {
	public static function register() {
		$types = array(
			'nirmaan_template' => array( 'name' => __( 'Builder Templates', 'nirmaan-builder' ), 'singular_name' => __( 'Builder Template', 'nirmaan-builder' ) ),
			'nirmaan_library' => array( 'name' => __( 'Builder Library', 'nirmaan-builder' ), 'singular_name' => __( 'Library Layout', 'nirmaan-builder' ) ),
		);
		foreach ( $types as $type => $labels ) {
			register_post_type( $type, array(
				'labels' => $labels, 'public' => false, 'publicly_queryable' => false,
				'exclude_from_search' => true, 'show_ui' => false, 'show_in_rest' => false,
				'supports' => array( 'title', 'revisions' ), 'capability_type' => 'post', 'map_meta_cap' => true,
			) );
		}
		foreach ( get_post_types( array( 'public' => true ), 'names' ) as $post_type ) {
			register_post_meta( $post_type, '_nirmaan_builder_enabled', array(
				'type' => 'boolean',
				'single' => true,
				'show_in_rest' => true,
				'default' => false,
				'sanitize_callback' => 'rest_sanitize_boolean',
				'auth_callback' => static function ( $allowed, $meta_key, $post_id ) {
					return current_user_can( 'edit_post', $post_id );
				},
			) );
		}
	}
}
