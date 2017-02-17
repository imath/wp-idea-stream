<?php
/**
 * WP Idea Stream Idea Meta Class.
 *
 * @package WP Idea Stream\ideas\classes
 *
 * @since 2.4.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Idea metas Class.
 *
 * Tries to ease the process of managing custom fields for ideas
 * @see  wp_idea_stream_ideas_register_meta() ideas/functions to
 * register new idea metas.
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
	 * @since 2.0.0
	 */
	public function __construct() {
		$this->do_metas();
	}

	/**
	 * Starts the class
	 *
	 * @since 2.0.0
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
	 * @since 2.0.0
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
	 * @since 2.0.0
	 *
	 * @param  WP_Post $idea the idea object
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
	 * @since 2.0.0
	 *
	 * @param  int     $idea_id     the ID of the idea
	 * @param  object  $meta_object the meta object to send to callback function
	 * @param  string  $context     the context (admin/single/form)
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
	 * @since 2.0.0
	 *
	 * @param  int      $id     the idea ID
	 * @param  WP_Post  $idea   the idea object
	 * @param  bool     $update whether it's an update or not
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
	 * @since 2.0.0
	 *
	 * @param  string $context the context (single/form)
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
	 * @since 2.0.0
	 * 
	 * @return string          HTML Output
	 */
	public function single_output() {
		if ( ! wp_idea_stream_is_single_idea() ) {
			return;
		}

		return $this->front_output( 'single' );
	}
}