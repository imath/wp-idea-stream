<?php
/**
 * IdeaStream's Idea entry template
 *
 * @package WP Idea Stream
 * @subpackage templates
 *
 * @since 2.0.0
 */
?>
<div class="idea-avatar">
	<?php wp_idea_stream_ideas_the_author_avatar(); ?>
</div>
<div class="idea-content">

	<?php do_action( 'wp_idea_stream_idea_entry_before_title' ); ?>

	<div class="idea-title">
		<?php wp_idea_stream_ideas_before_idea_title(); ?><a href="<?php wp_idea_stream_ideas_the_permalink();?>" title="<?php wp_idea_stream_ideas_the_title_attribute(); ?>"><?php wp_idea_stream_ideas_the_title(); ?></a>
	</div>

	<?php do_action( 'wp_idea_stream_idea_entry_before_header' ); ?>

	<?php wp_idea_stream_template_part( 'idea', 'header' ); ?>

	<div class="idea-excerpt">
		<?php wp_idea_stream_ideas_the_excerpt(); ?>
	</div>

	<?php do_action( 'wp_idea_stream_idea_entry_before_footer' ); ?>

	<?php wp_idea_stream_template_part( 'idea', 'footer' ); ?>

	<?php do_action( 'wp_idea_stream_idea_entry_after_footer' ); ?>
</div>
