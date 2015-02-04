<?php
/**
 * WP Idea Stream BuddyPress integration loader.
 *
 * BuddyPress main Loader class
 *
 * @package WP Idea Stream
 * @subpackage buddypress/loader
 *
 * @since 2.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WP_Idea_Stream_BuddyPress' ) ) :
/**
 * Main WP_Idea_Stream_BuddyPress Component Class
 *
 * Inspired by BuddyPress skeleton component (branch 1.7)
 * @see https://github.com/boonebgorges/buddypress-skeleton-component/tree/1.7
 *
 * This class includes all BuddyPress needed files, builds the user navigations
 * (logged in and displayed user ones) and adds some stuff to extend Plugin's
 * core functions.
 *
 * @package WP Idea Stream
 * @subpackage buddypress/loader
 *
 * @since 2.0.0
 */
class WP_Idea_Stream_BuddyPress extends BP_Component {

	/**
	 * Constructor method
	 *
	 * @package WP Idea Stream
	 * @subpackage buddypress/loader
	 *
	 * @since 2.0.0
	 *
	 * @uses wp_idea_stream_archive_title() to get the main archive page title
	 * @uses wp_idea_stream_get_includes_dir() to get the plugin's includes dir
	 */
	public function __construct() {

		parent::start(
			'ideastream',
			wp_idea_stream_archive_title(),
			trailingslashit( wp_idea_stream_get_includes_dir() . 'buddypress' )
		);

	 	$this->includes();
	 	$this->extend_ideastream();
	}

	/**
	 * Extend WP Idea Stream
	 *
	 * @package WP Idea Stream
	 * @subpackage buddypress/loader
	 *
	 * @since 2.0.0
	 *
	 * @uses  remove_filter() to avoid IdeaStream to override the comments array
	 * @uses  add_action() to override built in user domains by BuddyPressified ones
	 */
	private function extend_ideastream() {
		/**
		 * Using this, BuddyPress themes or plugins will be
		 * able to check if the plugin is active using
		 * bp_is_active( 'ideastream' );
		 */
		buddypress()->active_components[$this->id] = '1';

		/** Remove some core filters **************************************************/

		// Let BuddyPress take the lead on user's profile link in ideas post type comments
		remove_filter( 'comments_array', 'wp_idea_stream_comments_append_profile_url',  11, 2 );

		// Remove the signup override of ideastream
		remove_action( 'login_form_register', 'wp_idea_stream_user_signup_redirect' );

		// Filter the user domains once ideastream nav is set
		add_action( 'bp_' . $this->id .'_setup_nav', array( $this, 'filter_user_domains' ) );
	}

	/**
	 * Include the needed files
	 *
	 * @package WP Idea Stream
	 * @subpackage buddypress/loader
	 *
	 * @since 2.0.0
	 *
	 * @uses bp_is_active() to check if needed components are active
	 */
	public function includes( $includes = array() ) {

		// Files to include
		$includes = array(
			'functions.php',
			'screens.php',
		);

		if ( bp_is_active( 'activity' ) && bp_is_active( 'blogs' ) ) {
			$includes[] = 'activity.php';
		}

		if ( bp_is_active( 'notifications' ) ) {
			$includes[] = 'notifications.php';
		}

		if ( bp_is_active( 'groups' ) ) {
			$includes[] = 'groups.php';
		}

		if ( is_admin() ) {
			$includes[] = 'settings.php';
		}

		parent::includes( $includes );
	}

	/**
	 * Set up plugin's globals
	 *
	 * @package WP Idea Stream
	 * @subpackage buddypress/loader
	 *
	 * @since 2.0.0
	 *
	 * @uses buddypress() to get BuddyPress instance data
	 * @uses wp_idea_stream_root_slug() to get plugin's root slug
	 */
	public function setup_globals( $args = array() ) {
		$bp = buddypress();

		// Set up the $globals array to be passed along to parent::setup_globals()
		$globals = array(
			'slug'                  => wp_idea_stream_root_slug(),
			'has_directory'         => false,
			'notification_callback' => 'wp_idea_stream_buddypress_format_notifications'
		);

		// Let BP_Component::setup_globals() do its work.
		parent::setup_globals( $globals );
	}

	/**
	 * Set up IdeaStream navigation.
	 *
	 * @package WP Idea Stream
	 * @subpackage buddypress/loader
	 *
	 * @since 2.0.0
	 *
	 * @uses buddypress() to get BuddyPress instance data
	 * @uses bp_loggedin_user_id() to get logged in user id
	 * @uses bp_get_loggedin_user_username() to get logged in user nicename
	 * @uses bp_loggedin_user_domain() to get logged in user domain
	 * @uses bp_is_user() to check if a user's profile is displayed
	 * @uses bp_displayed_user_id() to get the displayed user id
	 * @uses bp_get_displayed_user_username() to get displayed user nicename
	 * @uses bp_displayed_user_domain() to get displayed user profile link
	 * @uses wp_idea_stream_users_get_profile_nav_items() to get IdeaStream user nav items
	 * @uses sanitize_title(), sanitize_key() to sanitize datas
	 */
	public function setup_nav( $main_nav = array(), $sub_nav = array() ) {
		$bp =  buddypress();

		// Default is current user.
		$user_id       = bp_loggedin_user_id();
		$user_nicename = bp_get_loggedin_user_username();
		$user_domain   = bp_loggedin_user_domain();

		// If viewing a user, set the user to displayed one
		if ( bp_is_user() ) {
			$user_id       = bp_displayed_user_id();
			$user_nicename = bp_get_displayed_user_username();
			$user_domain   = bp_displayed_user_domain();
		}

		// Build the user nav if we have an id
		if ( ! empty( $user_id ) ) {
			// Build user's ideas BuddyPress profile link
			$profile_link = trailingslashit( $user_domain . $this->slug );

			// Get Core User's profile nav
			$user_core_subnav = wp_idea_stream_users_get_profile_nav_items( $user_id, $user_nicename );

			// Build BuddyPress user's Main nav
			$main_nav = array(
				'name' 		          => $this->name,
				'slug' 		          => $this->slug,
				'position' 	          => 90,
				'screen_function'     => array( 'WP_Idea_Stream_Screens', 'user_ideas' ),
				'default_subnav_slug' => sanitize_title( $user_core_subnav['profile']['slug'], 'ideas', 'save' )
			);

			// Init nav position & subnav slugs
			$position = 10;
			$this->idea_nav = array();

			// Build BuddyPress user's Sub nav
			foreach ( $user_core_subnav as $key => $nav ) {

				$fallback_slug = sanitize_key( $key );

				if ( 'profile' == $fallback_slug ) {
					$fallback_slug = 'ideas';
				}

				// Register subnav slugs using the fallback title
				// as keys to easily build urls later on.
				$this->idea_nav[ $fallback_slug ] = array(
					'name' => $nav['title'],
					'slug' => sanitize_title( $nav['slug'], $fallback_slug, 'save' ),
				);

				$sub_nav[] = array(
					'name'            => $this->idea_nav[ $fallback_slug ]['name'],
					'slug'            => $this->idea_nav[ $fallback_slug ]['slug'],
					'parent_url'      => $profile_link,
					'parent_slug'     => $this->slug,
					'screen_function' => array( 'WP_Idea_Stream_Screens', 'user_' . $fallback_slug ),
					'position'        => $position,
				);

				// increment next nav position
				$position += 10;
			}
		}

		parent::setup_nav( $main_nav, $sub_nav );
	}

	/**
	 * Builds the user's navigation in WP Admin Bar
	 *
	 * @package WP Idea Stream
	 * @subpackage buddypress/loader
	 *
	 * @since 2.0.0
	 *
	 * @uses is_user_logged_in() to check if the user is logged in
	 * @uses bp_loggedin_user_domain() to get current user's profile link
	 */
	public function setup_admin_bar( $wp_admin_nav = array() ) {

		// Menus for logged in user
		if ( ! is_user_logged_in() ) {
			return;
		}

		// Build logged in user's ideas BuddyPress profile link
		$idea_link = trailingslashit( bp_loggedin_user_domain() . $this->slug );

		// Build logged in user main nav
		$wp_admin_nav[] = array(
			'parent' => 'my-account-buddypress',
			'id'     => 'my-account-' . $this->slug,
			'title'  => $this->name,
			'href'   => $idea_link
		);

		foreach ( $this->idea_nav as $key => $nav ) {
			$wp_admin_nav[] = array(
				'parent' => 'my-account-' . $this->slug,
				'id'     => 'my-account-' . $this->slug . '-' . $key,
				'title'  => $nav['name'],
				'href'   => trailingslashit( $idea_link . $nav['slug'] )
			);
		}

		parent::setup_admin_bar( $wp_admin_nav );
	}

	/**
	 * Use BuddyPressified user profiles
	 *
	 * @package WP Idea Stream
	 * @subpackage buddypress/loader
	 *
	 * @since 2.0.0
	 *
	 * @uses   bp_is_user() to check if a user's profile is displayed
	 * @uses   bp_is_group() to check if a group is displayed
	 * @uses   remove_filter() to stop IdeaStream from filtering nav menus
	 * @uses   add_filter() to early override IdeaStream user's profile parts
	 */
	public function filter_user_domains() {
		// When on a BuddyPress profile / ideastream screen, the current nav item is not IdeaStream
		if ( bp_is_user() || bp_is_group() ) {
			remove_filter( 'wp_nav_menu_objects', 'wp_idea_stream_wp_nav',       10, 2 );
			remove_filter( 'wp_title_parts',      'wp_idea_stream_title',        10, 1 );
			remove_filter( 'wp_title',            'wp_idea_stream_title_adjust', 20, 3 );
		}

		/* BuddyPress profile urls override */
		add_filter( 'wp_idea_stream_users_pre_get_user_profile_url',  'wp_idea_stream_buddypress_get_user_profile_url',  10, 2 );
		add_filter( 'wp_idea_stream_users_pre_get_user_comments_url', 'wp_idea_stream_buddypress_get_user_comments_url', 10, 2 );
		add_filter( 'wp_idea_stream_users_pre_get_user_rates_url',    'wp_idea_stream_buddypress_get_user_rates_url',    10, 2 );
	}
}

endif;

/**
 * Finally Loads the component into BuddyPress instance
 *
 * @package WP Idea Stream
 * @subpackage buddypress/loader
 *
 * @since 2.0.0
 *
 * @uses  wp_idea_stream() to get plugin's main instance
 * @uses  buddypress() to get BuddyPress main instance
 * @uses  get_option() to check for BuddyPress integration setting
 * @uses  is_admin() to check for the Administration context
 * @uses  wp_idea_stream_get_includes_dir() to get plugin's include dir
 * @uses  wp_idea_stream_set_idea_var() to globalize a value for a later use
 * @uses  add_query_arg(), admin_url() to build an url
 * @uses  WP_Idea_Stream_BuddyPress to launch the IdeaStream BuddyPress component
 */
function wp_idea_stream_buddypress() {
	// Init a dummy BuddyPress version
	$bp_version = 0;

	// Set the required version
	$required_buddypress_version = '2.2-rc';

	// Get main plugin instance
	$wp_idea_stream = wp_idea_stream();

	// Try to get buddypress()
	if ( function_exists( 'buddypress' ) ) {
		$bp_version = buddypress()->version;
	}

	// Should we load ? Yes, try by default!
	if ( ! get_option( '_ideastream_buddypress_integration', 1 ) ) {
		// Include at least BuddyPress filters & settings in order to extend
		// WP Idea Stream Settings and let the Admin deactivate/activate BuddyPress
		// integration.
		if ( is_admin() ) {
			require( wp_idea_stream_get_includes_dir() . 'buddypress/settings.php' );
		}

		// Prevent BuddyPress Integration load
		return;
	}

	// If BuddyPress required version does not match, provide a feedback
	// Does not fire if BuddyPress integration is disabled.
	if ( ! version_compare( $bp_version, $required_buddypress_version, '>=' ) ) {
		if ( is_admin() ) {
			wp_idea_stream_set_idea_var( 'feedback', array( 'admin_notices' => array(
				sprintf( esc_html__( 'To benefit of WP Idea Stream in BuddyPress, version %s of BuddyPress is required. Please upgrade or deactivate %s.', 'wp-idea-stream' ),
					$required_buddypress_version,
					'<a href="' . add_query_arg( array( 'page' => 'ideastream' ), admin_url( 'options-general.php' ) ) . '#buddypress">"BuddyPress integration"</a>'
				)
			) ) );

			require( wp_idea_stream_get_includes_dir() . 'buddypress/settings.php' );
		}

		// Prevent BuddyPress Integration load.
		return;
	}

	buddypress()->ideastream = new WP_Idea_Stream_BuddyPress();
}

// Load Main Component Class
add_action( 'bp_loaded', 'wp_idea_stream_buddypress' );
