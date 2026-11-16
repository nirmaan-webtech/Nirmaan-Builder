<?php
/**
 * WordPress admin navigation.
 *
 * @package NirmaanBuilder
 */

namespace NirmaanBuilder;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin menu and assets.
 */
class Admin {

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function hooks() {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Register parent menu and requested child screens.
	 *
	 * @return void
	 */
	public function register_menu() {
		$capability = 'edit_posts';
		$icon       = 'dashicons-layout';

		add_menu_page(
			__( 'Nirmaan Builder', 'nirmaan-builder' ),
			__( 'Nirmaan Builder', 'nirmaan-builder' ),
			$capability,
			'nirmaan-builder',
			array( $this, 'render_dashboard' ),
			$icon,
			58
		);

		add_submenu_page(
			'nirmaan-builder',
			__( 'Theme Option', 'nirmaan-builder' ),
			__( 'Theme Option', 'nirmaan-builder' ),
			$capability,
			'nirmaan-builder',
			array( $this, 'render_dashboard' )
		);

		add_submenu_page(
			'nirmaan-builder',
			__( 'Theme Builder', 'nirmaan-builder' ),
			__( 'Theme Builder', 'nirmaan-builder' ),
			$capability,
			'nirmaan-theme-builder',
			array( $this, 'render_theme_builder' )
		);

		add_submenu_page(
			'nirmaan-builder',
			__( 'Library', 'nirmaan-builder' ),
			__( 'Library', 'nirmaan-builder' ),
			$capability,
			'nirmaan-library',
			array( $this, 'render_library' )
		);
	}

	/**
	 * Enqueue admin stylesheet on plugin screens only.
	 *
	 * @param string $hook_suffix Current admin page hook.
	 * @return void
	 */
	public function enqueue_assets( $hook_suffix ) {
		if ( false === strpos( (string) $hook_suffix, 'nirmaan' ) ) {
			return;
		}

		wp_enqueue_style(
			'nirmaan-builder-admin',
			NIRMAAN_BUILDER_URL . 'assets/css/admin.css',
			array(),
			NIRMAAN_BUILDER_VERSION
		);
	}

	/**
	 * Render dashboard shell.
	 *
	 * @return void
	 */
	public function render_dashboard() {
		$this->render_screen(
			__( 'Nirmaan Builder', 'nirmaan-builder' ),
			__( 'Theme settings and builder overview will be configured here.', 'nirmaan-builder' )
		);
	}

	/**
	 * Render theme builder shell.
	 *
	 * @return void
	 */
	public function render_theme_builder() {
		$this->render_screen(
			__( 'Theme Builder', 'nirmaan-builder' ),
			__( 'Template management is part of the next implementation phase.', 'nirmaan-builder' )
		);
	}

	/**
	 * Render library shell.
	 *
	 * @return void
	 */
	public function render_library() {
		$this->render_screen(
			__( 'Library', 'nirmaan-builder' ),
			__( 'Saved layouts and reusable elements will appear here.', 'nirmaan-builder' )
		);
	}

	/**
	 * Render a consistent admin screen.
	 *
	 * @param string $title Screen title.
	 * @param string $description Screen description.
	 * @return void
	 */
	private function render_screen( $title, $description ) {
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this screen.', 'nirmaan-builder' ) );
		}
		?>
		<div class="wrap nirmaan-builder-admin">
			<h1><?php echo esc_html( $title ); ?></h1>
			<p><?php echo esc_html( $description ); ?></p>
			<div class="nirmaan-builder-notice notice notice-info inline">
				<p><?php esc_html_e( 'Foundation version 0.1.0. This screen is a scaffold, not a completed visual builder.', 'nirmaan-builder' ); ?></p>
			</div>
		</div>
		<?php
	}
}
