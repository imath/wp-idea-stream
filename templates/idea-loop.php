<?php
/**
 * IdeaStream's Ideas loop template
 *
 * @package WP Idea Stream
 * @subpackage templates
 *
 * @since 2.0.0
 */
?>

<?php if ( wp_idea_stream_ideas_has_ideas( wp_idea_stream_ideas_query_args() ) ) : ?>

	<div id="pag-top" class="pagination no-ajax">

		<div class="pag-count" id="idea-count-top">

			<?php wp_idea_stream_ideas_pagination_count(); ?>

		</div>

		<div class="pagination-links" id="idea-pag-top">

			<?php wp_idea_stream_ideas_pagination_links(); ?>

		</div>

	</div>

	<ul class="idea-list">

	<?php while ( wp_idea_stream_ideas_the_ideas() ) : wp_idea_stream_ideas_the_idea(); ?>

		<li id="idea-<?php wp_idea_stream_ideas_the_id(); ?>" <?php wp_idea_stream_ideas_the_classes(); ?>>
			<?php wp_idea_stream_template_part( 'idea', 'entry' ); ?>
		</li>

	<?php endwhile ; ?>

	</ul>

	<div id="pag-bottom" class="pagination no-ajax">

		<div class="pag-count" id="idea-count-bottom">

			<?php wp_idea_stream_ideas_pagination_count(); ?>

		</div>

		<div class="pagination-links" id="idea-pag-bottom">

			<?php wp_idea_stream_ideas_pagination_links(); ?>

		</div>

	</div>

	<?php wp_idea_stream_maybe_reset_postdata(); ?>

<?php else : ?>

<div class="message info">
	<p><?php wp_idea_stream_ideas_not_found(); ?></p>
</div>

<?php endif ;?>
