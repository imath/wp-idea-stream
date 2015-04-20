<?php
/**
 * WP Idea Stream Users tags.
 *
 * Template tags specific to users
 *
 * @package WP Idea Stream
 * @subpackage users/tags
 *
 * @since 2.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Outputs user's profile nav
 *
 * @package WP Idea Stream
 * @subpackage users/tags
 *
 * @since 2.0.0
 *
 * @uses wp_idea_stream_users_get_user_nav() to get the nav
 */
function wp_idea_stream_users_the_user_nav() {
	echo wp_idea_stream_users_get_user_nav();
}

	/**
	 * Gets user's profile nav
	 *
	 * @package WP Idea Stream
	 * @subpackage users/tags
	 *
	 * @since 2.0.0
	 *
	 * @uses wp_idea_stream_users_displayed_user_id() to get displayed user ID
	 * @uses wp_idea_stream_users_get_displayed_user_username() to get displayed username
	 * @uses wp_idea_stream_users_get_profile_nav_items() to get displayed user nav items
	 * @uses apply_filters() call 'wp_idea_stream_users_get_user_nav' to override output
	 */
	function wp_idea_stream_users_get_user_nav() {
		// Get displayed user id.
		$user_id = wp_idea_stream_users_displayed_user_id();

		// If not set, we're not on a user's profile.
		if ( empty( $user_id ) ) {
			return;
		}

		// Get username.
		$username = wp_idea_stream_users_get_displayed_user_username();

		// Get nav items for the user displayed.
		$nav_items = wp_idea_stream_users_get_profile_nav_items( $user_id, $username );

		if ( empty( $nav_items ) ) {
			return;
		}

		$user_nav = '<ul class="user-nav">';

		foreach ( $nav_items as $nav_item ) {
			$class =  ! empty( $nav_item['current'] ) ? ' class="current"' : '';
			$user_nav .= '<li' . $class .'>';
			$user_nav .= '<a href="' . esc_url( $nav_item['url'] ) . '" title="' . esc_attr( $nav_item['title'] ) . '">' . esc_html( $nav_item['title'] ) . '</a>';
			$user_nav .= '</li>';
		}

		$user_nav .= '</ul>';

		/**
		 * Filter the user nav output
		 *
		 * @param string $user_nav      User nav output
		 * @param int    $user_id       the user ID
		 * @param string $user_nicename the username
		 */
		return apply_filters( 'wp_idea_stream_users_get_user_nav', $user_nav, $user_id, $username );
	}

/**
 * Outputs user's profile avatar
 *
 * @package WP Idea Stream
 * @subpackage users/tags
 *
 * @since 2.0.0
 *
 * @uses wp_idea_stream_users_get_user_profile_avatar() to get the avatar
 */
function wp_idea_stream_users_the_user_profile_avatar() {
	echo wp_idea_stream_users_get_user_profile_avatar();
}

	/**
	 * Gets user's profile avatar
	 *
	 * @package WP Idea Stream
	 * @subpackage users/tags
	 *
	 * @since 2.0.0
	 *
	 * @uses get_avatar() to fetch user's avatar
	 * @uses wp_idea_stream_users_displayed_user_id() to get displayed user ID
	 * @uses apply_filters() call 'wp_idea_stream_users_get_user_profile_avatar' to override output
	 */
	function wp_idea_stream_users_get_user_profile_avatar() {
		return apply_filters( 'wp_idea_stream_users_get_user_profile_avatar', get_avatar( wp_idea_stream_users_displayed_user_id(), '150' ) );
	}

/**
 * Outputs user's profile description
 *
 * @package WP Idea Stream
 * @subpackage users/tags
 *
 * @since 2.0.0
 *
 * @uses wp_idea_stream_users_get_user_profile_description() to get the description
 */
function wp_idea_stream_users_the_user_profile_description() {
	echo wp_idea_stream_users_get_user_profile_description();
}

	/**
	 * Gets user's profile description
	 *
	 * @package WP Idea Stream
	 * @subpackage users/tags
	 *
	 * @since 2.0.0
	 *
	 * @uses wp_idea_stream_users_get_displayed_user_displayname() get displayed user display name
	 * @uses wp_idea_stream_is_current_user_profile() to check current user is viewing his profile
	 * @uses wp_idea_stream_users_get_displayed_user_description() to get user's descripton
	 * @uses wp_kses_allowed_html() to get allowed tags for user's description
     * @uses wp_kses to sanitize user's descripton
	 * @uses apply_filters() call 'wp_idea_stream_users_get_{$self}user_profile_description' to override output
	 */
	function wp_idea_stream_users_get_user_profile_description() {
		$display_name = wp_idea_stream_users_get_displayed_user_displayname();
		$self = '';
		$is_self_profile = wp_idea_stream_is_current_user_profile();

		$user_description = sprintf( esc_html__( '%s has not created his description yet', 'wp-idea-stream' ), $display_name );

		if ( ! empty( $is_self_profile ) ) {
			$user_description = esc_html__( 'Replace this text with your description, then hit the Edit button to save it.', 'wp-idea-stream' );
		}

		$description = wp_idea_stream_users_get_displayed_user_description();

		if ( ! empty( $description ) ) {
			$allowed_html = wp_kses_allowed_html( 'user_description' );
			$user_description = wp_kses( $description, $allowed_html );
		}

		$output = '<div class="user-description">';


		if ( ! empty( $is_self_profile ) ) {
			$output .= '<form action="" method="post" id="wp_idea_stream_profile_form" class="user-profile-form">';
		}

		$output .= '<blockquote>';

		if ( ! empty( $is_self_profile ) ) {
			$self = 'self_';
			$output .= '<div id="wp_idea_stream_profile_description" contenteditable="true">';
		}

		/**
		 * Use 'wp_idea_stream_users_get_user_profile_description' to filter description when the current user
		 * is viewing someone else profile
		 * Use 'wp_idea_stream_users_get_self_user_profile_description' to filter description when the current user
		 * is viewing his profile
		 *
		 * @param string $user_description User description
		 */
		$user_description = apply_filters( "wp_idea_stream_users_get_{$self}user_profile_description", $user_description );

		// Add desciption to the output
		$output .= $user_description;

		if ( ! empty( $is_self_profile ) ) {
			$output .= '</div>';
		}

		$output .= '</blockquote>';

		// Fall back is javscript's going wild
		if ( ! empty( $is_self_profile ) ) {
			$output .= '<textarea name="wp_idea_stream_profile[description]">' . $user_description . '</textarea>';
			$output .= wp_nonce_field( 'wp_idea_stream_update_description', '_wpis_nonce', true , false );
			$output .= '<input type="submit" name="wp_idea_stream_profile[save]" value="' . esc_attr_x( 'Edit', 'User profile description edit', 'wp-idea-stream' ) . '"/></form>';
		}

		$output .= '</div>';

		return $output;
	}

/**
 * Append displayed user's rating in ideas header when viewing his rates profile
 *
 * @package WP Idea Stream
 * @subpackage users/tags
 *
 * @since 2.0.0
 *
 * @param int $id      the idea ID
 * @param int $user_id the user ID
 * @uses  wp_idea_stream_users_get_user_idea_rating() to get the description
 */
function wp_idea_stream_users_the_user_idea_rating( $id = 0, $user_id = 0 ) {
	echo wp_idea_stream_users_get_user_idea_rating( $id, $user_id );
}

	/**
	 * Gets displayed user's rating for a given idea
	 *
	 * @package WP Idea Stream
	 * @subpackage users/tags
	 *
	 * @since 2.0.0
	 *
	 * @param int $id      the idea ID
	 * @param int $user_id the user ID
	 * @uses  wp_idea_stream_is_user_profile_rates() to check we're on the rates part of a user's profile
	 * @uses  wp_idea_stream_get_idea_var() to get a globalized value
	 * @uses  wp_idea_stream_users_displayed_user_id() to get displayed user's ID
	 * @uses  wp_idea_stream_users_get_displayed_user_username() to get displayed user's username
     * @uses  wp_idea_stream_users_get_user_profile_url() to get user's profile url
     * @uses  get_avatar() to get user's avatar
	 * @uses  apply_filters() call 'wp_idea_stream_users_get_user_idea_rating' to override output
	 */
	function wp_idea_stream_users_get_user_idea_rating( $id = 0, $user_id = 0 ) {
		if ( ! wp_idea_stream_is_user_profile_rates() ) {
			return;
		}

		if ( empty( $id ) ) {
			$query_loop = wp_idea_stream_get_idea_var( 'query_loop' );

			if ( ! empty( $query_loop->idea->ID ) ) {
				$id = $query_loop->idea->ID;
			}
		}

		if ( empty( $user_id ) ) {
			$user_id = wp_idea_stream_users_displayed_user_id();
		}

		if ( empty( $user_id ) || empty( $id ) ) {
			return;
		}

		$user_rating = wp_idea_stream_count_ratings( $id, $user_id );

		if ( empty( $user_rating ) || is_array( $user_rating ) ) {
			return false;
		}

		$username = wp_idea_stream_users_get_displayed_user_username();

		$output = '<a class="user-rating-link" href="' . esc_url( wp_idea_stream_users_get_user_profile_url( $user_id, $username ) ) . '" title="' . esc_attr( $username ) . '">';
		$output .= get_avatar( $user_id, 20 ) . sprintf( _n( 'rated 1 star', 'rated %s stars', $user_rating, 'wp-idea-stream' ), $user_rating ) . '</a>';

		/**
		 * Filter the user idea rating output
		 *
		 * @param string $output        the rating
		 * @param int    $id            the idea ID
		 * @param int    $user_id       the user ID
		 */
		return apply_filters( 'wp_idea_stream_users_get_user_idea_rating', $output, $id, $user_id );
	}

function wp_idea_stream_users_the_signup_fields() {
	echo wp_idea_stream_users_get_signup_fields();
}
	function wp_idea_stream_users_get_signup_fields() {
		$output = '';

		foreach ( (array) wp_idea_stream_user_get_fields() as $key => $label ) {
			// reset
			$sanitized = array(
				'key'   => sanitize_key( $key ),
				'label' => esc_html( $label ),
				'value' => '',
			);

			if ( ! empty( $_POST['wp_idea_stream_signup'][ $sanitized['key'] ] ) ) {
				$sanitized['value'] = apply_filters( "wp_idea_stream_users_get_signup_field_{$key}", $_POST['wp_idea_stream_signup'][ $sanitized['key'] ] );
			}

			$required = apply_filters( 'wp_idea_stream_users_is_signup_field_required', false, $key );
			$required_output = false;

			if ( ! empty( $required ) || in_array( $key, array( 'user_login', 'user_email' ) ) ) {
				$required_output = '<span class="required">*</span>';
			}

			$output .= '<label for="_wp_idea_stream_signup_' . esc_attr( $sanitized['key'] ) . '">' . esc_html( $sanitized['label'] ) . ' ' . $required_output . '</label>';
			$output .= '<input type="text" id="_wp_idea_stream_signup_' . esc_attr( $sanitized['key'] ) . '" name="wp_idea_stream_signup[' . esc_attr( $sanitized['key'] ) . ']" value="' . esc_attr( $sanitized['value'] ) . '"/>';

			$output .= apply_filters( 'wp_idea_stream_users_after_signup_field', '', $sanitized );
		}

		return apply_filters( 'wp_idea_stream_users_get_signup_fields', $output );
	}

function wp_idea_stream_users_the_signup_submit() {
	$wp_idea_stream = wp_idea_stream();

	wp_nonce_field( 'wp_idea_stream_signup' );

	do_action( 'wp_idea_stream_users_the_signup_submit' ); ?>

	<input type="reset" value="<?php esc_attr_e( 'Reset', 'wp-idea-stream' ) ;?>"/>
	<input type="submit" value="<?php esc_attr_e( 'Sign-up', 'wp-idea-stream' ) ;?>" name="wp_idea_stream_signup[signup]"/>
	<?php
}
