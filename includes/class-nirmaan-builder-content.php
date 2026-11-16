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
		$types = array(
			'nirmaan_template' => array(
				'name'          => __( 'Builder Templates', 'nirmaan-builder' ),
				'singular_name' => __( 'Builder Template', 'nirmaan-builder' ),
			),
			'nirmaan_library'  => array(
				'name'          => __( 'Builder Library', 'nirmaan-builder' ),
				'singular_name' => __( 'Library Item', 'nirmaan-builder' ),
			),
		);

		foreach ( $types as $post_type => $labels ) {
			register_post_type(
				$post_type,
				array(
					'labels'              => $labels,
					'public'              => false,
					'publicly_queryable'  => false,
					'exclude_from_search' => true,
					'show_ui'             => false,
					'show_in_rest'        => false,
					'supports'            => array( 'title', 'revisions', 'custom-fields' ),
					'capability_type'     => 'post',
					'map_meta_cap'        => true,
				)
			);
		}
	}
}
