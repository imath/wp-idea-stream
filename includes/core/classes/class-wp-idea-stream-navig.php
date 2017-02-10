<?php
/**
 * WP Idea Stream Nav Class.
 *
 * @package WP Idea Stream\core\classes
 *
 * @since 2.4.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * IdeaStream Navigation Menu widget class
 *
 * @since 2.0.0
 */
 class WP_Idea_Stream_Navig extends WP_Widget {

 	/**
	 * Constructor
	 *
	 * @since 2.0.0
	 */
	public function __construct() {
		$widget_ops = array( 'description' => __( 'Add WP Idea Stream&#39;s nav to your sidebar.', 'wp-idea-stream' ) );
		parent::__construct( false, $name = __( 'WP Idea Stream Nav', 'wp-idea-stream' ), $widget_ops );

		// We need to wait for the ideas post type to be registered
		add_action( 'wp_idea_stream_init', array( $this, 'set_available_nav_items' ) );
	}

	/**
	 * Register the widget
	 *
	 * @since 2.0.0
	 */
	public static function register_widget() {
		register_widget( 'WP_Idea_Stream_Navig' );
	}

	/**
	 * Setup available nav items
	 *
	 * @since 2.0.0
	 */
	public function set_available_nav_items() {
		// construct nav
		$this->nav_items_available = wp_idea_stream_get_nav_items();

		/**
		 * @param array the available nav items
		 * @param string the widget's id base
		 */
		$this->nav_items_available = apply_filters( 'wp_idea_stream_widget_nav_items', $this->nav_items_available, $this->id_base );
	}

	/**
	 * Display the widget on front end
	 *
	 * @since 2.0.0
	 */
	public function widget( $args = array(), $instance = array() ) {
		// Default to all items
		$nav_items = array( 'idea_archive', 'addnew', 'current_user_profile' );

		if ( ! empty( $instance['nav_items'] ) ) {
			$nav_items = (array) $instance['nav_items'];
		}

		// No nav items to show !? Stop!
		if ( empty( $nav_items ) ) {
			return;
		}

		// Get selected Nav items
		$nav_items = array_intersect_key( $this->nav_items_available, array_flip( $nav_items ) );

		// Default to nothing
		$title = '';

		if ( ! empty( $instance['title'] ) ) {
			$title = apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base );
		}

		echo $args['before_widget'];

		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		// Display the IdeaStream Nav
		?>
		<div class="menu-ideastream-container">

			<ul class="menu">

				<?php foreach ( $nav_items as $key_nav => $nav_item ) :
					$current = '';

					if ( function_exists( 'wp_idea_stream_is_' . $key_nav ) &&  call_user_func( 'wp_idea_stream_is_' . $key_nav ) ) {
						$current = ' current-menu-item';
					}
				?>

				<li class="menu-item menu-item-type-post_type<?php echo $current;?>">

					<a href="<?php echo esc_url( $nav_item['url'] );?>" title="<?php echo esc_attr( $nav_item['title'] );?>"><?php echo esc_html( $nav_item['title'] ); ?></a>

				</li>

				<?php endforeach; ?>

			</ul>

		</div>
		<?php

		echo $args['after_widget'];
	}

	/**
	 * Update widget preferences
	 *
	 * @since 2.0.0
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();

		if ( ! empty( $new_instance['title'] ) ) {
			$instance['title'] = strip_tags( wp_unslash( $new_instance['title'] ) );
		}

		$instance['nav_items'] = (array) $new_instance['nav_items'];

		return $instance;
	}

	/**
	 * Display the form in Widgets Administration
	 *
	 * @since 2.0.0
	 */
	public function form( $instance ) {
		// Default to nothing
		$title = '';

		if ( isset( $instance['title'] ) ) {
			$title = $instance['title'];
		}

		// Default to all nav items
		$nav_items = array( 'idea_archive', 'addnew', 'current_user_profile' );

		if ( ! empty( $instance['nav_items'] ) && is_array( $instance['nav_items'] ) ) {
			$nav_items = $instance['nav_items'];
		}
		?>

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php esc_html_e( 'Title:', 'wp-idea-stream' ) ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( $title ); ?>" />
		</p>

		<p>

			<?php foreach ( $this->nav_items_available as $key_item => $item ) : ?>

				<input class="checkbox" type="checkbox" <?php checked( in_array( $key_item, $nav_items), true) ?> id="<?php echo $this->get_field_id( 'nav_items' ) . '-' . $key_item; ?>" name="<?php echo $this->get_field_name( 'nav_items' ); ?>[]" value="<?php echo esc_attr( $key_item );?>" />
				<label for="<?php echo $this->get_field_id( 'nav_items' ) . '-' . $key_item; ?>"><?php echo esc_html( $item['title'] ); ?></label><br />

			<?php endforeach; ?>

		</p>

		<?php
	}
}
