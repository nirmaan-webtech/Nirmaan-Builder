<?php
/**
 * Single post template.
 *
 * @package NirmaanStarter
 */

get_header();
?>
<main id="primary" class="site-main">
	<div class="nirmaan-container">
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<article <?php post_class( 'entry' ); ?> id="post-<?php the_ID(); ?>">
				<header class="entry__header">
					<?php the_title( '<h1 class="entry__title">', '</h1>' ); ?>
					<p class="entry__meta">
						<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
					</p>
				</header>
				<?php if ( has_post_thumbnail() ) : ?>
					<div class="entry__featured-image"><?php the_post_thumbnail( 'large' ); ?></div>
				<?php endif; ?>
				<div class="entry__content">
					<?php the_content(); ?>
					<?php wp_link_pages(); ?>
				</div>
				<footer class="entry__footer"><?php the_category( ', ' ); ?></footer>
			</article>
			<?php
			the_post_navigation();
			if ( comments_open() || get_comments_number() ) {
				comments_template();
			}
		endwhile;
		?>
	</div>
</main>
<?php
get_footer();
