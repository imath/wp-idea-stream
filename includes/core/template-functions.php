<?php
/**
 * WP Idea Stream template functions.
 *
 * @package   WP Idea Stream
 * @subpackage core/template-functions
 *
 * @since 2.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Check the main WordPress query to match WP Idea Stream conditions
 * Eventually Override query vars and set global template conditions / vars
 *
 * This the key function of the plugin, it is definining the templates
 * to load and is setting the displayed user.
 *
 * Inspired by bbPress 's bbp_parse_query()
 *
 * @package WP Idea Stream
 * @subpackage core/template-functions
 *
 * @since 2.0.0
 *
 * @param WP_Query $posts_query The WP_Query instance
 * @uses  WP_Query->is_main_query() to check it's the main query
 * @uses  WP_Query->get() to get a query var
 * @uses  wp_idea_stream_is_admin() to check if in IdeaStream's Admin territory
 * @uses  wp_idea_stream_is_sticky_enabled() to check if sticky feature is available
 * @uses  WP_Query->set() to set a query var
 * @uses  wp_idea_stream_is_rating_disabled() to check if ratings feature are available
 * @uses  wp_idea_stream_set_idea_var() to globalize a var
 * @uses  is_admin() to check for WordPress administration
 * @uses  wp_idea_stream_get_post_type() to get the ideas post type identifier
 * @uses  wp_idea_stream_user_rewrite_id() to get the user rewrite id
 * @uses  wp_idea_stream_users_get_user_data() to get a specific user's data
 * @uses  WP_Query->set_404() to set a 404
 * @uses  wp_idea_stream_user_rates_rewrite_id() to get the user rates rewrite id
 * @uses  wp_idea_stream_user_comments_rewrite_id() to get the user comments rewrite id
 * @uses  wp_idea_stream_action_rewrite_id() to get the action rewrite id
 * @uses  wp_idea_stream_addnew_slug() to get the add new slug
 * @uses  wp_idea_stream_edit_slug() to get the edit slug
 * @uses  has_action() to check if the action 'wp_idea_stream_custom_action' is used by any plugins
 * @uses  do_action() Calls 'wp_idea_stream_custom_action' to perform actions relative to ideas
 * @uses  wp_idea_stream_get_category() to get the ideas category identifier
 * @uses  wp_idea_stream_get_tag() to get the ideas tag identifier
 * @uses  wp_idea_stream_search_rewrite_id() to get the search rewrite id
 */
function wp_idea_stream_parse_query( $posts_query = null ) {
	// Bail if $posts_query is not the main loop
	if ( ! $posts_query->is_main_query() ) {
		return;
	}

	// Bail if filters are suppressed on this query
	if ( true === $posts_query->get( 'suppress_filters' ) ) {
		return;
	}

	// Handle the specific queries in IdeaStream Admin
	if ( wp_idea_stream_is_admin() ) {

		// Display sticky ideas if requested
		if ( wp_idea_stream_is_sticky_enabled() && ! empty( $_GET['sticky_ideas'] ) ) {
			$posts_query->set( 'post__in', wp_idea_stream_ideas_get_stickies() );
		}

		// Build meta_query if orderby rates is set
		if ( ! wp_idea_stream_is_rating_disabled() && ! empty( $_GET['orderby'] ) && 'rates_count' == $_GET['orderby'] ) {
			$posts_query->set( 'meta_query', array(
				array(
					'key'     => '_ideastream_average_rate',
					'compare' => 'EXISTS'
				)
			) );

			// Set the orderby idea var
			wp_idea_stream_set_idea_var( 'orderby', 'rates_count' );
		}

		do_action( 'wp_idea_stream_admin_request', $posts_query );

		return;
	}

	// Bail if else where in admin
	if ( is_admin() ) {
		return;
	}

	// Ideas post type for a later use
	$idea_post_type = wp_idea_stream_get_post_type();

	/** User's profile ************************************************************/

	// Are we requesting the user-profile template ?
	$user       = $posts_query->get( wp_idea_stream_user_rewrite_id() );
	$embed_page = wp_idea_stream_is_embed_profile();

	if ( ! empty( $user ) ) {

		if ( ! is_numeric( $user ) ) {
			// Get user by his username
			$user = wp_idea_stream_users_get_user_data( 'slug', $user );
		} else {
			// Get user by his id
			$user = wp_idea_stream_users_get_user_data( 'id', $user );
		}

		// No user id: no profile!
		if ( empty( $user->ID ) || true === apply_filters( 'wp_idea_stream_users_is_spammy', is_multisite() && is_user_spammy( $user ), $user ) ) {
			$posts_query->set_404();

			// Make sure the WordPress Embed Template will be used
			if ( ( 'true' === get_query_var( 'embed' ) || true === get_query_var( 'embed' ) ) ) {
				$posts_query->is_embed = true;
				$posts_query->set( 'p', -1 );
			}

			return;
		}

		// Set the displayed user id
		wp_idea_stream_set_idea_var( 'is_user', absint( $user->ID ) );

		// Make sure the post_type is set to ideas.
		$posts_query->set( 'post_type', $idea_post_type );

		// Are we requesting user rates
		$user_rates    = $posts_query->get( wp_idea_stream_user_rates_rewrite_id() );

		// Or user comments ?
		$user_comments = $posts_query->get( wp_idea_stream_user_comments_rewrite_id() );

		if ( ! empty( $user_rates ) && ! wp_idea_stream_is_rating_disabled() ) {
			// We are viewing user's rates
			wp_idea_stream_set_idea_var( 'is_user_rates', true );

			// Define the Meta Query to get his rates
			$posts_query->set( 'meta_query', array(
				array(
					'key'     => '_ideastream_rates',
					'value'   => ';i:' . $user->ID .';',
					'compare' => 'LIKE'
				)
			) );

		} else if ( ! empty( $user_comments ) ) {
			// We are viewing user's comments
			wp_idea_stream_set_idea_var( 'is_user_comments', true );

			/**
			 * Make sure no result.
			 * Query will be built later in user comments loop
			 */
			$posts_query->set( 'p', -1 );

		} else {
			if ( ( 'true' === get_query_var( 'embed' ) || true === get_query_var( 'embed' ) ) ) {
				$posts_query->is_embed = true;
				$posts_query->set( 'p', -1 );

				if ( $embed_page ) {
					wp_idea_stream_set_idea_var( 'is_user_embed', true );
				} else {
					$posts_query->set_404();
					return;
				}
			}

			// Default to the ideas the user submitted
			$posts_query->set( 'author', $user->ID  );
		}

		// No stickies on user's profile
		$posts_query->set( 'ignore_sticky_posts', true );

		// Make sure no 404
		$posts_query->is_404  = false;

		// Set the displayed user.
		wp_idea_stream_set_idea_var( 'displayed_user', $user );
	}


	/** Actions (New Idea) ********************************************************/

	$action = $posts_query->get( wp_idea_stream_action_rewrite_id() );

	if ( ! empty( $action ) ) {
		// Make sure the post type is set to ideas
		$posts_query->set( 'post_type', $idea_post_type );

		// Define a global to inform we're dealing with an action
		wp_idea_stream_set_idea_var( 'is_action', true );

		// Is the new idea form requested ?
		if ( wp_idea_stream_addnew_slug() == $action ) {
			// Yes so set the corresponding var
			wp_idea_stream_set_idea_var( 'is_new', true );

			/**
			 * Make sure no result.
			 * We are not querying any content, but creating one
			 */
			$posts_query->set( 'p', -1 );

		// Edit action ?
		} else if ( wp_idea_stream_edit_slug() == $action ) {
			// Yes so set the corresponding var
			wp_idea_stream_set_idea_var( 'is_edit', true );

		// Signup support
		} else if ( wp_idea_stream_signup_slug() == $action && wp_idea_stream_is_signup_allowed_for_current_blog() ) {
			// Set the signup global var
			wp_idea_stream_set_idea_var( 'is_signup', true );

			/**
			 * Make sure no result.
			 * We are not querying any content, but creating one
			 */
			$posts_query->set( 'p', -1 );

		} else if ( has_action( 'wp_idea_stream_custom_action' ) ) {
			/**
			 * Allow plugins to other custom idea actions
			 *
			 * @param string   $action      The requested action
			 * @param WP_Query $posts_query The WP_Query instance
			 */
			do_action( 'wp_idea_stream_custom_action', $action, $posts_query );
		} else {
			$posts_query->set_404();
			return;
		}
	}

	/** Ideas by category *********************************************************/

	$category = $posts_query->get( wp_idea_stream_get_category() );

	if ( ! empty( $category ) ) {
		// Make sure the post type is set to ideas
		$posts_query->set( 'post_type', $idea_post_type );

		// Define the current category
		wp_idea_stream_set_idea_var( 'is_category', $category );
	}

	/** Ideas by tag **************************************************************/

	$tag = $posts_query->get( wp_idea_stream_get_tag() );

	if ( ! empty( $tag ) ) {
		// Make sure the post type is set to ideas
		$posts_query->set( 'post_type', $idea_post_type );

		// Define the current tag
		wp_idea_stream_set_idea_var( 'is_tag', $tag );
	}


	/** Searching ideas ***********************************************************/

	$search = $posts_query->get( wp_idea_stream_search_rewrite_id() );

	if ( ! empty( $search ) ) {
		// Make sure the post type is set to ideas
		$posts_query->set( 'post_type', $idea_post_type );

		// Define the query as a search one
		$posts_query->set( 'is_search', true );

		/**
		 * Temporarly set the 's' parameter of WP Query
		 * This will be reset while building ideas main_query args
		 * @see wp_idea_stream_set_template()
		 */
		$posts_query->set( 's', $search );

		// Set the search conditionnal var
		wp_idea_stream_set_idea_var( 'is_search', true );
	}

	/** Changing order ************************************************************/

	// Here we're using built-in var
	$orderby = $posts_query->get( 'orderby' );

	// Make sure we are ordering ideas
	if ( ! empty( $orderby ) && $idea_post_type == $posts_query->get( 'post_type' ) ) {

		if ( ! wp_idea_stream_is_rating_disabled() && 'rates_count' == $orderby ) {
			/**
			 * It's an order by rates request, set the meta query to achieve this.
			 * Here we're not ordering yet, we simply make sure to get ideas that
			 * have been rated.
			 * Order will happen thanks to wp_idea_stream_set_rates_count_orderby()
			 * filter.
			 */
			$posts_query->set( 'meta_query', array(
				array(
					'key'     => '_ideastream_average_rate',
					'compare' => 'EXISTS'
				)
			) );
		}

		// Set the order by var
		wp_idea_stream_set_idea_var( 'orderby', $orderby );
	}

	// Set the idea archive var if viewing ideas archive
	if ( $posts_query->is_post_type_archive() ) {
		wp_idea_stream_set_idea_var( 'is_idea_archive', true );
	}

	/**
	 * Finally if post_type is ideas, then we're in IdeaStream's
	 * territory so set this
	 */
	if ( $idea_post_type === $posts_query->get( 'post_type' ) ) {
		wp_idea_stream_set_idea_var( 'is_ideastream', true );

		// Reset the pagination
		if ( -1 !== $posts_query->get( 'p' ) ) {
			$posts_query->set( 'posts_per_page', wp_idea_stream_ideas_per_page() );
		}
	}
}

/**
 * Loads the plugin's stylesheet
 *
 * @package WP Idea Stream
 * @subpackage core/template-functions
 *
 * @since 2.0.0
 *
 * @uses apply_filters() call 'wp_idea_stream_style_deps' to override css dependencies
 * @uses wp_enqueue_style() to add the stylesheet file to WordPress queue
 * @uses wp_idea_stream_get_stylesheet() to get the stylesheet url
 * @uses wp_idea_stream_get_version() to get current plugin's version
 */
function wp_idea_stream_enqueue_style() {
	$style_deps = apply_filters( 'wp_idea_stream_style_deps', array( 'dashicons' ) );
	wp_enqueue_style( 'wp-idea-stream-style', wp_idea_stream_get_stylesheet(), $style_deps, wp_idea_stream_get_version() );

	$min = '.min';

	if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
		$min = '';
	}

	if ( wp_idea_stream_is_user_profile() && wp_idea_stream_is_embed_profile() ) {
		wp_enqueue_style( 'wp-idea-stream-sharing-profile', includes_url( "css/wp-embed-template{$min}.css" ), array(), wp_idea_stream_get_version() );
	}
}

/**
 * Loads the embed stylesheet to be used inside
 * WordPress & IdeaStream embed templates
 *
 * @since 2.3.0
 */
function wp_idea_stream_enqueue_embed_style() {
	$min = '.min';

	if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
		$min = '';
	}

	wp_enqueue_style( 'wp-idea-stream-embed-style', wp_idea_stream_get_stylesheet( "embed-style{$min}" ), array(), wp_idea_stream_get_version() );
}

/** Conditional template tags *************************************************/

/**
 * Is this the admin part of IdeaStream
 *
 * @package WP Idea Stream
 * @subpackage core/template-functions
 *
 * @since 2.0.0
 *
 * @uses   get_current_screen() to get administration screen post type
 * @uses   wp_idea_stream_get_post_type() to get the ideas post type identifier
 * @return bool true if on IdeaStream admin part, false otherwise
 */
function wp_idea_stream_is_admin() {
	$retval = false;

	// using this as is_admin() can be true in case of AJAX
	if ( ! function_exists( 'get_current_screen' ) ) {
		return $retval;
	}

	// Get current screen
	$current_screen = get_current_screen();

	// Make sure the current screen post type is step and is the ideas one
	if ( ! empty( $current_screen->post_type ) && wp_idea_stream_get_post_type() == $current_screen->post_type ) {
		$retval = true;
	}

	return $retval;
}

/**
 * Is this Plugin's front end territory ?
 *
 * @package WP Idea Stream
 * @subpackage core/template-functions
 *
 * @since 2.0.0
 *
 * @uses   wp_idea_stream_get_idea_var() to get a globalized var
 * @return bool true if viewing an IdeaStream page, false otherwise
 */
function wp_idea_stream_is_ideastream() {
	return (bool) wp_idea_stream_get_idea_var( 'is_ideastream' );
}

/**
 * Is this the new idea form ?
 *
 * @package WP Idea Stream
 * @subpackage core/template-functions
 *
 * @since 2.0.0
 *
 * @uses   wp_idea_stream_get_idea_var() to get a globalized var
 * @return bool true if on the addnew form, false otherwise
 */
function wp_idea_stream_is_addnew() {
	return (bool) wp_idea_stream_get_idea_var( 'is_new' );
}

/**
 * Is this the edit idea form ?
 *
 * @package WP Idea Stream
 * @subpackage core/template-functions
 *
 * @since 2.0.0
 *
 * @uses   wp_idea_stream_get_idea_var() to get a globalized var
 * @return bool true if on the edit form, false otherwise
 */
function wp_idea_stream_is_edit() {
	return (bool) wp_idea_stream_get_idea_var( 'is_edit' );
}

/**
 * Is this the signup form ?
 *
 * @package WP Idea Stream
 * @subpackage core/template-functions
 *
 * @since 2.1.0
 *
 * @uses   wp_idea_stream_get_idea_var() to get a globalized var
 * @return bool true if on the edit form, false otherwise
 */
function wp_idea_stream_is_signup() {
	return (bool) wp_idea_stream_get_idea_var( 'is_signup' );
}

/**
 * Are we viewing a single idea ?
 *
 * @package WP Idea Stream
 * @subpackage core/template-functions
 *
 * @since 2.0.0
 *
 * @uses   is_singular()
 * @uses   wp_idea_stream_get_post_type() to get a globalized var
 * @return bool true if on a single idea template, false otherwise
 */
function wp_idea_stream_is_single_idea() {
	return (bool) apply_filters( 'wp_idea_stream_is_single_idea', is_singular( wp_idea_stream_get_post_type() ) );
}

/**
 * Current ID for the idea being viewed
 *
 * @package WP Idea Stream
 * @subpackage core/template-functions
 *
 * @since 2.0.0
 *
 * @uses   apply_filters() call 'wp_idea_stream_get_single_idea_id' override the current idea ID
 * @uses   wp_idea_stream_get_idea_var() to get a globalized var
 * @return int the current idea ID
 */
function wp_idea_stream_get_single_idea_id() {
	return (int) apply_filters( 'wp_idea_stream_get_single_idea_id', wp_idea_stream_get_idea_var( 'single_idea_id' ) );
}

/**
 * Are we viewing ideas archive ?
 *
 * @package WP Idea Stream
 * @subpackage core/template-functions
 *
 * @since 2.0.0
 *
 * @uses   is_post_type_archive() to check if displaying the ideas archive
 * @uses   wp_idea_stream_get_post_type() to get the ideas post type identifier
 * @uses   wp_idea_stream_get_idea_var() to get a globalized var
 * @uses   apply_filters() call 'wp_idea_stream_is_idea_archive' to override condition
 * @return bool true if on ideas archive, false otherwise
 */
function wp_idea_stream_is_idea_archive() {
	$retval = false;

	if ( is_post_type_archive( wp_idea_stream_get_post_type() ) || wp_idea_stream_get_idea_var( 'is_idea_archive' ) ) {
		$retval = true;
	}

	return apply_filters( 'wp_idea_stream_is_idea_archive', $retval );
}

/**
 * Are we viewing ideas by category ?
 *
 * @package WP Idea Stream
 * @subpackage core/template-functions
 *
 * @since 2.0.0
 *
 * @uses   is_tax()
 * @uses   wp_idea_stream_get_category() to get the ideas category identifier
 * @uses   wp_idea_stream_get_idea_var() to get a globalized var
 * @uses   apply_filters() call 'wp_idea_stream_is_category' to override condition
 * @return bool true if viewing ideas categorized in a sepecific term, false otherwise.
 */
function wp_idea_stream_is_category() {
	$retval = false;

	if ( is_tax( wp_idea_stream_get_category() ) || wp_idea_stream_get_idea_var( 'is_category' ) ) {
		$retval = true;
	}

	return apply_filters( 'wp_idea_stream_is_category', $retval );
}

/**
 * Are we viewing ideas by tag ?
 *
 * @package WP Idea Stream
 * @subpackage core/template-functions
 *
 * @since 2.0.0
 *
 * @uses   is_tax()
 * @uses   wp_idea_stream_get_tag() to get the ideas tag identifier
 * @uses   wp_idea_stream_get_idea_var() to get a globalized var
 * @uses   apply_filters() call 'wp_idea_stream_is_tag' to override condition
 * @return bool true if viewing ideas tagged with a sepecific term, false otherwise.
 */
function wp_idea_stream_is_tag() {
	$retval = false;

	if ( is_tax( wp_idea_stream_get_tag() ) || wp_idea_stream_get_idea_var( 'is_tag' ) ) {
		$retval = true;
	}

	return apply_filters( 'wp_idea_stream_is_tag', $retval );
}

/**
 * Get / Set the current term being viewed
 *
 * @package WP Idea Stream
 * @subpackage core/template-functions
 *
 * @since 2.0.0
 *
 * @uses   wp_idea_stream_get_idea_var() to get a globalized var
 * @uses   get_queried_object() to get WordPress queried object
 * @uses   wp_idea_stream_set_idea_var() to set a globalized var
 * @uses   apply_filters() call 'wp_idea_stream_get_current_term' to override the current term being viewed
 * @return object $current_term
 */
function wp_idea_stream_get_current_term() {
	$current_term = wp_idea_stream_get_idea_var( 'current_term' );

	if ( empty( $current_term ) ) {
		$current_term = get_queried_object();
	}

	wp_idea_stream_set_idea_var( 'current_term', $current_term );

	return apply_filters( 'wp_idea_stream_get_current_term', $current_term );
}

/**
 * Get the current term name
 *
 * @package WP Idea Stream
 * @subpackage core/template-functions
 *
 * @since 2.0.0
 *
 * @uses   wp_idea_stream_get_current_term() to get the current term being requested
 * @uses   apply_filters() call 'wp_idea_stream_get_term_name' to override the current term name
 * @return string the term name
 */
function wp_idea_stream_get_term_name() {
	$term = wp_idea_stream_get_current_term();

	return apply_filters( 'wp_idea_stream_get_term_name', $term->name );
}

/**
 * Are we searching ideas ?
 *
 * @package WP Idea Stream
 * @subpackage core/template-functions
 *
 * @since 2.0.0
 *
 * @uses   wp_idea_stream_search_rewrite_id() to get the search rewrite id
 * @uses   wp_idea_stream_get_idea_var() to get a globalized var
 * @uses   apply_filters() call 'wp_idea_stream_is_search' to override the condition
 * @return bool true if an idea search is performed, otherwise false
 */
function wp_idea_stream_is_search() {
	$retval = false;

	if ( get_query_var( wp_idea_stream_search_rewrite_id() ) || wp_idea_stream_get_idea_var( 'is_search' ) ) {
		$retval = true;
	}

	return apply_filters( 'wp_idea_stream_is_search', $retval );
}

/**
 * Has the order changed to the type being checked
 *
 * @package WP Idea Stream
 * @subpackage core/template-functions
 *
 * @since 2.0.0
 *
 * @param  string $type the order to check
 * @uses   get_query_var() to get a query var
 * @uses   wp_idea_stream_get_idea_var() to get a globalized var
 * @uses   apply_filters() call 'wp_idea_stream_is_orderby' to override the condition
 * @return bool true if the order has changed from default one, false otherwise
 */
function wp_idea_stream_is_orderby( $type = '' ) {
	$retval = false;

	$orderby = wp_idea_stream_get_idea_var( 'orderby' );

	if ( empty( $orderby ) ) {
		$orderby = get_query_var( 'orderby' );
	}

	if ( ! empty( $orderby ) && $orderby == $type ) {
		$retval = true;
	}

	return apply_filters( 'wp_idea_stream_is_orderby', $retval, $type );
}

/**
 * Are viewing a user's profile ?
 *
 * @package WP Idea Stream
 * @subpackage core/template-functions
 *
 * @since 2.0.0
 *
 * @uses   wp_idea_stream_get_idea_var() to get a globalized var
 * @uses   apply_filters() call 'wp_idea_stream_is_user_profile' to override the condition
 * @return bool true a user's profile is being viewed, false otherwise
 */
function wp_idea_stream_is_user_profile() {
	return (bool) apply_filters( 'wp_idea_stream_is_user_profile', wp_idea_stream_get_idea_var( 'is_user' ) );
}

/**
 * Are we viewing comments in user's profile
 *
 * @package WP Idea Stream
 * @subpackage core/template-functions
 *
 * @since 2.0.0
 *
 * @uses   wp_idea_stream_get_idea_var() to get a globalized var
 * @uses   apply_filters() call 'wp_idea_stream_is_user_profile_comments' to override the condition
 * @return bool true if viewing user's profile comments, false otherwise
 */
function wp_idea_stream_is_user_profile_comments() {
	return (bool) apply_filters( 'wp_idea_stream_is_user_profile_comments', wp_idea_stream_get_idea_var( 'is_user_comments' ) );
}

/**
 * Are we viewing rates in user's profile
 *
 * @package WP Idea Stream
 * @subpackage core/template-functions
 *
 * @since 2.0.0
 *
 * @uses   wp_idea_stream_get_idea_var() to get a globalized var
 * @uses   apply_filters() call 'wp_idea_stream_is_user_profile_rates' to override the condition
 * @return bool true if viewing user's profile rates, false otherwise
 */
function wp_idea_stream_is_user_profile_rates() {
	return (bool) apply_filters( 'wp_idea_stream_is_user_profile_rates', wp_idea_stream_get_idea_var( 'is_user_rates' ) );
}

/**
 * Are we viewing ideas in user's profile
 *
 * @package WP Idea Stream
 * @subpackage core/template-functions
 *
 * @since 2.0.0
 *
 * @uses  wp_idea_stream_is_user_profile_comments() to check if viewing comments on user's profile
 * @uses  wp_idea_stream_is_user_profile_rates() to check if viewing rates on user's profile
 * @return bool true if viewing ideas in the user's profile, false otherwise
 */
function wp_idea_stream_is_user_profile_ideas() {
	return (bool) ( ! wp_idea_stream_is_user_profile_comments() && ! wp_idea_stream_is_user_profile_rates() );
}

/**
 * Is this self profile ?
 *
 * @package WP Idea Stream
 * @subpackage core/template-functions
 *
 * @since 2.0.0
 *
 * @uses  wp_idea_stream_get_idea_var() to get a globalized var
 * @uses  apply_filters() call 'wp_idea_stream_is_current_user_profile' to override the self profile check
 * @return bool true if current user is viewing his profile, false otherwise
 */
function wp_idea_stream_is_current_user_profile() {
	$current_user      = wp_idea_stream_get_idea_var( 'current_user' );
	$displayed_user_id = wp_idea_stream_get_idea_var( 'is_user' );

	if( empty( $current_user->ID ) ) {
		return false;
	}

	$is_user_profile = ( $current_user->ID == $displayed_user_id );

	/**
	 * Used Internally to map this function to BuddyPress bp_is_my_profile one
	 *
	 * @param  bool $is_user_profile whether the user is viewing his profile or not
	 */
	return (bool) apply_filters( 'wp_idea_stream_is_current_user_profile', $is_user_profile );
}

/**
 * Reset the page (post) title depending on the context
 *
 * @package WP Idea Stream
 * @subpackage core/template-functions
 *
 * @since 2.0.0
 *
 * @param string $context the context to build the title for
 * @uses  wp_idea_stream_archive_title() to get the IdeaStream archive page title
 * @uses  wp_idea_stream_user_can() to check for user's capability
 * @uses  wp_idea_stream_get_root_url() to get IdeaStream's root url
 * @uses  wp_idea_stream_get_form_url() to get IdeaStream's add new form url
 * @uses  wp_idea_stream_get_term_name() to get the term name
 * @uses  wp_idea_stream_users_get_displayed_user_displayname() to get the displayed user name
 * @uses  apply_filters() call 'wp_idea_stream_reset_post_title' to override the title of the page
 * @return string the post title
 */
function wp_idea_stream_reset_post_title( $context = '' ) {
	$post_title = wp_idea_stream_archive_title();

	switch( $context ) {
		case 'archive' :
			if ( wp_idea_stream_user_can( 'publish_ideas' ) ) {
				$post_title =  '<a href="' . esc_url( wp_idea_stream_get_root_url() ) . '">' . $post_title . '</a>';
				$post_title .= ' <a href="' . esc_url( wp_idea_stream_get_form_url() ) .'" class="button wpis-title-button">' . esc_html__( 'Add new', 'wp-idea-stream' ) . '</a>';
			}
			break;

		case 'taxonomy' :
			$post_title = '<a href="' . esc_url( wp_idea_stream_get_root_url() ) . '">' . $post_title . '</a>';
			$post_title .= '<span class="idea-title-sep"></span>' . wp_idea_stream_get_term_name();
			break;

		case 'user-profile':
			$post_title = '<a href="' . esc_url( wp_idea_stream_get_root_url() ) . '">' . $post_title . '</a>';
			$post_title .= '<span class="idea-title-sep"></span>' . sprintf( esc_html__( '%s&#39;s profile', 'wp-idea-stream' ), wp_idea_stream_users_get_displayed_user_displayname() );
			break;

		case 'new-idea' :
			$post_title = '<a href="' . esc_url( wp_idea_stream_get_root_url() ) . '">' . $post_title . '</a>';
			$post_title .= '<span class="idea-title-sep"></span>' . __( 'New Idea', 'wp-idea-stream' );
			break;

		case 'edit-idea' :
			$post_title = '<a href="' . esc_url( wp_idea_stream_get_root_url() ) . '">' . $post_title . '</a>';
			$post_title .= '<span class="idea-title-sep"></span>' . __( 'Edit Idea', 'wp-idea-stream' );
			break;

		case 'signup' :
			$post_title = '<a href="' . esc_url( wp_idea_stream_get_root_url() ) . '">' . $post_title . '</a>';
			$post_title .= '<span class="idea-title-sep"></span>' . __( 'Create an account', 'wp-idea-stream' );
			break;
	}

	/**
	 * @param  string $post_title the title for the template
	 * @param  string $context the context
	 */
	return apply_filters( 'wp_idea_stream_reset_post_title', $post_title, $context );
}

/**
 * Filters the <title> content
 *
 * Inspired by bbPress's bbp_title()
 *
 * @package WP Idea Stream
 * @subpackage core/template-functions
 *
 * @since 2.0.0
 *
 * @param array $title the title parts
 * @uses  wp_idea_stream_is_ideastream() to make sure it's plugin's territory
 * @uses  wp_idea_stream_is_addnew() to check the submit form is displayed
 * @uses  wp_idea_stream_is_user_profile() to check if a user's profile is displayed
 * @uses  wp_idea_stream_users_get_displayed_user_displayname() to get the display name of the user being viewed
 * @uses  wp_idea_stream_is_single_idea() to check whether page is displaying the single idea template
 * @uses  is_tax() to check if a taxonomy is in queried objects
 * @uses  wp_idea_stream_get_current_term() to get the current term
 * @uses  get_taxonomy() to get the taxonomy
 * @uses  wp_idea_stream_set_idea_var() to globalize the current term
 * @uses  wp_idea_stream_is_signup() to check if on the signup page
 * @uses  apply_filters() call 'wp_idea_stream_title' to override the title meta tag of the page
 * @return string the page title meta tag
 */
function wp_idea_stream_title( $title_array = array() ) {
	if ( ! wp_idea_stream_is_ideastream() ) {
		return $title_array;
	}

	$new_title = array();

	if ( wp_idea_stream_is_addnew() ) {
		$new_title[] = esc_attr__( 'New idea', 'wp-idea-stream' );
	} elseif ( wp_idea_stream_is_edit() ) {
		$new_title[] = esc_attr__( 'Edit idea', 'wp-idea-stream' );
	} elseif ( wp_idea_stream_is_user_profile() ) {
		$new_title[] = sprintf( esc_html__( '%s&#39;s profile', 'wp-idea-stream' ), wp_idea_stream_users_get_displayed_user_displayname() );
	} elseif ( wp_idea_stream_is_single_idea() ) {
		$new_title[] = single_post_title( '', false );
	} elseif ( is_tax() ) {
		$term = wp_idea_stream_get_current_term();
		if ( $term ) {
			$tax = get_taxonomy( $term->taxonomy );

			// Catch the term for later use
			wp_idea_stream_set_idea_var( 'current_term', $term );

			$new_title[] = single_term_title( '', false );
			$new_title[] = $tax->labels->name;
		}
	} elseif ( wp_idea_stream_is_signup() ) {
		$new_title[] = esc_html__( 'Create an account', 'wp-idea-stream' );
	} else {
		$new_title[] = esc_html__( 'Ideas', 'wp-idea-stream' );
	}

	// Compare new title with original title
	if ( empty( $new_title ) ) {
		return $title_array;
	}

	$title_array = array_diff( $title_array, $new_title );
	$new_title_array = array_merge( $title_array, $new_title );

	/**
	 * @param  string $new_title the filtered title
	 * @param  string $sep
	 * @param  string $seplocation
	 * @param  string $title the original title meta tag
	 */
	return apply_filters( 'wp_idea_stream_title', $new_title_array, $title_array, $new_title );
}

/**
 * Set the document title for IdeaStream pages
 *
 * @since  2.3.0
 *
 * @param  array  $document_title The WordPress Document title
 * @return array                  The IdeaStream Document title
 */
function wp_idea_stream_document_title_parts( $document_title = array() ) {
	if ( ! wp_idea_stream_is_ideastream() ) {
		return $document_title;
	}

	$new_document_title = $document_title;

	// Reset the document title if needed
	if ( ! wp_idea_stream_is_single_idea() ) {
		$title = (array) wp_idea_stream_title();

		// On user's profile, add some piece of info
		if ( wp_idea_stream_is_user_profile() && count( $title ) === 1 ) {
			// Seeing comments of the user
			if ( wp_idea_stream_is_user_profile_comments() ) {
				$title[] = __( 'Idea Comments', 'wp-idea-stream' );

				// Get the pagination page
				if ( get_query_var( wp_idea_stream_cpage_rewrite_id() ) ) {
					$cpage = get_query_var( wp_idea_stream_cpage_rewrite_id() );

				} elseif ( ! empty( $_GET[ wp_idea_stream_cpage_rewrite_id() ] ) ) {
					$cpage = $_GET[ wp_idea_stream_cpage_rewrite_id() ];
				}

				if ( ! empty( $cpage ) ) {
					$title['page'] = sprintf( __( 'Page %s', 'wp-idea-stream' ), (int) $cpage );
				}

			// Seeing Ratings for the user
			} elseif( wp_idea_stream_is_user_profile_rates() ) {
				$title[] = __( 'Idea Ratings', 'wp-idea-stream' );

			// Seeing The root profile
			} else {
				$title[] = __( 'Ideas', 'wp-idea-stream' );
			}
		}

		// Get WordPress Separator
		$sep = apply_filters( 'document_title_separator', '-' );

		$new_document_title['title'] = implode( " $sep ", array_filter( $title ) );;
	}

	// Set the site name if not already set.
	if ( ! isset( $new_document_title['site'] ) ) {
		$new_document_title['site'] = get_bloginfo( 'name', 'display' );
	}

	// Unset tagline for IdeaStream Pages
	if ( isset( $new_document_title['tagline'] ) ) {
		unset( $new_document_title['tagline'] );
	}

	return apply_filters( 'wp_idea_stream_document_title_parts', $new_document_title, $document_title );
}

/**
 * Remove the site description from title.
 * @todo we should make sure $wp_query->is_home is false in a future release
 *
 * @since 2.1.0
 *
 * @param  string $new_title the filtered title
 * @param  string $sep
 * @param  string $seplocation
 */
function wp_idea_stream_title_adjust( $title = '', $sep = '&raquo;', $seplocation = '' ) {
	if ( ! wp_idea_stream_is_ideastream() ) {
		return $title;
	}

	$site_description = get_bloginfo( 'description', 'display' );
	if ( ! empty( $sep ) ) {
		$site_description = ' ' . $sep . ' ' . $site_description;
	}

	$new_title = str_replace( $site_description, '', $title );

	return apply_filters( 'wp_idea_stream_title_adjust', $new_title, $title, $sep, $seplocation );
}

/**
 * Output a body class if in IdeaStream's territory
 *
 * Inspired by bbPress's bbp_body_class()
 *
 * @package WP Idea Stream
 * @subpackage core/template-functions
 *
 * @since 2.0.0
 *
 * @param  array $wp_classes
 * @param  array $custom_classes
 * @uses   wp_idea_stream_is_ideastream() to check if it's ideastream's territory
 * @return array the new Body Classes
 */
function wp_idea_stream_body_class( $wp_classes, $custom_classes = false ) {

	$ideastream_classes = array();

	/** IdeaStream **************************************************************/

	if ( wp_idea_stream_is_ideastream() ) {
		$ideastream_classes[] = 'ideastream';

	}

	/** Clean up **************************************************************/

	// Merge WP classes with IdeaStream classes and remove any duplicates
	$classes = array_unique( array_merge( (array) $ideastream_classes, (array) $wp_classes ) );

	/**
	 * @param array $classes returned classes
	 * @param array $ideastream_classes specific IdeaStream classes
	 * @param array $wp_classes regular WordPress classes
	 * @param array $custom_classes
	 */
	return apply_filters( 'wp_idea_stream_body_class', $classes, $ideastream_classes, $wp_classes, $custom_classes );
}

/**
 * Adds a 'type-page' class as the page template is the the most commonly targetted
 * as the root template.
 *
 * NB: TwentySixteen needs this to display the content on full available width
 *
 * @since  2.3.0
 *
 * @param  $wp_classes
 * @param  $theme_class
 * @return array Ideastream Post Classes
 */
function wp_idea_stream_post_class( $wp_classes, $theme_class ) {
	if ( wp_idea_stream_is_ideastream() ) {
		$classes = array_unique( array_merge( array( 'type-page' ), (array) $wp_classes ) );
	} else {
		$classes = $wp_classes;
	}

	return apply_filters( 'wp_idea_stream_body_class', $classes, $wp_classes, $theme_class );
}

/**
 * Reset postdata if needed
 *
 * @package WP Idea Stream
 * @subpackage core/template-functions
 *
 * @since 2.0.0
 *
 * @uses  wp_idea_stream_get_idea_var() to get the needs_reset global
 * @uses  wp_reset_postdata() to reset the post date
 * @uses  do_action() Call 'wp_idea_stream_maybe_reset_postdata' to perform custom actions after post reset
 */
function wp_idea_stream_maybe_reset_postdata() {
	if ( wp_idea_stream_get_idea_var( 'needs_reset' ) ) {
		wp_reset_postdata();

		/**
		 * Internally used in BuddyPress Groups pages
		 * to reset the $wp_query->post to BuddyPress Group's page one
		 */
		do_action( 'wp_idea_stream_maybe_reset_postdata' );
	}
}

/**
 * Filters nav menus looking for the root page to eventually make it current if not the
 * case although it's IdeaStream's territory
 *
 * @package WP Idea Stream
 * @subpackage core/template-functions
 *
 * @since 2.0.0
 *
 * @param  array  $sorted_menu_items list of menu items of the wp_nav menu
 * @param  array  $args
 * @uses   wp_idea_stream_is_ideastream() to make sure it's plugin's territory
 * @uses   apply_filters() call 'wp_idea_stream_wp_nav' to override the menu items classes
 * @return array  the menu items with specific classes if needed
 */
function wp_idea_stream_wp_nav( $sorted_menu_items = array(), $args = array() ) {

	if ( ! wp_idea_stream_is_ideastream() ) {
		return $sorted_menu_items;
	}

	foreach ( $sorted_menu_items as $key => $menu ) {

		if( wp_idea_stream_get_root_url() != $menu->url ){
			// maybe unset parent page if not the ideas root
			if ( in_array( 'current_page_parent', $menu->classes ) ) {
				$sorted_menu_items[$key]->classes = array_diff( $menu->classes, array( 'current_page_parent' ) );
			}
		} else {
			if ( ! in_array( 'current-menu-item', $menu->classes ) ) {
				$sorted_menu_items[$key]->classes = array_merge( $menu->classes, array( 'current-menu-item' ) );
			}
		}
	}

	return apply_filters( 'wp_idea_stream_wp_nav', $sorted_menu_items );
}

/**
 * Filters edit post link to avoid its display when needed
 *
 * @package WP Idea Stream
 * @subpackage core/template-functions
 *
 * @since 2.0.0
 *
 * @param  string $edit_link the link to edit the post
 * @param  int    $post_id   the post ID
 * @uses   wp_idea_stream_is_ideastream() to make sure it's plugin's territory
 * @uses   wp_idea_stream_user_can() to check user's capability
 * @uses   apply_filters() call 'wp_idea_stream_edit_post_link' to override the false value applyed by the plugin
 * @return mixed false if needed, original edit link otherwise
 */
function wp_idea_stream_edit_post_link( $edit_link = '', $post_id = 0 ) {
	/**
	 * using the capability check prevents edit link to display in case current user is the
	 * author of the idea and don't have the minimal capability to open the idea in WordPress
	 * Administration edit screen
	 */
	if ( wp_idea_stream_is_ideastream() && ( 0 === $post_id || ! wp_idea_stream_user_can( 'edit_ideas' ) ) ) {
		/**
		 * @param  bool false to be sure the edit link won't show
		 * @param  string $edit_link
		 * @param  int $post_id
		 */
		return apply_filters( 'wp_idea_stream_edit_post_link', false, $edit_link, $post_id );
	}

	return $edit_link;
}

/**
 * Use the Embed Profile template when an Embed profile is requested
 *
 * @since 2.3.0
 *
 * @param  string $template The WordPress Embed template
 * @return string           The appropriate template to use
 */
function wp_idea_stream_embed_profile( $template = '' ) {
	if ( ! wp_idea_stream_get_idea_var( 'is_user_embed' ) || ! wp_idea_stream_get_idea_var( 'is_user' ) ) {
		return $template;
	}

	return wp_idea_stream_get_template_part( 'embed', 'profile', false );
}

/**
 * Adds oEmbed discovery links in the website <head> for the IdeaStream user's profile root page.
 *
 * @since 2.3.0
 */
function wp_idea_stream_oembed_add_discovery_links() {
	if ( ! wp_idea_stream_is_user_profile_ideas() || ! wp_idea_stream_is_embed_profile() ) {
		return;
	}

	$user_link = wp_idea_stream_users_get_user_profile_url( wp_idea_stream_users_displayed_user_id(), '', true );
	$output = '<link rel="alternate" type="application/json+oembed" href="' . esc_url( get_oembed_endpoint_url( $user_link ) ) . '" />' . "\n";

	if ( class_exists( 'SimpleXMLElement' ) ) {
		$output .= '<link rel="alternate" type="text/xml+oembed" href="' . esc_url( get_oembed_endpoint_url( $user_link, 'xml' ) ) . '" />' . "\n";
	}

	/**
	 * Filter the oEmbed discovery links HTML.
	 *
	 * @since 2.3.0
	 *
	 * @param string $output HTML of the discovery links.
	 */
	echo apply_filters( 'wp_idea_stream_users_oembed_add_discovery_links', $output );
}
