<?php
namespace NirmaanBuilder;
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class Roles {
 public static function capabilities() {
  return array(
   'use_nirmaan_builder' => __( 'Access the visual builder', 'nirmaan-builder' ),
   'edit_nirmaan_content' => __( 'Edit builder-enabled content', 'nirmaan-builder' ),
   'use_nirmaan_modules' => __( 'Insert standard builder modules', 'nirmaan-builder' ),
   'use_nirmaan_advanced_modules' => __( 'Insert advanced modules (forms, query loops and WooCommerce)', 'nirmaan-builder' ),
   'edit_nirmaan_design' => __( 'Manage global design presets', 'nirmaan-builder' ),
   'manage_nirmaan_library' => __( 'Manage reusable Library items', 'nirmaan-builder' ),
   'manage_nirmaan_templates' => __( 'Manage Theme Builder templates', 'nirmaan-builder' ),
   'manage_nirmaan_settings' => __( 'Manage global Theme Options', 'nirmaan-builder' ),
   'manage_nirmaan_roles' => __( 'Manage Nirmaan Builder role permissions', 'nirmaan-builder' ),
  );
 }
 public static function register_hooks() {
  add_action( 'init', array( __CLASS__, 'ensure_roles' ), 1 );
 }
 public static function ensure_roles() {
  $all = array_keys( self::capabilities() );
  $admin = get_role( 'administrator' );
  if ( $admin ) { foreach ( $all as $cap ) { $admin->add_cap( $cap ); } }
  if ( ! get_option( 'nirmaan_builder_roles_initialized' ) ) {
   $editor = get_role( 'editor' );
   if ( $editor ) {
    foreach ( array( 'use_nirmaan_builder', 'edit_nirmaan_content', 'use_nirmaan_modules', 'edit_nirmaan_design', 'manage_nirmaan_library' ) as $cap ) { $editor->add_cap( $cap ); }
   }
   add_role( 'nirmaan_builder', __( 'Nirmaan Builder', 'nirmaan-builder' ), array(
    'read' => true, 'edit_posts' => true, 'upload_files' => true,
    'use_nirmaan_builder' => true, 'edit_nirmaan_content' => true,
    'use_nirmaan_modules' => true, 'edit_nirmaan_design' => true,
    'manage_nirmaan_library' => true,
   ) );
   update_option( 'nirmaan_builder_roles_initialized', 1, false );
  } elseif ( ! get_role( 'nirmaan_builder' ) ) {
   add_role( 'nirmaan_builder', __( 'Nirmaan Builder', 'nirmaan-builder' ), array( 'read' => true, 'edit_posts' => true, 'upload_files' => true, 'use_nirmaan_builder' => true, 'edit_nirmaan_content' => true, 'use_nirmaan_modules' => true, 'edit_nirmaan_design' => true, 'manage_nirmaan_library' => true ) );
  }
 }
 public static function render_page() {
  if ( ! current_user_can( 'manage_nirmaan_roles' ) ) { wp_die( esc_html__( 'Permission denied.', 'nirmaan-builder' ) ); }
  if ( 'POST' === ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) {
   check_admin_referer( 'nirmaan_builder_save_roles' );
   $submitted = isset( $_POST['nirmaan_roles'] ) && is_array( $_POST['nirmaan_roles'] ) ? wp_unslash( $_POST['nirmaan_roles'] ) : array();
   foreach ( wp_roles()->roles as $slug => $details ) {
    if ( 'administrator' === $slug ) { continue; }
    $role = get_role( $slug );
    if ( ! $role ) { continue; }
    foreach ( array_keys( self::capabilities() ) as $cap ) {
     if ( in_array( $cap, array( 'manage_nirmaan_roles', 'manage_nirmaan_settings' ), true ) ) { $role->remove_cap( $cap ); continue; }
     if ( ! empty( $submitted[ $slug ][ $cap ] ) ) { $role->add_cap( $cap ); } else { $role->remove_cap( $cap ); }
    }
   }
   echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Role permissions saved.', 'nirmaan-builder' ) . '</p></div>';
  }
  $caps = self::capabilities();
  echo '<div class="wrap"><h1>' . esc_html__( 'Nirmaan Builder — Role Permissions', 'nirmaan-builder' ) . '</h1>';
  echo '<p>' . esc_html__( 'Control which WordPress roles can access builder features. Administrator permissions are fixed and cannot be removed here.', 'nirmaan-builder' ) . '</p>';
  echo '<form method="post">';
  wp_nonce_field( 'nirmaan_builder_save_roles' );
  echo '<table class="widefat striped"><thead><tr><th>' . esc_html__( 'Role', 'nirmaan-builder' ) . '</th>';
  foreach ( $caps as $label ) { echo '<th>' . esc_html( $label ) . '</th>'; }
  echo '</tr></thead><tbody>';
  foreach ( wp_roles()->roles as $slug => $details ) {
   $role = get_role( $slug );
   if ( ! $role ) { continue; }
   echo '<tr><th>' . esc_html( translate_user_role( $details['name'] ) ) . '</th>';
   foreach ( $caps as $cap => $label ) {
    $fixed = 'administrator' === $slug || in_array( $cap, array( 'manage_nirmaan_roles', 'manage_nirmaan_settings' ), true );
    $checked = $role->has_cap( $cap );
    echo '<td><input type="checkbox" name="nirmaan_roles[' . esc_attr( $slug ) . '][' . esc_attr( $cap ) . ']" value="1" ' . checked( $checked, true, false ) . disabled( $fixed, true, false ) . ' aria-label="' . esc_attr( $label . ' — ' . $details['name'] ) . '"></td>';
   }
   echo '</tr>';
  }
  echo '</tbody></table><p><button type="submit" class="button button-primary">' . esc_html__( 'Save role permissions', 'nirmaan-builder' ) . '</button></p></form></div>';
 }
}
