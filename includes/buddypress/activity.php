<?php
/**
 * WP Idea Stream BuddyPress integration : activity.
 *
 * BuddyPress / activity
 *
 * @package WP Idea Stream
 * @subpackage buddypress/activity
 *
 * @since 2.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WP_Idea_Stream_Activity' ) ) :
/**
 * Main Activity class
 *
 * We will mainly use BuddyPress built-in mechanism to create activities out of regular
 * posts and comments, modifying it for our need in order to create activity types that
 * can be shared by 2 components 'blogs' & 'groups'.
 *
 * This also means, that activities will be generated if the blogs and activity component
 * are activated
 *
 * Then we will handle ideas status transition, in a different way than BuddyPress
 * - Private ideas need to benefit from activities (case of Private or Hidden groups)
 * - Password protected ideas won't generate any activities
 * - When an activity about an idea is trashed, we need to also delete the activities about
 *   the corresponding comments
 *
 * About comments, we will completely override BuddyPress if there are about the ideas
 * post type
 *
 * @package WP Idea Stream
 * @subpackage buddypress/activity
 *
 * @since 2.0.0
 */
class WP_Idea_Stream_Activity {

	/**
	 * The constructor
	 *
	 * @package WP Idea Stream
	 * @subpackage buddypress/activity
	 *
	 * @since 2.0.0
	 */
	public function __construct() {
		$this->setup_globals();
		$this->setup_actions();
		$this->setup_filters();
	}

	/**
	 * Starts the activity class
	 *
	 * @package WP Idea Stream
	 * @subpackage buddypress/activity
	 *
	 * @since 2.0.0
	 *
	 * @uses  buddypress() to get BuddyPress main instance
	 */
	public static function manage_activities() {
		$ideastream = buddypress()->ideastream;

		if ( empty( $ideastream->activities ) ) {
			$ideastream->activities = new self;
		}

		return $ideastream->activities;
	}

	/**
	 * Set some globals
	 *
	 * @package WP Idea Stream
	 * @subpackage buddypress/activity
	 *
	 * @since 2.0.0
	 *
	 * @uses   wp_idea_stream_get_post_type() to get ideas post type identifier
	 * @uses   get_post_type_object() to get the post type object (essentially labels)
	 */
	public function setup_globals() {
		/**
		 * The ideas post type identifier, called at many times throughout the class
		 *
		 * @var string
		 */
		$this->post_type          = wp_idea_stream_get_post_type();

		/**
		 * Used to get the post type labels
		 *
		 * @var object
		 */
		$this->post_type_object   = get_post_type_object( $this->post_type );

		/**
		 * Registers the activity actions for IdeaStream, used to populate
		 * BuddyPress dropdown filters and to build the action strings when
		 * an activity is generated.
		 *
		 * @var array
		 */
		$this->activity_actions   = array();

		/**
		 * Used to catch the idea object in order to use it when generating activities
		 *
		 * @var mixed (boolean/WP_Post) false at init, and can be populated with an idea
		 */
		$this->idea               = false;

		/**
		 * Used to catch the idea ID or comment ID in order to use it when generating activities
		 *
		 * @var mixed (boolean/int) false at init, and can be populated with an idea ID or a comment ID
		 */
		$this->secondary_item_id  = false;

		/**
		 * Used to catch pubic activities to avoid requesting too many times the same activities
		 *
		 * @var array
		 */
		$this->edit_activities    = array();

		/**
		 * Used to catch private activities to avoid requesting too many times the same activities
		 *
		 * @var array
		 */
		$this->private_activities = array();
	}

	/**
	 * Set some actions to extend BuddyPress activities to our need
	 *
	 * @package WP Idea Stream
	 * @subpackage buddypress/activity
	 *
	 * @since 2.0.0
	 *
	 * @uses   add_action() to perform custom actions at key points
	 */
	public function setup_actions() {
		// First register plugin's activities
		add_action( 'bp_register_activity_actions', array( $this, 'register_activity_actions' ), 11 );

		// Intercept the activity just before its creation
		add_action( 'bp_activity_before_save', array( $this, 'adjust_activity_args' ), 10, 1 );

		// Manage activity visibility or delete some if needed
		add_action( "private_{$this->post_type}",                  array( $this, 'mark_activity_private' ),  10, 2 );
		add_action( "publish_{$this->post_type}",                  array( $this, 'maybe_delete_activity' ),  10, 2 );
		add_action( "trash_{$this->post_type}",                    array( $this, 'maybe_delete_activity' ),  10, 2 );
		add_action( "draft_{$this->post_type}",                    array( $this, 'maybe_delete_activity' ),  10, 2 );
		add_action( "pending_{$this->post_type}",                  array( $this, 'maybe_delete_activity' ),  10, 2 );
		add_action( 'wp_idea_stream_buddypress_group_changed',     array( $this, 'change_item_id' ),         10, 4 );
		add_action( 'wp_idea_stream_buddypress_remove_from_group', array( $this, 'reset_activity_item_id' ), 10, 3 );

		// Manage comment transition, before BuddyPress
		add_action( 'comment_post',              array( $this, 'allow_private_comments'  ), 9, 2 );
		add_action( 'edit_comment',              array( $this, 'allow_private_comments'  ), 9    );
		add_action( 'transition_comment_status', array( $this, 'manage_comment_activity' ), 9, 3 );

		// Make sure spammed/deleted user has no activities
		add_action( 'wp_idea_stream_users_deleted_user_data', array( $this, 'user_deleted_activities' ), 10, 1 );
	}

	/**
	 * Set some filters to override some BuddyPress vars to our need
	 *
	 * @package WP Idea Stream
	 * @subpackage buddypress/activity
	 *
	 * @since 2.0.0
	 *
	 * @uses   add_filter() to override some key vars
	 */
	public function setup_filters() {
		// Fake post new post types action to populate the dropdowns
		add_filter( 'bp_activity_get_post_types_tracking_args', array( $this, 'dropdown_filters' ), 10, 1 );

		// Intercept the post/comment to see if it's an idea/comment about an idea
		add_filter( "bp_activity_{$this->post_type}_pre_publish", array( $this, 'catch_idea'         ), 10, 4 );
		add_filter( "bp_activity_{$this->post_type}_pre_comment", array( $this, 'catch_idea_comment' ), 10, 5 );

		// Make sure the comment will generate an activity
		add_filter( 'bp_disable_blogforum_comments', array( $this, 'force_activity_add' ), 10, 1 );

		// Activity comments about ideas/idea comments are not allowed
		add_filter( 'bp_activity_can_comment',               array( $this, 'activity_no_comment' ),       10, 2 );
		add_filter( 'bp_activity_admin_comment_row_actions', array( $this, 'activity_admin_no_comment' ), 10, 2 );

		// Map activity permalink to object permalink
		add_filter( 'bp_activity_get_permalink', array( $this, 'activity_permalink' ), 10, 2 );

		// Groups Activity Repair tools
		add_filter( 'bp_repair_list', array( $this, 'register_repair_tool' ), 10, 1 );
	}

	/**
	 * Registers new activity actions for IdeaStream
	 *
	 * By default, IdeaStream activity actions will be attached
	 * to the blogs component.
	 *
	 * @package WP Idea Stream
	 * @subpackage buddypress/activity
	 *
	 * @since 2.0.0
	 *
	 * @uses  buddypress() to get BuddyPress main instance
	 * @uses  add_post_type_support() to add the ideas post type to BuddyPress activities
	 * @uses  bp_activity_set_post_type_tracking_arg() to register the plugin's activity actions
	 * @uses  apply_filters() call 'wp_idea_stream_buddypress_get_activity_actions' to override/add activity actions
	 */
	public function register_activity_actions() {
		/**
		 * Used internally to add the IdeaStream Groups activities
		 * @see WP_Idea_Stream_Group::group_activity_context()
		 *
		 * @param array the list of activity actions
		 */
		$this->activity_actions = apply_filters( 'wp_idea_stream_buddypress_get_activity_actions', array(
			'new_' . $this->post_type => (object) array(
				'component'         => buddypress()->blogs->id,
				'type'              => 'new_' . $this->post_type,
				'admin_caption'     => sprintf( _x( 'New %s published', 'activity admin dropdown caption', 'wp-idea-stream' ), mb_strtolower( $this->post_type_object->labels->singular_name, 'UTF-8' ) ),
				'action_callback'   => array( $this, 'format_idea_activity_action' ),
				'front_caption'     => sprintf( _x( '%s', 'activity front dropdown caption', 'wp-idea-stream' ), $this->post_type_object->labels->name ),
				'contexts'          => array( 'activity', 'member' ),
			),
			'new_' . $this->post_type . '_comment' => (object) array(
				'component'         => buddypress()->blogs->id,
				'type'              => 'new_' . $this->post_type . '_comment',
				'admin_caption'     => sprintf( _x( 'New %s comment posted', 'activity comment admin dropdown caption', 'wp-idea-stream' ), mb_strtolower( $this->post_type_object->labels->singular_name, 'UTF-8' ) ),
				'action_callback'   => array( $this, 'format_idea_comment_activity_action' ),
				'front_caption'     => sprintf( _x( '%s comments', 'activity comments front dropdown caption', 'wp-idea-stream' ), $this->post_type_object->labels->singular_name ),
				'contexts'          => array( 'activity', 'member' ),
			),
		) );

		// Only add the new idea to BuddyPress tracked post types
		add_post_type_support( $this->post_type, 'buddypress-activity' );

		$tracking_args     = $this->activity_actions['new_' . $this->post_type];
		$comments_tracking = $this->activity_actions['new_' . $this->post_type . '_comment'];

		bp_activity_set_post_type_tracking_args( $this->post_type, array(
			'component_id'                      => $tracking_args->component,
			'action_id'                         => $tracking_args->type,
			'bp_activity_admin_filter'          => $tracking_args->admin_caption,
			'bp_activity_front_filter'          => $tracking_args->front_caption,
			'contexts'                          => $tracking_args->contexts,
			'activity_comment'                  => false,
			'format_callback'                   => $tracking_args->action_callback,
			'position'                          => 50,
			'comment_action_id'                 => $comments_tracking->type,
			'comment_format_callback'           => $comments_tracking->action_callback,
			'bp_activity_comments_admin_filter' => $comments_tracking->admin_caption,
			'bp_activity_comments_front_filter' => $comments_tracking->front_caption,
		) );
	}

	/**
	 * Fake post type activities in order to add custom options
	 * to BuddyPress activity dropdowns
	 *
	 * Make sure the Groups/blogs activity actions appears as one component > IdeaStream
	 * in the Activity Administration screen.
	 *
	 * @package WP Idea Stream
	 * @subpackage buddypress/activity
	 *
	 * @since 2.1.0
	 *
	 * @uses    buddypress() to get BuddyPress main instance
	 * @return  array        new tracking args
	 */
	public function dropdown_filters( $tracking_args ) {
		if ( ! isset( $tracking_args[ 'new_' . $this->post_type ] ) ) {
			return $tracking_args;
		}

		if ( is_admin() && function_exists( 'get_current_screen' ) ) {
			$current_screen = get_current_screen();

			if ( ! empty( $current_screen->id ) && strpos( 'toplevel_page_bp-activity', $current_screen->id ) !== false ) {
				$component =  buddypress()->ideastream->id;
			}
		}

		if ( empty( $component ) && ! bp_is_activity_directory() && ! bp_is_user_activity() && ! bp_is_group_activity() ) {
			return $tracking_args;
		}

		$position = 50;

		foreach ( $this->activity_actions as $key_action => $action ) {
			if ( 'new_' . $this->post_type == $key_action ) {
				if ( ! empty( $component ) ) {
					$tracking_args[ $key_action ]->component_id = $component;
				}

				continue;
			}

			$position += 1;

			$tracking_args[ $key_action ] = new stdClass();
			$tracking_args[ $key_action ]->component_id     = ! empty( $component ) ? $component : $action->component;
			$tracking_args[ $key_action ]->action_id        = $action->type;
			$tracking_args[ $key_action ]->admin_filter     = $action->admin_caption;
			$tracking_args[ $key_action ]->front_filter     = $action->front_caption;
			$tracking_args[ $key_action ]->contexts         = $action->contexts;
			$tracking_args[ $key_action ]->activity_comment = false;
			$tracking_args[ $key_action ]->format_callback  = $action->action_callback;
			$tracking_args[ $key_action ]->position         = $position;
		}

		/**
		 * Used internally to disallow activity dropdowns on groups not supporting ideastream
		 */
		return apply_filters( 'wp_idea_stream_buddypress_activity_filters', $tracking_args );
	}

	/** Adding activities *********************************************************/

	/**
	 * The BuddyPress blogs component is only registering blog posts or blog comments
	 * Our steps will be to :
	 * 1/ Use Post types Activities for post and use the 'bp_blogs_record_comment_post_types' filter to include our post type
	 * 2/ then catch the idea using the post_id/comment object included as a param to 'bp_activity_{$this->post_type}_pre_publish' or
	 *    'bp_blogs_activity_new_comment_content' filters (in this second case we'll take advantage of the fact that BuddyPress
	 *    is appending the post object to the comment one)
	 * 3/ we'll use the action 'bp_activity_before_save' to adjust the activity to fit our need.
	 * 4/ the actions will be set thanks to the 'action_callback' argument of our activity actions
	 * 5/ For the particular case of comments, we'll force BuddyPress to add an activity instead of an activity comment even if
	 * the synchronisation mechanism between blog comments & activity comments is on.
	 */

	/**
	 * 2/ Catch the idea in order to use it just before the activity is saved
	 * @see  $this->adjust_activity_args();
	 *
	 * @package WP Idea Stream
	 * @subpackage buddypress/activity
	 *
	 * @since 2.1.0
	 *
	 * @param  bool $true           whether to stop BuddyPress attempt to save an activity for the post type
	 *                              by returning false
	 * @param  int  $blog_id        the current blog id
	 * @param  int  $post_id        the ID of the idea
	 * @param  int  $user_id        the ID of the user
	 * @uses   get_post()           to get the post
	 * @return bool                 true
	 */
	public function catch_idea( $true = true, $blog_id = 0, $post_id = 0, $user_id = 0 ) {
		if ( ! empty( $post_id ) ) {
			$this->idea              = get_post( $post_id );
			$this->secondary_item_id = $post_id;
		}

		return $true;
	}

	/**
	 * 2/ Catch the idea in order to use it just before the activity is saved
	 * @see  $this->adjust_activity_args();
	 *
	 * We won't change anything to the $activity_content.
	 *
	 * @package WP Idea Stream
	 * @subpackage buddypress/activity
	 *
	 * @since 2.1.0
	 *
	 * @param  string  $activity_content the excerpt created by BuddyPress
	 * @param  WP_Post $post             the post object (can be an idea)
	 * @param  string  $post_permalink   the permalink to this object
	 * @uses   bp_activity_get_activity_id() to get an activity id
	 * @uses   bp_activity_delete()      to delete an activity
	 * @return string                    the activity content unchanged
	 */
	public function catch_idea_comment( $retval = true, $blog_id, $post_id, $user_id, $comment_id ) {
		// First remove the filter if needed!
		if ( ! empty( $this->allowed_private_comment ) ) {
			unset( $this->allowed_private_comment );
			remove_filter( 'bp_activity_post_type_is_post_status_allowed', '__return_false' );
		}

		if ( empty( $post_id ) || empty( $comment_id ) ) {
			return $retval;
		}

		$this->idea              = get_post( $post_id );
		$this->secondary_item_id = $comment_id;

		/**
		 * For this particular case, we need to check for a previous entry to delete it
		 * As a the 'edit_comment' hook will be fired if the comment was edited from the
		 * 'wp-admin/comment.php?action=editcomment..' screen, bp_blogs_record_comment()
		 * will not find the new_blog_comment activity to edit and will post a new one
		 */
		$id = bp_activity_get_activity_id( array(
			'type'              => 'new_' . $this->post_type . '_comment',
			'secondary_item_id' => $this->secondary_item_id,
		) );

		// Found one, delete it to prevent duplicates
		if ( ! empty( $id ) ) {
			bp_activity_delete( array( 'id' => $id ) );
		}

		// return without editing.
		return $retval;
	}

	/**
	 * 3/ Gets the activity before it is saved and adjusts his arguments to match our need
	 *
	 * @package WP Idea Stream
	 * @subpackage buddypress/activity
	 *
	 * @since 2.0.0
	 *
	 * @param  BP_Activity_Activity $activity the activity object before being saved
	 * @uses   apply_filters() call 'wp_idea_stream_buddypress_pre_adjust_activity' to early override the activity object
	 *                         call 'wp_idea_stream_buddypress_adjust_activity' to override the activity object
	 * @uses   bp_activity_set_action() to define the post type activity action if not set
	 * @uses   bp_activity_generate_action_string() to generate the action strings using the 'action_callback'
	 *                                              of our activity actions
	 * @return BP_Activity_Activity           the activity to be saved
	 */
	public function adjust_activity_args( $activity = null ) {
		// Bail if not an idea
		if ( empty( $this->secondary_item_id ) || $this->secondary_item_id != $activity->secondary_item_id ) {
			return;
		}

		$bp = buddypress();
		$bp_activity_actions = new stdClass();
		$bp_activity_actions = $bp->activity->actions;

		/**
		 * Used internally to override ideas posted within a group
		 *
		 * @param BP_Activity_Activity $activity   the activity object
		 * @param WP_Post              $this->idea the idea object
		 */
		$activity = apply_filters( 'wp_idea_stream_buddypress_pre_adjust_activity', $activity, $this->idea );

		// Define the corresponding index
		$activity_type = $activity->type;

		// override the activity type
		if ( ! empty( $this->activity_actions[ $activity_type ] ) ) {
			$activity->type   = $this->activity_actions[ $activity_type ]->type;
		}

		if ( empty( $bp->activity->actions->{$activity->component}->{$activity->type}['format_callback'] ) ) {
			bp_activity_set_action(
				$this->activity_actions[ $activity_type ]->component,
				$this->activity_actions[ $activity_type ]->type,
				$this->activity_actions[ $activity_type ]->admin_caption,
				$this->activity_actions[ $activity_type ]->action_callback
			);
		}

		// Regenerate the action string
		$activity->action = bp_activity_generate_action_string( $activity );

		/**
		 * Make sure the visibility status respect the idea post status
		 * So far BuddyPress :
		 * - is not generating an activity for private posts
		 * - is generating an activity for comments made on a private post
		 */
		if ( 'publish' !== $this->idea->post_status ) {
			$activity->hide_sitewide = true;
		}

		/**
		 * @param BP_Activity_Activity $activity   the activity object
		 */
		$activity = apply_filters( 'wp_idea_stream_buddypress_adjust_activity', $activity );

		// Reset BuddyPress activity actions
		$bp->activity->actions = $bp_activity_actions;
	}

	/**
	 * 4-Ideas/ Format the activity actions about a posted idea
	 *
	 * @package WP Idea Stream
	 * @subpackage buddypress/activity
	 *
	 * @since 2.0.0
	 *
	 * @param  string               $action   the action string
	 * @param  BP_Activity_Activity $activity the activity object
	 * @uses   bp_core_get_userlink() to get user's profile url
	 * @uses   esc_url() to sanitize the primary link (unchanged)
	 * @uses   esc_html() to sanitize the custom part of the action
	 * @uses   apply_filters() call 'wp_idea_stream_buddypress_format_idea_activity_action' to override the idea action strings
	 * @return string                         the action adjusted for an idea if needed
	 */
	public function format_idea_activity_action( $action = '', $activity = null ) {
		if ( ! is_object( $activity ) ) {
			return false;
		}

		$action = sprintf(
			_x( '%1$s wrote a new %2$s', 'idea posted activity action', 'wp-idea-stream' ),
			bp_core_get_userlink( $activity->user_id ),
			'<a href="' . esc_url( $activity->primary_link ) . '">' . esc_html( mb_strtolower( $this->post_type_object->labels->singular_name, 'UTF-8' ) ) . '</a>'
		);

		return apply_filters( 'wp_idea_stream_buddypress_format_idea_activity_action', $action );
	}

	/**
	 * 4-Comments/ Format the activity actions in case a comment about an idea was posted
	 *
	 * @package WP Idea Stream
	 * @subpackage buddypress/activity
	 *
	 * @since 2.0.0
	 *
	 * @param  string               $action   the action string
	 * @param  BP_Activity_Activity $activity the activity object
	 * @uses   wp_idea_stream_comments_get_comment_link() to override the primary link
	 * @uses   bp_core_get_userlink() to get user's profile url
	 * @uses   esc_url() to sanitize the primary link (unchanged)
	 * @uses   esc_html() to sanitize the custom part of the action
	 * @uses   apply_filters() call 'wp_idea_stream_buddypress_format_idea_comment_activity_action' to override the comment action strings
	 * @return string                         the action adjusted for an idea if needed
	 */
	public function format_idea_comment_activity_action( $action = '', $activity = null  ) {
		if ( ! is_object( $activity ) ) {
			return false;
		}

		$primary_link = wp_idea_stream_comments_get_comment_link( $activity->secondary_item_id );

		if ( empty( $primary_link ) ) {
			$primary_link = $activity->primary_link;
		}

		$action = sprintf(
			_x( '%1$s replied to this %2$s', 'idea commented activity action', 'wp-idea-stream' ),
			bp_core_get_userlink( $activity->user_id ),
			'<a href="' . esc_url( $primary_link ) . '">' . esc_html( mb_strtolower( $this->post_type_object->labels->singular_name, 'UTF-8' ) ) . '</a>'
		);

		return apply_filters( 'wp_idea_stream_buddypress_format_idea_comment_activity_action', $action );
	}

	/**
	 * 5/ Comments about ideas will generate an activity, never an activity comment
	 *
	 * @package WP Idea Stream
	 * @subpackage buddypress/activity
	 *
	 * @since 2.0.0
	 *
	 * @param  bool $option BuddyPress synchronisation setting
	 * @return bool         true if it's an idea, unchanged otherwise
	 */
	public function force_activity_add( $option = false ) {
		if ( ! empty( $this->idea ) ) {
			$option = true;
		}

		return $option;
	}

	/** Managing activities *********************************************************/

	/**
	 * Gets a list of activity ids to manage
	 *
	 * Generally, it's the activity id about an idea merged with the activities
	 * recorded for the comments about this idea.
	 *
	 * @package WP Idea Stream
	 * @subpackage buddypress/activity
	 *
	 * @since 2.0.0
	 *
	 * @param  int     $idea_id     the idea ID
	 * @param  string  $post_status the status of the idea
	 * @param  string  $post_password the post password if set
	 * @uses   bp_activity_get() to get activities
	 * @uses   wp_idea_stream_get_post_type() to get the idea post type
	 * @uses   wp_idea_stream_comments_get_comments() to get the comments about the idea
	 * @uses   wp_filter_object_list() filters a list of objects, based on a set of key => value arguments.
	 * @uses   wp_list_pluck() to pluck a certain field out of each object in a list.
	 * @return array                activity ids to manage
	 */
	public function get_idea_and_comments( $idea_id = 0, $post_status = 'publish', $post_password = '' ) {
		$idea_and_comments = array();

		if ( empty( $idea_id ) ) {
			return $idea_and_comments;
		}

		// We have a matching catched value, return it
		if ( ! empty( $this->edit_activities[ $idea_id ] ) ) {
			return $this->edit_activities[ $idea_id ];
		}

		// try to get the activity about the idea
		$activity = bp_activity_get( array(
			'filter' => array(
				'action'       => 'new_' . wp_idea_stream_get_post_type(),
				'secondary_id' => $idea_id,
			),
			'show_hidden'  => true,
			'spam'         => 'all',
			'per_page'     => false,
		) );

		// Case when the activity is deleted but we need to get the comments
		$already_deleted = (bool) ! empty( $post_password ) || in_array( $post_status, array( 'trash', 'draft', 'pending' ) );

		// If it exists, add it to the array
		if ( ! empty( $activity['activities'] ) || ( empty( $activity['activities'] ) && $already_deleted ) ) {
			$idea_and_comments = (array) $activity['activities'];

			// Check for comments
			$comments = wp_idea_stream_comments_get_comments( array(
				'fields'      => 'ids',
				'post_id'     => $idea_id,
				'post_status' => $post_status,
			) );

			if ( ! empty( $comments ) ) {
				// Do we have activities about the corresponding comments ?
				$activity_comments = bp_activity_get( array(
					'filter' => array(
						'action'       => 'new_' . wp_idea_stream_get_post_type() .'_comment',
						'secondary_id' => $comments,
					),
					'show_hidden'  => true,
					'spam'         => 'all',
					'per_page'     => false,
				) );

				// If found, add comments to the array
				if ( ! empty( $activity_comments['activities'] ) ) {
					$idea_and_comments = array_merge( $idea_and_comments, (array) $activity_comments['activities'] );
				}
			}

			// Do we have privates ?
			$this->private_activities[ $idea_id ] = wp_filter_object_list( $idea_and_comments, array( 'hide_sitewide' => 1 ), 'and', 'id' );

			// keep only ids
			$idea_and_comments = wp_list_pluck( $idea_and_comments, 'id' );

			// Catch results
			$this->edit_activities[ $idea_id ] = $idea_and_comments;
		}

		// Finally return the list of activity ids.
		return $idea_and_comments;
	}

	/**
	 * Bulk Edit activities (visibility and/or component and/or item id)
	 *
	 * @package WP Idea Stream
	 * @subpackage buddypress/activity
	 *
	 * @since 2.0.0
	 *
	 * @global $wpdb
	 * @param  array   $activities    the list of acitivy ids to edit
	 * @param  int     $hide_sitewide the visibility to update the activity with
	 * @param  string  $component_id  the BuddyPress component to update the activity with
	 * @param  int     $item_id       the item to update the activity with
	 * @uses   buddypress() to get BuddyPress instance
	 * @uses   wp_parse_id_list() to sanitize a list of ids
	 * @uses   wp_cache_delete() to clean a cached value
	 */
	public static function bulk_edit_activity( $activities = array(), $hide_sitewide = 1, $component_id = '', $item_id = 0 ) {
		global $wpdb;
		$bp = buddypress();
		$set = array();

		if ( empty( $activities ) ) {
			return false;
		}

		// Sanitize ids
		$activities = wp_parse_id_list( $activities );

		$in = implode( ',', $activities );
		$where = "id IN ({$in})";

		// Update visibility ?
		if ( -1 != $hide_sitewide ) {
			$set['hide_sitewide'] = $wpdb->prepare( 'hide_sitewide = %d', $hide_sitewide );
		}

		// Update the component (groups or blogs) ?
		if ( ! empty( $component_id ) ) {
			$set['component_id'] = $wpdb->prepare( 'component = %s', $component_id );
		}

		// Update the item id (root blog id or group id) ?
		if ( ! empty( $item_id ) ) {
			$set['item_id'] = $wpdb->prepare( 'item_id = %d', $item_id );
		}

		// Nothing to update ?
		if ( empty( $set ) ) {
			return false;
		}

		// Join the set part of the update query
		$set = join( ', ', $set );

		// We don't try to edit the activity action field as since BuddyPress 2.0
		// action are generated at run time for internationalization reasons.
		$updated = $wpdb->get_var( "UPDATE {$bp->activity->table_name} SET {$set} WHERE {$where}" );

		// Reset the activity cache
		foreach ( $activities as $activity_id ) {
			wp_cache_delete( $activity_id, 'bp_activity' );
		}
		wp_cache_delete( 'bp_activity_sitewide_front', 'bp' );

		return $updated;
	}

	/**
	 * Add or edit activities so that they are private
	 *
	 * @package WP Idea Stream
	 * @subpackage buddypress/activity
	 *
	 * @since 2.0.0
	 *
	 * @param  int     $idea_id     the idea ID
	 * @param  WP_Post $idea        the idea object
	 * @uses   get_post()           to get the idea object if not set
	 * @uses   WP_Idea_Stream_Activity->get_idea_and_comments() to get activities to manage
	 * @uses   self::bulk_edit_activity to mark activities as private
	 * @uses   get_current_blog_id() to get the current blog ID
	 * @uses   buddypress() to get BuddyPress instance
	 * @uses   add_query_arg(), get_home_url() to build the link to the idea.
	 * @uses   bp_activity_thumbnail_content_images() to take content, remove images, and replace them with a single thumbnail image
	 * @uses   bp_create_excerpt() to generate the content of the activity
	 * @uses   bp_activity_add() to save the private activity
	 * @uses   apply_filters() call 'bp_blogs_record_activity_content' to apply the filters on blog posts for private ideas
	 *                         call 'wp_idea_stream_buddypress_activity_post_private' to override the private activity args
	 */
	public function mark_activity_private( $idea_id = 0, $idea = null ) {
		if ( empty( $idea ) ) {
			$idea = get_post( $idea_id );
		}

		// Get activities
		$activities = $this->get_idea_and_comments( $idea_id, $idea->post_status );

		// If activities, then update their visibility status
		if ( ! empty( $activities ) ) {

			// Do not update activities if they all are already marked as private.
			if ( ! empty( $this->private_activities[ $idea_id ] ) && ! array_diff( $activities, $this->private_activities[ $idea_id ] ) ) {
				return;
			}

			self::bulk_edit_activity( $activities, 1 );

		// Otherwise, we need to create the activity
		} else {
			/**
			 * Here we can't use bp_blogs_record_activity() as we need
			 * to allow the groups component to eventually override the
			 * component argument. As a result, we need to set a fiew vars
			 */

			$bp = buddypress();
			$bp_activity_actions = new stdClass();
			$bp_activity_actions = $bp->activity->actions;

			// First the item id: the current blog
			$blog_id = get_current_blog_id();

			// Then all needed args except content
			$args = array(
				'component'         => buddypress()->blogs->id,
				'type'              => 'new_' . $this->post_type,
				'primary_link'      => add_query_arg( 'p', $idea_id, trailingslashit( get_home_url( $blog_id ) ) ),
				'user_id'           => (int) $idea->post_author,
				'item_id'           => $blog_id,
				'secondary_item_id' => $idea_id,
				'recorded_time'     => $idea->post_date_gmt,
				'hide_sitewide'     => 1,
			);

			// The content will require to use functions that bp_blogs_record_activity()
			// is using to format it.
			$content = '';

			if ( ! empty( $idea->post_content ) ) {
				$content = bp_activity_thumbnail_content_images( $idea->post_content, $args['primary_link'], $args );
				$content = bp_create_excerpt( $content );
			}

			// Add the content to the activity args
			$args['content'] = $content;

			/**
			 * Filter is used internally to override ideas posted within a private or hidden group
			 *
			 * @param array   $args    the private activity arguments
			 * @param WP_Post $idea    the idea object
			 */
			$args = apply_filters( 'wp_idea_stream_buddypress_activity_post_private', $args, $idea );

			// Define the corresponding index
			$activity_type      = $args['type'];
			$activity_component = $args['component'];

			// override the activity type
			$args['type'] = $this->activity_actions[ $activity_type ]->type;

			if ( empty( $bp->activity->actions->{$activity_component}->{$activity_type}['format_callback'] ) ) {
				bp_activity_set_action(
					$this->activity_actions[ $activity_type ]->component,
					$this->activity_actions[ $activity_type ]->type,
					$this->activity_actions[ $activity_type ]->admin_caption,
					$this->activity_actions[ $activity_type ]->action_callback
				);
			}

			/**
			 * Finally publish the private activity
			 * and reset BuddyPress activity actions
			 */
			$private_activity_id = bp_activity_add( $args );

			// Check for comments
			if ( ! empty( $private_activity_id ) ) {
				$activities = $this->get_idea_and_comments( $idea_id, $idea->post_status );

				$activities = array_diff( $activities, array( $private_activity_id ) );

				// Update comments visibility
				if ( ! empty( $activities ) ) {
					$test = self::bulk_edit_activity( $activities, 1 );
				}
			}

			$bp->activity->actions = $bp_activity_actions;

			// Reset edited activities
			$this->edit_activities = array();
		}
	}

	/**
	 * Delete or edit activities regarding the transition post status
	 *
	 * @package WP Idea Stream
	 * @subpackage buddypress/activity
	 *
	 * @since 2.0.0
	 *
	 * @param  int     $idea_id     the idea ID
	 * @param  WP_Post $idea        the idea object
	 * @uses   get_post()           to get the idea object if not set
	 * @uses   WP_Idea_Stream_Activity->get_idea_and_comments() to get activities to manage
	 * @uses   bp_activity_delete() to delete activities
	 * @uses   self::bulk_edit_activity to mark activities as private
	 * @uses   apply_filters() call 'wp_idea_stream_buddypress_activity_edit' to override edit args
	 */
	public function maybe_delete_activity( $idea_id = 0, $idea = null ) {
		if ( empty( $idea ) ) {
			$idea = get_post( $idea_id );
		}

		$activities = $this->get_idea_and_comments( $idea_id, $idea->post_status, $idea->post_password );

		// If no activity stop.
		if ( empty( $activities ) ) {

			// Publish the activity (no more pending or the password was reset?)
			if ( 'publish' == $idea->post_status && empty( $idea->post_password ) ) {
				bp_activity_post_type_publish( $idea_id, $idea );
			}

			return;
		}

		// Published with a password / trashed / pending or drafted idea > delete activities
		if ( ! empty( $idea->post_password ) || in_array( $idea->post_status, array( 'trash', 'draft', 'pending' ) ) ) {
			/**
			 * Delete activities.
			 */
			foreach ( $activities as $activity_id ) {
				bp_activity_delete( array( 'id' => $activity_id ) );
			}

			// Reset edited activities
			if ( ! empty( $this->edit_activities[ $idea_id ] ) ) {
				$this->edit_activities[ $idea_id ] = array();
			}

		// Make sure these activities are public
		} else {
			/**
			 * There can be a case where the idea was first privately published,
			 * and then edited as publicly published. BuddyPress will create a new
			 * activity, so we need to handle this case and eventually delete private
			 * activities about the same idea, but keep comments.
			 */
			if ( ! empty( $this->private_activities[ $idea_id ] ) ) {
				// Keep comments only, visibility will be edited after
				if ( count( $this->private_activities[ $idea_id ] ) > 1 ) {
					$activities = array_slice( $this->private_activities[ $idea_id ], 1 );
				} else {
					$activities = array_diff( $activities, $this->private_activities[ $idea_id ] );
				}

				// Delete the private to avoid duplicates
				bp_activity_delete( array( 'id' => reset( $this->private_activities[ $idea_id ] ) ) );
			}

			// Reset edited activities
			$this->edit_activities = array();

			// The above case needs a new check.
			if ( empty( $activities ) ) {
				return;
			}

			/**
			 * Used internally in case an idea is added or removed from a group
			 *
			 * @param array   $edit_args the component and item id arguments
			 * @param WP_Post $idea      the idea object
			 */
			$edit_args = apply_filters( 'wp_idea_stream_buddypress_activity_edit', array(), $idea );

			// Update component, item_id + visibility
			if ( ! empty( $edit_args['component'] ) && ! empty( $edit_args['item_id'] ) ) {
				$component = sanitize_key( $edit_args['component'] );
				$item_id   = absint( $edit_args['item_id'] );

				self::bulk_edit_activity( $activities, 0, $component, $item_id );

			// Simply update visibility
			} else {
				self::bulk_edit_activity( $activities, 0 );
			}
		}
	}

	/**
	 * Edit activities component and/or item id
	 *
	 * @package WP Idea Stream
	 * @subpackage buddypress/activity
	 *
	 * @since 2.0.0
	 *
	 * @param  WP_Post $idea          the idea object
	 * @param  string  $component_id  the BuddyPress component id to attach activities to
	 * @param  int     $old_item_id   the previous item id activities were attached to
	 * @param  object  $item          the new item object the activities are attached to
	 * @uses   WP_Idea_Stream_Activity->get_idea_and_comments() to get activities to manage
	 * @uses   self::bulk_edit_activity to edit activities (component and/or item)
	 */
	public function change_item_id( $idea = null, $component_id = '', $old_item_id = 0, $item = null ) {
		// Bail if we do not have an idea
		if ( empty( $idea->ID ) ) {
			return;
		}

		// Get activities about the idea
		$activities = $this->get_idea_and_comments( $idea->ID, $idea->post_status );

		// No activities to edit!
		if ( empty( $activities ) ) {
			return;
		}

		/**
		 * If the component id is not set, fallback to blogs one and
		 * set the item id to be the current blog id.
		 */
		if ( empty( $component_id ) ) {
			$component_id = buddypress()->blogs->id;
			$item_id = get_current_blog_id();

		// Updating the linked group.
		} else {
			// Bail if new group id is not defined
			if ( empty( $item->id ) ) {
				return;
			}

			// Set the new group
			$item_id = $item->id;
		}

		/**
		 * Finally update the component and item id and skip visibility as it
		 * should have already been handled in $this->maybe_delete_activity or
		 * in $this->mark_activity_private
		 */
		return self::bulk_edit_activity( $activities, -1, $component_id, $item_id );
	}

	/**
	 * Reset activities component and item id
	 *
	 * Used when one or more activities are removed from a group.
	 *
	 * @package WP Idea Stream
	 * @subpackage buddypress/activity
	 *
	 * @since 2.0.0
	 *
	 * @param  integer $item_id      the item id activities are attached to
	 * @param  string  $component_id the BuddyPress component id activities are attached to
	 * @param  WP_Post $idea         the idea object
	 * @uses   get_post()           to get the idea object if not set
	 * @uses   WP_Idea_Stream_Activity->get_idea_and_comments() to get activities to manage
	 * @uses   bp_activity_get() to get activities
	 * @uses   wp_idea_stream_get_post_type() to get the idea post type
	 * @uses   wp_list_pluck() to pluck a certain field out of each object in a list.
	 * @uses   self::bulk_edit_activity to edit activities (component and item)
	 */
	public function reset_activity_item_id( $item_id = 0, $component_id = '', $idea = null ) {
		if ( empty( $item_id ) || empty( $component_id ) ) {
			return false;
		}

		$activities = array();

		// Get activities about the idea ( secondary_id )
		if ( ! empty( $idea ) ) {
			if ( ! is_a( $idea, 'WP_Post' ) ) {
				$idea = get_post( (int) $idea );
			}

			// Avoid some troubles in activity table
			if ( empty( $idea->ID ) ) {
				return false;
			}

			$activities = $this->get_idea_and_comments( $idea->ID, $idea->post_status );

		} else {
			$get_activities = bp_activity_get( array(
				'filter' => array(
					'action'       => array(
						'new_' . wp_idea_stream_get_post_type(),
						'new_' . wp_idea_stream_get_post_type() .'_comment'
					),
					'object'  => $component_id,
					'item_id' => $item_id,
				),
				'show_hidden'  => true,
				'spam'         => 'all',
				'per_page'     => false,
			) );

			if ( ! empty( $get_activities['activities'] ) ) {
				$activities = wp_list_pluck( $get_activities['activities'], 'id' );
			}
		}

		if ( empty( $activities ) ) {
			return false;
		} else {
			return self::bulk_edit_activity( $activities, -1, buddypress()->blogs->id, get_current_blog_id() );
		}
	}

	/**
	 * Allow private comments about Ideas
	 *
	 * @since  2.3.1
	 *
	 * @param  integer $comment_id  The comment ID
	 * @param  boolean $is_approved Whether the comment is approved or not
	 */
	public function allow_private_comments( $comment_id = 0, $is_approved = true ) {
		if ( empty( $comment_id ) ) {
			return;
		}

		$comment   = get_comment( $comment_id );
		$post      = get_post( $comment->comment_post_ID );

		if ( $this->post_type === $post->post_type && 'private' === $post->post_status ) {
			$this->allowed_private_comment = true;
			add_filter( 'bp_activity_post_type_is_post_status_allowed', '__return_false' );
		}
	}

	/**
	 * Manage activities about idea comments
	 *
	 * @package WP Idea Stream
	 * @subpackage buddypress/activity
	 *
	 * @since 2.0.0
	 *
	 * @param  string $new_status comment status applyed
	 * @param  string $old_status previous comment status
	 * @param  object $comment    the comment object
	 * @uses   get_post_type() to get the post post type
	 * @uses   remove_action() to remove BuddyPress actions
	 * @uses   bp_activity_get() to get activities
	 * @uses   bp_activity_delete() to delete activities
	 */
	public function manage_comment_activity( $new_status = '', $old_status = '', $comment = null ) {
		// Bail if no post id, as we need to check for the post type
		if ( empty( $comment->comment_post_ID ) ) {
			return;
		}

		// Not a comment about an idea ? Bail.
		if ( $this->post_type != get_post_type( $comment->comment_post_ID ) ) {
			return;
		}

		// Avoid BuddyPress to manage transtion status, we'll manage idea comments ourselves
		remove_action( 'transition_comment_status', 'bp_activity_transition_post_type_comment_status' );

		/**
		 * If the new status is approved, and we already created the activity bail, it should be
		 * the case if comment was edited from wp-admin/comment.php?action=editcomment screen
		 */
		if ( 'approved' == $new_status && ! empty( $this->secondary_item_id ) && $this->secondary_item_id == $comment->comment_ID ) {
			return;
		}

		$activity_comments = bp_activity_get( array(
			'filter' => array(
				'action'       => 'new_' . $this->post_type .'_comment',
				'secondary_id' => $comment->comment_ID,
			),
			'show_hidden'  => true,
			'spam'         => 'all',
			'per_page'     => false,
		) );

		$activity_post_object = bp_activity_get_post_type_tracking_args( $this->post_type );

		// No activities and status is approved.
		if ( empty( $activity_comments['activities'] ) && 'approved' == $new_status ) {
			// Allow comments on private ideas
			add_filter( 'bp_activity_post_type_is_post_status_allowed', '__return_false' );

			// Record an activity
			bp_activity_post_type_comment( $comment->comment_ID, true, $activity_post_object );

			// Remove the temporary filter
			remove_filter( 'bp_activity_post_type_is_post_status_allowed', '__return_false' );

		/**
		 * Else status should be 'delete', 'hold', 'trash', 'spam', 'unapproved'
		 * For all these cases, simply remove the activity.
		 */
		} else if ( ! empty( $activity_comments['activities'] ) && 'approved' != $new_status ) {
			// Safely delete activities having status != approved
			foreach ( (array) $activity_comments['activities'] as $activity ) {
				bp_activity_delete( array( 'id' => $activity->id ) );
			}
		}
	}

	/** Activity templates ********************************************************/

	/**
	 * Prevents the 'comments' button to appear in activities that relate to an idea
	 * or a comment about an idea.
	 *
	 * @package WP Idea Stream
	 * @subpackage buddypress/activity
	 *
	 * @since 2.0.0
	 *
	 * @param  bool   $can_comment true if the activity can be commented, false otherwise
	 * @param  string $action      the activity type
	 * @uses   wp_list_pluck() to pluck a certain field out of each object in a list.
	 * @return bool false if activity has an IdeaStream type, unchanged otherwise
	 */
	public function activity_no_comment( $can_comment = true, $action = '' ) {
		$no_comment_types = wp_list_pluck( $this->activity_actions, 'type' );

		if ( in_array( $action, $no_comment_types ) ) {
			$can_comment = false;
		}

		return $can_comment;
	}

	/**
	 * Removes the comment action in Activity Admin screen, as the filter
	 * "bp_activity_list_table_can_comment" doesn't send the item.
	 *
	 * @package WP Idea Stream
	 * @subpackage buddypress/activity
	 *
	 * @since 2.0.0
	 *
	 * @param  array  $actions list of admin actions
	 * @param  array  $item    the item being iterated on
	 * @return array           the new list of actions if activity have an ideastream type,
	 *                         unchanged otherwise.
	 */
	public function activity_admin_no_comment( $actions = array(), $item = array() ) {
		$no_comment_types = wp_list_pluck( $this->activity_actions, 'type' );

		if ( ! empty( $item['type'] ) && in_array( $item['type'], $no_comment_types ) ) {
			unset( $actions['reply'] );
		}

		return $actions;
	}

	/**
	 * Forces the "view discussion" link to be the object link.
	 *
	 * @package WP Idea Stream
	 * @subpackage buddypress/activity
	 *
	 * @since 2.0.0
	 *
	 * @param  string $link the discussion link
	 * @param  BP_Activity_Activity $activity the activity object
	 * @uses   wp_list_pluck() to pluck a certain field out of each object in a list.
	 * @return string the new discussion link if type matches one of ideastream's ones, unchanged otherwise
	 */
	public function activity_permalink( $link = '', $activity = null ) {
		if ( empty( $activity->type ) ) {
			return $link;
		}

		$use_primary_link = wp_list_pluck( $this->activity_actions, 'type' );

		if ( in_array( $activity->type, $use_primary_link ) ) {
			$link = $activity->primary_link;
		}

		return $link;
	}

	/**
	 * Make sure a deleted/spammed user has no more IdeaStream activities
	 *
	 * @package WP Idea Stream
	 * @subpackage buddypress/activity
	 *
	 * @since 2.0.0
	 *
	 * @param  int $user_id the user ID
	 * @uses   wp_list_pluck() to pluck a certain field out of each object in a list.
	 * @uses   bp_activity_get() to get all ideastream activities of the user
	 * @uses   bp_activity_delete() to delete the activity
	 */
	public function user_deleted_activities( $user_id = 0 ) {
		if ( empty( $user_id ) ) {
			return;
		}

		$ideastream_types = wp_list_pluck( $this->activity_actions, 'type' );

		$ideastream_activities = bp_activity_get( array(
			'filter' => array(
				'action'  => $ideastream_types,
				'user_id' => $user_id,
			),
			'show_hidden'  => true,
			'spam'         => 'all',
			'per_page'     => false,
		) );

		if ( ! is_array( $ideastream_activities ) ) {
			return;
		}

		$activities = wp_list_pluck( $ideastream_activities['activities'], 'id' );

		foreach ( $activities as $activity_id ) {
			bp_activity_delete( array( 'id' => $activity_id ) );
		}
	}

	/**
	 * Reset the component id to blogs to avoid leaving activities in groups
	 * once the user "removed" his ideas as he left the group.
	 *
	 * @package WP Idea Stream
	 * @subpackage buddypress/activity
	 *
	 * @since 2.0.0
	 *
	 * @param  int     $user_id the user id
	 * @param  array   $ideas   list of WP_Post ideas objects
	 * @uses   WP_Idea_Stream_Activity->reset_activity_item_id() to reset the activity to blogs component
	 *                                                           and current blogs id
	 */
	public function user_reset_component_id( $user_id = 0, $ideas = array() ) {
		if ( empty( $user_id ) || ! is_array( $ideas ) ) {
			return;
		}

		foreach ( $ideas as $idea ) {
			$this->reset_activity_item_id( true, true, $idea );
		}
	}

	/** Repair Tool ***************************************************************/

	/**
	 * Add a repair tool to BuddyPress ones to repair IdeaStream group activities
	 *
	 * @package WP Idea Stream
	 * @subpackage buddypress/activity
	 *
	 * @since 2.0.0
	 *
	 * @param  array  $repair_list list of repair tools
	 * @uses   bp_is_active() to check if a component is active
	 * @return array               new list of repair tools
	 */
	public function register_repair_tool( $repair_list = array() ) {
		if ( ! bp_is_active( 'groups' ) ) {
			return $repair_list;
		}

		$repair_list[2718] = array(
			'ideastream-repair-groups-activities',
			__( 'Repair WP Idea Stream activities.', 'wp-idea-stream' ),
			array( $this, 'repair_activities' ),
		);

		return $repair_list;
	}

	/**
	 * Process the repair tool
	 *
	 * In case something went wrong with group activities (visibility, component...),
	 * this tool should repair the problematic activities.
	 *
	 * @package WP Idea Stream
	 * @subpackage buddypress/activity
	 *
	 * @since 2.0.0
	 *
	 * @global $wpdb
	 * @uses   bp_is_active() to check if a component is active
	 * @uses   wp_list_pluck() to pluck a certain field out of each object in a list.
	 * @uses   bp_activity_get() to get all ideastream activities
	 * @uses   wp_filter_object_list() to filter a list of objects, based on a set of key => value arguments.
	 * @uses   wp_parse_id_list() to sanitize a list of ids
	 * @uses   get_post_meta() to get the group id the idea is attached to
	 * @uses   groups_get_groups() to get the needed groups
	 * @uses   WP_Idea_Stream_Activity::bulk_edit_activity() to update the activities (component/item_id/visibility)
	 * @return array the result of the repair operation.
	 */
	public function repair_activities() {
		global $wpdb;
		$buddypress = buddypress();
		$blog_id = get_current_blog_id();

		// Description of this tool, displayed to the user
		$statement = __( 'Making sure WP Idea Stream activities are consistent: %s', 'wp-idea-stream' );

		// Default to failure text
		$result    = __( 'No activity needs to be repaired.', 'wp-idea-stream' );

		// Default to unrepaired
		$repair    = 0;

		if ( ! bp_is_active( 'groups' ) ) {
			return;
		}

		$ideastream_types = wp_list_pluck( $this->activity_actions, 'type' );

		// Get all activities
		$ideastream_activities = bp_activity_get( array(
			'filter' => array(
				'action'  => $ideastream_types,
			),
			'show_hidden'  => true,
			'spam'         => 'all',
			'per_page'     => false,
		) );

		if ( is_array( $ideastream_activities['activities'] ) ) {
			$idea_comments      = array();
			$idea_posts         = array();
			$to_repair          = array();
			$attached_component = array(
				'comment' => array(
					$buddypress->groups->id => array(),
					$buddypress->blogs->id  => array(),
				),
				'post' => array(
					$buddypress->groups->id => array(),
					$buddypress->blogs->id  => array(),
				),
			);

			foreach ( $ideastream_activities['activities'] as $activity ) {
				if ( false !== strpos( $activity->type, 'comment' ) ) {
					$idea_comments[ $activity->id ] = $activity->secondary_item_id;

					$attached_component['comment'][ $activity->component ][] = $activity->id;
				} else {
					$idea_posts[ $activity->id ] = $activity->secondary_item_id;

					$attached_component['post'][ $activity->component ][] = $activity->id;
				}
			}

			// Gets the comment activities to repair
			if ( ! empty( $idea_comments ) ) {
				// I don't think get_comments() allow us to get comments
				// using a list of comment ids..
				$in = implode( ',', wp_parse_id_list( $idea_comments ) );
				$sql = array(
					'select' => "SELECT c.comment_ID, m.meta_value as group_id",
					'from'   => "FROM {$wpdb->comments} c LEFT JOIN {$wpdb->postmeta} m",
					'on'     => "ON (c.comment_post_ID = m.post_id )",
					'where'  => $wpdb->prepare( "WHERE comment_ID IN ({$in}) AND m.meta_key = %s", '_ideastream_group_id' ),
				);

				$idea_comments_check = $wpdb->get_results( join( ' ', $sql ), OBJECT_K );

				foreach ( $idea_comments as $activity_comment_id => $comment_secondary_id ) {
					if ( ! empty( $idea_comments_check[ $comment_secondary_id ] ) && ! in_array( $activity_comment_id, $attached_component['comment'][ $buddypress->groups->id ] ) ) {
						$to_repair['groups'][ $idea_comments_check[ $comment_secondary_id ]->group_id ][] = $activity_comment_id;
					} else if ( empty( $idea_comments_check[ $comment_secondary_id ] ) && in_array( $activity_comment_id, $attached_component['comment'][ $buddypress->groups->id ] ) ) {
						$to_repair['blogs'][] = $activity_comment_id;
					}
				}
			}

			// Gets the idea activities to repair
			if ( ! empty( $idea_posts ) ) {

				add_filter( 'wp_idea_stream_ideas_get_status', 'wp_idea_stream_ideas_get_all_status', 10, 1 );

				// Get user's ideas posted in the group
				$idea_posts_check = wp_idea_stream_ideas_get_ideas( array(
					'per_page' => -1,
					'include'  => $idea_posts,
					'meta_query' => array(
						array(
							'key'     => '_ideastream_group_id',
							'compare' => 'EXIST'
						)
					)
				) );

				$idea_posts_check = wp_list_pluck( $idea_posts_check['ideas'], 'ID' );

				remove_filter( 'wp_idea_stream_ideas_get_status', 'wp_idea_stream_ideas_get_all_status', 10, 1 );

				foreach ( $idea_posts as $activity_post_id => $post_secondary_id ) {
					if ( in_array( $post_secondary_id, $idea_posts_check ) && ! in_array( $activity_post_id, $attached_component['post'][ $buddypress->groups->id ] ) ) {
						$group_id = get_post_meta( $post_secondary_id, '_ideastream_group_id', true );

						if ( ! empty( $group_id ) ) {
							$to_repair['groups'][ $group_id ][] = $activity_post_id;
						}
					} else if ( ! in_array( $post_secondary_id, $idea_posts_check ) && in_array( $activity_post_id, $attached_component['post'][ $buddypress->groups->id ] ) ) {
						$to_repair['blogs'][] = $activity_post_id;
					}
				}
			}
		}

		if ( ! empty( $to_repair['groups'] ) ) {
			// Get the groups to have their status
			$groups = groups_get_groups( array(
				'show_hidden'     => true,
				'populate_extras' => false,
				'include'         => join( ',', array_keys( $to_repair['groups'] ) ),
				'per_page'        => false,
			) );

			if ( ! empty( $groups['groups'] ) ) {
				$public_groups = wp_filter_object_list( $groups['groups'], array( 'status' => 'public' ), 'and', 'id' );

				foreach ( $to_repair['groups'] as $item_id => $repair_activities ) {
					$hide_sitewide = 0;

					if ( ! in_array( $item_id, $public_groups ) ) {
						$hide_sitewide = 1;
					}

					self::bulk_edit_activity( $repair_activities, $hide_sitewide, $buddypress->groups->id, $item_id );

					$repair += count( $repair_activities );
				}
			}
		}

		if ( ! empty( $to_repair['blogs'] ) ) {
			self::bulk_edit_activity( $to_repair['blogs'], 0, $buddypress->blogs->id, $blog_id );

			$repair += count( $to_repair['blogs'] );
		}

		// Setup success/fail messaging
		if ( ! empty( $repair ) ) {
			$result = sprintf( __( '%d repared', 'wp-idea-stream' ), $repair );
		}

		// All done!
		return array( 0, sprintf( $statement, $result ) );
	}
}

endif;

/**
 * Init Activity class, before 'bp_register_activity_actions'
 * so that we can hook to it to set our activity actions
 */
add_action( 'bp_init', array( 'WP_Idea_Stream_Activity', 'manage_activities' ), 7 );

