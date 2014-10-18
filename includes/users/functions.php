<?php
/**
 * WP Idea Stream Users functions.
 *
 * Functions specific to users
 *
 * @package WP Idea Stream
 * @subpackage users/functions
 *
 * @since 2.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/** Set/Get User Datas **********************************************************/

/**
 * Gets current user ID
 *
 * @package WP Idea Stream
 * @subpackage users/functions
 *
 * @since 2.0.0
 *
 * @uses   wp_idea_stream() to get plugin's main instance
 * @return int the logged in user ID
 */
function wp_idea_stream_users_current_user_id() {
	return (int) wp_idea_stream()->current_user->ID;
}

/**
 * Gets current user user nicename
 *
 * @package WP Idea Stream
 * @subpackage users/functions
 *
 * @since 2.0.0
 *
 * @uses   wp_idea_stream() to get plugin's main instance
 * @return string the logged in username
 */
function wp_idea_stream_users_current_user_nicename() {
	return wp_idea_stream()->current_user->user_nicename;
}

/**
 * Gets displayed user ID
 *
 * @package WP Idea Stream
 * @subpackage users/functions
 *
 * @since 2.0.0
 *
 * @uses   wp_idea_stream() to get plugin's main instance
 * @uses   apply_filters() call 'wp_idea_stream_users_displayed_user_id' to override value
 * @return int the displayed user ID
 */
function wp_idea_stream_users_displayed_user_id() {
	return (int) apply_filters( 'wp_idea_stream_users_displayed_user_id', wp_idea_stream()->displayed_user->ID );
}

/**
 * Gets displayed user user nicename
 *
 * @package WP Idea Stream
 * @subpackage users/functions
 *
 * @since 2.0.0
 *
 * @uses   wp_idea_stream() to get plugin's main instance
 * @uses   apply_filters() call 'wp_idea_stream_users_get_displayed_user_username' to override value
 * @return string the displayed user username
 */
function wp_idea_stream_users_get_displayed_user_username() {
	return apply_filters( 'wp_idea_stream_users_get_displayed_user_username', wp_idea_stream()->displayed_user->user_nicename );
}

/**
 * Gets displayed user display name
 *
 * @package WP Idea Stream
 * @subpackage users/functions
 *
 * @since 2.0.0
 *
 * @uses   wp_idea_stream() to get plugin's main instance
 * @uses   apply_filters() call 'wp_idea_stream_users_get_displayed_user_displayname' to override value
 * @return string the displayed user display name
 */
function wp_idea_stream_users_get_displayed_user_displayname() {
	return apply_filters( 'wp_idea_stream_users_get_displayed_user_displayname', wp_idea_stream()->displayed_user->display_name );
}

/**
 * Gets displayed user description
 *
 * @package WP Idea Stream
 * @subpackage users/functions
 *
 * @since 2.0.0
 *
 * @uses   wp_idea_stream() to get plugin's main instance
 * @uses   apply_filters() call 'wp_idea_stream_users_get_displayed_user_description' to override value
 * @return string the displayed user description
 */
function wp_idea_stream_users_get_displayed_user_description() {
	return apply_filters( 'wp_idea_stream_users_get_displayed_user_description', wp_idea_stream()->displayed_user->description );
}

/**
 * Gets one specific or all attribute about a user
 *
 * @package WP Idea Stream
 * @subpackage users/functions
 *
 * @since 2.0.0
 *
 * @uses   get_user_by() to get a user thanks to a specific field (eg: 'id' or 'slug')
 * @return mixed WP_User/string/array/int the user object or one of its attribute
 */
function wp_idea_stream_users_get_user_data( $field = '', $value ='', $attribute = 'all'  ) {
	$user = get_user_by( $field, $value );

	if ( empty( $user ) ) {
		return false;
	}

	if ( 'all' == $attribute ) {
		return $user;
	} else {
		return $user->{$attribute};
	}
}

/** User urls *****************************************************************/

/**
 * Gets the displayed user's profile url
 *
 * @package WP Idea Stream
 * @subpackage users/functions
 *
 * @since 2.0.0
 *
 * @param  string $type profile, rates, comments
 * @uses   wp_idea_stream_users_displayed_user_id() to get a user ID
 * @uses   wp_idea_stream_users_get_displayed_user_username() to get a user user nicename
 * @uses   apply_filters() call 'wp_idea_stream_users_get_displayed_profile_url' to override url
 * @return string url of the profile type
 */
function wp_idea_stream_users_get_displayed_profile_url( $type = 'profile' ) {
	$user_id = wp_idea_stream_users_displayed_user_id();
	$username = wp_idea_stream_users_get_displayed_user_username();

	$profile_url = call_user_func_array( 'wp_idea_stream_users_get_user_' . $type . '_url', array( $user_id, $username ) );

	/**
	 * @param  string $profile_url url to the profile part
	 * @param  string $type the requested part (profile, rates or comments)
	 */
	return apply_filters( 'wp_idea_stream_users_get_displayed_profile_url', $profile_url, $type );
}

/**
 * Gets the logged in user's profile url
 *
 * @package WP Idea Stream
 * @subpackage users/functions
 *
 * @since 2.0.0
 *
 * @param  string $type profile, rates, comments
 * @uses   wp_idea_stream_users_current_user_id() to get a user ID
 * @uses   wp_idea_stream_users_current_user_nicename() to get a user user nicename
 * @uses   apply_filters() call 'wp_idea_stream_users_get_logged_in_profile_url' to override url
 * @return string url of the profile type
 */
function wp_idea_stream_users_get_logged_in_profile_url( $type = 'profile' ) {
	$user_id = wp_idea_stream_users_current_user_id();
	$username = wp_idea_stream_users_current_user_nicename();

	$profile_url = call_user_func_array( 'wp_idea_stream_users_get_user_' . $type . '_url', array( $user_id, $username ) );

	/**
	 * @param  string $profile_url url to the profile part
	 * @param  string $type the requested part (profile, rates or comments)
	 */
	return apply_filters( 'wp_idea_stream_users_get_logged_in_profile_url', $profile_url, $type );
}

/**
 * Gets URL to the main profile page of a user
 *
 * Inspired by bbPress's bbp_get_user_profile_url() function
 *
 * @package WP Idea Stream
 * @subpackage users/functions
 *
 * @since 2.0.0
 *
 * @global $wp_rewrite
 * @param  int $user_id User id
 * @param  string $user_nicename Optional. User nicename
 * @uses   wp_idea_stream_user_slug() To get user slug
 * @uses   wp_idea_stream_user_rewrite_id() to get rewrite id
 * @uses   wp_idea_stream_users_get_user_data() get user nicename
 * @uses   home_url() to get blog home url
 * @uses   add_query_arg() to build url on default permalink setting
 * @uses   apply_filters() Calls 'wp_idea_stream_users_get_user_profile_url' to override the url
 * @return string User profile url
 */
function wp_idea_stream_users_get_user_profile_url( $user_id = 0, $user_nicename = '' ) {
	global $wp_rewrite;

	// Bail if no user id provided
	if ( empty( $user_id ) ) {
		return false;
	}

	/**
	 * Used internally to "early" override the profile Url by the one of BuddyPress profile
	 * @see WP_Idea_Stream_BuddyPress->filter_user_domains in buddypress/loader
	 *
	 * @param int    $user_id       the user ID
	 * @param string $user_nicename the username
	 */
	$early_profile_url = apply_filters( 'wp_idea_stream_users_pre_get_user_profile_url', (int) $user_id, $user_nicename );
	if ( is_string( $early_profile_url ) ) {
		return $early_profile_url;
	}

	// Pretty permalinks
	if ( $wp_rewrite->using_permalinks() ) {
		$url = $wp_rewrite->root . wp_idea_stream_user_slug() . '/%' . wp_idea_stream_user_rewrite_id() . '%';

		// Get username if not passed
		if ( empty( $user_nicename ) ) {
			$user_nicename = wp_idea_stream_users_get_user_data( 'id', $user_id, 'user_nicename' );
		}

		$url = str_replace( '%' . wp_idea_stream_user_rewrite_id() . '%', $user_nicename, $url );
		$url = home_url( user_trailingslashit( $url ) );

	// Unpretty permalinks
	} else {
		$url = add_query_arg( array( wp_idea_stream_user_rewrite_id() => $user_id ), home_url( '/' ) );
	}

	/**
	 * Filter the user profile url once IdeaStream has built it
	 *
	 * @param string $url           Profile Url
	 * @param int    $user_id       the user ID
	 * @param string $user_nicename the username
	 */
	return apply_filters( 'wp_idea_stream_users_get_user_profile_url', $url, $user_id, $user_nicename );
}

/**
 * Gets URL to the rates profile page of a user
 *
 * Inspired by bbPress's bbp_get_user_profile_url() function
 *
 * @package WP Idea Stream
 * @subpackage users/functions
 *
 * @since 2.0.0
 *
 * @global $wp_rewrite
 * @param  int $user_id User id
 * @param  string $user_nicename Optional. User nicename
 * @uses   wp_idea_stream_user_slug() To get user slug
 * @uses   wp_idea_stream_user_rewrite_id() to get rewrite id
 * @uses   wp_idea_stream_user_rates_slug() to get the rates slug
 * @uses   wp_idea_stream_users_get_user_data() get user nicename
 * @uses   home_url() to get blog home url
 * @uses   add_query_arg() to build url on default permalink setting
 * @uses   wp_idea_stream_user_rates_rewrite_id() to get the rates rewrite id
 * @uses   apply_filters() Calls 'wp_idea_stream_users_get_user_rates_url' to override the url
 * @return string Rates profile url
 */
function wp_idea_stream_users_get_user_rates_url( $user_id = 0, $user_nicename = '' ) {
	global $wp_rewrite;

	// Bail if no user id provided
	if ( empty( $user_id ) ) {
		return false;
	}

	/**
	 * Used internally to "early" override the rates Url by the one of BuddyPress profile
	 * @see WP_Idea_Stream_BuddyPress->filter_user_domains in buddypress/loader
	 *
	 * @param int    $user_id       the user ID
	 * @param string $user_nicename the username
	 */
	$early_profile_url = apply_filters( 'wp_idea_stream_users_pre_get_user_rates_url', (int) $user_id, $user_nicename );
	if ( is_string( $early_profile_url ) ) {
		return $early_profile_url;
	}


	// Pretty permalinks
	if ( $wp_rewrite->using_permalinks() ) {
		$url = $wp_rewrite->root . wp_idea_stream_user_slug() . '/%' . wp_idea_stream_user_rewrite_id() . '%/' . wp_idea_stream_user_rates_slug();

		// Get username if not passed
		if ( empty( $user_nicename ) ) {
			$user_nicename = wp_idea_stream_users_get_user_data( 'id', $user_id, 'user_nicename' );
		}

		$url = str_replace( '%' . wp_idea_stream_user_rewrite_id() . '%', $user_nicename, $url );
		$url = home_url( user_trailingslashit( $url ) );

	// Unpretty permalinks
	} else {
		$url = add_query_arg( array( wp_idea_stream_user_rewrite_id() => $user_id, wp_idea_stream_user_rates_rewrite_id() => '1' ), home_url( '/' ) );
	}

	/**
	 * Filter the rates profile url once IdeaStream has built it
	 *
	 * @param string $url           Rates profile Url
	 * @param int    $user_id       the user ID
	 * @param string $user_nicename the username
	 */
	return apply_filters( 'wp_idea_stream_users_get_user_rates_url', $url, $user_id, $user_nicename );
}

/**
 * Gets URL to the comments profile page of a user
 *
 * Inspired by bbPress's bbp_get_user_profile_url() function
 *
 * @package WP Idea Stream
 * @subpackage users/functions
 *
 * @since 2.0.0
 *
 * @global $wp_rewrite
 * @param  int $user_id User id
 * @param  string $user_nicename Optional. User nicename
 * @uses   wp_idea_stream_user_slug() To get user slug
 * @uses   wp_idea_stream_user_rewrite_id() to get rewrite id
 * @uses   wp_idea_stream_user_comments_slug() to get the comments slug
 * @uses   wp_idea_stream_users_get_user_data() get user nicename
 * @uses   home_url() to get blog home url
 * @uses   add_query_arg() to build url on default permalink setting
 * @uses   wp_idea_stream_user_comments_rewrite_id() to get the comments rewrite id
 * @uses   apply_filters() Calls 'wp_idea_stream_users_get_user_comments_url' to override the url
 * @return string Comments profile url
 */
function wp_idea_stream_users_get_user_comments_url( $user_id = 0, $user_nicename = '' ) {
	global $wp_rewrite;

	// Bail if no user id provided
	if ( empty( $user_id ) ) {
		return false;
	}

	/**
	 * Used internally to "early" override the comments Url by the one of BuddyPress profile
	 * @see WP_Idea_Stream_BuddyPress->filter_user_domains in buddypress/loader
	 *
	 * @param int    $user_id       the user ID
	 * @param string $user_nicename the username
	 */
	$early_profile_url = apply_filters( 'wp_idea_stream_users_pre_get_user_comments_url', (int) $user_id, $user_nicename );
	if ( is_string( $early_profile_url ) ) {
		return $early_profile_url;
	}


	// Pretty permalinks
	if ( $wp_rewrite->using_permalinks() ) {
		$url = $wp_rewrite->root . wp_idea_stream_user_slug() . '/%' . wp_idea_stream_user_rewrite_id() . '%/' . wp_idea_stream_user_comments_slug();

		// Get username if not passed
		if ( empty( $user_nicename ) ) {
			$user_nicename = wp_idea_stream_users_get_user_data( 'id', $user_id, 'user_nicename' );
		}

		$url = str_replace( '%' . wp_idea_stream_user_rewrite_id() . '%', $user_nicename, $url );
		$url = home_url( user_trailingslashit( $url ) );

	// Unpretty permalinks
	} else {
		$url = add_query_arg( array( wp_idea_stream_user_rewrite_id() => $user_id, wp_idea_stream_user_comments_rewrite_id() => '1' ), home_url( '/' ) );
	}

	/**
	 * Filter the comments profile url once IdeaStream has built it
	 *
	 * @param string $url           Rates profile Url
	 * @param int    $user_id       the user ID
	 * @param string $user_nicename the username
	 */
	return apply_filters( 'wp_idea_stream_users_get_user_comments_url', $url, $user_id, $user_nicename );
}

/**
 * Gets the signup url
 *
 * @package WP Idea Stream
 * @subpackage users/functions
 *
 * @since 2.1.0
 *
 * @global  $wp_rewrite
 * @return string signup url
 */
function wp_idea_stream_users_get_signup_url() {
	global $wp_rewrite;

	/**
	 * Early filter to override form url before being built
	 *
	 * @param mixed false or url to override
	 */
	$early_signup_url = apply_filters( 'wp_idea_stream_users_pre_get_signup_url', false );

	if ( ! empty( $early_form_url ) ) {
		return $early_form_url;
	}

	// Pretty permalinks
	if ( $wp_rewrite->using_permalinks() ) {
		$signup_url = $wp_rewrite->root . wp_idea_stream_action_slug() . '/%' . wp_idea_stream_action_rewrite_id() . '%';

		$signup_url = str_replace( '%' . wp_idea_stream_action_rewrite_id() . '%', wp_idea_stream_signup_slug(), $signup_url );
		$signup_url = home_url( user_trailingslashit( $signup_url ) );

	// Unpretty permalinks
	} else {
		$signup_url = add_query_arg( array( wp_idea_stream_action_rewrite_id() => wp_idea_stream_signup_slug() ), home_url( '/' ) );
	}

	/**
	 * Filter to override form url after being built
	 *
	 * @param string url to override
	 */
	return apply_filters( 'wp_idea_stream_get_form_url', $signup_url );
}

/** Template functions ********************************************************/

/**
 * Enqueues Users description editing scripts
 *
 * @package WP Idea Stream
 * @subpackage users/functions
 *
 * @since 2.0.0
 *
 * @uses wp_idea_stream_is_ideastream() to check it's plugin's territory
 * @uses wp_idea_stream_is_current_user_profile() to check the current user is on his profile
 * @uses wp_enqueue_script() to add the script to WordPress queue
 * @uses wp_idea_stream_get_js_script() to get a specific javascript
 * @uses wp_idea_stream_get_version() to get plugin's version
 * @uses wp_localize_script() to internatianlize data used in the script
 * @uses apply_filters() Calls 'wp_idea_stream_users_current_profile_script' to override/add new datas
 */
function wp_idea_stream_users_enqueue_scripts() {
	if ( ! wp_idea_stream_is_ideastream() ) {
		return;
	}

	if ( wp_idea_stream_is_current_user_profile() ) {
		wp_enqueue_script( 'wp-idea-stream-script', wp_idea_stream_get_js_script( 'script' ), array( 'jquery' ), wp_idea_stream_get_version(), true );
		wp_localize_script( 'wp-idea-stream-script', 'wp_idea_stream_vars', apply_filters( 'wp_idea_stream_users_current_profile_script', array(
			'profile_editing' => 1
		) ) );
	}
}

/**
 * Builds user's profile nav
 *
 * @package WP Idea Stream
 * @subpackage users/functions
 *
 * @since 2.0.0
 *
 * @param  int $user_id User id
 * @param  string $user_nicename Optional. User nicename
 * @uses   wp_idea_stream_users_get_user_profile_url() to get user main profile url
 * @uses   wp_idea_stream_is_user_profile_ideas() to check whether main profile is currently displayed
 * @uses   sanitize_title() to sanitize the nav slug
 * @uses   wp_idea_stream_users_get_user_comments_url() to get user comments profile url
 * @uses   wp_idea_stream_is_user_profile_comments() to check whether comments profile is currently displayed
 * @uses   wp_idea_stream_user_comments_slug() to get user comments slug
 * @uses   wp_idea_stream_is_rating_disabled() to check ratings functionnality is available
 * @uses   wp_idea_stream_users_get_user_rates_url() to get user rates profile url
 * @uses   wp_idea_stream_is_user_profile_rates() to check whether rates profile is currently displayed
 * @uses   wp_idea_stream_user_rates_slug() to get user rates slug
 * @uses   apply_filters() Calls 'wp_idea_stream_users_get_profile_nav_items' to override/add new datas
 * @return array the nav items organized in an associative array
 */
function wp_idea_stream_users_get_profile_nav_items( $user_id = 0, $username ='' ) {
	// Bail if no id or username are provided.
	if ( empty( $user_id ) || empty( $username ) ) {
		return array();
	}

	$nav_items = array(
		'profile' => array(
			'title'   => __( 'Published', 'wp-idea-stream' ),
			'url'     => wp_idea_stream_users_get_user_profile_url( $user_id, $username ),
			'current' => wp_idea_stream_is_user_profile_ideas(),
			'slug'    => sanitize_title( _x( 'ideas', 'user ideas profile slug for BuddyPress use', 'wp-idea-stream' ) ),
		),
		'comments' => array(
			'title'   => __( 'Commented', 'wp-idea-stream' ),
			'url'     => wp_idea_stream_users_get_user_comments_url( $user_id, $username ),
			'current' => wp_idea_stream_is_user_profile_comments(),
			'slug'    => wp_idea_stream_user_comments_slug(),
		),
	);

	if ( ! wp_idea_stream_is_rating_disabled() ) {
		$nav_items['rates'] = array(
			'title'   => __( 'Rated', 'wp-idea-stream' ),
			'url'     => wp_idea_stream_users_get_user_rates_url( $user_id, $username ),
			'current' => wp_idea_stream_is_user_profile_rates(),
			'slug'    => wp_idea_stream_user_rates_slug(),
		);
	}

	/**
	 * Filter the available user's profile nav items
	 *
	 * @param array  $nav_items     the nav items
	 * @param int    $user_id       the user ID
	 * @param string $username the username
	 */
	return apply_filters( 'wp_idea_stream_users_get_profile_nav_items', $nav_items, $user_id, $username );
}

/** Handle User actions *******************************************************/

/**
 * Edit User's profile description
 *
 * @package WP Idea Stream
 * @subpackage users/functions
 *
 * @since 2.0.0
 *
 * @uses check_admin_referer() to check the request was made on the site
 * @uses wp_idea_stream_users_displayed_user_id() to get displayed user id
 * @uses wp_idea_stream_users_get_user_profile_url() to build redirect url
 * @uses wp_idea_stream_add_message() to give feedback to the user
 * @uses wp_safe_redirect() to safely redirect the user
 * @uses wp_kses_allowed_html() to get allowed tags for user's description
 * @uses wp_kses to sanitize user's descripton
 * @uses update_user_meta() to save the edited description
 * @uses do_action() Calls 'wp_idea_stream_users_profile_description_updated' to perform actions once description edited
 */
function wp_idea_stream_users_profile_description_update() {
	// Bail if not a post request
	if ( 'POST' != strtoupper( $_SERVER['REQUEST_METHOD'] ) ) {
		return;
	}

	// Bail if not a post idea request
	if ( empty( $_POST['wp_idea_stream_profile'] ) ) {
		return;
	}

	// Check nonce
	check_admin_referer( 'wp_idea_stream_update_description', '_wpis_nonce' );

	$user_id = wp_idea_stream_users_displayed_user_id();

	$redirect = wp_idea_stream_users_get_user_profile_url( $user_id, wp_idea_stream_users_get_displayed_user_username() );

	$user_description = str_replace( array( '<div>', '</div>'), "\n", $_POST['wp_idea_stream_profile']['description'] );
	$user_description = rtrim( $user_description, "\n" );

	if ( empty( $user_description ) ) {
		wp_idea_stream_add_message( array(
			'type'    => 'error',
			'content' => __( 'Please, enter some content in your description', 'wp-idea-stream' ),
		) );

		wp_safe_redirect( $redirect );
		exit();
	}


	$allowed_html = wp_kses_allowed_html( 'user_description' );
	$user_description = wp_kses( $user_description, $allowed_html );

	if ( ! update_user_meta( $user_id, 'description', $user_description ) ) {
		wp_idea_stream_add_message( array(
			'type'    => 'error',
			'content' => __( 'Something went wrong while trying to update your description.', 'wp-idea-stream' ),
		) );

		wp_safe_redirect( $redirect );
		exit();
	} else {
		wp_idea_stream_add_message( array(
			'type'    => 'success',
			'content' => __( 'Description updated.', 'wp-idea-stream' ),
		) );

		/**
		 * @param int    $user_id          the user ID
		 * @param string $user_description the user description ("about field")
		 */
		do_action( 'wp_idea_stream_users_profile_description_updated', $user_id, $user_description );

		wp_safe_redirect( $redirect );
		exit();
	}
}

/**
 * Hooks to deleted_user to perform additional actions
 *
 * When a user is deleted, we need to be sure the ideas he shared are also
 * deleted to avoid troubles in edit screens as the post author field will found
 * no user. I also remove rates.
 *
 * The main problem here (excepting error notices) is ownership of the idea. To avoid any
 * troubles, deleting when user leaves seems to be the safest. If you have a different point
 * of view, you can remove_action( 'deleted_user', 'wp_idea_stream_users_delete_user_data', 10, 1 )
 * and use a different way of managing this. I advise you to make sure ideas are reattributed to
 * an existing user ID. About rates, there's no problem if a non existing user ID is in the rating
 * list of an idea.
 *
 * @package WP Idea Stream
 * @subpackage users/functions
 *
 * @since 2.0.0
 *
 * @uses add_filter() to temporarly include all post status
 * @uses wp_idea_stream_ideas_get_ideas() to get all user's ideas and rates
 * @uses remove_filter() to remove the filter
 * @uses apply_filters() Calls 'wp_idea_stream_users_delete_user_force_delete' to override
 * @uses do_action() Calls 'wp_idea_stream_users_before_trash_user_data' to perform actions before idea is trashed
 *                   Calls 'wp_idea_stream_users_before_delete_user_data' to perform actions before idea is deleted
 * @uses wp_delete_post() to peramanently delete (forces flag on) these ideas
 * @uses wp_idea_stream_is_rating_disabled() to check if rating functionality is available
 * @uses wp_idea_stream_delete_rate() to delete user's rates
 * @uses do_action() Calls 'wp_idea_stream_delete_user_rates' to perform actions once user is deleted
 */
function wp_idea_stream_users_delete_user_data( $user_id = 0 ) {
	if ( empty( $user_id ) ) {
		return;
	}

	// Make sure we don't miss any ideas
	add_filter( 'wp_idea_stream_ideas_get_status', 'wp_idea_stream_ideas_get_all_status', 10, 1 );

	// Get user's ideas, in case of multisite
	$user_ideas = wp_idea_stream_ideas_get_ideas( array(
		'per_page' => -1,
		'author'   => $user_id,
	) );

	// remove asap
	remove_filter( 'wp_idea_stream_ideas_get_status', 'wp_idea_stream_ideas_get_all_status', 10, 1 );

	/**
	 * We're forcing ideas to be deleted definitively
	 * Using this filter you can set it to only be trashed
	 *
	 * Internally use in case user has been spammed (BuddyPress functionnality)
	 * @see  buddypress/functions
	 *
	 * @param bool   $force_delete true to permanently delete, false to trash
	 */
	$force_delete = apply_filters( 'wp_idea_stream_users_delete_user_force_delete', true );

	// If any delete them
	if ( ! empty( $user_ideas['ideas'] ) ) {
		foreach ( $user_ideas['ideas'] as $user_idea ) {
			/**
			 * WordPress is using a check on native post types
			 * so we can't just pass $force_delete to wp_delete_post().
			 */
			if ( empty( $force_delete ) ) {
				/**
				 * @param  int ID of the idea being trashed
				 * @param  int $user_id the user id
				 */
				do_action( 'wp_idea_stream_users_before_trash_user_data', $user_idea->ID, $user_id );

				wp_trash_post( $user_idea->ID );
			} else {
				/**
				 * @param  int ID of the idea being trashed
				 * @param  int $user_id the user id
				 */
				do_action( 'wp_idea_stream_users_before_delete_user_data', $user_idea->ID, $user_id );

				wp_delete_post( $user_idea->ID, true );
			}
		}
	}

	// Ratings are on, try to delete them.
	if ( ! wp_idea_stream_is_rating_disabled() ) {
		// Make sure we don't miss any ideas
		add_filter( 'wp_idea_stream_ideas_get_status', 'wp_idea_stream_ideas_get_all_status', 10, 1 );

		// Get user's rates
		$rated_ideas = wp_idea_stream_ideas_get_ideas( array(
			'per_page' => -1,
			'meta_query' => array( array(
				'key'     => '_ideastream_rates',
				'value'   => ';i:' . $user_id,
				'compare' => 'LIKE'
			) ),
		) );

		// remove asap
		remove_filter( 'wp_idea_stream_ideas_get_status', 'wp_idea_stream_ideas_get_all_status', 10, 1 );

		// If any delete them.
		if ( ! empty( $rated_ideas['ideas'] ) ) {

			foreach ( $rated_ideas['ideas'] as $idea ) {
				wp_idea_stream_delete_rate( $idea->ID, $user_id );
			}

			/**
			 * Internally used in BuddyPress part of the plugin to delete notifications
			 * generated by the deleted user.
			 * @see buddypress/notifications part
			 *
			 * @param int $user_id the user ID
			 */
			do_action( 'wp_idea_stream_delete_user_rates', $user_id );
		}
	}

	/**
	 * @param int $user_id the user ID
	 */
	do_action( 'wp_idea_stream_users_deleted_user_data', $user_id );
}

/**
 * Get idea authors sorted by count
 *
 * count_many_users_posts() does not match the need
 *
 * @package WP Idea Stream
 * @subpackage users/functions
 *
 * @since 2.0.0
 *
 * @global  $wpdb
 * @param   int  $max the number of users to limit the query
 * @uses    get_posts_by_author_sql() to get the sql part for the author request
 * @uses    wp_idea_stream_get_post_type() to get the ideas post type identifier
 * @return  array list of users ordered by ideas count.
 */
function wp_idea_stream_users_ideas_count_by_user( $max = 10 ) {
	global $wpdb;

	$sql = array();
	$sql['select']  = "SELECT p.post_author, COUNT(p.ID) as count_ideas, u.user_nicename";
	$sql['from']    = "FROM {$wpdb->posts} p LEFT JOIN {$wpdb->users} u ON ( p.post_author = u.ID )";
	$sql['where']   = get_posts_by_author_sql( wp_idea_stream_get_post_type(), true, null, true );
	$sql['groupby'] = 'GROUP BY p.post_author';
	$sql['order']   = 'ORDER BY count_ideas DESC';
	$sql['limit']   = $wpdb->prepare( 'LIMIT 0, %d', $max );

	$query = apply_filters( 'wp_idea_stream_users_ideas_count_by_user_query', join( ' ', $sql ), $sql, $max );

	return $wpdb->get_results( $query );
}

function wp_idea_stream_users_signup_user() {
	// Bail if not a post request
	if ( 'POST' != strtoupper( $_SERVER['REQUEST_METHOD'] ) ) {
		return;
	}

	// Bail if not a post idea request
	if ( empty( $_POST['wp_idea_stream_signup'] ) || ! is_array( $_POST['wp_idea_stream_signup'] ) ) {
		return;
	}

	// Check nonce
	check_admin_referer( 'wp_idea_stream_signup' );

	$redirect = wp_idea_stream_get_redirect_url();

	$user_login = false;

	// Force the login to exist and to be at least 4 characters long
	if ( ! empty( $_POST['wp_idea_stream_signup']['user_login'] ) &&  4 < mb_strlen( $_POST['wp_idea_stream_signup']['user_login'] ) ) {
		$user_login = $_POST['wp_idea_stream_signup']['user_login'];
	}

	$user_email = false;
	if ( ! empty( $_POST['wp_idea_stream_signup']['user_email'] ) ) {
		$user_email = $_POST['wp_idea_stream_signup']['user_email'];
	}

	// Do we need to edit the user once created ?
	$edit_user = array_diff_key(
		$_POST['wp_idea_stream_signup'],
		array(
			'signup'     => 'signup',
			'user_login' => 'user_login',
			'user_email' => 'user_email',
		)
	);

	/**
	 * Before registering the user, check for required field
	 */
	$required_errors = new WP_Error();

	foreach ( $edit_user as $key => $value ) {

		if ( ! apply_filters( 'wp_idea_stream_users_is_signup_field_required', false, $key ) ) {
			continue;
		}

		if ( empty( $value ) ) {
			$required_errors->add( 'empty_required_field', __( 'Please fill all required fields.', 'wp-idea-stream' ) );
		}
	}

	// Stop the process and ask to fill all fields.
	if ( $required_errors->get_error_code() ) {
		//Add feedback to the user
		wp_idea_stream_add_message( array(
			'type'    => 'error',
			'content' => $required_errors->get_error_message(),
		) );
		return;
	}

	// Register the user
	$user = register_new_user( $user_login, $user_email );

	if ( is_wp_error( $user ) ) {
		//Add feedback to the user
		wp_idea_stream_add_message( array(
			'type'    => 'error',
			'content' => join( ' ', array_map( 'strip_tags', $user->get_error_messages() ) ),
		) );
		return;

	// User is created, now we need to eventually edit him
	} else {

		if ( ! empty( $edit_user ) )  {

			$userdata = new stdClass();
			$userdata = (object) $edit_user;
			$userdata->ID = $user;

			// Edit the user
			if ( wp_update_user( $userdata ) ) {
				/**
				 * Any extra field not using contact methods or WordPress built in user fields can hook here
				 *
				 * @param int $user the user id
				 * @param array $edit_user the submitted user fields
				 */
				do_action( 'wp_idea_stream_users_signup_user_created', $user, $edit_user );
			}
		}

		// Finally invite the user to check his email.
		wp_idea_stream_add_message( array(
			'type'    => 'success',
			'content' => __( 'Registration complete. Please check your e-mail.', 'wp-idea-stream' ),
		) );

		wp_safe_redirect( $redirect );
		exit();
	}
}

function wp_idea_stream_user_get_fields( $type = 'signup' ) {
	$fields = wp_get_user_contact_methods();

	if ( 'signup' == $type ) {
		$fields = array_merge(
			apply_filters( 'wp_idea_stream_user_get_signup_fields', array(
				'user_login' => __( 'Username',   'wp-idea-stream' ),
				'user_email' => __( 'E-mail',     'wp-idea-stream' ),
			) ),
			$fields
		);
	}

	return apply_filters( 'wp_idea_stream_user_get_fields', $fields, $type );
}

/**
 * Redirect the loggedin user to its profile as already a member
 * Or redirect WP (non multisite) register form to IdeaStream signup form
 *
 * @package WP Idea Stream
 * @subpackage users/functions
 *
 * @since 2.1.0
 *
 * @param  string $context the template context
 */
function wp_idea_stream_user_signup_redirect( $context = '' ) {
	// Bail if signup is not allowed
	if ( ! wp_idea_stream_is_signup_allowed() ) {
		return;
	}

	if ( is_user_logged_in() && 'signup' == $context ) {
		wp_safe_redirect( wp_idea_stream_users_get_logged_in_profile_url() );
		return;
	} else if ( ! empty( $_SERVER['SCRIPT_NAME'] ) && false !== strpos( $_SERVER['SCRIPT_NAME'], 'wp-login.php' ) && ! empty( $_REQUEST['action'] ) &&  'register' == $_REQUEST['action'] ) {
		wp_safe_redirect( wp_idea_stream_users_get_signup_url() );
		return;
	} else {
		return;
	}
}
