<?php
/**
 * Theme Builder template resolver.
 *
 * @package NirmaanBuilder
 */

namespace NirmaanBuilder;

defined( 'ABSPATH' ) || exit;

final class Template_Resolver {
	/**
	 * Find the highest-priority matching published template.
	 *
	 * @param string $type Template type.
	 * @return int
	 */
	public static function find( $type ) {
		$templates = get_posts( array(
			'post_type' => 'nirmaan_layout',
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'no_found_rows' => true,
			'update_post_meta_cache' => true,
			'update_post_term_cache' => false,
			'meta_key' => '_nirmaan_template_type',
			'meta_value' => sanitize_key( $type ),
			'orderby' => 'ID',
			'order' => 'ASC',
		) );

		$matches = array();
		foreach ( $templates as $template ) {
			$condition = get_post_meta( $template->ID, '_nirmaan_template_condition', true );
			if ( ! is_array( $condition ) ) {
				$condition = array( 'type' => 'entire_site', 'value' => '' );
			}
			$specificity = self::matches( $condition );
			if ( false === $specificity ) {
				continue;
			}
			$matches[] = array(
				'id' => $template->ID,
				'priority' => absint( get_post_meta( $template->ID, '_nirmaan_template_priority', true ) ),
				'specificity' => $specificity,
			);
		}

		if ( empty( $matches ) ) {
			return 0;
		}

		usort( $matches, static function ( $a, $b ) {
			if ( $a['priority'] !== $b['priority'] ) {
				return $b['priority'] <=> $a['priority'];
			}
			if ( $a['specificity'] !== $b['specificity'] ) {
				return $b['specificity'] <=> $a['specificity'];
			}
			return $a['id'] <=> $b['id'];
		} );

		return absint( $matches[0]['id'] );
	}

	/**
	 * Match a supported display condition against the current request.
	 *
	 * @param array $condition Condition data.
	 * @return int|false Specificity or false when it does not match.
	 */
	private static function matches( $condition ) {
		$type  = isset( $condition['type'] ) ? sanitize_key( $condition['type'] ) : 'entire_site';
		$value = isset( $condition['value'] ) ? $condition['value'] : '';

		switch ( $type ) {
			case 'entire_site':
				return 1;
			case 'front_page':
				return is_front_page() ? 10 : false;
			case 'singular':
				return is_singular() ? 5 : false;
			case 'post_type':
				$post_type = sanitize_key( (string) $value );
				return ( is_singular( $post_type ) || is_post_type_archive( $post_type ) ) ? 20 : false;
			case 'page_id':
				$page_id = absint( $value );
				return ( $page_id && is_page( $page_id ) ) ? 30 : false;
			case 'category_id':
				$category_id = absint( $value );
				return ( $category_id && is_category( $category_id ) ) ? 25 : false;
			case 'search':
				return is_search() ? 10 : false;
			case '404':
				return is_404() ? 10 : false;
		}

		return false;
	}
}
