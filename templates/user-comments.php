<?php
/**
 * IdeaStream's User comments template
 *
 * @package WP Idea Stream
 * @subpackage templates
 *
 * @since 2.0.0
 */
?>
<?php if ( wp_idea_stream_comments_has_comments( wp_idea_stream_comments_query_args() ) ) : ?>

	<div id="pag-top" class="pagination no-ajax">

		<div class="pag-count" id="idea-count-top">

			<?php wp_idea_stream_comments_pagination_count(); ?>

		</div>

		<div class="pagination-links" id="idea-pag-top">

			<?php wp_idea_stream_comments_pagination_links(); ?>

		</div>

	</div>

	<ul class="idea-comments-list">

	<?php while ( wp_idea_stream_comments_the_comments() ) : wp_idea_stream_comments_the_comment() ; ?>

		<li id="idea-comment-<?php wp_idea_stream_comments_the_comment_id(); ?>">
			<div class="idea-comment-avatar">
				<?php wp_idea_stream_comments_the_comment_author_avatar(); ?>
			</div>
			<div class="idea-comment-content">

				<?php do_action( 'wp_idea_stream_idea_comment_before_title' ); ?>

				<div class="idea-comment-title">
					<?php wp_idea_stream_comments_before_comment_title(); ?> <a href="<?php wp_idea_stream_comments_the_comment_permalink();?>" title="<?php wp_idea_stream_comments_the_comment_title_attribute(); ?>"><?php wp_idea_stream_comments_the_comment_title(); ?></a>
				</div>

				<?php do_action( 'wp_idea_stream_idea_comment_before_excerpt' ); ?>

				<div class="idea-comment-excerpt">
					<?php wp_idea_stream_comments_the_comment_excerpt(); ?>
				</div>

				<?php do_action( 'wp_idea_stream_idea_comment_before_footer' ); ?>

				<div class="idea-comment-footer">
					<p class="idea-comment-meta"><?php wp_idea_stream_comments_the_comment_footer(); ?></p>

					<?php do_action( 'wp_idea_stream_comment_footer' ) ;?>
				</div>
			</div>
		</li>

	<?php endwhile ; ?>

	</ul>

	<div id="pag-bottom" class="pagination no-ajax">

		<div class="pag-count" id="idea-count-bottom">

			<?php wp_idea_stream_comments_pagination_count(); ?>

		</div>

		<div class="pagination-links" id="idea-pag-bottom">

			<?php wp_idea_stream_comments_pagination_links(); ?>

		</div>

	</div>

	<?php wp_idea_stream_maybe_reset_postdata() ;?>

<?php else : ?>

<div class="message info">
	<p><?php wp_idea_stream_comments_no_comment_found() ;?></p>
</div>

<?php endif ;?>
