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
	}
}
