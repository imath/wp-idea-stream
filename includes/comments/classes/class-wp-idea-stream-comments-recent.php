<?php
/**
 * WP Idea Stream Recent Comments Widget Class.
 *
 * @package WP Idea Stream\comments\classes
 *
 * @since 2.4.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Recent comment about ideas Widget
 *
 * @since 2.0.0
 */
 class WP_Idea_Stream_Comments_Recent extends WP_Widget_Recent_Comments {

 	/**
	 * Constructor
	 *
	 * @since 2.0.0
	 */
	public function __construct() {
		$widget_ops = array( 'classname' => 'widget_ideas_recent_comments', 'description' => __( 'Latest comments about ideas', 'wp-idea-stream' ) );
		WP_Widget::__construct( 'idea-recent-comments', $name = __( 'WP Idea Stream latest comments', 'wp-idea-stream' ), $widget_ops );

		$this->alt_option_name = 'widget_ideas_recent_comments';

		if ( is_active_widget( false, false, $this->id_base ) ) {
			add_action( 'wp_head', array( $this, 'recent_comments_style' ) );
		}
	}

	/**
	 * Register the widget
	 *
	 * @since 2.0.0
	 */
	public static function register_widget() {
		register_widget( 'WP_Idea_Stream_Comments_Recent' );
	}

	/**
	 * Override comments query args to only onclude comments about ideas
	 *
	 * @since 2.0.0
	 *
	 * @param  array  $comment_args
	 * @return array  the comments query args to display comments about ideas
	 */
	public function override_comment_args( $comment_args = array() ) {
		// It's that simple !!
		$comment_args['post_type'] = wp_idea_stream_get_post_type();

		// Now return these args
		return $comment_args;
	}

	/**
	 * Dispplay the widget.
	 *
	 * @since 2.0.0
	 * 
	 * @param  array $args
	 * @param  array $instance
	 */
	public function widget( $args, $instance ) {
		/**
		 * Add filter so that post type used is ideas but before the dummy var
		 * @see WP_Idea_Stream_Comments::comments_widget_dummy_var()
		 */
		add_filter( 'widget_comments_args', array( $this, 'override_comment_args' ), 5, 1 );

		parent::widget( $args, $instance );

		/**
		 * Once done we need to remove the filter
		 */
		remove_filter( 'widget_comments_args', array( $this, 'override_comment_args' ), 5, 1 );
	}

	/**
	 * Update the preferences for the widget
	 *
	 * @since 2.0.0
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['number'] = absint( $new_instance['number'] );

		$alloptions = wp_cache_get( 'alloptions', 'options' );
		if ( isset( $alloptions[ 'widget_ideas_recent_comments'] ) ) {
			delete_option( 'widget_ideas_recent_comments' );
		}

		return $instance;
	}
}