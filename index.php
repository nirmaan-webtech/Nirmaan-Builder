<?php get_header(); ?>
<main id="primary" class="nb-theme-content nb-theme-shell">
	<?php if ( have_posts() ) : ?>
		<?php while ( have_posts() ) : the_post(); ?>
			<article id="post-<?php the_ID(); ?>" <?php post_class( 'nb-theme-post' ); ?>>
				<?php if ( is_singular() ) : ?>
					<?php the_title( '<h1 class="nb-theme-entry-title">', '</h1>' ); ?>
				<?php else : ?>
					<?php the_title( '<h2 class="nb-theme-entry-title"><a href="' . esc_url( get_permalink() ) . '">', '</a></h2>' ); ?>
				<?php endif; ?>
				<div class="entry-content"><?php the_content(); ?></div>
				<?php wp_link_pages(); ?>
			</article>
		<?php endwhile; ?>
		<?php the_posts_pagination( array( 'class' => 'nb-theme-pagination' ) ); ?>
	<?php else : ?>
		<h1><?php esc_html_e( 'Nothing Found', 'nirmaan-builder' ); ?></h1>
		<p><?php esc_html_e( 'No content is available here yet.', 'nirmaan-builder' ); ?></p>
	<?php endif; ?>
</main>
<?php get_footer(); ?>
