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

	<form class="standard-form" id="wp-idea-stream-form" action="" method="post">

		<?php do_action( 'wp_idea_stream_signup_custom_field_before' ); ?>

		<?php wp_idea_stream_users_the_signup_fields() ; ?>

		<?php do_action( 'wp_idea_stream_signup_custom_field_after' ); ?>

		<div class="submit">

			<?php wp_idea_stream_users_the_signup_submit() ;?>

		</div>

	</form>

	<?php do_action( 'wp_idea_stream_signup_after_content' ); ?>

</div>
