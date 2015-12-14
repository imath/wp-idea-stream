<?php
/**
 * WP Idea Stream Ideas functions.
 *
 * Functions that are specifics to ideas
 *
 * @package WP Idea Stream
 * @subpackage ideas/functions
 *
 * @since 2.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/** Set/Get Idea(s) ***********************************************************/

/**
 * Default status used in idea 'get' queries
 *
 * By default, 'publish'
 *
 * @package WP Idea Stream
 * @subpackage ideas/functions
 *
 * @since 2.0.0
 *
 * @uses   wp_idea_stream_user_can() to check user's capbility to eventually add private ideas
 * @uses   apply_filters() call 'wp_idea_stream_ideas_get_status' to override the post status
 * @return array          the post status of ideas to retrieve
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
 * @package WP Idea Stream
 * @subpackage ideas/functions
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
 * @package WP Idea Stream
 * @subpackage ideas/functions
 *
 * @since 2.0.0
 *
 * @uses   wp_idea_stream_get_idea_var() get the globalized per page value
 * @uses   apply_filters() call 'wp_idea_stream_ideas_per_page' to override number of ideas per page
 * @return array           the post status of ideas to retrieve
 */
function wp_idea_stream_ideas_per_page() {
	return apply_filters( 'wp_idea_stream_ideas_per_page', wp_idea_stream_get_idea_var( 'per_page' ) );
}

/**
 * Get Ideas matching the query args
 *
 * @package WP Idea Stream
 * @subpackage ideas/functions
 *
 * @since 2.0.0
 *
 * @param  array  $args custom args to merge with default ones
 * @uses   wp_idea_stream_ideas_per_page() to get the preferences about pagination
 * @uses   wp_idea_stream_get_idea_var() to get globalized vars
 * @uses   wp_parse_args to merge custom args with default ones
 * @uses   WP_Idea_Stream_Idea::get() to get the ideas if the main query is not used or overriden
 * @uses   wp_idea_stream_set_idea_var() to globally set a var
 * @uses   apply_filters() call 'wp_idea_stream_ideas_get_ideas' to override/edit the retrieved ideas
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
 * @package WP Idea Stream
 * @subpackage ideas/functions
 *
 * @since 2.0.0
 *
 * @param  string $id_or_name ID or post_name of the idea to get
 * @uses   WP_Idea_Stream_Idea  to get the idea
 * @uses   apply_filters() call 'wp_idea_stream_ideas_get_idea' to override/edit the retrieved idea
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
 * @package WP Idea Stream
 * @subpackage ideas/functions
 *
 * @since 2.0.0
 *
 * @param  string $name the post_name of the idea to get
 * @uses   WP_Idea_Stream_Idea::get_idea_by_name()  to get the idea
 * @uses   apply_filters() call 'wp_idea_stream_ideas_get_idea_by_name' to override/edit the retrieved idea
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
 * @package WP Idea Stream
 * @subpackage ideas/functions
 *
 * @since 2.0.0
 *
 * @param  string $meta_key  the identifier of the meta key to register
 * @param  string $meta_args the arguments (array of callback functions)
 * @uses   wp_idea_stream_get_idea_var() to get the globalized array of registered metas
 * @uses   sanitize_key() to sanitize the new meta key
 * @uses   wp_parse_args() to merge args with defaults
 * @uses   wp_idea_stream_set_idea_var() to update the globalized array of registered metas
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
 * @package WP Idea Stream
 * @subpackage ideas/functions
 *
 * @since 2.0.0
 *
 * @param  int     $idea_id  the ID of the idea
 * @param  string  $meta_key the meta key to get
 * @param  bool    $single   whether to get an array of meta or unique one
 * @uses   sanitize_key() to sanitize a meta key
 * @uses   get_post_meta() to get an idea meta
 * @uses   apply_filters() call 'wp_idea_stream_meta_{$sanitized_key}_sanitize_display' to customize the
 *                         meta value sanitization to be displayed
 * @uses   sanitize_text_field() to sanitize a meta value
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
 * @package WP Idea Stream
 * @subpackage ideas/functions
 *
 * @since 2.0.0
 *
 * @param  int     $idea_id    the ID of the idea
 * @param  string  $meta_key   the meta key to update
 * @param  mixed   $meta_value the meta value to update
 * @uses   sanitize_key() to sanitize a meta key
 * @uses   apply_filters() call 'wp_idea_stream_meta_{$sanitized_key}_sanitize_db' to customize the
 *                         meta value sanitization to be saved in db
 * @uses   sanitize_text_field() to sanitize a meta value
 * @uses   update_post_meta() to update an idea meta
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
 * @package WP Idea Stream
 * @subpackage ideas/functions
 *
 * @since 2.0.0
 *
 * @param  int     $idea_id    the ID of the idea
 * @param  string  $meta_key   the meta key to update
 * @uses   sanitize_key() to sanitize a meta key
 * @uses   delete_post_meta() to delete an idea meta
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
 * @package WP Idea Stream
 * @subpackage ideas/functions
 *
 * @since 2.0.0
 *
 * @param  string $taxonomy the taxonomy identifier
 * @param  array  $args     the arguments to get the terms
 * @uses   wp_parse_args()  to merge custom with defaults
 * @uses   get_terms()      to get the taxonomy terms
 * @uses   apply_filters() call 'wp_idea_stream_ideas_get_terms' to override/edit the retrieved terms
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
 * @package WP Idea Stream
 * @subpackage ideas/functions
 *
 * @since 2.0.0
 *
 * @param  array  $ideaarr the posted arguments
 * @uses   wp_idea_stream_default_idea_status() to get default post status
 * @uses   apply_filters() call 'wp_idea_stream_ideas_insert_status' to override the post status
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
 * @package WP Idea Stream
 * @subpackage ideas/functions
 *
 * @since 2.0.0
 *
 * @param  int $idea_id The ID of the idea to edit
 * @uses   wp_check_post_lock() to check if the idea is locked to another user
 * @uses   wp_set_post_lock() to lock the idea to current user
 * @return int                the user id editing the idea
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
 * @package WP Idea Stream
 * @subpackage ideas/functions
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
 * @package WP Idea Stream
 * @subpackage ideas/functions
 *
 * @since 2.0.0
 *
 * @param  WP_Post $idea the idea object
 * @uses   wp_idea_stream_users_current_user_id() to get current user's ID
 * @uses   is_super_admin() to check for a super admin
 * @uses   wp_idea_stream_user_can() to check user's capability
 * @uses   get_post_meta() to check if the idea received rates
 * @uses   apply_filters() call 'wp_idea_stream_ideas_pre_can_edit' to disable the time lock, comments and rates checks
 *                         call 'wp_idea_stream_ideas_can_edit' to override user's edit capability
 *                         call 'wp_idea_stream_ideas_can_edit_time' to change the time period an idea can be edited
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
 * @package WP Idea Stream
 * @subpackage ideas/functions
 *
 * @since 2.0.0
 *
 * @param  array  $ideaarr the posted arguments
 * @uses   wp_idea_stream_users_current_user_id() to get current user ID
 * @uses   wp_idea_stream_ideas_insert_status() to get the status of the idea
 * @uses   wp_parse_id_list() to sanitize a list of ids
 * @uses   wp_idea_stream_get_category() to get the category taxonomy identifier
 * @uses   wp_idea_stream_get_tag() to get the tag taxonomy identifier
 * @uses   WP_Idea_Stream_Idea->save() to insert the idea
 * @uses   do_action() call 'wp_idea_stream_ideas_before_idea_save' to perform custom actions
 *                     before the idea is saved
 *                     call 'wp_idea_stream_ideas_after_{$hook}_idea' to perform custom actions
 *                     after the idea is saved
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
 * @package WP Idea Stream
 * @subpackage ideas/functions
 *
 * @since 2.0.0
 *
 * @param  WP_Post|int  $idea the idea object or its ID
 * @uses   get_post()   to get the idea object
 * @uses   get_permalink() to get the link to the idea
 * @uses   apply_filters() call 'wp_idea_stream_ideas_get_idea_permalink' to customize the url
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
 * @package WP Idea Stream
 * @subpackage ideas/functions
 *
 * @since 2.0.0
 *
 * @param  WP_Post $idea the idea object or its ID
 * @uses   wp_idea_stream_ideas_get_idea_permalink() to get the link to the idea
 * @uses   apply_filters() call 'wp_idea_stream_ideas_get_idea_comments_link' to customize the url
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
 * @package WP Idea Stream
 * @subpackage ideas/functions
 *
 * @since 2.0.0
 *
 * @uses   wp_idea_stream_is_ideastream() to check it's plugin territory
 * @uses   wp_idea_stream_is_single_idea() to check if a single idea is displayed
 * @uses   wp_idea_stream_is_edit() to check if the idea is being edited
 * @uses   wp_idea_stream_is_rating_disabled() to check if ratings are enabled
 * @uses   wp_idea_stream_count_ratings() to get the idea rating stats
 * @uses   wp_idea_stream_get_hint_list() to get the rating captions
 * @uses   wp_idea_stream_users_current_user_id() to get current user ID
 * @uses   wp_create_nonce() to create a nonce to be check when rating an idea
 * @uses   wp_idea_stream_user_can() to check user's capability
 * @uses   wp_enqueue_script() to add the needed scripts to WordPress queue
 * @uses   wp_idea_stream_get_js_script() to get a specific javascript
 * @uses   wp_idea_stream_get_version() to get plugin's version
 * @uses   wp_localize_script() to localized script datas
 * @uses   wp_idea_stream_is_addnew() to check the form is displayed
 * @uses   wp_idea_stream_get_single_idea_id() to get current idea ID
 * @uses   apply_filters() call 'wp_idea_stream_ideas_single_script' to add data to scripts used on single idea
 *                         call 'wp_idea_stream_ideas_form_script_vars' to add data to scripts used when using the form
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
			'ajaxurl'      => admin_url( 'admin-ajax.php', 'relative' ),
			'wait_msg'     => esc_html__( 'Saving your rating, please wait', 'wp-idea-stream' ),
			'success_msg'  => esc_html__( 'Thanks, the average rating is now:', 'wp-idea-stream' ),
			'error_msg'    => esc_html__( 'OOps, something went wrong', 'wp-idea-stream' ),
			'average_rate' => $ratings['average'],
			'rate_nb'      => $users_nb,
			'one_rate'     => esc_html__( 'One rate', 'wp-idea-stream' ),
			'x_rate'       => esc_html__( '% rates', 'wp-idea-stream' ),
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
		wp_localize_script( 'wp-idea-stream-script', 'wp_idea_stream_vars', apply_filters( 'wp_idea_stream_ideas_single_script', $js_vars ) );
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
		wp_localize_script( 'wp-idea-stream-script', 'wp_idea_stream_vars', apply_filters( 'wp_idea_stream_ideas_form_script_vars', $js_vars ) );
	}
}

/**
 * Builds the loop query arguments
 *
 * By default,it's an empty array as the plugin is first
 * using WordPress main query & retrieved posts. This function
 * allows to override it with custom arguments usfin the filter
 *
 * @package WP Idea Stream
 * @subpackage ideas/functions
 *
 * @since 2.0.0
 *
 * @param  string $type is this a single idea ?
 * @uses   apply_filters() call 'wp_idea_stream_ideas_query_args' to set different loop args
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
 * @package WP Idea Stream
 * @subpackage ideas/functions
 *
 * @since 2.0.0
 *
 * @uses   wp_idea_stream_is_rating_disabled() to check if ratings are enabled
 * @uses   apply_filters() call 'wp_idea_stream_ideas_get_order_options' to add / remove orderby filters
 */
function wp_idea_stream_ideas_get_order_options() {
	$order_options =  array(
		'date'           => __( 'Latest', 'wp-idea-stream' ),
		'comment_count'  => __( 'Most commented', 'wp-idea-stream' ),
	);

	// Only if not disabled.
	if ( ! wp_idea_stream_is_rating_disabled() ) {
		$order_options['rates_count'] = __( 'Best rated', 'wp-idea-stream' );
	}

	/**
	 * @param  array $order_options the list of available order options
	 */
	return apply_filters( 'wp_idea_stream_ideas_get_order_options', $order_options );
}

/**
 * Sets the title prefix in case of a private idea
 *
 * @package WP Idea Stream
 * @subpackage ideas/functions
 *
 * @since 2.0.0
 *
 * @param  string  $prefix the prefix to apply in case of a private idea
 * @param  WP_Post $idea   the idea object
 * @uses   wp_idea_stream_get_post_type() to check current post is an idea
 * @uses   apply_filters() call 'wp_idea_stream_ideas_private_title_prefix' to override the prefix
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
 * @package WP Idea Stream
 * @subpackage ideas/functions
 *
 * @since 2.0.0
 *
 * @param  string  $prefix the prefix to apply in case of a private idea
 * @param  WP_Post $idea   the idea object
 * @uses   wp_idea_stream_get_post_type() to check current post is an idea
 * @uses   apply_filters() call 'wp_idea_stream_ideas_private_title_prefix' to override the prefix
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
 * @package WP Idea Stream
 * @subpackage ideas/functions
 *
 * @since 2.0.0
 *
 * @uses   check_admin_referer() to check the request has been done from current site
 * @uses   wp_idea_stream_get_redirect_url() to get default redirect url
 * @uses   wp_idea_stream_user_can() to check user's capability
 * @uses   wp_idea_stream_add_message() to add a feddback message to user
 * @uses   wp_safe_redirect() to safely redirect the user and avoid duplicates
 * @uses   wp_idea_stream_ideas_save_idea() to save the idea
 * @uses   wp_idea_stream_get_form_url() to get the add new form url
 * @uses   get_post() to get the idea object
 * @uses   wp_idea_stream_moderation_message() to check for a custom moderation message
 * @uses   wp_idea_stream_ideas_get_idea_permalink() to get the idea link
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
		// Add feedback to the user
		wp_idea_stream_add_message( array(
			'type'    => 'error',
			'content' => __( 'You are not allowed to publish ideas', 'wp-idea-stream' ),
		) );

		// Redirect to main archive page
		wp_safe_redirect( $redirect );
		exit();
	}

	$posted = array_diff_key( $_POST['wp_idea_stream'], array( 'save' => 'submit' ) );

	// Title & content are required
	if ( empty( $posted['_the_title'] ) || empty( $posted['_the_content'] ) ) {
		// Add feedback to the user
		wp_idea_stream_add_message( array(
			'type'    => 'error',
			'content' => __( 'Title and description are required fields.', 'wp-idea-stream' ),
		) );

		// Simply stop, so that the user keeps the posted values.
		return;
	}

	$id = wp_idea_stream_ideas_save_idea( $posted );

	if ( empty( $id ) ) {
		// Add feedback to the user
		wp_idea_stream_add_message( array(
			'type'    => 'error',
			'content' => __( 'Something went wrong while trying to save your idea.', 'wp-idea-stream' ),
		) );

		// Redirect to an empty form
		wp_safe_redirect( wp_idea_stream_get_form_url() );
		exit();
	} else {
		$idea             = get_post( $id );
		$feedback_message = array();

		if ( ! empty( $posted['_the_thumbnail'] ) ) {
			$thumbnail = reset( $posted['_the_thumbnail'] );
			$sideload = WP_Idea_Stream_Ideas_Thumbnail::start( $thumbnail, $id );

			if ( is_wp_error( $sideload->result ) ) {
				$feedback_message[] = __( 'There was a problem saving the featured image, sorry.', 'wp-idea-stream' );
			}
		}

		if ( 'pending' == $idea->post_status ) {
			// Build pending message.
			$feedback_message['pending'] = __( 'Your idea is currently awaiting moderation.', 'wp-idea-stream' );

			// Check for a custom pending message
			$custom_pending_message = wp_idea_stream_moderation_message();
			if ( ! empty( $custom_pending_message ) ) {
				$feedback_message['pending'] = $custom_pending_message;
			}

		// redirect to the idea
		} else {
			$redirect = wp_idea_stream_ideas_get_idea_permalink( $idea );
		}

		if ( ! empty( $feedback_message ) ) {
			// Add feedback to the user
			wp_idea_stream_add_message( array(
				'type'    => 'info',
				'content' => join( ' ', $feedback_message ),
			) );
		}

		wp_safe_redirect( $redirect );
		exit();
	}
}

/**
 * Handles updating an idea
 *
 * @package WP Idea Stream
 * @subpackage ideas/functions
 *
 * @since 2.0.0
 *
 * @uses   check_admin_referer() to check the request has been done from current site
 * @uses   wp_idea_stream_get_redirect_url() to get default redirect url
 * @uses   get_query_var() to get the value of a specific query var
 * @uses   wp_idea_stream_get_post_type() to get the ideas post type identifier
 * @uses   get_queried_object() to try to get the idea object WordPress built
 * @uses   wp_idea_stream_ideas_get_idea_by_name() to get an idea object out of its post name
 * @uses   wp_idea_stream_user_can() to check user's capability
 * @uses   wp_idea_stream_add_message() to add a feddback message to user
 * @uses   wp_safe_redirect() to safely redirect the user and avoid duplicates
 * @uses   wp_idea_stream_ideas_save_idea() to save the idea
 * @uses   wp_idea_stream_get_form_url() to get the add new form url
 * @uses   wp_idea_stream_ideas_get_idea_permalink() to get the idea link
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
		wp_idea_stream_add_message( array(
			'type'    => 'error',
			'content' => __( 'The idea you are trying to edit does not seem to exist.', 'wp-idea-stream' ),
		) );

		// Redirect to main archive page
		wp_safe_redirect( $redirect );
		exit();
	}


	// Checks if the user can edit the idea
	if ( ! wp_idea_stream_ideas_can_edit( $idea ) ) {
		// Add feedback to the user
		wp_idea_stream_add_message( array(
			'type'    => 'error',
			'content' => __( 'You are not allowed to edit this idea.', 'wp-idea-stream' ),
		) );

		// Redirect to main archive page
		wp_safe_redirect( $redirect );
		exit();
	}

	$updated = array_diff_key( $_POST['wp_idea_stream'], array( 'save' => 'submit' ) );

	// Title & content are required
	if ( empty( $updated['_the_title'] ) || empty( $updated['_the_content'] ) ) {
		// Add feedback to the user
		wp_idea_stream_add_message( array(
			'type'    => 'error',
			'content' => __( 'Title and description are required fields.', 'wp-idea-stream' ),
		) );

		// Simply stop, so that the user keeps the posted values.
		return;
	}

	// Reset '_the_id' param to the ID of the idea found
	$updated['_the_id'] = $idea->ID;
	$feedback_message   = array();
	$featured_error     = __( 'There was a problem saving the featured image, sorry.', 'wp-idea-stream' );
	$featured_type      = 'info';

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
					$feedback_message[] = $featured_error;
				// Set the new Featured image
				} else {
					set_post_thumbnail( $idea->ID, $thumbnail );
				}
			} else {
				$sideload = WP_Idea_Stream_Ideas_Thumbnail::start( $thumbnail_src, $idea->ID );

				if ( is_wp_error( $sideload->result ) ) {
					$feedback_message[] = $featured_error;
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
		$featured_type    = 'error';
		$feedback_message = __( 'Something went wrong while trying to update your idea.', 'wp-idea-stream' );

		// Redirect to the form
		$redirect = wp_idea_stream_get_form_url( wp_idea_stream_edit_slug(), $idea_name );

	// Redirect to the idea
	} else {
		$redirect = wp_idea_stream_ideas_get_idea_permalink( $id );
	}

	if ( ! empty( $feedback_message ) ) {
		// Add feedback to the user
		wp_idea_stream_add_message( array(
			'type'    => $featured_type,
			'content' => join( ' ', $feedback_message ),
		) );
	}

	wp_safe_redirect( $redirect );
	exit();
}

/** Sticky ideas **************************************************************/

/**
 * Gets the sticky ideas
 *
 * @package WP Idea Stream
 * @subpackage ideas/functions
 *
 * @since 2.0.0
 *
 * @uses   get_option() to get the stickies
 * @uses   apply_filters() call 'wp_idea_stream_ideas_get_stickies' to alter this list
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
 * @package WP Idea Stream
 * @subpackage ideas/functions
 *
 * @since 2.0.0
 *
 * @param  array    $posts The array of retrieved posts.
 * @param  WP_Query The WP_Query instance
 * @uses   wp_idea_stream_is_sticky_enabled() to check sticking ideas is enabled
 * @uses   wp_idea_stream_ideas_get_stickies() to get the sticky ideas
 * @uses   is_post_type_archive() to check the ideas archive page is displayed
 * @uses   apply_filters() call 'wp_idea_stream_ideas_get_stickies' to alter this list
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
 * @package WP Idea Stream
 * @subpackage ideas/functions
 *
 * @since 2.0.0
 *
 * @param  int $id The idea ID
 * @param  array $stickies the list of IDs of the sticky ideas
 * @uses   wp_idea_stream() to get plugin's main instance
 * @uses   wp_idea_stream_ideas_get_stickies() to get the sticky ideas
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
 * @package WP Idea Stream
 * @subpackage ideas/functions
 *
 * @since 2.0.0
 *
 * @param  WP_Post $idea   the idea object
 * @uses   apply_filters() call 'wp_idea_stream_ideas_admin_no_sticky' to alter this list
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
