<?php
/**
 * IdeaStream's Idea header template
 *
 * @package WP Idea Stream
 * @subpackage templates
 *
 * @since 2.0.0
 */
?>
<div class="idea-header">
	<?php wp_idea_stream_ideas_the_idea_comment_link( __( 'Leave a reply', 'wp-idea-stream' ), __( '1 Reply', 'wp-idea-stream' ), __( '% Replies', 'wp-idea-stream' ), 'idea-comment-link icon' );?>
	<?php wp_idea_stream_ideas_the_rating_link( __( 'Rate the idea', 'wp-idea-stream' ), __( 'Average rate: %', 'wp-idea-stream' ), 'idea-rating-link icon' ); ?>

	<?php do_action( 'wp_idea_stream_idea_header' ) ;?>
</div>
