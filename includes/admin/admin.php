<?php
/**
 * WP Idea Stream Admin.
 *
 * IdeaStream Administration
 *
 * @package WP Idea Stream
 * @subpackage admin/admin
 *
 * @since 2.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WP_Idea_Stream_Admin' ) ) :

class WP_Idea_Stream_Admin {

	/** Variables ****************************************************************/

	/**
	 * @access  private
	 * @var string The ideas post type
	 */
	private $post_type = '';

	/**
	 * @access  public
	 * @var string path to includes dir
	 */
	private $includes_dir = '';

	/**
	 * @access  public
	 * @var string the parent slug for submenus
	 */
	public $parent_slug;

	/**
	 * @access  public
	 * @var array the list of available metaboxes
	 */
	public $metaboxes;

	/**
	 * @access  public
	 * @var bool whether it's plugin's settings page or not
	 */
	public $is_plugin_settings;

	/**
	 * The Admin constructor
	 *
	 * @package WP Idea Stream
	 * @subpackage admin/admin
	 *
	 * @since 2.0.0
	 */
	public function __construct() {
		$this->setup_globals();
		$this->includes();
		$this->hooks();
	}

	/**
	 * Starts the Admin class
	 *
	 * @package WP Idea Stream
	 * @subpackage admin/admin
	 *
	 * @since 2.0.0
	 *
	 * @uses  is_admin() to check for WordPress Administration
	 * @uses  wp_idea_stream() to get WP Idea Stream instance
	 */
	public static function start() {
		if ( ! is_admin() ) {
			return;
		}

		$wp_idea_stream = wp_idea_stream();

		if ( empty( $wp_idea_stream->admin ) ) {
			$wp_idea_stream->admin = new self;
		}

		return $wp_idea_stream->admin;
	}

	/**
	 * Setups some globals
	 *
	 * @package WP Idea Stream
	 * @subpackage admin/admin
	 *
	 * @since 2.0.0
	 *
	 * @uses wp_idea_stream_get_post_type() to get the ideas post type identifier
	 * @uses wp_idea_stream() to get WP Idea Stream instance
	 */
	private function setup_globals() {
		$this->post_type     = wp_idea_stream_get_post_type();
		$this->includes_dir  = trailingslashit( wp_idea_stream()->includes_dir . 'admin' );
		$this->parent_slug   = false;

		$this->metaboxes          = array();
		$this->is_plugin_settings = false;
		$this->downloading_csv    = false;
		$this->current_view       = 'edit';
	}

	/**
	 * Includes the needed admin files
	 *
	 * @package WP Idea Stream
	 * @subpackage admin/admin
	 *
	 * @since 2.0.0
	 *
	 * @uses wp_idea_stream_is_comments_disjoined() to check for comments disjoin setting
	 * @uses wp_idea_stream_is_sticky_enabled() to check for sticky setting
	 */
	private function includes() {
		// By default, comments are disjoined from the other post types.
		if ( wp_idea_stream_is_comments_disjoined() ) {
			require( $this->includes_dir . 'comments.php' );
		}

		// By default, ideas can be sticked to front post type archive page.
		if ( wp_idea_stream_is_sticky_enabled() ) {
			require( $this->includes_dir . 'sticky.php' );
		}

		// Settings
		require( $this->includes_dir . 'settings.php' );

		// About & credits screens
		require( $this->includes_dir . 'thanks.php' );
	}

	/**
	 * Setups the action and filters to hook to
	 *
	 * @package WP Idea Stream
	 * @subpackage admin/admin
	 *
	 * @since 2.0.0
	 *
	 * @uses add_action() to perform custom actions at key points
	 * @uses add_filter() to override some key vars
	 * @uses wp_idea_stream_is_rating_disabled() to be sure rating is enabled
	 */
	private function hooks() {

		/** Actions *******************************************************************/

		// Build the submenus.
		add_action( 'admin_menu', array( $this, 'admin_menus' ), 10 );

		// Loading the ideas edit screen
		add_action( 'load-edit.php', array( $this, 'load_edit_idea' ) );

		// Make sure Editing a plugin's taxonomy highlights IdeaStream nav
		add_action( 'load-edit-tags.php', array( $this, 'taxonomy_highlight' ) );

		// Add metaboxes for the post type
		add_action( "add_meta_boxes_{$this->post_type}", array( $this, 'add_metaboxes' ),       10, 1 );
		// Save metabox inputs
		add_action( "save_post_{$this->post_type}",      array( $this, 'save_metaboxes' ),      10, 3 );

		// Display upgrade notices
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );

		// Register the settings
		add_action( 'wp_idea_stream_admin_register_settings', array( $this, 'register_admin_settings' ) );

		add_action( 'load-settings_page_ideastream', array( $this, 'settings_load' ) );

		// Idea columns (in post row)
		add_action( "manage_{$this->post_type}_posts_custom_column", array( $this, 'column_data' ), 10, 2 );

		// Neutralize quick edit
		add_action( 'post_row_actions', array( $this, 'idea_row_actions'), 10, 2 );

		// Do some global stuff here (custom css rule)
		add_action( 'wp_idea_stream_admin_head', array( $this, 'admin_head' ), 10 );

		/** Filters *******************************************************************/

		// Updated message
		add_filter( 'post_updated_messages',      array( $this, 'ideas_updated_messages' ),      10, 1 );
		add_filter( 'bulk_post_updated_messages', array( $this, 'ideas_updated_bulk_messages' ), 10, 2 );

		// Redirect
		add_filter( 'redirect_post_location', array( $this, 'redirect_idea_location' ), 10, 2 );

		// Filter the WP_List_Table views to include custom views.
		add_filter( "views_edit-{$this->post_type}", array( $this, 'idea_views' ), 10, 1 );

		// Customize bulk actions
		add_filter( "bulk_actions-edit-{$this->post_type}",        array( $this, 'idea_bulk_actions' ),        10, 1 );
		add_filter( "handle_bulk_actions-edit-{$this->post_type}", array( $this, 'idea_handle_bulk_actions' ), 10, 4 );

		// Idea column headers.
		add_filter( "manage_{$this->post_type}_posts_columns", array( $this, 'column_headers' ) );

		// Add a link to About & settings page in plugins list
		add_filter( 'plugin_action_links', array( $this, 'modify_plugin_action_links' ), 10, 2 );

		/** Specific case: ratings ****************************************************/

		// Only sort by rates & display people who voted if ratings is not disabled.
		if ( ! wp_idea_stream_is_rating_disabled() ) {
			add_action( "manage_edit-{$this->post_type}_sortable_columns", array( $this, 'sortable_columns' ), 10, 1 );

			// Manage votes
			add_filter( 'wp_idea_stream_admin_get_meta_boxes', array( $this, 'ratings_metabox' ),   9, 1 );
			add_action( 'load-post.php',                       array( $this, 'maybe_delete_rate' )       );

			// Custom feedback
			add_filter( 'wp_idea_stream_admin_updated_messages', array( $this, 'ratings_updated' ), 10, 1 );

			// Help tabs
			add_filter( 'wp_idea_stream_get_help_tabs', array( $this, 'rates_help_tabs' ), 11, 1 );
		}
	}

	/**
	 * Builds the different IdeaStream admin menus and submenus
	 *
	 * @package WP Idea Stream
	 * @subpackage admin/admin
	 *
	 * @since 2.0.0
	 *
	 * @uses add_query_arg() to build the menu items default parent slug
	 * @uses add_options_page() to add the settings submenu
	 * @uses add_submenu_page() to add a submenu to parent slug
	 * @uses add_dashboard_page() to add the welcome and credits screens
	 * @uses add_action() to run some custom actions regarding the menu args
	 * @uses apply_filters() call 'wp_idea_stream_admin_menus' to add/edit/remove menu items
	 */
	public function admin_menus() {
		$menus = array();
		$this->parent_slug = add_query_arg( 'post_type', $this->post_type, 'edit.php' );

		/**
		 * @param array list of menu items
		 */
		$menus = apply_filters( 'wp_idea_stream_admin_menus', array(
			/* Settings has a late order to be at last position */
			10 => array(
				'type'          => 'settings',
				'parent_slug'   => $this->parent_slug,
				'page_title'    => esc_html__( 'Settings',  'wp-idea-stream' ),
				'menu_title'    => esc_html__( 'Settings',  'wp-idea-stream' ),
				'capability'    => 'wp_idea_stream_ideas_admin',
				'slug'          => add_query_arg( 'page', 'ideastream', 'options-general.php' ),
				'function'      => '',
				'alt_screen_id' => 'settings_page_ideastream',
				'actions'       => array(
					'admin_head-%page%' => array( $this, 'settings_menu_highlight' ),
				),
			),
		) );

		// Fake an option page to register the handling function
		// Then remove it hooking admin_head.
		add_options_page(
			esc_html__( 'Settings',  'wp-idea-stream' ),
			esc_html__( 'Settings',  'wp-idea-stream' ),
			'manage_options',
			'ideastream',
			'wp_idea_stream_settings'
		);

		// Sort the menus
		ksort( $menus );

		// Build the sub pages and particular hooks
		foreach ( $menus as $menu ) {
			$screen_id = add_submenu_page(
				$menu['parent_slug'],
				$menu['page_title'],
				$menu['menu_title'],
				$menu['capability'],
				$menu['slug'],
				$menu['function']
			);

			if ( ! empty( $menu['alt_screen_id'] ) ) {
				$screen_id = $menu['alt_screen_id'];
			}

			foreach ( $menu['actions'] as $key => $action ) {
				add_action( str_replace( '%page%', $screen_id, $key ), $action );
			}
		}

		// finally add the about & credit pages
		add_dashboard_page(
			__( 'Welcome to WP Idea Stream',  'wp-idea-stream' ),
			__( 'Welcome to WP Idea Stream',  'wp-idea-stream' ),
			'manage_options',
			'about-ideastream',
			'wp_idea_stream_admin_about'
		);

		add_dashboard_page(
			__( 'Welcome to WP Idea Stream',  'wp-idea-stream' ),
			__( 'Welcome to WP Idea Stream',  'wp-idea-stream' ),
			'manage_options',
			'credits-ideastream',
			'wp_idea_stream_admin_credits'
		);
	}

	/**
	 * Hooks Admin edit screen load to eventually perform
	 * actions before the WP_List_Table is generated.
	 *
	 * @package WP Idea Stream
	 * @subpackage admin/admin
	 *
	 * @since 2.1.0
	 *
	 * @uses   wp_idea_stream_is_admin() to make sure it's an IdeaStream admin screen
	 * @uses   check_admin_referer() to check the request was made on the site
	 * @uses   add_action() to perform a custom action
	 * @uses   WP_Idea_Stream_Admin->csv_export() to export all ideas in an csv spreadsheet
	 * @uses   do_action() call 'wp_idea_stream_load_edit_idea' to perform custom actions
	 */
	public function load_edit_idea() {
		// Make sure it's an Idea Stream admin screen
		if ( ! wp_idea_stream_is_admin() ) {
			return;
		}

		if ( ! empty( $_GET['csv'] ) ) {

			check_admin_referer( 'wp_idea_stream_is_csv' );

			$this->downloading_csv = true;

			// Add content row data
			add_action( 'wp_idea_stream_admin_column_data', array( $this, 'idea_row_extra_data'), 10, 2 );

			$this->csv_export();

		// Is there an idea to unarchive ?
		} elseif ( ! empty( $_GET['wpis_unarchive'] ) ) {
			$idea_id = (int) $_GET['wpis_unarchive'];

			$redirect = $this->idea_handle_bulk_actions( false, 'wpis_unarchive', array( $idea_id ) );

			if ( $redirect ) {
				wp_safe_redirect( $redirect );
				exit();
			}
		// Other plugins can do stuff here
		} else {
			do_action( 'wp_idea_stream_load_edit_idea' );
		}
	}

	/**
	 * Add metaboxes in Edit and Post New Idea screens
	 *
	 * @package WP Idea Stream
	 * @subpackage admin/admin
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Post $idea the idea object
	 * @uses  apply_filters() call 'wp_idea_stream_admin_get_meta_boxes' to add/edit/remove metaboxes
	 * @uses  add_meta_box() to add the meta boxes
	 * @uses  do_action() call 'wp_idea_stream_add_metaboxes' to perform custom actions
	 */
	public function add_metaboxes( $idea = null ) {
		if ( 'wpis_archive' === get_post_status( $idea ) ) {
			$this->metaboxes = array(
				'unarchive' => array(
					'id'            => 'wp_idea_stream_unarchive',
					'title'         => __( 'Unarchive', 'wp-idea-stream' ),
					'callback'      => array( $this, 'unarchive_do_metabox' ),
					'context'       => 'side',
					'priority'      => 'high'
			) );

			remove_meta_box( 'submitdiv', get_current_screen(), 'side' );
		}

		/**
		 * @see  $this->ratings_metabox() for an example of use
		 * @param array $metaboxes list of metaboxes to add
		 */
		$this->metaboxes = apply_filters( 'wp_idea_stream_admin_get_meta_boxes', $this->metaboxes );

		if ( empty( $this->metaboxes ) ) {
			return;
		}

		foreach ( $this->metaboxes as $metabox ) {
			$m = array_merge( array(
				'id'            => '',
				'title'         => '',
				'callback'      => '',
				'context'       => '',
				'priority'      => '',
				'callback_args' => array()
			), $metabox );

			if ( empty( $m['id'] ) || empty( $m['title'] ) || empty( $m['callback'] ) ) {
				continue;
			}

			// Add the metabox
			add_meta_box(
				$m['id'],
				$m['title'],
				$m['callback'],
				$this->post_type,
				$m['context'],
				$m['priority'],
				$m['callback_args']
			);
		}

		/**
		 * @param WP_Post $idea the idea object
		 */
		do_action( 'wp_idea_stream_add_metaboxes', $idea );
	}

	/**
	 * Output a metabox to unarchive ideas.
	 *
	 * @since 2.4.0
	 *
	 * @param WP_Post $idea the idea object
	 * @return string HTML Output.
	 */
	public function unarchive_do_metabox( $idea = null ) {
		if ( empty( $idea->ID ) ) {
			return;
		}

		$status = get_post_meta( $idea->ID, '_wp_idea_stream_original_status', true );

		if ( ! $status ) {
			$status = 'private';
		}
		
		$idea_status = get_post_status_object( $status );

		$status_description = '';
		if ( ! empty( $idea_status->label ) ) {
			$status_description = sprintf( __( 'The status for the idea, once unarchived, will be: %s.', 'wp-idea-stream'), $idea_status->label );
		}
		
		printf( '<p class="description">%1$s</p><div style="text-align: right"><a href="%2$s" class="button button-primary button-large">%3$s</a></div>',
			esc_html( $status_description ),
			esc_url( add_query_arg( array(
				'post_type'      => wp_idea_stream_get_post_type(),
				'wpis_unarchive' => $idea->ID,
			), admin_url( 'edit.php' ) ) ),
			esc_html__( 'Unarchive', 'wp-idea-stream' )
		);
	}

	/**
	 * Fire an action to save the metabox entries
	 *
	 * @package WP Idea Stream
	 * @subpackage admin/admin
	 *
	 * @since 2.0.0
	 *
	 * @param  int      $id     the idea ID
	 * @param  WP_Post  $idea   the idea object
	 * @param  boolean $update  whether it's an update or not
	 * @uses   do_action() call 'wp_idea_stream_save_metaboxes' to perform custom actions
	 */
	public function save_metaboxes( $id = 0, $idea = null, $update = false ) {
		// Bail if doing an autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $id;
		}

		// Bail if not a post request
		if ( 'POST' != strtoupper( $_SERVER['REQUEST_METHOD'] ) ) {
			return $id;
		}

		/**
		 * @param  int      $id     the idea ID
		 * @param  WP_Post  $idea   the idea object
		 * @param  boolean $update  whether it's an update or not
		 */
		do_action( 'wp_idea_stream_save_metaboxes', $id, $idea, $update );
	}

	/**
	 * Create specific updated messages for ideas
	 *
	 * @package WP Idea Stream
	 * @subpackage admin/admin
	 *
	 * @since 2.0.0
	 *
	 * @global $post
	 * @param  array  $messages list of available updated messages
	 * @uses   wp_idea_stream_is_admin() to check if on an IdeaStream Administration screen
	 * @uses   apply_filters() call 'wp_idea_stream_admin_updated_messages' to add/edit/remove updated messages
	 * @uses   esc_url() to sanitize a link
	 * @uses   wp_idea_stream_ideas_get_idea_permalink() to get the idea permalink
	 * @uses   wp_post_revision_title()
	 * @uses   date_i18n() to format a date
	 * @uses   add_query_arg() to add query vars to an url
	 * @return array  the original updated messages if not on an IdeaStream screen, custom messages otherwise
	 */
	public function ideas_updated_messages( $messages = array() ) {
		global $post;

		// Bail if not posting/editing an idea
		if ( ! wp_idea_stream_is_admin() ) {
			return $messages;
		}

		/**
		 * @param  array list of IdeaStream updated messages
		 */
		$messages[ $this->post_type ] = apply_filters( 'wp_idea_stream_admin_updated_messages', array(
			 0 => '', // Unused. Messages start at index 1.
			 1 => sprintf( __( 'Idea updated. <a href="%s">View idea</a>', 'wp-idea-stream' ), esc_url( wp_idea_stream_ideas_get_idea_permalink( $post ) ) ),
			 2 => __( 'Custom field updated.', 'wp-idea-stream' ),
			 3 => __( 'Custom field deleted.', 'wp-idea-stream' ),
			 4 => __( 'Idea updated.', 'wp-idea-stream'),
			 5 => isset( $_GET['revision'] ) ? sprintf( __( 'Idea restored to revision from %s', 'wp-idea-stream' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			 6 => sprintf( __( 'Idea published. <a href="%s">View idea</a>', 'wp-idea-stream' ), esc_url( wp_idea_stream_ideas_get_idea_permalink( $post ) ) ),
			 7 => __( 'Idea saved.', 'wp-idea-stream' ),
			 8 => sprintf( __( 'Idea submitted. <a target="_blank" href="%s">Preview idea</a>', 'wp-idea-stream' ), esc_url( add_query_arg( 'preview', 'true', wp_idea_stream_ideas_get_idea_permalink( $post  ) ) ) ),
			 9 => sprintf(
			 		__( 'Idea scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview idea</a>', 'wp-idea-stream' ),
					date_i18n( _x( 'M j, Y @ G:i', 'Idea Publish box date format', 'wp-idea-stream' ), strtotime( $post->post_date ) ),
					esc_url( wp_idea_stream_ideas_get_idea_permalink( $post ) )
				),
			10 => sprintf( __( 'Idea draft updated. <a target="_blank" href="%s">Preview idea</a>', 'wp-idea-stream' ), esc_url( add_query_arg( 'preview', 'true', wp_idea_stream_ideas_get_idea_permalink( $post ) ) ) ),
		) );

		return $messages;
	}

	/**
	 * Create specific updated bulk messages for ideas
	 *
	 * @package WP Idea Stream
	 * @subpackage admin/admin
	 *
	 * @since 2.0.0
	 *
	 * @param  array  $bulk_messages list of available updated bulk messages
	 * @param  array  $bulk_counts   count list by type
	 * @uses   wp_idea_stream_is_admin() to check if on an IdeaStream Administration screen
	 * @uses   apply_filters() call 'wp_idea_stream_admin_updated_bulk_messages' to add/edit/remove updated bulk messages
	 * @return array  the original updated bulk messages if not on an IdeaStream screen, custom messages otherwise
	 */
	public function ideas_updated_bulk_messages( $bulk_messages = array(), $bulk_counts = array() ) {
		// Bail if not posting/editing an idea
		if ( ! wp_idea_stream_is_admin() ) {
			return $bulk_messages;
		}

		$bulk_messages[ $this->post_type ] = apply_filters( 'wp_idea_stream_admin_updated_bulk_messages', array(
			'updated'   => _n( '%s idea updated.', '%s ideas updated.', $bulk_counts['updated'], 'wp-idea-stream' ),
			'locked'    => _n( '%s idea not updated; somebody is editing it.', '%s ideas not updated; somebody is editing them.', $bulk_counts['locked'], 'wp-idea-stream' ),
			'deleted'   => _n( '%s idea permanently deleted.', '%s ideas permanently deleted.', $bulk_counts['deleted'], 'wp-idea-stream' ),
			'trashed'   => _n( '%s idea moved to the Trash.', '%s ideas moved to the Trash.', $bulk_counts['trashed'], 'wp-idea-stream' ),
			'untrashed' => _n( '%s idea restored from the Trash.', '%s ideas restored from the Trash.', $bulk_counts['untrashed'], 'wp-idea-stream' ),
		) );

		global $bulk_counts;

		$bulk_counts = array_merge( $bulk_counts, array(
			'archived'   => 0,
			'unarchived' => 0,
		) );

		if ( isset( $_REQUEST['archived'] ) ) {
			$bulk_counts['archived'] = absint( $_REQUEST['archived'] );
		}

		if ( isset( $_REQUEST['unarchived'] ) ) {
			$bulk_counts['unarchived'] = absint( $_REQUEST['unarchived'] );
		}

		$bulk_messages[ $this->post_type ] = array_merge( $bulk_messages[ $this->post_type ], array(
			'archived'   => _n( '%s idea archived.', '%s ideas archived.', $bulk_counts['archived'], 'wp-idea-stream' ),
			'unarchived' => _n( '%s idea unarchived.', '%s ideas unarchived.', $bulk_counts['unarchived'], 'wp-idea-stream' ),
		) );

		return $bulk_messages;
	}

	/**
	 * Build a specific redirect url to handle specific feedbacks
	 *
	 * @package WP Idea Stream
	 * @subpackage admin/admin
	 *
	 * @since 2.0.0
	 *
	 * @param  string  $location url to redirect to
	 * @param  int     $idea_id  the idea ID
	 * @uses   wp_idea_stream_is_admin() to check if on an IdeaStream Administration screen
	 * @uses   wp_idea_stream_get_idea_var() to get a globalized IdeaStream var
	 * @uses   get_edit_post_link() to get the idea edit link
	 * @uses   add_query_arg() to add query vars to an url
	 * @return string            url to redirect to
	 */
	public function redirect_idea_location( $location = '', $idea_id = 0 ) {
		// Bail if not posting/editing an idea
		if ( ! wp_idea_stream_is_admin() || empty( $idea_id ) ) {
			return $location;
		}

		if ( ! empty( $_POST['addmeta'] ) || ! empty( $_POST['deletemeta'] ) ) {
			return $location;
		}

		$messages = wp_idea_stream_get_idea_var( 'feedback' );

		if ( empty( $messages['updated_message'] ) ) {
			return $location;
		}

		return add_query_arg( 'message', $messages['updated_message'], get_edit_post_link( $idea_id, 'url' ) );
	}

	/**
	 * The idea views (Over the top of WP_List_Table)
	 *
	 * @package WP Idea Stream
	 * @subpackage admin/admin
	 *
	 * @since 2.1.0
	 *
	 * @param  array  $views list of views for the edit ideas screen
	 * @uses   apply_filters() call 'wp_idea_stream_admin_edit_ideas_views' to add/edit/remove views
	 * @uses   add_query_arg() to add query vars to an url
	 * @uses   admin_url() to build the new links
	 * @uses   esc_url() to sanitize the url
	 * @uses   wp_nonce_url() to add a security token to check upon once the link clicked
	 * @return array         idea views
	 */
	public function idea_views( $views = array() ) {
		/**
		 * Add new views to edit ideas page
		 * @param  array $view list of views
		 */
		$idea_views = apply_filters( 'wp_idea_stream_admin_edit_ideas_views', $views );

		$csv_args = array(
			'post_type' => $this->post_type,
			'csv'       => 1,
		);

		if ( ! empty( $_GET['post_status'] ) ) {
			$this->current_view      = sanitize_key( $_GET['post_status'] );
			$csv_args['post_status'] = $this->current_view;
		}

		$csv_url = add_query_arg(
			$csv_args,
			admin_url( 'edit.php' )
		);

		$csv_link = sprintf( '<a href="%s" id="wp-idea-stream-csv" title="%s"><span class="dashicons dashicons-media-spreadsheet"></span></a>',
			esc_url( wp_nonce_url( $csv_url, 'wp_idea_stream_is_csv' ) ),
			esc_attr__( 'Download all ideas in a csv spreadsheet', 'wp-idea-stream' )
		);

		return array_merge( $idea_views, array(
			'csv_ideas' => $csv_link
		) );
	}

	/**
	 * Displays IdeaStream notices if any
	 *
	 * @package WP Idea Stream
	 * @subpackage admin/admin
	 *
	 * @since 2.0.0
	 *
	 * @uses   wp_idea_stream_get_idea_var() to get a globalized IdeaStream var
	 * @uses   esc_html() to sanitize output
	 * @return string HTML output
	 */
	public function admin_notices() {
		$notices = wp_idea_stream_get_idea_var( 'feedback' );

		if ( empty( $notices['admin_notices'] ) ) {
			return;
		}
		?>
		<div class="update-nag">
			<?php foreach ( $notices['admin_notices'] as $notice ) : ?>
				<p><?php echo $notice; ?></p>
			<?php endforeach ;?>
		</div>
		<?php
	}

	/**
	 * Registers IdeaStream global settings
	 *
	 * @package WP Idea Stream
	 * @subpackage admin/admin
	 *
	 * @since 2.0.0
	 *
	 * @uses  wp_idea_stream_get_settings_sections() to get the settings sections
	 * @uses  wp_idea_stream_user_can() to check for user's capabilities
	 * @uses  wp_idea_stream_get_settings_fields_for_section() to get the fields in the section
	 * @uses  add_settings_section() to add the settings section
	 * @uses  add_settings_field() to add a setting field
	 * @uses  register_setting() to register the setting
	 */
	public function register_admin_settings() {
		// Bail if no sections available
		$sections = wp_idea_stream_get_settings_sections();

		if ( empty( $sections ) )
			return false;

		// Loop through sections
		foreach ( (array) $sections as $section_id => $section ) {

			// Only proceed if current user can see this section
			if ( ! wp_idea_stream_user_can( 'wp_idea_stream_ideas_admin' ) )
				continue;

			// Only add section and fields if section has fields
			$fields = wp_idea_stream_get_settings_fields_for_section( $section_id );
			if ( empty( $fields ) )
				continue;

			// Add the section
			add_settings_section( $section_id, $section['title'], $section['callback'], $section['page'] );

			// Loop through fields for this section
			foreach ( (array) $fields as $field_id => $field ) {

				// Add the field
				add_settings_field( $field_id, $field['title'], $field['callback'], $section['page'], $section_id, $field['args'] );

				// Register the setting
				register_setting( $section['page'], $field_id, $field['sanitize_callback'] );
			}
		}
	}

	/**
	 * Make sure the settings save messages are displayed
	 *
	 * @package WP Idea Stream
	 * @subpackage admin/admin
	 *
	 * @since 2.0.0
	 *
	 * @uses  add_action() to perform a custom action
	 * @uses  wp_idea_stream_is_pretty_links() to check if a custom permalink structure is used
	 * @uses  flush_rewrite_rules()
	 */
	public function settings_load() {
		// First restore settings feedback lost as $parent file is no more options-general.php
		add_action( 'all_admin_notices', array( $this, 'restore_settings_feedback' ) );

		// Then flush rewrite rules if needed.
		if ( wp_idea_stream_is_pretty_links() && isset( $_GET['settings-updated'] ) && isset( $_GET['page'] ) ) {
			flush_rewrite_rules();
		}

		$this->is_plugin_settings = true;
	}

	/**
	 * Include options head file to restore settings feedback
	 *
	 * @package WP Idea Stream
	 * @subpackage admin/admin
	 *
	 * @since 2.0.0
	 */
	public function restore_settings_feedback() {
		require( ABSPATH . 'wp-admin/options-head.php' );
	}

	/**
	 * Customize the highlighted parent menu for IdeaStream settings
	 *
	 * @package WP Idea Stream
	 * @subpackage admin/admin
	 *
	 * @since 2.0.0
	 *
	 * @global $parent_file
	 * @global $submenu_file
	 * @global $typenow
	 * @uses   add_query_arg() to build parent slug
	 */
	public function settings_menu_highlight() {
		global $parent_file, $submenu_file, $typenow;

		$parent_file  = add_query_arg( 'post_type', $this->post_type, 'edit.php' );
		$submenu_file = add_query_arg( 'page', 'ideastream', 'options-general.php' );
		$typenow = $this->post_type;
	}

	/**
	 * Make sure the highlighted menus are IdeaStream ones for IdeaStream taxonomies
	 *
	 * @package WP Idea Stream
	 * @subpackage admin/admin
	 *
	 * @since 2.0.0
	 *
	 * @global $current_screen
	 * @global $axnow
	 * @uses   add_query_arg() to build parent slug
	 * @uses   wp_idea_stream_get_tag() to get the ideas tag taxonomy identifier
	 * @uses   wp_idea_stream_get_category() to get the ideas category taxonomy identifier
	 */
	public function taxonomy_highlight() {
		global $current_screen, $taxnow;

		if ( is_a( $current_screen, 'WP_Screen' ) && ! empty( $taxnow ) && in_array( $taxnow, array( wp_idea_stream_get_tag(), wp_idea_stream_get_category() ) ) ) {
 			$current_screen->post_type = $this->post_type;
 		}
	}

	/**
	 * Restrict Bulk actions to only keep the delete one
	 *
	 * @package WP Idea Stream
	 * @subpackage admin/admin
	 *
	 * @since 2.0.0
	 *
	 * @param  array  $bulk_actions list of available bulk actions
	 * @return array                the new list
	 */
	public function idea_bulk_actions( $bulk_actions = array() ) {
		if ( in_array( 'edit', array_keys( $bulk_actions ) ) ) {
			unset( $bulk_actions['edit'] );
		}

		$status = get_post_status_object( 'wpis_archive' );

		if ( ! is_null( $status ) ) {
			if ( 'wpis_archive' === $this->current_view ) {
				$bulk_actions[ 'wpis_unarchive' ] = __( 'Unarchive selected ideas', 'wp-idea-stream' );
			} else {
				$bulk_actions[ $status->name ] = $status->bulk_action_label;
			}
		}

		return $bulk_actions;
	}

	/**
	 * Handle the Archive bulk actions.
	 *
	 * @since  2.4.0
	 *
	 * @param  string  $redirect The referer url.
	 * @param  string  $action   The requested bulk action.
	 * @param  array   $post_ids The list of post ids
	 * @return string            The custom redirect url.
	 */
	public function idea_handle_bulk_actions( $redirect = '', $action = '', $post_ids = array() ) {
		if ( ( 'wpis_archive' !== $action && 'wpis_unarchive' !== $action ) || empty( $post_ids ) ) {
			return $redirect;
		}

		$idea_ids = array_filter( wp_parse_id_list( $post_ids ) );

		if ( empty( $idea_ids ) ) {
			return $redirect;
		}

		
		$key         = 'archived';
		$idea_status = $action;

		if ( 'wpis_unarchive' === $action ) {
			$key = 'unarchived';
		}

		$result = array(
			'post_type' => $this->post_type,
			$key        => 0,
		);

		foreach ( $idea_ids as $idea_id ) {
			if ( 'wpis_archive' === $action ) {
				$status = get_post_status( $idea_id );
			} else {
				$status = get_post_meta( $idea_id, '_wp_idea_stream_original_status', true );

				// Validate the post status.
				if ( get_post_status_object( $status ) ) {
					$idea_status = $status;
				} else {
					$idea_status = 'private';
				}
			}

			if ( wp_update_post( array(
				'ID'          => $idea_id,
				'post_status' => $idea_status,
			) ) ) {
				$result[ $key ] += 1;

				if ( 'wpis_archive' === $action ) {
					update_post_meta( $idea_id, '_wp_idea_stream_original_status', $status );
				} else {
					delete_post_meta( $idea_id, '_wp_idea_stream_original_status' );
				}
			}
		}

		return add_query_arg( $result, admin_url( 'edit.php' ) );
	}

	/**
	 * Disable the quick edit row action
	 *
	 * @package WP Idea Stream
	 * @subpackage admin/admin
	 *
	 * @since 2.0.0
	 *
	 * @param  array  $actions list of available row actions
	 * @return array           the new list
	 */
	public function idea_row_actions( $actions = array(), $idea = null ) {
		if ( empty( $idea ) || $idea->post_type != $this->post_type ) {
			return $actions;
		}

		// No row actions in case downloading ideas
		if ( ! empty( $this->downloading_csv ) ) {
			return array();
		} elseif ( 'wpis_archive' === $this->current_view ) {
			return array(
				'unarchive' => sprintf( '<a href="%1$s">%2$s</a>',
					esc_url( add_query_arg( array(
						'post_type'      => wp_idea_stream_get_post_type(),
						'wpis_unarchive' => $idea->ID,
					), admin_url( 'edit.php' ) ) ),
					esc_html__( 'Unarchive', 'wp-idea-stream' )
				),
			);
		}

		/**
		 * I don't know yet if inline edit is well supported by the plugin, so if you
		 * want to test, just return true to this filter
		 * eg: add_filter( 'wp_idea_stream_admin_ideas_inline_edit', '__return_true' );
		 *
		 * @param  bool true to allow inline edit, false otherwise (default is false)
		 */
		$keep_inline_edit = apply_filters( 'wp_idea_stream_admin_ideas_inline_edit', false );

		if ( ! empty( $keep_inline_edit ) ) {
			return $actions;
		}

		if ( ! empty( $actions['inline hide-if-no-js'] ) ) {
			unset( $actions['inline hide-if-no-js'] );
		}

		return $actions;
	}

	/**
	 * Add new columns to the Ideas WP List Table
	 *
	 * @package WP Idea Stream
	 * @subpackage admin/admin
	 *
	 * @since 2.0.0
	 *
	 * @param  array  $columns the WP List Table columns
	 * @uses   wp_idea_stream_is_rating_disabled() to check if ratings are available
	 * @uses   apply_filters() call 'wp_idea_stream_admin_column_headers' to add/edit/remove IdeaStream columns
	 * @uses                   call 'wp_idea_stream_admin_csv_column_headers' to add/edit/remove IdeaStream csv columns
	 * @return array           the new columns
	 */
	public function column_headers( $columns = array() ) {
		$new_columns = array(
			'cat_ideas' => _x( 'Categories', 'ideas admin category column header', 'wp-idea-stream' ),
			'tag_ideas' => _x( 'Tags',      'ideas admin tag column header',       'wp-idea-stream' ),
		);

		if ( ! wp_idea_stream_is_rating_disabled() ) {
			$new_columns['rates'] = '<span class="vers"><span title="' . esc_attr__( 'Average Rating', 'wp-idea-stream' ) .'" class="idea-rating-bubble"></span></span>';
		}

		/**
		 * @see WP_Idea_Stream_Group->manage_columns_header() in buddypress/groups
		 * for an example of use
		 *
		 * @param array $new_columns the IdeaStream specific columns
		 */
		$new_columns = apply_filters( 'wp_idea_stream_admin_column_headers', $new_columns );

		$temp_remove_columns = array( 'comments', 'date' );
		$has_columns = array_intersect( $temp_remove_columns, array_keys( $columns ) );

		// Reorder
		if ( $has_columns == $temp_remove_columns ) {
			$new_columns['comments'] = $columns['comments'];
			$new_columns['date'] = $columns['date'];
			unset( $columns['comments'], $columns['date'] );
		}

		// Merge
		$columns = array_merge( $columns, $new_columns );


		if ( ! empty( $this->downloading_csv ) ) {
			unset( $columns['cb'], $columns['date'] );

			if ( ! empty( $columns['title'] ) ) {
				$csv_columns = array(
					'title'        => $columns['title'],
					'idea_content' => esc_html_x( 'Content', 'downloaded csv content header', 'wp-idea-stream' ),
					'idea_link'    => esc_html_x( 'Link', 'downloaded csv link header', 'wp-idea-stream' ),
				);
			}

			$columns = array_merge( $csv_columns, $columns );

			// Replace dashicons to text
			if ( ! empty( $columns['comments'] ) ) {
				$columns['comments'] = esc_html_x( '# comments', 'downloaded csv comments num header', 'wp-idea-stream' );
			}

			if ( ! empty( $columns['rates'] ) ) {
				$columns['rates'] = esc_html_x( 'Average rating', 'downloaded csv rates num header', 'wp-idea-stream' );
			}

			/**
			 * User this filter to only add columns for the downloaded csv file
			 *
			 * @param array $columns the IdeaStream csv specific columns
			 */
			$columns = apply_filters( 'wp_idea_stream_admin_csv_column_headers', $columns );
		}

		return $columns;
	}

	/**
	 * Fills the custom columns datarows
	 *
	 * @package WP Idea Stream
	 * @subpackage admin/admin
	 *
	 * @since 2.0.0
	 *
	 * @param  string $column_name the column name
	 * @param  int    $idea_id     the ID of the idea (row)
	 * @uses   wp_idea_stream_ideas_get_average_rating() to get the idea average rate
	 * @uses   wp_idea_stream_get_category() to get the ideas category taxonomy identifier
	 * @uses   wp_idea_stream_get_tag() to get the ideas tag taxonomy identifier
	 * @uses   get_taxonomy() to get the taxonomy object
	 * @uses   wp_get_object_terms() to get the terms the idea is associated with
	 * @uses   esc_html() to sanitize output
	 * @uses   esc_url() to sanitize url
	 * @uses   sanitize_term_field() to cleanse the field value in the term based on the context
	 * @uses   do_action() call 'wp_idea_stream_admin_column_data' to perform custom actions in case of custom columns
	 * @return string HTML output
	 */
	public function column_data( $column_name = '', $idea_id = 0 ) {
		switch( $column_name ) {
			case 'rates' :
				$rate = wp_idea_stream_ideas_get_average_rating( $idea_id );

				if ( ! empty( $rate ) ) {
					echo $rate;
				} else {
					echo '&#8212;';
				}
				break;

			case 'cat_ideas' :
			case 'tag_ideas' :
				if ( 'cat_ideas' == $column_name ) {
					$taxonomy = wp_idea_stream_get_category();
				} elseif ( 'tag_ideas' == $column_name ) {
					$taxonomy = wp_idea_stream_get_tag();
				} else {
					$taxonomy = false;
				}

				if ( empty( $taxonomy ) ) {
					return;
				}

				$taxonomy_object = get_taxonomy( $taxonomy );
				$terms = wp_get_object_terms( $idea_id, $taxonomy, array( 'fields' => 'all' ) );

				if ( empty( $terms ) ) {
					echo '&#8212;';
					return;
				}

				$output = array();
				foreach ( $terms as $term ) {
					$query_vars = array(
						'post_type'                 => $this->post_type,
						$taxonomy_object->query_var => $term->slug,
					);

					$out[] = sprintf( '<a href="%s">%s</a>',
						esc_url( add_query_arg( $query_vars, 'edit.php' ) ),
						esc_html( sanitize_term_field( 'name', $term->name, $term->term_id, $taxonomy, 'display' ) )
					);
				}

				echo join( __( ', ' ), $out );
				break;

			default:
				/**
				 * @param  string $column_name the column name
				 * @param  int    $idea_id     the ID of the idea (row)
				 */
				do_action( 'wp_idea_stream_admin_column_data', $column_name, $idea_id );
				break;
		}
	}

	/**
	 * Add extra info to downloaded csv file
	 *
	 * @package WP Idea Stream
	 * @subpackage admin/admin
	 *
	 * @since 2.1.0
	 *
	 * @param  string $column_name the column name
	 * @param  int    $idea_id     the ID of the idea (row)
	 * @uses   the_content() to display the idea content
	 * @uses   the_permalink() to display the permalink to the idea
	 * @return string HTML Output
	 */
	public function idea_row_extra_data( $column_name = '', $idea_id = '' ) {
		if ( 'idea_content' == $column_name ) {
			// Temporarly remove filters
			remove_filter( 'the_content', 'wptexturize'     );
			remove_filter( 'the_content', 'convert_smilies' );
			remove_filter( 'the_content', 'convert_chars'   );

			the_content();

			// Restore just in case
			add_filter( 'the_content', 'wptexturize'     );
			add_filter( 'the_content', 'convert_smilies' );
			add_filter( 'the_content', 'convert_chars'   );
		} else if ( 'idea_link' == $column_name ) {
			the_permalink();
		}
	}

	/**
	 * Gets the IdeaStream sortable columns
	 *
	 * @package WP Idea Stream
	 * @subpackage admin/admin
	 *
	 * @since 2.0.0
	 *
	 * @param  array  $sortable_columns the list of sortable columns
	 * @return array                   the new list
	 */
	public function sortable_columns( $sortable_columns = array() ) {
		// No sortable columns if downloading ideas
		if ( ! empty( $this->downloading_csv ) ) {
			return array();
		}

		$sortable_columns['rates'] = array( 'rates_count', true );

		return $sortable_columns;
	}

	/**
	 * Adds the list of ratings in a new metabox
	 *
	 * @package WP Idea Stream
	 * @subpackage admin/admin
	 *
	 * @since 2.0.0
	 *
	 * @param  array  $metaboxes list of IdeaStream metaboxes
	 * @return array             the new list
	 */
	public function ratings_metabox( $metaboxes = array() ) {
		$rating_metabox = array(
			'rates' => array(
				'id'            => 'wp_idea_stream_ratings_box',
				'title'         => _x( 'Rates', 'Ratings metabox title', 'wp-idea-stream' ),
				'callback'      => array( $this, 'ratings_do_metabox' ),
				'context'       => 'advanced',
				'priority'      => 'core'
		) );

		return array_merge( $metaboxes, $rating_metabox );
	}

	/**
	 * Displays the ratings metabox
	 *
	 * @package WP Idea Stream
	 * @subpackage admin/admin
	 *
	 * @since 2.0.0
	 *
	 * @param  WP_Post $idea the idea object
	 * @uses   wp_idea_stream_count_ratings() to get the rating stats for the idea
	 * @uses   get_edit_post_link() to get the idea edit link
	 * @uses   wp_idea_stream_get_hint_list() to get the captions for the rates
	 * @uses   esc_html() to sanitize output
	 * @uses   number_format_i18n() to format numbers
	 * @uses   wp_idea_stream_users_get_user_profile_url() to get a user's IdeaStream profile url
	 * @uses   esc_url() to sanitize url
	 * @uses   get_avatar() to get user's avatar
	 * @uses   wp_nonce_url() to add a security token to check upon once the link clicked
	 * @uses   add_query_arg() to add query vars to an url
	 * @return string HTML output
	 */
	public function ratings_do_metabox( $idea = null ) {
		$id = $idea->ID;

		$ratings_stats = wp_idea_stream_count_ratings( $id, 0, true );
		$users_count  = count( $ratings_stats['users'] );

		$edit_link = get_edit_post_link( $id );

		if ( empty( $users_count ) ) {
			esc_html_e( 'Not rated yet', 'wp-idea-stream' );
		} else {
			$hintlabels = wp_idea_stream_get_hint_list();
			$hintlist = array_keys( $hintlabels );
			?>
			<p class="description">
				<?php echo esc_html( sprintf( _n(
					'%1$s member rated the idea. Its Average rating is: %2$s',
					'%1$s members rated the idea. Its Average rating is: %2$s',
					$users_count,
					'wp-idea-stream'
				), number_format_i18n( $users_count ), number_format_i18n( $ratings_stats['average'], 1 ) ) ); ?>
			</p>
			<ul class="admin-idea-rates">
				<?php foreach ( $hintlist as $hintlabel ) :
					$hint = $hintlabel + 1;
				?>
				<li>
					<div class="admin-idea-rates-star"><?php echo esc_html( ucfirst( $hintlabels[ $hintlabel ] ) ); ?></div>
					<div class="admin-idea-rates-users">
						<?php if ( empty( $ratings_stats['details'][ $hint ] ) ) : ?>
							&#8212;
						<?php else :
							foreach ( $ratings_stats['details'][ $hint ] as $user_id ) : ?>
							<span class="user-rated">
								<a href="<?php echo esc_url( wp_idea_stream_users_get_user_profile_url( $user_id ) );?>"><?php echo get_avatar( $user_id, 40 ); ?></a>

								<?php $edit_user_link = wp_nonce_url( add_query_arg( 'remove_vote', $user_id, $edit_link ), 'idea_remove_vote_' . $user_id ); ?>

								<a href="<?php echo esc_url( $edit_user_link ); ?>" class="del-rate" title="<?php esc_attr_e( 'Delete this rating', 'wp-idea-stream' );?>" data-userid="<?php echo $user_id; ?>">
									<div class="dashicons dashicons-trash"></div>
								</a>
							</span>
						<?php endforeach; endif; ?>
					</div>
				</li>
				<?php endforeach; ?>
			</ul>
			<?php
		}
	}

	/**
	 * Checks if a rate is to be deleted
	 *
	 * @package WP Idea Stream
	 * @subpackage admin/admin
	 *
	 * @since 2.0.0
	 *
	 * @uses   wp_idea_stream_is_admin() to check if on an IdeaStream Administration screen
	 * @uses   wp_idea_stream_user_can() to check for user's capabilities
	 * @uses   check_admin_referer() to check the request was made on the site
	 * @uses   wp_idea_stream_delete_rate() to delete a rate
	 * @uses   add_query_arg() to add query vars to an url
	 * @uses   get_edit_post_link() to get the idea edit link
	 * @uses   wp_safe_redirect() to safely redirect the user
	 */
	public function maybe_delete_rate() {
		if ( ! wp_idea_stream_is_admin() ) {
			return;
		}

		if ( ! wp_idea_stream_user_can( 'edit_ideas' ) ) {
			return;
		}

		if ( empty( $_GET['remove_vote'] ) || empty( $_GET['post'] ) || empty( $_GET['action'] ) ) {
			return;
		}

		$idea_id = absint( $_GET['post'] );
		$user_id = absint( $_GET['remove_vote'] );

		// nonce check
		check_admin_referer( 'idea_remove_vote_' . $user_id );

		if( false !== wp_idea_stream_delete_rate( $idea_id, $user_id ) ) {
			$message = 11;
		} else {
			$message = 12;
		}

		// Utimate and not necessary check...
		if ( ! empty( $_GET['remove_vote'] ) ) {
			$redirect = add_query_arg( 'message', $message, get_edit_post_link( $idea_id, 'url' ) );
			wp_safe_redirect( $redirect );
			exit();
		}
	}

	/**
	 * Adds ratings specific updated messages
	 *
	 * @package WP Idea Stream
	 * @subpackage admin/admin
	 *
	 * @since 2.0.0
	 *
	 * @param  array  $messages IdeaStream list of updated messages
	 * @return array           new list
	 */
	public function ratings_updated( $messages = array() ) {

		$messages[11] = esc_html__( 'Rating successfully deleted', 'wp-idea-stream' );
		$messages[12] = esc_html__( 'Something went wrong while trying to delete the rating.', 'wp-idea-stream' );

		return $messages;
	}

	/**
	 * Forces the query to include all ideas
	 * Used to "feed" the downloaded csv spreadsheet
	 *
	 * @package WP Idea Stream
	 * @subpackage admin/admin
	 *
	 * @since 2.1.0
	 *
	 * @param  WP_Query $posts_query
	 */
	public function get_ideas_by_status( $posts_query = null ) {
		if ( ! empty( $posts_query->query_vars['posts_per_page'] ) ) {
			$posts_query->query_vars['posts_per_page'] = -1;
		}

		// Unset the post status if not registered
		if ( ! empty( $_GET['post_status'] ) && ! get_post_status_object( $_GET['post_status'] ) ) {
			unset( $posts_query->query_vars['post_status'] );
		}
	}

	/**
	 * Temporarly restrict all user caps to 2 idea caps
	 * This is to avoid get_inline_data() to add extra html in title column
	 *
	 * @package WP Idea Stream
	 * @subpackage admin/admin
	 *
	 * @since 2.1.0
	 *
	 * @param  array  $all_caps user's caps
	 * @return array            restricted user's caps
	 */
	public function filter_has_cap( $all_caps = array() ) {
		return array( 'read_private_ideas' => true, 'edit_others_ideas' => true );
	}

	/**
	 * Buffer ideas list and outputs an csv file
	 *
	 * @package WP Idea Stream
	 * @subpackage admin/admin
	 *
	 * @since 2.1.0
	 *
	 * @uses   remove_filter() to temporarly disable caps mapping
	 * @uses   add_filter() to override some key vars
	 * @uses   add_action() to perform custom actions at key points
	 * @uses   _get_list_table() to include a specific WP_List_Table class
	 * @uses   wp_kses() to only keep 'table' relative tags
	 * @return String text/csv
	 */
	public function csv_export() {
		// Strip edit inline extra html
		remove_filter( 'map_meta_cap', 'wp_idea_stream_map_meta_caps', 10, 4 );
		add_filter( 'user_has_cap', array( $this, 'filter_has_cap' ), 10, 1 );

		// Get all ideas
		add_action( 'wp_idea_stream_admin_request', array( $this, 'get_ideas_by_status' ), 10, 1 );

		$html_list_table = _get_list_table( 'WP_Posts_List_Table' );
		$html_list_table->prepare_items();
		ob_start();
		?>
		<table>
			<tr>
				<?php $html_list_table->print_column_headers(); ?>
			</tr>
				<?php $html_list_table->display_rows_or_placeholder(); ?>
			</tr>
		</table>
		<?php
		$output = ob_get_clean();

		// Keep only table tags
		$allowed_html = array(
			'table' => array(),
			'tbody' => array(),
			'td'    => array(),
			'th'    => array(),
			'tr'    => array()
		);

		$output = wp_kses( $output, $allowed_html );

		$comma = ',';

		// If some users are still using Microsoft ;)
		if ( preg_match( "/Windows/i", $_SERVER['HTTP_USER_AGENT'] ) ) {
			$comma = ';';
			$output = utf8_decode( $output );
		}

		// $output to csv
		$csv = array();
		preg_match( '/<table(>| [^>]*>)(.*?)<\/table( |>)/is', $output, $b );
		$table = $b[2];
		preg_match_all( '/<tr(>| [^>]*>)(.*?)<\/tr( |>)/is', $table, $b );
		$rows = $b[2];
		foreach ( $rows as $row ) {
			//cycle through each row
			if ( preg_match( '/<th(>| [^>]*>)(.*?)<\/th( |>)/is', $row ) ) {
				//match for table headers
				preg_match_all( '/<th(>| [^>]*>)(.*?)<\/th( |>)/is', $row, $b );
				$csv[] = '"' . implode( '"' . $comma . '"', array_map( 'wp_idea_stream_generate_csv_content', $b[2] ) ) . '"';
			} else if ( preg_match( '/<td(>| [^>]*>)(.*?)<\/td( |>)/is', $row ) ) {
				//match for table cells
				preg_match_all( '/<td(>| [^>]*>)(.*?)<\/td( |>)/is', $row, $b );
				$csv[] = '"' . implode( '"' . $comma . '"', array_map( 'wp_idea_stream_generate_csv_content', $b[2] ) ) . '"';
			}
		}

		$file = implode( "\n", $csv );

		status_header( 200 );
		header( 'Cache-Control: cache, must-revalidate' );
		header( 'Pragma: public' );
		header( 'Content-Description: File Transfer' );
		header( 'Content-Disposition: attachment; filename=' . sprintf( '%s-%s.csv', esc_attr_x( 'ideas', 'prefix of the downloaded csv', 'wp-idea-stream' ), date('Y-m-d-his' ) ) );
		header( 'Content-Type: text/csv;' );
		print( $file );
		exit();
	}

	/**
	 * Gets the help tabs for a given IdeaStream Administration screen
	 *
	 * @package WP Idea Stream
	 * @subpackage admin/admin
	 *
	 * @since 2.0.0
	 *
	 * @param  string  $screen_id the IdeaStream Administration screen ID
	 * @uses   apply_filters() call 'wp_idea_stream_get_help_tabs' to add/edit/remove help tabs
	 * @return array         the help tabs
	 */
	public function get_help_tabs( $screen_id = '' ) {
		// Help urls
		$plugin_forum         = '<a href="http://wordpress.org/support/plugin/wp-idea-stream">';
		$plugin_posts_archive = '<a href="http://imathi.eu/tag/ideastream/">';
		$help_tabs            = false;
		$nav_menu_page        = '<a href="' . esc_url( admin_url( 'nav-menus.php' ) ) . '">';
		$widgets_page         = '<a href="' . esc_url( admin_url( 'widgets.php' ) ) . '">';

		/**
		 * Used internally to add sticky/ratings/BuddyPress groups help tabs
		 * if enabled.
		 *
		 * @param array associative array to list the help tabs
		 */
		$help = array(
			'edit-ideas' => array(
				'add_help_tab'     => array(
					array(
						'id'      => 'edit-ideas-overview',
						'title'   => esc_html__( 'Overview', 'wp-idea-stream' ),
						'content' => array(
							esc_html__( 'This screen provides access to all the ideas users of your site shared. You can customize the display of this screen to suit your workflow.', 'wp-idea-stream' ),
							esc_html__( 'You can customize the display of this screen&#39;s contents in a number of ways:', 'wp-idea-stream' ),
							array(
								esc_html__( 'You can hide/display columns based on your needs and decide how many ideas to list per screen using the Screen Options tab.', 'wp-idea-stream' ),
								esc_html__( 'You can filter the list of ideas by post status using the text links in the upper left to show All, Published, Private or Trashed ideas. The default view is to show all ideas.', 'wp-idea-stream' ),
								esc_html__( 'You can view ideas in a simple title list or with an excerpt. Choose the view you prefer by clicking on the icons at the top of the list on the right.', 'wp-idea-stream' ),
							),
						),
					),
					array(
						'id'      => 'edit-ideas-row-actions',
						'title'   => esc_html__( 'Actions', 'wp-idea-stream' ),
						'content' => array(
							esc_html__( 'Hovering over a row in the ideas list will display action links that allow you to manage an idea. You can perform the following actions:', 'wp-idea-stream' ),
							array(
								esc_html__( 'Edit takes you to the editing screen for that idea. You can also reach that screen by clicking on the idea title.', 'wp-idea-stream' ),
								esc_html__( 'Trash removes the idea from this list and places it in the trash, from which you can permanently delete it.', 'wp-idea-stream' ),
								esc_html__( 'View opens the idea in the WP Idea Stream&#39;s part of your site.', 'wp-idea-stream' ),
							),
						),
					),
					array(
						'id'      => 'edit-ideas-bulk-actions',
						'title'   => esc_html__( 'Bulk Actions', 'wp-idea-stream' ),
						'content' => array(
							esc_html__( 'You can also move multiple ideas to the trash at once. Select the ideas you want to trash using the checkboxes, then select the &#34;Move to Trash&#34; action from the Bulk Actions menu and click Apply.', 'wp-idea-stream' ),
						),
					),
					array(
						'id'      => 'edit-ideas-sort-filter',
						'title'   => esc_html__( 'Sorting & filtering', 'wp-idea-stream' ),
						'content' => array(
							esc_html__( 'Clicking on specific column headers will sort the ideas list. You can sort the ideas alphabetically using the Title column header or by popularity:', 'wp-idea-stream' ),
							array(
								esc_html__( 'Click on the column header having a dialog buble icon to sort by number of comments.', 'wp-idea-stream' ),
								esc_html__( 'Click on the column header having a star icon to sort by rating.', 'wp-idea-stream' ),
							),
							esc_html__( 'Inside the rows, you can filter the ideas by categories or tags clicking on the corresponding terms.', 'wp-idea-stream' ),
						),
					),
				),
			),
			'ideas' => array(
				'add_help_tab' => array(
					array(
						'id'      => 'ideas-overview',
						'title'   => esc_html__( 'Overview', 'wp-idea-stream' ),
						'content' => array(
							esc_html__( 'The title field and the big Idea Editing Area are fixed in place, but you can reposition all the other boxes using drag and drop. You can also minimize or expand them by clicking the title bar of each box. Use the Screen Options tab to hide/show boxes.', 'wp-idea-stream' ),
						),
					),
				),
			),
			'settings_page_ideastream' => array(
				'add_help_tab' => array(
					array(
						'id'      => 'settings-overview',
						'title'   => esc_html__( 'Overview', 'wp-idea-stream' ),
						'content' => array(
							esc_html__( 'This is the place where you can customize the behavior of WP Idea Stream.', 'wp-idea-stream' ),
							esc_html__( 'Please see the additional help tabs for more information on each individual section.', 'wp-idea-stream' ),
						),
					),
					array(
						'id'      => 'settings-main',
						'title'   => esc_html__( 'Main Settings', 'wp-idea-stream' ),
						'content' => array(
							sprintf( esc_html__( 'Just before the first option, you will find the link to the main archive page of the plugin. If you wish, you can use it to define a new custom link %1$smenu item%2$s.', 'wp-idea-stream' ), $nav_menu_page, '</a>' ),
							sprintf( esc_html__( 'If you do so, do not forget to update the link in case you change your permalink settings. Another possible option is to use the %1$sWP Idea Stream Navigation%2$s widget in one of your dynamic sidebars.', 'wp-idea-stream' ), $widgets_page, '</a>' ),
							esc_html__( 'In the Main Settings you have a number of options:', 'wp-idea-stream' ),
							array(
								esc_html__( 'WP Idea Stream archive page: you can customize the title of this page. It will appear on every WP Idea Stream&#39;s page, except the single idea one.', 'wp-idea-stream' ),
								esc_html__( 'New ideas status: this is the default status to apply to the ideas submitted by the user. If this setting is set to &#34;Pending&#34;, it will be possible to edit the moderation message once this setting has been saved.', 'wp-idea-stream' ),
								esc_html__( 'Images & Links are settings about the WP Idea Stream&#39;s editor. If you wish to disable the image or link buttons, you can disable the corresponding setting.', 'wp-idea-stream' ),
								esc_html__( 'Featured images is a setting that requires the Editor Images button to be active. It allows your users to select an image they inserted and set it as the featured image for the idea. You must know this image will be uploaded inside your WordPress site.', 'wp-idea-stream' ),
								esc_html__( 'Moderation message: if New ideas status is defined to Pending, it is the place to customize the awaiting moderation message the user will see once he submited his idea.', 'wp-idea-stream' ),
								esc_html__( 'Not logged in message: if a user reaches the WP Idea Stream&#39;s front end submit form without being logged in, a message will invite him to do so. If you wish to use a custom message, use this setting.', 'wp-idea-stream' ),
								esc_html__( 'Rating stars hover captions: fill a comma separated list of captions to replace default one. On front end, the number of rating stars will depend on the number of comma separated captions you defined in this setting.', 'wp-idea-stream' ),
								esc_html__( 'Sticky ideas: choose whether to allow or not Administrators to stick ideas to the top of the WP Idea Stream&#39;s archive first page.', 'wp-idea-stream' ),
								esc_html__( 'Idea comments: if on, comments about ideas will be separated from other post types comments and you will be able to moderate comments about ideas from the comments submenu of the WP Idea Stream&#39;s main Administration menu. If you uncheck this setting, ideas comments will be mixed up with other post types comments into the main Comments Administration menu', 'wp-idea-stream' ),
								esc_html__( 'Comments: you can completely disable commenting about ideas by activating this option', 'wp-idea-stream' ),
								esc_html__( 'Embed profile: if this setting is active, your users profiles will include a sharing button to let your visitors copy the embed code and share it into their website.', 'wp-idea-stream' ),
							),
						),
					),
				),
			),
			'edit-category-ideas' => array(
				'add_help_tab' => array(
					array(
						'id'      => 'ideas-category-overview',
						'title'   => esc_html__( 'Overview', 'wp-idea-stream' ),
						'content' => array(
							esc_html__( 'Idea Categories can only be created by the site Administrator. To add a new idea category please fill the following fields:', 'wp-idea-stream' ),
							array(
								esc_html__( 'Name - The name is how it appears on your site (in the category checkboxes of the idea front end submit form, in the idea&#39;s footer part or in the title of WP Idea Stream&#39;s category archive pages).', 'wp-idea-stream' ),
								esc_html__( 'Slug - The &#34;slug&#34; is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens.', 'wp-idea-stream' ),
								esc_html__( 'Description - If you set a description for your category, it will be displayed over the list of ideas in the category archive page.', 'wp-idea-stream' ),
							),
							esc_html__( 'You can change the display of this screen using the Screen Options tab to set how many items are displayed per screen and to display/hide columns in the table.', 'wp-idea-stream' ),
						),
					),
				),
			),
			'edit-tag-ideas' => array(
				'add_help_tab' => array(
					array(
						'id'      => 'ideas-tag-overview',
						'title'   => esc_html__( 'Overview', 'wp-idea-stream' ),
						'content' => array(
							esc_html__( 'Idea Tags can be created by any logged in user of the site from the idea front end submit form. From this screen, to add a new idea tag please fill the following fields:', 'wp-idea-stream' ),
							array(
								esc_html__( 'Name - The name is how it appears on your site (in the tag cloud, in the tags editor of the idea front end submit form, in the idea&#39;s footer part or in the title of WP Idea Stream&#39;s tag archive pages).', 'wp-idea-stream' ),
								esc_html__( 'Slug - The &#34;slug&#34; is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens.', 'wp-idea-stream' ),
								esc_html__( 'Description - If you set a description for your tag, it will be displayed over the list of ideas in the tag archive page.', 'wp-idea-stream' ),
							),
							esc_html__( 'You can change the display of this screen using the Screen Options tab to set how many items are displayed per screen and to display/hide columns in the table.', 'wp-idea-stream' ),
						),
					),
				),
			),
		);

		if ( wp_idea_stream_is_pretty_links() ) {
			$help['settings_page_ideastream']['add_help_tab'][] = array(
				'id'      => 'settings-slugs',
				'title'   => esc_html__( 'Pretty Links', 'wp-idea-stream' ),
				'content' => array(
					esc_html__( 'The Pretty Links section allows you to control the permalink structure of the plugin by defining custom slugs.', 'wp-idea-stream' ),
					esc_html__( 'The WP Idea Stream root slug is the most important one. Make sure the slug you chose is unique. Once saved, WP Idea Stream will check for an eventual slug collision with WordPress (Posts, Pages or subsites in case of a MultiSite Config), bbPress or BuddyPress, and will display a warning next to the option field.', 'wp-idea-stream' ),
					esc_html__( 'In the case of a slug collision, I strongly advise you to change the WP Idea Stream root slug.', 'wp-idea-stream' ),
					esc_html__( 'Concerning the text you will enter in the slug fields, make sure it is all lowercase and contains only letters, numbers, and hyphens.', 'wp-idea-stream' ),
				),
			);
		}

		/**
		 * Used internally to add sticky/ratings/BuddyPress groups help tabs
		 * if enabled.
		 *
		 * @param array $help associative array to list the help tabs
		 */
		$help = apply_filters( 'wp_idea_stream_get_help_tabs', $help );

		if ( ! empty( $help[ $screen_id ] ) ) {
			$help_tabs = array_merge( $help[ $screen_id ], array(
				'set_help_sidebar' => array(
					array(
						'strong'   => esc_html__( 'For more information:', 'wp-idea-stream' ),
						'content' => array(
							sprintf( esc_html_x( '%1$sSupport Forums (en)%2$s', 'help tab links', 'wp-idea-stream'   ), $plugin_forum,          '</a>' ),
							sprintf( esc_html_x( '%1$sWP Idea Stream posts (fr)%2$s', 'help tab links', 'wp-idea-stream' ), $plugin_posts_archive,  '</a>' ),
						),
					),
				),
			) );
		}

		return $help_tabs;
	}

	/**
	 * Adds the Ratings help tabs
	 *
	 * @package WP Idea Stream
	 * @subpackage admin/admin
	 *
	 * @since 2.0.0
	 *
	 * @param  array  $help_tabs the list of help tabs
	 * @return array            the new list of help tabs
	 */
	public function rates_help_tabs( $help_tabs = array() ) {
		if ( ! empty( $help_tabs['ideas']['add_help_tab'] ) ) {
			$ideas_help_tabs = wp_list_pluck( $help_tabs['ideas']['add_help_tab'], 'id' );
			$ideas_overview = array_search( 'ideas-overview', $ideas_help_tabs );

			if ( isset( $help_tabs['ideas']['add_help_tab'][ $ideas_overview ]['content'] ) ) {
				$help_tabs['ideas']['add_help_tab'][ $ideas_overview ]['content'][] = esc_html__( 'The Ratings metabox allows you to manage the ratings the idea has received.', 'wp-idea-stream' );
			}
		}

		return $help_tabs;
	}

	/**
	 * Remove some submenus and add some custom styles
	 *
	 * @package WP Idea Stream
	 * @subpackage admin/admin
	 *
	 * @since 2.0.0
	 *
	 * @uses   remove_submenu_page() to remove a page to admin menu
	 * @uses   wp_idea_stream_is_admin() to check if on an IdeaStream Administration screen
	 * @uses   wp_idea_stream_is_rating_disabled() to check if ratings are enabled
	 * @return string CSS output
	 */
	public function admin_head() {
 		// Remove the fake Settings submenu
 		remove_submenu_page( 'options-general.php', 'ideastream' );

 		// Remove the About & credits pages from menu
 		remove_submenu_page( 'index.php', 'about-ideastream' );
 		remove_submenu_page( 'index.php', 'credits-ideastream' );

 		//Generate help if one is available for the current screen
 		if ( wp_idea_stream_is_admin() || ! empty( $this->is_plugin_settings ) ) {

 			$screen = get_current_screen();

 			if ( ! empty( $screen->id ) && ! $screen->get_help_tabs() ) {
 				$help_tabs_list = $this->get_help_tabs( $screen->id );

 				if ( ! empty( $help_tabs_list ) ) {
 					// Loop through tabs
 					foreach ( $help_tabs_list as $key => $help_tabs ) {
 						// Make sure types are a screen method
 						if ( ! in_array( $key, array( 'add_help_tab', 'set_help_sidebar' ) ) ) {
 							continue;
 						}

 						foreach ( $help_tabs as $help_tab ) {
 							$content = '';

 							if ( empty( $help_tab['content'] ) || ! is_array( $help_tab['content'] ) ) {
 								continue;
 							}

 							if ( ! empty( $help_tab['strong'] ) ) {
 								$content .= '<p><strong>' . $help_tab['strong'] . '</strong></p>';
 							}

 							foreach ( $help_tab['content'] as $tab_content ) {
								if ( is_array( $tab_content ) ) {
									$content .= '<ul><li>' . join( '</li><li>', $tab_content ) . '</li></ul>';
								} else {
									$content .= '<p>' . $tab_content . '</p>';
								}
							}

							$help_tab['content'] = $content;

 							if ( 'add_help_tab' == $key ) {
 								$screen->add_help_tab( $help_tab );
 							} else {
 								$screen->set_help_sidebar( $content );
 							}
 						}
 					}
 				}
 			}
 		}

 		// Add some css
		?>

		<style type="text/css" media="screen">
		/*<![CDATA[*/

			/* Bubble style for Main Post type menu */
			#adminmenu .wp-menu-open.menu-icon-<?php echo $this->post_type?> .awaiting-mod {
				background-color: #2ea2cc;
				color: #fff;
			}

			.about-wrap .wp-idea-stream-badge {
				font: normal 150px/1 'dashicons' !important;
				/* Better Font Rendering =========== */
				-webkit-font-smoothing: antialiased;
				-moz-osx-font-smoothing: grayscale;

				color: #000;
				display: inline-block;
				content:'';
			}

			.wp-idea-stream-badge:before{
				content: "\f339";
			}

			.about-wrap .wp-idea-stream-badge {
				position: absolute;
				top: 0;
				right: 0;
			}
				body.rtl .about-wrap .wp-idea-stream-badge {
					right: auto;
					left: 0;
				}

			.ideastream-credits {
				position:relative;
				float:left;
				margin-right:15px;
			}

			.ideastream-credits img.gravatar {
				width:150px;
				height:150px;
			}

			.dashboard_page_credits-ideastream .changelog {
				clear:both;
				overflow: hidden;
			}

			#wp-idea-stream-csv span.dashicons-media-spreadsheet {
				vertical-align: text-bottom;
			}

			<?php if ( wp_idea_stream_is_admin() && ! wp_idea_stream_is_rating_disabled() ) : ?>
				/* Rating stars in screen options and in ideas WP List Table */
				.metabox-prefs .idea-rating-bubble:before,
				th .idea-rating-bubble:before,
				.metabox-prefs .idea-group-bubble:before,
				th .idea-group-bubble:before {
					font: normal 20px/.5 'dashicons';
					speak: none;
					display: inline-block;
					padding: 0;
					top: 4px;
					left: -4px;
					position: relative;
					vertical-align: top;
					-webkit-font-smoothing: antialiased;
					-moz-osx-font-smoothing: grayscale;
					text-decoration: none !important;
					color: #444;
				}

				th .idea-rating-bubble:before,
				.metabox-prefs .idea-rating-bubble:before {
					content: '\f155';
				}

				.metabox-prefs .idea-group-bubble:before,
				th .idea-group-bubble:before {
					content: '\f307';
				}

				.metabox-prefs .idea-rating-bubble:before,
				.metabox-prefs .idea-group-bubble:before {
					vertical-align: baseline;
				}

				/* Rates management */
				#wp_idea_stream_ratings_box ul.admin-idea-rates {
					width: 100%;
					list-style: none;
					clear: both;
					margin: 0;
					padding: 0;
				}

				#wp_idea_stream_ratings_box ul.admin-idea-rates li {
					list-style: none;
					overflow: hidden;
					position: relative;
					padding:15px 0;
					border-bottom:dotted 1px #ccc;
				}

				#wp_idea_stream_ratings_box ul.admin-idea-rates li:last-child {
					border:none;
				}

				#wp_idea_stream_ratings_box ul.admin-idea-rates li div.admin-idea-rates-star {
					float:left;
				}

				#wp_idea_stream_ratings_box ul.admin-idea-rates li div.admin-idea-rates-star {
					width:20%;
					font-weight: bold;
				}

				#wp_idea_stream_ratings_box ul.admin-idea-rates li div.admin-idea-rates-users {
					margin-left: 20%;
				}

				#wp_idea_stream_ratings_box ul.admin-idea-rates li div.admin-idea-rates-users span.user-rated {
					display:inline-block;
					margin:5px;
					padding:5px;
					-webkit-box-shadow: 0 1px 1px 1px rgba(0,0,0,0.1);
					box-shadow: 0 1px 1px 1px rgba(0,0,0,0.1);
				}

				#wp_idea_stream_ratings_box ul.admin-idea-rates li div.admin-idea-rates-users a.del-rate {
					text-decoration: none;
				}

				#wp_idea_stream_ratings_box ul.admin-idea-rates li div.admin-idea-rates-users a.del-rate div {
					vertical-align: baseline;
				}
			<?php endif; ?>

		/*]]>*/
		</style>
		<?php
	}

	/**
	 * Modifies the links in plugins table
	 *
	 * @package WP Idea Stream
	 * @subpackage admin/admin
	 *
	 * @since 2.0.0
	 *
	 * @param  array $links the existing links
	 * @param  string $file  the file of plugins
	 * @uses   wp_idea_stream_get_basename() to get the IdeaStream plugin's Basename
	 * @uses   add_query_arg() to add args to the link
	 * @uses   admin_url() to build the new links
	 * @return array  the existing links + the new ones
	 */
	public function modify_plugin_action_links( $links, $file ) {

		// Return normal links if not BuddyPress
		if ( wp_idea_stream_get_basename() != $file ) {
			return $links;
		}

		// Add a few links to the existing links array
		return array_merge( $links, array(
			'settings' => '<a href="' . esc_url( add_query_arg( 'page', 'ideastream', admin_url( 'options-general.php' ) ) ) . '">' . esc_html__( 'Settings', 'wp-idea-stream' ) . '</a>',
			'about'    => '<a href="' . esc_url( add_query_arg( 'page', 'about-ideastream', admin_url( 'index.php'     ) ) ) . '">' . esc_html__( 'About',    'wp-idea-stream' ) . '</a>'
		) );
	}
}

endif;

add_action( 'wp_idea_stream_loaded', array( 'WP_Idea_Stream_Admin', 'start' ), 5 );
