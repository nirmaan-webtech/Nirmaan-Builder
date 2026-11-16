<?php
namespace NirmaanBuilder;
if ( ! defined( 'ABSPATH' ) ) { exit; }
final class Plugin {
	private static $instance = null;
	public static function instance() { if ( null === self::$instance ) { self::$instance = new self(); } return self::$instance; }
	public function boot() {
		require_once NIRMAAN_BUILDER_PATH . 'includes/class-nirmaan-builder-admin.php';
		require_once NIRMAAN_BUILDER_PATH . 'includes/class-nirmaan-builder-content.php';
		require_once NIRMAAN_BUILDER_PATH . 'includes/class-nirmaan-builder-rest.php';
		require_once NIRMAAN_BUILDER_PATH . 'includes/class-nirmaan-builder-renderer.php';
		require_once NIRMAAN_BUILDER_PATH . 'includes/class-nirmaan-builder-settings.php';
		( new Admin() )->hooks(); $this->register_content_types(); ( new Rest() )->register_hooks(); ( new Renderer() )->hooks(); ( new Settings() )->register_hooks();
		add_action( 'init', array( $this, 'load_textdomain' ) );
	}
	public function load_textdomain() { load_plugin_textdomain( 'nirmaan-builder', false, dirname( plugin_basename( NIRMAAN_BUILDER_FILE ) ) . '/languages' ); }
	public function register_content_types() { Content::register(); }
}
