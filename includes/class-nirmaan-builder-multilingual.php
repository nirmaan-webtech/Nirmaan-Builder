<?php
namespace NirmaanBuilder;
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Multilingual integrations for builder documents and private layout records.
 * Builder structure is copied to translated posts; user-facing strings are
 * registered separately so translation plugins never need to rewrite JSON.
 */
final class Multilingual {
 const CONTEXT = 'Nirmaan Builder';
 private static $translatable_keys = array( 'content', 'alt', 'form_title', 'submit_label', 'success_message', 'label', 'options' );

 public static function register_hooks() {
  add_filter( 'pll_get_post_types', array( __CLASS__, 'polylang_post_types' ), 10, 2 );
  add_filter( 'pll_copy_post_metas', array( __CLASS__, 'polylang_copy_post_metas' ), 10, 4 );
  add_action( 'added_post_meta', array( __CLASS__, 'register_meta_strings' ), 10, 4 );
  add_action( 'updated_post_meta', array( __CLASS__, 'register_meta_strings' ), 10, 4 );
 }
 public static function polylang_post_types( $post_types, $is_settings = false ) {
  if ( ! is_array( $post_types ) ) { $post_types = array(); }
  $post_types['nirmaan_template'] = 'nirmaan_template';
  $post_types['nirmaan_library'] = 'nirmaan_library';
  return $post_types;
 }
 public static function polylang_copy_post_metas( $keys, $sync = false, $from = 0, $to = 0 ) {
  if ( ! is_array( $keys ) ) { $keys = array(); }
  $builder_keys = array(
   '_nirmaan_builder_document', '_nirmaan_builder_enabled', '_nirmaan_builder_custom_fields',
   '_nirmaan_template_parts', '_nirmaan_conditions', '_nirmaan_priority',
   '_nirmaan_part_settings', '_nirmaan_global', '_nirmaan_category', '_nirmaan_library_document',
  );
  return array_values( array_unique( array_merge( $keys, $builder_keys ) ) );
 }
 public static function register_meta_strings( $meta_id, $post_id, $meta_key, $meta_value ) {
  if ( ! in_array( $meta_key, array( '_nirmaan_builder_document', '_nirmaan_template_parts', '_nirmaan_library_document' ), true ) ) { return; }
  $strings = array();
  self::collect_strings( maybe_unserialize( $meta_value ), (string) $meta_key, 'post-' . absint( $post_id ), $strings );
  foreach ( $strings as $name => $value ) {
   if ( function_exists( 'pll_register_string' ) ) { pll_register_string( $name, $value, self::CONTEXT, false ); }
   do_action( 'wpml_register_single_string', self::CONTEXT, $name, $value );
  }
 }
 private static function collect_strings( $value, $path, $prefix, &$strings ) {
  if ( ! is_array( $value ) ) { return; }
  foreach ( $value as $key => $child ) {
   $child_path = $path . '/' . (string) $key;
   if ( is_array( $child ) ) { self::collect_strings( $child, $child_path, $prefix, $strings ); continue; }
   $is_option_value = preg_match( '#/options$#', $path ) && is_numeric( (string) $key );
   if ( ( ! in_array( (string) $key, self::$translatable_keys, true ) && ! $is_option_value ) || ! is_scalar( $child ) ) { continue; }
   $text = (string) $child;
   if ( '' === trim( $text ) ) { continue; }
   $strings[ $prefix . '-' . md5( $child_path ) ] = $text;
  }
 }
 public static function translate_document( $document, $post_id, $meta_key = '_nirmaan_builder_document' ) {
  if ( ! is_array( $document ) ) { return $document; }
  return self::translate_array( $document, 'post-' . absint( $post_id ), (string) $meta_key );
 }
 private static function translate_array( $value, $prefix, $path ) {
  foreach ( $value as $key => $child ) {
   $child_path = $path . '/' . (string) $key;
   if ( is_array( $child ) ) { $value[ $key ] = self::translate_array( $child, $prefix, $child_path ); continue; }
   $is_option_value = preg_match( '#/options$#', $path ) && is_numeric( (string) $key );
   if ( ( ! in_array( (string) $key, self::$translatable_keys, true ) && ! $is_option_value ) || ! is_scalar( $child ) || '' === trim( (string) $child ) ) { continue; }
   $name = $prefix . '-' . md5( $child_path );
   $translated = apply_filters( 'wpml_translate_single_string', (string) $child, self::CONTEXT, $name );
   if ( function_exists( 'pll__' ) ) { $translated = pll__( (string) $translated ); }
   $value[ $key ] = $translated;
  }
  return $value;
 }
}
