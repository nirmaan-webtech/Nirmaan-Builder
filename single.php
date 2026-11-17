<?php get_header(); ?>
<main id="primary" class="nb-theme-content nb-theme-shell">
	<?php while ( have_posts() ) : the_post(); ?>
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<?php the_title( '<h1 class="nb-theme-entry-title">', '</h1>' ); ?>
			<div class="entry-content"><?php the_content(); ?></div>
			<?php wp_link_pages(); ?>
		</article>
	<?php endwhile; ?>
</main>
<?php get_footer(); ?>
