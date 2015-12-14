<?php
/**
 * IdeaStream's User profile template
 *
 * @package WP Idea Stream
 * @subpackage templates
 *
 * @since 2.0.0
 */
?>

<div id="wp-idea-stream">

	<?php wp_idea_stream_user_feedback(); ?>

	<?php do_action( 'wp_idea_stream_user_profile_before_header' ); ?>

	<div class="profile-header">

		<?php do_action( 'wp_idea_stream_user_profile_before_avatar' ); ?>

		<div class="user-avatar">

			<?php wp_idea_stream_users_the_user_profile_avatar(); ?>

		</div>

		<?php do_action( 'wp_idea_stream_user_profile_after_avatar' ); ?>

		<?php wp_idea_stream_users_the_user_profile_description(); ?>

		<div class="clear"></div>

		<?php do_action( 'wp_idea_stream_user_profile_after_description' ); ?>

		<div class="clear"></div>

	</div>

	<?php do_action( 'wp_idea_stream_user_profile_before_nav' ); ?>

	<?php wp_idea_stream_users_the_user_nav(); ?>

	<?php do_action( 'wp_idea_stream_user_profile_after_nav' ); ?>

	<?php if ( wp_idea_stream_is_user_profile_comments() ) : ?>

		<?php wp_idea_stream_template_part( 'user', 'comments' ); ?>

		<?php do_action( 'wp_idea_stream_user_profile_after_comments' ); ?>

	<?php else : ?>

		<?php wp_idea_stream_template_part( 'idea', 'loop' ); ?>

		<?php do_action( 'wp_idea_stream_user_profile_after_loop' ); ?>

	<?php endif; ?>

</div>
