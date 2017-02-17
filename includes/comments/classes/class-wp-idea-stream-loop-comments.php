<?php
/**
 * WP Idea Stream Comments Loop Class.
 *
 * @package WP Idea Stream\comments\classes
 *
 * @since 2.4.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Comments loop Class.
 *
 * @since 2.0.0
 */
class WP_Idea_Stream_Loop_Comments extends WP_Idea_Stream_Loop {

	/**
	 * Constructor
	 *
	 * @since 2.0.0
	 *
	 * @param  array $args the loop args
	 */
	public function __construct( $args = array() ) {

		$default = array(
			'post_status' => 'publish',
			'status'      => 'approve',
			'user_id'     => 0,
			'number'      => wp_idea_stream_ideas_per_page(),
		);

		// All post status if user is viewing his profile
		if ( wp_idea_stream_is_current_user_profile() || current_user_can( 'read_private_ideas' ) ) {
			$default['post_status'] = '';
		}

		//Merge default with requested
		$r = wp_parse_args( $args, $default );

		// Set which pagination page
		if ( get_query_var( wp_idea_stream_cpage_rewrite_id() ) ) {
			$paged = get_query_var( wp_idea_stream_cpage_rewrite_id() );

		} else if ( ! empty( $_GET[ wp_idea_stream_cpage_rewrite_id() ] ) ) {
			$paged = absint( $_GET[ wp_idea_stream_cpage_rewrite_id() ] );

		} else if ( ! empty( $r['page'] ) ) {
			$paged = absint( $r['page'] );

		// Set default page (first page)
		} else {
			$paged = 1;
		}

		$comments_args = array(
			'post_type'   => wp_idea_stream_get_post_type(),
			'post_status' => $r['post_status'],
			'status'      => $r['status'],
			'user_id'     => (int) $r['user_id'],
			'number'      => (int) $r['number'],
			'offset'      => intval( ( $paged - 1 ) * $r['number'] ),
			'page'        => (int) $paged,
		);

		if ( ! empty( $comments_args ) ) {
			foreach ( $comments_args as $key => $value ) {
				$this->{$key} = $value;
			}
		} else {
			return false;
		}

		if ( empty( $this->user_id ) ) {
			$comment_count = 0;
		} else {
			$comment_count = wp_idea_stream_comments_count_comments( $this->user_id );
		}

		// Get the comments
		$comments = get_comments( $comments_args );

		if ( ! empty( $comments ) ) {
			$post_ids = wp_list_pluck( $comments, 'comment_post_ID' );

			// Get all posts in the object cache.
			$posts = get_posts( array( 'include' => $post_ids, 'post_type' => wp_idea_stream_get_post_type() ) );

			// Reset will need to be done at the end of the loop
			wp_idea_stream_set_idea_var( 'needs_reset', true );

			// Build a new post array indexed by post ID
			$p = array();
			foreach ( $posts as $post ) {
				$p[ $post->ID ] = $post;
			}

			// Attach the corresponding post to each comment
			foreach ( $comments as $key => $comment ) {
				if ( ! empty( $p[ $comment->comment_post_ID ] ) ) {
					$comments[ $key ]->idea = $p[ $comment->comment_post_ID ];
				}
			}
		}

		$params = array(
			'plugin_prefix'    => 'wp_idea_stream',
			'item_name'        => 'comment',
			'item_name_plural' => 'comments',
			'items'            => $comments,
			'total_item_count' => $comment_count,
			'page'             => $this->page,
			'per_page'         => $this->number,
		);

		$paginate_args = array();

		if ( ! wp_idea_stream_is_pretty_links() ) {
			$paginate_args['base'] = add_query_arg( wp_idea_stream_cpage_rewrite_id(), '%#%' );
		} else {
			$paginate_args['base']   = trailingslashit( wp_idea_stream_users_get_displayed_profile_url( 'comments') ) . '%_%';
			$paginate_args['format'] = wp_idea_stream_cpage_slug() . '/%#%/';
		}

		parent::start( $params, apply_filters( 'wp_idea_stream_comments_pagination_args', $paginate_args ) );
	}
}
