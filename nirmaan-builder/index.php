<?php
/**
 * Main template fallback.
 *
 * @package NirmaanStarter
 */

get_header();
?>
<main id="primary" class="site-main">
	<div class="nirmaan-container">
		<?php if ( have_posts() ) : ?>
			<?php while ( have_posts() ) : ?>
				<?php the_post(); ?>
				<article <?php post_class( 'entry' ); ?> id="post-<?php the_ID(); ?>">
					<header class="entry__header">
						<?php the_title( '<h1 class="entry__title">', '</h1>' ); ?>
					</header>
					<div class="entry__content">
						<?php
						the_content();
						wp_link_pages(
							array(
								'before' => '<nav class="page-links" aria-label="' . esc_attr__( 'Page', 'nirmaan-starter' ) . '">',
								'after'  => '</nav>',
							)
						);
						?>
					</div>
				</article>
			<?php endwhile; ?>
			<?php the_posts_navigation(); ?>
		<?php else : ?>
			<section class="no-results">
				<h1><?php esc_html_e( 'Nothing found', 'nirmaan-starter' ); ?></h1>
				<p><?php esc_html_e( 'Try a search or return to the homepage.', 'nirmaan-starter' ); ?></p>
				<?php get_search_form(); ?>
			</section>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
