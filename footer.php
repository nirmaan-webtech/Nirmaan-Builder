<?php if ( ! nirmaan_builder_has_active_part( 'footer' ) ) : ?>
<footer class="nb-theme-site-footer">
	<div class="nb-theme-shell">
		<?php if ( has_nav_menu( 'footer' ) ) : ?>
			<nav class="nb-theme-nav" aria-label="<?php esc_attr_e( 'Footer menu', 'nirmaan-builder' ); ?>">
				<?php wp_nav_menu( array( 'theme_location' => 'footer', 'container' => false, 'fallback_cb' => false ) ); ?>
			</nav>
		<?php endif; ?>
		<p><?php echo esc_html( sprintf( __( '© %1$s %2$s. All rights reserved.', 'nirmaan-builder' ), wp_date( 'Y' ), get_bloginfo( 'name' ) ) ); ?></p>
	</div>
</footer>
<?php endif; ?>
<?php wp_footer(); ?>
</body>
</html>
