<?php
namespace NirmaanBuilder;
if ( ! defined( 'ABSPATH' ) ) { exit; }
class Admin {
	public function hooks() {
		add_action( 'admin_menu', array( $this, 'register_menu' ) ); add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_builder_metabox' ) ); add_filter( 'page_row_actions', array( $this, 'row_builder_link' ), 10, 2 ); add_filter( 'post_row_actions', array( $this, 'row_builder_link' ), 10, 2 );
	}
	public function register_menu() {
		$cap = 'edit_posts'; add_menu_page( 'Nirmaan Builder', 'Nirmaan Builder', $cap, 'nirmaan-builder', array( $this, 'render_dashboard' ), 'dashicons-layout', 58 );
		add_submenu_page( 'nirmaan-builder', 'Theme Option', 'Theme Option', $cap, 'nirmaan-builder', array( $this, 'render_dashboard' ) );
		add_submenu_page( 'nirmaan-builder', 'Theme Builder', 'Theme Builder', 'edit_theme_options', 'nirmaan-theme-builder', array( $this, 'render_theme_builder' ) );
		add_submenu_page( 'nirmaan-builder', 'Library', 'Library', $cap, 'nirmaan-library', array( $this, 'render_library' ) );
		add_submenu_page( null, 'Visual Editor', 'Visual Editor', $cap, 'nirmaan-editor', array( $this, 'render_editor' ) );
	}
	public function enqueue_assets( $hook ) {
		if ( false === strpos( (string) $hook, 'nirmaan' ) ) { return; } wp_enqueue_style( 'nirmaan-builder-admin', NIRMAAN_BUILDER_URL . 'assets/css/admin.css', array(), NIRMAAN_BUILDER_VERSION );
		if ( 'admin_page_nirmaan-editor' === $hook ) {
			wp_enqueue_style( 'nirmaan-builder-editor', NIRMAAN_BUILDER_URL . 'assets/css/editor.css', array(), NIRMAAN_BUILDER_VERSION ); wp_enqueue_script( 'nirmaan-builder-editor', NIRMAAN_BUILDER_URL . 'assets/js/builder.js', array(), NIRMAAN_BUILDER_VERSION, true );
			$post_id = absint( $_GET['post_id'] ?? 0 ); $template_id = absint( $_GET['template_id'] ?? 0 ); $part = sanitize_key( wp_unslash( $_GET['part'] ?? 'body' ) );
			wp_localize_script( 'nirmaan-builder-editor', 'NirmaanBuilderConfig', array( 'apiRoot' => esc_url_raw( rest_url( 'nirmaan-builder/v1/' ) ), 'nonce' => wp_create_nonce( 'wp_rest' ), 'postId' => $post_id, 'templateId' => $template_id, 'part' => in_array( $part, array( 'header', 'body', 'footer' ), true ) ? $part : 'body' ) );
		}
	}
	public function add_builder_metabox() { foreach ( get_post_types( array( 'public' => true ), 'names' ) as $type ) { add_meta_box( 'nirmaan-builder', 'Nirmaan Builder', array( $this, 'render_builder_metabox' ), $type, 'side', 'high' ); } }
	public function render_builder_metabox( $post ) {
		if ( ! current_user_can( 'edit_post', $post->ID ) ) { return; } $url = add_query_arg( array( 'page' => 'nirmaan-editor', 'post_id' => $post->ID ), admin_url( 'admin.php' ) );
		echo '<p>' . esc_html__( 'Design this content with Nirmaan Builder.', 'nirmaan-builder' ) . '</p><a class="button button-primary" href="' . esc_url( $url ) . '">' . esc_html__( 'Edit with Nirmaan Builder', 'nirmaan-builder' ) . '</a>';
	}
	public function row_builder_link( $actions, $post ) {
		if ( ! is_object( $post ) || ! current_user_can( 'edit_post', $post->ID ) ) { return $actions; } $url = add_query_arg( array( 'page' => 'nirmaan-editor', 'post_id' => $post->ID ), admin_url( 'admin.php' ) );
		$actions['nirmaan_builder'] = '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Edit with Nirmaan Builder', 'nirmaan-builder' ) . '</a>'; return $actions;
	}
	public function render_dashboard() { $this->screen( 'Nirmaan Builder', 'Global builder settings will be expanded in upcoming releases.' ); }
	public function render_library() {
		if ( ! current_user_can( 'edit_posts' ) ) { wp_die( esc_html__( 'Permission denied.', 'nirmaan-builder' ) ); }
		$items = get_posts( array( 'post_type' => 'nirmaan_library', 'post_status' => 'publish', 'numberposts' => 100 ) ); ?>
		<div class="wrap nirmaan-builder-admin"><h1>Library</h1><p>Reusable layouts saved from the visual editor.</p>
		<table class="widefat striped"><thead><tr><th>Layout</th><th>Actions</th></tr></thead><tbody>
		<?php if ( ! $items ) : ?><tr><td colspan="2">No saved layouts yet. Use Save to Library in the visual editor.</td></tr><?php endif; ?>
		<?php foreach ( $items as $item ) : ?><tr><td><?php echo esc_html( $item->post_title ); ?></td><td><button class="button button-small nirmaan-delete-library" data-id="<?php echo esc_attr( $item->ID ); ?>">Delete</button></td></tr><?php endforeach; ?>
		</tbody></table></div><?php
	}
	public function render_theme_builder() {
		if ( ! current_user_can( 'edit_theme_options' ) ) { wp_die( esc_html__( 'You cannot manage theme templates.', 'nirmaan-builder' ) ); }
		$templates = get_posts( array( 'post_type' => 'nirmaan_template', 'post_status' => array( 'publish', 'draft' ), 'numberposts' => 100 ) ); ?>
		<div class="wrap nirmaan-builder-admin"><h1>Theme Builder</h1><p>Create templates and edit Header, Body and Footer parts.</p>
		<form id="nirmaan-create-template" class="nirmaan-create-template"><input name="title" required placeholder="Template name"><select name="condition"><option value="entire_site">Entire Website</option><option value="front_page">Front Page</option><option value="all_pages">All Pages</option><option value="all_posts">All Posts</option><option value="archive">Archives</option></select><button class="button button-primary">Create Template</button></form>
		<table class="widefat striped"><thead><tr><th>Template</th><th>Header</th><th>Body</th><th>Footer</th><th>Actions</th></tr></thead><tbody>
		<?php if ( ! $templates ) : ?><tr><td colspan="5">No templates yet.</td></tr><?php endif; ?>
		<?php foreach ( $templates as $template ) : $base = admin_url( 'admin.php?page=nirmaan-editor&template_id=' . $template->ID . '&part=' ); ?><tr><td><strong><?php echo esc_html( $template->post_title ); ?></strong></td><?php foreach ( array( 'header', 'body', 'footer' ) as $part ) : ?><td><a class="button button-small" href="<?php echo esc_url( $base . $part ); ?>"><?php echo esc_html( ucfirst( $part ) . ' · Edit' ); ?></a></td><?php endforeach; ?><td><button class="button button-small nirmaan-delete-template" data-id="<?php echo esc_attr( $template->ID ); ?>">Delete</button></td></tr><?php endforeach; ?>
		</tbody></table></div><?php
	}
	public function render_editor() {
		$post_id = absint( $_GET['post_id'] ?? 0 ); $template_id = absint( $_GET['template_id'] ?? 0 );
		if ( $post_id && ! current_user_can( 'edit_post', $post_id ) ) { wp_die( esc_html__( 'You cannot edit this content.', 'nirmaan-builder' ) ); }
		if ( $template_id && ! current_user_can( 'edit_theme_options' ) ) { wp_die( esc_html__( 'You cannot edit theme templates.', 'nirmaan-builder' ) ); }
		if ( ! $post_id && ! $template_id ) { wp_die( esc_html__( 'Select content or a template to edit.', 'nirmaan-builder' ) ); }
		$part = sanitize_key( wp_unslash( $_GET['part'] ?? 'body' ) ); ?>
		<div class="wrap nirmaan-editor-wrap"><div class="nirmaan-editor-topbar"><a href="<?php echo esc_url( admin_url( $template_id ? 'admin.php?page=nirmaan-theme-builder' : 'edit.php' ) ); ?>" class="button">Exit</a><strong>Nirmaan Visual Builder</strong><span class="nirmaan-save-status" aria-live="polite"></span><button id="nirmaan-save-library" class="button">Save to Library</button><button id="nirmaan-save" class="button button-primary">Save</button></div>
		<div class="nirmaan-editor-layout"><aside class="nirmaan-editor-sidebar"><h2>Add Elements</h2><div id="nirmaan-module-list"></div><hr><h2>Saved Layouts</h2><div id="nirmaan-library-list"></div><hr><h2>Element Settings</h2><div id="nirmaan-settings">Select an element on the canvas.</div></aside>
		<main class="nirmaan-editor-main"><div class="nirmaan-canvas-label"><?php echo esc_html( $template_id ? ucfirst( $part ) . ' Template Part' : get_the_title( $post_id ) ); ?></div><div id="nirmaan-canvas" class="nirmaan-canvas"></div></main></div></div><?php
	}
	private function screen( $title, $description ) { if ( ! current_user_can( 'edit_posts' ) ) { wp_die( esc_html__( 'Permission denied.', 'nirmaan-builder' ) ); } echo '<div class="wrap nirmaan-builder-admin"><h1>' . esc_html( $title ) . '</h1><p>' . esc_html( $description ) . '</p></div>'; }
}
