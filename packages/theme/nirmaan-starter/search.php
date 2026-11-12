<?php
/**
 * Search results template.
 *
 * @package NirmaanStarter
 */

get_header();
?>
<main id="primary" class="site-main">
	<div class="nirmaan-container">
		<header class="page-header">
			<h1 class="page-title">
				<?php
				printf(
					/* translators: %s: search query. */
					esc_html__( 'Search results for: %s', 'nirmaan-starter' ),
					'<span>' . esc_html( get_search_query() ) . '</span>'
				);
				?>
			</h1>
			<?php get_search_form(); ?>
		</header>
		<?php if ( have_posts() ) : ?>
			<div class="entry-list">
				<?php
				while ( have_posts() ) :
					the_post();
					?>
					<article <?php post_class( 'entry-card' ); ?> id="post-<?php the_ID(); ?>">
						<h2 class="entry-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
						<div class="entry-card__excerpt"><?php the_excerpt(); ?></div>
					</article>
					<?php
				endwhile;
				?>
			</div>
			<?php the_posts_pagination(); ?>
		<?php else : ?>
			<p><?php esc_html_e( 'No results matched your search.', 'nirmaan-starter' ); ?></p>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
