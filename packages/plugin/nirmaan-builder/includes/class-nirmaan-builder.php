<?php
/**
 * Core plugin coordinator.
 *
 * @package NirmaanBuilder
 */

namespace NirmaanBuilder;

defined( 'ABSPATH' ) || exit;

final class Plugin {
	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function init() {
		add_action( 'init', array( $this, 'register_post_types' ) );
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );
		add_action( 'admin_menu', array( $this, 'register_admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_editor_assets' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
		add_filter( 'template_include', array( $this, 'load_layout_template' ) );
	}

	public function register_post_types() {
		register_post_type(
			'nirmaan_layout',
			array(
				'labels' => array(
					'name'          => __( 'Builder Layouts', 'nirmaan-builder' ),
					'singular_name' => __( 'Builder Layout', 'nirmaan-builder' ),
				),
				'public'              => true,
				'publicly_queryable'  => true,
				'show_ui'             => false,
				'show_in_rest'        => false,
				'has_archive'         => false,
				'rewrite'             => false,
				'query_var'           => true,
				'exclude_from_search' => true,
				'supports'            => array( 'title', 'author', 'revisions' ),
				'capability_type'     => 'post',
				'map_meta_cap'        => true,
			)
		);
	}

	public function load_layout_template( $template ) {
		if ( is_singular( 'nirmaan_layout' ) ) {
			$plugin_template = NIRMAAN_BUILDER_DIR . 'templates/single-nirmaan_layout.php';
			if ( file_exists( $plugin_template ) ) {
				return $plugin_template;
			}
		}
		return $template;
	}

	public function register_admin_menu() {
		add_menu_page(
			__( 'Nirmaan Builder', 'nirmaan-builder' ),
			__( 'Nirmaan Builder', 'nirmaan-builder' ),
			'edit_posts',
			'nirmaan-builder',
			array( $this, 'render_editor_page' ),
			'dashicons-layout',
			58
		);
	}

	public function render_editor_page() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die( esc_html__( 'You are not allowed to access the Builder.', 'nirmaan-builder' ) );
		}
		echo '<div id="nirmaan-builder-root"></div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static mount element.
	}

	public function enqueue_editor_assets( $hook_suffix ) {
		if ( 'toplevel_page_nirmaan-builder' !== $hook_suffix ) {
			return;
		}

		$script_path = NIRMAAN_BUILDER_DIR . 'assets/build/editor.js';
		$style_path  = NIRMAAN_BUILDER_DIR . 'assets/build/editor.css';

		if ( ! file_exists( $script_path ) || ! file_exists( $style_path ) ) {
			add_action( 'admin_notices', static function () {
				echo '<div class="notice notice-error"><p>' .
					esc_html__( 'Nirmaan Builder editor assets are missing. Build the plugin assets using npm run build inside the plugin directory.', 'nirmaan-builder' ) .
					'</p></div>';
			} );
			return;
		}

		wp_enqueue_style( 'nirmaan-builder-editor', NIRMAAN_BUILDER_URL . 'assets/build/editor.css', array(), NIRMAAN_BUILDER_VERSION );
		wp_enqueue_script( 'nirmaan-builder-editor', NIRMAAN_BUILDER_URL . 'assets/build/editor.js', array(), NIRMAAN_BUILDER_VERSION, true );
		wp_localize_script(
			'nirmaan-builder-editor',
			'NirmaanBuilderConfig',
			array(
				'restUrl' => esc_url_raw( rest_url( 'nirmaan-builder/v1/' ) ),
				'nonce'   => wp_create_nonce( 'wp_rest' ),
			)
		);
	}

	public function enqueue_frontend_assets() {
		if ( ! is_singular( 'nirmaan_layout' ) ) {
			return;
		}
		wp_enqueue_style(
			'nirmaan-builder-frontend',
			NIRMAAN_BUILDER_URL . 'assets/css/frontend.css',
			array(),
			NIRMAAN_BUILDER_VERSION
		);
	}

	public function register_rest_routes() {
		$controller = new Rest\Documents_Controller();
		$controller->register_routes();
	}
}
