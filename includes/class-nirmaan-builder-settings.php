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
  foreach ( array( 'primary_color', 'secondary_color', 'text_color', 'background_color' ) as $key ) {
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
  $out['custom_css'] = substr( wp_strip_all_tags( (string) ( $input['custom_css'] ?? $out['custom_css'] ) ), 0, 20000 );
  return $out;
 }

 public function register_hooks() {
  add_action( 'wp_head', array( $this, 'print_design_tokens' ), 20 );
  add_action( 'wp_head', array( $this, 'print_custom_css' ), 30 );
  add_filter( 'get_site_icon_url', array( $this, 'site_icon' ), 10, 3 );
 }

 public function print_design_tokens() {
  if ( is_admin() ) { return; }
  $s = self::get();
  $font = 'system' === $s['font_family'] ? '-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif' : $s['font_family'];
  echo '<style id="nirmaan-builder-design-tokens">:root{--nb-primary:' . esc_attr( $s['primary_color'] ) . ';--nb-secondary:' . esc_attr( $s['secondary_color'] ) . ';--nb-text:' . esc_attr( $s['text_color'] ) . ';--nb-background:' . esc_attr( $s['background_color'] ) . ';--nb-font-family:' . esc_attr( $font ) . ';--nb-base-font-size:' . esc_attr( $s['base_font_size'] ) . ';--nb-container-width:' . esc_attr( $s['container_width'] ) . ';--nb-section-spacing:' . esc_attr( $s['section_spacing'] ) . ';--nb-button-radius:' . esc_attr( $s['button_radius'] ) . ';}</style>';
 }

 public function print_custom_css() {
  if ( is_admin() ) { return; }
  $css = self::get()['custom_css'];
  if ( '' !== trim( $css ) ) { echo '<style id="nirmaan-builder-custom-css">' . wp_strip_all_tags( $css ) . '</style>'; }
 }

 public function site_icon( $url, $size, $blog_id ) {
  $id = absint( self::get()['favicon_id'] );
  $custom = $id ? wp_get_attachment_image_url( $id, array( $size, $size ) ) : false;
  return $custom ? $custom : $url;
 }
}
