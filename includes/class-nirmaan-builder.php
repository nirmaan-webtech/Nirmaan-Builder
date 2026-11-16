<?php
/**
 * Main plugin coordinator.
 *
 * @package NirmaanBuilder
 */

namespace NirmaanBuilder;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main plugin class.
 */
final class Plugin {

	/**
	 * Singleton instance.
	 *
	 * @var Plugin|null
	 */
	private static $instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @return Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Boot plugin services.
	 *
	 * @return void
	 */
	public function boot() {
		require_once NIRMAAN_BUILDER_PATH . 'includes/class-nirmaan-builder-admin.php';
		require_once NIRMAAN_BUILDER_PATH . 'includes/class-nirmaan-builder-content.php';
		require_once NIRMAAN_BUILDER_PATH . 'includes/class-nirmaan-builder-rest.php';

		( new Admin() )->hooks();
		$this->register_content_types();
		( new Rest() )->register_hooks();

		add_action( 'init', array( $this, 'load_textdomain' ) );
	}

	/**
	 * Load translations.
	 *
	 * @return void
	 */
	public function load_textdomain() {
		load_plugin_textdomain(
			'nirmaan-builder',
			false,
			dirname( plugin_basename( NIRMAAN_BUILDER_FILE ) ) . '/languages'
		);
	}

	/**
	 * Register plugin-owned content types.
	 *
	 * @return void
	 */
	public function register_content_types() {
		Content::register();
	}
}
