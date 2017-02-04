<?php
/**
 * WP Idea Stream Options.
 *
 * List of options used to customize the plugins
 * @see  admin/settings
 *
 * Mainly inspired by bbPress way of dealing with options
 * @see bbpress/includes/core/options.php
 *
 * @package WP Idea Stream
 * @subpackage core/options
 *
 * @since 2.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Get the default plugin's options and their values.
 *
 * @package WP Idea Stream
 * @subpackage core/options
 *
 * @since 2.0.0
 *
 * @uses   wp_idea_stream_get_version()
 * @uses   wp_idea_stream_is_pretty_links() to check if pretty links are on
 * @uses   apply_filters() call 'wp_idea_stream_get_default_options' to override options values
 * @return array Filtered option names and values
 */
function wp_idea_stream_get_default_options() {
	// Default options

	$default_options = array(

		/** DB Version ********************************************************/
		'_ideastream_version'            => wp_idea_stream_get_version(),

		/** Core Settings **********************************************************/
		'_ideastream_archive_title'      => 'IdeaStream',
		'_ideastream_submit_status'      => 'publish',
		'_ideastream_editor_image'       => 1,
		'_ideastream_editor_link'        => 1,
		'_ideastream_moderation_message' => '',
		'_ideastream_login_message'      => '',
		'_ideastream_hint_list'          => '',
		'_ideastream_sticky_ideas'       => 1,
		'_ideastream_disjoin_comments'   => 1,
		'_ideastream_allow_comments'     => 1,
		'_ideastream_embed_profile'      => 0,
		'_ideastream_featured_images'    => 1,
		'_ideastream_as_front_page'      => 0,
	);

	// Pretty links customization
	if ( wp_idea_stream_is_pretty_links() ) {
		$default_options = array_merge( $default_options, array(
			'_ideastream_root_slug'          => _x( 'ideastream', 'default root slug', 'wp-idea-stream' ),
			'_ideastream_idea_slug'          => _x( 'idea', 'default idea slug', 'wp-idea-stream' ),
			'_ideastream_category_slug'      => _x( 'category', 'default category slug', 'wp-idea-stream' ),
			'_ideastream_tag_slug'           => _x( 'tag', 'default tag slug', 'wp-idea-stream' ),
			'_ideastream_user_slug'          => _x( 'user', 'default user slug', 'wp-idea-stream' ),
			'_ideastream_user_comments_slug' => _x( 'comments', 'default comments slug', 'wp-idea-stream' ),
			'_ideastream_user_rates_slug'    => _x( 'ratings', 'default ratings slug', 'wp-idea-stream' ),
			'_ideastream_signup_slug'        => _x( 'sign-up', 'default sign-up action slug', 'wp-idea-stream' ),
			'_ideastream_action_slug'        => _x( 'action', 'default action slug', 'wp-idea-stream' ),
			'_ideastream_addnew_slug'        => _x( 'add', 'default add idea action slug', 'wp-idea-stream' ),
			'_ideastream_edit_slug'          => _x( 'edit', 'default edit idea action slug', 'wp-idea-stream' ),
			'_ideastream_cpage_slug'         => _x( 'cpage', 'default comments pagination slug', 'wp-idea-stream' ),
		) );
	}

	// Multisite options
	if ( is_multisite() ) {
		$default_options = array_merge( $default_options, array(
			'_ideastream_allow_signups'          => 0,
			'_ideastream_user_new_idea_set_role' => 0,
		) );
	}

	/**
	 * Used internally to merge options of the previous verions
	 * of the plugin with new ones during upgrade routine.
	 *
	 * @see  core/upgrade wp_idea_stream_merge_legacy_options()
	 *
	 * @param  array $default_options list of options
	 */
	return apply_filters( 'wp_idea_stream_get_default_options', $default_options );
}

/**
 * Add default plugin's options
 *
 * Used during routine upgrade in order to customize
 * the slugs regarding previous versions
 * (eg: "is" instead of "ideastream")
 *
 * @package WP Idea Stream
 * @subpackage core/options
 *
 * @since 2.0.0
 *
 * @uses wp_idea_stream_get_default_options() To get default options
 * @uses add_option() Adds default options
 * @uses do_action() Calls 'wp_idea_stream_add_options'
 */
function wp_idea_stream_add_options() {

	// Add default options
	foreach ( wp_idea_stream_get_default_options() as $key => $value ) {
		add_option( $key, $value );
	}

	// Allow plugins to append their own options.
	do_action( 'wp_idea_stream_add_options' );
}

/**
 * Main archive page title
 *
 * @package WP Idea Stream
 * @subpackage core/options
 *
 * @since 2.0.0
 *
 * @param  string $default default value
 * @uses   apply_filters() call 'wp_idea_stream_archive_title' to override default or customized value
 * @uses   get_option() to get customized value
 * @return string       default value or customized one
 */
function wp_idea_stream_archive_title( $default = 'IdeaStream' ) {
	return apply_filters( 'wp_idea_stream_archive_title', get_option( '_ideastream_archive_title', $default ) );
}

/**
 * Default publishing status (publish/pending)
 *
 * If BuddyPress Groupes are enabled, this option is overriden
 * and only publish status is available
 *
 * @package WP Idea Stream
 * @subpackage core/options
 *
 * @since 2.0.0
 *
 * @param  string $default default value
 * @uses   get_option() to get customized value
 * @uses   wp_idea_stream_user_can() to check user's capability
 * @uses   apply_filters() call 'wp_idea_stream_default_idea_status' to override default or customized value
 * @return string       default value or customized one
 */
function wp_idea_stream_default_idea_status( $default = 'publish' ) {
	$default_status = get_option( '_ideastream_submit_status', $default );

	// Make sure admins will have a publish status whatever the settings choice
	if ( wp_idea_stream_is_ideastream() && wp_idea_stream_user_can( 'wp_idea_stream_ideas_admin' ) ) {
		$default_status = 'publish';
	}

	/**
	 * @param  string $default_status
	 */
	return apply_filters( 'wp_idea_stream_default_idea_status', $default_status );
}

/**
 * Should the editor include the add image url button ?
 *
 * @package WP Idea Stream
 * @subpackage core/options
 *
 * @since 2.0.0
 *
 * @param  int $default default value
 * @uses   apply_filters() call 'wp_idea_stream_idea_editor_image' to override default or customized value
 * @uses   get_option() to get customized value
 * @return bool         True if enabled, false otherwise
 */
function wp_idea_stream_idea_editor_image( $default = 1 ) {
	return (bool) apply_filters( 'wp_idea_stream_idea_editor_image', (bool) get_option( '_ideastream_editor_image', $default ) );
}

/**
 * Should the editor include the add/remove link buttons ?
 *
 * @package WP Idea Stream
 * @subpackage core/options
 *
 * @since 2.0.0
 *
 * @param  int $default default value
 * @uses   apply_filters() call 'wp_idea_stream_idea_editor_link' to override default or customized value
 * @uses   get_option() to get customized value
 * @return bool         True if enabled, false otherwise
 */
function wp_idea_stream_idea_editor_link( $default = 1 ) {
	return (bool) apply_filters( 'wp_idea_stream_idea_editor_link', (bool) get_option( '_ideastream_editor_link', $default ) );
}

/**
 * Use a custom moderation message ?
 *
 * This option depends on the default publish status one. If pending
 * is set, it will be possible to customize a moderation message.
 *
 * @package WP Idea Stream
 * @subpackage core/options
 *
 * @since 2.0.0
 *
 * @param  string $default default value
 * @uses   apply_filters() call 'wp_idea_stream_moderation_message' to override default or customized value
 * @uses   get_option() to get customized value
 * @return string       the moderation message
 */
function wp_idea_stream_moderation_message( $default = '' ) {
	return apply_filters( 'wp_idea_stream_moderation_message', get_option( '_ideastream_moderation_message', $default ) );
}

/**
 * Use a custom login message ?
 *
 * @package WP Idea Stream
 * @subpackage core/options
 *
 * @since 2.0.0
 *
 * @param  string $default default value
 * @uses   apply_filters() call 'wp_idea_stream_login_message' to override default or customized value
 * @uses   get_option() to get customized value
 * @return string       the moderation message
 */
function wp_idea_stream_login_message( $default = '' ) {
	return apply_filters( 'wp_idea_stream_login_message', get_option( '_ideastream_login_message', $default ) );
}

/**
 * Use a custom captions for rating stars ?
 *
 * @package WP Idea Stream
 * @subpackage core/options
 *
 * @since 2.0.0
 *
 * @param  array $default default value
 * @uses   apply_filters() call 'wp_idea_stream_hint_list' to override default or customized value
 * @uses   get_option() to get customized value
 * @return array        the list of rating stars captions.
 */
function wp_idea_stream_hint_list( $default = array() ) {
	return apply_filters( 'wp_idea_stream_hint_list', get_option( '_ideastream_hint_list', $default ) );
}

/**
 * Do ideas can be stick to the front of first archive page ?
 *
 * @package WP Idea Stream
 * @subpackage core/options
 *
 * @since 2.0.0
 *
 * @param  bool $default default value
 * @uses   apply_filters() call 'wp_idea_stream_is_sticky_enabled' to override default or customized value
 * @uses   get_option() to get customized value
 * @return bool         True if enabled, false otherwise
 */
function wp_idea_stream_is_sticky_enabled( $default = 1 ) {
	return (bool) apply_filters( 'wp_idea_stream_is_sticky_enabled', (bool) get_option( '_ideastream_sticky_ideas', $default ) );
}

/**
 * Should we disjoin comments about ideas from regular comments ?
 *
 * @package WP Idea Stream
 * @subpackage core/options
 *
 * @since 2.0.0
 *
 * @param  bool $default default value
 * @uses   apply_filters() call 'wp_idea_stream_is_comments_disjoined' to override default or customized value
 * @uses   get_option() to get customized value
 * @return bool         True if enabled, false otherwise
 */
function wp_idea_stream_is_comments_disjoined( $default = 1 ) {
	return (bool) apply_filters( 'wp_idea_stream_is_comments_disjoined', (bool) get_option( '_ideastream_disjoin_comments', $default ) );
}

/**
 * Are comments about ideas globally allowed
 *
 * Thanks to this option, plugin will be able to neutralize comments about
 * ideas without having to rely on WordPress discussion settings. If this
 * option is enabled, it's still possible from the edit Administration screen
 * of the idea to neutralize for each specific idea the comments.
 *
 * @package WP Idea Stream
 * @subpackage core/options
 *
 * @since 2.0.0
 *
 * @param  bool $default default value
 * @uses   apply_filters() call 'wp_idea_stream_is_comments_allowed' to override default or customized value
 * @uses   get_option() to get customized value
 * @return bool         True if enabled, false otherwise
 */
function wp_idea_stream_is_comments_allowed( $default = 1 ) {
	return (bool) apply_filters( 'wp_idea_stream_is_comments_allowed', (bool) get_option( '_ideastream_allow_comments', $default ) );
}

/**
 * Can profile be embed ?
 *
 * @since 2.3.0
 *
 * @param  bool $default default value
 * @return int           The id of the Page Utility if enabled, 0 otherwise
 */
function wp_idea_stream_is_embed_profile( $default = 0 ) {
	return (int) apply_filters( 'wp_idea_stream_is_embed_profile', get_option( '_ideastream_embed_profile', $default ) );
}

/**
 * Featured images for ideas ?
 *
 * @since 2.3.0
 *
 * @param  int $default default value
 * @return bool         True if enabled, false otherwise
 */
function wp_idea_stream_featured_images_allowed( $default = 1 ) {
	return (bool) apply_filters( 'wp_idea_stream_featured_images_allowed', (bool) get_option( '_ideastream_featured_images', $default ) );
}

/**
 * Should ideas be listed on the static front page ?
 *
 * @since 2.4.0
 *
 * @param  bool $default default value
 * @return bool          True if enabled, false otherwise
 */
function wp_idea_stream_is_front_page( $default = false ) {
	return (bool) apply_filters( 'wp_idea_stream_is_embed_profile', get_option( '_ideastream_as_front_page', $default ) );
}

/**
 * Customize the root slug of the plugin
 *
 * @package WP Idea Stream
 * @subpackage core/options
 *
 * @since 2.0.0
 *
 * @param  string $default default value
 * @uses   apply_filters() call 'wp_idea_stream_root_slug' to override default or customized value
 * @uses   get_option() to get customized value
 * @return string       the root slug
 */
function wp_idea_stream_root_slug( $default = '' ) {
	if ( empty( $default ) ) {
		$default = _x( 'ideastream', 'default root slug', 'wp-idea-stream' );
	}

	return apply_filters( 'wp_idea_stream_root_slug', get_option( '_ideastream_root_slug', $default ) );
}

/**
 * Build the idea slug (root + idea ones)
 *
 * @package WP Idea Stream
 * @subpackage core/options
 *
 * @since 2.0.0
 *
 * @uses   apply_filters() call 'wp_idea_stream_idea_slug' to override value
 * @uses   wp_idea_stream_root_slug() to get root slug
 * @uses   wp_idea_stream_idea_get_slug() to get idea slug
 * @return string       the idea slug (prefixed by the root one)
 */
function wp_idea_stream_idea_slug() {
	return apply_filters( 'wp_idea_stream_idea_slug', wp_idea_stream_root_slug() . '/' . wp_idea_stream_idea_get_slug() );
}

	/**
	 * Customize the idea (post type) slug of the plugin
	 *
	 * @package WP Idea Stream
	 * @subpackage core/options
	 *
	 * @since 2.0.0
	 *
	 * @param  string $default default value
	 * @uses   apply_filters() call 'wp_idea_stream_idea_get_slug' to override default or customized value
	 * @uses   get_option() to get customized value
	 * @return string       the idea slug
	 */
	function wp_idea_stream_idea_get_slug( $default = '' ) {
		if ( empty( $default ) ) {
			$default = _x( 'idea', 'default idea slug', 'wp-idea-stream' );
		}

		return apply_filters( 'wp_idea_stream_idea_get_slug', get_option( '_ideastream_idea_slug', $default ) );
	}

/**
 * Build the category slug (root + category ones)
 *
 * @package WP Idea Stream
 * @subpackage core/options
 *
 * @since 2.0.0
 *
 * @uses   apply_filters() call 'wp_idea_stream_category_slug' to override value
 * @uses   wp_idea_stream_root_slug() to get root slug
 * @uses   wp_idea_stream_category_get_slug() to get category slug
 * @return string       the category slug (prefixed by the root one)
 */
function wp_idea_stream_category_slug() {
	return apply_filters( 'wp_idea_stream_category_slug', wp_idea_stream_root_slug() . '/' . wp_idea_stream_category_get_slug() );
}

	/**
	 * Customize the category (hierarchical taxonomy) slug of the plugin
	 *
	 * @package WP Idea Stream
	 * @subpackage core/options
	 *
	 * @since 2.0.0
	 *
	 * @param  string $default default value
	 * @uses   apply_filters() call 'wp_idea_stream_category_get_slug' to override default or customized value
	 * @uses   get_option() to get customized value
	 * @return string       the category slug
	 */
	function wp_idea_stream_category_get_slug( $default = '' ) {
		if ( empty( $default ) ) {
			$default = _x( 'category', 'default category slug', 'wp-idea-stream' );
		}

		return apply_filters( 'wp_idea_stream_category_get_slug', get_option( '_ideastream_category_slug', $default ) );
	}

/**
 * Build the tag slug (root + tag ones)
 *
 * @package WP Idea Stream
 * @subpackage core/options
 *
 * @since 2.0.0
 *
 * @uses   apply_filters() call 'wp_idea_stream_tag_slug' to override value
 * @uses   wp_idea_stream_root_slug() to get root slug
 * @uses   wp_idea_stream_tag_get_slug() to get tag slug
 * @return string       the tag slug (prefixed by the root one)
 */
function wp_idea_stream_tag_slug() {
	return apply_filters( 'wp_idea_stream_tag_slug', wp_idea_stream_root_slug() . '/' . wp_idea_stream_tag_get_slug() );
}

	/**
	 * Customize the tag (non hierarchical taxonomy) slug of the plugin
	 *
	 * @package WP Idea Stream
	 * @subpackage core/options
	 *
	 * @since 2.0.0
	 *
	 * @param  string $default default value
	 * @uses   apply_filters() call 'wp_idea_stream_tag_get_slug' to override default or customized value
	 * @uses   get_option() to get customized value
	 * @return string       the tag slug
	 */
	function wp_idea_stream_tag_get_slug( $default = '' ) {
		if ( empty( $default ) ) {
			$default = _x( 'tag', 'default tag slug', 'wp-idea-stream' );
		}

		return apply_filters( 'wp_idea_stream_tag_get_slug', get_option( '_ideastream_tag_slug', $default ) );
	}

/**
 * Build the user's profile slug (root + user ones)
 *
 * @package WP Idea Stream
 * @subpackage core/options
 *
 * @since 2.0.0
 *
 * @uses   apply_filters() call 'wp_idea_stream_user_slug' to override value
 * @uses   wp_idea_stream_root_slug() to get root slug
 * @uses   wp_idea_stream_user_get_slug() to get user slug
 * @return string       the user slug (prefixed by the root one)
 */
function wp_idea_stream_user_slug() {
	return apply_filters( 'wp_idea_stream_user_slug', wp_idea_stream_root_slug() . '/' . wp_idea_stream_user_get_slug() );
}

	/**
	 * Customize the user's profile slug of the plugin
	 *
	 * @package WP Idea Stream
	 * @subpackage core/options
	 *
	 * @since 2.0.0
	 *
	 * @param  string $default default value
	 * @uses   apply_filters() call 'wp_idea_stream_user_get_slug' to override default or customized value
	 * @uses   get_option() to get customized value
	 * @return string       the user slug
	 */
	function wp_idea_stream_user_get_slug( $default = '' ) {
		if ( empty( $default ) ) {
			$default = _x( 'user', 'default user slug', 'wp-idea-stream' );
		}

		return apply_filters( 'wp_idea_stream_user_get_slug', get_option( '_ideastream_user_slug', $default ) );
	}

/**
 * Customize the user's profile rates slug of the plugin
 *
 * @package WP Idea Stream
 * @subpackage core/options
 *
 * @since 2.0.0
 *
 * @param  string $default default value
 * @uses   apply_filters() call 'wp_idea_stream_user_rates_slug' to override default or customized value
 * @uses   get_option() to get customized value
 * @return string       the user's profile rates slug
 */
function wp_idea_stream_user_rates_slug( $default = '' ) {
	if ( empty( $default ) ) {
		$default = _x( 'ratings', 'default ratings slug', 'wp-idea-stream' );
	}

	return apply_filters( 'wp_idea_stream_user_rates_slug', get_option( '_ideastream_user_rates_slug', $default ) );
}

/**
 * Customize the user's profile comments slug of the plugin
 *
 * @package WP Idea Stream
 * @subpackage core/options
 *
 * @since 2.0.0
 *
 * @param  string $default default value
 * @uses   apply_filters() call 'wp_idea_stream_user_comments_slug' to override default or customized value
 * @uses   get_option() to get customized value
 * @return string       the user's profile comments slug
 */
function wp_idea_stream_user_comments_slug( $default = '' ) {
	if ( empty( $default ) ) {
		$default = _x( 'comments', 'default comments slug', 'wp-idea-stream' );
	}

	return apply_filters( 'wp_idea_stream_user_comments_slug', get_option( '_ideastream_user_comments_slug', $default ) );
}

/**
 * Build the action slug (root + action ones)
 *
 * @package WP Idea Stream
 * @subpackage core/options
 *
 * @since 2.0.0
 *
 * @uses   apply_filters() call 'wp_idea_stream_action_slug' to override value
 * @uses   wp_idea_stream_root_slug() to get root slug
 * @uses   wp_idea_stream_action_get_slug() to get action slug
 * @return string       the action slug (prefixed by the root one)
 */
function wp_idea_stream_action_slug() {
	return apply_filters( 'wp_idea_stream_action_slug', wp_idea_stream_root_slug() . '/' . wp_idea_stream_action_get_slug() );
}

	/**
	 * Customize the action slug of the plugin
	 *
	 * @package WP Idea Stream
	 * @subpackage core/options
	 *
	 * @since 2.0.0
	 *
	 * @param  string $default default value
	 * @uses   apply_filters() call 'wp_idea_stream_action_get_slug' to override default or customized value
	 * @uses   get_option() to get customized value
	 * @return string       the action slug
	 */
	function wp_idea_stream_action_get_slug( $default = '' ) {
		if ( empty( $default ) ) {
			$default = _x( 'action', 'default action slug', 'wp-idea-stream' );
		}

		return apply_filters( 'wp_idea_stream_action_get_slug', get_option( '_ideastream_action_slug', $default ) );
	}

/**
 * Customize the add (action) slug of the plugin
 *
 * @package WP Idea Stream
 * @subpackage core/options
 *
 * @since 2.0.0
 *
 * @param  string $default default value
 * @uses   apply_filters() call 'wp_idea_stream_addnew_slug' to override default or customized value
 * @uses   get_option() to get customized value
 * @return string       the add (action) slug
 */
function wp_idea_stream_addnew_slug( $default = '' ) {
	if ( empty( $default ) ) {
		$default = _x( 'add', 'default add idea action slug', 'wp-idea-stream' );
	}

	return apply_filters( 'wp_idea_stream_addnew_slug', get_option( '_ideastream_addnew_slug', $default ) );
}

/**
 * Customize the edit (action) slug of the plugin
 *
 * @package WP Idea Stream
 * @subpackage core/options
 *
 * @since 2.0.0
 *
 * @param  string $default default value
 * @uses   apply_filters() call 'wp_idea_stream_edit_slug' to override default or customized value
 * @uses   get_option() to get customized value
 * @return string       the add (action) slug
 */
function wp_idea_stream_edit_slug( $default = '' ) {
	if ( empty( $default ) ) {
		$default = _x( 'edit', 'default edit idea action slug', 'wp-idea-stream' );
	}

	return apply_filters( 'wp_idea_stream_edit_slug', get_option( '_ideastream_edit_slug', $default ) );
}

/**
 * Build the signup slug (root + signup one)
 *
 * @package WP Idea Stream
 * @subpackage core/options
 *
 * @since 2.1.0
 *
 * @uses   apply_filters() call 'wp_idea_stream_signup_slug' to override value
 * @uses   wp_idea_stream_root_slug() to get root slug
 * @uses   wp_idea_stream_signup_get_slug() to get user slug
 * @return string       the user slug (prefixed by the root one)
 */
function wp_idea_stream_signup_slug( $default = '' ) {
	if ( empty( $default ) ) {
		$default = _x( 'sign-up', 'default sign-up action slug', 'wp-idea-stream' );
	}

	return apply_filters( 'wp_idea_stream_signup_slug', get_option( '_ideastream_signup_slug', $default ) );
}

/**
 * Customize the comment pagination slug of the plugin in user's profile
 *
 * @package WP Idea Stream
 * @subpackage core/options
 *
 * @since 2.0.0
 *
 * @param  string $default default value
 * @uses   apply_filters() call 'wp_idea_stream_cpage_slug' to override default or customized value
 * @uses   get_option() to get customized value
 * @return string       the comment pagination slug
 */
function wp_idea_stream_cpage_slug( $default = '' ) {
	if ( empty( $default ) ) {
		$default = _x( 'cpage', 'default comments pagination slug', 'wp-idea-stream' );
	}

	return apply_filters( 'wp_idea_stream_cpage_slug', get_option( '_ideastream_cpage_slug', $default ) );
}

/**
 * Should IdeaStream manage signups for the blog ?
 *
 * @package WP Idea Stream
 * @subpackage core/options
 *
 * @since 2.2.0
 *
 * @param  int $default default value
 * @uses   apply_filters() call 'wp_idea_stream_allow_signups' to override default or customized value
 * @uses   get_option() to get customized value
 * @return bool         True if enabled, false otherwise
 */
function wp_idea_stream_allow_signups( $default = 0 ) {
	return (bool) apply_filters( 'wp_idea_stream_allow_signups', get_option( '_ideastream_allow_signups', $default ) );
}

/**
 * Should we make sure the user posting an idea on the site has the default role ?
 *
 * @package WP Idea Stream
 * @subpackage core/options
 *
 * @since 2.2.0
 *
 * @param  int $default default value
 * @uses   apply_filters() call 'wp_idea_stream_user_new_idea_set_role' to override default or customized value
 * @uses   get_option() to get customized value
 * @return bool         True if enabled, false otherwise
 */
function wp_idea_stream_user_new_idea_set_role( $default = 0 ) {
	return (bool) apply_filters( 'wp_idea_stream_user_new_idea_set_role', get_option( '_ideastream_user_new_idea_set_role', $default ) );
}
