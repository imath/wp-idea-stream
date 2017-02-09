<?php
/**
 * WP Idea Stream Rewrites Class.
 *
 * @package WP Idea Stream\core\classes
 *
 * @since 2.4.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Rewrites Class.
 *
 * @since 2.0.0
 */
class WP_Idea_Stream_Rewrites {

	/**
	 * Constructor
	 *
	 * @since 2.0.0
	 */
	public function __construct() {
		$this->setup_globals();
		$this->hooks();
	}

	/**
	 * Start the rewrites
	 *
	 * @since 2.0.0
	 */
	public static function start() {
		$wp_idea_stream = wp_idea_stream();

		if ( empty( $wp_idea_stream->rewrites ) ) {
			$wp_idea_stream->rewrites = new self;
		}

		return $wp_idea_stream->rewrites;
	}

	/**
	 * Setup the rewrite ids and slugs
	 *
	 * @since 2.0.0
	 */
	private function setup_globals() {
		/** Rewrite ids ***************************************************************/

		$this->page_rid          = 'paged'; // WordPress built-in global var
		$this->user_rid          = wp_idea_stream_user_rewrite_id();
		$this->user_rates_rid    = wp_idea_stream_user_rates_rewrite_id();
		$this->user_comments_rid = wp_idea_stream_user_comments_rewrite_id();
		$this->cpage_rid         = wp_idea_stream_cpage_rewrite_id();
		$this->action_rid        = wp_idea_stream_action_rewrite_id();
		$this->search_rid        = wp_idea_stream_search_rewrite_id();

		/** Rewrite slugs *************************************************************/

		$this->user_slug          = wp_idea_stream_user_slug();
		$this->user_rates_slug    = wp_idea_stream_user_rates_slug();
		$this->user_comments_slug = wp_idea_stream_user_comments_slug();
		$this->cpage_slug         = wp_idea_stream_cpage_slug();
		$this->action_slug        = wp_idea_stream_action_slug();
	}

	/**
	 * Hooks to load the register methods
	 *
	 *
	 * @since 2.0.0
	 */
	private function hooks() {
		// Register rewrite tags.
		add_action( 'wp_idea_stream_add_rewrite_tags',  array( $this, 'add_rewrite_tags' )  );

		// Register the rewrite rules
		add_action( 'wp_idea_stream_add_rewrite_rules', array( $this, 'add_rewrite_rules' ) );

		// Register the permastructs
		add_action( 'wp_idea_stream_add_permastructs',  array( $this, 'add_permastructs' )  );
	}

	/**
	 * Register the rewrite tags
	 *
	 * @since 2.0.0
	 */
	public function add_rewrite_tags() {
		add_rewrite_tag( '%' . $this->user_rid          . '%', '([^/]+)'   );
		add_rewrite_tag( '%' . $this->user_rates_rid    . '%', '([1]{1,})' );
		add_rewrite_tag( '%' . $this->user_comments_rid . '%', '([1]{1,})' );
		add_rewrite_tag( '%' . $this->cpage_rid         . '%', '([^/]+)'   );
		add_rewrite_tag( '%' . $this->action_rid        . '%', '([^/]+)'   );
		add_rewrite_tag( '%' . $this->search_rid        . '%', '([^/]+)'   );
	}

	/**
	 * Register the rewrite rules
	 *
	 * @since 2.0.0
	 */
	public function add_rewrite_rules() {
		$priority  = 'top';
		$root_rule = '/([^/]+)/?$';

		$page_slug  = wp_idea_stream_paged_slug();
		$paged_rule = '/([^/]+)/' . $page_slug . '/?([0-9]{1,})/?$';
		$embed_rule = '/([^/]+)/embed/?$';

		// User Rates
		$user_rates_rule       = '/([^/]+)/' . $this->user_rates_slug . '/?$';
		$user_rates_paged_rule = '/([^/]+)/' . $this->user_rates_slug . '/' . $page_slug . '/?([0-9]{1,})/?$';

		// User Comments
		$user_comments_rule        = '/([^/]+)/' . $this->user_comments_slug . '/?$';
		$user_comments_paged_rule  = '/([^/]+)/' . $this->user_comments_slug . '/' . $this->cpage_slug . '/?([0-9]{1,})/?$';

		// User rules
		add_rewrite_rule( $this->user_slug . $user_comments_paged_rule, 'index.php?' . $this->user_rid . '=$matches[1]&' . $this->user_comments_rid . '=1&' . $this->cpage_rid . '=$matches[2]', $priority );
		add_rewrite_rule( $this->user_slug . $user_comments_rule,       'index.php?' . $this->user_rid . '=$matches[1]&' . $this->user_comments_rid . '=1',                                      $priority );
		add_rewrite_rule( $this->user_slug . $user_rates_paged_rule,    'index.php?' . $this->user_rid . '=$matches[1]&' . $this->user_rates_rid .    '=1&' . $this->page_rid . '=$matches[2]',  $priority );
		add_rewrite_rule( $this->user_slug . $user_rates_rule,          'index.php?' . $this->user_rid . '=$matches[1]&' . $this->user_rates_rid .    '=1',                                      $priority );
		add_rewrite_rule( $this->user_slug . $embed_rule,               'index.php?' . $this->user_rid . '=$matches[1]&embed=true',                                                              $priority );
		add_rewrite_rule( $this->user_slug . $root_rule,                'index.php?' . $this->user_rid . '=$matches[1]',                                                                         $priority );

		// Action rules (only add a new idea right now)
		add_rewrite_rule( $this->action_slug . $root_rule, 'index.php?' . $this->action_rid . '=$matches[1]', $priority );
	}

	/**
	 * Register the permastructs
	 *
	 * @since 2.0.0
	 */
	public function add_permastructs() {
		// User Permastruct
		add_permastruct( $this->user_rid, $this->user_slug . '/%' . $this->user_rid . '%', array(
			'with_front'  => false,
			'ep_mask'     => EP_NONE,
			'paged'       => true,
			'feed'        => false,
			'forcomments' => false,
			'walk_dirs'   => true,
			'endpoints'   => false,
		) );

		// Action Permastruct
		add_permastruct( $this->action_rid, $this->action_slug . '/%' . $this->action_rid . '%', array(
			'with_front'  => false,
			'ep_mask'     => EP_NONE,
			'paged'       => true,
			'feed'        => false,
			'forcomments' => false,
			'walk_dirs'   => true,
			'endpoints'   => false,
		) );
	}
}
