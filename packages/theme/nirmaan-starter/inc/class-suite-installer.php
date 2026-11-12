<?php
/**
 * Installs and activates the bundled Builder plugin when the theme is activated.
 *
 * @package NirmaanStarter
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Nirmaan_Starter_Suite_Installer' ) ) {
	/**
	 * One-upload suite installer. The theme and plugin remain separate runtime packages.
	 */
	final class Nirmaan_Starter_Suite_Installer {
		/**
		 * Register installer hooks.
		 *
		 * @return void
		 */
		public static function register() {
			add_action( 'after_switch_theme', array( __CLASS__, 'install_builder' ) );
			add_action( 'admin_notices', array( __CLASS__, 'render_notice' ) );
		}

		/**
		 * Copy the bundled plugin into wp-content/plugins and activate it.
		 *
		 * @return void
		 */
		public static function install_builder() {
			if ( ! current_user_can( 'install_plugins' ) || ! current_user_can( 'activate_plugins' ) ) {
				self::set_notice( 'error', __( 'Nirmaan Builder could not be installed automatically. Your account needs permission to install and activate plugins.', 'nirmaan-starter' ) );
				return;
			}

			$plugin_file = 'nirmaan-builder/nirmaan-builder.php';
			$destination = trailingslashit( WP_PLUGIN_DIR ) . 'nirmaan-builder';
			$source      = trailingslashit( get_template_directory() ) . 'bundled-plugins/nirmaan-builder';

			if ( ! file_exists( trailingslashit( WP_PLUGIN_DIR ) . $plugin_file ) ) {
				if ( ! is_dir( $source ) || ! file_exists( trailingslashit( $source ) . 'nirmaan-builder.php' ) ) {
					self::set_notice( 'error', __( 'The bundled Nirmaan Builder plugin was not found in this theme package. Re-upload the complete Nirmaan Suite ZIP.', 'nirmaan-starter' ) );
					return;
				}

				require_once ABSPATH . 'wp-admin/includes/file.php';

				if ( ! WP_Filesystem() ) {
					self::set_notice( 'error', __( 'WordPress could not initialize filesystem access. Check hosting file permissions, then switch to Nirmaan Starter again to retry.', 'nirmaan-starter' ) );
					return;
				}

				$result = copy_dir( $source, $destination );

				if ( is_wp_error( $result ) ) {
					self::set_notice( 'error', sprintf( __( 'Nirmaan Builder could not be copied: %s', 'nirmaan-starter' ), $result->get_error_message() ) );
					return;
				}
			}

			if ( ! function_exists( 'activate_plugin' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			if ( ! function_exists( 'is_plugin_active' ) || ! is_plugin_active( $plugin_file ) ) {
				$activation = activate_plugin( $plugin_file, '', false, true );

				if ( is_wp_error( $activation ) ) {
					self::set_notice( 'error', sprintf( __( 'Nirmaan Builder was copied but could not be activated: %s', 'nirmaan-starter' ), $activation->get_error_message() ) );
					return;
				}
			}

			self::set_notice( 'success', __( 'Nirmaan Suite is ready. The Nirmaan Builder plugin has been installed and activated.', 'nirmaan-starter' ) );
		}

		/**
		 * Store a one-time admin notice.
		 *
		 * @param string $type Notice type.
		 * @param string $message Notice message.
		 * @return void
		 */
		private static function set_notice( $type, $message ) {
			set_transient(
				'nirmaan_starter_suite_notice',
				array(
					'type'    => sanitize_key( $type ),
					'message' => sanitize_text_field( $message ),
				),
				MINUTE_IN_SECONDS * 10
			);
		}

		/**
		 * Display and clear the installer notice.
		 *
		 * @return void
		 */
		public static function render_notice() {
			$notice = get_transient( 'nirmaan_starter_suite_notice' );

			if ( ! is_array( $notice ) || empty( $notice['message'] ) ) {
				return;
			}

			delete_transient( 'nirmaan_starter_suite_notice' );
			$type = ( isset( $notice['type'] ) && 'success' === $notice['type'] ) ? 'success' : 'error';
			?>
			<div class="notice notice-<?php echo esc_attr( $type ); ?> is-dismissible">
				<p><?php echo esc_html( $notice['message'] ); ?></p>
			</div>
			<?php
		}
	}

	Nirmaan_Starter_Suite_Installer::register();
}
