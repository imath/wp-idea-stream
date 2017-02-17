<?php
/**
 * WP Idea Stream Ideas Categories widget Class.
 *
 * @package WP Idea Stream\ideas\classes
 *
 * @since 2.4.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Ideas Categories Widget
 *
 * "Extends".. It's more limit WP_Widget_Categories widget feature
 * disallow the dropdown because the javascript part is not "filterable"
 * disallow the hierarchy.. I still wonder why i've chosen to ? But we'll
 * see if we can use it in a future release.
 *
 * @since 2.0.0
 */
 class WP_Idea_Stream_Ideas_Categories extends WP_Widget_Categories {

 	/**
	 * Constructor
	 *
	 * @since 2.0.0
	 */
	public function __construct() {
		$widget_ops = array( 'description' => __( 'A list of Idea categories', 'wp-idea-stream' ) );
		WP_Widget::__construct( false, $name = __( 'WP Idea Stream categories', 'wp-idea-stream' ), $widget_ops );
	}

	/**
	 * Register the widget
	 *
	 * @since 2.0.0
	 */
	public static function register_widget() {
		register_widget( 'WP_Idea_Stream_Ideas_Categories' );
	}

	/**
	 * Forces the idea category taxonomy to be used
	 *
	 * @since 2.0.0
	 *
	 * @param  array  $category_args the arguments to get the list of categories
	 * @return array                 same arguments making sure idea taxonomy is set
	 */
	public function use_ideas_category( $category_args = array() ) {
		// It's that simple !!
		$category_args['taxonomy'] = wp_idea_stream_get_category();

		// Now return these args
		return $category_args;
	}

	/**
	 * Displays the content of the widget
	 *
	 * Temporarly adds and remove filters and use parent category widget display
	 *
	 * @since  2.0.0
	 *
	 * @param  array  $args
	 * @param  array  $instance
	 */
	public function widget( $args = array(), $instance = array() ) {
		// Add filter so that the taxonomy used is cat-ideas
		add_filter( 'widget_categories_args', array( $this, 'use_ideas_category' ) );

		// Use WP_Widget_Categories::widget()
		parent::widget( $args, $instance );

		// Remove filter to reset the taxonomy for other widgets
		add_filter( 'widget_categories_args', array( $this, 'use_ideas_category' ) );
	}

	/**
	 * Display the form in Widgets Administration
	 *
	 * @since 2.0.0
	 */
	public function form( $instance = array() ) {
		//Defaults
		$instance = wp_parse_args( (array) $instance, array( 'title' => '') );
		$title = esc_attr( $instance['title'] );
		$count = isset($instance['count']) ? (bool) $instance['count'] :false;
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'wp-idea-stream' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
		</p>

		<p>
			<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id( 'count' ); ?>" name="<?php echo $this->get_field_name( 'count' ); ?>"<?php checked( $count ); ?> />
			<label for="<?php echo $this->get_field_id( 'count' ); ?>"><?php _e( 'Show idea counts', 'wp-idea-stream' ); ?></label><br />
		</p>
		<?php
	}
}