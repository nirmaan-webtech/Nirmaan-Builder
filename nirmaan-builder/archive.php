<?php
/**
 * Archive template.
 *
 * @package NirmaanStarter
 */

get_header();
?>
<main id="primary" class="site-main">
	<div class="nirmaan-container">
		<header class="page-header">
			<?php the_archive_title( '<h1 class="page-title">', '</h1>' ); ?>
			<?php the_archive_description( '<div class="archive-description">', '</div>' ); ?>
		</header>
		<?php if ( have_posts() ) : ?>
			<div class="entry-list">
				<?php
				while ( have_posts() ) :
					the_post();
					?>
					<article <?php post_class( 'entry-card' ); ?> id="post-<?php the_ID(); ?>">
						<?php if ( has_post_thumbnail() ) : ?>
							<a href="<?php the_permalink(); ?>" class="entry-card__image"><?php the_post_thumbnail( 'medium_large' ); ?></a>
						<?php endif; ?>
						<h2 class="entry-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
						<div class="entry-card__excerpt"><?php the_excerpt(); ?></div>
					</article>
					<?php
				endwhile;
				?>
			</div>
			<?php the_posts_pagination(); ?>
		<?php else : ?>
			<p><?php esc_html_e( 'No content found.', 'nirmaan-starter' ); ?></p>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
