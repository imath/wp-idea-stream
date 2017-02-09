<?php
/**
 * WP Idea Stream Ideas classes.
 *
 * Only used if class autoload is not available.
 *
 * @package WP Idea Stream\ideas
 *
 * @since 2.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/** Idea Class ****************************************************************/

if ( ! class_exists( 'WP_Idea_Stream_Idea' ) ) {
	require( dirname( __FILE__ ) . '/classes/class-wp-idea-stream-idea.php' );
}

/** Ideas Loop ****************************************************************/

if ( ! class_exists( 'WP_Idea_Stream_Loop_Ideas' ) ) {
	require( dirname( __FILE__ ) . '/classes/class-wp-idea-stream-loop-ideas.php' );
}

/** Idea Metas ****************************************************************/

if ( ! class_exists( 'WP_Idea_Stream_Idea_Metas' ) ) {
	require( dirname( __FILE__ ) . '/classes/class-wp-idea-stream-idea-metas.php' );
}

/** Ideas Thumbnail ***********************************************************/

if ( ! class_exists( 'WP_Idea_Stream_Ideas_Thumbnail' ) ) {
	require( dirname( __FILE__ ) . '/classes/class-wp-idea-stream-ideas-thumbnail.php' );
}

/** Ideas Rest Controller *****************************************************/

if ( ! class_exists( 'WP_Idea_Stream_Ideas_REST_Controller' ) {
	require( dirname( __FILE__ ) . '/classes/class-wp-idea-stream-ideas-rest-controller.php' );
}

/** Ideas Categories Widget ***************************************************/

if ( ! class_exists( 'WP_Idea_Stream_Ideas_Categories' ) ) {
	require( dirname( __FILE__ ) . '/classes/class-wp-idea-stream-ideas-categories.php' );
}

/** Ideas Popular Widget ******************************************************/

if ( ! class_exists( 'WP_Idea_Stream_Ideas_Popular' ) ) {
	require( dirname( __FILE__ ) . '/classes/class-wp-idea-stream-ideas-popular.php' );
}
