<?php
namespace NirmaanBuilder;
if ( ! defined( 'ABSPATH' ) ) { exit; }
class Renderer {
	public function hooks() {
		add_filter( 'the_content', array( $this, 'filter_content' ), 20 );
		add_action( 'wp_body_open', array( $this, 'render_header' ), 5 );
		add_action( 'wp_footer', array( $this, 'render_footer' ), 5 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
	}
	public function enqueue_styles() { wp_enqueue_style( 'nirmaan-builder-frontend', NIRMAAN_BUILDER_URL . 'assets/css/frontend.css', array(), NIRMAAN_BUILDER_VERSION ); }
	private function assigned_template() {
		$templates = get_posts( array( 'post_type' => 'nirmaan_template', 'post_status' => 'publish', 'numberposts' => 100, 'orderby' => 'menu_order', 'order' => 'ASC' ) );
		foreach ( $templates as $template ) {
			$conditions = get_post_meta( $template->ID, '_nirmaan_conditions', true );
			if ( $this->matches_conditions( is_array( $conditions ) ? $conditions : array() ) ) { return $template->ID; }
		}
		return 0;
	}
	private function matches_conditions( $conditions ) {
		$includes = isset( $conditions['include'] ) && is_array( $conditions['include'] ) ? $conditions['include'] : array();
		$excludes = isset( $conditions['exclude'] ) && is_array( $conditions['exclude'] ) ? $conditions['exclude'] : array();
		$included = false;
		foreach ( $includes as $rule ) { if ( $this->matches_rule( $rule ) ) { $included = true; break; } }
		if ( ! $included ) { return false; }
		foreach ( $excludes as $rule ) { if ( $this->matches_rule( $rule ) ) { return false; } }
		return true;
	}
	private function matches_rule( $rule ) {
		if ( ! is_array( $rule ) || empty( $rule['type'] ) ) { return false; }
		$type = sanitize_key( $rule['type'] ); $value = absint( $rule['value'] ?? 0 );
		switch ( $type ) {
			case 'entire_site': return true;
			case 'front_page': return is_front_page();
			case 'singular': return is_singular() && $value === get_queried_object_id();
			case 'all_pages': return is_page();
			case 'all_posts': return is_single() && 'post' === get_post_type();
			case 'post_type': return is_singular( sanitize_key( $rule['post_type'] ?? '' ) );
			case 'archive': return is_archive();
			case 'search': return is_search();
			case '404': return is_404();
			default: return false;
		}
	}
	public function filter_content( $content ) {
		if ( is_admin() || ! is_main_query() || ! in_the_loop() ) { return $content; }
		$document = get_post_meta( get_the_ID(), '_nirmaan_builder_document', true );
		if ( is_array( $document ) && ! empty( $document['nodes'] ) ) { return $this->render_nodes( $document['nodes'] ); }
		$template_id = $this->assigned_template();
		if ( $template_id ) {
			$parts = get_post_meta( $template_id, '_nirmaan_template_parts', true );
			if ( is_array( $parts ) && ! empty( $parts['body']['nodes'] ) ) { return $this->render_nodes( $parts['body']['nodes'] ); }
		}
		return $content;
	}
	public function render_header() { $this->render_part( 'header' ); }
	public function render_footer() { $this->render_part( 'footer' ); }
	private function render_part( $part ) {
		if ( is_admin() ) { return; } $id = $this->assigned_template(); if ( ! $id ) { return; }
		$parts = get_post_meta( $id, '_nirmaan_template_parts', true );
		if ( isset( $parts[ $part ]['nodes'] ) && is_array( $parts[ $part ]['nodes'] ) ) {
			echo $this->render_nodes( $parts[ $part ]['nodes'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}
	private function woo_module( $type ) {
		if ( ! function_exists( 'wc_get_product' ) ) { return '<!-- WooCommerce module unavailable: WooCommerce is inactive. -->'; }
		if ( 'related_products' === $type ) { return do_shortcode( '[related_products limit="4"]' ); }
		if ( 'shop_products' === $type ) { return do_shortcode( '[products limit="8" columns="4"]' ); }
		global $product;
		if ( ! $product && get_the_ID() ) { $product = wc_get_product( get_the_ID() ); }
		if ( ! $product ) { return ''; }
		$map = array(
			'product_title' => 'woocommerce_template_single_title',
			'product_price' => 'woocommerce_template_single_price',
			'product_image' => 'woocommerce_show_product_images',
			'product_add_to_cart' => 'woocommerce_template_single_add_to_cart',
			'product_description' => 'woocommerce_template_single_excerpt',
			'product_rating' => 'woocommerce_template_single_rating',
		);
		if ( empty( $map[ $type ] ) || ! function_exists( $map[ $type ] ) ) { return ''; }
		ob_start(); call_user_func( $map[ $type ] ); return ob_get_clean();
	}
	public function render_nodes( $nodes ) {
		if ( ! is_array( $nodes ) ) { return ''; } $html = '';
		foreach ( array_slice( $nodes, 0, 300 ) as $node ) {
			if ( ! is_array( $node ) ) { continue; }
			$type = sanitize_key( $node['type'] ?? '' ); $s = is_array( $node['settings'] ?? null ) ? $node['settings'] : array(); $content = $s['content'] ?? '';
			if ( in_array( $type, array( 'product_title', 'product_price', 'product_image', 'product_add_to_cart', 'product_description', 'product_rating', 'related_products', 'shop_products' ), true ) ) { $html .= $this->woo_module( $type ); continue; }
			switch ( $type ) {
				case 'heading': $level = min( 6, max( 1, absint( $s['level'] ?? 2 ) ) ); $html .= sprintf( '<h%d class="nb-heading">%s</h%d>', $level, esc_html( $content ), $level ); break;
				case 'text': $html .= '<div class="nb-text">' . wp_kses_post( $content ) . '</div>'; break;
				case 'image': $url = esc_url( $s['url'] ?? '' ); $alt = esc_attr( $s['alt'] ?? '' ); if ( $url ) { $html .= sprintf( '<figure class="nb-image"><img src="%s" alt="%s" loading="lazy"></figure>', $url, $alt ); } break;
				case 'button': $html .= sprintf( '<p class="nb-button-wrap"><a class="nb-button" href="%s">%s</a></p>', esc_url( $s['url'] ?? '#' ), esc_html( $content ?: __( 'Button', 'nirmaan-builder' ) ) ); break;
				case 'divider': $html .= '<hr class="nb-divider">'; break;
				case 'spacer': $height = min( 400, absint( $s['height'] ?? 32 ) ); $html .= '<div class="nb-spacer" style="height:' . esc_attr( $height ) . 'px"></div>'; break;
				case 'container': case 'columns': $html .= '<div class="nb-container">' . $this->render_nodes( $node['children'] ?? array() ) . '</div>'; break;
			}
		}
		return $html;
	}
}
