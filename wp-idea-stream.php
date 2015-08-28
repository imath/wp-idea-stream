<?php
/*
Plugin Name: WP Idea Stream
Plugin URI: http://imathi.eu/tag/ideastream/
Description: Share ideas, great ones will rise to the top!
Version: 2.2.0
Requires at least: 4.3
Tested up to: 4.3
License: GNU/GPL 2
Author: imath
Author URI: http://imathi.eu/
Text Domain: wp-idea-stream
Domain Path: /languages/
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WP_Idea_Stream' ) ) :
/**
 * Main plugin's class
 *
 * Sets the needed globalized vars, includes the required
 * files and registers post type stuff.
 *
 * @package WP Idea Stream
 *
 * @since 2.0.0
 */
final class WP_Idea_Stream {

	/**
	 * Plugin's main instance
	 * @var object
	 */
	protected static $instance;

	/**
	 * Initialize the plugin
	 *
	 * @package WP Idea Stream
	 *
	 * @since 2.0.0
	 */
	private function __construct() {
		$this->setup_globals();
		$this->includes();
		$this->setup_hooks();
	}

	/**
	 * Return an instance of this class.
	 *
	 * @package WP Idea Stream
	 *
	 * @since 2.0.0
	 *
	 * @return object A single instance of this class.
	 */
	public static function start() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Setups plugin's globals
	 *
	 * @package WP Idea Stream
	 *
	 * @since 2.0.0
	 *
	 * @uses plugin_basename() to get the plugin basename
	 * @uses plugin_dir_path() to get the path to plugin's dir
	 * @uses plugin_dir_url() to get the url to plugin's dir
	 * @uses get_option() to get an option value based on name of option.
	 */
	private function setup_globals() {
		// Version
		$this->version = '2.2.0';

		// Domain
		$this->domain = 'wp-idea-stream';

		// Base name
		$this->file       = __FILE__;
		$this->basename   = apply_filters( 'wp_idea_stream_plugin_basename', plugin_basename( $this->file ) );

		// Path and URL
		$this->plugin_dir = apply_filters( 'wp_idea_stream_plugin_dir_path', plugin_dir_path( $this->file                     ) );
		$this->plugin_url = apply_filters( 'wp_idea_stream_plugin_dir_url',  plugin_dir_url ( $this->file                     ) );
		$this->js_url     = apply_filters( 'wp_idea_stream_js_url',          trailingslashit( $this->plugin_url . 'js'        ) );
		$this->lang_dir   = apply_filters( 'wp_idea_stream_lang_dir',        trailingslashit( $this->plugin_dir . 'languages' ) );

		// Includes
		$this->includes_dir = apply_filters( 'wp_idea_stream_includes_dir_path', trailingslashit( $this->plugin_dir . 'includes'  ) );
		$this->includes_url = apply_filters( 'wp_idea_stream_includes_dir_url',  trailingslashit( $this->plugin_url . 'includes'  ) );

		// Default templates location (can be overridden from theme or child theme)
		$this->templates_dir = apply_filters( 'wp_idea_stream_templates_dir_path', trailingslashit( $this->plugin_dir . 'templates'  ) );

		// Post types / taxonomies default ids
		$this->post_type = 'ideas';
		$this->category  = 'category-ideas';
		$this->tag       = 'tag-ideas';

		// Pretty links ?
		$this->pretty_links = get_option( 'permalink_structure' );

		// template globals
		$this->is_ideastream    = false;
		$this->template_file    = false;
		$this->main_query       = array();
		$this->query_loop       = false;
		$this->per_page         = get_option( 'posts_per_page' );
		$this->is_idea_archive  = false;
		$this->is_category      = false;
		$this->is_tag           = false;
		$this->current_term     = false;
		$this->is_user          = false;
		$this->is_user_rates    = false;
		$this->is_user_comments = false;
		$this->is_action        = false;
		$this->is_new           = false;
		$this->is_edit          = false;
		$this->is_search        = false;
		$this->orderby          = false;
		$this->needs_reset      = false;

		// User globals
		$this->displayed_user   = new WP_User();
		$this->current_user     = new WP_User();
		$this->feedback         = array();
	}

	/**
	 * Includes plugin's needed files
	 *
	 * @package WP Idea Stream
	 *
	 * @since 2.0.0
	 *
	 * @uses  is_admin() to check for WordPress Administration
	 */
	private function includes() {
		require( $this->includes_dir . 'core/options.php' );
		require( $this->includes_dir . 'core/functions.php' );
		require( $this->includes_dir . 'core/rewrites.php' );
		require( $this->includes_dir . 'core/classes.php' );
		require( $this->includes_dir . 'core/capabilities.php' );
		require( $this->includes_dir . 'core/upgrade.php' );
		require( $this->includes_dir . 'core/template-functions.php' );
		require( $this->includes_dir . 'core/template-loader.php' );
		require( $this->includes_dir . 'core/widgets.php' );

		require( $this->includes_dir . 'comments/functions.php' );
		require( $this->includes_dir . 'comments/classes.php' );
		require( $this->includes_dir . 'comments/tags.php' );
		require( $this->includes_dir . 'comments/widgets.php' );

		require( $this->includes_dir . 'ideas/functions.php' );
		require( $this->includes_dir . 'ideas/classes.php' );
		require( $this->includes_dir . 'ideas/tags.php' );
		require( $this->includes_dir . 'ideas/widgets.php' );

		require( $this->includes_dir . 'users/functions.php' );
		require( $this->includes_dir . 'users/tags.php' );
		require( $this->includes_dir . 'users/widgets.php' );

		require( $this->includes_dir . 'core/actions.php' );
		require( $this->includes_dir . 'core/filters.php' );

		if ( is_admin() ) {
			require( $this->includes_dir . 'admin/admin.php' );
		}

		/**
		 * I drop some features in 2.0.0 :
		 *
		 * Using this file will allow people to keep them in,
		 * or extend WP Idea Stream from the plugin directory
		 * instead of the functions.php of the active theme.
		 *
		 * @see https://github.com/imath/wp-idea-stream/wiki/wp-idea-stream-custom.php#the-global-custom-file
		 */
		if ( file_exists( WP_PLUGIN_DIR . '/wp-idea-stream-custom.php' ) ) {
			require( WP_PLUGIN_DIR . '/wp-idea-stream-custom.php' );
		}

		/**
		 * On multisite configs, load current blog's specific custom file
		 *
		 * This will help you to have specific feature for each blog.
		 *
		 * @since  2.2.0
		 *
		 * @see https://github.com/imath/wp-idea-stream/wiki/wp-idea-stream-custom.php#on-multisite-configs-a-custom-file-for-each-blog
		 */
		if ( is_multisite() && file_exists( WP_PLUGIN_DIR . '/wp-idea-stream-custom-' . get_current_blog_id() . '.php' ) ) {
			require( WP_PLUGIN_DIR . '/wp-idea-stream-custom-' . get_current_blog_id() . '.php' );
		}
	}

	/**
	 * Setups some hooks to register post type stuff, scripts, set
	 * the current user & load plugin's BuddyPress integration
	 *
	 * @package WP Idea Stream
	 *
	 * @since 2.0.0
	 *
	 * @uses  add_action() to perform custom actions at key points
	 */
	private function setup_hooks() {
		// Main hooks
		add_action( 'wp_idea_stream_loaded',              array( $this, 'load_textdomain'     ), 0 );
		add_action( 'wp_idea_stream_register_post_types', array( $this, 'register_post_type'  )    );
		add_action( 'wp_idea_stream_register_taxonomies', array( $this, 'register_taxonomies' )    );
		add_action( 'wp_idea_stream_setup_current_user',  array( $this, 'setup_current_user'  )    );
		add_action( 'wp_idea_stream_enqueue_scripts',     array( $this, 'enqueue_scripts'     ), 1 );

		/**
		 * BuddyPress integration starts by hooking bp_include!
		 * This way we make sure all BuddyPress functions are
		 * available for the plugin.
		 */
		add_action( 'bp_include', array( $this, 'use_buddypress' ) );
	}

	/**
	 * Registers the ideas post type
	 *
	 * @package WP Idea Stream
	 *
	 * @since 2.0.0
	 *
	 * @uses register_post_type() to register the post type
	 * @uses wp_idea_stream_get_post_type() to get the ideas post type identifier
	 * @uses wp_idea_stream_post_type_register_labels() to the post type's labels
	 * @uses wp_idea_stream_post_type_register_args() to get the post type's arguments
	 */
	public function register_post_type() {
		//register the ideas post-type
		register_post_type(
			wp_idea_stream_get_post_type(),
			array_merge(
				wp_idea_stream_post_type_register_labels(),
				wp_idea_stream_post_type_register_args()
			)
		);
	}

	/**
	 * Registers the ideas taxonomies
	 *
	 * @package WP Idea Stream
	 *
	 * @since 2.0.0
	 *
	 * @uses register_taxonomy() to register the taxonomy
	 * @uses wp_idea_stream_get_category() to get the category taxonomy identifier
	 * @uses wp_idea_stream_get_post_type() to get the ideas post type identifier
	 * @uses wp_idea_stream_category_register_labels() to the category taxonomy labels
	 * @uses wp_idea_stream_category_register_args() to the category taxonomy arguments
	 * @uses wp_idea_stream_get_tag() to get the tag taxonomy identifier
	 * @uses wp_idea_stream_tag_register_labels() to the tag taxonomy labels
	 * @uses wp_idea_stream_tag_register_args() to the tag taxonomy arguments
	 */
	public function register_taxonomies() {

		// Register the category taxonomy
		register_taxonomy(
			wp_idea_stream_get_category(),
			wp_idea_stream_get_post_type(),
			array_merge(
				wp_idea_stream_category_register_labels(),
				wp_idea_stream_category_register_args()
			)
		);

		// Register the tag taxonomy
		register_taxonomy(
			wp_idea_stream_get_tag(),
			wp_idea_stream_get_post_type(),
			array_merge(
				wp_idea_stream_tag_register_labels(),
				wp_idea_stream_tag_register_args()
			)
		);
	}

	/**
	 * Setups the loggedin user
	 *
	 * @package WP Idea Stream
	 *
	 * @since 2.0.0
	 *
	 * @uses wp_get_current_user() to get the current user
	 */
	public function setup_current_user() {
		$this->current_user = wp_get_current_user();
	}

	/**
	 * Setups a globalized var for a later use
	 *
	 * @package WP Idea Stream
	 *
	 * @since 2.0.0
	 *
	 * @param string $idea_var       the key to access to the globalized var
	 * @param mixed $idea_var_value  the value of the globalized var
	 */
	public function set_idea_var( $idea_var = '', $idea_var_value = null ) {
		if ( empty( $idea_var ) || empty( $idea_var_value ) ) {
			return false;
		}

		$this->{$idea_var} = $idea_var_value;
	}

	/**
	 * Gets a globalized var
	 *
	 * @package WP Idea Stream
	 *
	 * @since 2.0.0
	 *
	 * @param  string $idea_var the key to access to the globalized var
	 * @return mixed            the value of the globalized var
	 */
	public function get_idea_var( $idea_var = '' ) {
		if ( empty( $idea_var ) || empty( $this->{$idea_var} ) ) {
			return false;
		}

		return $this->{$idea_var};
	}

	/**
	 * Registers external javascript libraries to be linked later
	 * using the wp_enqueue_script() function, & adds the plugin's stylesheet
	 *
	 * @package WP Idea Stream
	 *
	 * @since 2.0.0
	 *
	 * @uses  wp_idea_stream_is_ideastream() to check if it's plugin's territory
	 * @uses  wp_register_script() to register the external library
	 * @uses  wp_idea_stream_get_js_script() to get the javascript url
	 * @uses  wp_idea_stream_enqueue_style() to add plugin's stylesheet to WordPress queue
	 */
	public function enqueue_scripts() {
		if ( ! wp_idea_stream_is_ideastream() ) {
			return;
		}

		// Register jquery Raty
		wp_register_script( 'jquery-raty', wp_idea_stream_get_js_script( 'jquery.raty' ), array( 'jquery' ), '2.7.0.imath', true );

		// Register tagging
		wp_register_script( 'tagging', wp_idea_stream_get_js_script( 'tagging' ), array( 'jquery' ), '1.3.1', true );

		wp_idea_stream_enqueue_style();
	}

	/**
	 * Includes the plugin's BuddyPress loader file
	 *
	 * @package WP Idea Stream
	 *
	 * @since 2.0.0
	 *
	 * @uses bp_is_root_blog() to check current blog is the one where BuddyPress is activated
	 */
	public function use_buddypress() {
		/**
		 * Only Use BuddyPress if IdeaStream is activated on the same
		 * blog than BuddyPress. This way, on multisite configurations
		 * It will be possible to have independant IdeaStreams..
		 */
		if ( ! bp_is_root_blog() ) {
			return;
		}

		require( $this->includes_dir . 'buddypress/loader.php' );
	}

	/**
	 * Loads the translation files
	 *
	 * @package WP Idea Stream
	 *
	 * @since 2.0.0
	 *
	 * @uses get_locale() to get the language of WordPress config
	 * @uses load_texdomain() to load the translation if any is available for the language
	 */
	public function load_textdomain() {
		// Traditional WordPress plugin locale filter
		$locale        = apply_filters( 'plugin_locale', get_locale(), $this->domain );

		if ( empty( $locale ) ) {
			$mofile = $this->domain . '.mo';
		} else {
			$mofile = sprintf( '%1$s-%2$s.mo', $this->domain, $locale );
		}

		// Setup paths to current locale file
		$mofile_local  = $this->lang_dir . $mofile;
		$mofile_global = WP_LANG_DIR . '/' . $this->domain . '/' . $mofile;

		// Look in global /wp-content/languages/wp-idea-stream folder
		load_textdomain( $this->domain, $mofile_global );

		// Look in local /wp-content/plugins/wp-idea-stream/languages/ folder
		load_textdomain( $this->domain, $mofile_local );

		// Look in global /wp-content/languages/plugins/
		load_plugin_textdomain( $this->domain );
	}
}

endif;

/**
 * Boot function of the plugin
 *
 * It's hooked to plugins_loaded to avoid being in global scope
 * Priority is less than 10, in order to be able to use the
 * bp_include BuddyPress hook to load BuddyPress integration.
 *
 * @package WP Idea Stream
 *
 * @since 2.0.0
 *
 * @uses WP_Idea_Stream
 */
function wp_idea_stream() {
	return WP_Idea_Stream::start();
}
add_action( 'plugins_loaded', 'wp_idea_stream', 8 );

/**
 * Activation function for the plugin
 *
 * Simply create a transient to redirect the admin to the
 * welcome screen.
 * Database stuff are managed in core/upgrade.php function
 *
 * @package WP Idea Stream
 *
 * @since 2.0.0
 *
 * @uses set_transient() to set a transient
 */
function wp_idea_stream_activation() {
	// Add a transient to redirect after activation.
    set_transient( '_ideastream_activation_redirect', true, 30 );
}
add_action( 'activate_' . plugin_basename( __FILE__ ) , 'wp_idea_stream_activation' );
