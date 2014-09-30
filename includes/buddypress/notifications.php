<?php
/**
 * WP Idea Stream BuddyPress integration : notifications.
 *
 * BuddyPress / notifications
 *
 * @package WP Idea Stream
 * @subpackage buddypress/notifications
 *
 * @since  2.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Callback function to format BuddyPress notification
 *
 * @see  buddypress/loader setup_globals()
 *
 * @package WP Idea Stream
 * @subpackage buddypress/notifications
 *
 * @since  2.0.0
 *
 * @param  string $action            string identifier for the notification
 * @param  int    $item_id           the id of the object the notification relates to (ID of an idea)
 * @param  int    $secondary_item_id the user id in case of rating actions, the comment id otherwise
 * @param  int    $total_items       number of notifications having the same action
 * @param  string $format            array is WP Admin Bar, string is notifications screen
 * @uses   buddypress() to get BuddyPress main instance
 * @uses   wp_idea_stream_get_post_type() to get the ideas post type identifier
 * @uses   bp_loggedin_user_domain() to get the logged in user profile url
 * @uses   wp_idea_stream_ideas_get_idea_permalink() to get the idea permalink
 * @uses   get_the_title() to get the title of the idea
 * @uses   bp_core_get_user_displayname() to get the user's display name
 * @uses   apply_filters() call 'wp_idea_stream_buddypress_multiple_{$action}_notification' to override multiple items notification
 *                         call 'wp_idea_stream_buddypress_single_{$action}_notification' to override single item notification
 * @uses   esc_url()  to sanitize url
 * @uses   esc_attr() to sanitize attribute
 * @uses   esc_html() to sanitize output
 * @uses   do_action() call 'wp_idea_stream_buddypress_format_notifications' to perform custom actions once the notification is formated
 * @return string                    notification output
 */
function wp_idea_stream_buddypress_format_notifications( $action = '', $item_id = 0, $secondary_item_id = 0, $total_items = 0, $format = 'string' ) {
	$bp = buddypress();

	switch ( $action ) {
		case 'new_' . wp_idea_stream_get_post_type() . '_comment' :
			/**
			 * BuddyPress is grouping notifications for the same component/component action
			 * but we can have comments about several ideas, so best is to view all user's
			 * ideas (in his profile)
			 */
			if ( (int) $total_items > 1 ) {
				$url      = trailingslashit( bp_loggedin_user_domain() . $bp->ideastream->slug ) . '?notif=' . $total_items ;
				$title    = __( 'New idea comments', 'wp-idea-stream' );
				$text     = sprintf( __( '%d new idea comments', 'wp-idea-stream' ), (int) $total_items );
				$filter   = "wp_idea_stream_buddypress_multiple_{$action}_notification";
			} else {
				$url      = wp_idea_stream_ideas_get_idea_permalink( $item_id ) . '?notif=1' ;
				$title    = __( 'New idea comment', 'wp-idea-stream' );
				$text     = __( 'New idea comment', 'wp-idea-stream' );

				// Viewing notifications on user's notification screen will give a bit more infos.
				if ( 'string' == $format ) {
					$text = sprintf( __( 'New comment about the idea: %s', 'wp-idea-stream' ), strip_tags( get_the_title( $item_id ) ) );
				}

				$filter   = "wp_idea_stream_buddypress_single_{$action}_notification";
			}

		break;

		case 'new_' . wp_idea_stream_get_post_type() . '_rate' :
			if ( (int) $total_items > 1 ) {
				$url      = trailingslashit( bp_loggedin_user_domain() . $bp->ideastream->slug ) . '?notif=' . $total_items ;
				$title    = __( 'New idea rates', 'wp-idea-stream' );
				$text     = sprintf( __( '%d new idea rates', 'wp-idea-stream' ), (int) $total_items );
				$filter   = "wp_idea_stream_buddypress_multiple_{$action}_notification";
			} else {
				$url      = wp_idea_stream_ideas_get_idea_permalink( $item_id ) . '?notif=1' ;
				$title    = __( 'New idea rate', 'wp-idea-stream' );
				$text     = __( 'New idea rate', 'wp-idea-stream' );

				// Viewing notifications on user's notification screen will give a bit more infos.
				if ( 'string' == $format ) {
					$text = sprintf(
						__( '%s rated the idea: %s', 'wp-idea-stream' ),
						bp_core_get_user_displayname( $secondary_item_id ),
						strip_tags( get_the_title( $item_id ) )
					);
				}

				$filter   = "wp_idea_stream_buddypress_single_{$action}_notification";
			}
			break;
	}

	if ( 'string' == $format ) {
		/**
		 * @param  string                 the notification output
		 * @param  string $url            the href attribute of the notification
		 * @param  string $title          the title attribute of the notification
		 * @param  string $text           the text for the notification
		 * @param  int $total_items       the number of objects concerned
		 * @param  int $item_id           the ID of the idea
		 * @param  int $secondary_item_id the user id in case of rating actions, the comment id otherwise
		 */
		$return = apply_filters( $filter, '<a href="' . esc_url( $url ) . '" title="' . esc_attr( $title ) . '">' . esc_html( $text ) . '</a>', $url, $title, $text, (int) $total_items, $item_id, $secondary_item_id );
	} else {
		/**
		 * @param  array                  the notification array
		 * @param  string $url            the href attribute of the notification
		 * @param  string $text           the text for the notification
		 * @param  int $total_items       the number of objects concerned
		 * @param  int $item_id           the ID of the idea
		 * @param  int $secondary_item_id the user id in case of rating actions, the comment id otherwise
		 */
		$return = apply_filters( $filter, array(
			'text' => $text,
			'link' => $url
		), $url, $text, (int) $total_items, $item_id, $secondary_item_id );
	}

	/**
	 * @param  string $action         the action of the notification
	 * @param  int $item_id           the ID of the idea
	 * @param  int $secondary_item_id the user id in case of rating actions, the comment id otherwise
	 * @param  int $total_items       the number of objects concerned
	 */
	do_action( 'wp_idea_stream_buddypress_format_notifications', $action, $item_id, $secondary_item_id, $total_items );

	return $return;
}

/**
 * Sends a new screen notification to the author of an idea if a new comment was approved
 *
 * @package WP Idea Stream
 * @subpackage buddypress/notifications
 *
 * @since  2.0.0
 *
 * @param  object $comment comment object
 * @uses   wp_idea_stream_comments_get_comment() to get the "ideafied" comment object
 * @uses   bp_notifications_add_notification() to save a new notification
 * @uses   buddypress() to get BuddyPress instance
 * @uses   wp_idea_stream_get_post_type() to get the ideas post type identifier
 */
function wp_idea_stream_buddypress_comments_notify_idea_author( $comment = null ) {
	// Bail if no author to notify
	if ( empty( $comment->comment_post_ID ) ) {
		return;
	}

	// Append post information about the comment if not there
	if ( empty( $comment->comment_post_type ) ) {
		$comment = wp_idea_stream_comments_get_comment( $comment->comment_ID );
	}

	// If the comment_post_author is not set, it's not a comment about an idea
	if ( empty( $comment->comment_post_author ) ) {
		return;
	}

	// Do not notify if author is the same than the user who commented
	if ( ! empty( $comment->user_id ) && $comment->comment_post_author == $comment->user_id ) {
		return;
	}

	// Add the notification.
	bp_notifications_add_notification( array(
		'user_id'           => $comment->comment_post_author,
		'item_id'           => $comment->comment_post_ID,
		'secondary_item_id' => $comment->comment_ID,
		'component_name'    => buddypress()->ideastream->id,
		'component_action'  => 'new_' . wp_idea_stream_get_post_type() . '_comment',
		'is_new'            => 1,
	) );
}
add_action( 'wp_idea_stream_comments_notify_author', 'wp_idea_stream_buddypress_comments_notify_idea_author', 10, 1 );
add_action( 'comment_unapproved_to_approved',        'wp_idea_stream_buddypress_comments_notify_idea_author', 10, 1 );

/**
 * Delete a notification in case a comment is trashed, deleted or spammed
 *
 * @package WP Idea Stream
 * @subpackage buddypress/notifications
 *
 * @since  2.0.0
 *
 * @param  int $comment_id the ID of the comment
 * @uses   wp_idea_stream_comments_get_comment() to get the "ideafied" comment object
 * @uses   bp_notifications_delete_notifications_by_item_id() to delete the notification
 * @uses   buddypress() to get BuddyPress instance
 * @uses   wp_idea_stream_get_post_type() to get the ideas post type identifier
 */
function wp_idea_stream_buddypress_comments_delete_notifications_by_comment_id( $comment_id = 0 ) {
	// Bail, if no comment
	if ( empty( $comment_id ) ) {
		return;
	}

	$comment = wp_idea_stream_comments_get_comment( $comment_id );

	if ( empty( $comment->comment_post_author ) ) {
		return;
	}

	// Remove the notification.
	bp_notifications_delete_notifications_by_item_id(
		$comment->comment_post_author,
		$comment->comment_post_ID,
		buddypress()->ideastream->id,
		'new_' . wp_idea_stream_get_post_type() . '_comment',
		$comment_id
	);
}
add_action( 'trash_comment',  'wp_idea_stream_buddypress_comments_delete_notifications_by_comment_id',  10, 1 );
add_action( 'spam_comment',   'wp_idea_stream_buddypress_comments_delete_notifications_by_comment_id',  10, 1 );
add_action( 'delete_comment', 'wp_idea_stream_buddypress_comments_delete_notifications_by_comment_id',  10, 1 );

/**
 * Sends a screen notification to the author in case the idea received a vote
 *
 * @package WP Idea Stream
 * @subpackage buddypress/notifications
 *
 * @since  2.0.0
 *
 * @param  int $idea_id the ID of the idea
 * @param  int $user_id the ID of the user who rated the idea
 * @uses   bp_notifications_add_notification() to save a new notification
 * @uses   buddypress() to get BuddyPress instance
 * @uses   wp_idea_stream_get_post_type() to get the ideas post type identifier
 */
function wp_idea_stream_buddypress_rates_notify_idea_author( $idea_id = 0, $user_id = 0 ) {
	$author = get_post_field( 'post_author', $idea_id );

	// Bail, if no author or no user id or if both are same
	if ( empty( $author ) || empty( $user_id ) || $author == $user_id ) {
		return;
	}

	// Add the notification.
	bp_notifications_add_notification( array(
		'user_id'           => $author, // the one who wrote if the idea
		'item_id'           => $idea_id,
		'secondary_item_id' => $user_id, // the one who voted
		'component_name'    => buddypress()->ideastream->id,
		'component_action'  => 'new_' . wp_idea_stream_get_post_type() . '_rate',
		'is_new'            => 1,
	) );
}
add_action( 'wp_idea_stream_added_rate', 'wp_idea_stream_buddypress_rates_notify_idea_author', 10, 2 );

/**
 * Delete a notification in case a the vote was removed
 *
 * @package WP Idea Stream
 * @subpackage buddypress/notifications
 *
 * @since  2.0.0
 *
 * @param  int $idea_id the ID of the idea
 * @param  int $user_id the ID of the user who rated the idea
 * @uses   get_post_field() to get the author of the idea
 * @uses   bp_notifications_delete_notifications_by_item_id() to delete the notification
 * @uses   buddypress() to get BuddyPress instance
 * @uses   wp_idea_stream_get_post_type() to get the ideas post type identifier
 */
function wp_idea_stream_buddypress_rates_delete_notification( $idea_id = 0, $user_id = 0 ) {
	if ( empty( $idea_id ) ) {
		return;
	}

	$author = get_post_field( 'post_author', $idea_id );

	// Bail, if no author or no user id or if both are same
	if ( empty( $author ) || empty( $user_id ) || $author == $user_id ) {
		return;
	}

	// Remove the notification.
	bp_notifications_delete_notifications_by_item_id(
		$author,
		$idea_id,
		buddypress()->ideastream->id,
		'new_' . wp_idea_stream_get_post_type() . '_rate',
		$user_id
	);
}
add_action( 'wp_idea_stream_deleted_rate', 'wp_idea_stream_buddypress_rates_delete_notification', 10, 2 );

/**
 * Delete notifications if the idea was removed
 *
 * @package WP Idea Stream
 * @subpackage buddypress/notifications
 *
 * @since  2.0.0
 *
 * @param  int $idea_id the ID of the idea
 * @uses   wp_idea_stream_get_post_type() to get the ideas post type identifier
 * @uses   get_post_type() to get the post type of the trashed post
 * @uses   bp_notifications_delete_all_notifications_by_type() to delete the notification
 * @uses   buddypress() to get BuddyPress instance
 */
function wp_idea_stream_buddypress_delete_notifications_by_post_id( $idea_id = 0 ) {
	// Bail if not the idea post type.
	if ( wp_idea_stream_get_post_type() != get_post_type( $idea_id ) ) {
		return;
	}

	bp_notifications_delete_all_notifications_by_type(
		$idea_id,
		buddypress()->ideastream->id
	);
}
add_action( 'wp_trash_post', 'wp_idea_stream_buddypress_delete_notifications_by_post_id', 10, 1 );

/**
 * Delete notifications the user received in case his account was deleted
 *
 * @package WP Idea Stream
 * @subpackage buddypress/notifications
 *
 * @since  2.0.0
 *
 * @param  int $user_id the deleted user id
 * @uses   bp_notifications_delete_notifications_by_type() to delete the notifications
 * @uses   buddypress() to get BuddyPress instance
 */
function wp_idea_stream_buddypress_delete_all_user_notifications( $user_id = 0 ) {
	if ( empty( $user_id ) ) {
		return false;
	}

	// Delete all user's notifications
	return bp_notifications_delete_notifications_by_type( $user_id, buddypress()->ideastream->id, '' );
}
add_action( 'deleted_user', 'wp_idea_stream_buddypress_delete_all_user_notifications', 11, 1 );

/**
 * Delete notifications the user "sent" in case his account was deleted
 *
 * @package WP Idea Stream
 * @subpackage buddypress/notifications
 *
 * @since  2.0.0
 *
 * @param  int $user_id the deleted user id
 * @uses   BP_Notifications_Notification::delete() to delete the notifications
 * @uses   buddypress() to get BuddyPress instance
 * @uses   wp_idea_stream_get_post_type() to get the ideas post type identifier
 */
function wp_idea_stream_buddypress_delete_notifications_by_user( $user_id = 0 ) {
	if ( empty( $user_id ) ) {
		return false;
	}

	// Delete all notification about the rates he gave
	return BP_Notifications_Notification::delete( array(
		'component_name'    => buddypress()->ideastream->id,
		'component_action'  => 'new_' . wp_idea_stream_get_post_type() . '_rate',
		'secondary_item_id' => $user_id
	) );
}
add_action( 'wp_idea_stream_delete_user_rates', 'wp_idea_stream_buddypress_delete_notifications_by_user', 10, 1 );

/**
 * Mark notification(s) as read
 *
 * @package WP Idea Stream
 * @subpackage buddypress/notifications
 *
 * @since  2.0.0
 *
 * @uses   wp_idea_stream_is_single_idea() to check if viewing the single template of an idea
 * @uses   bp_notifications_mark_notifications_by_item_id() to mark notifications as read
 * @uses   bp_loggedin_user_id() to get the logged in user ID
 * @uses   wp_idea_stream_get_single_idea_id() to get the ID of the idea being viewed
 * @uses   buddypress() to get BuddyPress instance
 * @uses   wp_idea_stream_get_post_type() to get the ideas post type identifier
 * @uses   bp_is_user() to check a user's profile is displayed
 * @uses   bp_is_current_component( 'ideastream' ) to check it's an IdeaStream part of the profile
 * @uses   bp_notifications_mark_notifications_by_type() to mark notifications as read
 */
function wp_idea_stream_buddypress_comments_mark_notifications_read() {
	if (  ! empty( $_GET['notif'] ) ) {

		if ( wp_idea_stream_is_single_idea() ) {
			bp_notifications_mark_notifications_by_item_id( bp_loggedin_user_id(), wp_idea_stream_get_single_idea_id(), buddypress()->ideastream->id, 'new_' . wp_idea_stream_get_post_type() . '_comment' );
			bp_notifications_mark_notifications_by_item_id( bp_loggedin_user_id(), wp_idea_stream_get_single_idea_id(), buddypress()->ideastream->id, 'new_' . wp_idea_stream_get_post_type() . '_rate' );
		}

		if ( bp_is_user() && bp_is_current_component( 'ideastream' ) ) {
			bp_notifications_mark_notifications_by_type( bp_loggedin_user_id(), buddypress()->ideastream->id, 'new_' . wp_idea_stream_get_post_type() . '_comment' );
			bp_notifications_mark_notifications_by_type( bp_loggedin_user_id(), buddypress()->ideastream->id, 'new_' . wp_idea_stream_get_post_type() . '_rate' );
		}

	}
}
add_action( 'bp_screens', 'wp_idea_stream_buddypress_comments_mark_notifications_read', 11 );
