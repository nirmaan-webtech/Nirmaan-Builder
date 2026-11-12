<?php
/**
 * Theme Builder footer template.
 *
 * @package NirmaanBuilder
 */

defined( 'ABSPATH' ) || exit;

$template_id = \NirmaanBuilder\Template_Resolver::find( 'footer' );
if ( $template_id ) {
	echo \NirmaanBuilder\Renderer::render( $template_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Renderer escapes supported module output.
}
