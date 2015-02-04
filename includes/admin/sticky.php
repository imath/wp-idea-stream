<?php
/**
 * WP Idea Stream Sticky Administration.
 *
 * Sticky ideas Administration
 *
 * @package WP Idea Stream
 * @subpackage admin/sticky
 *
 * @since 2.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WP_Idea_Stream_Admin_Sticky' ) ) :
/**
 * Sticky Ideas Administration class
 *
 * Unlike regular Posts, WordPress doesn't support natively
 * the sticky feature for other post types.
 * @see  https://core.trac.wordpress.org/ticket/12702
 *
 * The goal of this class is to add a custom metabox to allow
 * ideas to be sticked to the top of the ideas post type archive
 * page (not the front page of the blog)
 * On front end, in ideas/functions you'll find the wp_idea_stream_ideas_stick_ideas()
 * function that is extending the WP_Query in order to prepend the ideas sticked to top
 * of the post type archive page.
 *
 * @package WP Idea Stream
 * @subpackage admin/sticky
 *
 * @since 2.0.0
 */
class WP_Idea_Stream_Admin_Sticky {

	/** Variables *************************************************************/

	/**
	 * @access  private
	 * @var string The ideas post type identifier
	 */
	private $post_type = '';

	/**
	 * The constructor
	 *
	 * @package WP Idea Stream
	 * @subpackage admin/sticky
	 *
	 * @since 2.0.0
	 */
	public function __construct() {
		$this->setup_globals();
		$this->hooks();
	}

	/**
	 * Let's start the class
	 *
	 * @package WP Idea Stream
	 * @subpackage admin/sticky
	 *
	 * @since 2.0.0
	 *
	 * @uses  is_admin() to check for WordPress Administration
	 * @uses  wp_idea_stream() to get plugin's main instance
	 */
	public static function start() {
		if ( ! is_admin() ) {
			return;
		}

		$wp_idea_stream_admin = wp_idea_stream()->admin;

		if ( empty( $wp_idea_stream_admin->sticky ) ) {
			$wp_idea_stream_admin->sticky = new self;
		}

		return $wp_idea_stream_admin->sticky;
	}

	/**
	 * Setups the post type global
	 *
	 * @package WP Idea Stream
	 * @subpackage admin/sticky
	 *
	 * @since 2.0.0
	 *
	 * @uses  wp_idea_stream_get_post_type() The ideas post type identifier
	 */
	private function setup_globals() {
		$this->post_type = wp_idea_stream_get_post_type();
	}

	/**
	 * Setups the action and filters to hook to
	 *
	 * @package WP Idea Stream
	 * @subpackage admin/sticky
	 *
	 * @since 2.0.0
	 *
	 * @uses add_action() to perform custom actions at key points
	 * @uses add_filter() to override some key vars
	 */
	private function hooks() {

		/** Actions *******************************************************************/

		// Sticky metabox
		add_action( 'wp_idea_stream_save_metaboxes', array( $this, 'sticky_metabox_save' ), 10, 3 );

		// Remove trashed post from stickies
		add_action( 'wp_trash_post', array( $this, 'unstick_idea' ), 10, 1 );

		/** Filters *******************************************************************/

		// Sticky metabox
		add_filter( 'wp_idea_stream_admin_get_meta_boxes', array( $this, 'sticky_metabox' ),  10, 1 );

		// Adds the sticky states to the idea
		add_filter( 'display_post_states', array( $this, 'idea_states' ), 10, 2 );

		// Filter the WP_List_Table views to include a sticky one.
		add_filter( "wp_idea_stream_admin_edit_ideas_views", array( $this, 'idea_views' ), 10, 1 );

		// Add sticky updated messages
		add_filter( 'wp_idea_stream_admin_updated_messages', array( $this, 'updated_messages' ), 10, 1 );

		// Help tabs
		add_filter( 'wp_idea_stream_get_help_tabs', array( $this, 'sticky_help_tabs' ), 10, 1 );
	}

	/**
	 * Adds a sticky metabox to the IdeaStream metaboxes
	 *
	 * @package WP Idea Stream
	 * @subpackage admin/sticky
	 *
	 * @since 2.0.0
	 *
	 * @param  array  $metaboxes the IdeaStream metabox list
	 * @return array            the new list
	 */
	public function sticky_metabox( $metaboxes = array() ) {
		$sticky_metabox = array(
			'sticky' => array(
				'id'            => 'wp_idea_stream_sticky_box',
				'title'         => __( 'Sticky', 'wp-idea-stream' ),
				'callback'      => array( 'WP_Idea_Stream_Admin_Sticky', 'sticky_do_metabox' ),
				'context'       => 'side',
				'priority'      => 'high'
		) );

		return array_merge( $metaboxes, $sticky_metabox );
	}

	/**
	 * Displays the sticky metabox
	 *
	 * It also checks the status of the idea to eventually
	 * remove the idea from stickies if the status is not
	 * 'publish'
	 *
	 * @package WP Idea Stream
	 * @subpackage admin/sticky
	 *
	 * @since 2.0.0
	 *
	 * @param  WP_Post $idea the idea object
	 * @uses   WP_Idea_Stream_Admin_Sticky::unstick_idea() to check if the idea needs to be removed from stickies
	 * @uses   wp_idea_stream_ideas_is_sticky() to check if the idea is already sticked to front of archive page
	 * @uses   checked() to add a checked attribute if needed
	 * @uses   wp_nonce_field() to add a security token to check upon once submitted
	 * @uses   do_action() call 'wp_idea_stream_do_sticky_metabox' to perform custom actions
	 * @return string HTML output
	 */
	public static function sticky_do_metabox( $idea = null ) {
		$id = $idea->ID;

		if ( wp_idea_stream_ideas_admin_no_sticky( $idea ) ) {

			self::unstick_idea( $id );

			esc_html_e( 'This idea cannot be sticky', 'wp-idea-stream' );
		} else {

			$is_sticky = wp_idea_stream_ideas_is_sticky( $id );
			?>

			<p>
				<label class="screen-reader-text" for="wp_idea_stream_sticky"><?php esc_html_e( 'Select whether to stick or not the idea.', 'wp-idea-stream' ); ?></label>
				<input type="checkbox" name="wp_idea_stream_sticky" id="wp_idea_stream_sticky" value="1" <?php checked( true, $is_sticky ) ;?>/> <strong class="label"><?php esc_html_e( 'Mark as sticky', 'wp-idea-stream' ); ?></strong>
			</p>

			<?php
			wp_nonce_field( 'wp_idea_stream_sticky_metabox_save', 'wp_idea_stream_sticky_metabox' );

			/**
			 * @param  int  $id the idea ID
			 * @param  bool $is_sticky true if the idea is sticky, false otherwise
			 */
			do_action( 'wp_idea_stream_do_sticky_metabox', $id, $is_sticky );
		}
	}

	/**
	 * Saves the sticky preference for the idea
	 *
	 * @package WP Idea Stream
	 * @subpackage admin/sticky
	 *
	 * @since 2.0.0
	 *
	 * @param  int      $id     the idea ID
	 * @param  WP_Post  $idea   the idea object
	 * @param  bool     $update whether it's an update or not
	 * @uses   wp_idea_stream_set_idea_var() to set an IdeaStream global for a later use
	 * @uses   check_admin_referer() to check the request was made on the site
	 * @uses   wp_idea_stream_ideas_get_stickies() to get the sticky ideas (list of IDs)
	 * @uses   update_option() to update the list of sticky ideas
	 * @return int          the idea ID
	 */
	public function sticky_metabox_save( $id = 0, $idea = null, $update = false ) {
		$updated_message = false;

		// Private post or password protected ideas cant be sticky
		if ( 'private' == $idea->post_status || ! empty( $idea->post_password ) ) {
			// Eventually add a message
			if ( ! empty( $_POST['wp_idea_stream_sticky'] ) ) {
				wp_idea_stream_set_idea_var( 'feedback', array( 'updated_message' => 14 ) );
			}

			return $id;
		}

		// Nonce check
		if ( ! empty( $_POST['wp_idea_stream_sticky_metabox'] ) && check_admin_referer( 'wp_idea_stream_sticky_metabox_save', 'wp_idea_stream_sticky_metabox' ) ) {

			$sticky_ideas = wp_idea_stream_ideas_get_stickies();
			$updated_stickies = $sticky_ideas;

			// The idea is no more sticky
			if ( empty( $_POST['wp_idea_stream_sticky'] ) && in_array( $id, $sticky_ideas ) ) {
				$updated_stickies = array_diff( $updated_stickies, array( $id ) );
				$updated_message = 15;
			}

			// The idea is to mark as sticky
			if ( ! empty( $_POST['wp_idea_stream_sticky'] ) && ! in_array( $id, $sticky_ideas ) ) {
				$updated_stickies = array_merge( $updated_stickies, array( $id ) );
				$updated_message = 16;
			}

			if ( $sticky_ideas != $updated_stickies ) {
				update_option( 'sticky_ideas', $updated_stickies );
			}
		}

		if ( ! empty( $updated_message ) ) {
			wp_idea_stream_set_idea_var( 'feedback', array( 'updated_message' => $updated_message ) );
		}

		return $id;
	}

	/**
	 * Unstick an idea
	 *
	 * If the post status is not publish or if the idea was trashed: unstick!
	 *
	 * @package WP Idea Stream
	 * @subpackage admin/sticky
	 *
	 * @since 2.0.0
	 *
	 * @param  int      $id     the idea ID
	 * @param  bool     $update whether it's an update or not
	 * @uses   wp_idea_stream_ideas_admin_no_sticky() to check if the idea can be sticked
	 * @uses   wp_idea_stream_ideas_get_stickies() to get the sticky ideas (list of IDs)
	 * @uses   update_option() to update the list of sticky ideas
	 */
	public static function unstick_idea( $id = 0 ) {
		if ( empty( $id ) ) {
			return false;
		}

		$stickies = wp_idea_stream_ideas_get_stickies();

		if ( ! wp_idea_stream_ideas_is_sticky( $id, $stickies ) ) {
			return;
		}

		$stickies = array_diff( $stickies, array( $id ) );

		// Update the sticky ideas
		update_option( 'sticky_ideas', $stickies );
	}

	/**
	 * Adds sticky updated messages to IdeaStream updated messages
	 *
	 * @package WP Idea Stream
	 * @subpackage admin/sticky
	 *
	 * @since 2.0.0
	 *
	 * @param  array  $messages list of IdeaStream Updated messages
	 * @return array            new list
	 */
	public function updated_messages( $messages = array() ) {
		$messages[14] = $messages[1] . '<br/>' . esc_html__( 'Private or password protected ideas cannot be marked as sticky', 'wp-idea-stream' );
		$messages[15] = $messages[1] . '<br/>' . esc_html__( 'Idea successfully removed from stickies', 'wp-idea-stream' );
		$messages[16] = $messages[1] . '<br/>' . esc_html__( 'Idea successfully added to stickies', 'wp-idea-stream' );

		return $messages;
	}

	/**
	 * Adds a sticky state after the idea title in WP_List_Table
	 *
	 * @package WP Idea Stream
	 * @subpackage admin/sticky
	 *
	 * @since 2.0.0
	 *
	 * @param  array   $idea_states  the available idea states
	 * @param  WP_Post $idea         the idea object
	 * @uses   wp_idea_stream_ideas_is_sticky() to check if the idea is sticked to front of archive page
	 * @return array                 the new idea states
	 */
	public function idea_states( $idea_states = array(), $idea = null ) {
		if ( $idea->post_type != $this->post_type ) {
			return $idea_states;
		}

		if ( wp_idea_stream_ideas_is_sticky( $idea->ID ) ) {
			$idea_states['sticky'] = esc_html_x( 'Sticky', 'idea list table row state', 'wp-idea-stream' );
		}

		return $idea_states;
	}

	/**
	 * Add a sticky view to existing idea views (WP_List_Table)
	 *
	 * @package WP Idea Stream
	 * @subpackage admin/sticky
	 *
	 * @since 2.0.0
	 *
	 * @param  array  $views the available idea views
	 * @uses   wp_idea_stream_ideas_get_stickies() to get the sticky ideas (list of IDs)
	 * @uses   add_query_arg() to add query vars to an url
	 * @uses   admin_url() to build a link inside the current blog's Administration
	 * @uses   esc_url() to sanitize an url
	 * @uses   number_format_i18n() to format numbers
	 * @return array         the new views
	 */
	public function idea_views( $views = array() ) {
		$stickies = wp_idea_stream_ideas_get_stickies();
		$count_stickies = count( $stickies );

		if ( ! empty( $stickies ) ) {
			$sticky_url = add_query_arg(
				array(
					'post_type'    => $this->post_type,
					'sticky_ideas' => 1,
				),
				admin_url( 'edit.php' )
			);

			$class = '';
			if ( ! empty( $_GET['sticky_ideas'] ) ) {
				$class = 'class="current"';
			}

			$sticky_link = '<a href="' . esc_url( $sticky_url ) .'"' . $class . '>' . sprintf(
				_nx( 'Sticky <span class="count">(%s)</span>', 'Sticky <span class="count">(%s)</span>', $count_stickies, 'admin ideas sticky view', 'wp-idea-stream' ),
				number_format_i18n( $count_stickies )
				) . '</a>';

			$sticky_view = array(
				'sticky_ideas' => $sticky_link
			);

			foreach ( $views as $key => $view ) {
				// Make sure current class is removed for the other views
				// if viewing stickies
				if ( ! empty( $class ) ) {
					$views[ $key ] = str_replace( $class, '', $view );
				}

				// Make sure the trash view is last
				if ( 'trash' == $key ) {
					$sticky_view[ $key ] = $view;
					unset( $views[ $key ] );
				}
			}

			$views = array_merge( $views, $sticky_view );
		}

		return $views;
	}

	/**
	 * Adds the Sticky help tabs
	 *
	 * @package WP Idea Stream
	 * @subpackage admin/sticky
	 *
	 * @since 2.0.0
	 *
	 * @param  array  $help_tabs the list of help tabs
	 * @return array            the new list of help tabs
	 */
	public function sticky_help_tabs( $help_tabs = array() ) {
		if ( ! empty( $help_tabs['ideas']['add_help_tab'] ) ) {
			$ideas_help_tabs = wp_list_pluck( $help_tabs['ideas']['add_help_tab'], 'id' );
			$ideas_overview = array_search( 'ideas-overview', $ideas_help_tabs );

			if ( isset( $help_tabs['ideas']['add_help_tab'][ $ideas_overview ]['content'] ) ) {
				$help_tabs['ideas']['add_help_tab'][ $ideas_overview ]['content'][] = esc_html__( 'The Sticky metabox allows you to stick a published idea (not password protected) to the top of the front ideas archive first page.', 'wp-idea-stream' );
			}
		}

		return $help_tabs;
	}
}

endif;

add_action( 'wp_idea_stream_loaded', array( 'WP_Idea_Stream_Admin_Sticky', 'start' ), 7 );
