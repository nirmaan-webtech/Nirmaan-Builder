<?php get_header(); ?>
<main id="primary" class="nb-theme-content nb-theme-shell">
	<header class="page-header"><h1 class="nb-theme-entry-title"><?php the_archive_title(); ?></h1><?php the_archive_description(); ?></header>
	<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
		<article id="post-<?php the_ID(); ?>" <?php post_class( 'nb-theme-post' ); ?>>
			<?php the_title( '<h2><a href="' . esc_url( get_permalink() ) . '">', '</a></h2>' ); ?>
			<div class="entry-summary"><?php the_excerpt(); ?></div>
		</article>
	<?php endwhile; the_posts_pagination(); else : ?>
		<p><?php esc_html_e( 'No content found.', 'nirmaan-builder' ); ?></p>
	<?php endif; ?>
</main>
<?php get_footer(); ?>
