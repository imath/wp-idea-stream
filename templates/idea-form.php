<?php
/**
 * IdeaStream's Idea form template
 *
 * @package WP Idea Stream
 * @subpackage templates
 *
 * @since 2.0.0
 */
?>
<div id="wp-idea-stream">

	<?php do_action( 'wp_idea_stream_ideas_before_form' ); ?>

	<?php wp_idea_stream_user_feedback(); ?>

	<?php if ( wp_idea_stream_user_can( 'publish_ideas' ) ) : ?>

		<form class="standard-form" id="wp-idea-stream-form" action="" method="post">

			<?php wp_idea_stream_ideas_the_title_edit(); ?>

			<?php wp_idea_stream_ideas_the_editor(); ?>

			<div class="category-list">

				<?php wp_idea_stream_ideas_the_category_edit(); ?>

			</div>

			<div class="tag-list">

				<?php wp_idea_stream_ideas_the_tags_edit() ;?>

			</div>

			<?php do_action( 'wp_idea_stream_ideas_the_idea_meta_edit' ); ?>

			<div class="submit">

				<?php wp_idea_stream_ideas_the_form_submit() ;?>

			</div>

		</form>

	<?php else: ?>

		<div class="message info">
			<p><?php wp_idea_stream_ideas_not_loggedin(); ?></p>
		</div>

	<?php endif; ?>

	<?php do_action( 'wp_idea_stream_ideas_after_form' ); ?>

</div>
