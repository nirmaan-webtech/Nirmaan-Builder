<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<?php if ( ! nirmaan_builder_has_active_part( 'header' ) ) : ?>
<header class="nb-theme-site-header">
	<div class="nb-theme-shell nb-theme-header-inner">
		<a class="nb-theme-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
			<?php if ( has_custom_logo() ) { the_custom_logo(); } else { bloginfo( 'name' ); } ?>
		</a>
		<?php if ( has_nav_menu( 'primary' ) ) : ?>
			<nav class="nb-theme-nav" aria-label="<?php esc_attr_e( 'Primary menu', 'nirmaan-builder' ); ?>">
				<?php wp_nav_menu( array( 'theme_location' => 'primary', 'container' => false, 'fallback_cb' => false ) ); ?>
			</nav>
		<?php endif; ?>
	</div>
</header>
<?php endif; ?>
