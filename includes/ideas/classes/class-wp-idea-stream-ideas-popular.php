<?php
/**
 * WP Idea Stream Ideas Popular widget Class.
 *
 * @package WP Idea Stream\ideas\classes
 *
 * @since 2.4.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * List the most popular ideas
 *
 * Popularity can be the average rate for some, or
 * the number of comments for others.. I guess tracking
 * page views would be another way to measure popularity..
 * But that's not supported and i doubt, i'll adventure
 * in this way in the future.
 *
 * @since 2.0.0
 */
 class WP_Idea_Stream_Ideas_Popular extends WP_Widget {

 	/**
	 * Constructor
	 *
	 * @since 2.0.0
	 */
	public function __construct() {
		$widget_ops = array( 'description' => __( 'List the most popular ideas', 'wp-idea-stream' ) );
		parent::__construct( false, $name = __( 'WP Idea Stream Popular Ideas', 'wp-idea-stream' ), $widget_ops );
	}

	/**
	 * Register the widget
	 *
	 * @since 2.0.0
	 */
	public static function register_widget() {
		register_widget( 'WP_Idea_Stream_Ideas_Popular' );
	}

	/**
	 * Display the widget on front end
	 *
	 * @since 2.0.0
	 */
	public function widget( $args = array(), $instance = array() ) {
		// Default to comment_count
		$orderby = 'comment_count';

		if ( ! empty( $instance['orderby'] ) ) {
			$orderby = $instance['orderby'];
		}

		// Default per_page is 5
		$number = 5;

		// No nav items to show !? Stop!
		if ( ! empty( $instance['number'] ) ) {
			$number = (int) $instance['number'];
		}

		// Default title is nothing
		$title = '';

		if ( ! empty( $instance['title'] ) ) {
			$title = apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base );
		}

		echo $args['before_widget'];

		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		// Popular argumments.
		$idea_args = apply_filters( 'wp_idea_stream_ideas_popular_args', array(
			'per_page'  => $number,
			'orderby'   => $orderby,
			'is_widget' => true,
		) );

		if ( 'rates_count' == $orderby ) {
			wp_idea_stream_set_idea_var( 'rating_widget', true );
		}

		// Display the popular ideas
		if ( wp_idea_stream_ideas_has_ideas( $idea_args ) ) : ?>

		<ul>

			<?php while ( wp_idea_stream_ideas_the_ideas() ) : wp_idea_stream_ideas_the_idea(); ?>

				<li>
					<a href="<?php wp_idea_stream_ideas_the_permalink();?>" title="<?php wp_idea_stream_ideas_the_title_attribute(); ?>"><?php wp_idea_stream_ideas_the_title(); ?></a>
					<span class="count">
						<?php if ( 'comment_count' == $orderby ) :?>
							(<?php wp_idea_stream_ideas_the_comment_number();?>)
						<?php else : ?>
							(<?php wp_idea_stream_ideas_the_average_rating();?>)
						<?php endif ;?>
					</span>
				</li>

		<?php endwhile ;

		// Reset post data
		wp_idea_stream_maybe_reset_postdata(); ?>

		</ul>
		<?php
		endif;

		if ( 'rates_count' == $orderby ) {
			wp_idea_stream_set_idea_var( 'rating_widget', false );
		}

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

		$instance['orderby'] = sanitize_text_field( $new_instance['orderby'] );
		$instance['number'] = (int) $new_instance['number'];

		return $instance;
	}

	/**
	 * Display the form in Widgets Administration
	 *
	 * @since 2.0.0
	 */
	public function form( $instance = array() ) {
		// Default to nothing
		$title = '';

		if ( isset( $instance['title'] ) ) {
			$title = $instance['title'];
		}

		// Available 'orderbys'
		$orderby = wp_idea_stream_ideas_get_order_options();

		// The date choice is default so let's unset it
		unset( $orderby['date'] );

		// comment count is default, as it's possible to deactivate ratings
		$current_order = 'comment_count';

		if ( ! empty( $instance['orderby'] ) ) {
			$current_order = sanitize_text_field( $instance['orderby'] );
		}

		// Number default to 5
		$number = 5;

		if ( ! empty( $instance['number'] ) ) {
			$number = absint( $instance['number'] );
		}
		?>

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php esc_html_e( 'Title:', 'wp-idea-stream' ) ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( $title ); ?>" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'orderby' ); ?>"><?php esc_html_e( 'Type:', 'wp-idea-stream' ) ?></label>
			<select class="widefat" id="<?php echo $this->get_field_id('orderby'); ?>" name="<?php echo $this->get_field_name('orderby'); ?>">

				<?php foreach ( $orderby as $key_order => $order_name ) : ?>

					<option value="<?php echo esc_attr( $key_order ) ?>" <?php selected( $key_order, $current_order ) ?>><?php echo esc_html( $order_name );?></option>

				<?php endforeach; ?>

			</select>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number of ideas to show:', 'wp-idea-stream' ); ?></label>
			<input id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="text" value="<?php echo $number; ?>" size="3" />
		</p>

		<?php
	}
}
