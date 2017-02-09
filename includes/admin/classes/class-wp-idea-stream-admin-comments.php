<?php
/**
 * WP Idea Stream Admin Comments Class.
 *
 * @package WP Idea Stream\admin\classes
 *
 * @since 2.4.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Comments Administration class
 *
 * The goal of the class is to adapt the Comments
 * Administration interface so that comments about ideas
 * are disjoined and included in the main IdeaStream menu
 *
 * @since 2.0.0
 *
 * @see  comments/class WP_Idea_Stream_Comments for the disjoin methods
 */
class WP_Idea_Stream_Admin_Comments {

	/** Variables *****************************************************************/

	/**
	 * @access  private
	 * @var string the ideas post type
	 */
	private $post_type = '';

	/**
	 * @access  public
	 * @var object idea comments stats
	 */
	public $idea_comment_count;

	/**
	 * The constuctor
	 *
	 * @since 2.0.0
	 */
	public function __construct() {
		$this->setup_globals();
		$this->hooks();
	}

	/**
	 * Starts the class
	 *
	 * @since 2.0.0
	 */
	public static function start() {
		if ( ! is_admin() ) {
			return;
		}

		$wp_idea_stream_admin = wp_idea_stream()->admin;

		if ( empty( $wp_idea_stream_admin->comments ) ) {
			$wp_idea_stream_admin->comments = new self;
		}

		return $wp_idea_stream_admin->comments;
	}

	/**
	 * Sets some globals
	 *
	 * @since 2.0.0
	 */
	private function setup_globals() {
		$this->post_type          = wp_idea_stream_get_post_type();
		$this->idea_comment_count = false;
	}

	/**
	 * Sets up the hooks to extend IdeaStream Administration
	 *
	 * @since 2.0.0
	 */
	private function hooks() {

		/** Actions *******************************************************************/

		// Add a bubble to IdeaStream parent menu if some idea comments are pending
		add_action( 'wp_idea_stream_admin_head',  array( $this, 'admin_head' ), 10 );

		// Check the post type if actions were made clicking on a moderation link from an email
		add_action( 'load-edit-comments.php', array( $this, 'maybe_force_post_type' ) );

		// Load some script to also disjoin bubbles
		add_action( 'admin_footer-edit-comments.php', array( $this, 'disjoin_post_bubbles' ) );
		add_action( 'admin_footer-edit.php',          array( $this, 'disjoin_post_bubbles' ) );

		/** Filters *******************************************************************/

		// Add a comment submenu to IdeaStream menu.
		add_filter( 'wp_idea_stream_admin_menus', array( $this, 'comments_menu' ), 10, 1 );

		// Adjust comment views (count) and comment row actions
		add_filter( 'comment_status_links', array( $this, 'adjust_comment_status_links' ), 10, 1 );
		add_filter( 'comment_row_actions',  array( $this, 'adjust_row_actions' ),          10, 2 );
	}

	/**
	 * Adds a bubble to menu title to show how many comments are pending
	 *
	 * @since 2.0.0
	 *
	 * @param  string  $menu_title the text for the menu
	 * @param  int     $count      the number of comments
	 * @return string              the title menu output
	 */
	public function bubbled_menu( $menu_title = '', $count = 0 ) {
		return sprintf(
			_x( '%1$s %2$s', 'wp idea stream admin menu bubble', 'wp-idea-stream' ),
			$menu_title,
			"<span class='awaiting-mod count-" . esc_attr( $count ) . "'><span class='pending-count-idea'>" . number_format_i18n( $count ) . "</span></span>"
		);
	}

	/**
	 * Creates a comments submenu to the IdeaStream menu
	 *
	 * @since 2.0.0
	 *
	 * @param  array  $menus list of menu items to add
	 * @return array         the new menu items
	 */
	public function comments_menu( $menus = array() ) {
		// Comments menu title
		$comments_menu_title = esc_html__( 'Comments', 'wp-idea-stream' );

		$this->idea_comment_count = wp_idea_stream_get_idea_var( 'idea_comment_count' );

		if ( empty( $this->idea_comment_count ) ) {
			$this->idea_comment_count = wp_idea_stream_comments_count_comments();
		}

		$comments_menu_title = $this->bubbled_menu( $comments_menu_title . ' ', $this->idea_comment_count->moderated );

		$menus[0] = array(
			'type'          => 'comments',
			'parent_slug'   => wp_idea_stream()->admin->parent_slug,
			'page_title'    => esc_html__( 'Comments', 'wp-idea-stream' ),
			'menu_title'    => $comments_menu_title,
			'capability'    => 'edit_ideas',
			'slug'          => add_query_arg( 'post_type', $this->post_type, 'edit-comments.php' ),
			'function'      => '',
			'alt_screen_id' => 'edit-comments.php',
			'actions'       => array(
				'admin_head-%page%' => array( $this, 'comments_menu_highlight' )
			),
		);

		return $menus;
	}

	/**
	 * Adds a bubble to IdeaStream menu title and make sure it's the highlighted parent
	 * when idea comments screens are displayed
	 *
	 * @since 2.0.0
	 *
	 * @global $menu
	 * @global $submenu
	 * @global $parent_file
	 * @global $submenu_file
	 */
	public function admin_head() {
 		global $menu, $submenu, $parent_file, $submenu_file;

 		$menu_title = _x( 'Ideas', 'Main Plugin menu', 'wp-idea-stream' );

 		// Eventually add a bubble in IdeaStream Menu
 		foreach ( $menu as $position => $data ) {
 			if ( strpos( $data[0], $menu_title ) !== false ) {
				$menu[ $position ][0] = $this->bubbled_menu( $menu_title . ' ', $this->idea_comment_count->moderated );
 			}
		}

		if ( $this->post_type == get_current_screen()->post_type && 'comment' == get_current_screen()->id ) {
			$parent_file  = add_query_arg( 'post_type', $this->post_type, 'edit.php' );
			$submenu_file = add_query_arg( 'post_type', $this->post_type, 'edit-comments.php' );
		}
	}

	/**
	 * Make the comments IdeaStream submenu is the highlighted submenu
	 * if its content is displayed
	 *
	 * @since 2.0.0
	 *
	 * @global $submenu_file
	 */
	public function comments_menu_highlight() {
		global $submenu_file;

		if( ! wp_idea_stream_is_admin() ) {
			return;
		}

		$submenu_file = add_query_arg( 'post_type', $this->post_type, 'edit-comments.php' );
	}

	/**
	 * Replaces the comment count by the idea comments count in the screen views when
	 * managing comments about ideas
	 *
	 * @since 2.0.0
	 *
	 * @param  array  $status_links list of WP Liste Comments Table views
	 * @return array                list of views with a new count if needed
	 */
	public function adjust_comment_status_links( $status_links = array() ) {
		// Bail if not in Idea Comments area
		if ( ! wp_idea_stream_is_admin() ) {
			return $status_links;
		}

		foreach ( $status_links as $key => $link ) {

			if ( isset( $this->idea_comment_count->{$key} ) ) {
				$prefix = $key;

				if ( 'moderated' === $key ) {
					$prefix = 'pending';
				}

				$link = preg_replace(
					'/<span class=\"' . $prefix . '-count\">\d<\/span>/',
					'<span class="' . $prefix . '-count">' . $this->idea_comment_count->{$key} . '</span>',
					$link
				);
			}

			$link = preg_replace( '/\?/', '?post_type=' . $this->post_type . '&', $link );

			if ( preg_match( '/class=\"pending-count\"/', $link ) ) {
				$link = preg_replace( '/class=\"pending-count\"/', 'class="pending-count-idea"', $link );
			}

			$status_links[$key] = $link;
		}

		return $status_links;
	}

	/**
	 * Adds a post_type query var to the edit action link
	 *
	 * @since 2.0.0
	 *
	 * @param  array  $actions the list of row actions
	 * @param  object $comment the comment object
	 * @return array           the list of row actions
	 */
	public function adjust_row_actions( $actions = array(), $comment = null ) {
		// Default is unknown...
		$post_type = '';

		if ( ! empty( $comment->post_type ) ) {
			$post_type = $comment->post_type;

		// Ajax Listing comments in the edit idea screen will
		// fail in getting the post type.
		} else {
			$post_type = get_post_type( get_the_ID() );
		}

		// Bail if not the ideas post type
		if ( $this->post_type != $post_type ) {
			return $actions;
		}

		if ( ! empty( $actions['edit'] ) ) {
			// get the url
			preg_match( '/<a\s[^>]*?href=[\'"](.+?)[\'"]/is', $actions['edit'], $matches );

			// and add the post type query var to it.
			if ( ! empty( $matches[1] ) ) {
				$actions['edit'] = str_replace( $matches[1], $matches[1] . '&amp;post_type=' . $this->post_type, $actions['edit'] );
			}
		}

		return $actions;
	}

	/**
	 * Sets the post type attribute of the screen when the comments
	 * was made on an idea
	 *
	 * When clicking on a moderation link within a moderation email, the post type
	 * is not set, as a result, the highlighted menu is not the good one. This make
	 * sure the typenow global and the post type attribute of the screen are set
	 * to the ideas post type if needed.
	 *
	 * @since 2.0.0
	 *
	 * @global $typenow
	 */
	function maybe_force_post_type() {
		global $typenow;

		if ( empty( $_GET['post_type'] ) ) {

			$get_keys = array_keys( $_GET );
			$did_keys = array( 'approved', 'trashed', 'spammed' );

			$match_keys = array_intersect( $get_keys, $did_keys);

			if ( ! $match_keys ) {
				return;
			}

			if ( ! in_array( 'p', $get_keys ) ) {
				return;
			}

			$post_type = get_post_type( absint( $_GET['p'] ) );

			if ( empty( $post_type ) ) {
				return;
			}

			$typenow = $post_type;
			get_current_screen()->post_type = $post_type;
		}
	}

	/**
	 * Disjoin comment count bubbles
	 *
	 * The goal here is to make sure the ajax bubbles count update
	 * are dissociated between posts and ideas
	 *
	 * @since 2.0.0
	 *
	 * @return string JS output
	 */
	public function disjoin_post_bubbles() {
		if ( ! wp_idea_stream_is_admin() ) {
			return;
		}
		?>
		<script type="text/javascript">
		/* <![CDATA[ */
		( function( $ ) {

			<?php if ( 'edit-comments' == get_current_screen()->id ) :?>

				// Neutralize post bubbles
				$( 'span.pending-count' ).each( function() {
					original = $( this ).prop( 'class' );
					$( this ).prop( 'class', original.replace( 'pending-count', 'pending-count-post' ) )
				} );

				// Activate idea bubbles
				$( 'span.pending-count-idea' ).each( function() {
					original = $( this ).prop( 'class' );
					$( this ).prop( 'class', original.replace( 'pending-count-idea', 'pending-count' ) )
				} );

			<?php endif; ?>

			// As WP_List_Table->comments_bubble() function is protected and no filter... last option is JS
			$( '.post-com-count' ).each( function() {
				original = $( this ).prop( 'href' );
				$( this ).prop( 'href', original + '&post_type=<?php wp_idea_stream_post_type(); ?>' );
			} );

		} )(jQuery);
		/* ]]> */
		</script>
		<?php
	}
}
