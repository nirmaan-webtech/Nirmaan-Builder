<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

define( 'NIRMAAN_BUILDER_VERSION', '0.3.0' );
define( 'NIRMAAN_BUILDER_PATH', trailingslashit( get_template_directory() ) );
define( 'NIRMAAN_BUILDER_URL', trailingslashit( get_template_directory_uri() ) );

require_once NIRMAAN_BUILDER_PATH . 'includes/class-nirmaan-builder.php';

function nirmaan_builder_theme_setup() {
	load_theme_textdomain( 'nirmaan-builder', NIRMAAN_BUILDER_PATH . 'languages' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'custom-logo', array( 'height' => 80, 'width' => 280, 'flex-height' => true, 'flex-width' => true ) );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'align-wide' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'woocommerce' );
	add_editor_style( 'style.css' );
	register_nav_menus( array( 'primary' => __( 'Primary Menu', 'nirmaan-builder' ), 'footer' => __( 'Footer Menu', 'nirmaan-builder' ) ) );
}
add_action( 'after_setup_theme', 'nirmaan_builder_theme_setup' );

function nirmaan_builder_enqueue_theme_styles() {
	wp_enqueue_style( 'nirmaan-builder-theme', get_stylesheet_uri(), array(), NIRMAAN_BUILDER_VERSION );
}
add_action( 'wp_enqueue_scripts', 'nirmaan_builder_enqueue_theme_styles', 5 );

function nirmaan_builder_theme_boot() {
	$GLOBALS['nirmaan_builder_renderer'] = \NirmaanBuilder\Theme::instance()->boot();
}
add_action( 'after_setup_theme', 'nirmaan_builder_theme_boot', 20 );

function nirmaan_builder_has_active_part( $part ) {
	if ( empty( $GLOBALS['nirmaan_builder_renderer'] ) || ! $GLOBALS['nirmaan_builder_renderer'] instanceof \NirmaanBuilder\Renderer ) {
		return false;
	}
	return $GLOBALS['nirmaan_builder_renderer']->has_active_part( $part );
}
