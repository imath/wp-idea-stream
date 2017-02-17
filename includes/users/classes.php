<?php
/**
 * WP Idea Stream Users Widgets.
 *
 * Only used if class autoload is not available
 *
 * @package WP Idea Stream\users
 *
 * @since 2.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WP_Idea_Stream_Users_Top_Contributors' ) ) {
	require( dirname( __FILE__ ) . '/classes/class-wp-idea-stream-users-top-contributors.php' );
}
