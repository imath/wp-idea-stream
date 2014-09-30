<?php
/**
 * IdeaStream's Idea footer template
 *
 * @package WP Idea Stream
 * @subpackage templates
 *
 * @since 2.0.0
 */
?>
<div class="idea-footer">
	<?php do_action( 'wp_idea_stream_before_idea_footer' ) ;?>

	<p class="idea-meta"><?php wp_idea_stream_ideas_the_idea_footer(); ?></p>

	<?php do_action( 'wp_idea_stream_after_idea_footer' ) ;?>

	<?php if ( is_single() ) wp_idea_stream_ideas_bottom_navigation() ;?>
</div>
