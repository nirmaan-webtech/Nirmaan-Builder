<?php
/**
 * Plugin Name: Nirmaan Builder
 * Plugin URI: https://github.com/nirmaan-webtech/Nirmaan-Builder
 * Description: An independent visual theme and page builder for WordPress.
 * Version: 0.2.0
 * Requires at least: 6.4
 * Requires PHP: 8.0
 * Author: Nirmaan WebTech
 * Author URI: https://github.com/nirmaan-webtech
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: nirmaan-builder
 * Domain Path: /languages
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
define( 'NIRMAAN_BUILDER_VERSION', '0.2.0' );
define( 'NIRMAAN_BUILDER_FILE', __FILE__ );
define( 'NIRMAAN_BUILDER_PATH', plugin_dir_path( __FILE__ ) );
define( 'NIRMAAN_BUILDER_URL', plugin_dir_url( __FILE__ ) );
require_once NIRMAAN_BUILDER_PATH . 'includes/class-nirmaan-builder.php';
function nirmaan_builder_bootstrap() { \NirmaanBuilder\Plugin::instance()->boot(); }
add_action( 'plugins_loaded', 'nirmaan_builder_bootstrap' );
function nirmaan_builder_activate() { \NirmaanBuilder\Plugin::instance()->register_content_types(); flush_rewrite_rules(); }
register_activation_hook( __FILE__, 'nirmaan_builder_activate' );
function nirmaan_builder_deactivate() { flush_rewrite_rules(); }
register_deactivation_hook( __FILE__, 'nirmaan_builder_deactivate' );
