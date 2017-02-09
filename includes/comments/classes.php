<?php
/**
 * WP Idea Stream Comments classes.
 *
 * Only used if class autoload is not available
 *
 * @package WP Idea Stream\comments
 *
 * @since 2.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/** Disjoin comments **********************************************************/

if ( ! class_exists( 'WP_Idea_Stream_Comments' ) ) {
	require( dirname( __FILE__ ) . '/classes/class-wp-idea-stream-comments.php' );
}

/** Comment Loop **************************************************************/

if ( ! class_exists( 'WP_Idea_Stream_Loop_Comments' ) ) {
	require( dirname( __FILE__ ) . '/classes/class-wp-idea-stream-loop-comments.php' );
}

/** Recent Comments Widget ****************************************************/

if ( ! class_exists( 'WP_Idea_Stream_Comments_Recent' ) ) {
	require( dirname( __FILE__ ) . '/classes/class-wp-idea-stream-comments-recent.php' );
}
