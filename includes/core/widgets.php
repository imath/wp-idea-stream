<?php
/**
 * WP Idea Stream Widgets.
 *
 * Core Widgets
 *
 * @package WP Idea Stream
 * @subpackage core/widgets
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WP_Idea_Stream_Navig' ) ) :
/**
 * IdeaStream Navigation Menu widget class
 *
 * @package WP Idea Stream
 * @subpackage core/widgets
 *
 * @since 2.0.0
 */
 class WP_Idea_Stream_Navig extends WP_Widget {

 	/**
	 * Constructor
	 *
	 * @package WP Idea Stream
	 * @subpackage core/widgets
	 *
	 * @since 2.0.0
	 *
	 * @uses WP_Widget::__construct()
	 * @uses add_action() to hook to plugin's action and set the nav items
	 */
	public function __construct() {
		$widget_ops = array( 'description' => __( 'Add IdeaStream&#39;s nav to your sidebar.', 'wp-idea-stream' ) );
		parent::__construct( false, $name = __( 'IdeaStream Nav', 'wp-idea-stream' ), $widget_ops );

		// We need to wait for the ideas post type to be registered
		add_action( 'wp_idea_stream_init', array( $this, 'set_available_nav_items' ) );
	}

	/**
	 * Register the widget
	 *
	 * @package WP Idea Stream
	 * @subpackage core/widgets
	 *
	 * @since 2.0.0
	 *
	 * @uses   register_widget() to register the widget
	 */
	public static function register_widget() {
		register_widget( 'WP_Idea_Stream_Navig' );
	}

	/**
	 * Setup available nav items
	 *
	 * @package WP Idea Stream
	 * @subpackage core/widgets
	 *
	 * @since 2.0.0
	 *
	 * @uses  wp_idea_stream_get_root_url() to get the root url
	 * @uses  wp_idea_stream_archive_title() to get the name of the archive page
	 * @uses  wp_idea_stream_get_form_url() to get the url of the add new form
	 * @uses  is_user_logged_in() to check we have a logged in user
	 * @uses  wp_idea_stream_users_get_logged_in_profile_url() to get current user's profile url
	 * @uses  apply_filters() call 'wp_idea_stream_widget_nav_items' to allow overrides
	 */
	public function set_available_nav_items() {
		// construct nav
		$this->nav_items_available = array(
			'idea_archive' => array(
				'url'  => wp_idea_stream_get_root_url(),
				'name' => wp_idea_stream_archive_title()
			),
			'addnew'       => array(
				'url'  => wp_idea_stream_get_form_url(),
				'name' => __( 'New idea', 'wp-idea-stream' )
			)
		);

		if ( is_user_logged_in() ) {
			$this->nav_items_available['current_user_profile'] = array(
				'url'  => wp_idea_stream_users_get_logged_in_profile_url(),
				'name' => __( 'My profile', 'wp-idea-stream' )
			);
		}

		/**
		 * @param array the available nav items
		 * @param string the widget's id base
		 */
		$this->nav_items_available = apply_filters( 'wp_idea_stream_widget_nav_items', $this->nav_items_available, $this->id_base );
	}

	/**
	 * Display the widget on front end
	 *
	 * @package WP Idea Stream
	 * @subpackage core/widgets
	 *
	 * @since 2.0.0
	 *
	 * @uses  apply_filters() call 'widget_title' to allow the widget title to edited
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

					<a href="<?php echo esc_url( $nav_item['url'] );?>" title="<?php echo esc_attr( $nav_item['name'] );?>"><?php echo esc_html( $nav_item['name'] ); ?></a>

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
	 * @package WP Idea Stream
	 * @subpackage core/widgets
	 *
	 * @since 2.0.0
	 *
	 * @uses  wp_unslash()
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
	 * @package WP Idea Stream
	 * @subpackage core/widgets
	 *
	 * @since 2.0.0
	 *
	 * @uses WP_Widget->get_field_id()
	 * @uses WP_Widget->get_field_name()
	 * @uses checked()
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
				<label for="<?php echo $this->get_field_id( 'nav_items' ) . '-' . $key_item; ?>"><?php echo esc_html( $item['name'] ); ?></label><br />

			<?php endforeach; ?>

		</p>

		<?php
	}
}

endif;
