<?php
/**
 * IdeaStream's Single idea template (for BuddyPress groups)
 *
 * @package WP Idea Stream
 * @subpackage templates
 *
 * @since 2.0.0
 */
?>
<div id="wp-idea-stream">

	<?php wp_idea_stream_user_feedback(); ?>

	<?php if ( wp_idea_stream_ideas_has_ideas( wp_idea_stream_ideas_query_args( 'single' ) ) ) : ?>

		<div class="idea-content">

			<?php while ( wp_idea_stream_ideas_the_ideas() ) : wp_idea_stream_ideas_the_idea(); ?>

				<?php do_action( 'wp_idea_stream_idea_entry_before_title' ); ?>

				<article id="idea-<?php wp_idea_stream_ideas_the_id(); ?>" <?php wp_idea_stream_ideas_the_classes(); ?>>

					<h1 class="idea-title">
						<?php wp_idea_stream_ideas_before_idea_title(); ?><?php wp_idea_stream_ideas_the_title(); ?>
					</h1>


					<?php do_action( 'wp_idea_stream_idea_entry_before_header' ); ?>

					<?php wp_idea_stream_template_part( 'idea', 'header' ); ?>

					<div class="idea-description">
						<?php wp_idea_stream_ideas_the_content(); ?>
					</div>

					<?php do_action( 'wp_idea_stream_idea_entry_before_footer' ); ?>

					<?php wp_idea_stream_template_part( 'idea', 'footer' ); ?>

					<?php do_action( 'wp_idea_stream_idea_entry_after_footer' ); ?>

				</article>

				<?php comments_template( '', true ); ?>

			<?php endwhile ; ?>

		</div>

		<?php wp_idea_stream_maybe_reset_postdata(); ?>

	<?php else : ?>

		<div class="message info">
			<p><?php wp_idea_stream_ideas_not_found(); ?></p>
		</div>

	<?php endif ;?>

</div>
