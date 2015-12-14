<?php
/**
 * Contains the user's profile embed template.
 *
 * @package WP Idea Stream
 * @subpackage Templates
 * @since 2.3.0
 */

if ( ! headers_sent() ) {
	header( 'X-WP-embed: true' );
}

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js">
<head>
	<title><?php echo wp_get_document_title(); ?></title>
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<?php
	/**
	 * Print scripts or data in the embed template <head> tag.
	 *
	 * @since 2.3.0
	 */
	do_action( 'embed_head' );
	?>
</head>
<body <?php body_class(); ?>>
	<div id="wp-idea-stream" class="wp-embed">
		<div id="buddypress-cover-image"></div>
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
				do_action( 'wp_idea_stream_embed_content_meta' );
				?>
			</div>
		</div>
	</div>

<?php
/**
 * Print scripts or data before the closing body tag in the embed template.
 *
 * @since 2.3.0
 */
do_action( 'embed_footer' ); ?>
</body>
</html>
