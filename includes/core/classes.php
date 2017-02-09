<?php
/**
 * WP Idea Stream Classes.
 *
 * Only used if class autoload is not available.
 *
 * @package WP Idea Stream\core
 *
 * @since  2.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/** Rewrites ******************************************************************/

if ( ! class_exists( 'WP_Idea_Stream_Rewrites' ) ) {
	require( dirname( __FILE__ ) . '/classes/class-wp-idea-stream-rewrites.php' );
}

/** Template Loader class *****************************************************/

if ( ! class_exists( 'WP_Idea_Stream_Template_Loader' ) ) {
	require( dirname( __FILE__ ) . '/classes/class-wp-idea-stream-template-loader.php' );
}

/** Loop **********************************************************************/

if ( ! class_exists( 'WP_Idea_Stream_Loop' ) ) {
	require( dirname( __FILE__ ) . '/classes/class-wp-idea-stream-loop.php' );
}

/** Nav Widget ****************************************************************/

if ( ! class_exists( 'WP_Idea_Stream_Navig' ) ) {
	require( dirname( __FILE__ ) . '/classes/class-wp-idea-stream-navig.php' );
}
