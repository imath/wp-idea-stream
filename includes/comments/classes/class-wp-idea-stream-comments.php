<?php
/**
 * WP Idea Stream Comments Class.
 *
 * @package WP Idea Stream\comments\classes
 *
 * @since 2.4.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Main Comments class
 *
 * Actions and filters organized in a class
 * Its main goal is to disjoin comments about ideas
 * from regular comments (other post types)
 *
 * @since 2.0.0
 */
class WP_Idea_Stream_Comments {


	/**
	 * Constructor
	 *
	 * @since 2.0.0
	 */
	public function __construct() {
		$this->setup_globals();
		$this->hooks();
	}

	/**
	 * Starts the class
	 *
	 * @since 2.0.0
	 */
	public static function start() {
		$wp_idea_stream = wp_idea_stream();

		if ( empty( $wp_idea_stream->comments ) ) {
			$wp_idea_stream->comments = new self;
		}

		return $wp_idea_stream->comments;
	}

	/**
	 * Setups some globals
	 *
	 * @since 2.0.0
	 */
	private function setup_globals() {
		/** Rewrite ids ***************************************************************/
		$this->post_type = wp_idea_stream_get_post_type();
		$this->comments_count = false;
		$this->idea_comments_count = false;
	}

	/**
	 * Hooks to disjoin comments about ideas
	 * & to filter the email notifications
	 *
	 * @since 2.0.0
	 */
	private function hooks() {

		if ( wp_idea_stream_is_comments_disjoined() ) {
			add_action( 'pre_get_comments',     array( $this, 'maybe_idea_comments' ),       10, 1 );

			add_action( 'wp_idea_stream_init',  array( $this, 'cache_comments_count' )             );
			add_filter( 'wp_count_comments',    array( $this, 'adjust_comment_count' ),      10, 1 );
			add_filter( 'widget_comments_args', array( $this, 'comments_widget_dummy_var' ), 10, 1 );
			add_filter( 'comments_clauses',     array( $this, 'maybe_alter_comments_query'), 10, 2 );
		}

		// Make sure the comment notifications respect idea authors capability
		add_filter( 'comment_moderation_recipients', array( $this, 'moderation_recipients' ), 10, 2 );
		add_filter( 'comment_notification_text',     array( $this, 'comment_notification' ),  10, 2 );
		add_filter( 'comment_moderation_text',       array( $this, 'comment_notification' ),  10, 2 );
	}

	/**
	 * Makes sure the post type is set to ideas when in Ideas
	 * administration screens
	 *
	 * @since 2.0.0
	 *
	 * @param  WP_Comment_Query $wp_comment_query
	 */
	function maybe_idea_comments( $wp_comment_query = null ) {
		// Bail if Ajax
		if ( wp_doing_ajax() ) {
			return;
		}

		if ( wp_idea_stream_is_admin() ) {
			$wp_comment_query->query_vars['post_type'] = $this->post_type;
		}
	}

	/**
	 * Catches the "all comments" count
	 *
	 * @since 2.0.0
	 */
	public function cache_comments_count() {
		$this->comment_count = wp_cache_get( 'comments-0', 'counts' );

		if ( empty( $this->comment_count ) ) {
			$this->comment_count = wp_count_comments();
		}

		// For internal use only, please don't use this action.
		do_action( 'wp_idea_stream_cache_comments_count' );
	}

	/**
	 * Adjust the comment count
	 * by counting comments about ideas
	 * by removing this count to the global comment count
	 *
	 * @since 2.0.0
	 *
	 * @param   array $stats empty array to override in the method
	 * @return  array adjusted comment count stats
	 */
	public function adjust_comment_count( $stats = array() ) {
		if ( did_action( 'wp_idea_stream_cache_comments_count' ) ) {
			$this->idea_comment_count = wp_idea_stream_comments_count_comments();

			// Catch this count
			wp_idea_stream_set_idea_var( 'idea_comment_count', $this->idea_comment_count );

			if ( ! did_action( 'wp_idea_stream_comments_count_cached' ) ) {
				$idea_comment_count = clone $this->idea_comment_count;

				foreach ( $this->comment_count as $key => $count ) {
					if ( empty( $idea_comment_count->{$key} ) ) {
						continue;
					}

					$this->comment_count->{$key} = $count - $idea_comment_count->{$key};
					unset( $idea_comment_count->{$key} );
				}

				// For internal use only, please don't use this action.
				do_action( 'wp_idea_stream_comments_count_cached' );
			}

			$stats = $this->comment_count;
		}

		return $stats;
	}

	/**
	 * Adds a dummy argument to comments widget in order
	 * to be able to remove a bit later comments about ideas
	 *
	 * @since 2.0.0
	 */
	public function comments_widget_dummy_var( $comment_args = array() ) {
		if ( empty( $comment_args['post_type' ] ) || $this->post_type != $comment_args['post_type' ] ) {
			$comment_args['strip_ideas'] = true;
		}

		/**
		 * @param  array $comment_args the arguments of the comment query of the widget
		 */
		return apply_filters( 'wp_idea_stream_comments_widget_disjoin_ideas', $comment_args );
	}

	/**
	 * Make sure ideas comments are not mixed with posts ones
	 *
	 * @since 2.0.0
	 *
	 * @param   array  $pieces
	 * @param   WP_Comment_Query $wp_comment_query
	 * @return  array  $pieces
	 */
	public function maybe_alter_comments_query( $pieces = array(), $wp_comment_query = null ) {

		// Bail if Ajax
		if ( wp_doing_ajax() ) {
			return $pieces;
		}

		/* Bail if not the ideas post type */
		if ( $this->post_type == $wp_comment_query->query_vars['post_type'] || wp_idea_stream_is_admin() ) {
			return $pieces;
		}

		/* Bail if strip ideas query var is not set on front */
		if ( ! is_admin() && empty( $wp_comment_query->query_vars['strip_ideas'] ) ) {
			return $pieces;
		}

		// Override pieces
		return array_merge( $pieces, self::comments_query_pieces( $pieces ) );
	}

	/**
	 * Removes recipients from the moderation notification if needed
	 *
	 * @since 2.0.0
	 *
	 * @param  array   $emails     list of emails that will receive the moderation notification
	 * @param  integer $comment_id the comment ID
	 * @return array               the emails, without the author
	 */
	public function moderation_recipients( $emails = array(), $comment_id = 0 ) {
		// Return if no comment ID
		if ( empty( $comment_id ) ) {
			return $emails;
		}

		// Get the comment
		$comment = wp_idea_stream_comments_get_comment( $comment_id );

		// check if it relates to an idea
		if ( empty( $comment->comment_post_type ) || wp_idea_stream_get_post_type() != $comment->comment_post_type ) {
			return $emails;
		}

		// We have a comment about an idea, catch it for a later use
		$this->{'comment_post_' . $comment_id} = $comment;

		/**
		 * Idea's author will receive a moderation email but won't be able
		 * to moderate it in WordPress Admin, so we need to remove their
		 * email from recipients list.
		 */
		$author_email = wp_idea_stream_users_get_user_data( 'id', $comment->comment_post_author, 'user_email' );

		// Found author's email in the list ? If so, let's remove it.
		if ( ! empty( $author_email ) && in_array( $author_email, $emails ) ) {
			$emails = array_diff( $emails, array( $author_email ) );
		}

		return $emails;
	}

	/**
	 * Edit the new comment notification message
	 *
	 * @since 2.0.0
	 *
	 * @param  string  $message    the content of the notification
	 * @param  integer $comment_id the comment ID
	 * @return string              the content, edited if needed
	 */
	public function comment_notification( $message = '', $comment_id = 0 ) {
		// Return if no comment ID
		if ( empty( $comment_id ) ) {
			return $message;
		}

		// Check caught value
		if ( ! empty( $this->{'comment_post_' . $comment_id} ) ) {
			$comment = $this->{'comment_post_' . $comment_id};

		// Get the comment to check if it relates to an idea
		} else {
			$comment = wp_idea_stream_comments_get_comment( $comment_id );
		}

		// Return if no user_id or the comment does not relate to an idea
		if ( empty( $comment->comment_post_author ) || empty( $comment->comment_post_type ) || wp_idea_stream_get_post_type() != $comment->comment_post_type ) {
			return $message;
		}

		// First add a post type var at the end of the links
		preg_match_all( '/(comment|comments).php\?(.*)\\r\\n/', $message, $matches );

		if ( ! empty( $matches[2] ) ) {
			foreach ( $matches[2] as $action ) {
				$message = str_replace( $action, $action . '&post_type=' . wp_idea_stream_get_post_type(), $message );
			}
		}

		// It's not a notification to author return the message
		if ( empty( $comment->comment_approved ) ) {
			return $message;
		}

		/**
		 * If we arrive here, then WordPress is notifying the author of the idea
		 * that a new comment has been posted and approuved on his idea. So if the
		 * idea's author does not have the capability to moderate comments, we need
		 * to make sure he won't receive the links to delete|trash|spam the comment
		 * The easiest way is to completely replace the content of the message sent.
		 */
		if ( ! user_can( $comment->comment_post_author, 'moderate_comments' ) ) {
			// reset the message
			$message = sprintf( __( 'New comment on your idea "%s"', 'wp-idea-stream' ), $comment->comment_post_title ) . "\r\n";
			$message .= __('Comment: ') . "\r\n" . $comment->comment_content . "\r\n\r\n";
			$message .= sprintf( __( 'Permalink to the comment: %s', 'wp-idea-stream' ), wp_idea_stream_comments_get_comment_link( $comment_id ) ) . "\r\n";
		}

		/**
		 * Used internally to generate a BuddyPress screen notification
		 *
		 * @param  object $comment the comment object
		 */
		do_action( 'wp_idea_stream_comments_notify_author', $comment );

		return $message;
	}

	/**
	 * Build pieces to remove comments about ideas
	 *
	 * @since 2.0.0
	 *
	 * @global  $wpdb
	 * @param   array  $pieces the comment sql query pieces
	 * @return  array  $pieces
	 */
	public static function comments_query_pieces( $pieces = array() ) {
		global $wpdb;

		if ( ! empty( $pieces ) ) {
			$pieces = array(
				'join'  => "JOIN {$wpdb->posts} ON {$wpdb->posts}.ID = {$wpdb->comments}.comment_post_ID",
				'where' => $pieces['where'] . ' ' . $wpdb->prepare( "AND {$wpdb->posts}.post_type != %s", wp_idea_stream_get_post_type() ),
			);
		}

		return $pieces;
	}

	/**
	 * Count user's comments about ideas
	 *
	 * @since 2.0.0
	 *
	 * @global  $wpdb
	 * @param   int  $user_id
	 * @return  int  $stats number of comments for the user
	 */
	public static function count_user_comments( $user_id = 0 ) {
		global $wpdb;

		// Initialize vars
		$stats = 0;
		$sql = array();

		if ( empty( $user_id ) ) {
			return $stats;
		}

		$sql['select']  = 'SELECT COUNT( * )';
		$sql['from']    = "FROM {$wpdb->comments} LEFT JOIN {$wpdb->posts} ON ( {$wpdb->posts}.ID = {$wpdb->comments}.comment_post_ID )";
		$sql['where'][] = $wpdb->prepare( "{$wpdb->posts}.post_type = %s", wp_idea_stream_get_post_type() );
		$sql['where'][] = $wpdb->prepare( "{$wpdb->comments}.user_id = %d", $user_id );
		$sql['where'][] = $wpdb->prepare( "{$wpdb->comments}.comment_approved = %d", 1 );

		//Merge where clauses
		$sql['where'] = 'WHERE ' . join( ' AND ', $sql['where'] );

		$query = apply_filters( 'wp_idea_stream_count_user_comments_query', join( ' ', $sql ), $sql );

		$stats = (int) $wpdb->get_var( $query );

		return $stats;
	}

	/**
	 * Count comments about ideas
	 *
	 * @since 2.0.0
	 *
	 * @global  $wpdb
	 * @return  object  $stats list of comments by type (approved, pending, spam, trash...)
	 */
	public static function count_ideas_comments() {
		global $wpdb;

		// Initialize vars
		$stats = array();
		$sql = array();

		$sql['select']  = 'SELECT comment_approved, COUNT( * ) AS num_comments';
		$sql['from']    = "FROM {$wpdb->comments} LEFT JOIN {$wpdb->posts} ON ( {$wpdb->posts}.ID = {$wpdb->comments}.comment_post_ID )";
		$sql['where']   = $wpdb->prepare( "WHERE {$wpdb->posts}.post_type = %s", wp_idea_stream_get_post_type() );
		$sql['groupby'] = 'GROUP BY comment_approved';

		$query = apply_filters( 'wp_idea_stream_count_ideas_comments_query', join( ' ', $sql ), $sql );
		$count = $wpdb->get_results( $query, ARRAY_A );

		$total = 0;
		$approved = array('0' => 'moderated', '1' => 'approved', 'spam' => 'spam', 'trash' => 'trash', 'post-trashed' => 'post-trashed' );
		foreach ( (array) $count as $row ) {
			// Don't count post-trashed toward totals
			if ( 'post-trashed' != $row['comment_approved'] && 'trash' != $row['comment_approved'] ) {
				$total += $row['num_comments'];
			}

			if ( isset( $approved[ $row['comment_approved'] ] ) ) {
				$stats[ $approved[ $row['comment_approved'] ] ] = $row['num_comments'];
			}
		}

		$stats['total_comments'] = $total;
		$stats['all']            = $total;

		foreach ( $approved as $key ) {
			if ( empty( $stats[ $key ] ) ) {
				$stats[ $key ] = 0;
			} else {
				$stats[ $key ] = (int) $stats[ $key ];
			}
		}

		return (object) $stats;
	}
}
