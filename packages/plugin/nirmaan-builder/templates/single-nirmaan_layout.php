<?php
/**
 * Frontend template for published Builder layouts.
 *
 * @package NirmaanBuilder
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="primary" class="site-main nirmaan-layout-main">
	<div class="nirmaan-container">
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<article <?php post_class( 'nirmaan-layout' ); ?> id="post-<?php the_ID(); ?>">
				<?php if ( get_the_title() ) : ?>
					<header class="entry__header">
						<?php the_title( '<h1 class="entry__title">', '</h1>' ); ?>
					</header>
				<?php endif; ?>
				<div class="entry__content">
					<?php echo \NirmaanBuilder\Renderer::render( get_the_ID() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Renderer escapes all supported module output. ?>
				</div>
			</article>
			<?php
		endwhile;
		?>
	</div>
</main>
<?php
get_footer();
