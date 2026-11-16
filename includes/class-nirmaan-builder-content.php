<?php
namespace NirmaanBuilder;
if ( ! defined( 'ABSPATH' ) ) { exit; }
class Content {
	public static function register() {
		register_post_type( 'nirmaan_template', array(
			'labels' => array( 'name' => __( 'Builder Templates', 'nirmaan-builder' ), 'singular_name' => __( 'Builder Template', 'nirmaan-builder' ) ),
			'public' => false, 'publicly_queryable' => false, 'exclude_from_search' => true,
			'show_ui' => false, 'show_in_rest' => false, 'supports' => array( 'title', 'revisions' ),
			'capability_type' => 'post', 'map_meta_cap' => true,
		) );
	}
}
