<?php
/**
 * WP Idea Stream Actions.
 *
 * List of main Action hooks used in the plugin
 *
 * @package WP Idea Stream
 * @subpackage core/actions
 *
 * @since 2.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'plugins_loaded',           'wp_idea_stream_loaded',                 11 );
add_action( 'init',                     'wp_idea_stream_init',                   9  );
add_action( 'parse_query',              'wp_idea_stream_parse_query',            2  );
add_action( 'widgets_init',             'wp_idea_stream_widgets_init',           10 );
add_action( 'wp_enqueue_scripts',       'wp_idea_stream_enqueue_scripts',        10 );
add_action( 'wp_head',                  'wp_idea_stream_head',                   10 );
add_action( 'wp_footer',                'wp_idea_stream_footer',                 10 );
add_action( 'set_current_user',         'wp_idea_stream_setup_current_user',     10 );
add_action( 'after_setup_theme',        'wp_idea_stream_after_setup_theme',      10 );
add_action( 'template_redirect',        'wp_idea_stream_template_redirect',      8  );

// Actions to register post_type, metas, taxonomies & rewrite stuff
add_action( 'wp_idea_stream_init', 'wp_idea_stream_register_post_types',            2 );
add_action( 'wp_idea_stream_init', 'wp_idea_stream_register_taxonomies',            4 );
add_action( 'wp_idea_stream_init', 'wp_idea_stream_add_rewrite_tags',               6 );
add_action( 'wp_idea_stream_init', 'wp_idea_stream_add_rewrite_rules',              8 );
add_action( 'wp_idea_stream_init', 'wp_idea_stream_add_permastructs',               9 );
add_action( 'wp_idea_stream_init', array( 'WP_Idea_Stream_Idea_Metas', 'start' ), 100 );

// Actions hooking loaded (rewrites/comments disjoin)
add_action( 'wp_idea_stream_loaded', array( 'WP_Idea_Stream_Rewrites', 'start' ), 1 );
add_action( 'wp_idea_stream_loaded', 'wp_idea_stream_cache_global_group' );
add_action( 'wp_idea_stream_loaded', array( 'WP_Idea_Stream_Comments', 'start' ) );

// Comments actions
add_action( 'wp_set_comment_status', 'wp_idea_stream_comments_clean_count_cache', 10, 2 );
add_action( 'delete_comment',        'wp_idea_stream_comments_clean_count_cache', 10, 1 );

// Actions hooking enqueue_scripts (tags, rates UI)
add_action( 'wp_idea_stream_enqueue_scripts', 'wp_idea_stream_ideas_enqueue_scripts', 10 );
add_action( 'wp_idea_stream_enqueue_scripts', 'wp_idea_stream_users_enqueue_scripts', 11 );

// Template actions
add_action( 'wp_idea_stream_idea_header',             'wp_idea_stream_users_the_user_idea_rating', 1 );
add_action( 'wp_idea_stream_before_archive_main_nav', 'wp_idea_stream_ideas_taxonomy_description'    );

// Actions to handle user actions (eg: submit new idea)
add_action( 'wp_idea_stream_template_redirect', 'wp_idea_stream_actions',                         4 );
add_action( 'wp_idea_stream_actions',           'wp_idea_stream_set_user_feedback',               5 );
add_action( 'wp_idea_stream_actions',           'wp_idea_stream_ideas_post_idea'                    );
add_action( 'wp_idea_stream_actions',           'wp_idea_stream_ideas_update_idea'                  );
add_action( 'wp_idea_stream_actions',           'wp_idea_stream_users_profile_description_update'   );
add_action( 'wp_ajax_wp_idea_stream_rate',      'wp_idea_stream_ajax_rate'                          );

// Admin
add_action( 'admin_init', 'wp_idea_stream_admin_init', 10 );
add_action( 'admin_head', 'wp_idea_stream_admin_head', 10 );

add_action( 'wp_idea_stream_admin_init', 'wp_idea_stream_activation_redirect',      1 );
add_action( 'wp_idea_stream_admin_init', 'wp_idea_stream_admin_register_settings', 11 );
add_action( 'wp_idea_stream_admin_init', 'wp_idea_stream_maybe_upgrade',          999 );

// Widgets
add_action( 'wp_idea_stream_widgets_init', array( 'WP_Idea_Stream_Navig',                  'register_widget' ), 10 );
add_action( 'wp_idea_stream_widgets_init', array( 'WP_Idea_Stream_Ideas_Categories',       'register_widget' ), 11 );
add_action( 'wp_idea_stream_widgets_init', array( 'WP_Idea_Stream_Ideas_Popular',          'register_widget' ), 12 );
add_action( 'wp_idea_stream_widgets_init', array( 'WP_Idea_Stream_Users_Top_Contributors', 'register_widget' ), 14 );
add_action( 'wp_idea_stream_widgets_init', array( 'WP_Idea_Stream_Comments_Recent',        'register_widget' ), 15 );

// User deleted
add_action( 'deleted_user', 'wp_idea_stream_users_delete_user_data', 10, 1 );

// Signups
add_action( 'wp_idea_stream_set_core_template', 'wp_idea_stream_user_signup_redirect', 10, 1 );
add_action( 'login_form_register',              'wp_idea_stream_user_signup_redirect', 10    );

// Admin Menu Bar
add_action( 'admin_bar_menu', 'wp_idea_stream_adminbar_menu', 999 );

/**
 * Fire the 'wp_idea_stream_init' action.
 *
 * @package WP Idea Stream
 * @subpackage core/actions
 *
 * @since 2.0.0
 */
function wp_idea_stream_init() {
	do_action( 'wp_idea_stream_init' );
}

/**
 * Fire the 'wp_idea_stream_loaded' action.
 *
 * @package WP Idea Stream
 * @subpackage core/actions
 *
 * @since 2.0.0
 */
function wp_idea_stream_loaded() {
	do_action( 'wp_idea_stream_loaded' );
}

/**
 * Fire the 'wp_idea_stream_widgets_init' action.
 *
 * Used to register IdeaStream widgets.
 *
 * @package WP Idea Stream
 * @subpackage core/actions
 *
 * @since 2.0.0
 */
function wp_idea_stream_widgets_init() {
	do_action( 'wp_idea_stream_widgets_init' );
}

/**
 * Fire the 'wp_idea_stream_enqueue_scripts' action.
 *
 * Used to register and enqueue custom scripts
 *
 * @package WP Idea Stream
 * @subpackage core/actions
 *
 * @since 2.0.0
 */
function wp_idea_stream_enqueue_scripts() {
	do_action( 'wp_idea_stream_enqueue_scripts' );
}

/**
 * Fire the 'wp_idea_stream_head' action.
 *
 * @package WP Idea Stream
 * @subpackage core/actions
 *
 * @since 2.0.0
 */
function wp_idea_stream_head() {
	do_action( 'wp_idea_stream_head' );
}

/**
 * Fire the 'wp_idea_stream_footer' action.
 *
 * @package WP Idea Stream
 * @subpackage core/actions
 *
 * @since 2.0.0
 */
function wp_idea_stream_footer() {
	do_action( 'wp_idea_stream_footer' );
}

/**
 * Fire the 'wp_idea_stream_setup_current_user' action.
 *
 * Used to set the IdeaStream logged in user
 *
 * @package WP Idea Stream
 * @subpackage core/actions
 *
 * @since 2.0.0
 */
function wp_idea_stream_setup_current_user() {
	do_action( 'wp_idea_stream_setup_current_user' );
}

/**
 * Fire the 'wp_idea_stream_template_redirect' action.
 *
 * @package WP Idea Stream
 * @subpackage core/actions
 *
 * @since 2.0.0
 */
function wp_idea_stream_template_redirect() {
	do_action( 'wp_idea_stream_template_redirect' );
}

/**
 * Fire the 'wp_idea_stream_register_post_types' action.
 *
 * @package WP Idea Stream
 * @subpackage core/actions
 *
 * @since 2.0.0
 */
function wp_idea_stream_register_post_types() {
	do_action( 'wp_idea_stream_register_post_types' );
}

/**
 * Fire the 'wp_idea_stream_register_taxonomies' action.
 *
 * @package WP Idea Stream
 * @subpackage core/actions
 *
 * @since 2.0.0
 */
function wp_idea_stream_register_taxonomies() {
	do_action( 'wp_idea_stream_register_taxonomies' );
}

/**
 * Fire the 'wp_idea_stream_add_rewrite_tags' action.
 *
 * Used in core/rewrites to add user's profile, search & action
 * custom tags
 *
 * @package WP Idea Stream
 * @subpackage core/actions
 *
 * @since 2.0.0
 */
function wp_idea_stream_add_rewrite_tags() {
	do_action( 'wp_idea_stream_add_rewrite_tags' );
}

/**
 * Fire the 'wp_idea_stream_add_rewrite_rules' action.
 *
 * Used in core/rewrites to add user's profile custom rule
 *
 * @package WP Idea Stream
 * @subpackage core/actions
 *
 * @since 2.0.0
 */
function wp_idea_stream_add_rewrite_rules() {
	do_action( 'wp_idea_stream_add_rewrite_rules' );
}

/**
 * Fire the 'wp_idea_stream_add_permastructs' action.
 *
 * Used in core/rewrites to add custom permalink structures
 * such as the user's profile one
 *
 * @package WP Idea Stream
 * @subpackage core/actions
 *
 * @since 2.0.0
 */
function wp_idea_stream_add_permastructs() {
	do_action( 'wp_idea_stream_add_permastructs' );
}

/**
 * Fire the 'wp_idea_stream_after_setup_theme' action.
 *
 * @package WP Idea Stream
 * @subpackage core/actions
 *
 * @since 2.0.0
 */
function wp_idea_stream_after_setup_theme() {
	do_action( 'wp_idea_stream_after_setup_theme' );
}

/**
 * Fire the 'wp_idea_stream_actions' action.
 *
 * Used to handle user actions (profile update, submit ideas)
 *
 * @package WP Idea Stream
 * @subpackage core/actions
 *
 * @since 2.0.0
 */
function wp_idea_stream_actions() {
	do_action( 'wp_idea_stream_actions' );
}

/** Admin *********************************************************************/

/**
 * Fire the 'wp_idea_stream_admin_init' action.
 *
 * @package WP Idea Stream
 * @subpackage core/actions
 *
 * @since 2.0.0
 */
function wp_idea_stream_admin_init() {
	do_action( 'wp_idea_stream_admin_init' );
}

/**
 * Fire the 'wp_idea_stream_admin_head' action.
 *
 * @package WP Idea Stream
 * @subpackage core/actions
 *
 * @since 2.0.0
 */
function wp_idea_stream_admin_head() {
	do_action( 'wp_idea_stream_admin_head' );
}

/**
 * Fire the 'wp_idea_stream_admin_register_settings' action.
 *
 * @package WP Idea Stream
 * @subpackage core/actions
 *
 * @since 2.0.0
 */
function wp_idea_stream_admin_register_settings() {
	do_action( 'wp_idea_stream_admin_register_settings' );
}
