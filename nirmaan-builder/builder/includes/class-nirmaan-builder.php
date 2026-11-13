<?php
/**
 * Integrated visual Builder coordinator.
 *
 * @package NirmaanBuilder
 */

namespace NirmaanBuilder;

defined( 'ABSPATH' ) || exit;

final class Builder_Engine {
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
		add_filter( 'nirmaan_starter_use_builder_header', array( $this, 'use_builder_header' ) );
		add_filter( 'nirmaan_starter_use_builder_footer', array( $this, 'use_builder_footer' ) );
		add_action( 'nirmaan_starter_render_builder_header', array( $this, 'render_builder_header' ) );
		add_action( 'nirmaan_starter_render_builder_footer', array( $this, 'render_builder_footer' ) );
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
			$builder_template = NIRMAAN_BUILDER_DIR . 'templates/single-nirmaan_layout.php';
			if ( file_exists( $builder_template ) ) {
				return $builder_template;
			}
		}
		return $template;
	}

	public function use_builder_header( $enabled ) {
		return (bool) Template_Resolver::find( 'header' ) || (bool) $enabled;
	}

	public function use_builder_footer( $enabled ) {
		return (bool) Template_Resolver::find( 'footer' ) || (bool) $enabled;
	}

	public function render_builder_header() {
		$template_id = Template_Resolver::find( 'header' );
		if ( $template_id ) {
			echo Renderer::render( $template_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	public function render_builder_footer() {
		$template_id = Template_Resolver::find( 'footer' );
		if ( $template_id ) {
			echo Renderer::render( $template_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
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
		echo '<div id="nirmaan-builder-root"></div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
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
					esc_html__( 'Nirmaan Builder assets are missing. Run npm install and npm run build from the theme/builder directory.', 'nirmaan-builder' ) .
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
		$has_builder_header = (bool) Template_Resolver::find( 'header' );
		$has_builder_footer = (bool) Template_Resolver::find( 'footer' );
		if ( ! is_singular( 'nirmaan_layout' ) && ! $has_builder_header && ! $has_builder_footer ) {
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
