<?php
/**
 * Theme Builder header template.
 *
 * @package NirmaanBuilder
 */

defined( 'ABSPATH' ) || exit;
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
<a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e( 'Skip to content', 'nirmaan-builder' ); ?></a>
<header class="nirmaan-theme-builder-header">
	<?php
	$template_id = \NirmaanBuilder\Template_Resolver::find( 'header' );
	if ( $template_id ) {
		echo \NirmaanBuilder\Renderer::render( $template_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Renderer escapes supported module output.
	}
	?>
</header>
