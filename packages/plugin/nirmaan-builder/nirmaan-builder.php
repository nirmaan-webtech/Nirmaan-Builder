<?php
/**
 * Plugin Name: Nirmaan Builder
 * Plugin URI:  https://github.com/nirmaan-webtech/Nirmaan-Builder
 * Description: Independent foundation for the Nirmaan visual page builder.
 * Version:     0.1.0
 * Requires at least: 6.5
 * Requires PHP: 8.2
 * Author:      Nirmaan WebTech
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: nirmaan-builder
 *
 * @package NirmaanBuilder
 */

defined( 'ABSPATH' ) || exit;

define( 'NIRMAAN_BUILDER_VERSION', '0.1.0' );
define( 'NIRMAAN_BUILDER_FILE', __FILE__ );
define( 'NIRMAAN_BUILDER_DIR', plugin_dir_path( __FILE__ ) );
define( 'NIRMAAN_BUILDER_URL', plugin_dir_url( __FILE__ ) );

require_once NIRMAAN_BUILDER_DIR . 'includes/class-nirmaan-builder.php';

/**
 * Boot the plugin after all plugins have loaded.
 *
 * @return void
 */
function nirmaan_builder_boot() {
	\NirmaanBuilder\Plugin::instance()->init();
}
add_action( 'plugins_loaded', 'nirmaan_builder_boot' );
