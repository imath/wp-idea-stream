<?php
/**
 * IdeaStream's Idea plugin template
 *
 * @package WP Idea Stream
 * @subpackage templates
 *
 * @since 2.0.0
 */
?>
<div id="wp-idea-stream">

	<?php do_action( 'wp_idea_stream_ideas_before_plugin_content' ); ?>

	<?php wp_idea_stream_user_feedback(); ?>

	<?php do_action( 'wp_idea_stream_ideas_plugin_content' ); ?>

</div>
