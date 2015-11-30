<?php
/**
 * WP Idea Stream User's comments tags
 *
 * User's profile comments tags
 *
 * @package WP Idea Stream
 * @subpackage comments/tags
 *
 * @since 2.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/** Comment Loop **************************************************************/

/**
 * Initialize the user's comments loop.
 *
 * @package WP Idea Stream
 * @subpackage comments/tags
 *
 * @since 2.0.0
 *
 * @param array $args {
 *     Arguments for customizing comments retrieved in the loop.
 *     Arguments must be passed as an associative array
 *     @type int 'user_id' to restrict the loop to one user (defaults to displayed user)
 *     @type string 'status' to limit the query to comments having a certain status (defaults to approve)
 *     @type int 'number' Number of results per page.
 *     @type int 'page' the page of results to display.
 * }
 * @uses   wp_parse_args() to merge args with defaults
 * @uses   wp_idea_stream_users_displayed_user_id() to get the ID of the displayed user.
 * @uses   wp_idea_stream_ideas_per_page() to get the pagination preferences
 * @uses   WP_Idea_Stream_Loop_Comments to get the comments matching arguments
 * @uses   wp_idea_stream() to get plugin's main instance
 * @uses   apply_filters() call 'wp_idea_stream_comments_has_comments' to choose whether to init the loop or not
 * @return bool         true if comments were found, false otherwise
 */
function wp_idea_stream_comments_has_comments( $args = array() ) {

	$r = wp_parse_args( $args, array(
		'user_id' => wp_idea_stream_users_displayed_user_id(),
		'status'  => 'approve',
		'number'  => wp_idea_stream_ideas_per_page(),
		'page'    => 1,
	) );

	// Get the WP Idea Stream
	$comment_query_loop = new WP_Idea_Stream_Loop_Comments( array(
		'user_id' => (int) $r['user_id'],
		'status'  => $r['status'],
		'number'  => (int) $r['number'],
		'page'    => (int) $r['page'],
	) );

	// Setup the global query loop
	wp_idea_stream()->comment_query_loop = $comment_query_loop;

	return apply_filters( 'wp_idea_stream_comments_has_comments', $comment_query_loop->has_items(), $comment_query_loop );
}

/**
 * Get the comments returned by the template loop.
 *
 * @package WP Idea Stream
 * @subpackage comments/tags
 *
 * @since 2.0.0
 *
 * @uses   wp_idea_stream() to get plugin's main instance
 * @return array List of comments.
 */
function wp_idea_stream_comments_the_comments() {
	return wp_idea_stream()->comment_query_loop->items();
}

/**
 * Get the current comment object in the loop.
 *
 * @package WP Idea Stream
 * @subpackage comments/tags
 *
 * @since 2.0.0
 *
 * @uses   wp_idea_stream() to get plugin's main instance
 * @return object The current comment within the loop.
 */
function wp_idea_stream_comments_the_comment() {
	return wp_idea_stream()->comment_query_loop->the_item();
}

/** Loop Output ***************************************************************/
// Mainly inspired by The BuddyPress notifications loop

/**
 * Displays a message if no comments were found
 *
 * @package WP Idea Stream
 * @subpackage comments/tags
 *
 * @since 2.0.0
 *
 * @uses wp_idea_stream_comments_get_no_comment_found() to get the message
 */
function wp_idea_stream_comments_no_comment_found() {
	echo wp_idea_stream_comments_get_no_comment_found();
}

	/**
	 * Gets a message if no comments were found
	 *
	 * @package WP Idea Stream
	 * @subpackage comments/tags
	 *
	 * @since 2.0.0
	 *
	 * @uses   wp_idea_stream_users_get_displayed_user_displayname() to get the message
	 * @uses   apply_filters() call 'wp_idea_stream_comments_get_no_comment_found' to override the output
	 * @return string the message if no comments were found
	 */
	function wp_idea_stream_comments_get_no_comment_found() {
		$output = sprintf(
			__( 'It looks like %s has not commented any idea yet', 'wp-idea-stream' ),
			wp_idea_stream_users_get_displayed_user_displayname()
		);

		/**
		 * @param  string $output the message if no comments were found
		 */
		return apply_filters( 'wp_idea_stream_comments_get_no_comment_found', $output );
	}

/**
 * Output the pagination count for the current comments loop.
 *
 * @package WP Idea Stream
 * @subpackage comments/tags
 *
 * @since 2.0.0
 *
 * @uses   wp_idea_stream_comments_get_pagination_count() to get the pagination count
 */
function wp_idea_stream_comments_pagination_count() {
	echo wp_idea_stream_comments_get_pagination_count();
}

	/**
	 * Return the pagination count for the current comments loop.
	 *
	 * @package WP Idea Stream
	 * @subpackage comments/tags
	 *
	 * @since 2.0.0
	 *
	 * @uses   wp_idea_stream() to get plugin's main instance
	 * @uses   number_format_i18n() to format numbers
	 * @uses   apply_filters() call 'wp_idea_stream_comments_get_pagination_count' to override the output
	 * @return string HTML for the pagination count.
	 */
	function wp_idea_stream_comments_get_pagination_count() {
		$query_loop = wp_idea_stream()->comment_query_loop;
		$start_num  = intval( ( $query_loop->page - 1 ) * $query_loop->per_page ) + 1;
		$from_num   = number_format_i18n( $start_num );
		$to_num     = number_format_i18n( ( $start_num + ( $query_loop->per_page - 1 ) > $query_loop->total_comment_count ) ? $query_loop->total_comment_count : $start_num + ( $query_loop->number - 1 ) );
		$total      = number_format_i18n( $query_loop->total_comment_count );
		$pag        = sprintf( _n( 'Viewing %1$s to %2$s (of %3$s comments)', 'Viewing %1$s to %2$s (of %3$s comments)', $total, 'wp-idea-stream' ), $from_num, $to_num, $total );

		/**
		 * @param  string $pag the pagination count to output
		 */
		return apply_filters( 'wp_idea_stream_comments_get_pagination_count', $pag );
	}

/**
 * Output the pagination links for the current comments loop.
 *
 * @package WP Idea Stream
 * @subpackage comments/tags
 *
 * @since 2.0.0
 *
 * @uses   wp_idea_stream_comments_get_pagination_links() to get the pagination links
 */
function wp_idea_stream_comments_pagination_links() {
	echo wp_idea_stream_comments_get_pagination_links();
}

	/**
	 * Return the pagination links for the current comments loop.
	 *
	 * @package WP Idea Stream
	 * @subpackage comments/tags
	 *
	 * @since 2.0.0
	 *
	 * @uses   wp_idea_stream() to get plugin's main instance
	 * @uses   apply_filters() call 'wp_idea_stream_comments_get_pagination_links' to override the output
	 * @return string HTML for the pagination links.
	 */
	function wp_idea_stream_comments_get_pagination_links() {
		/**
		 * @param  string the pagination links to output
		 */
		return apply_filters( 'wp_idea_stream_comments_get_pagination_links', wp_idea_stream()->comment_query_loop->pag_links );
	}

/**
 * Output the ID of the comment currently being iterated on.
 *
 * @package WP Idea Stream
 * @subpackage comments/tags
 *
 * @since 2.0.0
 *
 * @uses   wp_idea_stream_comments_get_comment_id() to get the comment ID
 */
function wp_idea_stream_comments_the_comment_id() {
	echo wp_idea_stream_comments_get_comment_id();
}

	/**
	 * Return the ID of the comment currently being iterated on.
	 *
	 * @package WP Idea Stream
	 * @subpackage comments/tags
	 *
	 * @since 2.0.0
	 *
	 * @uses   wp_idea_stream() to get plugin's main instance
	 * @uses   apply_filters() call 'wp_idea_stream_comments_get_comment_id' to override the output
	 * @return int ID of the current comment.
	 */
	function wp_idea_stream_comments_get_comment_id() {
		/**
		 * @param  int the comment ID to output
		 */
		return apply_filters( 'wp_idea_stream_comments_get_comment_id', wp_idea_stream()->comment_query_loop->comment->comment_ID );
	}

/**
 * Output the avatar of the author of the comment currently being iterated on.
 *
 * @package WP Idea Stream
 * @subpackage comments/tags
 *
 * @since 2.0.0
 *
 * @uses   wp_idea_stream_comments_get_comment_author_avatar() to get the avatar
 */
function wp_idea_stream_comments_the_comment_author_avatar() {
	echo wp_idea_stream_comments_get_comment_author_avatar();
}

	/**
	 * Return the avatar of the author of the comment currently being iterated on.
	 *
	 * @package WP Idea Stream
	 * @subpackage comments/tags
	 *
	 * @since 2.0.0
	 *
	 * @uses   wp_idea_stream() to get plugin's main instance
	 * @uses   get_avatar() to get the avatar of the author
	 * @uses   wp_idea_stream_users_get_user_profile_url() to get the comment author profile url
	 * @uses   apply_filters() call 'wp_idea_stream_comments_get_comment_author_avatar' to override the output
	 * @return string the avatar.
	 */
	function wp_idea_stream_comments_get_comment_author_avatar() {
		$author = wp_idea_stream()->comment_query_loop->comment->user_id;
		$avatar = get_avatar( $author );
		$avatar_link = '<a href="' . esc_url( wp_idea_stream_users_get_user_profile_url( $author ) ) . '" title="' . esc_attr__( 'User&#39;s profile', 'wp-idea-stream' ) . '">' . $avatar . '</a>';

		/**
		 * @param  string  $avatar_link the avatar output
		 * @param  int     $author the author ID
		 * @param  string  $avatar the avatar
		 */
		return apply_filters( 'wp_idea_stream_comments_get_comment_author_avatar', $avatar_link, $author, $avatar );
	}

/**
 * Output the mention to add before the title of the comment currently being iterated on.
 *
 * @package WP Idea Stream
 * @subpackage comments/tags
 *
 * @since 2.0.0
 *
 * @uses   wp_idea_stream_comments_get_before_comment_title() to get the mention
 */
function wp_idea_stream_comments_before_comment_title() {
	echo wp_idea_stream_comments_get_before_comment_title();
}

	/**
	 * Return the mention to add before the title of the comment currently being iterated on.
	 *
	 * @package WP Idea Stream
	 * @subpackage comments/tags
	 *
	 * @since 2.0.0
	 *
	 * @uses   apply_filters() call 'wp_idea_stream_comments_get_before_comment_title' to override the output
	 * @return string the mention to prefix the title with.
	 */
	function wp_idea_stream_comments_get_before_comment_title() {
		/**
		 * @param  string  the mention output
		 */
		return apply_filters( 'wp_idea_stream_comments_get_before_comment_title', esc_html__( 'In reply to :', 'wp-idea-stream' ) );
	}

/**
 * Output the permalink of the comment currently being iterated on.
 *
 * @package WP Idea Stream
 * @subpackage comments/tags
 *
 * @since 2.0.0
 *
 * @uses   wp_idea_stream_comments_get_comment_permalink() to get the permalink
 */
function wp_idea_stream_comments_the_comment_permalink() {
	echo wp_idea_stream_comments_get_comment_permalink();
}

	/**
	 * Return the permalink of the comment currently being iterated on.
	 *
	 * @package WP Idea Stream
	 * @subpackage comments/tags
	 *
	 * @since 2.0.0
	 *
	 * @uses   wp_idea_stream() to get plugin's main instance
	 * @uses   wp_idea_stream_comments_get_comment_link() to get the permalink
	 * @uses   apply_filters() call 'wp_idea_stream_comments_get_comment_permalink' to override the output
	 * @return string the comment's permalink.
	 */
	function wp_idea_stream_comments_get_comment_permalink() {
		$comment = wp_idea_stream()->comment_query_loop->comment;
		$comment_link = wp_idea_stream_comments_get_comment_link( $comment );

		/**
		 * @param  string  $comment_link the comment link
		 * @param  object  $comment the comment object
		 */
		return apply_filters( 'wp_idea_stream_comments_get_comment_permalink', esc_url( $comment_link ), $comment );
	}

/**
 * Output the title attribute of the comment currently being iterated on.
 *
 * @package WP Idea Stream
 * @subpackage comments/tags
 *
 * @since 2.0.0
 *
 * @uses   wp_idea_stream_comments_get_comment_title_attribute() to get the title attribute
 */
function wp_idea_stream_comments_the_comment_title_attribute() {
	echo wp_idea_stream_comments_get_comment_title_attribute();
}

	/**
	 * Return the title attribute of the comment currently being iterated on.
	 *
	 * @package WP Idea Stream
	 * @subpackage comments/tags
	 *
	 * @since 2.0.0
	 *
	 * @uses   wp_idea_stream() to get plugin's main instance
	 * @uses   get_post() to get the idea the comment is linked to
	 * @uses   apply_filters() call 'wp_idea_stream_comments_get_comment_title_attribute' to override the output
	 * @uses   esc_attr() to sanitize the title attribute
	 * @return string the title attribute.
	 */
	function wp_idea_stream_comments_get_comment_title_attribute() {
		$comment = wp_idea_stream()->comment_query_loop->comment;
		$title = '';

		$idea = $comment->comment_post_ID;

		if ( ! empty( $comment->idea ) ) {
			$idea = $comment->idea;
		}

		$idea = get_post( $idea );

		if ( ! empty( $idea->post_password ) ) {
			$title = _x( 'Protected :', 'idea permalink title protected attribute', 'wp-idea-stream' ) . ' ';
		} else if ( ! empty( $idea->post_status ) && 'private' == $idea->post_status ) {
			$title = _x( 'Private :', 'idea permalink title private attribute', 'wp-idea-stream' ) . ' ';
		}

		$title .= $idea->post_title;

		/**
		 * @param  string   $title the title attribute
		 * @param  WP_Post  $idea the idea object
		 * @param  object   $comment the comment object
		 */
		return apply_filters( 'wp_idea_stream_comments_get_comment_title_attribute', esc_attr( $title ), $idea, $comment );
	}

/**
 * Output the title of the comment currently being iterated on.
 *
 * @package WP Idea Stream
 * @subpackage comments/tags
 *
 * @since 2.0.0
 *
 * @uses   wp_idea_stream_comments_get_comment_title() to get the title
 */
function wp_idea_stream_comments_the_comment_title() {
	echo wp_idea_stream_comments_get_comment_title();
}

	/**
	 * Return the title of the comment currently being iterated on.
	 *
	 * @package WP Idea Stream
	 * @subpackage comments/tags
	 *
	 * @since 2.0.0
	 *
	 * @uses   wp_idea_stream() to get plugin's main instance
	 * @uses   get_the_title() to get the title of the idea the comment is linked to
	 * @uses   apply_filters() call 'wp_idea_stream_comments_get_comment_title' to override the output
	 * @return string the title.
	 */
	function wp_idea_stream_comments_get_comment_title() {
		$comment = wp_idea_stream()->comment_query_loop->comment;

		/**
		 * When the idea has a private status, we're applying a dashicon to a span
		 * So we need to only allow this tag when sanitizing the output
		 */
		if ( isset( $comment->post_status ) && 'publish' !== $comment->post_status ) {
			$title = wp_kses( get_the_title( $comment->comment_post_ID ), array( 'span' => array( 'class' => array() ) ) );
		} else {
			$title = esc_html( get_the_title( $comment->comment_post_ID ) );
		}

		/**
		 * @param  string   the title of the idea, the comment is linked to
		 * @param  object   $comment the comment object
		 */
		return apply_filters( 'wp_idea_stream_comments_get_comment_title', $title, $comment );
	}

/**
 * Output the excerpt of the comment currently being iterated on.
 *
 * @package WP Idea Stream
 * @subpackage comments/tags
 *
 * @since 2.0.0
 *
 * @uses   wp_idea_stream_comments_get_comment_excerpt() to get the excerpt
 */
function wp_idea_stream_comments_the_comment_excerpt() {
	echo wp_idea_stream_comments_get_comment_excerpt();
}

	/**
	 * Return the excerpt of the comment currently being iterated on.
	 *
	 * @package WP Idea Stream
	 * @subpackage comments/tags
	 *
	 * @since 2.0.0
	 *
	 * @uses   wp_idea_stream() to get plugin's main instance
	 * @uses   get_post() to get the idea the comment is linked to
	 * @uses   get_comment_excerpt() to get the comment excerpt
	 * @uses   apply_filters() call 'wp_idea_stream_comments_get_comment_excerpt' to override the output
	 * @return string the excerpt.
	 */
	function wp_idea_stream_comments_get_comment_excerpt() {
		$comment = wp_idea_stream()->comment_query_loop->comment;
		$title = '';

		$idea = $comment->comment_post_ID;

		if ( ! empty( $comment->idea ) ) {
			$idea = $comment->idea;
		}

		$idea = get_post( $idea );

		if ( post_password_required( $idea ) ) {
			$excerpt = __( 'The idea, the comment was posted on, is password protected, you will need it to view its content.', 'wp-idea-stream' );

		// Private
		} else if ( ! empty( $idea->post_status ) && 'private' == $idea->post_status && ! wp_idea_stream_user_can( 'read_idea', $idea->ID ) ) {
			$excerpt = __( 'The idea, the comment was posted on is private, you cannot view its content.', 'wp-idea-stream' );

		// Public
		} else {
			$excerpt = get_comment_excerpt( wp_idea_stream()->comment_query_loop->comment->comment_ID );
		}

		/**
		 * @param  string   $excerpt the comment excerpt
		 */
		return apply_filters( 'wp_idea_stream_comments_get_comment_excerpt', $excerpt );
	}

/**
 * Output the footer of the comment currently being iterated on.
 *
 * @package WP Idea Stream
 * @subpackage comments/tags
 *
 * @since 2.0.0
 *
 * @uses   wp_idea_stream_comments_get_comment_footer() to get the footer
 */
function wp_idea_stream_comments_the_comment_footer() {
	echo wp_idea_stream_comments_get_comment_footer();
}

	/**
	 * Return the footer of the comment currently being iterated on.
	 *
	 * @package WP Idea Stream
	 * @subpackage comments/tags
	 *
	 * @since 2.0.0
	 *
	 * @uses   wp_idea_stream() to get plugin's main instance
	 * @uses   get_comment_date() to get the date the comment was posted
	 * @uses   apply_filters() call 'wp_idea_stream_comments_get_comment_footer' to override the output
	 * @return string the footer.
	 */
	function wp_idea_stream_comments_get_comment_footer() {
		$posted_on = sprintf( esc_html__( 'This comment was posted on %s', 'wp-idea-stream' ), get_comment_date( '', wp_idea_stream()->comment_query_loop->comment->comment_ID ) );

		/**
		 * @param  string   $posted_on the comment footer
		 * @param  object   the comment object
		 */
		return apply_filters( 'wp_idea_stream_comments_get_comment_footer', $posted_on, wp_idea_stream()->comment_query_loop->comment );
	}
