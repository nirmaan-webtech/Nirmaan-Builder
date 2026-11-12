<?php
/**
 * 404 template.
 *
 * @package NirmaanStarter
 */

get_header();
?>
<main id="primary" class="site-main">
	<div class="nirmaan-container">
		<section class="error-404">
			<h1><?php esc_html_e( 'Page not found', 'nirmaan-starter' ); ?></h1>
			<p><?php esc_html_e( 'The page may have moved or no longer exists.', 'nirmaan-starter' ); ?></p>
			<?php get_search_form(); ?>
			<p><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Return to homepage', 'nirmaan-starter' ); ?></a></p>
		</section>
	</div>
</main>
<?php
get_footer();
