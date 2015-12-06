<?php
/**
 * WP Idea Stream Ideas tags.
 *
 * template tags specific to ideas
 *
 * @package WP Idea Stream
 * @subpackage ideas/tags
 *
 * @since 2.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/** Ideas Main nav ************************************************************/

/**
 * Displays the Ideas Search form
 *
 * @package WP Idea Stream
 * @subpackage ideas/tags
 *
 * @since 2.0.0
 *
 * @uses   get_query_var() to get the search terms
 * @uses   wp_idea_stream_search_rewrite_id() to get the search rewrite id
 * @uses   esc_html() to sanitize output value
 * @uses   wp_idea_stream_is_pretty_links() to check if a custom permalink structure is set
 * @uses   wp_idea_stream_get_post_type() to get the post type identifier
 * @uses   wp_idea_stream_get_root_url() to get the main ideas archive page url
 * @uses   esc_url() to sanitize the url
 * @uses   esc_attr() to sanitize attributes
 * @uses   apply_filters() call 'wp_idea_stream_ideas_search_form_action_url' to override the base url
 *                         call 'wp_idea_stream_ideas_search_form' to override the search form output
 * @return string Output for the search form.
 */
function wp_idea_stream_ideas_search_form() {
	$placeholder = __( 'Search Ideas', 'wp-idea-stream' );
	$search_value = get_query_var( wp_idea_stream_search_rewrite_id() );
	$action = '';
	$hidden = '';

	if ( ! empty( $search_value ) ) {
		$search_value = esc_html( $search_value );
	}

	if ( ! wp_idea_stream_is_pretty_links() ) {
		$hidden = "\n" . '<input type="hidden" name="post_type" value="' . wp_idea_stream_get_post_type() . '"/>';
	} else {
		$action = apply_filters( 'wp_idea_stream_ideas_search_form_action_url', wp_idea_stream_get_root_url() );
	}

	$search_form_html = '<form action="' . esc_url( $action ) . '" method="get" id="ideas-search-form" class="nav-form">' . $hidden;
	$search_form_html .= '<label><input type="text" name="' . wp_idea_stream_search_rewrite_id() . '" id="ideas-search-box" placeholder="'. esc_attr( $placeholder ) .'" value="' . $search_value . '" /></label>';
	$search_form_html .= '<input type="submit" id="ideas-search-submit" value="'. esc_attr__( 'Search', 'wp-idea-stream' ) .'" /></form>';

	echo apply_filters( 'wp_idea_stream_ideas_search_form', $search_form_html );
}

/**
 * Displays the Orderby form
 *
 * @package WP Idea Stream
 * @subpackage ideas/tags
 *
 * @since 2.0.0
 *
 * @uses   wp_idea_stream_ideas_get_order_options() to get available orders
 * @uses   get_query_var() to get the selected order
 * @uses   wp_idea_stream_get_category() to get ideas category rewrite id (which is also its identifier)
 * @uses   wp_idea_stream_get_tag() to get ideas tag rewrite id (which is also its identifier)
 * @uses   esc_html() to sanitize output value
 * @uses   wp_idea_stream_is_pretty_links() to check if a custom permalink structure is set
 * @uses   wp_idea_stream_get_post_type() to get the post type identifier
 * @uses   wp_idea_stream_get_tag_url() to get the url to the current tag
 * @uses   wp_idea_stream_get_category_url() to get the url to the current category
 * @uses   wp_idea_stream_get_root_url() to get the main ideas archive page url
 * @uses   esc_url() to sanitize the url
 * @uses   esc_attr() to sanitize attributes
 * @uses   selected() to add the selected attribute to the selected option
 * @uses   apply_filters() call 'wp_idea_stream_ideas_order_form_action_url' to override the base url
 *                         call 'wp_idea_stream_ideas_order_form' to override the orderby form output
 * @return string Output for the search form.
 */
function wp_idea_stream_ideas_order_form() {
	$order_options = wp_idea_stream_ideas_get_order_options();
	$order_value   = get_query_var( 'orderby' );
	$category      = get_query_var( wp_idea_stream_get_category() );
	$tag           = get_query_var( wp_idea_stream_get_tag() );
	$action        = '';
	$hidden        = '';

	if ( ! empty( $order_value ) ) {
		$order_value = esc_html( $order_value );
	} else {
		$order_value = 'date';
	}

	if ( ! wp_idea_stream_is_pretty_links() ) {
		if ( ! empty( $category ) ) {
			$hidden = "\n" . '<input type="hidden" name="' . esc_attr( wp_idea_stream_get_category() ). '" value="' . $category . '"/>';
		} else if ( ! empty( $tag ) ) {
			$hidden = "\n" . '<input type="hidden" name="' . esc_attr( wp_idea_stream_get_tag() ). '" value="' . $tag . '"/>';
		} else {
			$hidden = "\n" . '<input type="hidden" name="post_type" value="' . wp_idea_stream_get_post_type() . '"/>';
		}

	// We need to set the action url
	} else {
		// Viewing tags
		if ( wp_idea_stream_is_tag() ) {
			$action = wp_idea_stream_get_tag_url( $tag );

		// Viewing categgories
		} else if ( wp_idea_stream_is_category() ) {
			$action = wp_idea_stream_get_category_url( $category );

		// Defaults to roout url
		} else {
			$action = wp_idea_stream_get_root_url();
		}

		/**
		 * @param string $action the action form attribute
		 * @param string the current category term slug if set
		 * @param string the current tag term slug if set
		 */
		$action = apply_filters( 'wp_idea_stream_ideas_order_form_action_url', $action, $category, $tag );
	}

	$order_form_html = '<form action="' . esc_url( $action ) . '" method="get" id="ideas-order-form" class="nav-form">' . $hidden;
	$order_form_html .= '<label><select name="orderby" id="ideas-order-box">';

	foreach ( $order_options as $query_var => $label ) {
		$order_form_html .= '<option value="' . esc_attr( $query_var ) . '" ' . selected( $order_value, $query_var, false ) . '>' . esc_html( $label) . '</option>';
	}

	$order_form_html .= '</select></label>';
	$order_form_html .= '<input type="submit" id="ideas-order-submit" value="'. esc_attr__( 'Sort', 'wp-idea-stream' ) .'" /></form>';

	echo apply_filters( 'wp_idea_stream_ideas_order_form', $order_form_html );
}

/**
 * Displays the current term description if it exists
 *
 * @package WP Idea Stream
 * @subpackage ideas/tags
 *
 * @since 2.0.0
 *
 * @uses   wp_idea_stream_is_category() to check if a category about ideas is displayed
 * @uses   wp_idea_stream_is_tag() to check if a tag about ideas is displayed
 * @uses   wp_idea_stream_get_current_term() to get the current term in the displayed taxonomy
 * @return string Output for the current term description.
 */
function wp_idea_stream_ideas_taxonomy_description() {

	if ( wp_idea_stream_is_category() || wp_idea_stream_is_tag() ) {
		$term = wp_idea_stream_get_current_term();

		if ( ! empty( $term->description ) ) {
			?>
			<p class="idea-term-description"><?php echo esc_html( $term->description ) ; ?></p>
			<?php
		}
	}
}

/** Idea Loop *****************************************************************/

/**
 * Initialize the ideas loop.
 *
 * @package WP Idea Stream
 * @subpackage ideas/tags
 *
 * @since 2.0.0
 *
 * @param array $args {
 *     Arguments for customizing ideas retrieved in the loop.
 *     Arguments must be passed as an associative array
 *     @type int 'author' to restrict the loop to one author
 *     @type int 'per_page' Number of results per page.
 *     @type int 'page' the page of results to display.
 *     @type string 'search' to limit the query to ideas containing the requested search terms
 *     @type array|string 'exclude' Array or comma separated list of idea IDs to exclude
 *     @type array|string 'include' Array or comma separated list of idea IDs to include
 *     @type string 'orderby' to customize the sorting order type for the ideas (default is by date)
 *     @type string 'order' the way results should be sorted : 'DESC' or 'ASC' (default is DESC)
 *     @type array 'meta_query' Limit ideas regarding their post meta by passing an array of
 *           meta_query conditions. See {@link WP_Meta_Query->queries} for a
 *           description of the syntax.
 *     @type array 'tax_query' Limit ideas regarding their terms by passing an array of
 *           tax_query conditions. See {@link WP_Tax_Query->queries} for a
 *           description of the syntax.
 *     @type string 'idea_name' Limit results by a the post name of the idea.
 *     @type bool 'is_widget' is the query performed inside a widget ?
 * }
 * @uses   wp_parse_args() to merge args with defaults
 * @uses   wp_idea_stream_search_rewrite_id() to get the search rewrite id
 * @uses   wp_idea_stream_is_user_profile_ideas() to check if a user's profile is displayed
 * @uses   wp_idea_stream_users_displayed_user_id() to get the ID of the displayed user.
 * @uses   wp_idea_stream_ideas_per_page() to get the pagination preferences
 * @uses   WP_Idea_Stream_Loop_Ideas to get the ideas matching arguments
 * @uses   wp_idea_stream() to get plugin's main instance
 * @uses   apply_filters() call 'wp_idea_stream_ideas_has_ideas' to choose whether to init the loop or not
 * @return bool         true if ideas were found, false otherwise
 */
function wp_idea_stream_ideas_has_ideas( $args = array() ) {
	if ( ! is_array( $args ) ) {
		$args = wp_parse_args( $args, array() );
	}

	$template_args = array();

	/**
	 * We have arguments, so let's override the main query
	 */
	if ( ! empty( $args ) ) {
		$search_terms = '';

		if ( isset( $_GET[ wp_idea_stream_search_rewrite_id() ] ) ) {
			$search_terms = stripslashes( $_GET[ wp_idea_stream_search_rewrite_id() ] );
		}

		$r = wp_parse_args( $args, array(
			'author'     => wp_idea_stream_is_user_profile_ideas() ? wp_idea_stream_users_displayed_user_id() : '',
			'per_page'   => wp_idea_stream_ideas_per_page(),
			'page'       => 1,
			'search'     => '',
			'exclude'    => '',
			'include'    => '',
			'orderby'    => 'date',
			'order'      => 'DESC',
			'meta_query' => array(),
			'tax_query'  => array(),
			'idea_name'  => '',
			'is_widget'  => false
		) );

		$template_args = array(
			'author'     => (int) $r['author'],
			'per_page'   => (int) $r['per_page'],
			'page'       => (int) $r['page'],
			'search'     => $r['search'],
			'exclude'    => $r['exclude'],
			'include'    => $r['include'],
			'orderby'    => $r['orderby'],
			'order'      => $r['order'],
			'meta_query' => (array) $r['meta_query'],
			'tax_query'  => (array) $r['tax_query'],
			'idea_name'  => $r['idea_name'],
			'is_widget'  => (bool) $r['is_widget'],
		);
	}

	// Get the ideas
	$query_loop = new WP_Idea_Stream_Loop_Ideas( $template_args );

	// Setup the global query loop
	wp_idea_stream()->query_loop = $query_loop;

	/**
	 * @param  bool   true if ideas were found, false otherwise
	 * @param  object $query_loop the ideas loop
	 * @param  array  $template_args arguments used to build the loop
	 * @param  array  $args requested arguments
	 */
	return apply_filters( 'wp_idea_stream_ideas_has_ideas', $query_loop->has_items(), $query_loop, $template_args, $args );
}

/**
 * Get the Ideas returned by the template loop.
 *
 * @package WP Idea Stream
 * @subpackage ideas/tags
 *
 * @since 2.0.0
 *
 * @uses   wp_idea_stream() to get plugin's main instance
 * @return array List of Ideas.
 */
function wp_idea_stream_ideas_the_ideas() {
	return wp_idea_stream()->query_loop->items();
}

/**
 * Get the current Idea object in the loop.
 *
 * @package WP Idea Stream
 * @subpackage ideas/tags
 *
 * @since 2.0.0
 *
 * @uses   wp_idea_stream() to get plugin's main instance
 * @return object The current Idea within the loop.
 */
function wp_idea_stream_ideas_the_idea() {
	return wp_idea_stream()->query_loop->the_item();
}

/** Loop Output ***************************************************************/
// Mainly inspired by The BuddyPress notifications loop

/**
 * Displays a message in case no idea was found
 *
 * @package WP Idea Stream
 * @subpackage ideas/tags
 *
 * @since 2.0.0
 *
 * @uses wp_idea_stream_ideas_get_not_found() to get the message
 */
function wp_idea_stream_ideas_not_found() {
	echo wp_idea_stream_ideas_get_not_found();
}

	/**
	 * Gets a message in case no idea was found
	 *
	 * @package WP Idea Stream
	 * @subpackage ideas/tags
	 *
	 * @since 2.0.0
	 *
	 * @uses   wp_idea_stream_is_user_profile() to check if on a user's profile
	 * @uses   wp_idea_stream_is_user_profile_rates() to check if the rates part of a user's profile
	 * @uses   wp_idea_stream_users_get_displayed_user_displayname() to get the displayed user's display name
	 * @uses   wp_idea_stream_is_category() to check if a category is displayed
	 * @uses   wp_idea_stream_is_tag() to check if a tag is displayed
	 * @uses   wp_idea_stream_is_search() to check if a search is being performed
	 * @uses   wp_idea_stream_is_orderby() to check if a specific order is being requested
	 * @uses   wp_idea_stream_user_can() to check for user's capability
	 * @uses   wp_idea_stream_get_form_url() to get the form url to add new ideas
	 * @uses   apply_filters() call 'wp_idea_stream_ideas_get_not_found' to override the output
	 * @return string the message to output
	 */
	function wp_idea_stream_ideas_get_not_found() {
		// general feedback
		$output = esc_html__( 'It looks like no idea has been submitted yet, please sign in or sign up to add yours!', 'wp-idea-stream' );

		if ( wp_idea_stream_is_user_profile() ) {

			if ( ! wp_idea_stream_is_user_profile_rates() ) {
				$output = sprintf(
					__( 'It looks like %s has not submitted any idea yet', 'wp-idea-stream' ),
					wp_idea_stream_users_get_displayed_user_displayname()
				);
			// We're viewing the idea the user rated
			} else {
				$output = sprintf(
					__( 'It looks like %s has not rated any idea yet', 'wp-idea-stream' ),
					wp_idea_stream_users_get_displayed_user_displayname()
				);
			}

		} else if ( wp_idea_stream_is_category() ) {
			$output = __( 'It looks like no idea has been published in this category yet', 'wp-idea-stream' );

		} else if ( wp_idea_stream_is_tag() ) {
			$output = __( 'It looks like no idea has been marked with this tag yet', 'wp-idea-stream' );

		} else if ( wp_idea_stream_is_search() ) {
			$output = __( 'It looks like no idea match your search terms.', 'wp-idea-stream' );

		} else if ( wp_idea_stream_is_search() ) {
			$output = __( 'It looks like no idea match your search terms.', 'wp-idea-stream' );

		} else if ( wp_idea_stream_is_orderby( 'rates_count' ) ) {
			$output = __( 'It looks like no idea has been rated yet.', 'wp-idea-stream' );

		} else if ( wp_idea_stream_user_can( 'publish_ideas' ) ) {
			$output = sprintf(
				__( 'It looks like no idea has been submitted yet, <a href="%s" title="Submit your idea">add yours</a>', 'wp-idea-stream' ),
				esc_url( wp_idea_stream_get_form_url() )
			);
		}

		/**
		 * @param  string $output the message to output
		 */
		return apply_filters( 'wp_idea_stream_ideas_get_not_found', $output );
	}

/**
 * Output the pagination count for the current Ideas loop.
 *
 * @package WP Idea Stream
 * @subpackage ideas/tags
 *
 * @since 2.0.0
 *
 * @uses  wp_idea_stream_ideas_get_pagination_count() to get the pagination count
 */
function wp_idea_stream_ideas_pagination_count() {
	echo wp_idea_stream_ideas_get_pagination_count();
}
	/**
	 * Return the pagination count for the current Ideas loop.
	 *
	 * @package WP Idea Stream
	 * @subpackage ideas/tags
	 *
	 * @since 2.0.0
	 *
	 * @uses   wp_idea_stream() to get plugin's main instance
	 * @uses   number_format_i18n() to format numbers
	 * @uses   apply_filters() call 'wp_idea_stream_ideas_get_pagination_count' to override the output
	 * @return string HTML for the pagination count.
	 */
	function wp_idea_stream_ideas_get_pagination_count() {
		$query_loop = wp_idea_stream()->query_loop;
		$start_num  = intval( ( $query_loop->page - 1 ) * $query_loop->per_page ) + 1;
		$from_num   = number_format_i18n( $start_num );
		$to_num     = number_format_i18n( ( $start_num + ( $query_loop->per_page - 1 ) > $query_loop->total_idea_count ) ? $query_loop->total_idea_count : $start_num + ( $query_loop->per_page - 1 ) );
		$total      = number_format_i18n( $query_loop->total_idea_count );
		$pag        = sprintf( _n( 'Viewing %1$s to %2$s (of %3$s ideas)', 'Viewing %1$s to %2$s (of %3$s ideas)', $total, 'wp-idea-stream' ), $from_num, $to_num, $total );

		/**
		 * @param  string $pag the pagination count to output
		 */
		return apply_filters( 'wp_idea_stream_ideas_get_pagination_count', $pag );
	}

/**
 * Output the pagination links for the current Ideas loop.
 *
 * @package WP Idea Stream
 * @subpackage ideas/tags
 *
 * @since 2.0.0
 *
 * @uses  wp_idea_stream_ideas_get_pagination_links() to get pagination links
 */
function wp_idea_stream_ideas_pagination_links() {
	echo wp_idea_stream_ideas_get_pagination_links();
}

	/**
	 * Return the pagination links for the current Rendez Vous loop.
	 *
	 * @package WP Idea Stream
	 * @subpackage ideas/tags
	 *
	 * @since 2.0.0
	 *
	 * @uses   wp_idea_stream() to get plugin's main instance
	 * @uses   apply_filters() call 'wp_idea_stream_ideas_get_pagination_links' to override the output
	 * @return string output for the pagination links.
	 */
	function wp_idea_stream_ideas_get_pagination_links() {
		/**
		 * @param  string the pagination links to output
		 */
		return apply_filters( 'wp_idea_stream_ideas_get_pagination_links', wp_idea_stream()->query_loop->pag_links );
	}

/**
 * Output the ID of the idea currently being iterated on.
 *
 * @package WP Idea Stream
 * @subpackage ideas/tags
 *
 * @since 2.0.0
 *
 * @uses  wp_idea_stream_ideas_get_id() to get the ID
 */
function wp_idea_stream_ideas_the_id() {
	echo wp_idea_stream_ideas_get_id();
}

	/**
	 * Return the ID of the Idea currently being iterated on.
	 *
	 * @package WP Idea Stream
	 * @subpackage ideas/tags
	 *
	 * @since 2.0.0
	 *
	 * @uses   wp_idea_stream() to get plugin's main instance
	 * @uses   apply_filters() call 'wp_idea_stream_ideas_get_id' to override the output
	 * @return int ID of the current Idea.
	 */
	function wp_idea_stream_ideas_get_id() {
		/**
		 * @param  int the idea ID to output
		 */
		return apply_filters( 'wp_idea_stream_ideas_get_id', wp_idea_stream()->query_loop->idea->ID );
	}

/**
 * Checks if the Idea being iterated on is sticky
 *
 * @package WP Idea Stream
 * @subpackage ideas/tags
 *
 * @since 2.0.0
 *
 * @uses   wp_idea_stream() to get plugin's main instance
 * @uses   wp_idea_stream_is_idea_archive() to check if on an idea archive page
 * @uses   wp_idea_stream_get_idea_var() to check if order is not default
 * @uses   wp_idea_stream_is_search() to check if a search is being performed
 * @uses   wp_idea_stream_is_sticky_enabled() to check if sticky feature is enabled
 * @uses   wp_idea_stream_ideas_is_sticky() to check if the idea is sticky
 * @return bool True if the Idea being iterating on is sticky, false otherwise
 */
function wp_idea_stream_ideas_is_sticky_idea() {
	$query_loop = wp_idea_stream()->query_loop;
	$idea = $query_loop->idea;

	if ( ! wp_idea_stream_is_idea_archive() || wp_idea_stream_get_idea_var( 'orderby' ) || wp_idea_stream_is_search() ) {
		return;
	}

	if ( empty( $query_loop->page ) || ( ! empty( $query_loop->page ) && 1 < $query_loop->page ) ) {
		return;
	}

	// Bail if sticky is disabled
	if ( ! wp_idea_stream_is_sticky_enabled() ) {
		return;
	}

	if ( ! empty( $idea->is_sticky ) ) {
		return true;
	} else {
		return wp_idea_stream_ideas_is_sticky( $idea->ID );
	}
}

/**
 * Output the row classes of the Idea being iterated on.
 *
 * @package WP Idea Stream
 * @subpackage ideas/tags
 *
 * @since 2.0.0
 *
 * @uses  wp_idea_stream_ideas_get_classes() to get the row classes
 */
function wp_idea_stream_ideas_the_classes() {
	echo wp_idea_stream_ideas_get_classes();
}

	/**
	 * Gets the row classes for the Idea being iterated on
	 *
	 * @package WP Idea Stream
	 * @subpackage ideas/tags
	 *
	 * @since 2.0.0
	 *
	 * @uses   wp_idea_stream_ideas_is_sticky_idea() to check if the idea is sticky
	 * @uses   apply_filters() call 'wp_idea_stream_ideas_get_classes' to add/remove row classes
	 * @return string output the row class attribute
	 */
	function wp_idea_stream_ideas_get_classes() {
		$classes = array( 'idea' );

		if ( wp_idea_stream_ideas_is_sticky_idea() ) {
			$classes[] = 'sticky-idea';
		}

		/**
		 * @param  array $classes the idea row classes
		 */
		$classes = apply_filters( 'wp_idea_stream_ideas_get_classes', $classes );

		return 'class="' . join( ' ', $classes ) . '"';
	}

/**
 * Output the author avatar of the Idea being iterated on.
 *
 * @package WP Idea Stream
 * @subpackage ideas/tags
 *
 * @since 2.0.0
 *
 * @uses  wp_idea_stream_ideas_get_author_avatar() to get the author avatar
 */
function wp_idea_stream_ideas_the_author_avatar() {
	echo wp_idea_stream_ideas_get_author_avatar();
}

	/**
	 * Gets the author avatar
	 *
	 * @package WP Idea Stream
	 * @subpackage ideas/tags
	 *
	 * @since 2.0.0
	 *
	 * @uses   wp_idea_stream() to get plugin's main instance
	 * @uses   get_avatar() to get author's avatar
	 * @uses   wp_idea_stream_users_get_user_profile_url() to get author's profile url
	 * @uses   apply_filters() call 'wp_idea_stream_ideas_get_author_avatar' to override the output
	 * @return string output the author's avatar
	 */
	function wp_idea_stream_ideas_get_author_avatar() {
		$idea   = wp_idea_stream()->query_loop->idea;
		$author = $idea->post_author;
		$avatar = get_avatar( $author );
		$avatar_link = '<a href="' . esc_url( wp_idea_stream_users_get_user_profile_url( $author ) ) . '" title="' . esc_attr__( 'User&#39;s profile', 'wp-idea-stream' ) . '">' . $avatar . '</a>';

		/**
		 * @param  string  $avatar_link the avatar output
		 * @param  int     $author the author ID
		 * @param  string  $avatar the avatar
		 * @param  WP_Post $idea the idea object
		 */
		return apply_filters( 'wp_idea_stream_ideas_get_author_avatar', $avatar_link, $author, $avatar, $idea );
	}

/**
 * Prefix idea title.
 *
 * @package WP Idea Stream
 * @subpackage ideas/tags
 *
 * @since 2.0.0
 *
 * @uses  wp_idea_stream_ideas_get_before_idea_title() to get the output to add before idea title
 */
function wp_idea_stream_ideas_before_idea_title() {
	echo wp_idea_stream_ideas_get_before_idea_title();
}

	/**
	 * Gets the idea title prefix
	 *
	 * @package WP Idea Stream
	 * @subpackage ideas/tags
	 *
	 * @since 2.0.0
	 *
	 * @uses   wp_idea_stream() to get plugin's main instance
	 * @uses   wp_idea_stream_ideas_is_sticky_idea() to check if idea is sticky
	 * @uses   apply_filters() call 'wp_idea_stream_ideas_get_before_idea_title' to override the output
	 * @return string output the idea title prefix
	 */
	function wp_idea_stream_ideas_get_before_idea_title() {
		$output = '';

		if ( wp_idea_stream_ideas_is_sticky_idea() ) {
			$output = '<span class="sticky-idea"></span> ';
		}

		/**
		 * @param  string  $output the avatar output
		 * @param  int     the idea ID
		 */
		return apply_filters( 'wp_idea_stream_ideas_get_before_idea_title', $output, wp_idea_stream()->query_loop->idea->ID );
	}

/**
 * Displays idea title.
 *
 * @package WP Idea Stream
 * @subpackage ideas/tags
 *
 * @since 2.0.0
 *
 * @uses  wp_idea_stream_ideas_get_title() to get the title
 */
function wp_idea_stream_ideas_the_title() {
	echo wp_idea_stream_ideas_get_title();
}

	/**
	 * Gets the title of the idea
	 *
	 * @package WP Idea Stream
	 * @subpackage ideas/tags
	 *
	 * @since 2.0.0
	 *
	 * @uses   wp_idea_stream() to get plugin's main instance
	 * @uses   get_the_title() to get the title
	 * @uses   apply_filters() call 'wp_idea_stream_ideas_get_title' to override the output
	 * @return string output the title of the idea
	 */
	function wp_idea_stream_ideas_get_title() {
		$idea = wp_idea_stream()->query_loop->idea;
		$title = get_the_title( $idea );

		/**
		 * @param  string  $title the title to output
		 * @param  WP_Post $idea the idea object
		 */
		return apply_filters( 'wp_idea_stream_ideas_get_title', $title, $idea );
	}

/**
 * Displays idea permalink.
 *
 * @package WP Idea Stream
 * @subpackage ideas/tags
 *
 * @since 2.0.0
 *
 * @uses  wp_idea_stream_ideas_get_permalink() to get the permalink
 */
function wp_idea_stream_ideas_the_permalink() {
	echo wp_idea_stream_ideas_get_permalink();
}

	/**
	 * Gets the permalink of the idea
	 *
	 * @package WP Idea Stream
	 * @subpackage ideas/tags
	 *
	 * @since 2.0.0
	 *
	 * @uses   wp_idea_stream() to get plugin's main instance
	 * @uses   wp_idea_stream_ideas_get_idea_permalink() to get the permalink of the idea
	 * @uses   esc_url() to sanitize the url
	 * @uses   apply_filters() call 'wp_idea_stream_ideas_get_permalink' to override the output
	 * @return string output the permalink to the idea
	 */
	function wp_idea_stream_ideas_get_permalink() {
		$idea = wp_idea_stream()->query_loop->idea;
		$permalink = wp_idea_stream_ideas_get_idea_permalink( $idea );

		/**
		 * @param  string  the permalink url
		 * @param  WP_Post $idea the idea object
		 */
		return apply_filters( 'wp_idea_stream_ideas_get_permalink', esc_url( $permalink ), $idea );
	}

/**
 * Adds to idea's permalink an attribute containg the idea's title
 *
 * @package WP Idea Stream
 * @subpackage ideas/tags
 *
 * @since 2.0.0
 *
 * @uses  wp_idea_stream_ideas_get_title_attribute() to get the title attibute of the idea
 */
function wp_idea_stream_ideas_the_title_attribute() {
	echo wp_idea_stream_ideas_get_title_attribute();
}

	/**
	 * Gets the title attribute of the idea's permalink
	 *
	 * @package WP Idea Stream
	 * @subpackage ideas/tags
	 *
	 * @since 2.0.0
	 *
	 * @uses   wp_idea_stream() to get plugin's main instance
	 * @uses   esc_attr() to sanitize the attribute
	 * @uses   apply_filters() call 'wp_idea_stream_ideas_get_title_attribute' to override the output
	 * @return string output of the attribute
	 */
	function wp_idea_stream_ideas_get_title_attribute() {
		$idea = wp_idea_stream()->query_loop->idea;
		$title = '';

		if ( ! empty( $idea->post_password ) ) {
			$title = _x( 'Protected :', 'idea permalink title protected attribute', 'wp-idea-stream' ) . ' ';
		} else if ( ! empty( $idea->post_status ) && 'private' == $idea->post_status ) {
			$title = _x( 'Private :', 'idea permalink title private attribute', 'wp-idea-stream' ) . ' ';
		}

		$title .= $idea->post_title;

		/**
		 * @param  string  the title to output
		 * @param  string  the db title
		 * @param  WP_Post $idea the idea object
		 */
		return apply_filters( 'wp_idea_stream_ideas_get_title_attribute', esc_attr( $title ), $idea->post_title, $idea );
	}

/**
 * Displays the number of comments about an idea
 *
 * @package WP Idea Stream
 * @subpackage ideas/tags
 *
 * @since 2.0.0
 *
 * @uses  wp_idea_stream_ideas_get_comment_number() to get the number of comments
 */
function wp_idea_stream_ideas_the_comment_number() {
	echo wp_idea_stream_ideas_get_comment_number();
}

	/**
	 * Gets the title attribute of the idea's permalink
	 *
	 * @package WP Idea Stream
	 * @subpackage ideas/tags
	 *
	 * @since 2.0.0
	 *
	 * @param  int $id the idea ID
	 * @uses   wp_idea_stream() to get plugin's main instance
	 * @uses   get_comments_number() to get the comments number
	 * @return int the comments number
	 */
	function wp_idea_stream_ideas_get_comment_number( $id = 0 ) {
		if ( empty( $id ) ) {
			$id = wp_idea_stream()->query_loop->idea->ID;
		}

		return get_comments_number( $id );
	}

/**
 * Displays the comment link of an idea
 *
 * @package WP Idea Stream
 * @subpackage ideas/tags
 *
 * @since 2.0.0
 *
 * @param  mixed $zero       false or the text to show when idea got no comments
 * @param  mixed $one        false or the text to show when idea got one comment
 * @param  mixed $more       false or the text to show when idea got more than one comment
 * @param  string $css_class the name of the css classes to use
 * @param  mixed $none       false or the text to show when no idea comment link
 * @uses   wp_idea_stream_ideas_get_idea_comment_link() to get the number of comments
 */
function wp_idea_stream_ideas_the_idea_comment_link( $zero = false, $one = false, $more = false, $css_class = '', $none = false ) {
	echo wp_idea_stream_ideas_get_idea_comment_link( $zero, $one, $more, $css_class, $none );
}

	/**
	 * Gets the comment link of an idea
	 *
	 * @package WP Idea Stream
	 * @subpackage ideas/tags
	 *
	 * @since 2.0.0
	 *
	 * @param  mixed $zero       false or the text to show when idea got no comments
	 * @param  mixed $one        false or the text to show when idea got one comment
	 * @param  mixed $more       false or the text to show when idea got more than one comment
	 * @param  string $css_class the name of the css classes to use
	 * @param  mixed $none       false or the text to show when no idea comment link
	 * @uses   wp_idea_stream() to get plugin's main instance
	 * @uses   wp_idea_stream_ideas_get_comment_number() to get the comments number for the idea
	 * @uses   post_password_required() to check if the idea requires a password
	 * @uses   wp_idea_stream_user_can() to check for user's capability
	 * @uses   comments_open() to check if comments are open for the idea
	 * @uses   esc_attr() to sanitize the attribute
	 * @uses   wp_idea_stream_ideas_get_idea_permalink() to get the idea permalink
	 * @uses   wp_idea_stream_ideas_get_idea_comments_link() to get the idea link to comments
	 * @uses   number_format_i18n() to format numbers
	 * @uses   apply_filters() call 'wp_idea_stream_ideas_get_idea_comment_link' to override the output
	 * @return string             output for the comment link
	 */
	function wp_idea_stream_ideas_get_idea_comment_link( $zero = false, $one = false, $more = false, $css_class = '', $none = false ) {
		$output = '';
		$idea = wp_idea_stream()->query_loop->idea;

		if ( false === $zero ) {
			$zero = __( 'No Comments', 'wp-idea-stream' );
		}
		if ( false === $one ) {
			$one = __( '1 Comment', 'wp-idea-stream' );
		}
		if ( false === $more ) {
			$more = __( '% Comments', 'wp-idea-stream' );
		}
		if ( false === $none ) {
			$none = __( 'Comments Off', 'wp-idea-stream' );
		}

		$number = wp_idea_stream_ideas_get_comment_number( $idea->ID );
		$title = '';

		if ( post_password_required( $idea->ID ) ) {
			$title = _x( 'Comments are protected.', 'idea protected comments message', 'wp-idea-stream' );
			$output .= '<span class="idea-comments-protected">' . $title . '</span>';
		} else if ( ! empty( $idea->post_status ) && 'private' == $idea->post_status && ! wp_idea_stream_user_can( 'read_idea', $idea->ID ) ) {
			$title = _x( 'Comments are private.', 'idea private comments message', 'wp-idea-stream' );
			$output .= '<span class="idea-comments-private">' . $title . '</span>';
		} else if ( ! comments_open( $idea->ID ) ) {
			$output .= '<span' . ( ( ! empty( $css_class ) ) ? ' class="' . esc_attr( $css_class ) . '"' : '') . '>' . $none . '</span>';
		} else {
			$comment_link = ( 0 == $number ) ? wp_idea_stream_ideas_get_idea_permalink( $idea ) . '#respond' : wp_idea_stream_ideas_get_idea_comments_link( $idea );
			$output .= '<a href="' . esc_url( $comment_link ) . '"';

			if ( ! empty( $css_class ) ) {
				$output .= ' class="' . $css_class . '" ';
			}

			$title = esc_attr( strip_tags( $idea->post_title ) );

			$output .= ' title="' . esc_attr( sprintf( __('Comment on %s', 'wp-idea-stream'), $title ) ) . '">';

			$comment_number_output = '';

			if ( $number > 1 ) {
				$comment_number_output = str_replace( '%', number_format_i18n( $number ), $more );
			} elseif ( $number == 0 ) {
				$comment_number_output = $zero;
			} else { // must be one
				$comment_number_output = $one;
			}

			/**
			 * Filter the comments count for display just like WordPress does
			 * in get_comments_number_text()
			 *
			 * @param  string  $comment_number_output
			 * @param  int     $number
			 */
			$comment_number_output = apply_filters( 'comments_number', $comment_number_output, $number );

			$output .= $comment_number_output . '</a>';
		}

		/**
		 * @param  string  $output the comment link to output
		 * @param  int     the idea ID
		 * @param  string  $title the title attribute
		 * @param  int     $number amount of comments about the idea
		 */
		return apply_filters( 'wp_idea_stream_ideas_get_idea_comment_link', $output, $idea->ID, $title, $number );
	}

/**
 * Displays the average rating of an idea
 *
 * @package WP Idea Stream
 * @subpackage ideas/tags
 *
 * @since 2.0.0
 *
 * @uses   wp_idea_stream_ideas_get_average_rating() to get the average rating
 */
function wp_idea_stream_ideas_the_average_rating() {
	echo wp_idea_stream_ideas_get_average_rating();
}

	/**
	 * Gets the average rating of an idea
	 *
	 * @package WP Idea Stream
	 * @subpackage ideas/tags
	 *
	 * @since 2.0.0
	 *
	 * @param  int $id the idea ID
	 * @uses   wp_idea_stream() to get plugin's main instance
	 * @uses   get_post_meta() to get the average rating
	 * @return string  output for the average rating
	 */
	function wp_idea_stream_ideas_get_average_rating( $id = 0 ) {
		if ( empty( $id ) ) {
			$id = wp_idea_stream()->query_loop->idea->ID;
		}

		$rating = get_post_meta( $id, '_ideastream_average_rate', true );

		if ( ! empty( $rating ) && is_numeric( $rating ) ) {
			$rating = number_format_i18n( $rating, 1 );
		}

		return $rating;
	}

/**
 * Displays the rating link of an idea
 *
 * @package WP Idea Stream
 * @subpackage ideas/tags
 *
 * @since 2.0.0
 *
 * @param  mixed $zero       false or the text to show when idea got no rates
 * @param  mixed $more       false or the text to show when idea got one or more rates
 * @param  string $css_class the name of the css classes to use
 * @uses   wp_idea_stream_is_rating_disabled() to check if ratings are enabled
 * @uses   wp_idea_stream_is_single_idea() to check the idea is displayed on a single template
 * @uses   wp_idea_stream_ideas_get_rating_link() to get the rating link of an idea
 */
function wp_idea_stream_ideas_the_rating_link( $zero = false, $more = false, $css_class = '' ) {
	// Bail if ratings are disabled
	if ( wp_idea_stream_is_rating_disabled() ) {
		return false;
	}

	if ( wp_idea_stream_is_single_idea() ) {
		echo '<div id="rate" data-idea="' . wp_idea_stream()->query_loop->idea->ID . '"></div><div class="rating-info"></div>';
	} else {
		echo wp_idea_stream_ideas_get_rating_link( $zero, $more, $css_class );
	}
}

	/**
	 * Gets the rating link of an idea
	 *
	 * @package WP Idea Stream
	 * @subpackage ideas/tags
	 *
	 * @since 2.0.0
	 *
	 * @param  mixed $zero       false or the text to show when idea got no rates
	 * @param  mixed $more       false or the text to show when idea got one or more rates
	 * @param  string $css_class the name of the css classes to use
	 * @uses   wp_idea_stream() to get plugin's main instance
	 * @uses   post_password_required() to check if the idea requires a password
	 * @uses   wp_idea_stream_user_can() to check for user's capability
	 * @uses   wp_idea_stream_ideas_get_average_rating() to get the average rating
	 * @uses   wp_idea_stream_ideas_get_idea_permalink() to get the idea permalink
	 * @uses   esc_attr() to sanitize the attribute
	 * @uses   wp_login_url() to get the login url
	 * @uses   esc_url() to sanitize the url
	 * @uses   number_format_i18n() to format numbers
	 * @uses   apply_filters() call 'wp_idea_stream_ideas_get_rating_link' to override the output
	 * @return string             output for the rating link
	 */
	function wp_idea_stream_ideas_get_rating_link( $zero = false, $more = false, $css_class = '' ) {
		$output = '';
		$idea = wp_idea_stream()->query_loop->idea;

		// Simply dont display votes if password protected or private.
		if ( post_password_required( $idea->ID ) ) {
			return $output;
		} else if ( ! empty( $idea->post_status ) && 'private' == $idea->post_status && ! wp_idea_stream_user_can( 'read_idea', $idea->ID ) ) {
			return $output;
		}

		if ( false === $zero ) {
			$zero = __( 'Not rated yet', 'wp-idea-stream' );
		}
		if ( false === $more ) {
			$more = __( 'Average rate: %', 'wp-idea-stream' );
		}

		$average = wp_idea_stream_ideas_get_average_rating( $idea->ID );

		$rating_link = wp_idea_stream_ideas_get_idea_permalink( $idea ) . '#rate';

		$title = esc_attr( strip_tags( $idea->post_title ) );
		$title = sprintf( __('Rate %s', 'wp-idea-stream'), $title );

		if ( ! is_user_logged_in() ) {
			$rating_link = wp_login_url( $rating_link );
			$title = _x( 'Please, log in to rate.', 'idea rating not logged in message', 'wp-idea-stream' );
		}

		$output .= '<a href="' . esc_url( $rating_link ) . '"';

		if ( ! empty( $css_class ) ) {
			if ( empty( $average ) ) {
				$css_class .= ' empty';
			}
			$output .= ' class="' . $css_class . '" ';
		}

		$output .= ' title="' . esc_attr( $title ) . '">';

		if ( ! empty( $average  ) ) {
			$output .= str_replace( '%', $average, $more );
		} else {
			$output .= $zero;
		}

		$output .= '</a>';

		/**
		 * @param  string  $output the rating link to output
		 * @param  int     the idea ID
		 * @param  string  $title the title attribute
		 * @param  string  $average the average rating of an idea
		 */
		return apply_filters( 'wp_idea_stream_ideas_get_rating_link', $output, $idea->ID, $title, $average );
	}

/**
 * Displays the excerpt of an idea
 *
 * @package WP Idea Stream
 * @subpackage ideas/tags
 *
 * @since 2.0.0
 *
 * @uses   wp_idea_stream_ideas_get_excerpt() to get the excerpt
 */
function wp_idea_stream_ideas_the_excerpt() {
	echo wp_idea_stream_ideas_get_excerpt();
}

	/**
	 * Gets the excerpt of an idea
	 *
	 * @package WP Idea Stream
	 * @subpackage ideas/tags
	 *
	 * @since 2.0.0
	 *
	 * @uses   wp_idea_stream() to get plugin's main instance
	 * @uses   post_password_required() to check if the idea requires a password
	 * @uses   wp_idea_stream_user_can() to check for user's capability
	 * @uses   strip_shortcodes() to strip the shortcodes in case the excerpt field was used in Edit screen
	 * @uses   wp_idea_stream_create_excerpt() to build the excerpt
	 * @uses   apply_filters() call 'wp_idea_stream_create_excerpt_text' to override the output
	 * @return string  output for the excerpt
	 */
	function wp_idea_stream_ideas_get_excerpt() {
		$idea = wp_idea_stream()->query_loop->idea;

		// Password protected
		if ( post_password_required( $idea ) ) {
			$excerpt = __( 'This idea is password protected, you will need it to view its content.', 'wp-idea-stream' );

		// Private
		} else if ( ! empty( $idea->post_status ) && 'private' == $idea->post_status && ! wp_idea_stream_user_can( 'read_idea', $idea->ID ) ) {
			$excerpt = __( 'This idea is private, you cannot view its content.', 'wp-idea-stream' );

		// Public
		} else {
			$excerpt = strip_shortcodes( $idea->post_excerpt );
		}

		if ( empty( $excerpt ) ) {
			$excerpt = wp_idea_stream_create_excerpt( $idea->post_content, 20 );
		} else {
			/**
			 * @param  string  $excerpt the excerpt to output
			 * @param  WP_Post $idea the idea object
			 */
			$excerpt = apply_filters( 'wp_idea_stream_create_excerpt_text', $excerpt, $idea );
		}

		return $excerpt;
	}

/**
 * Displays the content of an idea
 *
 * @package WP Idea Stream
 * @subpackage ideas/tags
 *
 * @since 2.0.0
 *
 * @uses   wp_idea_stream_ideas_get_content() to get the content
 */
function wp_idea_stream_ideas_the_content() {
	echo wp_idea_stream_ideas_get_content();
}

	/**
	 * Gets the content of an idea
	 *
	 * @package WP Idea Stream
	 * @subpackage ideas/tags
	 *
	 * @since 2.0.0
	 *
	 * @uses   wp_idea_stream() to get plugin's main instance
	 * @uses   post_password_required() to check if the idea requires a password
	 * @uses   wp_idea_stream_user_can() to check for user's capability
	 * @uses   apply_filters() call 'wp_idea_stream_ideas_get_content' to override the output
	 * @return string  output for the excerpt
	 */
	function wp_idea_stream_ideas_get_content() {
		$idea = wp_idea_stream()->query_loop->idea;

		// Password protected
		if ( post_password_required( $idea ) ) {
			$content = __( 'This idea is password protected, you will need it to view its content.', 'wp-idea-stream' );

		// Private
		} else if ( ! empty( $idea->post_status ) && 'private' == $idea->post_status && ! wp_idea_stream_user_can( 'read_idea', $idea->ID ) ) {
			$content = __( 'This idea is private, you cannot view its content.', 'wp-idea-stream' );

		// Public
		} else {
			$content = $idea->post_content;
		}

		/**
		 * @param  string  $content the content to output
		 * @param  WP_Post $idea the idea object
		 */
		return apply_filters( 'wp_idea_stream_ideas_get_content', do_shortcode( $content ), $idea );
	}

/**
 * Displays the term list links
 *
 * @package WP Idea Stream
 * @subpackage ideas/tags
 *
 * @since 2.0.0
 *
 * @param   integer $id       the idea ID
 * @param   string  $taxonomy the taxonomy of the terms
 * @param   string  $before   the string to display before
 * @param   string  $sep      the separator for the term list
 * @param   string  $after    the string to display after
 * @uses    get_the_term_list() to get the term list
 * @uses    apply_filters() call 'wp_idea_stream_ideas_get_the_term_list' to override the output
 * @return  string the term list links
 */
function wp_idea_stream_ideas_get_the_term_list( $id = 0, $taxonomy = '', $before = '', $sep = ', ', $after = '' ) {
	// Bail if no idea ID or taxonomy identifier
	if ( empty( $id ) || empty( $taxonomy ) ) {
		return false;
	}

	/**
	 * @param  string  the term list
	 * @param  int $id the idea ID
	 * @param  string $taxonomy the taxonomy identifier
	 */
	return apply_filters( 'wp_idea_stream_ideas_get_the_term_list', get_the_term_list( $id, $taxonomy, $before, $sep, $after ), $id, $taxonomy );
}

/**
 * Displays a custom field in single idea's view
 *
 * @package WP Idea Stream
 * @subpackage ideas/tags
 *
 * @since 2.0.0
 *
 * @param  string $display_meta the meta field single output
 * @param  object $meta_object  the meta object
 * @param  string $context      the display context (single/form/admin)
 * @uses   wp_idea_stream_get_meta_single_display() to get the output
 */
function wp_idea_stream_meta_single_display( $display_meta = '', $meta_object = null, $context = '' ) {
	echo wp_idea_stream_get_meta_single_display( $display_meta, $meta_object, $context );
}

	/**
	 * Gets the custom field output for single idea's view
	 *
	 * @package WP Idea Stream
	 * @subpackage ideas/tags
	 *
	 * @since 2.0.0
	 *
	 * @param  string $display_meta the meta field single output
	 * @param  object $meta_object  the meta object
	 * @param  string $context      the display context (single/form/admin)
	 * @uses   esc_html() to sanitize the output
	 * @uses   apply_filters() call 'wp_idea_stream_get_meta_single_display' to override the output
	 * @return string               HTML Output
	 */
	function wp_idea_stream_get_meta_single_display( $display_meta = '', $meta_object = null, $context = '' ) {
		// Bail if no field name.
		if ( empty( $meta_object->field_name ) ) {
			return;
		}

		$output = '';

		if ( 'single' != $context ) {
			return;
		}

		$output  = '<p><strong>' . esc_html( $meta_object->label ) . '</strong> ';
		$output .= esc_html( $meta_object->field_value ) . '</p>';

		/**
		 * @param  string $output       the meta field single output
		 * @param  object $meta_object  the meta object
		 * @param  string $context      the display context (single/form/admin)
		 */
		return apply_filters( 'wp_idea_stream_get_meta_single_display', $output, $meta_object, $context );
	}

/**
 * Displays the footer of an idea
 *
 * @package WP Idea Stream
 * @subpackage ideas/tags
 *
 * @since 2.0.0
 *
 * @uses   wp_idea_stream_ideas_get_idea_footer() to get the footer
 */
function wp_idea_stream_ideas_the_idea_footer() {
	echo wp_idea_stream_ideas_get_idea_footer();
}

	/**
	 * Gets the footer of an idea
	 *
	 * @package WP Idea Stream
	 * @subpackage ideas/tags
	 *
	 * @since 2.0.0
	 *
	 * @uses   wp_idea_stream() to get plugin's main instance
	 * @uses   wp_idea_stream_ideas_get_the_term_list() to get the taxonomy term list
	 * @uses   wp_idea_stream_get_category() to get the category taxonomy identifier
	 * @uses   wp_idea_stream_get_tag() to get the tag taxonomy identifier
	 * @uses   mysql2date() to format the date
	 * @uses   wp_idea_stream_is_single_idea() to check if the idea is displayed on its single template
	 * @uses   wp_idea_stream_user_can() to check for user's capability
	 * @uses   esc_url() to sanitize url
	 * @uses   get_edit_post_link() to get the edit link of an idea
	 * @uses   wp_idea_stream_users_get_user_data() to get user's attribute
	 * @uses   wp_idea_stream_users_get_user_profile_url() to get user's profile link
	 * @uses   get_avatar() to get user's avatar
	 * @uses   esc_html() to sanitize the output
	 * @uses   apply_filters() call 'wp_idea_stream_ideas_get_idea_footer' to override the output
	 * @return string  output for the footer
	 */
	function wp_idea_stream_ideas_get_idea_footer() {
		$idea = wp_idea_stream()->query_loop->idea;

		$retarray = array(
			'start' => __( 'This idea', 'wp-idea-stream' ),
		);

		$category_list = wp_idea_stream_ideas_get_the_term_list( $idea->ID, wp_idea_stream_get_category() );

		if ( ! empty( $category_list ) ) {
			$retarray['category'] = sprintf( _x( 'was posted in %s', 'idea categories comma separated list', 'wp-idea-stream' ), $category_list );
		}

		$tag_list = wp_idea_stream_ideas_get_the_term_list( $idea->ID, wp_idea_stream_get_tag() );

		if ( ! empty( $tag_list ) ) {
			$in = _x( 'and tagged', 'idea tags join words','wp-idea-stream' );

			if ( empty( $category_list ) ) {
				$in = _x( 'was tagged', 'idea tags join words no category','wp-idea-stream' );
			}

			$retarray['tag'] = sprintf( _x( '%1$s %2$s', 'idea tags comma separated list', 'wp-idea-stream' ), $in, $tag_list );
		}

		if ( empty( $retarray['category'] ) && empty( $retarray['tag'] ) ) {
			$retarray['posted'] = _x( 'was posted', 'idea footer empty tags and categories', 'wp-idea-stream' );
		}

		$date = apply_filters( 'get_the_date', mysql2date( get_option( 'date_format' ), $idea->post_date ) );

		if ( ! wp_idea_stream_is_single_idea() ) {
			// point at the end
			$retarray['date'] = sprintf( _x( 'on %s.', 'idea date of publication point', 'wp-idea-stream' ), $date );

		} else {
			// no point at the end
			$retarray['date'] = sprintf( _x( 'on %s', 'idea date of publication no point', 'wp-idea-stream' ), $date );

			$user = wp_idea_stream_users_get_user_data( 'id', $idea->post_author );
			$user_link = '<a class="idea-author" href="' . esc_url( wp_idea_stream_users_get_user_profile_url( $idea->post_author, $user->user_nicename ) ) . '" title="' . esc_attr( $user->display_name ) . '">';
			$user_link .= get_avatar( $idea->post_author, 20 ) . esc_html( $user->display_name ) . '</a>';

			$retarray['author'] = sprintf( _x( 'by %s.', 'single idea author link', 'wp-idea-stream' ), $user_link );
		}

		// Init edit url
		$edit_url = '';

		// Super admin will use the IdeaStream Administration screens
		if ( wp_idea_stream_user_can( 'wp_idea_stream_ideas_admin' ) ) {
			$edit_url = get_edit_post_link( $idea->ID );

		// The author will use the front end edit form
		} else if ( wp_idea_stream_ideas_can_edit( $idea ) ) {
			$edit_url = wp_idea_stream_get_form_url( wp_idea_stream_edit_slug(), $idea->post_name );
		}

		if ( ! empty( $edit_url ) ) {
			$retarray['edit'] = '<a href="' . esc_url( $edit_url ) . '" title="' . esc_attr__( 'Edit Idea', 'wp-idea-stream' ) . '">' . esc_html__( 'Edit Idea', 'wp-idea-stream' ) . '</a>';
		}

		/**
		 * @param  string  the footer to output
		 * @param  array   $retarray the parts of the footer organized in an associative array
		 * @param  WP_Post $idea the idea object
		 */
		return apply_filters( 'wp_idea_stream_ideas_get_idea_footer', join( ' ', $retarray ), $retarray, $idea );
	}

/**
 * Displays a bottom nav on single template
 *
 * @package WP Idea Stream
 * @subpackage ideas/tags
 *
 * @since 2.0.0
 *
 * @uses   previous_post_link() to get the previous adjacent idea
 * @uses   esc_url() to sanitize url
 * @uses   wp_idea_stream_get_root_url() to get main archive page url
 * @uses   next_post_link() to get the next adjacent idea
 * @return string the bottom nav output
 */
function wp_idea_stream_ideas_bottom_navigation() {
	?>
	<ul class="idea-nav-single">
		<li class="idea-nav-previous"><?php previous_post_link( '%link', '<span class="meta-nav">' . _x( '&larr;', 'Previous post link', 'wp-idea-stream' ) . '</span> %title' ); ?></li>
		<li class="idea-nav-all"><span class="meta-nav">&uarr;</span> <a href="<?php echo esc_url( wp_idea_stream_get_root_url() );?>" title="<?php esc_attr_e( 'All Ideas', 'wp-idea-stream') ;?>"><?php esc_html_e( 'All Ideas', 'wp-idea-stream') ;?></a></li>
		<li class="idea-nav-next"><?php next_post_link( '%link', '%title <span class="meta-nav">' . _x( '&rarr;', 'Next post link', 'wp-idea-stream' ) . '</span>' ); ?></li>
	</ul>
	<?php
}

/** Idea Form *****************************************************************/

/**
 * Displays a message to not logged in users
 *
 * @package WP Idea Stream
 * @subpackage ideas/tags
 *
 * @since 2.0.0
 *
 * @uses   is_user_logged_in() to check if the user is logged in
 * @uses   esc_url() to sanitize url
 * @uses   wp_login_url() to build login url
 * @uses   wp_idea_stream_get_form_url() to get the new idea form url
 * @uses   wp_idea_stream_login_message() to get the custom message to display
 * @uses   apply_filters() call 'wp_idea_stream_ideas_not_loggedin' to override the output
 * @return string the not logged in message output
 */
function wp_idea_stream_ideas_not_loggedin() {
	$output = esc_html__( 'You are not allowed to submit ideas', 'wp-idea-stream' );

	if ( ! is_user_logged_in() ) {

		if ( wp_idea_stream_is_signup_allowed_for_current_blog() ) {
			$output = sprintf(
				__( 'Please <a href="%s" title="Log in">log in</a> or <a href="%s" title="Sign up">register</a> to this site to submit an idea.', 'wp-idea-stream' ),
				esc_url( wp_login_url( wp_idea_stream_get_form_url() ) ),
				esc_url( wp_idea_stream_users_get_signup_url() )
			);
		} else {
			$output = sprintf(
				__( 'Please <a href="%s" title="Log in">log in</a> to this site to submit an idea.', 'wp-idea-stream' ),
				esc_url( wp_login_url( wp_idea_stream_get_form_url() ) )
			);
		}

		// Check for a custom message..
		$custom_message = wp_idea_stream_login_message();

		if ( ! empty( $custom_message ) ) {
			$output = $custom_message;
		}

	}

	/**
	 * @param  string $output the message to output
	 */
	echo apply_filters( 'wp_idea_stream_ideas_not_loggedin', $output );
}

/**
 * Displays the field to edit the idea title
 *
 * @package WP Idea Stream
 * @subpackage ideas/tags
 *
 * @since 2.0.0
 *
 * @uses   wp_idea_stream_ideas_get_title_edit() to get the value of the field
 * @return string output for the idea title field
 */
function wp_idea_stream_ideas_the_title_edit() {
	?>
	<label for="_wp_idea_stream_the_title"><?php esc_html_e( 'Title', 'wp-idea-stream' );?> <span class="required">*</span></label>
	<input type="text" id="_wp_idea_stream_the_title" name="wp_idea_stream[_the_title]" value="<?php wp_idea_stream_ideas_get_title_edit();?>"/>
	<?php
}

	/**
	 * Gets the value of the title field of an idea
	 *
	 * @package WP Idea Stream
	 * @subpackage ideas/tags
	 *
	 * @since 2.0.0
	 *
	 * @uses   wp_idea_stream() to get plugin's main instance
	 * @uses   apply_filters() call 'wp_idea_stream_ideas_get_title_edit' to override the output
	 * @return string  output for the title field
	 */
	function wp_idea_stream_ideas_get_title_edit() {
		$wp_idea_stream = wp_idea_stream();

		// Did the user submitted a title ?
		if ( ! empty( $_POST['wp_idea_stream']['_the_title'] ) ) {
			$edit_title = $_POST['wp_idea_stream']['_the_title'];

		// Are we editing an idea ?
		} else if ( ! empty( $wp_idea_stream->query_loop->idea->post_title ) ) {
			$edit_title = $wp_idea_stream->query_loop->idea->post_title;

		// Fallback to empty
		} else {
			$edit_title = '';
		}

		/**
		 * @param  string $edit_title the title field
		 */
		echo apply_filters( 'wp_idea_stream_ideas_get_title_edit', esc_attr( $edit_title ) );
	}

/**
 * Displays the field to edit the idea content
 *
 * @package WP Idea Stream
 * @subpackage ideas/tags
 *
 * @since 2.0.0
 *
 * @uses   get_option() to get the number of rows for the WP Editor
 * @uses   add_filter() to temporarly filter the mce buttons
 * @uses   wp_editor() to load the WP Editor
 * @uses   wp_idea_stream_ideas_get_editor_content() to get the content of the editor
 * @uses   remove_filter() to remove the filter
 * @return string output for the idea content field
 */
function wp_idea_stream_ideas_the_editor() {
	$args = array(
		'textarea_name' => 'wp_idea_stream[_the_content]',
		'wpautop'       => true,
		'media_buttons' => false,
		'editor_class'  => 'wp-idea-stream-tinymce',
		'textarea_rows' => get_option( 'default_post_edit_rows', 10 ),
		'teeny'         => false,
		'dfw'           => false,
		'tinymce'       => true,
		'quicktags'     => false
	);

	// Temporarly filter the editor
	add_filter( 'mce_buttons', 'wp_idea_stream_teeny_button_filter', 10, 1 );
	?>

	<label for="wp_idea_stream_the_content"><?php esc_html_e( 'Description', 'wp-idea-stream' ) ;?> <span class="required">*</span></label>

	<?php
	do_action( 'wp_idea_stream_media_buttons' );
	wp_editor( wp_idea_stream_ideas_get_editor_content(), 'wp_idea_stream_the_content', $args );

	remove_filter( 'mce_buttons', 'wp_idea_stream_teeny_button_filter', 10, 1 );
}

	/**
	 * Gets the value of the content field of an idea
	 *
	 * @package WP Idea Stream
	 * @subpackage ideas/tags
	 *
	 * @since 2.0.0
	 *
	 * @uses   wp_idea_stream() to get plugin's main instance
	 * @uses   wp_unslash() to strip slashes
	 * @uses   wp_kses_post() to sanitize the idea content
	 * @uses   apply_filters() call 'wp_idea_stream_ideas_get_editor_content' to override the output
	 * @return string  output for the content field
	 */
	function wp_idea_stream_ideas_get_editor_content() {
		$wp_idea_stream = wp_idea_stream();

		// Did the user submitted a content ?
		if ( ! empty( $_POST['wp_idea_stream']['_the_content'] ) ) {
			$edit_content = $_POST['wp_idea_stream']['_the_content'];

		// Are we editing an idea ?
		} else if ( ! empty( $wp_idea_stream->query_loop->idea->post_content ) ) {
			$edit_content = $wp_idea_stream->query_loop->idea->post_content;

		// Fallback to empty
		} else {
			$edit_content = '';
		}

		/**
		 * @param  string $edit_content the content field
		 */
		return apply_filters( 'wp_idea_stream_ideas_get_editor_content', $edit_content );
	}

/**
 * Checks if the category taxonomy has terms
 *
 * @package WP Idea Stream
 * @subpackage ideas/tags
 *
 * @since 2.0.0
 *
 * @uses   apply_filters() call 'wp_idea_stream_ideas_pre_has_terms' to disable the category checkboxes
 *                         call 'wp_idea_stream_ideas_get_terms_args' to customize the terms to retrieve
 * @uses   wp_idea_stream_ideas_get_terms() to get the terms
 * @uses   wp_idea_stream_get_category() to get the category taxonomy identifier
 * @uses   wp_idea_stream_set_idea_var() to globalize the terms for a later use
 * @return bool true if category has terms, false otherwise
 */
function wp_idea_stream_ideas_has_terms() {
	// Allow hiding cats
	$pre_has_terms = apply_filters( 'wp_idea_stream_ideas_pre_has_terms', true );

	if ( empty( $pre_has_terms ) ) {
		return false;
	}

	// Allow category listing override
	$args = apply_filters( 'wp_idea_stream_ideas_get_terms_args', array() );

	// Get all terms matching args
	$terms = wp_idea_stream_ideas_get_terms( wp_idea_stream_get_category(), $args );

	if ( empty( $terms ) ) {
		return false;
	}

	// Catch terms
	wp_idea_stream_set_idea_var( 'edit_form_terms', $terms );

	// Inform we have categories
	return true;
}

/**
 * Displays the checkboxes to select categories
 *
 * @package WP Idea Stream
 * @subpackage ideas/tags
 *
 * @since 2.0.0
 *
 * @uses   taxonomy_exists() to check a taxonomy exists
 * @uses   wp_idea_stream_get_category() to get the category taxonomy identifier
 * @uses   wp_idea_stream_ideas_has_terms() to check there are categories available
 * @uses   wp_idea_stream_ideas_get_category_edit() to get the category checkboxes list
 */
function wp_idea_stream_ideas_the_category_edit() {
	if ( ! taxonomy_exists( wp_idea_stream_get_category() ) || ! wp_idea_stream_ideas_has_terms() ) {
		return;
	}
	?>
	<label><?php esc_html_e( 'Categories', 'wp-idea-stream' );?></label>
	<?php wp_idea_stream_ideas_get_category_edit();
}

	/**
	 * Builds a checkboxes list of categories
	 *
	 * @package WP Idea Stream
	 * @subpackage ideas/tags
	 *
	 * @since 2.0.0
	 *
	 * @uses   wp_idea_stream() to get plugin's main instance
	 * @uses   wp_get_object_terms() to get the categories for the idea
	 * @uses   wp_idea_stream_get_category() to get the category taxonomy identifier
	 * @uses   wp_idea_stream_get_idea_var() to get the globalized category terms
	 * @uses   apply_filters() call 'wp_idea_stream_ideas_get_category_edit_none' to override the output when no categories
	 *                         call 'wp_idea_stream_ideas_get_category_edit' to override the output when has categories
	 * @uses   esc_attr() to sanitize an attribute
	 * @uses   esc_html() to sanitize an output
	 * @uses   checked() to add the checked attribute to the checkbox if needed
	 * @return string  output for the list of categories
	 */
	function wp_idea_stream_ideas_get_category_edit() {
		$wp_idea_stream = wp_idea_stream();

		// Did the user submitted categories ?
		if ( ! empty( $_POST['wp_idea_stream']['_the_category'] ) ) {
			$edit_categories = (array) $_POST['wp_idea_stream']['_the_category'];

		// Are we editing an idea ?
		} else if ( ! empty( $wp_idea_stream->query_loop->idea->ID ) ) {
			$edit_categories = (array) wp_get_object_terms( $wp_idea_stream->query_loop->idea->ID, wp_idea_stream_get_category(), array( 'fields' => 'ids' ) );

		// Default to en empty array
		} else {
			$edit_categories = array();
		}

		$terms = wp_idea_stream_get_idea_var( 'edit_form_terms' );

		// Default output
		$output = esc_html__( 'No categories are available.', 'wp-idea-stream' );

		if ( empty( $terms ) ) {
			/**
			 * @param  string $output the output when no categories
			 */
			echo apply_filters( 'wp_idea_stream_ideas_get_category_edit_none', $output );
			return;
		}

		$output = '<ul class="category-list">';

		foreach ( $terms as $term ) {
			$output .= '<li><label for="_wp_idea_stream_the_category_' . esc_attr( $term->term_id ) . '">';
			$output .= '<input type="checkbox" name="wp_idea_stream[_the_category][]" id="_wp_idea_stream_the_category_' . esc_attr( $term->term_id ) . '" value="' . esc_attr( $term->term_id ) . '" ' . checked( true, in_array( $term->term_id, $edit_categories  ), false ) . '/>';
			$output .= ' ' . esc_html( $term->name ) . '</label></li>';

		}

		$output .= '</ul>';

		/**
		 * @param  string $output the output when has categories
		 * @param  array  $edit_categories selected term ids
		 * @param  array  $terms available terms for the category taxonomy
		 */
		echo apply_filters( 'wp_idea_stream_ideas_get_category_edit', $output, $edit_categories, $terms );
	}


/**
 * Displays the tag editor for an idea
 *
 * @package WP Idea Stream
 * @subpackage ideas/tags
 *
 * @since 2.0.0
 *
 * @uses   taxonomy_exists() to check a taxonomy exists
 * @uses   wp_idea_stream_get_tag() to get the tag taxonomy identifier
 * @uses   wp_idea_stream_ideas_get_tags() to get the selected tags
 * @uses   wp_idea_stream_ideas_the_tag_cloud() to get the most used tags
 */
function wp_idea_stream_ideas_the_tags_edit() {
	if ( ! taxonomy_exists( wp_idea_stream_get_tag() ) ) {
		return;
	}
	?>
	<label for="_wp_idea_stream_the_tags"><?php esc_html_e( 'Tags', 'wp-idea-stream' );?></label>
	<p class="description"><?php esc_html_e( 'Type your tag, then hit the return or space key to add it','wp-idea-stream' ); ?></p>
	<div id="_wp_idea_stream_the_tags"><?php wp_idea_stream_ideas_get_tags();?></div>
	<?php wp_idea_stream_ideas_the_tag_cloud();
}

	/**
	 * Builds a checkboxes list of categories
	 *
	 * @package WP Idea Stream
	 * @subpackage ideas/tags
	 *
	 * @since 2.0.0
	 *
	 * @uses   wp_idea_stream() to get plugin's main instance
	 * @uses   wp_get_object_terms() to get the tags for the idea
	 * @uses   wp_idea_stream_get_tag() to get the tag taxonomy identifier
	 * @uses   apply_filters() call 'wp_idea_stream_ideas_get_tags' to override the output
	 * @return string  output for the list of tags
	 */
	function wp_idea_stream_ideas_get_tags() {
		$wp_idea_stream = wp_idea_stream();

		// Did the user submitted tags ?
		if ( ! empty( $_POST['wp_idea_stream']['_the_tags'] ) ) {
			$edit_tags = (array) $_POST['wp_idea_stream']['_the_tags'];

		// Are we editing tags ?
		} else if ( ! empty( $wp_idea_stream->query_loop->idea->ID ) ) {
			$edit_tags = (array) wp_get_object_terms( $wp_idea_stream->query_loop->idea->ID, wp_idea_stream_get_tag(), array( 'fields' => 'names' ) );

		// Default to an empty array
		} else {
			$edit_tags = array();
		}

		// Sanitize tags
		$edit_tags = array_map( 'esc_html', $edit_tags );

		/**
		 * @param  string the tags list output
		 * @param  array  $edit_tags selected term slugs
		 */
		echo apply_filters( 'wp_idea_stream_ideas_get_tags', join( ', ', $edit_tags ), $edit_tags );
	}

/**
 * Displays a tag cloud to show the most used one
 *
 * @package WP Idea Stream
 * @subpackage ideas/tags
 *
 * @since 2.0.0
 *
 * @param  int $number the number of tags to display
 * @uses   wp_idea_stream_generate_tag_cloud() to generate a tag cloud
 * @uses   number_format_i18n() to format number
 * @return string output for the tag cloud
 */
function wp_idea_stream_ideas_the_tag_cloud( $number = 10 ) {
	$tag_cloud = wp_idea_stream_generate_tag_cloud();

	if ( empty( $tag_cloud ) ) {
		return;
	}

	if ( $tag_cloud['number'] != $number  ) {
		$number = $tag_cloud['number'];
	}

	$number = number_format_i18n( $number );
	?>
	<div id="wp_idea_stream_most_used_tags">
		<p class="description"><?php printf( _n( 'Choose the most used tag', 'Choose from the %d most used tags', $number, 'wp-idea-stream' ), $number ) ;?></p>
		<div class="tag-items">
			<?php echo $tag_cloud['tagcloud'] ;?>
		</div>
	</div>
	<?php
}

/**
 * Displays a meta field for form/admin views
 *
 * @package WP Idea Stream
 * @subpackage ideas/tags
 *
 * @since 2.0.0
 *
 * @param  string $display_meta the meta field single output
 * @param  object $meta_object  the meta object
 * @param  string $context      the display context (single/form/admin)
 * @uses   wp_idea_stream_get_meta_admin_display() to get the custom field output
 */
function wp_idea_stream_meta_admin_display( $display_meta = '', $meta_object = null, $context = '' ) {
	echo wp_idea_stream_get_meta_admin_display( $display_meta, $meta_object, $context );
}

	/**
	 * Gets the custom field output for form/admin idea's view
	 *
	 * @package WP Idea Stream
	 * @subpackage ideas/tags
	 *
	 * @since 2.0.0
	 *
	 * @param  string $display_meta the meta field single output
	 * @param  object $meta_object  the meta object
	 * @param  string $context      the display context (single/form/admin)
	 * @uses   esc_html() to sanitize the output
	 * @uses   esc_attr() to sanitize an attribute
	 * @uses   apply_filters() call 'wp_idea_stream_get_meta_admin_display' to override the output
	 * @return string               HTML Output
	 */
	function wp_idea_stream_get_meta_admin_display( $display_meta = '', $meta_object = null, $context = '' ) {
		if ( empty( $meta_object->field_name ) ) {
			return;
		}

		$output = '';

		if ( 'admin' == $context ) {
			$output  = '<p><strong class="label">' . esc_html( $meta_object->label ) . '</strong> ';
			$output .= '<input type="text" name="' . esc_attr( $meta_object->field_name ) . '" value="' . esc_attr( $meta_object->field_value ) . '"/></p>';
		} else if ( 'form' == $context ) {
			$output  = '<p><label for="_wp_idea_stream_' . $meta_object->meta_key . '">' . esc_html( $meta_object->label ) . '</label>';
			$output .= '<input type="text" id="_wp_idea_stream_' . $meta_object->meta_key . '" name="' . esc_attr( $meta_object->field_name ) . '" value="' . esc_attr( $meta_object->field_value ) . '"/></p>';
		}

		/**
		 * @param  string $output       the meta field admin/form output
		 * @param  object $meta_object  the meta object
		 * @param  string $context      the display context (single/form/admin)
		 */
		return apply_filters( 'wp_idea_stream_get_meta_admin_display', $output, $meta_object, $context );
	}

/**
 * Displays the form submit/reset buttons
 *
 * @package WP Idea Stream
 * @subpackage ideas/tags
 *
 * @since 2.0.0
 *
 * @uses   wp_idea_stream() to get plugin's instance
 * @uses   wp_nonce_field() to add a security token to check upon once submitted
 * @uses   do_action() call 'wp_idea_stream_ideas_the_form_submit' to add custom actions before buttons
 * @uses   wp_idea_stream_is_addnew() to check if using the add new form
 * @uses   wp_idea_stream_is_edit() to check if using the edit form
 * @return string output for submit/reset buttons
 */
function wp_idea_stream_ideas_the_form_submit() {
	$wp_idea_stream = wp_idea_stream();

	wp_nonce_field( 'wp_idea_stream_save' );

	do_action( 'wp_idea_stream_ideas_the_form_submit' ); ?>

	<?php if ( wp_idea_stream_is_addnew() ) : ?>

		<input type="reset" value="<?php esc_attr_e( 'Reset', 'wp-idea-stream' ) ;?>"/>
		<input type="submit" value="<?php esc_attr_e( 'Submit', 'wp-idea-stream' ) ;?>" name="wp_idea_stream[save]"/>

	<?php elseif( wp_idea_stream_is_edit() && ! empty( $wp_idea_stream->query_loop->idea->ID ) ) : ?>

		<input type="hidden" value="<?php echo esc_attr( $wp_idea_stream->query_loop->idea->ID ) ;?>" name="wp_idea_stream[_the_id]"/>
		<input type="submit" value="<?php esc_attr_e( 'Update', 'wp-idea-stream' ) ;?>" name="wp_idea_stream[save]"/>

	<?php endif ; ?>

	<?php
}

/**
 * If BuddyDrive is activated, then use it to allow files
 * to be added to ideas !
 *
 * @since  2.2.0
 */
function wp_idea_stream_buddydrive_button() {
	if ( function_exists( 'buddydrive_editor' ) ) {
		buddydrive_editor();
	}
}
add_action( 'wp_idea_stream_media_buttons', 'wp_idea_stream_buddydrive_button' );

/**
 * Add inline style for the Embed Rating
 *
 * @since  2.2.1
 *
 * @return string style output
 */
function wp_idea_stream_ideas_embed_style() {
	// Bail if not an idea
	if ( wp_idea_stream_get_post_type() !== get_query_var( 'post_type' ) || wp_idea_stream_is_rating_disabled() ) {
		return;
	}
	?>
	<style type="text/css">
		.wp-idea-stream-embed-ratings {
			display: inline;
			margin-right: 10px;
		}

		.wp-idea-stream-embed-ratings a {
			line-height: 25px;
			display: inline-block;
		}

		.wp-idea-stream-embed-ratings .ideastream-star-filled {
			background-image: url("data:image/svg+xml;charset=utf8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%2020%2020%22%3E%3Cpath%20d%3D%22M10%201l3%206%206%200.75-4.12%204.62%201.12%206.63-6-3-6%203%201.13-6.63-4.13-4.62%206-0.75z%22%20fill%3D%22%2382878c%22%3E%3C%2Fpath%3E%3C%2Fsvg%3E");
			top: 2px;
		}

		.wp-idea-stream-embed-ratings a:hover .ideastream-star-filled {
			background-image: url("data:image/svg+xml;charset=utf8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%2020%2020%22%3E%3Cpath%20d%3D%22M10%201l3%206%206%200.75-4.12%204.62%201.12%206.63-6-3-6%203%201.13-6.63-4.13-4.62%206-0.75z%22%20fill%3D%22%230073aa%22%3E%3C%2Fpath%3E%3C%2Fsvg%3E");
		}
	</style>
	<?php
}

/**
 * Output the Idea Ratings if needed into the Embedded idea
 *
 * @since  2.2.1
 *
 * @return string HTML output
 */
function wp_idea_stream_ideas_embed_meta() {
	$idea = get_post();

	if ( ! isset( $idea->post_type ) || wp_idea_stream_get_post_type() !== $idea->post_type || wp_idea_stream_is_rating_disabled() ) {
		return;
	}

	// Get the Average Rate
	$average_rate = wp_idea_stream_ideas_get_average_rating( $idea->ID );

	if ( ! $average_rate ) {
		return;
	}

	// Get rating link
	$rating_link = wp_idea_stream_ideas_get_idea_permalink( $idea ) . '#rate';
	?>
	<div class="wp-idea-stream-embed-ratings">
		<a href="<?php echo esc_url( $rating_link ); ?>" target="_top">
			<span class="dashicons ideastream-star-filled"></span>
			<?php printf(
				esc_html__( '%1$sAverage Rating:%2$s%3$s', 'wp-idea-stream' ),
				'<span class="screen-reader-text">',
				'</span>',
				$average_rate
			); ?>
		</a>
	</div>
	<?php
}
