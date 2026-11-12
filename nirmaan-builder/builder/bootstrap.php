<?php
/**
 * Nirmaan Builder engine bootstrapped directly by the Nirmaan theme.
 *
 * @package NirmaanBuilder
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'NIRMAAN_BUILDER_VERSION' ) ) {
	define( 'NIRMAAN_BUILDER_VERSION', '0.2.0' );
}
if ( ! defined( 'NIRMAAN_BUILDER_DIR' ) ) {
	define( 'NIRMAAN_BUILDER_DIR', trailingslashit( get_template_directory() ) . 'builder/' );
}
if ( ! defined( 'NIRMAAN_BUILDER_URL' ) ) {
	define( 'NIRMAAN_BUILDER_URL', trailingslashit( get_template_directory_uri() ) . 'builder/' );
}

require_once NIRMAAN_BUILDER_DIR . 'includes/class-document-validator.php';
require_once NIRMAAN_BUILDER_DIR . 'includes/class-renderer.php';
require_once NIRMAAN_BUILDER_DIR . 'includes/class-template-resolver.php';
require_once NIRMAAN_BUILDER_DIR . 'includes/class-nirmaan-builder.php';

/**
 * Start the visual builder as part of the theme lifecycle.
 *
 * @return void
 */
function nirmaan_builder_theme_boot() {
	\NirmaanBuilder\Plugin::instance()->init();
}
add_action( 'after_setup_theme', 'nirmaan_builder_theme_boot', 20 );
