<?php
/**
 * Nirmaan theme and integrated Builder bootstrap.
 *
 * @package NirmaanStarter
 */

defined( 'ABSPATH' ) || exit;

define( 'NIRMAAN_STARTER_VERSION', '0.2.0' );
define( 'NIRMAAN_STARTER_DIR', get_template_directory() );

function nirmaan_starter_setup() {
	load_theme_textdomain( 'nirmaan-starter', NIRMAAN_STARTER_DIR . '/languages' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'html5', array( 'comment-list', 'comment-form', 'search-form', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'custom-logo', array( 'height' => 80, 'width' => 260, 'flex-height' => true, 'flex-width' => true ) );
	register_nav_menus( array(
		'primary' => __( 'Primary Menu', 'nirmaan-starter' ),
		'footer'  => __( 'Footer Menu', 'nirmaan-starter' ),
	) );
}
add_action( 'after_setup_theme', 'nirmaan_starter_setup' );

function nirmaan_starter_enqueue_assets() {
	wp_enqueue_style( 'nirmaan-starter', get_stylesheet_uri(), array(), NIRMAAN_STARTER_VERSION );
	wp_enqueue_style( 'nirmaan-starter-theme', get_template_directory_uri() . '/assets/css/theme.css', array( 'nirmaan-starter' ), NIRMAAN_STARTER_VERSION );
}
add_action( 'wp_enqueue_scripts', 'nirmaan_starter_enqueue_assets' );

function nirmaan_starter_editor_assets() {
	add_editor_style( 'assets/css/editor.css' );
}
add_action( 'after_setup_theme', 'nirmaan_starter_editor_assets' );

function nirmaan_starter_body_classes( $classes ) {
	$classes[] = 'nirmaan-site';
	return $classes;
}
add_filter( 'body_class', 'nirmaan_starter_body_classes' );

// The complete visual Builder engine is part of this theme; no companion plugin is required.
require_once NIRMAAN_STARTER_DIR . '/builder/bootstrap.php';
