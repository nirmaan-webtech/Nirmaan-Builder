<?php
/**
 * Server-side Builder document renderer.
 *
 * @package NirmaanBuilder
 */

namespace NirmaanBuilder;

defined( 'ABSPATH' ) || exit;

final class Renderer {
	/**
	 * Render a saved layout.
	 *
	 * @param int $post_id Layout ID.
	 * @return string
	 */
	public static function render( $post_id ) {
		$document = get_post_meta( absint( $post_id ), '_nirmaan_document_json', true );
		if ( ! is_array( $document ) || empty( $document['elements'] ) || ! is_array( $document['elements'] ) ) {
			return '';
		}

		$output = '<div class="nirmaan-builder-content">';
		foreach ( $document['elements'] as $element ) {
			if ( ! is_array( $element ) || empty( $element['type'] ) || ! isset( $element['settings'] ) || ! is_array( $element['settings'] ) ) {
				continue;
			}
			$output .= self::render_element( $element );
		}
		$output .= '</div>';

		return $output;
	}

	/**
	 * Render one allowlisted module.
	 *
	 * @param array $element Element data.
	 * @return string
	 */
	private static function render_element( $element ) {
		$type     = sanitize_key( $element['type'] );
		$settings = $element['settings'];
		$align    = isset( $settings['align'] ) && in_array( $settings['align'], array( 'left', 'center', 'right' ), true ) ? $settings['align'] : 'left';
		$class    = 'nb-module nb-module-' . $type . ' nb-align-' . $align;

		switch ( $type ) {
			case 'heading':
				$level = isset( $settings['level'] ) && in_array( $settings['level'], array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ), true ) ? $settings['level'] : 'h2';
				$text  = isset( $settings['text'] ) ? (string) $settings['text'] : '';
				return '<div class="' . esc_attr( $class ) . '"><' . $level . '>' . esc_html( $text ) . '</' . $level . '></div>';

			case 'text':
				$text = isset( $settings['text'] ) ? (string) $settings['text'] : '';
				return '<div class="' . esc_attr( $class ) . '"><p>' . nl2br( esc_html( $text ) ) . '</p></div>';

			case 'button':
				$text = isset( $settings['text'] ) ? (string) $settings['text'] : __( 'Button', 'nirmaan-builder' );
				$url  = isset( $settings['url'] ) ? esc_url( $settings['url'] ) : '';
				if ( '' === $url ) {
					return '<div class="' . esc_attr( $class ) . '"><span class="nb-button">' . esc_html( $text ) . '</span></div>';
				}
				return '<div class="' . esc_attr( $class ) . '"><a class="nb-button" href="' . $url . '">' . esc_html( $text ) . '</a></div>';

			case 'image':
				$url = isset( $settings['url'] ) ? esc_url( $settings['url'] ) : '';
				if ( '' === $url ) {
					return '';
				}
				$alt = isset( $settings['alt'] ) ? (string) $settings['alt'] : '';
				return '<figure class="' . esc_attr( $class ) . '"><img src="' . $url . '" alt="' . esc_attr( $alt ) . '" loading="lazy" decoding="async"></figure>';

			case 'divider':
				return '<div class="' . esc_attr( $class ) . '"><hr></div>';

			case 'spacer':
				$height = isset( $settings['height'] ) ? absint( $settings['height'] ) : 32;
				$height = min( 500, $height );
				return '<div class="' . esc_attr( $class ) . '" aria-hidden="true" style="height:' . esc_attr( $height ) . 'px"></div>';
		}

		/**
		 * Allow extensions to render registered custom modules.
		 *
		 * Custom render callbacks remain responsible for escaping their output.
		 *
		 * @param string $html    Rendered markup.
		 * @param array  $element Element data.
		 */
		return (string) apply_filters( 'nirmaan_builder_render_unknown_module', '', $element );
	}
}
