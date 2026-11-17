<?php
namespace NirmaanBuilder;
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Stores and sanitizes site-wide builder design settings.
 */
final class Settings {
 const OPTION = 'nirmaan_builder_settings';

 public static function defaults() {
  return array(
   'logo_id' => 0,
   'favicon_id' => 0,
   'primary_color' => '#2271b1',
   'secondary_color' => '#172033',
   'text_color' => '#1f2937',
   'background_color' => '#ffffff',
   'font_family' => 'system',
   'base_font_size' => '16px',
   'container_width' => '1200px',
   'section_spacing' => '32px',
   'button_radius' => '4px',
   'accent_color' => '#0ea5e9',
   'surface_color' => '#f8fafc',
   'border_color' => '#e2e8f0',
   'muted_color' => '#64748b',
   'heading_font_family' => 'inherit',
   'font_weight' => '400',
   'line_height' => '1.6',
   'spacing_scale' => '8px',
   'shadow_style' => 'soft',
   'custom_css' => '',
  );
 }

 public static function get() {
  $saved = get_option( self::OPTION, array() );
  return wp_parse_args( is_array( $saved ) ? $saved : array(), self::defaults() );
 }

 public static function sanitize( $input ) {
  $input = is_array( $input ) ? $input : array();
  $out = self::get();
  foreach ( array( 'logo_id', 'favicon_id' ) as $key ) {
   $id = absint( $input[ $key ] ?? 0 );
   $out[ $key ] = $id && wp_attachment_is_image( $id ) ? $id : 0;
  }
  foreach ( array( 'primary_color', 'secondary_color', 'text_color', 'background_color', 'accent_color', 'surface_color', 'border_color', 'muted_color' ) as $key ) {
   $out[ $key ] = sanitize_hex_color( $input[ $key ] ?? $out[ $key ] ) ?: self::defaults()[ $key ];
  }
  $fonts = array( 'system', 'Arial, sans-serif', 'Georgia, serif', 'Tahoma, sans-serif', 'Verdana, sans-serif' );
  $font = sanitize_text_field( $input['font_family'] ?? $out['font_family'] );
  $out['font_family'] = in_array( $font, $fonts, true ) ? $font : 'system';
  foreach ( array( 'base_font_size', 'container_width', 'section_spacing', 'button_radius' ) as $key ) {
   $value = sanitize_text_field( $input[ $key ] ?? $out[ $key ] );
   if ( ! preg_match( '/^\d{1,4}(px|rem|em|%)$/', $value ) ) { $value = self::defaults()[ $key ]; }
   $out[ $key ] = $value;
  }
  $heading_fonts = array( 'inherit', 'system', 'Arial, sans-serif', 'Georgia, serif', 'Tahoma, sans-serif', 'Verdana, sans-serif' );
  $heading_font = sanitize_text_field( $input['heading_font_family'] ?? $out['heading_font_family'] );
  $out['heading_font_family'] = in_array( $heading_font, $heading_fonts, true ) ? $heading_font : 'inherit';
  $out['font_weight'] = in_array( (string) ( $input['font_weight'] ?? $out['font_weight'] ), array( '300', '400', '500', '600', '700', '800' ), true ) ? (string) $input['font_weight'] : '400';
  $line_height = (string) ( $input['line_height'] ?? $out['line_height'] );
  $out['line_height'] = preg_match( '/^(0?\\.[8-9]|[1-2](\\.[0-9])?)$/', $line_height ) ? $line_height : '1.6';
  $spacing = sanitize_text_field( $input['spacing_scale'] ?? $out['spacing_scale'] );
  $out['spacing_scale'] = preg_match( '/^\\d{1,3}(px|rem)$/', $spacing ) ? $spacing : '8px';
  $shadow = sanitize_key( $input['shadow_style'] ?? $out['shadow_style'] );
  $out['shadow_style'] = in_array( $shadow, array( 'none', 'soft', 'medium', 'strong' ), true ) ? $shadow : 'soft';
  $out['custom_css'] = substr( wp_strip_all_tags( (string) ( $input['custom_css'] ?? $out['custom_css'] ) ), 0, 20000 );
  return $out;
 }

 public function register_hooks() {
  add_action( 'wp_head', array( $this, 'print_design_tokens' ), 20 );
  add_action( 'wp_head', array( $this, 'print_custom_css' ), 30 );
  add_filter( 'get_site_icon_url', array( $this, 'site_icon' ), 10, 3 );
  add_filter( 'get_custom_logo', array( $this, 'custom_logo' ), 10, 2 );
 }

 public function print_design_tokens() {
  if ( is_admin() ) { return; }
  $s = self::get();
  $font = 'system' === $s['font_family'] ? '-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif' : $s['font_family'];
  echo '<style id="nirmaan-builder-design-tokens">:root{--nb-primary:' . esc_attr( $s['primary_color'] ) . ';--nb-secondary:' . esc_attr( $s['secondary_color'] ) . ';--nb-text:' . esc_attr( $s['text_color'] ) . ';--nb-background:' . esc_attr( $s['background_color'] ) . ';--nb-font-family:' . esc_attr( $font ) . ';--nb-base-font-size:' . esc_attr( $s['base_font_size'] ) . ';--nb-container-width:' . esc_attr( $s['container_width'] ) . ';--nb-section-spacing:' . esc_attr( $s['section_spacing'] ) . ';--nb-button-radius:' . esc_attr( $s['button_radius'] ) . ';--nb-accent:' . esc_attr( $s['accent_color'] ) . ';--nb-surface:' . esc_attr( $s['surface_color'] ) . ';--nb-border:' . esc_attr( $s['border_color'] ) . ';--nb-muted:' . esc_attr( $s['muted_color'] ) . ';--nb-heading-font:' . esc_attr( 'system' === $s['heading_font_family'] ? 'system-ui,sans-serif' : $s['heading_font_family'] ) . ';--nb-font-weight:' . esc_attr( $s['font_weight'] ) . ';--nb-line-height:' . esc_attr( $s['line_height'] ) . ';--nb-space-unit:' . esc_attr( $s['spacing_scale'] ) . ';--nb-shadow:' . esc_attr( 'none' === $s['shadow_style'] ? 'none' : ( 'medium' === $s['shadow_style'] ? '0 6px 18px rgba(15,23,42,.14)' : ( 'strong' === $s['shadow_style'] ? '0 12px 32px rgba(15,23,42,.22)' : '0 2px 8px rgba(15,23,42,.08)' ) ) ) . ';}body{font-family:var(--nb-font-family);font-size:var(--nb-base-font-size);font-weight:var(--nb-font-weight);line-height:var(--nb-line-height);color:var(--nb-text);background-color:var(--nb-background)}h1,h2,h3,h4,h5,h6{font-family:var(--nb-heading-font);}.nb-button{background-color:var(--nb-primary);border-radius:var(--nb-button-radius)}</style>';
 }

 public function print_custom_css() {
  if ( is_admin() ) { return; }
  $css = self::get()['custom_css'];
  if ( '' !== trim( $css ) ) { echo '<style id="nirmaan-builder-custom-css">' . wp_strip_all_tags( $css ) . '</style>'; }
 }

 public function custom_logo( $html, $blog_id ) {
  $id = absint( self::get()['logo_id'] );
  if ( ! $id ) { return $html; }
  $image = wp_get_attachment_image( $id, 'full', false, array( 'class' => 'custom-logo', 'alt' => get_bloginfo( 'name' ) ) );
  return $image ? '<a href="' . esc_url( home_url( '/' ) ) . '" class="custom-logo-link" rel="home" aria-current="page">' . $image . '</a>' : $html;
 }

 public function site_icon( $url, $size, $blog_id ) {
  $id = absint( self::get()['favicon_id'] );
  $custom = $id ? wp_get_attachment_image_url( $id, array( $size, $size ) ) : false;
  return $custom ? $custom : $url;
 }
}
