<?php
/**
 * WP Idea Stream Ideas functions.
 *
 * Functions that are specifics to ideas
 *
 * @package WP Idea Stream\ideas
 *
 * @since 2.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/** Set/Get Idea(s) ***********************************************************/

/**
 * Default status used in idea 'get' queries
 *
 * By default, 'publish'
 *
 * @since 2.0.0
 *
 * @return array the post status of ideas to retrieve
 */
function wp_idea_stream_ideas_get_status() {
	$status = array( 'publish' );

	if ( wp_idea_stream_user_can( 'read_private_ideas' ) ) {
		$status[] = 'private';
	}

	/**
	 * Use this filter to override post status of ideas to retieve
	 *
	 * @param  array $status
	 */
	return apply_filters( 'wp_idea_stream_ideas_get_status', $status );
}

/**
 * Gets all WordPress built in post status (to be used in filters)
 *
 * @since 2.0.0
 *
 * @param  array  $status
 * @return array          the available post status
 */
function wp_idea_stream_ideas_get_all_status( $status = array() ) {
	return array_keys( get_post_statuses() );
}

/**
 * How much ideas to retrieve per page ?
 *
 * By default, same value than regular posts
 * Uses the WordPress posts per page setting
 *
 * @since 2.0.0
 *
 * @return array the post status of ideas to retrieve
 */
function wp_idea_stream_ideas_per_page() {
	return apply_filters( 'wp_idea_stream_ideas_per_page', wp_idea_stream_get_idea_var( 'per_page' ) );
}

/**
 * Get Ideas matching the query args
 *
 * @since 2.0.0
 *
 * @param  array  $args custom args to merge with default ones
 * @return array        requested ideas
 */
function wp_idea_stream_ideas_get_ideas( $args = array() ) {
	$get_args = array();
	$ideas    = array();

	$default = array(
		'author'     => 0,
		'per_page'   => wp_idea_stream_ideas_per_page(),
		'page'       => 1,
		'search'     => '',
		'exclude'    => '',
		'include'    => '',
		'orderby'    => 'date',
		'order'      => 'DESC',
		'meta_query' => array(),
		'tax_query'  => array(),
	);

	if ( ! empty( $args ) ) {
		$get_args = $args;
	} else {
		$main_query = wp_idea_stream_get_idea_var( 'main_query' );

		if ( ! empty( $main_query['query_vars'] ) ) {
			$get_args = $main_query['query_vars'];
			unset( $main_query['query_vars'] );
		}

		$ideas = $main_query;
	}

	// Parse the args
	$r = wp_parse_args( $get_args, $default );

	if ( empty( $ideas ) ) {
		$ideas = WP_Idea_Stream_Idea::get( $r );

		// Reset will need to be done at the end of the loop
		wp_idea_stream_set_idea_var( 'needs_reset', true );
	}

	$ideas = array_merge( $ideas, array( 'get_args' => $r ) );

	/**
	 * @param  array $ideas     associative array to find ideas, total count and loop args
	 * @param  array $r         merged args
	 * @param  array $get_args  args before merge
	 */
	return apply_filters( 'wp_idea_stream_ideas_get_ideas', $ideas, $r, $get_args );
}

/**
 * Gets an idea with additional metas and terms
 *
 * @since 2.0.0
 *
 * @param  string $id_or_name ID or post_name of the idea to get
 * @return WP_Idea_Stream_Idea  the idea object
 */
function wp_idea_stream_ideas_get_idea( $id_or_name = '' ) {
	if ( empty( $id_or_name ) ) {
		return false;
	}

	$idea = new WP_Idea_Stream_Idea( $id_or_name );

	/**
	 * @param  WP_Idea_Stream_Idea $idea the idea object
	 * @param  mixed               $id_or_name  the ID or slug of the idea
	 */
	return apply_filters( 'wp_idea_stream_ideas_get_idea', $idea, $id_or_name );
}

/**
 * Gets an idea by its slug without additional metas or terms
 *
 * @since 2.0.0
 *
 * @param  string $name the post_name of the idea to get
 * @return WP_Post the idea object
 */
function wp_idea_stream_ideas_get_idea_by_name( $name = '' ) {
	if ( empty( $name ) ) {
		return false;
	}

	$idea = WP_Idea_Stream_Idea::get_idea_by_name( $name );

	/**
	 * @param  WP_Post $idea the idea object
	 * @param  string  $name the post_name of the idea
	 */
	return apply_filters( 'wp_idea_stream_ideas_get_idea_by_name', $idea, $name );
}

/**
 * Registers a new ideas meta
 *
 * @since 2.0.0
 *
 * @param  string $meta_key  the identifier of the meta key to register
 * @param  string $meta_args the arguments (array of callback functions)
 */
function wp_idea_stream_ideas_register_meta( $meta_key = '', $meta_args = '' ) {
	if ( empty( $meta_key ) || ! is_array( $meta_args ) ) {
		return false;
	}

	$ideastream_metas = wp_idea_stream_get_idea_var( 'ideastream_metas' );

	if ( empty( $ideastream_metas ) ) {
		$ideastream_metas = array();
	}

	$key = sanitize_key( $meta_key );

	$args = wp_parse_args( $meta_args, array(
		'meta_key' => $key,
		'label'    => '',
		'admin'    => 'wp_idea_stream_meta_admin_display',
		'form'     => '',
		'single'   => 'wp_idea_stream_meta_single_display',
	) );

	$ideastream_metas[ $key ] = (object) $args;

	wp_idea_stream_set_idea_var( 'ideastream_metas', $ideastream_metas );
}

/**
 * Gets an idea meta data
 *
 * @since 2.0.0
 *
 * @param  int     $idea_id  the ID of the idea
 * @param  string  $meta_key the meta key to get
 * @param  bool    $single   whether to get an array of meta or unique one
 * @return mixed             the meta value
 */
function wp_idea_stream_ideas_get_meta( $idea_id = 0, $meta_key = '', $single = true ) {
	if ( empty( $idea_id ) || empty( $meta_key ) ) {
		return false;
	}

	$sanitized_key   = sanitize_key( $meta_key );
	$sanitized_value = false;

	$meta_value = get_post_meta( $idea_id, '_ideastream_' . $sanitized_key, $single );

	if ( empty( $meta_value ) ) {
		return false;
	}

	// Custom sanitization
	if ( has_filter( "wp_idea_stream_meta_{$sanitized_key}_sanitize_display" ) ) {
		/**
		 * Use this filter if you need to apply custom sanitization to
		 * the meta value
		 * @param  mixed   $meta_value the meta value
		 * @param  string  $meta_key  the meta_key
		 */
		$sanitized_value = apply_filters( "wp_idea_stream_meta_{$sanitized_key}_sanitize_display", $meta_value, $meta_key );

	// Fallback to generic sanitization
	} else {

		if ( is_array( $meta_value) ) {
			$sanitized_value = array_map( 'sanitize_text_field', $meta_value );
		} else {
			$sanitized_value = sanitize_text_field( $meta_value );
		}
	}

	return apply_filters( 'wp_idea_stream_ideas_get_meta', $sanitized_value, $meta_key, $idea_id );
}

/**
 * Updates an idea meta data
 *
 * @since 2.0.0
 *
 * @param  int     $idea_id    the ID of the idea
 * @param  string  $meta_key   the meta key to update
 * @param  mixed   $meta_value the meta value to update
 * @return bool                the update meta result
 */
function wp_idea_stream_ideas_update_meta( $idea_id = 0, $meta_key = '', $meta_value = '' ) {
	if ( empty( $idea_id ) || empty( $meta_key ) || empty( $meta_value ) ) {
		return false;
	}

	$sanitized_key   = sanitize_key( $meta_key );
	$sanitized_value = false;

	// Custom sanitization
	if ( has_filter( "wp_idea_stream_meta_{$sanitized_key}_sanitize_db" ) ) {
		/**
		 * Use this filter if you need to apply custom sanitization to
		 * the meta value
		 * @param  mixed   $meta_value the meta value
		 * @param  string  $meta_key  the meta_key
		 */
		$sanitized_value = apply_filters( "wp_idea_stream_meta_{$sanitized_key}_sanitize_db", $meta_value, $meta_key );

	// Fallback to generic sanitization
	} else {

		if ( is_array( $meta_value) ) {
			$sanitized_value = array_map( 'sanitize_text_field', $meta_value );
		} else {
			$sanitized_value = sanitize_text_field( $meta_value );
		}
	}

	if ( empty( $sanitized_value ) ) {
		return false;
	}

	return update_post_meta( $idea_id, '_ideastream_' . $sanitized_key, $sanitized_value );
}

/**
 * Deletes an idea meta data
 *
 * @since 2.0.0
 *
 * @param  int     $idea_id    the ID of the idea
 * @param  string  $meta_key   the meta key to update
 * @return bool                the delete meta result
 */
function wp_idea_stream_ideas_delete_meta( $idea_id = 0, $meta_key = '' ) {
	if ( empty( $idea_id ) || empty( $meta_key ) ) {
		return false;
	}

	$sanitized_key = sanitize_key( $meta_key );

	return delete_post_meta( $idea_id, '_ideastream_' . $sanitized_key );
}

/**
 * Gets idea terms given a taxonomy and args
 *
 * @since 2.0.0
 *
 * @param  string $taxonomy the taxonomy identifier
 * @param  array  $args     the arguments to get the terms
 * @return array|WP_Error List of Term Objects and their children. Will return WP_Error, if any of $taxonomies
 *                        do not exist.
 */
function wp_idea_stream_ideas_get_terms( $taxonomy = '', $args = array() ) {
	if ( empty( $taxonomy ) || ! is_array( $args ) ) {
		return false;
	}

	// Merge args
	$term_args = wp_parse_args( $args, array(
		'orderby'    => 'count',
		'hide_empty' => 0
	) );

	// get the terms for the requested taxonomy and args
	$terms = get_terms( $taxonomy, $term_args );

	/**
	 * @param  array|WP_Error $terms    the list of terms of the taxonomy
	 * @param  string         $taxonomy the taxonomy of the terms retrieved
	 * @param  array          $args     the arguments to get the terms
	 */
	return apply_filters( 'wp_idea_stream_ideas_get_terms', $terms, $taxonomy, $args );
}

/**
 * Sets the post status of an idea
 *
 * @since 2.0.0
 *
 * @param  array  $ideaarr the posted arguments
 * @return string          the post status of the idea
 */
function wp_idea_stream_ideas_insert_status( $ideaarr = array() ) {
	/**
	 * Used internally to set the status to private in case the idea
	 * is posted from a non public BuddyPress group
	 * @see  WP_Idea_Stream_Group->group_idea_status()
	 *
	 * @param  string  the default post status for an idea
	 * @param  array   $ideaarr  the arguments of the idea to save
	 */
	return apply_filters( 'wp_idea_stream_ideas_insert_status', wp_idea_stream_default_idea_status(), $ideaarr );
}

/**
 * Checks if another user is editing an idea, if not
 * locks the idea for the current user.
 *
 * @since 2.0.0
 *
 * @param  int $idea_id The ID of the idea to edit
 * @return int          the user id editing the idea
 */
function wp_idea_stream_ideas_lock_idea( $idea_id = 0 ) {
	$user_id = false;

	// Bail if no ID to check
	if ( empty( $idea_id ) ) {
		return $user_id;
	}

	// Include needed file
	require_once( ABSPATH . '/wp-admin/includes/post.php' );

	$user_id = wp_check_post_lock( $idea_id );

	// If not locked, then lock it as current user is editing it.
	if( empty( $user_id ) ) {
		wp_set_post_lock( $idea_id );
	}

	return $user_id;
}

/**
 * HeartBeat callback to check if an idea is being edited by an admin
 *
 * @since 2.0.0
 *
 * @param  array  $response the heartbeat response
 * @param  array  $data     the data sent by heartbeat
 * @return array            IdeaStream's response to heartbeat
 */
function wp_idea_stream_ideas_heartbeat_check_locked( $response = array(), $data = array() ) {

	if ( empty( $data['ideastream_heartbeat_current_idea'] ) ) {
		return $response;
	}

	$response['ideastream_heartbeat_response'] = wp_idea_stream_ideas_lock_idea( $data['ideastream_heartbeat_current_idea'] );

	return $response;
}

/**
 * Checks if a user can edit an idea
 *
 * A user can edit the idea if :
 * - he is the author
 *   - and idea was created 0 to 5 mins ago
 *   - no comment was posted on the idea
 *   - no rates was given to the idea
 *   - nobody else is currently editing the idea
 * - he is a super admin. But in this case he shouldn't
 *   arrive here but should be in IdeaStream Administration edit
 *   screen.
 * - BuddyPress Group admins can also edit ideas.
 *
 * @since 2.0.0
 *
 * @param  WP_Post $idea the idea object
 * @return bool          whether the user can edit the idea (true), or not (false)
 */
function wp_idea_stream_ideas_can_edit( $idea = null ) {
	// Default to can't edit !
	$retval = false;

	// Bail if we can't check anything
	if ( empty( $idea ) || ! is_a( $idea, 'WP_Post' ) ) {
		return $retval;
	}

	// Map to edit others ideas if current user is not the author
	if ( wp_idea_stream_users_current_user_id() != $idea->post_author ) {

		// Do not edit ideas of the super admin
		if ( ! is_super_admin( $idea->post_author ) ) {
			return wp_idea_stream_user_can( 'edit_others_ideas' );
		} else {
			return $retval;
		}

	}

	/** Now we're dealing with author's capacitiy to edit the idea ****************/

	/**
	 * First, give the possibility to early override
	 *
	 * If you want to avoid the comment/rate and time lock, you
	 * can use this filter.
	 *
	 * Used internally in buddypress/groups to allow a group admin to edit his own idea.
	 *
	 * @param bool whether to directly check user's capacity
	 * @param WP_Post $idea   the idea object
	 */
	$early_can_edit = apply_filters( 'wp_idea_stream_ideas_pre_can_edit', false, $idea );

	if ( ! empty( $early_can_edit ) || is_super_admin() ) {
		return wp_idea_stream_user_can( 'edit_idea', $idea->ID );
	}

	// Idea was commented or rated
	if ( ! empty( $idea->comment_count ) || get_post_meta( $idea->ID, '_ideastream_average_rate', true ) ) {
		return $retval;
	}

	/**
	 * This part is based on bbPress's bbp_past_edit_lock() function
	 *
	 * In the case of an Idea Management system, i find the way bbPress
	 * manage the time a content can be edited by its author very interesting
	 * and simple (simplicity is allways great!)
	 */

	// Bail if empty date
	if ( empty( $idea->post_date_gmt ) ) {
		return $retval;
	}

	// Period of time
	$lockable  = apply_filters( 'wp_idea_stream_ideas_can_edit_time', '+5 minutes' );

	// Now
	$cur_time  = current_time( 'timestamp', true );

	// Add lockable time to post time
	$lock_time = strtotime( $lockable, strtotime( $idea->post_date_gmt ) );

	// Compare
	if ( $cur_time <= $lock_time ) {
		$retval = wp_idea_stream_user_can( 'edit_idea', $idea->ID );
	}

	/**
	 * Late filter
	 *
	 * @param bool    $retval whether to allow the user's to edit the idea
	 * @param WP_Post $idea   the idea object
	 */
	return apply_filters( 'wp_idea_stream_ideas_can_edit', $retval, $idea );
}

/**
 * Saves an idea entry in posts table
 *
 * @since 2.0.0
 *
 * @param  array  $ideaarr the posted arguments
 * @return int    the ID of the created or updated idea
 */
function wp_idea_stream_ideas_save_idea( $ideaarr = array() ) {
	if ( ! is_array( $ideaarr ) ) {
		return false;
	}

	if ( empty( $ideaarr['_the_title'] ) || empty( $ideaarr['_the_content'] ) ) {
		return false;
	}

	// Init update vars
	$update         = false;
	$old_taxonomies = array();
	$old_metas      = array();

	if ( ! empty( $ideaarr['_the_id'] ) ) {
		/**
		 * Passing the id attribute to WP_Idea_Stream_Idea will get the previous version of the idea
		 * In this case we don't need to set the author or status
		 */
		$idea = new WP_Idea_Stream_Idea( absint( $ideaarr['_the_id'] ) );

		if ( ! empty( $idea->id ) ) {
			$update = true;

			// Get old metas
			if ( ! empty( $idea->metas['keys'] ) ) {
				$old_metas = $idea->metas['keys'];
			}

			// Get old taxonomies
			if ( ! empty( $idea->taxonomies ) )  {
				$old_taxonomies = $idea->taxonomies;
			}

		// If we don't find the idea, stop!
		} else {
			return false;
		}

	} else {
		$idea         = new WP_Idea_Stream_Idea();
		$idea->author = wp_idea_stream_users_current_user_id();
		$idea->status = wp_idea_stream_ideas_insert_status( $ideaarr );
	}

	// Set the title and description of the idea
	$idea->title       = $ideaarr['_the_title'];
	$idea->description = $ideaarr['_the_content'];

	// Handling categories
	if ( ! empty( $ideaarr['_the_category'] ) && is_array( $ideaarr['_the_category'] ) ) {
		$categories = wp_parse_id_list( $ideaarr['_the_category'] );

		$idea->taxonomies = array(
			wp_idea_stream_get_category() => $categories
		);

	// In case of an update, we need to eventually remove all categories
	} else if ( empty( $ideaarr['_the_category'] ) && ! empty( $old_taxonomies[ wp_idea_stream_get_category() ] ) ) {

		// Reset categories if some were set
		if ( is_array( $idea->taxonomies ) ) {
			$idea->taxonomies[ wp_idea_stream_get_category() ] = array();
		} else {
			$idea->taxonomies = array( wp_idea_stream_get_category() => array() );
		}
	}

	// Handling tags
	if ( ! empty( $ideaarr['_the_tags'] ) && is_array( $ideaarr['_the_tags'] ) ) {
		$tags = array_map( 'strip_tags', $ideaarr['_the_tags'] );

		$tags = array(
			wp_idea_stream_get_tag() => join( ',', $tags )
		);

		if ( ! empty( $idea->taxonomies ) ) {
			$idea->taxonomies = array_merge( $idea->taxonomies, $tags );
		} else {
			$idea->taxonomies = $tags;
		}

	// In case of an update, we need to eventually remove all tags
	} else if ( empty( $ideaarr['_the_tags'] ) && ! empty( $old_taxonomies[ wp_idea_stream_get_tag() ] ) ) {

		// Reset tags if some were set
		if ( is_array( $idea->taxonomies ) ) {
			$idea->taxonomies[ wp_idea_stream_get_tag() ] = '';
		} else {
			$idea->taxonomies = array( wp_idea_stream_get_tag() => '' );
		}
	}

	// Handling metas. By default none, but can be useful for plugins or
	// when playing with BuddyPress groups.
	if ( ! empty( $ideaarr['_the_metas'] ) && is_array( $ideaarr['_the_metas'] ) ) {
		$idea->metas = $ideaarr['_the_metas'];
	}

	// Check if some metas need to be deleted
	if ( ! empty( $old_metas ) && is_array( $idea->metas ) ) {
		$to_delete = array_diff( $old_metas, array_keys( $idea->metas ) );

		if ( ! empty( $to_delete ) ) {
			$to_delete = array_fill_keys( $to_delete, 0 );
			$idea->metas = array_merge( $idea->metas, $to_delete );
		}
	}

	/**
	 * Do stuff before the idea is saved
	 *
	 * @param  array $ideaarr the posted values
	 * @param  bool  $update  whether it's an update or not
	 */
	do_action( 'wp_idea_stream_ideas_before_idea_save', $ideaarr, $update );

	$saved_id = $idea->save();

	if ( ! empty( $saved_id ) ) {

		$hook = 'insert';

		if ( ! empty( $update ) ) {
			$hook = 'update';
		}

		/**
		 * Do stuff after the idea was saved
		 *
		 * Call wp_idea_stream_ideas_after_insert_idea for a new idea
		 * Call wp_idea_stream_ideas_after_update_idea for an updated idea
		 *
		 * @param  int    $inserted_id the inserted id
		 * @param  object $idea the idea
		 */
		do_action( "wp_idea_stream_ideas_after_{$hook}_idea", $saved_id, $idea );
	}

	return $saved_id;
}

/** Idea urls *****************************************************************/

/**
 * Gets the permalink to the idea
 *
 * @since 2.0.0
 *
 * @param  WP_Post|int  $idea the idea object or its ID
 * @return string|bool     the permalink to the idea, false if the idea is not set
 */
function wp_idea_stream_ideas_get_idea_permalink( $idea = null ) {
	// Bail if not set
	if ( empty( $idea ) ) {
		return false;
	}

	// Not a post, try to get it
	if ( ! is_a( $idea, 'WP_Post' ) ) {
		$idea = get_post( $idea );
	}

	if ( empty( $idea->ID ) ) {
		return false;
	}

	/**
	 * @param  string        permalink to the idea
	 * @param  WP_Post $idea the idea object
	 */
	return apply_filters( 'wp_idea_stream_ideas_get_idea_permalink', get_permalink( $idea ), $idea );
}

/**
 * Gets the comment link of an idea
 *
 * @since 2.0.0
 *
 * @param  WP_Post $idea the idea object or its ID
 * @return string          the comment link of an idea
 */
function wp_idea_stream_ideas_get_idea_comments_link( $idea = null ) {
	$comments_link = wp_idea_stream_ideas_get_idea_permalink( $idea ) . '#comments';

	/**
	 * @param  string  $comments_link comment link
	 * @param  WP_Post $idea          the idea object
	 */
	return apply_filters( 'wp_idea_stream_ideas_get_idea_comments_link', $comments_link, $idea );
}

/** Template functions ********************************************************/

/**
 * Adds needed scripts to rate the idea or add tags to it
 *
 * @since 2.0.0
 */
function wp_idea_stream_ideas_enqueue_scripts() {
	if ( ! wp_idea_stream_is_ideastream() ) {
		return;
	}

	// Single idea > ratings
	if ( wp_idea_stream_is_single_idea() && ! wp_idea_stream_is_edit() && ! wp_idea_stream_is_rating_disabled() ) {

		$ratings = (array) wp_idea_stream_count_ratings();
		$users_nb = count( $ratings['users'] );
		$hintlist = (array) wp_idea_stream_get_hint_list();

		$js_vars = array(
			'raty_loaded'  => 1,
			'root_url'     => esc_url_raw( rest_url( trailingslashit( 'wp/v2' ) ) ),
			'nonce'        => wp_create_nonce( 'wp_rest' ),
			'ajaxurl'      => admin_url( 'admin-ajax.php', 'relative' ),
			'wait_msg'     => esc_html__( 'Saving your rating; please wait', 'wp-idea-stream' ),
			'success_msg'  => esc_html__( 'Thanks! The average rating is now:', 'wp-idea-stream' ),
			'error_msg'    => esc_html__( 'Oops! Something went wrong', 'wp-idea-stream' ),
			'average_rate' => $ratings['average'],
			'rate_nb'      => $users_nb,
			'one_rate'     => esc_html__( 'One rating', 'wp-idea-stream' ),
			'x_rate'       => esc_html__( '% ratings', 'wp-idea-stream' ),
			'readonly'     => true,
			'can_rate'     => wp_idea_stream_user_can( 'rate_ideas' ),
			'not_rated'    => esc_html__( 'Not rated yet', 'wp-idea-stream' ),
			'hints'        => $hintlist,
			'hints_nb'     => count( $hintlist ),
			'wpnonce'      => wp_create_nonce( 'wp_idea_stream_rate' ),
		);

		$user_id = wp_idea_stream_users_current_user_id();

		if ( wp_idea_stream_user_can( 'rate_ideas' ) ) {
			$js_vars['readonly'] = ( 0 != $users_nb ) ? in_array( $user_id, $ratings['users'] ) : false;
		}

		wp_enqueue_script( 'wp-idea-stream-script', wp_idea_stream_get_js_script( 'script' ), array( 'jquery-raty' ), wp_idea_stream_get_version(), true );
		wp_idea_stream_get_js_script_localized_data( $js_vars, 'wp-idea-stream-script', 'wp_idea_stream_ideas_single_script' );
	}

	// Form > tags
	if ( wp_idea_stream_is_addnew() || wp_idea_stream_is_edit() ) {
		// Default dependencies
		$deps = array( 'tagging' );

		// Defaul js vars
		$js_vars = array(
			'tagging_loaded'  => 1,
			'taginput_name'   => 'wp_idea_stream[_the_tags][]',
			'duplicate_tag'   => __( 'Duplicate tag:',       'wp-idea-stream' ),
			'forbidden_chars' => __( 'Forbidden character:', 'wp-idea-stream' ),
			'forbidden_words' => __( 'Forbidden word:',      'wp-idea-stream' ),
		);

		// Add HeartBeat if idea is being edited
		if ( wp_idea_stream_is_edit() ) {
			$deps = array_merge( $deps, array( 'heartbeat' ) );
			$js_vars = array_merge( $js_vars, array(
				'idea_id' => wp_idea_stream_get_single_idea_id(),
				'pulse'   => 'fast',
				'warning' => esc_html__( 'An admin is currently editing this idea, please try to edit your idea later.', 'wp-idea-stream' ),
			) );
		}

		// Enqueue and localize script
		wp_enqueue_script( 'wp-idea-stream-script', wp_idea_stream_get_js_script( 'script' ), $deps, wp_idea_stream_get_version(), true );
		wp_idea_stream_get_js_script_localized_data( $js_vars, 'wp-idea-stream-script', 'wp_idea_stream_ideas_form_script_vars' );
	}
}

/**
 * Builds the loop query arguments
 *
 * By default,it's an empty array as the plugin is first
 * using WordPress main query & retrieved posts. This function
 * allows to override it with custom arguments usfin the filter
 *
 * @since 2.0.0
 *
 * @param  string $type is this a single idea ?
 * @return array        the loop args
 */
function wp_idea_stream_ideas_query_args( $type = '' ) {
	/**
	 * Use this filter to overide loop args
	 * @see wp_idea_stream_ideas_has_ideas() for the list of available ones
	 *
	 * @param  array by default an empty array
	 */
	$query_args = apply_filters( 'wp_idea_stream_ideas_query_args', array() );

	if ( 'single' == $type ) {
		$query_arg = array_intersect_key( $query_args, array( 'idea_name' => false ) );
	}

	return $query_args;
}

/**
 * Sets the available orderby possible filters
 *
 * @since 2.0.0
 */
function wp_idea_stream_ideas_get_order_options() {
	$order_options =  array(
		'date'           => __( 'Latest', 'wp-idea-stream' ),
		'comment_count'  => __( 'Most commented', 'wp-idea-stream' ),
	);

	// Only if not disabled.
	if ( ! wp_idea_stream_is_rating_disabled() ) {
		$order_options['rates_count'] = __( 'Highest Rating', 'wp-idea-stream' );
	}

	/**
	 * @param  array $order_options the list of available order options
	 */
	return apply_filters( 'wp_idea_stream_ideas_get_order_options', $order_options );
}

/**
 * Sets the title prefix in case of a private idea
 *
 * @since 2.0.0
 *
 * @param  string  $prefix the prefix to apply in case of a private idea
 * @param  WP_Post $idea   the idea object
 * @return string          the title prefix
 */
function wp_idea_stream_ideas_private_title_prefix( $prefix = '', $idea = null ) {
	// Not an idea ? Bail.
	if ( empty( $idea ) || wp_idea_stream_get_post_type() != $idea->post_type ) {
		return $prefix;
	}

	/**
	 * @param  string        the prefix output
	 * @param  WP_Post $idea the idea object
	 */
	return apply_filters( 'wp_idea_stream_ideas_private_title_prefix', '<span class="private-idea"></span> %s', $idea );
}

/**
 * Sets the title prefix in case of a password protected idea
 *
 * @since 2.0.0
 *
 * @param  string  $prefix the prefix to apply in case of a private idea
 * @param  WP_Post $idea   the idea object
 * @return string          the title prefix
 */
function wp_idea_stream_ideas_protected_title_prefix( $prefix = '', $idea = null ) {
	// Not an idea ? Bail.
	if ( empty( $idea ) || wp_idea_stream_get_post_type() != $idea->post_type ) {
		return $prefix;
	}

	/**
	 * @param  string        the prefix output
	 * @param  WP_Post $idea the idea object
	 */
	return apply_filters( 'wp_idea_stream_ideas_protected_title_prefix', '<span class="protected-idea"></span> %s', $idea );
}

/** Handle Idea actions *******************************************************/

/**
 * Handles posting ideas
 *
 * @since 2.0.0
 */
function wp_idea_stream_ideas_post_idea() {
	// Bail if not a post request
	if ( 'POST' != strtoupper( $_SERVER['REQUEST_METHOD'] ) ) {
		return;
	}

	// Bail if not a post idea request
	if ( empty( $_POST['wp_idea_stream'] ) || ! is_array( $_POST['wp_idea_stream'] ) ) {
		return;
	}

	// Bail if it's an update
	if ( ! empty( $_POST['wp_idea_stream']['_the_id'] ) ) {
		return;
	}

	// Check nonce
	check_admin_referer( 'wp_idea_stream_save' );

	$redirect = wp_idea_stream_get_redirect_url();

	// Check capacity
	if ( ! wp_idea_stream_user_can( 'publish_ideas' ) ) {
		// Redirect to main archive page and inform the user he cannot publish talks.
		wp_safe_redirect( add_query_arg( 'error', 3, $redirect ) );
		exit();
	}

	$posted = array_diff_key( $_POST['wp_idea_stream'], array( 'save' => 'submit' ) );

	// Title & content are required
	if ( empty( $posted['_the_title'] ) || empty( $posted['_the_content'] ) ) {
		// Add feedback to the user
		wp_idea_stream_add_message( array(
			'error' => array( 4 ),
		) );

		// Simply stop, so that the user keeps the posted values.
		return;
	}

	$id = wp_idea_stream_ideas_save_idea( $posted );

	if ( empty( $id ) ) {
		// Redirect to an empty form
		wp_safe_redirect( add_query_arg( 'error', 5, wp_idea_stream_get_form_url() ) );
		exit();

	} else {
		$idea             = get_post( $id );
		$feedback_message = array(
			'error'   => array(),
			'success' => array( 3 ),
			'info'    => array(),
		);

		if ( ! empty( $posted['_the_thumbnail'] ) ) {
			$thumbnail = reset( $posted['_the_thumbnail'] );
			$sideload = WP_Idea_Stream_Ideas_Thumbnail::start( $thumbnail, $id );

			if ( is_wp_error( $sideload->result ) ) {
				$feedback_message['error'][] = 6;
			}
		}

		if ( 'pending' == $idea->post_status ) {
			// Use the pending message.
			$feedback_message['info'][] = 2;

		// redirect to the idea
		} else {
			$redirect = wp_idea_stream_ideas_get_idea_permalink( $idea );
		}

		wp_safe_redirect( wp_idea_stream_add_feedback_args( array_filter( $feedback_message ), $redirect ) );
		exit();
	}
}

/**
 * Handles updating an idea
 *
 * @since 2.0.0
 */
function wp_idea_stream_ideas_update_idea() {
	global $wp_query;
	// Bail if not a post request
	if ( 'POST' != strtoupper( $_SERVER['REQUEST_METHOD'] ) ) {
		return;
	}

	// Bail if not a post idea request
	if ( empty( $_POST['wp_idea_stream'] ) || ! is_array( $_POST['wp_idea_stream'] ) ) {
		return;
	}

	// Bail if it's not an update
	if ( empty( $_POST['wp_idea_stream']['_the_id'] ) ) {
		return;
	}

	// Check nonce
	check_admin_referer( 'wp_idea_stream_save' );

	$redirect = wp_idea_stream_get_redirect_url();

	// Get idea name
	$idea_name = get_query_var( wp_idea_stream_get_post_type() );

	// Get Idea Object
	$idea = get_queried_object();

	// If queried object doesn't match or wasn't helpfull, try to get the idea using core function
	if ( empty( $idea->post_name ) || empty( $idea_name ) || $idea_name != $idea->post_name ) {
		$idea = wp_idea_stream_ideas_get_idea_by_name( $idea_name );
	}

	// Found no idea, redirect and inform the user
	if ( empty( $idea->ID ) ) {
		wp_safe_redirect( add_query_arg( 'error', 9, $redirect ) );
		exit();
	}


	// Checks if the user can edit the idea
	if ( ! wp_idea_stream_ideas_can_edit( $idea ) ) {
		// Redirect to main archive page
		wp_safe_redirect( add_query_arg( 'error', 2, $redirect ) );
		exit();
	}

	$updated = array_diff_key( $_POST['wp_idea_stream'], array( 'save' => 'submit' ) );

	// Title & content are required
	if ( empty( $updated['_the_title'] ) || empty( $updated['_the_content'] ) ) {
		// Add feedback to the user
		wp_idea_stream_add_message( array(
			'error' => array( 4 ),
		) );

		// Simply stop, so that the user keeps the posted values.
		return;
	}

	// Reset '_the_id' param to the ID of the idea found
	$updated['_the_id'] = $idea->ID;
	$feedback_message   = array(
		'error'   => array(),
		'success' => array(),
		'info'    => array(),
	);

	// Take care of the featured image
	$thumbnail_id = (int) get_post_thumbnail_id( $idea );

	if ( ! empty( $updated['_the_thumbnail'] ) ) {
		$thumbnail_src = key( $updated['_the_thumbnail'] );
		$thumbnail     = reset( $updated['_the_thumbnail'] );

		// Update the Featured image
		if ( ! is_numeric( $thumbnail ) || $thumbnail_id !== (int) $thumbnail ) {
			if ( is_numeric( $thumbnail ) ) {
				// validate the attachment
				if ( ! get_post( $thumbnail ) ) {
					$feedback_message['error'][] = 6;
				// Set the new Featured image
				} else {
					set_post_thumbnail( $idea->ID, $thumbnail );
				}
			} else {
				$sideload = WP_Idea_Stream_Ideas_Thumbnail::start( $thumbnail_src, $idea->ID );

				if ( is_wp_error( $sideload->result ) ) {
					$feedback_message['error'][] = 6;
				}
			}
		}

	// Delete the featured image
	} elseif ( ! empty( $thumbnail_id ) ) {
		delete_post_thumbnail( $idea );
	}

	// Update the idea
	$id = wp_idea_stream_ideas_save_idea( $updated );

	if ( empty( $id ) ) {
		// Set the feedback for the user
		$feedback_message['error'][] = 10;

		// Redirect to the form
		$redirect = wp_idea_stream_get_form_url( wp_idea_stream_edit_slug(), $idea_name );

	// Redirect to the idea
	} else {
		$feedback_message['success'][] = 4;
		$redirect = wp_idea_stream_ideas_get_idea_permalink( $id );
	}

	wp_safe_redirect( wp_idea_stream_add_feedback_args( array_filter( $feedback_message ), $redirect ) );
	exit();
}

/** Sticky ideas **************************************************************/

/**
 * Gets the sticky ideas
 *
 * @since 2.0.0
 *
 * @return array the list of IDs of sticked to front ideas
 */
function wp_idea_stream_ideas_get_stickies() {
	$sticky_ideas = get_option( 'sticky_ideas', array() );

	/**
	 * @param  array $sticky_ideas the ideas sticked to front archive page
	 */
	return apply_filters( 'wp_idea_stream_ideas_get_stickies', $sticky_ideas );
}

/**
 * Edit WP_Query posts to append sticky ideas
 *
 * Simply a "copy paste" of how WordPress deals with sticky posts
 *
 * @since 2.0.0
 *
 * @param  array    $posts The array of retrieved posts.
 * @param  WP_Query The WP_Query instance
 * @return array the posts with stickies if some are found
 */
function wp_idea_stream_ideas_stick_ideas( $posts = array(), $wp_query = null ) {
	// Bail if sticky is disabled
	if ( ! wp_idea_stream_is_sticky_enabled() ) {
		return $posts;
	}

	$q = $wp_query->query_vars;
	$post_type = $q['post_type'];

	if ( 'ideas' != $post_type ) {
		return $posts;
	}

	$page = absint( $q['paged'] );
	$search = $q['s'];

	if ( ! empty( $q['orderby'] ) ) {
		return $posts;
	}

	$sticky_posts = wp_idea_stream_ideas_get_stickies();

	if ( wp_idea_stream_is_admin() ) {
		return $posts;
	}

	$post_type_landing_page = is_post_type_archive( $post_type ) && $page <= 1 && empty( $search );

	if ( empty( $post_type_landing_page ) || empty( $sticky_posts ) || ! empty( $q['ignore_sticky_posts'] ) ) {
		return $posts;
	}

	// Put sticky ideas at the top of the posts array
	$num_posts = count( $posts );
	$sticky_offset = 0;
	// Loop over posts and relocate stickies to the front.
	for ( $i = 0; $i < $num_posts; $i++ ) {
		if ( in_array( $posts[$i]->ID, $sticky_posts ) ) {
			$sticky_post = $posts[$i];
			// Remove sticky from current position
			array_splice( $posts, $i, 1 );
			// Move to front, after other stickies
			array_splice( $posts, $sticky_offset, 0, array( $sticky_post ) );
			// Increment the sticky offset. The next sticky will be placed at this offset.
			$sticky_offset++;
			// Remove post from sticky posts array
			$offset = array_search( $sticky_post->ID, $sticky_posts );
			unset( $sticky_posts[$offset] );
		}
	}

	// If any posts have been excluded specifically, Ignore those that are sticky.
	if ( ! empty( $sticky_posts ) && ! empty( $q['post__not_in'] ) )
		$sticky_posts = array_diff( $sticky_posts, $q['post__not_in'] );

	// Fetch sticky posts that weren't in the query results
	if ( ! empty( $sticky_posts ) ) {
		$stickies = get_posts( array(
			'post__in' => $sticky_posts,
			'post_type' => $post_type,
			'post_status' => 'publish',
			'nopaging' => true
		) );

		foreach ( $stickies as $sticky_post ) {
			$sticky_post->is_sticky = true;
			array_splice( $posts, $sticky_offset, 0, array( $sticky_post ) );
			$sticky_offset++;
		}
	}

	return $posts;
}

/**
 * Checks if an idea is sticky
 *
 * @since 2.0.0
 *
 * @param  int $id The idea ID
 * @return bool true if it's a sticky idea, false otherwise
 */
function wp_idea_stream_ideas_is_sticky( $id = 0, $stickies = array() ) {
	$id = absint( $id );

	if ( empty( $id ) ) {
		if ( ! wp_idea_stream()->query_loop->idea->ID ) {
			return false;
		} else {
			$id = wp_idea_stream()->query_loop->idea->ID;
		}
	}

	if ( empty( $stickies ) ) {
		$stickies = wp_idea_stream_ideas_get_stickies();
	}

	if ( ! is_array( $stickies ) ) {
		return false;
	}

	if ( in_array( $id, $stickies ) ) {
		return true;
	}

	return false;
}

/**
 * Make sure sticky ideas are not private or password protected
 *
 * @since 2.0.0
 *
 * @param  WP_Post $idea   the idea object
 * @return bool true if the idea cannot be sticked, false otherwise
 */
function wp_idea_stream_ideas_admin_no_sticky( $idea = null ) {
	// bail if not set
	if ( empty( $idea ) ) {
		return false;
	}

	$no_sticky = ( 'private' == $idea->post_status || ! empty( $idea->post_password ) );

	/**
	 * @param  bool $no_sticky
	 * @param  WP_Post $idea   the idea object
	 */
	return (bool) apply_filters( 'wp_idea_stream_ideas_admin_no_sticky', $no_sticky, $idea );
}

/** Featured images ***********************************************************/

/**
 * Simulate a tinymce plugin to intercept images once added to the
 * WP Editor
 *
 * @since 2.3.0
 *
 * @param  array $tinymce_plugins Just what the name of the param says!
 * @return array Tiny MCE plugins + IdeaStream one if needed
 */
function wp_idea_stream_ideas_tiny_mce_plugins( $tinymce_plugins = array() ) {
	if ( ! wp_idea_stream_featured_images_allowed() || ! current_theme_supports( 'post-thumbnails' ) ) {
		return $tinymce_plugins;
	}

	if ( ! wp_idea_stream_is_addnew() && ! wp_idea_stream_is_edit() ) {
		return $tinymce_plugins;
	}

	return array_merge( $tinymce_plugins, array( 'wpIdeaStreamListImages' => wp_idea_stream_get_js_script( 'featured-images' ) ) );
}

function wp_idea_stream_do_embed( $content ) {
	global $wp_embed;

	return $wp_embed->autoembed( $content );
}
