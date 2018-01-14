<?php
/**
 * The template for displaying all single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package WordPress
 * @subpackage Twenty_Seventeen
 * @since 1.0
 * @version 1.0
 */

get_header(); ?>

	<div class="wrap">
		<div id="primary" class="content-area">
			<main id="main" class="site-main" role="main">

				<?php
				/* Start the Loop */
				while ( have_posts() ) : the_post(); ?>

					<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
						<?php if ( is_sticky() && is_home() ) :
							echo twentyseventeen_get_svg( array( 'icon' => 'thumb-tack' ) );
						endif; ?>

						<header class="entry-header">
							<?php the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' ); ?>
						</header><!-- .entry-header -->

						<?php if ( '' !== get_the_post_thumbnail() && ! is_single() ) : ?>
							<div class="post-thumbnail">
								<a href="<?php the_permalink(); ?>">
									<?php the_post_thumbnail( 'twentyseventeen-featured-image' ); ?>
								</a>
							</div><!-- .post-thumbnail -->
						<?php endif;

						$questions = get_the_content();
						if ( ! empty( $questions ) ) :
							$questions_q = new WP_Query( array( 'post_type' => 'question', 'post__in' => array_map( 'absint', explode( ',', $questions ) ) ) );
						else :
							$questions_q = new WP_Query();
						endif;

						if ( ! $questions_q->have_posts() ) :
							echo 'No questions yet !';
						else :
							while( $questions_q->have_posts() ) : $questions_q->the_post(); ?>
								<hr>
								<div class="entry-content">
									<?php printf( '<h3>%d - %s</h3>', $questions_q->current_post + 1, get_the_title() );
									the_content(); ?>
								</div>
								<hr>
							<?php endwhile;
							$questions_q->reset_postdata();
						endif; ?>

					</article><!-- #post-## -->

				<?php endwhile; ?>

			</main><!-- #main -->
		</div><!-- #primary -->
		<?php get_sidebar(); ?>
	</div><!-- .wrap -->

<?php get_footer();
