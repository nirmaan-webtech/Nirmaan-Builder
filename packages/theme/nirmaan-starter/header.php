<?php
/**
 * Site header.
 *
 * @package NirmaanStarter
 */
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e( 'Skip to content', 'nirmaan-starter' ); ?></a>
<?php if ( apply_filters( 'nirmaan_starter_use_builder_header', false ) ) : ?>
	<header class="site-header site-header--builder">
		<div class="nirmaan-container site-header__inner">
			<?php do_action( 'nirmaan_starter_render_builder_header' ); ?>
		</div>
	</header>
<?php else : ?>
<header class="site-header">
	<div class="nirmaan-container site-header__inner">
		<div class="site-branding">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
				<a class="site-title" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
					<?php bloginfo( 'name' ); ?>
				</a>
				<?php $description = get_bloginfo( 'description', 'display' ); ?>
				<?php if ( $description || is_customize_preview() ) : ?>
					<p class="site-description"><?php echo esc_html( $description ); ?></p>
				<?php endif; ?>
			<?php endif; ?>
		</div>
		<nav class="primary-navigation" aria-label="<?php esc_attr_e( 'Primary navigation', 'nirmaan-starter' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'fallback_cb'    => false,
				)
			);
			?>
		</nav>
	</div>
</header>
<?php endif; ?>
