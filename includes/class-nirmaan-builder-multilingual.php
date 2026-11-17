<?php
namespace NirmaanBuilder;
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Integrations for multilingual plugins.
 *
 * Builder documents remain attached to each translated WordPress post. Template
 * and Library records are translatable so each language can have its own layout.
 */
final class Multilingual {
 public static function register_hooks() {
  add_filter( 'pll_get_post_types', array( __CLASS__, 'polylang_post_types' ), 10, 2 );
  add_filter( 'pll_copy_post_metas', array( __CLASS__, 'polylang_copy_post_metas' ), 10, 4 );
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
   '_nirmaan_builder_document',
   '_nirmaan_builder_enabled',
   '_nirmaan_builder_custom_fields',
   '_nirmaan_template_parts',
   '_nirmaan_conditions',
   '_nirmaan_priority',
   '_nirmaan_part_settings',
   '_nirmaan_global',
   '_nirmaan_category',
   '_nirmaan_library_document',
  );
  return array_values( array_unique( array_merge( $keys, $builder_keys ) ) );
 }
}
