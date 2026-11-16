<?php
/**
 * Registers builder-owned content types.
 *
 * @package NirmaanBuilder
 */

namespace NirmaanBuilder;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builder content registration.
 */
class Content {

	/**
	 * Register private templates and reusable library entries.
	 *
	 * @return void
	 */
	public static function register() {
		register_post_type(
			'nirmaan_template',
			array(
				'labels'       => array(
					'name'          => __( 'Builder Templates', 'nirmaan-builder' ),
					'singular_name' => __( 'Builder Template', 'nirmaan-builder' ),
				),
				'public'       => false,
				'show_ui'      => false,
				'show_in_rest' => false,
				'supports'     => array( 'title', 'revisions', 'custom-fields' ),
				'capability_type' => 'post',
				'map_meta_cap' => true,
			)
		);

		register_post_type(
			'nirmaan_library',
			array(
				'labels'       => array(
					'name'          => __( 'Builder Library', 'nirmaan-builder' ),
					'singular_name' => __( 'Library Item', 'nirmaan-builder' ),
				),
				'public'       => false,
				'show_ui'      => false,
				'show_in_rest' => false,
				'supports'     => array( 'title', 'revisions', 'custom-fields' ),
				'capability_type' => 'post',
				'map_meta_cap' => true,
			)
		);
	}
}
