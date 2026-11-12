<?php
/**
 * Page template.
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
				</header>
				<div class="entry__content">
					<?php
					the_content();
					wp_link_pages();
					?>
				</div>
			</article>
			<?php
			if ( comments_open() || get_comments_number() ) {
				comments_template();
			}
		endwhile;
		?>
	</div>
</main>
<?php
get_footer();
