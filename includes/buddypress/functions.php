<?php
/**
 * WP Idea Stream BuddyPress integration : functions.
 *
 * BuddyPress / functions
 *
 * @package WP Idea Stream
 * @subpackage buddypress/functions
 *
 * @since  2.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Map IdeaStream is self profile to BuddyPress one
 *
 * @package WP Idea Stream
 * @subpackage buddypress/functions
 *
 * @since  2.0.0
 *
 * @param  bool $is_self  the IdeaStream self profile
 * @return bool           true if on user's on his self profile, false otherwise
 */
function wp_idea_stream_buddypress_is_user_profile( $is_self = false ) {
	return bp_is_my_profile();
}
add_filter( 'wp_idea_stream_is_current_user_profile', 'wp_idea_stream_buddypress_is_user_profile', 10, 1 );

/**
 * Map IdeaStream User's profile to BuddyPress one
 *
 * @package WP Idea Stream
 * @subpackage buddypress/functions
 *
 * @since  2.0.0
 *
 * @param  int     $user_id       the user ID
 * @param  string  $user_nicename the user nicename
 * @uses   bp_core_get_user_domain() to build BuddyPress user's base url
 * @uses   wp_idea_stream_root_slug() to get IdeaStream root slug
 * @return string                 the BuddyPressified user's profile url
 */
function wp_idea_stream_buddypress_get_user_profile_url( $user_id = 0, $user_nicename = '' ) {
	if ( empty( $user_id ) ) {
		return false;
	}

	// Extra check to avoid overriding in case of an embed request
	if ( is_a( wp_idea_stream_get_idea_var( 'embed_user_data' ), 'WP_User' ) ) {
		return false;
	}

	$root_url = bp_core_get_user_domain( $user_id, $user_nicename );

	return trailingslashit( $root_url . wp_idea_stream_root_slug() );
}

/**
 * Map IdeaStream User's profile, comments part, to BuddyPress one
 *
 * @package WP Idea Stream
 * @subpackage buddypress/functions
 *
 * @since  2.0.0
 *
 * @param  int     $user_id       the user ID
 * @param  string  $user_nicename the user nicename
 * @uses   bp_core_get_user_domain() to build BuddyPress user's base url
 * @uses   wp_idea_stream_root_slug() to get IdeaStream root slug
 * @return string                 the BuddyPressified user's profile comments url
 */
function wp_idea_stream_buddypress_get_user_comments_url( $user_id = 0, $user_nicename = '' ) {
	if ( empty( $user_id ) ) {
		return false;
	}

	$root_url = bp_core_get_user_domain( $user_id, $user_nicename );

	if ( ! wp_idea_stream_get_idea_var( 'is_user_embed' ) ) {
		$comments_slug = buddypress()->ideastream->idea_nav['comments']['slug'];
	} else {
		$comments_slug = wp_idea_stream_user_comments_slug();
	}

	return trailingslashit( $root_url . wp_idea_stream_root_slug() . '/' . $comments_slug );
}

/**
 * Map IdeaStream User's profile, rates part, to BuddyPress one
 *
 * @package WP Idea Stream
 * @subpackage buddypress/functions
 *
 * @since  2.0.0
 *
 * @param  int     $user_id       the user ID
 * @param  string  $user_nicename the user nicename
 * @uses   bp_core_get_user_domain() to build BuddyPress user's base url
 * @uses   wp_idea_stream_root_slug() to get IdeaStream root slug
 * @return string                 the BuddyPressified user's profile rates url
 */
function wp_idea_stream_buddypress_get_user_rates_url( $user_id = 0, $user_nicename = '' ) {
	if ( empty( $user_id ) ) {
		return false;
	}

	$root_url = bp_core_get_user_domain( $user_id, $user_nicename );

	if ( ! wp_idea_stream_get_idea_var( 'is_user_embed' ) ) {
		$rates_slug = buddypress()->ideastream->idea_nav['rates']['slug'];
	} else {
		$rates_slug = wp_idea_stream_user_rates_slug();
	}

	return trailingslashit( $root_url . wp_idea_stream_root_slug() . '/' . $rates_slug );
}

/**
 * Map IdeaStream displayed username to BuddyPress one
 *
 * @package WP Idea Stream
 * @subpackage buddypress/functions
 *
 * @since  2.0.0
 *
 * @param  string $username the username
 * @uses   bp_get_displayed_user_username() to get displayed user nicename
 * @return string           the username
 */
function wp_idea_stream_buddypress_displayed_user_username( $username = '' ) {
	if ( empty( $username ) ) {
		$username = bp_get_displayed_user_username();
	}

	return $username;
}
add_filter( 'wp_idea_stream_users_get_displayed_user_username', 'wp_idea_stream_buddypress_displayed_user_username', 10, 1 );

/**
 * Map IdeaStream displayed display name to BuddyPress one
 *
 * @package WP Idea Stream
 * @subpackage buddypress/functions
 *
 * @since  2.0.0
 *
 * @param  string $display_name the display name
 * @uses   bp_get_displayed_user_fullname() to get displayed user display name
 * @return string           the username
 */
function wp_idea_stream_buddypress_displayed_user_displayname( $display_name = '' ) {
	if ( empty( $display_name ) ) {
		$display_name = bp_get_displayed_user_fullname();
	}

	return $display_name;
}
add_filter( 'wp_idea_stream_users_get_displayed_user_displayname', 'wp_idea_stream_buddypress_displayed_user_displayname', 10, 1 );

/**
 * Redirect IdeaStream profile to BuddyPress one
 *
 * @package WP Idea Stream
 * @subpackage buddypress/functions
 *
 * @since  2.0.0
 *
 * @param  string $context the context of the template
 * @uses   wp_idea_stream_users_displayed_user_id() to get displayed user ID
 * @uses   wp_idea_stream_users_get_displayed_user_username() to get displayed user nicename
 * @uses   bp_core_redirect() to redirect the user to BuddyPress profile
 * @uses   wp_idea_stream_buddypress_get_user_profile_url() to get the BuddyPressified user's profile
 */
function wp_idea_stream_buddypress_profile_redirect( $context = '' ) {
	if ( empty( $context ) || 'user-profile' != $context || wp_idea_stream_get_idea_var( 'is_user_embed' ) ) {
		return;
	}

	// Be sure it's a user's profile
	$user_id = wp_idea_stream_users_displayed_user_id();

	// Bail if not on WP Idea Stream built in profile
	if ( empty( $user_id ) ) {
		return;
	}

	// Get user nicename
	$user_nicename = wp_idea_stream_users_get_displayed_user_username();

	// Safely redirect the user to his BuddyPress profile.
	bp_core_redirect( wp_idea_stream_buddypress_get_user_profile_url( $user_id, $user_nicename ) );
}
add_action( 'wp_idea_stream_set_core_template', 'wp_idea_stream_buddypress_profile_redirect', 10, 1 );

/**
 * Let BuddyPress handle signups by early overriding the ideastream signup url
 *
 * @package WP Idea Stream
 * @subpackage buddypress/functions
 *
 * @since  2.1.0
 *
 * @uses bp_get_signup_page() to get BuddyPress sign up url
 */
function wp_idea_stream_buddypress_get_signup_url( $url = '' ) {
	return bp_get_signup_page();
}
add_filter( 'wp_idea_stream_users_pre_get_signup_url', 'wp_idea_stream_buddypress_get_signup_url', 10, 1 );

/**
 * Let BuddyPress handle signups by redirecting to BuddyPress signup form
 *
 * @package WP Idea Stream
 * @subpackage buddypress/functions
 *
 * @since  2.1.0
 *
 * @uses bp_get_signup_page() to get BuddyPress sign up url
 */
function wp_idea_stream_buddypress_signup_redirect() {
	wp_safe_redirect( bp_get_signup_page() );
}
add_action( 'wp_idea_stream_user_signup_override', 'wp_idea_stream_buddypress_signup_redirect' );

/**
 * Sets a new IdeaStream territory to load needed scripts & css
 *
 * @package WP Idea Stream
 * @subpackage buddypress/functions
 *
 * @since  2.0.0
 *
 * @uses bp_is_user() to check a profile is displayed
 * @uses bp_is_group() to check a group is displayed
 * @uses wp_idea_stream_set_idea_var() to set the ideastream global
 */
function wp_idea_stream_buddypress_set_is_ideastream() {
	if ( ! bp_is_user() && ! bp_is_group() ) {
		return;
	}

	wp_idea_stream_set_idea_var( 'is_ideastream', true );
}

/**
 * Sets the new idea form global to load needed scripts
 *
 * @package WP Idea Stream
 * @subpackage buddypress/functions
 *
 * @since  2.0.0
 *
 * @uses bp_is_group() to check a group is displayed
 * @uses wp_idea_stream_set_idea_var() to set the new form global
 */
function wp_idea_stream_buddypress_set_is_new() {
	if ( ! bp_is_group() ) {
		return;
	}

	wp_idea_stream_set_idea_var( 'is_new', true );
}

/**
 * Sets the edit idea form global to load needed scripts
 *
 * @package WP Idea Stream
 * @subpackage buddypress/functions
 *
 * @since  2.0.0
 *
 * @uses bp_is_group() to check a group is displayed
 * @uses wp_idea_stream_set_idea_var() to set the new form global
 */
function wp_idea_stream_buddypress_set_is_edit() {
	if ( ! bp_is_group() ) {
		return;
	}

	wp_idea_stream_set_idea_var( 'is_edit', true );
}

/**
 * Adds IdeaStream component id and slug into groups forbidden names
 *
 * @package WP Idea Stream
 * @subpackage buddypress/functions
 *
 * @since  2.0.0
 *
 * @param  array  $names the groups forbidden names
 * @uses   wp_idea_stream_root_slug() to get the plugin's slug
 * @return array        the same names + IdeaStream forbidden ones.
 */
function wp_idea_stream_buddypress_group_forbidden_names( $names = array() ) {
	$forbidden = array( wp_idea_stream_root_slug() );

	// Just in case!
	if ( 'ideastream' != wp_idea_stream_root_slug() ) {
		$forbidden[] = 'ideastream';
	}

	return array_merge( $names, $forbidden );
}
add_filter( 'groups_forbidden_names', 'wp_idea_stream_buddypress_group_forbidden_names', 10, 1 );

/**
 * Checks if an idea can be commented
 *
 * @package WP Idea Stream
 * @subpackage buddypress/functions
 *
 * @since  2.0.0
 *
 * @param  bool $open    true if comments opened, false otherwise
 * @param  int  $idea_id the idea ID
 * @uses   wp_idea_stream_is_ideastream() to check if it's the plugin's territory
 * @uses   is_buddypress() to check if it's BuddyPress territory
 * @uses   wp_idea_stream_is_comments_allowed() to get IdeaStream global setting
 * @uses   get_post_field() to get the idea comments opened setting
 * @uses   apply_filters() call 'wp_idea_stream_buddypress_comments_open' to override the comments opened setting
 * @return bool          the comments opened status for the idea
 */
function wp_idea_stream_buddypress_comments_open( $open = true, $idea_id = 0 ) {
	$retval = true;

	if ( ! wp_idea_stream_is_ideastream() || ! is_buddypress() ) {
		return $open;
	}

	// Comments can be disabled globally
	if ( ! wp_idea_stream_is_comments_allowed() ) {
		$retval = false;
	}

	// We need to recheck as BuddyPress is forcing comment status to be closed
	// on its directory pages.
	if ( 'open' != get_post_field( 'comment_status', $idea_id ) ) {
		$retval = false;
	}

	/**
	 * Used internally to check the group's comments opened setting
	 *
	 * @param  bool $retval  the comments opened setting
	 * @param  int  $idea_id the ID of the idea
	 */
	return apply_filters( 'wp_idea_stream_buddypress_comments_open', $retval, $idea_id );
}
add_filter( 'wp_idea_stream_comments_open', 'wp_idea_stream_buddypress_comments_open', 10, 2 );

/**
 * Checks if the user/super admin is on the delete account screen
 *
 * @package WP Idea Stream
 * @subpackage buddypress/functions
 *
 * @since  2.0.0
 *
 * @uses   bp_is_settings_component() to check if on the settings component
 * @uses   bp_is_current_action() to check for a specific BuddyPress action
 * @uses   bp_is_my_profile() to check if the user is on his self profile
 * @uses   is_super_admin() to check if the user has delete capabilities on all WordPress configs
 * @return bool true if on the delete account screen, false otherwise
 */
function wp_idea_stream_buddypress_is_delete_account() {
	$retval = false;

	if ( bp_is_settings_component() && bp_is_current_action( 'delete-account' ) && ( bp_is_my_profile() || is_super_admin() ) ) {
		$retval = true;
	}

	return (bool) apply_filters( 'wp_idea_stream_buddypress_is_delete_account', $retval );
}

/**
 * Process a spammed user
 *
 * @package WP Idea Stream
 * @subpackage buddypress/functions
 *
 * @since  2.0.0
 *
 * @param  int $user_id the user ID
 * @uses   add_filter() to avoid ideas to be permanently deleted
 * @uses   wp_idea_stream_users_delete_user_data() to remove user's IdeaStream Data.
 * @uses   get_comments() to get user's comment
 * @uses   wp_idea_stream_get_post_type() to get the ideas post type identifier
 * @uses   wp_spam_comment() to spam user's comments
 */
function wp_idea_stream_buddypress_spam_user( $user_id = 0 ) {
	if ( empty( $user_id ) ) {
		return;
	}

	// Let's trash ideas instead of completely removed them.
	add_filter( 'wp_idea_stream_users_delete_user_force_delete', '__return_false' );

	// Remove IdeaStream Data
	wp_idea_stream_users_delete_user_data( $user_id );

	// Spam approved comments about ideas
	$comments = get_comments( array(
		'fields'    => 'ids',
		'user_id'   => $user_id,
		'post_type' => wp_idea_stream_get_post_type(),
		'status'    => 'approve'
	) );

	if ( ! empty( $comments ) ) {
		foreach ( $comments as $comment ) {
			wp_spam_comment( $comment );
		}
	}
}
add_action( 'bp_make_spam_user', 'wp_idea_stream_buddypress_spam_user', 11, 1 );

/**
 * As BuddyPress brings a "spam user" feature to regular configs,
 * let's use it!
 *
 * @since  2.3.0
 *
 * @param  bool    $is_spammer whether the user is a spammer or not
 * @param  WP_User $user       the WordPress User Object
 * @return bool    Whether the user is a spammer or not
 */
function wp_idea_stream_buddypress_is_spammy( $is_spammer, $user = null ) {
	if ( empty( $user->ID ) ) {
		return $is_spammer;
	}

	return bp_is_user_spammer( $user->ID );
}
add_action( 'wp_idea_stream_users_is_spammy', 'wp_idea_stream_buddypress_is_spammy', 10, 2 );

/**
 * Add the user's cover image to his embed profile
 *
 * @since  2.3.0
 */
function wp_idea_stream_buddypress_embed_inline_style() {
	// Get displayed user id.
	$user_id = wp_idea_stream_users_displayed_user_id();

	// If not set, we're not on a user's profile.
	if ( empty( $user_id ) || bp_disable_cover_image_uploads() ) {
		return;
	}

	$cover_image = bp_attachments_get_attachment( 'url', array(
		'object_dir' => 'members',
		'item_id'    => $user_id,
	) );

	if ( ! $cover_image ) {
		return;
	}

	wp_add_inline_style( 'wp-idea-stream-embed-style', '
		#buddypress-cover-image {
			display: block;
			background-image: url(' . $cover_image . ');
			background-position: center top;
			background-repeat: no-repeat;
			background-size: cover;
			height: 100px;
			position: absolute;
			top: 0;
			left: 0;
			width: 100%;
			z-index: 1;
		}

		#wp-idea-stream .profile-header {
			z-index: 1;
			position: relative;
		}

		#wp-idea-stream .wp-embed-excerpt {
			margin-top: 1em;
		}

		#wp-idea-stream .profile-header .wp-embed-heading a {
			color: #FFF;
			text-rendering: optimizelegibility;
			text-shadow: 0px 0px 3px rgba( 0, 0, 0, 0.8 );
			height: 50px;
			line-height: 50px;
		}

		#wp-idea-stream .profile-header .user-avatar img.avatar {
			border: solid 2px #FFF;
			background: rgba( 255, 255, 255, 0.8 );
		}
	' );
}
add_action( 'wp_idea_stream_enqueue_embed_scripts', 'wp_idea_stream_buddypress_embed_inline_style', 20 );

/**
 * Enqueue specific scripts and styles (if needed) to let any
 * user get the displayed user's embed link
 *
 * @since  2.3.0
 */
function wp_idea_stream_buddypress_enqueue_profile_sharing_dialog_css() {
	if ( ! wp_idea_stream_is_embed_profile() ) {
		return;
	}

	wp_enqueue_script( 'wp-idea-stream-script', wp_idea_stream_get_js_script( 'script' ), array( 'jquery' ), wp_idea_stream_get_version(), true );
	wp_localize_script( 'wp-idea-stream-script', 'wp_idea_stream_vars', apply_filters( 'wp_idea_stream_users_current_profile_script', array(
		'is_profile' => 1
	) ) );

	$min = '.min';

	if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
		$min = '';
	}

	wp_enqueue_style( 'wp-idea-stream-sharing-profile', includes_url( "css/wp-embed-template{$min}.css" ), array(), wp_idea_stream_get_version() );
}
add_action( 'wp_idea_stream_buddypress_load_member_template', 'wp_idea_stream_buddypress_enqueue_profile_sharing_dialog_css' );

/**
 * Add a new member-header button to open the embed profile dialog box
 *
 * @since  2.3.0
 */
function wp_idea_stream_buddypress_add_profile_sharing_dialog_button() {
	if ( ! ( bp_is_user() && bp_is_current_component( 'ideastream' ) && wp_idea_stream_is_embed_profile() ) ) {
		return;
	}

	// Temporarly map IdeaStream Displayed user with BuddyPress One
	add_filter( 'wp_idea_stream_users_displayed_user_id', 'bp_displayed_user_id' );

	wp_idea_stream_users_sharing_button();

	// Stop mapping IdeaStream Displayed user with BuddyPress One
	remove_filter( 'wp_idea_stream_users_displayed_user_id', 'bp_displayed_user_id' );
}
add_action( 'bp_member_header_actions', 'wp_idea_stream_buddypress_add_profile_sharing_dialog_button', 100 );
