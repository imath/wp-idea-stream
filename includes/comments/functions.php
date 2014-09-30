<?php
/**
 * WP Idea Stream Comments functions.
 *
 * functions specific to Comments
 *
 * @package WP Idea Stream
 * @subpackage comments/functions
 *
 * @since 2.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/** Set/Get comment(s) ********************************************************/

/**
 * Builds the idea comments object
 *
 * Adds usefull datas to check if the comments is about an idea
 * - post type
 * - post author
 * - post title
 *
 * @package WP Idea Stream
 * @subpackage comments/functions
 *
 * @since 2.0.0
 *
 * @param  int  $comment_id the comment ID
 * @uses   get_comment() to get the comment object
 * @uses   get_post() to get the idea the comment is linked to
 * @uses   get_comments() to get the comments
 * @uses   wp_idea_stream_get_post_type() to get the idea post type identifier
 * @uses   apply_filters() call 'wp_idea_stream_comments_get_comment' to override the comment object
 * @return array        the list of comments matching arguments
 */
function wp_idea_stream_comments_get_comment( $comment_id = 0 ) {
	// Bail if comment id is not set
	if ( empty( $comment_id ) ) {
		return false;
	}

	$comment = get_comment( $comment_id );

	// Make sur the comment exist
	if ( empty( $comment ) ) {
		return false;
	}

	// Get and append post type if an idea one
	$post = get_post( $comment->comment_post_ID );

	if ( wp_idea_stream_get_post_type() == $post->post_type ) {
		$comment->comment_post_type   = $post->post_type;
		$comment->comment_post_author = $post->post_author;
		$comment->comment_post_title  = $post->post_title;
	}

	/**
	 * @param  object  $comment the comment object
	 * @param  WP_Post $post    the post the comment is linked to
	 */
	return apply_filters( 'wp_idea_stream_comments_get_comment', $comment, $post );
}

/**
 * Gets comments matching arguments
 *
 * @package WP Idea Stream
 * @subpackage comments/functions
 *
 * @since 2.0.0
 *
 * @param  array  $args the arguments of the comments query
 * @uses   wp_parse_args() to merge custom args with default ones
 * @uses   wp_idea_stream_get_post_type() to get the idea post type identifier
 * @uses   get_comments() to get the comments
 * @return array        the list of comments matching arguments
 */
function wp_idea_stream_comments_get_comments( $args = array() ) {
	$comments_args = wp_parse_args( $args, array(
		'post_type'   => wp_idea_stream_get_post_type(),
		'post_status' => 'publish',
		'status'      => 'approve',
		'number'      => false,
		'offset'      => false,
		'fields'      => false,
		'post_id'     => 0
	) );

	return get_comments( $comments_args );
}

/**
 * Clean the idea's comment count cache
 *
 * @package WP Idea Stream
 * @subpackage comments/functions
 *
 * @since 2.0.0
 *
 * @param  int     $comment_id the comment ID
 * @param  string  $status     its status
 * @uses   wp_idea_stream_comments_get_comment() to get the comment object
 * @uses   wp_idea_stream_get_post_type() to get the idea post type identifier
 * @uses   wp_idea_stream_is_comments_disjoined() to check comments about ideas need to be separated from others
 * @uses   wp_cache_delete() to clean the cached count
 */
function wp_idea_stream_comments_clean_count_cache( $comment_id = 0, $status = '' ) {
	// Bail if no comment id or the status is delete
	if ( empty( $comment_id ) || ( ! empty( $status ) && 'delete' == $status ) ) {
		return;
	}

	$comment = wp_idea_stream_comments_get_comment( $comment_id );

	// Make sure the comment has been made on an idea post type
	if ( empty( $comment->comment_post_type ) || wp_idea_stream_get_post_type() != $comment->comment_post_type ) {
		return;
	}

	// Clean global idea comment count cache if needed.
	if ( wp_idea_stream_is_comments_disjoined() ) {
		wp_cache_delete( "idea_comment_count_0", 'wp_idea_stream' );
	}

	// Clean user count cache
	if ( ! empty( $comment->user_id ) ) {
		wp_cache_delete( "idea_comment_count_{$comment->user_id}", 'wp_idea_stream' );
	}
}

/**
 * Retrieve total comments about ideas for blog or user.
 *
 * @package WP Idea Stream
 * @subpackage comments/functions
 *
 * @since 2.0.0
 *
 * @param int $user_id Optional. User ID.
 * @uses  wp_cache_get() to get the cached value
 * @uses  WP_Idea_Stream_Comments::count_user_comments() to get the user's number of comments
 * @uses  WP_Idea_Stream_Comments::count_ideas_comments() to build stats on all comments about ideas
 * @uses  wp_cache_set() to set the cached value
 * @return object Comment stats.
 */
function wp_idea_stream_comments_count_comments( $user_id = 0 ) {

	$user_id = (int) $user_id;

	$count = wp_cache_get( "idea_comment_count_{$user_id}", 'wp_idea_stream' );

	if ( false !== $count ) {
		return $count;
	}

	// Counting for one user
	if ( ! empty( $user_id ) ) {
		$stats = WP_Idea_Stream_Comments::count_user_comments( $user_id );

	// Counting for comments on ideas
	} else {
		$stats = WP_Idea_Stream_Comments::count_ideas_comments();
	}

	wp_cache_set( "idea_comment_count_{$user_id}", $stats, 'wp_idea_stream' );

	return $stats;
}

/** Comments urls *************************************************************/

/**
 * Builds the idea's comment permalink
 *
 * @package WP Idea Stream
 * @subpackage comments/functions
 *
 * @since 2.0.0
 *
 * @param  integer $comment_id the comment ID
 * @uses   get_comment() to get the comment object
 * @uses   get_comment_link() to build the link to the comment
 * @uses   apply_filters() call 'wp_idea_stream_comments_get_comment_link' to override the url
 * @return string              the comment link
 */
function wp_idea_stream_comments_get_comment_link( $comment_id = 0 ) {
	if ( empty( $comment_id ) ) {
		return false;
	}

	$comment = get_comment( $comment_id );

	/**
	 * Check if the Idea still exists.
	 * Avoid notices when deleting a user/post if BuddyPress Activity & Blogs are active
	 */
	if ( empty( $comment->comment_post_ID ) ) {
		$comment_link = false;
	} else {
		$comment_link = get_comment_link( $comment );
	}

	/**
	 * @param  string $comment_link the comment permalink
	 * @param  int    $comment_id   the comment ID
	 */
	return apply_filters( 'wp_idea_stream_comments_get_comment_link', $comment_link, $comment_id );
}

/**
 * Make sure the comment edit link about an ideas post type will
 * open the Ideastream Comments Submenu once cliked on.
 *
 * @package WP Idea Stream
 * @subpackage comments/functions
 *
 * @since 2.0.0
 *
 * @param  string $location the comment edit link
 * @uses   wp_idea_stream_comments_get_comment() to get the Idea comment object
 * @uses   wp_idea_stream_get_post_type() to get the ideas post type identifier
 * @uses   add_query_arg() to add the post type query arg to the comment edit link
 * @uses   apply_filters() call 'wp_idea_stream_edit_comment_link' to override the url
 * @return string           the new comment edit link if about an idea, unchanged otherwise
 */
function wp_idea_stream_edit_comment_link( $location = '' ) {
	if ( empty( $location ) ) {
		return $location;
	}

	// Too bad WordPres is not sending the comment object or ID in the filter :(
	if ( ! preg_match( '/[&|&amp;]c=(\d+)/', $location, $matches ) ) {
		return $location;
	}

	if ( empty( $matches[1] ) ) {
		return $location;
	}

	$comment_id = absint( $matches[1] );
	$comment    = wp_idea_stream_comments_get_comment( $comment_id );

	if ( empty( $comment->comment_post_type ) || wp_idea_stream_get_post_type() != $comment->comment_post_type ) {
		return $location;
	}

	$new_location = add_query_arg( 'post_type', wp_idea_stream_get_post_type(), $location );

	/**
	 * @param  string $new_location the new comment edit link
	 * @param  string $location     the original comment edit link
	 * @param  object $comment      the idea's comment object
	 */
	return apply_filters( 'wp_idea_stream_edit_comment_link', $new_location, $location, $comment );
}

/** Template functions ********************************************************/

/**
 * Builds the loop query arguments for user comments
 *
 * @package WP Idea Stream
 * @subpackage comments/functions
 *
 * @since 2.0.0
 *
 * @param  string $type is this a single idea ?
 * @uses   apply_filters() call 'wp_idea_stream_comments_query_args' to set different loop args
 * @return array        the loop args
 */
function wp_idea_stream_comments_query_args() {
	/**
	 * Use this filter to overide loop args
	 * @see wp_idea_stream_comments_has_comments() for the list of available ones
	 *
	 * @param  array by default an empty array
	 */
	return apply_filters( 'wp_idea_stream_comments_query_args', array() );
}

/**
 * Should we display the comments form ?
 *
 * @package WP Idea Stream
 * @subpackage comments/functions
 *
 * @since 2.0.0
 *
 * @param  bool $open   true if comments are opened, false otherwise
 * @param  int $idea_id the ID of the idea
 * @uses   wp_idea_stream_is_ideastream() to make sure it's plugin's territory
 * @uses   wp_idea_stream_is_comments_allowed() to check if comments about ideas is globally allowed
 * @uses   apply_filters() call 'wp_idea_stream_comments_open' to override the value
 * @return bool          true if comments are opened, false otherwise
 */
function wp_idea_stream_comments_open( $open = true, $idea_id = 0 ) {
	if ( ! wp_idea_stream_is_ideastream() ) {
		return $open;
	}

	if ( $open != wp_idea_stream_is_comments_allowed() ) {
		$open = false;
	}

	/**
	 * Used internally in BuddyPress parts
	 *
	 * @param  bool $open true if comments are opened, false otherwise
	 * @param  int $idea_id the ID of the idea
	 */
	return apply_filters( 'wp_idea_stream_comments_open', $open, $idea_id );
}

/**
 * Replace or Add the user's profile link to the comment authors
 *
 * @package WP Idea Stream
 * @subpackage comments/functions
 *
 * @since 2.0.0
 *
 * @param  array   $comments the list of comments in an array
 * @param  int     $idea_id  the ID of the idea
 * @uses   wp_idea_stream_is_single_idea() to make sure the single template of an idea is displayed
 * @uses   esc_url() to sanitize the url
 * @uses   wp_idea_stream_users_get_user_profile_url() to build the link to user's profile
 * @return array             the list of comments, author links replaced by their IdeaStream profile if needed
 */
function wp_idea_stream_comments_append_profile_url( $comments = array(), $idea_id = 0 ) {
	// Only filter comments arry if on a single idea
	if ( ! wp_idea_stream_is_single_idea() || empty( $comments ) || empty( $idea_id ) ) {
		return $comments;
	}

	foreach (  $comments as $key => $comment ) {
		if ( empty( $comment->user_id ) ) {
			continue;
		}

		$comments[ $key ]->comment_author_url = esc_url( wp_idea_stream_users_get_user_profile_url( $comment->user_id ) );
	}

	return $comments;
}
