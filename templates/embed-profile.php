<?php
/**
 * Contains the user's profile embed template.
 *
 * @package WP Idea Stream\Templates
 * 
 * @since 2.3.0
 * @since 2.4.0 Use the WordPress Embed header and footer.
 */

get_header( 'embed' ); ?>

<div id="wp-idea-stream" class="wp-embed">
	<?php
	/**
	 * Fires before the Profile content.
	 *
	 * @since 2.4.0
	 */
	do_action( 'wp_idea_stream_embed_before_content' ); ?>

	<div class="profile-header">
		<div class="user-avatar">
			<?php wp_idea_stream_users_embed_user_profile_avatar(); ?>
		</div>

		<div class="user-display-name">
			<p class="wp-embed-heading">
				<a href="<?php wp_idea_stream_users_embed_user_profile_link(); ?>">
					<?php wp_idea_stream_users_embed_user_profile_display_name(); ?>
				</a>
			</p>
		</div>
	</div>

	<?php if ( wp_idea_stream_users_has_embed_description() ) : ?>
		<div class="wp-embed-excerpt">
			<p><?php wp_idea_stream_users_embed_user_profile_description(); ?></p>
		</div>
	<?php endif ; ?>

	<div class="profile-footer">

		<?php wp_idea_stream_users_embed_user_stats() ;?>

		<div class="wp-embed-meta">
			<?php
			/**
			 * Print additional meta content in the embed template.
			 *
			 * @since 2.3.0
			 */
			do_action( 'wp_idea_stream_embed_content_meta' ); ?>
		</div>
	</div>
</div>

<?php get_footer( 'embed' );
