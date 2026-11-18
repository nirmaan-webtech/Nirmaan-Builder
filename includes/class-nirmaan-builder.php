<?php
namespace NirmaanBuilder;
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class Theme {
 private static $instance = null;
 private $booted = false;
 public static function instance() { if ( null === self::$instance ) { self::$instance = new self(); } return self::$instance; }
 public function boot() {
  if ( $this->booted ) return $GLOBALS['nirmaan_builder_renderer'] ?? null;
  $this->booted = true;
  require_once NIRMAAN_BUILDER_PATH . 'includes/class-nirmaan-builder-admin.php';
  require_once NIRMAAN_BUILDER_PATH . 'includes/class-nirmaan-builder-content.php';
  require_once NIRMAAN_BUILDER_PATH . 'includes/class-nirmaan-builder-rest.php';
  require_once NIRMAAN_BUILDER_PATH . 'includes/class-nirmaan-builder-renderer.php';
  require_once NIRMAAN_BUILDER_PATH . 'includes/class-nirmaan-builder-settings.php';
  require_once NIRMAAN_BUILDER_PATH . 'includes/class-nirmaan-builder-forms.php';
  require_once NIRMAAN_BUILDER_PATH . 'includes/class-nirmaan-builder-roles.php';
  require_once NIRMAAN_BUILDER_PATH . 'includes/class-nirmaan-builder-multilingual.php';
  ( new Admin() )->hooks();
  Roles::register_hooks();
  Multilingual::register_hooks();
  add_action( 'init', array( $this, 'register_content_types' ), 5 );
  add_action( 'init', array( Content::class, 'register_meta' ), 99 );
  ( new Rest() )->register_hooks();
  $renderer = new Renderer();
  $renderer->hooks();
  ( new Settings() )->register_hooks();
  ( new Forms() )->hooks();
  add_action( 'init', array( $this, 'load_textdomain' ) );
  return $renderer;
 }
 public function load_textdomain() { load_theme_textdomain( 'nirmaan-builder', NIRMAAN_BUILDER_PATH . 'languages' ); }
 public function register_content_types() { Content::register(); }
}
