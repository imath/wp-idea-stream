<?php
/**
 * WP Idea Stream Ideas loop Class.
 *
 * @package WP Idea Stream\ideas\classes
 *
 * @since 2.4.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Ideas loop Class.
 *
 * @since 2.0.0
 */
class WP_Idea_Stream_Loop_Ideas extends WP_Idea_Stream_Loop {

	/**
	 * Constructor
	 *
	 * @since 2.0.0
	 *
	 * @param  array $args the loop args
	 */
	public function __construct( $args = array() ) {

		if ( ! empty( $args ) && empty( $args['is_widget'] ) ) {
			$paged = get_query_var( 'paged' );

			// Set which pagination page
			if ( ! empty( $paged ) ) {
				$args['page'] = $paged;

			// Checking query string just in case
			} else if ( ! empty( $_GET['paged'] ) ) {
				$args['page'] = absint( $_GET['paged'] );

			// Checking in page args
			} else if ( ! empty( $args['page'] ) ) {
				$args['page'] = absint( $args['page'] );

			// Default to first page
			} else {
				$args['page'] = 1;
			}
		}

		// Only get the idea requested
		if ( ! empty( $args['idea_name'] ) ) {

			$query_loop = wp_idea_stream_get_idea_var( 'query_loop' );

			if ( empty( $query_loop->idea ) ) {
				$idea  = wp_idea_stream_ideas_get_idea_by_name( $args['idea_name'] );
			} else {
				$idea  = $query_loop->idea;
			}

			// can't do this too ealy
			$reset_data = array_merge( (array) $idea, array( 'is_page' => true ) );
			wp_idea_stream_reset_post( $reset_data );

			// this needs a "reset postdata"!
			wp_idea_stream_set_idea_var( 'needs_reset', true );

			$ideas = array(
				'ideas'    => array( $idea ),
				'total'    => 1,
				'get_args' => array(
					'page'     => 1,
					'per_page' => 1,
				),
			);

		// Get the ideas
		} else {
			$ideas = wp_idea_stream_ideas_get_ideas( $args );
		}

		if ( ! empty( $ideas['get_args'] ) ) {
			foreach ( $ideas['get_args'] as $key => $value ) {
				$this->{$key} = $value;
			}
		} else {
			return false;
		}

		$params = array(
			'plugin_prefix'    => 'wp_idea_stream',
			'item_name'        => 'idea',
			'item_name_plural' => 'ideas',
			'items'            => $ideas['ideas'],
			'total_item_count' => $ideas['total'],
			'page'             => $this->page,
			'per_page'         => $this->per_page,
		);

		$paginate_args = array();

		// No pretty links
		if ( ! wp_idea_stream_is_pretty_links() ) {
			$paginate_args['base'] = add_query_arg( 'paged', '%#%' );

		} else {

			// Is it the main archive page ?
			if ( wp_idea_stream_is_idea_archive() ) {
				$base = trailingslashit( wp_idea_stream_get_root_url() ) . '%_%';

			// Or the category archive page ?
			} else if ( wp_idea_stream_is_category() ) {
				$base = trailingslashit( wp_idea_stream_get_category_url() ) . '%_%';

			// Or the tag archive page ?
			} else if ( wp_idea_stream_is_tag() ) {
				$base = trailingslashit( wp_idea_stream_get_tag_url() ) . '%_%';

			// Or the displayed user rated ideas ?
			} else if ( wp_idea_stream_is_user_profile_rates() ) {
				$base = trailingslashit( wp_idea_stream_users_get_displayed_profile_url( 'rates' ) ) . '%_%';

			// Or the displayed user published ideas ?
			} else if ( wp_idea_stream_is_user_profile_ideas() ) {
				$base = trailingslashit( wp_idea_stream_users_get_displayed_profile_url() ) . '%_%';

			// Or nothing i've planed ?
			} else {

				/**
				 * Create your own pagination base if not handled by the plugin
				 *
				 * @param string empty string
				 */
				$base = apply_filters( 'wp_idea_stream_ideas_pagination_base', '' );
			}

			$paginate_args['base']   = $base;
			$paginate_args['format'] = wp_idea_stream_paged_slug() . '/%#%/';

			if ( wp_idea_stream_is_front_page() && ( wp_idea_stream_is_idea_archive() || wp_idea_stream_get_idea_var( 'is_front_ideas' ) ) ) {
				$base_url = home_url();

				$paginate_args['base'] = trailingslashit( $base_url ) . '%_%';
				$paginate_slug = trim( str_replace( $base_url, '', wp_idea_stream_get_root_url() ), '/' );
				$paginate_args['format'] = trailingslashit( $paginate_slug ) . wp_idea_stream_paged_slug() . '/%#%/';
			}
		}

		// Is this a search ?
		if ( wp_idea_stream_get_idea_var( 'is_search' ) ) {
			$paginate_args['add_args'] = array( wp_idea_stream_search_rewrite_id() => $_GET[ wp_idea_stream_search_rewrite_id() ] );
		}

		// Do we have a specific order to use ?
		$orderby = wp_idea_stream_get_idea_var( 'orderby' );

		if ( ! empty( $orderby ) && 'date' != $orderby ) {
			$merge = array();

			if ( ! empty( $paginate_args['add_args'] ) ) {
				$merge = $paginate_args['add_args'];
			}
			$paginate_args['add_args'] = array_merge( $merge, array( 'orderby' => $orderby ) );
		}

		/**
		 * Use this filter to override the pagination
		 *
		 * @param array $paginate_args the pagination arguments
		 */
		parent::start( $params, apply_filters( 'wp_idea_stream_ideas_pagination_args', $paginate_args ) );
	}
}