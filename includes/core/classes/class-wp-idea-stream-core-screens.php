<?php
/**
 * WP Idea Stream Screens Class.
 *
 * @package WP Idea Stream\core\classes
 *
 * @since 2.4.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Replace the content when in an idea stream part
 *
 * @since  2.2.0
 */
class WP_Idea_Stream_Core_Screens {
	public function __construct( $template_args = null ) {
		if ( ! empty( $template_args ) ) {
			$this->template_args = $template_args;
		}

		add_filter( 'the_content', array( $this, 'replace_the_content' ), 10, 1 );
	}

	public static function start( $context, $template_args ) {
		$wp_idea_stream = wp_idea_stream();

		if ( empty( $wp_idea_stream->screens ) ) {
			$wp_idea_stream->screens = new self( $template_args );
		}

		return $wp_idea_stream->screens;
	}

	public function replace_the_content( $content ) {
		if ( 'single-idea' === $this->template_args['context'] ) {
			// Do not filter the content inside the document header
			if ( doing_action( 'wp_head' ) ) {
				return $content;
			}

			$content = wp_idea_stream_buffer_single_idea( $content );
		} else {
			$content = wp_idea_stream_buffer_template_part( $this->template_args['template_slug'], $this->template_args['template_name'], false );
		}

		return $content;
	}
}
