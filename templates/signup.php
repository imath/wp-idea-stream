<?php
/**
 * IdeaStream's signup template
 *
 * @package WP Idea Stream
 * @subpackage templates
 *
 * @since 2.1.0
 */
?>
<div id="wp-idea-stream">

	<?php do_action( 'wp_idea_stream_signup_before_content' ); ?>

	<?php wp_idea_stream_user_feedback(); ?>

	<p>Sinup form</p>

	<?php do_action( 'wp_idea_stream_signup_after_content' ); ?>

</div>