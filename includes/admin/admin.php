<?php
/**
 * WP Idea Stream Admin.
 *
 * Only used if class autoload is not available
 *
 * @package WP Idea Stream\admin
 *
 * @since 2.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WP_Idea_Stream_Admin' ) ) {
	require( dirname( __FILE__ ) . '/classes/class-wp-idea-stream-admin.php' );
}
