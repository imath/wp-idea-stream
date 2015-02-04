<?php
/**
 * WP Idea Stream Ideas classes.
 *
 * For now only the ideas loop one.
 *
 * @package WP Idea Stream
 * @subpackage ideas/classes
 *
 * @since 2.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/** Idea Class ****************************************************************/

if ( ! class_exists( 'WP_Idea_Stream_Idea' ) ) :
/**
 * Idea Class.
 *
 * @package WP Idea Stream
 * @subpackage core/classes
 *
 * @since 2.0.0
 */
class WP_Idea_Stream_Idea {

	/**
	 * The ID of the idea
	 *
	 * @access  public
	 * @var     integer
	 */
	public $id;

	/**
	 * The name of the idea
	 *
	 * @access  public
	 * @var     string
	 */
	public $name;

	/**
	 * The ID of the author
	 *
	 * @access  public
	 * @var     integer
	 */
	public $author;

	/**
	 * The title of the idea
	 *
	 * @access  public
	 * @var     string
	 */
	public $title;

	/**
	 * The content of the idea
	 *
	 * @access  public
	 * @var     string
	 */
	public $description;

	/**
	 * The status of the idea
	 *
	 * @access  public
	 * @var     string
	 */
	public $status;

	/**
	 * Associative Array containing terms for
	 * the tag and category taxonomies
	 *
	 * @access  public
	 * @var     array
	 */
	public $taxonomies;

	/**
	 * Associative Array meta_key => meta_value
	 *
	 * @access  public
	 * @var     array
	 */
	public $metas;

	/**
	 * Constructor.
	 *
	 * @package WP Idea Stream
	 * @subpackage ideas/classes
	 *
	 * @since 2.0.0
	 *
	 * @param  mixed int|string ID or name of the idea
	 */
	function __construct( $id = 0 ){
		if ( ! empty( $id ) ) {
			if ( is_numeric( $id ) ) {
				$this->id = $id;
			} else {
				$this->name = $id;
			}
			$this->populate();
		}
	}

	/**
	 * Get an idea
	 *
	 * @package WP Idea Stream
	 * @subpackage ideas/classes
	 *
	 * @since 2.0.0
	 *
	 * @uses  self::get_idea_by_name()
	 * @uses  get_post()
	 * @uses  wp_get_object_terms()
	 * @uses  wp_idea_stream_get_category()
	 * @uses  wp_idea_stream_get_tag()
	 * @uses  get_post_custom()
	 * @uses  maybe_unserialize()
	 */
	public function populate() {

		if ( empty( $this->id ) ) {
			// Let's try to get an ID thanks to its name.
			if ( ! empty( $this->name ) ) {
				$this->idea = self::get_idea_by_name( $this->name );
			}
		} else {
			$this->idea  = get_post( $this->id );
		}

		$this->id          = $this->idea->ID;
		$this->author      = $this->idea->post_author;
		$this->title       = $this->idea->post_title;
		$this->description = $this->idea->post_content;
		$this->status      = $this->idea->post_status;

		// Build an array of taxonomies
		$this->taxonomies = array();

		// Look in categories
		$categories = wp_get_object_terms( $this->id, wp_idea_stream_get_category(), array( 'fields' => 'ids' ) );

		if ( ! empty( $categories ) ) {
			$this->taxonomies = array_merge( $this->taxonomies, array(
				wp_idea_stream_get_category() => $categories,
			) );
		}

		// Look in tags
		$tags = wp_get_object_terms( $this->id, wp_idea_stream_get_tag(), array( 'fields' => 'slugs' ) );

		if ( ! empty( $tags ) ) {
			$this->taxonomies = array_merge( $this->taxonomies, array(
				wp_idea_stream_get_tag() => join( ',', $tags )
			) );
		}

		// Build an array of post metas
		$this->metas = array();

		$metas = get_post_custom( $this->id );

		foreach ( $metas as $key => $meta ) {
			if ( false === strpos( $key, '_ideastream_' ) ) {
				continue;
			}

			$ideastream_key = str_replace( '_ideastream_', '', $key );

			if ( count( $meta ) == 1 ) {
				$this->metas[ $ideastream_key ] = maybe_unserialize( $meta[0] );
			} else {
				$this->metas[ $ideastream_key ] = array_map( 'maybe_unserialize', $meta );
			}

			$this->metas['keys'][] = $ideastream_key;
		}
	}

	/**
	 * Save an idea.
	 *
	 * @package WP Idea Stream
	 * @subpackage ideas/classes
	 *
	 * @since 2.0.0
	 *
	 * @uses  wp_update_post() to edit an idea
	 * @uses  wp_insert_post() to inset an idea
	 * @uses  delete_post_meta() to delete an idea meta field
	 * @uses  update_post_meta() to update an idea meta field
	 */
	public function save() {
		$this->id          = apply_filters_ref_array( 'wp_idea_stream_id_before_save',          array( $this->id,          &$this ) );
		$this->author      = apply_filters_ref_array( 'wp_idea_stream_author_before_save',      array( $this->author,      &$this ) );
		$this->title       = apply_filters_ref_array( 'wp_idea_stream_title_before_save',       array( $this->title,       &$this ) );
		$this->description = apply_filters_ref_array( 'wp_idea_stream_description_before_save', array( $this->description, &$this ) );
		$this->status      = apply_filters_ref_array( 'wp_idea_stream_status_before_save',      array( $this->status,      &$this ) );
		$this->taxonomies  = apply_filters_ref_array( 'wp_idea_stream_taxonomies_before_save',  array( $this->taxonomies,  &$this ) );
		$this->metas       = apply_filters_ref_array( 'wp_idea_stream_metas_before_save',       array( $this->metas,       &$this ) );

		// Use this, not the filters above
		do_action_ref_array( 'wp_idea_stream_before_save', array( &$this ) );

		if ( empty( $this->author ) || empty( $this->title ) ) {
			return false;
		}

		if ( empty( $this->status ) ) {
			$this->status = 'publish';
		}

		$post_args = array(
			'post_author'  => $this->author,
			'post_title'   => $this->title,
			'post_type'    => wp_idea_stream_get_post_type(),
			'post_content' => $this->description,
			'post_status'  => $this->status,
			'tax_input'    => $this->taxonomies,
		);

		// Update.
		if ( $this->id ) {
			$post_args = array_merge( array(
				'ID' => $this->id,
			), $post_args );

			$result = wp_update_post( $post_args );
		// Insert.
		} else {
			$result = wp_insert_post( $post_args );
		}

		if ( ! empty( $result ) && ! empty( $this->metas ) ) {

			foreach ( $this->metas as $meta_key => $meta_value ) {
				// Do not update these keys.
				$skip_keys = apply_filters( 'wp_idea_stream_meta_key_skip_save', array( 'keys', 'rates', 'average_rate' ) );
				if ( in_array( $meta_key, $skip_keys ) ) {
					continue;
				}

				if ( empty( $meta_value ) ) {
					wp_idea_stream_ideas_delete_meta( $result, $meta_key );
				} else {
					wp_idea_stream_ideas_update_meta( $result, $meta_key, $meta_value );
				}
			}
		}

		do_action_ref_array( 'wp_idea_stream_after_save', array( $result, &$this ) );

		return $result;
	}

	/**
	 * The selection query
	 *
	 * @package WP Idea Stream
	 * @subpackage ideas/classes
	 *
	 * @since 2.0.0
	 *
	 * @param  array $args arguments to customize the query
	 * @uses   wp_parse_args() to merge custom args with default ones
	 * @uses   wp_idea_stream_ideas_get_status() to get the idea status to request
	 * @uses   wp_parse_id_list() to sanitize an id list
	 * @uses   wp_idea_stream_get_idea_var() to get a globalized var
	 * @uses   wp_idea_stream_set_idea_var() to set a globalized var
	 * @uses   WP_Query
	 * @return array associative array containing ideas and total count.
	 */
	public static function get( $args = array() ) {

		$defaults = array(
			'author'     => 0,
			'per_page'   => 10,
			'page'       => 1,
			'search'     => '',
			'exclude'    => '',
			'include'    => '',
			'orderby'    => 'date',
			'order'      => 'DESC',
			'meta_query' => array(),
			'tax_query'  => array(),
		);

		$r = wp_parse_args( $args, $defaults );

		/**
		 * Allow status to be filtered
		 * @see wp_idea_stream_ideas_get_status()
		 */
		$ideas_status = wp_idea_stream_ideas_get_status();

		$query_args = array(
			'post_status'    => $ideas_status,
			'post_type'      => 'ideas',
			'posts_per_page' => $r['per_page'],
			'paged'          => $r['page'],
			'orderby'        => $r['orderby'],
			'order'          => $r['order'],
			's'              => $r['search'],
		);

		if ( ! empty( $r['author'] ) ) {
			$query_args['author'] = $r['author'];
		}

		if ( ! empty( $r['exclude'] ) ) {
			$query_args['post__not_in'] = wp_parse_id_list( $r['exclude'] );
		}

		if ( ! empty( $r['include'] ) ) {
			$query_args['post__in'] = wp_parse_id_list( $r['include'] );
		}

		if ( 'rates_count' == $r['orderby'] ) {
			$r['meta_query'][] = array(
				'key'     => '_ideastream_average_rate',
				'compare' => 'EXISTS'
			);
		}

		if ( ! empty( $r['meta_query'] ) ) {
			$query_args['meta_query'] = $r['meta_query'];
		}

		if ( ! empty( $r['tax_query'] ) ) {
			$query_args['tax_query'] = $r['tax_query'];
		}

		// Get the main order
		$main_order = wp_idea_stream_get_idea_var( 'orderby' );

		// Apply the one requested
		wp_idea_stream_set_idea_var( 'orderby', $r['orderby'] );

		$ideas = new WP_Query( $query_args );

		// Reset to main order
		wp_idea_stream_set_idea_var( 'orderby', $main_order );

		return array( 'ideas' => $ideas->posts, 'total' => $ideas->found_posts );
	}

	/**
	 * Get an idea using its post name.
	 *
	 * @package WP Idea Stream
	 * @subpackage ideas/classes
	 *
	 * @since 2.0.0
	 *
	 * @global $wpdb
	 * @param  string name of the idea
	 * @uses   wp_idea_stream_get_post_type() to get the ideas post type identifier
	 * @uses   get_post() to get the idea object
	 * @return WP_Post the idea object
	 */
	public static function get_idea_by_name( $name = '' ) {
		global $wpdb;

		$where = $wpdb->prepare( 'post_name = %s AND post_type = %s', $name, wp_idea_stream_get_post_type() );
		$id = $wpdb->get_var( "SELECT ID FROM {$wpdb->posts} WHERE {$where}" );

		return get_post( $id );
	}
}

endif;

/** Ideas Loop ****************************************************************/

if ( ! class_exists( 'WP_Idea_Stream_Loop_Ideas' ) ) :
/**
 * Ideas loop Class.
 *
 * @package WP Idea Stream
 * @subpackage idea/tags
 *
 * @since 2.0.0
 */
class WP_Idea_Stream_Loop_Ideas extends WP_Idea_Stream_Loop {

	/**
	 * Constructor
	 *
	 * @package WP Idea Stream
	 * @subpackage idea/tags
	 *
	 * @since 2.0.0
	 *
	 * @param  array $args the loop args
	 * @uses   get_query_var()
	 * @uses   wp_idea_stream_get_idea_var() to get the globalized query loop
	 * @uses   wp_idea_stream_ideas_get_idea_by_name() to get the idea object thanks to its post_name
	 * @uses   wp_idea_stream_reset_post() to reset the $wp_query->post data
	 * @uses   wp_idea_stream_set_idea_var() to globalized the need for a reset postdata
	 * @uses   wp_idea_stream_ideas_get_ideas() get all matching ideas
	 * @uses   wp_idea_stream_is_pretty_links() do we have a custom permalink structure ?
	 * @uses   add_query_arg() to build the url in case default permalink is set
	 * @uses   wp_idea_stream_is_idea_archive() to check an idea archive page is being displayed
	 * @uses   wp_idea_stream_get_root_url() to get ideas archive url
	 * @uses   wp_idea_stream_is_category() to check a category page is being displayed
	 * @uses   wp_idea_stream_get_category_url() to get the category url
	 * @uses   wp_idea_stream_is_tag() to check a tag page is being displayed
	 * @uses   wp_idea_stream_get_tag_url() to get the category url
	 * @uses   wp_idea_stream_is_user_profile_rates() to check the rates user's profile page is displayed
	 * @uses   wp_idea_stream_users_get_displayed_profile_url() to get user's profile url
	 * @uses   wp_idea_stream_is_user_profile_ideas() to check the main user's profile page is displayed
	 * @uses   wp_idea_stream_paged_slug() to get the pagination slug
	 * @uses   wp_idea_stream_search_rewrite_id() to get the search rewrite id
	 * @uses   WP_Idea_Stream_Loop::start() to launch the loop
	 * @uses   apply_filters() call 'wp_idea_stream_ideas_pagination_args' to override paginate args
	 */
	public function __construct( $args = array() ) {

		if ( ! empty( $args ) && empty( $args['is_widget'] ) ) {
			$paged = get_query_var( 'paged' );

			// Set which pagination page
			if ( ! empty( $paged ) ) {
				$args['page'] = $paged;

			// Checking query string just in case
			} else if ( ! empty( $_GET['paged'] ) ) {
				$args['page'] = absint( $_GET['paged'] );

			// Checking in page args
			} else if ( ! empty( $args['page'] ) ) {
				$args['page'] = absint( $args['page'] );

			// Default to first page
			} else {
				$args['page'] = 1;
			}
		}

		// Only get the idea requested
		if ( ! empty( $args['idea_name'] ) ) {

			$query_loop = wp_idea_stream_get_idea_var( 'query_loop' );

			if ( empty( $query_loop->idea ) ) {
				$idea  = wp_idea_stream_ideas_get_idea_by_name( $args['idea_name'] );
			} else {
				$idea  = $query_loop->idea;
			}

			// can't do this too ealy
			$reset_data = array_merge( (array) $idea, array( 'is_page' => true ) );
			wp_idea_stream_reset_post( $reset_data );

			// this needs a "reset postdata"!
			wp_idea_stream_set_idea_var( 'needs_reset', true );

			$ideas = array(
				'ideas'    => array( $idea ),
				'total'    => 1,
				'get_args' => array(
					'page'     => 1,
					'per_page' => 1,
				),
			);

		// Get the ideas
		} else {
			$ideas = wp_idea_stream_ideas_get_ideas( $args );
		}

		if ( ! empty( $ideas['get_args'] ) ) {
			foreach ( $ideas['get_args'] as $key => $value ) {
				$this->{$key} = $value;
			}
		} else {
			return false;
		}

		$params = array(
			'plugin_prefix'    => 'wp_idea_stream',
			'item_name'        => 'idea',
			'item_name_plural' => 'ideas',
			'items'            => $ideas['ideas'],
			'total_item_count' => $ideas['total'],
			'page'             => $this->page,
			'per_page'         => $this->per_page,
		);

		$paginate_args = array();

		// No pretty links
		if ( ! wp_idea_stream_is_pretty_links() ) {
			$paginate_args['base'] = add_query_arg( 'paged', '%#%' );

		} else {

			// Is it the main archive page ?
			if ( wp_idea_stream_is_idea_archive() ) {
				$base = trailingslashit( wp_idea_stream_get_root_url() ) . '%_%';

			// Or the category archive page ?
			} else if ( wp_idea_stream_is_category() ) {
				$base = trailingslashit( wp_idea_stream_get_category_url() ) . '%_%';

			// Or the tag archive page ?
			} else if ( wp_idea_stream_is_tag() ) {
				$base = trailingslashit( wp_idea_stream_get_tag_url() ) . '%_%';

			// Or the displayed user rated ideas ?
			} else if ( wp_idea_stream_is_user_profile_rates() ) {
				$base = trailingslashit( wp_idea_stream_users_get_displayed_profile_url( 'rates' ) ) . '%_%';

			// Or the displayed user published ideas ?
			} else if ( wp_idea_stream_is_user_profile_ideas() ) {
				$base = trailingslashit( wp_idea_stream_users_get_displayed_profile_url() ) . '%_%';

			// Or nothing i've planed ?
			} else {

				/**
				 * Create your own pagination base if not handled by the plugin
				 *
				 * @param string empty string
				 */
				$base = apply_filters( 'wp_idea_stream_ideas_pagination_base', '' );
			}

			$paginate_args['base']   = $base;
			$paginate_args['format'] = wp_idea_stream_paged_slug() . '/%#%/';
		}

		// Is this a search ?
		if ( wp_idea_stream_get_idea_var( 'is_search' ) ) {
			$paginate_args['add_args'] = array( wp_idea_stream_search_rewrite_id() => $_GET[ wp_idea_stream_search_rewrite_id() ] );
		}

		// Do we have a specific order to use ?
		$orderby = wp_idea_stream_get_idea_var( 'orderby' );

		if ( ! empty( $orderby ) && 'date' != $orderby ) {
			$merge = array();

			if ( ! empty( $paginate_args['add_args'] ) ) {
				$merge = $paginate_args['add_args'];
			}
			$paginate_args['add_args'] = array_merge( $merge, array( 'orderby' => $orderby ) );
		}

		/**
		 * Use this filter to override the pagination
		 *
		 * @param array $paginate_args the pagination arguments
		 */
		parent::start( $params, apply_filters( 'wp_idea_stream_ideas_pagination_args', $paginate_args ) );
	}
}

endif;

if ( ! class_exists( 'WP_Idea_Stream_Idea_Metas' ) ) :
/**
 * Idea metas Class.
 *
 * Tries to ease the process of managing custom fields for ideas
 * @see  wp_idea_stream_ideas_register_meta() ideas/functions to
 * register new idea metas.
 *
 * @package WP Idea Stream
 * @subpackage idea/tags
 *
 * @since 2.0.0
 */
class WP_Idea_Stream_Idea_Metas {

	/**
	 * List of meta objects
	 *
	 * @access  public
	 * @var     array
	 */
	public $metas;

	/**
	 * The constructor
	 *
	 * @package WP Idea Stream
	 * @subpackage ideas/classes
	 *
	 * @since 2.0.0
	 */
	public function __construct() {
		$this->do_metas();
	}

	/**
	 * Starts the class
	 *
	 * @package WP Idea Stream
	 * @subpackage ideas/classes
	 *
	 * @since 2.0.0
	 *
	 * @uses  wp_idea_stream() to get WP Idea Stream instance
	 */
	public static function start() {
		$wp_idea_stream = wp_idea_stream();

		if ( empty( $wp_idea_stream->idea_metas ) ) {
			$wp_idea_stream->idea_metas = new self;
		}

		return $wp_idea_stream->idea_metas;
	}

	/**
	 * Checks if idea metas are registered and hooks to some key actions/filters
	 *
	 * @package WP Idea Stream
	 * @subpackage ideas/classes
	 *
	 * @since 2.0.0
	 *
	 * @uses add_action() to perform custom actions at key points
	 * @uses add_filter() to override some key vars
	 */
	private function do_metas() {
		$this->metas = wp_idea_stream_get_idea_var( 'ideastream_metas' );

		if ( empty( $this->metas ) || ! is_array( $this->metas ) ) {
			return;
		}

		/** Admin *********************************************************************/
		add_filter( 'wp_idea_stream_admin_get_meta_boxes', array( $this, 'register_metabox' ), 10, 1 );
		add_action( 'wp_idea_stream_save_metaboxes',       array( $this, 'save_metabox' ),     10, 3 );

		/** Front *********************************************************************/
		add_action( 'wp_idea_stream_ideas_the_idea_meta_edit', array( $this, 'front_output'  ) );
		add_action( 'wp_idea_stream_before_idea_footer',       array( $this, 'single_output' ) );
	}

	/**
	 * Registers a new IdeaStream metabox for custom fields
	 *
	 * @package WP Idea Stream
	 * @subpackage ideas/classes
	 *
	 * @since 2.0.0
	 *
	 * @param  array  $metaboxes the IdeaStream metabox list
	 * @return array            the new list
	 */
	public function register_metabox( $metaboxes = array() ) {
		$metas_metabox = array(
			'ideastream_metas' => array(
				'id'            => 'wp_idea_stream_metas_box',
				'title'         => __( 'Custom fields', 'wp-idea-stream' ),
				'callback'      => array( $this, 'do_metabox' ),
				'context'       => 'advanced',
				'priority'      => 'high'
		) );

		return array_merge( $metaboxes, $metas_metabox );
	}

	/**
	 * Outputs the fields in the Custom Field Idea metabox
	 *
	 * @package WP Idea Stream
	 * @subpackage ideas/classes
	 *
	 * @since 2.0.0
	 *
	 * @param  WP_Post $idea the idea object
	 * @uses   WP_Idea_Stream_Idea_Metas->display_meta() to display the meta output
	 * @uses   wp_nonce_field() to add a security token to check upon once submitted
	 * @return string       HTML output
	 */
	public function do_metabox( $idea = null ) {
		if ( empty( $idea->ID ) || ! is_array( $this->metas ) ) {
			esc_html_e( 'No custom fields available', 'wp-idea-stream' );
			return;
		}

		$meta_list = array_keys( $this->metas );
		?>
		<div id="ideastream_list_metas">
			<ul>
			<?php foreach ( $this->metas as $meta_object ) :?>
				<li id="ideastream-meta-<?php echo esc_attr( $meta_object->meta_key );?>"><?php $this->display_meta( $idea->ID, $meta_object, 'admin' );?></li>
			<?php endforeach;?>
			</ul>

			<input type="hidden" value="<?php echo join( ',', $meta_list );?>" name="wp_idea_stream[meta_keys]"/>
		</div>
		<?php
		wp_nonce_field( 'admin-ideastream-metas', '_admin_ideastream_metas' );
	}

	/**
	 * Displays an idea's meta
	 *
	 * Used for forms (admin or front) and single outputs
	 *
	 * @package WP Idea Stream
	 * @subpackage ideas/classes
	 *
	 * @since 2.0.0
	 *
	 * @param  int     $idea_id     the ID of the idea
	 * @param  object  $meta_object the meta object to send to callback function
	 * @param  string  $context     the context (admin/single/form)
	 * @uses   wp_idea_stream_ideas_get_meta() to get an idea meta value
	 * @uses   add_action() to temporarly add the meta callback
	 * @uses   do_action() to output the html generated by callback
	 * @uses   remove_action() to remove the meta callback
	 * @return string               HTML Output
	 */
	public function display_meta( $idea_id = 0, $meta_object = null, $context = 'form' ) {
		// bail if no meta key
		if ( empty( $meta_object->meta_key ) ) {
			return;
		}

		$meta_object->field_name  = 'wp_idea_stream[_the_metas]['. $meta_object->meta_key .']';
		$meta_object->field_value = false;
		$meta_object->idea_id     = $idea_id;
		$display_meta             = '';

		if ( empty( $meta_object->label ) ) {
			$meta_object->label = ucfirst( str_replace( '_', ' ', $meta_object->meta_key ) );
		}

		if ( ! empty( $idea_id ) ) {
			$meta_object->field_value = wp_idea_stream_ideas_get_meta( $idea_id, $meta_object->meta_key );
		}

		if ( empty( $meta_object->form ) ) {
			$meta_object->form = $meta_object->admin;
		}

		if ( 'single' == $context && empty( $meta_object->field_value ) ) {
			return;
		}

		if ( ! is_callable( $meta_object->{$context} ) ) {
			return;
		}

		// We apply the callback as an action
		add_action( 'wp_idea_stream_ideas_meta_display', $meta_object->{$context}, 10, 3 );

		// Generate the output for the meta object
		do_action( 'wp_idea_stream_ideas_meta_display', $display_meta, $meta_object, $context );

		// Remove the action for other metas
		remove_action( 'wp_idea_stream_ideas_meta_display', $meta_object->{$context}, 10, 3 );
	}

	/**
	 * Saves the custom fields when edited from the admin screens (edit/post new)
	 *
	 * @package WP Idea Stream
	 * @subpackage ideas/classes
	 *
	 * @since 2.0.0
	 *
	 * @param  int      $id     the idea ID
	 * @param  WP_Post  $idea   the idea object
	 * @param  bool     $update whether it's an update or not
	 * @uses   check_admin_referer() to check the request was made on the site
	 * @uses   wp_idea_stream_ideas_get_meta() to get an idea meta value
	 * @uses   wp_idea_stream_ideas_delete_meta() to delete an idea meta value
	 * @uses   wp_idea_stream_ideas_update_meta() to update an idea meta value
	 * @return int         		the ID of the idea
	 */
	public function save_metabox( $id = 0, $idea = null, $update = false ) {
		// Bail if no meta to save
		if ( empty( $_POST['wp_idea_stream']['meta_keys'] ) )  {
			return $id;
		}

		check_admin_referer( 'admin-ideastream-metas', '_admin_ideastream_metas' );

		$the_metas = array();
		if ( ! empty( $_POST['wp_idea_stream']['_the_metas'] ) ) {
			$the_metas = $_POST['wp_idea_stream']['_the_metas'];
		}

		$meta_keys = explode( ',', $_POST['wp_idea_stream']['meta_keys'] );
		$meta_keys = array_map( 'sanitize_key', (array) $meta_keys );

		foreach ( $meta_keys as $meta_key ) {
			if ( empty( $the_metas[ $meta_key ] ) && wp_idea_stream_ideas_get_meta( $id, $meta_key ) ) {
				wp_idea_stream_ideas_delete_meta( $id, $meta_key );
			} else if ( ! empty( $the_metas[ $meta_key ] ) ) {
				wp_idea_stream_ideas_update_meta( $id, $meta_key, $the_metas[ $meta_key ] );
			}
		}

		return $id;
	}

	/**
	 * Displays metas for form/single display
	 *
	 * @package WP Idea Stream
	 * @subpackage ideas/classes
	 *
	 * @since 2.0.0
	 *
	 * @param  string $context the context (single/form)
	 * @uses   wp_idea_stream() to get plugin's instance
	 * @uses   WP_Idea_Stream_Idea_Metas->display_meta() to display the meta output
	 * @return string          HTML Output
	 */
	public function front_output( $context = '' ) {
		if ( empty( $this->metas ) ) {
			return;
		}

		if ( empty( $context ) ) {
			$context = 'form';
		}

		$wp_idea_stream = wp_idea_stream();

		$idea_id = 0;

		if ( ! empty( $wp_idea_stream->query_loop->idea->ID ) ) {
			$idea_id = $wp_idea_stream->query_loop->idea->ID;
		}

		foreach ( $this->metas as $meta_object ) {
			$this->display_meta( $idea_id, $meta_object, $context );
		}
	}

	/**
	 * Displays metas for single display
	 *
	 * @package WP Idea Stream
	 * @subpackage ideas/classes
	 *
	 * @since 2.0.0
	 *
	 * @uses   WP_Idea_Stream_Idea_Metas->front_output() to display the meta output
	 * @return string          HTML Output
	 */
	public function single_output() {
		if ( ! wp_idea_stream_is_single_idea() ) {
			return;
		}

		return $this->front_output( 'single' );
	}
}

endif;
