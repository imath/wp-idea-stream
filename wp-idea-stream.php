<?php
/*
Plugin Name: WP Idea Stream
Plugin URI: https://imathi.eu/tag/wp-idea-stream/
Description: Share ideas, great ones will rise to the top!
Version: 2.4.0
Requires at least: 4.7
Tested up to: 4.7
License: GNU/GPL 2
Author: imath
Author URI: https://imathi.eu/
Text Domain: wp-idea-stream
Domain Path: /languages/
*/

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

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
	 * @since 2.0.0
	 */
	private function setup_globals() {
		// Version
		$this->version = '2.4.0';

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

		// Autoload
		$this->autoload = false;
	}

	/**
	 * Includes plugin's needed files
	 *
	 * @since 2.0.0
	 * @since 2.4.0 Use Class autoload when possible.
	 */
	private function includes() {
		if ( function_exists( 'spl_autoload_register' ) ) {
			spl_autoload_register( array( $this, 'autoload' ) );
			$this->autoload = true;
		}

		require( $this->includes_dir . 'core/options.php' );
		require( $this->includes_dir . 'core/functions.php' );
		require( $this->includes_dir . 'core/rewrites.php' );
		require( $this->includes_dir . 'core/capabilities.php' );
		require( $this->includes_dir . 'core/upgrade.php' );
		require( $this->includes_dir . 'core/template-functions.php' );
		require( $this->includes_dir . 'core/template-loader.php' );

		require( $this->includes_dir . 'comments/functions.php' );
		require( $this->includes_dir . 'comments/tags.php' );

		require( $this->includes_dir . 'ideas/metas.php' );
		require( $this->includes_dir . 'ideas/functions.php' );
		require( $this->includes_dir . 'ideas/tags.php' );

		require( $this->includes_dir . 'users/functions.php' );
		require( $this->includes_dir . 'users/tags.php' );

		if ( ! $this->autoload ) {
			require( $this->includes_dir . 'core/classes.php' );
			require( $this->includes_dir . 'comments/classes.php' );
			require( $this->includes_dir . 'ideas/classes.php' );
			require( $this->includes_dir . 'users/classes.php' );
		}

		require( $this->includes_dir . 'core/actions.php' );
		require( $this->includes_dir . 'core/filters.php' );

		if ( is_admin() ) {
			if ( ! $this->autoload ) {
				require( $this->includes_dir . 'admin/admin.php' );
			}
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
	 * @since 2.0.0
	 */
	private function setup_hooks() {
		// Main hooks
		add_action( 'wp_idea_stream_loaded',              array( $this, 'load_textdomain'     ), 0 );
		add_action( 'wp_idea_stream_register_post_types', array( $this, 'register_post_type'  )    );
		add_action( 'wp_idea_stream_register_post_stati', array( $this, 'register_post_stati' )    );
		add_action( 'wp_idea_stream_register_taxonomies', array( $this, 'register_taxonomies' )    );
		add_action( 'wp_idea_stream_setup_current_user',  array( $this, 'setup_current_user'  )    );
		add_action( 'wp_idea_stream_enqueue_scripts',     array( $this, 'enqueue_scripts'     ), 1 );

		// Boot the Admin
		if ( is_admin() ) {
			add_action( 'wp_idea_stream_loaded', array( 'WP_Idea_Stream_Admin', 'start' ), 5 );
		}
	}

	/**
	 * Registers the ideas post type
	 *
	 * @since 2.0.0
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

		// Register a private utility post type
		register_post_type(
			'ideastream_utility',
			array(
				'label'              => 'ideastream_utility',
				'public'             => false,
				'publicly_queryable' => false,
				'show_ui'            => false,
				'show_in_menu'       => false,
				'show_in_nav_menus'  => false,
				'query_var'          => false,
				'rewrite'            => false,
				'has_archive'        => false,
				'hierarchical'       => true,
			)
		);
	}

	/**
	 * Registers the ideas post stati
	 *
	 * @since 2.4.0
	 */
	public function register_post_stati() {
		register_post_status( 'wpis_archive', array(
			'label'                     => __( 'Archive', 'wp-idea-stream' ),
			'label_count'               => _n_noop( 'Archived <span class="count">(%s)</span>', 'Archived <span class="count">(%s)</span>', 'wp-idea-stream' ),
			'public'                    => false,
			'show_in_admin_all_list'    => false,
			'show_in_admin_status_list' => true,
			'bulk_action_label'         => __( 'Archive selected ideas', 'wp-idea-stream' ),
		) );
	}

	/**
	 * Registers the ideas taxonomies
	 *
	 * @since 2.0.0
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
	 * @since 2.0.0
	 */
	public function setup_current_user() {
		$this->current_user = wp_get_current_user();
	}

	/**
	 * Setups a globalized var for a later use
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
	 * @since 2.0.0
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
	 * Loads the translation files
	 *
	 * @since 2.0.0
	 */
	public function load_textdomain() {
		// Traditional WordPress plugin locale filter
		$locale = apply_filters( 'plugin_locale', get_locale(), $this->domain );

		if ( empty( $locale ) ) {
			$mofile = $this->domain . '.mo';
		} else {
			$mofile = sprintf( '%1$s-%2$s.mo', $this->domain, $locale );
		}

		// Setup paths to current locale file
		$mofile_local  = $this->lang_dir . $mofile;
		$mofile_global = WP_LANG_DIR . '/' . $this->domain . '/' . $mofile;

		/**
		 * Need to use custom language pack for a specific blog of the network ?
		 *
		 * Simply put your custom language pack in /wp-content/languages/wp-idea-stream/blog_id/
		 * and it will be used instead of generic ones.
		 *
		 * @since  2.2.0
		 * @since  2.3.3 Use load_plugin_textdomain() the right way to make sure "my" french translation will
		 *               be loaded.
		 */
		if ( is_multisite() ) {
			$mofile_current_blog = WP_LANG_DIR . '/' . $this->domain . '/' . get_current_blog_id() . '/' . $mofile;

			// Look in global /wp-content/languages/wp-idea-stream/blog_id/ folder
			load_textdomain( $this->domain, $mofile_current_blog );
		}

		// Look in global /wp-content/languages/wp-idea-stream folder
		load_textdomain( $this->domain, $mofile_global );

		// Always Look in /wp-content/plugins/wp-idea-stream/languages for the french translation.
		if ( 'fr_FR' === $locale ) {
			load_textdomain( $this->domain, trailingslashit( $this->plugin_dir ) . 'languages/' . $mofile );

		// Rely on GlotPress for other languages
		} else {
			load_plugin_textdomain( $this->domain, false, trailingslashit( basename( $this->plugin_dir ) ) . 'languages' );
		}
	}

	/**
	 * Class Autoload function
	 *
	 * @since  2.4.0
	 *
	 * @param  string $class The class name.
	 */
	public function autoload( $class ) {
		$name = str_replace( '_', '-', strtolower( $class ) );

		if ( false === strpos( $name, $this->domain ) ) {
			return;
		}

		// Class Name => includes' folder
		$map = array(
			'wp-idea-stream-loop'            => 'core',
			'wp-idea-stream-navig'           => 'core',
			'wp-idea-stream-rewrites'        => 'core',
			'wp-idea-stream-template-loader' => 'core',
			'wp-idea-stream-loop-comments'   => 'comments',
			'wp-idea-stream-idea'            => 'ideas',
			'wp-idea-stream-loop-ideas'      => 'ideas',
			'wp-idea-stream-idea-metas'      => 'ideas',
		);

		$folder = null;

		if ( isset( $map[$name] ) ) {
			$folder = $map[$name];
		} else {
			$parts = explode( '-', $name );
			$folder = $parts[3];
		}

		$path = $this->includes_dir . "{$folder}/classes/class-{$name}.php";

		// Sanity check.
		if ( ! file_exists( $path ) ) {
			return;
		}

		require $path;
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
 * @since 2.0.0
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
 * @since 2.0.0
 */
function wp_idea_stream_activation() {
	// Add a transient to redirect after activation.
    set_transient( '_ideastream_activation_redirect', true, 30 );
}
add_action( 'activate_' . plugin_basename( __FILE__ ) , 'wp_idea_stream_activation' );
