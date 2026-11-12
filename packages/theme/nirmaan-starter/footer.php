<?php
/**
 * Site footer.
 *
 * @package NirmaanStarter
 */
?>
<footer class="site-footer">
	<div class="nirmaan-container site-footer__inner">
		<?php if ( apply_filters( 'nirmaan_starter_use_builder_footer', false ) ) : ?>
			<?php do_action( 'nirmaan_starter_render_builder_footer' ); ?>
		<?php else : ?>

		<nav class="footer-navigation" aria-label="<?php esc_attr_e( 'Footer navigation', 'nirmaan-starter' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'footer',
					'container'      => false,
					'fallback_cb'    => false,
				)
			);
			?>
		</nav>
		<p class="site-footer__copyright">
			<?php
			printf(
				/* translators: %s: current year. */
				esc_html__( '© %s %s. All rights reserved.', 'nirmaan-starter' ),
				esc_html( wp_date( 'Y' ) ),
				esc_html( get_bloginfo( 'name' ) )
			);
			?>
		</p>
	</div>
 
		<?php endif; ?>
	</div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
