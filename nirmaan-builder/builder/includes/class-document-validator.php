<?php
/**
 * Canonical document payload validator.
 *
 * @package NirmaanBuilder
 */
namespace NirmaanBuilder;

defined( 'ABSPATH' ) || exit;

final class Document_Validator {
	private const MAX_ELEMENTS = 500;
	private const MAX_TEXT_BYTES = 10000;

	/**
	 * Validate the versioned, flat document schema used by editor v1.
	 *
	 * @param mixed $document Document payload.
	 * @return bool
	 */
	public static function is_valid( $document ) {
		if ( ! is_array( $document ) || array_diff( array_keys( $document ), array( 'schema_version', 'elements' ) ) ) {
			return false;
		}
		if ( ! isset( $document['schema_version'], $document['elements'] ) || 1 !== $document['schema_version'] || ! is_array( $document['elements'] ) || ! array_is_list( $document['elements'] ) ) {
			return false;
		}
		if ( count( $document['elements'] ) > self::MAX_ELEMENTS ) {
			return false;
		}

		$seen_ids = array();
		foreach ( $document['elements'] as $element ) {
			if ( ! self::valid_element( $element, $seen_ids ) ) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Validate one element and its module-specific settings.
	 *
	 * @param mixed $element Element.
	 * @param array $seen_ids IDs already encountered.
	 * @return bool
	 */
	private static function valid_element( $element, &$seen_ids ) {
		if ( ! is_array( $element ) || array_diff( array_keys( $element ), array( 'id', 'type', 'settings' ) ) ) {
			return false;
		}
		if ( ! isset( $element['id'], $element['type'], $element['settings'] ) || ! is_string( $element['id'] ) || ! preg_match( '/^[A-Za-z0-9][A-Za-z0-9._:-]{0,127}$/', $element['id'] ) || isset( $seen_ids[ $element['id'] ] ) ) {
			return false;
		}
		if ( ! is_string( $element['type'] ) || ! is_array( $element['settings'] ) ) {
			return false;
		}
		$seen_ids[ $element['id'] ] = true;

		$schemas = array(
			'heading' => array( 'text', 'level', 'align' ),
			'text'    => array( 'text', 'align' ),
			'button'  => array( 'text', 'url', 'align' ),
			'image'   => array( 'url', 'alt', 'align' ),
			'divider' => array(),
			'spacer'  => array( 'height' ),
		);
		if ( ! isset( $schemas[ $element['type'] ] ) || array_diff( array_keys( $element['settings'] ), $schemas[ $element['type'] ] ) ) {
			return false;
		}

		$settings = $element['settings'];
		foreach ( array( 'text', 'alt' ) as $field ) {
			if ( array_key_exists( $field, $settings ) && ( ! is_string( $settings[ $field ] ) || strlen( $settings[ $field ] ) > self::MAX_TEXT_BYTES ) ) {
				return false;
			}
		}
		if ( array_key_exists( 'url', $settings ) && ! self::valid_url( $settings['url'] ) ) {
			return false;
		}
		if ( array_key_exists( 'align', $settings ) && ! in_array( $settings['align'], array( 'left', 'center', 'right' ), true ) ) {
			return false;
		}
		if ( array_key_exists( 'level', $settings ) && ! in_array( $settings['level'], array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ), true ) ) {
			return false;
		}
		if ( array_key_exists( 'height', $settings ) && ( ! is_int( $settings['height'] ) || $settings['height'] < 0 || $settings['height'] > 500 ) ) {
			return false;
		}
		return true;
	}

	/**
	 * Reject unsafe URL schemes before the renderer applies WordPress escaping.
	 *
	 * @param mixed $url URL value.
	 * @return bool
	 */
	private static function valid_url( $url ) {
		if ( ! is_string( $url ) || strlen( $url ) > 2048 || preg_match( '/[\x00-\x20\\\\]/', $url ) ) {
			return false;
		}
		$scheme = wp_parse_url( $url, PHP_URL_SCHEME );
		return ! $scheme || in_array( strtolower( $scheme ), array( 'http', 'https', 'mailto', 'tel' ), true );
	}
}
