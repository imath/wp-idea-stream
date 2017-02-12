<?php
/**
 * WP Idea Stream Users tags.
 *
 * Template tags specific to users
 *
 * @package WP Idea Stream\users
 *
 * @since 2.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Outputs user's profile nav
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
	 * @since 2.0.0
	 *
	 * @return  string The user's profile nav.
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
 * Outputs user's embed profile stats
 *
 * @since 2.3.0
 */
function wp_idea_stream_users_embed_user_stats() {
	echo wp_idea_stream_users_get_embed_user_stats();
}

	/**
	 * Gets user's embed profile stats
	 *
	 * @since 2.3.0
	 *
	 * @return string HTML Output
	 */
	function wp_idea_stream_users_get_embed_user_stats() {
		// Get displayed user id.
		$user_id = wp_idea_stream_users_displayed_user_id();

		// If not set, we're not on a user's profile.
		if ( empty( $user_id ) ) {
			return;
		}

		// Get username.
		$username = wp_idea_stream_users_get_displayed_user_username();

		/**
		 * Get nav items for the user displayed to build the user stats.
		 *
		 * @since 2.3.0
		 *
		 * @param array $value the nav items that will be used for the embed stats
		 */
		$nav_items = apply_filters( 'wp_idea_stream_users_get_embed_user_stats', wp_idea_stream_users_get_profile_nav_items( $user_id, $username, true ) );

		if ( empty( $nav_items ) ) {
			return;
		}

		$user_stats = '<ul class="user-stats">';

		foreach ( $nav_items as $key_nav => $nav_item ) {
			$user_stats .= '<li class=' . sanitize_html_class( $key_nav ) . '>';
			$stat_title  = sprintf( _x( '%s ideas', 'embed profile type of stat', 'wp-idea-stream' ), $nav_item['title'] );
			$dashicon    = 'ideastream-' . esc_attr( $key_nav );

			if ( 'comments' === $key_nav ) {
				$dashicon = 'dashicons-admin-comments';
			}

			$user_stats .= '<div class="stat-label"><a href="' . esc_url( $nav_item['url'] ) . '" title="' . esc_attr( $stat_title ) . '"><span class="dashicons ' . $dashicon . '"></span><span class="screen-reader-text">' . esc_html( $stat_title ) . '</span></a></div>';
			$user_stats .= '<div class="stat-value"><a href="' . esc_url( $nav_item['url'] ) . '" title="' . esc_attr( $stat_title ) . '">' . wp_idea_stream_users_get_stat_for( $key_nav, $user_id ) . '</a></span>';
			$user_stats .= '</li>';
		}

		$user_stats .= '</ul>';

		/**
		 * Filter the embed stats output
		 *
		 * @since  2.3.0
		 *
		 * @param string $user_stats    User stats output
		 * @param int    $user_id       the user ID
		 * @param string $user_nicename the username
		 */
		return apply_filters( 'wp_idea_stream_users_get_embed_user_stats_output', $user_stats, $user_id, $username );
	}

/**
 * Outputs user's profile avatar
 *
 * @since 2.0.0
 */
function wp_idea_stream_users_the_user_profile_avatar() {
	echo wp_idea_stream_users_get_user_profile_avatar();
}

	/**
	 * Gets user's profile avatar
	 *
	 * @since 2.0.0
	 *
	 * @return  string the user's avatar.
	 */
	function wp_idea_stream_users_get_user_profile_avatar() {
		return apply_filters( 'wp_idea_stream_users_get_user_profile_avatar', get_avatar( wp_idea_stream_users_displayed_user_id(), '150' ) );
	}


/**
 * Outputs user's embed profile avatar
 *
 * @since 2.3.0
 */
function wp_idea_stream_users_embed_user_profile_avatar() {
	echo wp_idea_stream_users_get_embed_user_profile_avatar();
}

	/**
	 * Gets user's embed profile avatar
	 *
	 * @since 2.3.0
	 *
	 * @return  string the user's avatar for the embed profile.
	 */
	function wp_idea_stream_users_get_embed_user_profile_avatar() {
		return apply_filters( 'wp_idea_stream_users_get_embed_user_profile_avatar', get_avatar( wp_idea_stream_users_displayed_user_id(), '50' ) );
	}

/**
 * Outputs user's embed profile display name
 *
 * @since 2.3.0
 */
function wp_idea_stream_users_embed_user_profile_display_name() {
	echo wp_idea_stream_users_get_embed_user_profile_display_name();
}

	/**
	 * Gets user's embed profile display name
	 *
	 * @since 2.3.0
	 *
	 * @return  string the user's display name.
	 */
	function wp_idea_stream_users_get_embed_user_profile_display_name() {
		return esc_html( apply_filters( 'wp_idea_stream_users_get_embed_user_profile_display_name', wp_idea_stream_users_get_displayed_user_displayname() ) );
	}

/**
 * Outputs user's embed profile link
 *
 * @since 2.3.0
 */
function wp_idea_stream_users_embed_user_profile_link() {
	echo esc_url( wp_idea_stream_users_get_embed_user_profile_link() );
}

	/**
	 * Gets user's embed profile link
	 *
	 * @since 2.3.0
	 *
	 * @return string the user's profile link for the embed profile
	 */
	function wp_idea_stream_users_get_embed_user_profile_link() {
		$link = wp_idea_stream_users_get_user_profile_url( wp_idea_stream_users_displayed_user_id(), wp_idea_stream_users_get_displayed_user_username() );
		return apply_filters( 'wp_idea_stream_users_get_embed_user_profile_display_name', $link );
	}

/**
 * Outputs user's profile description
 *
 * @since 2.0.0
 */
function wp_idea_stream_users_the_user_profile_description() {
	echo wp_idea_stream_users_get_user_profile_description();
}

	/**
	 * Gets user's profile description
	 *
	 * @since 2.0.0
	 *
	 * @return string the User's description.
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
 * Does the user's embed profile has a description ?
 *
 * @since 2.3.0
 *
 * @return bool True if the embed profile has a description, False otherwise.
 */
function wp_idea_stream_users_has_embed_description() {
	return (bool) wp_idea_stream_users_get_displayed_user_description();
}

/**
 * Outputs user's embed profile description
 *
 * @since 2.3.0
 *
 * @uses wp_idea_stream_users_get_embed_user_profile_description() to get the description
 */
function wp_idea_stream_users_embed_user_profile_description() {
	echo wp_idea_stream_users_get_embed_user_profile_description();
}

	/**
	 * Get the user's embed profile description
	 *
	 * @since 2.3.0
	 *
	 * @return string HTML Output
	 */
	function wp_idea_stream_users_get_embed_user_profile_description() {
		$description = wp_idea_stream_users_get_displayed_user_description();

		if ( ! empty( $description ) ) {
			$more = ' &hellip; ' . sprintf( '<a href="%1$s" class="wp-embed-more" target="_top">%2$s</a>',
				esc_url( wp_idea_stream_users_get_embed_user_profile_link() ),
				sprintf( esc_html__( "View %s full profile", 'wp-idea-stream' ), '<span class="screen-reader-text">' . sprintf( _x( '%s&#39;s', 'Screen reader text for embed user display name for the more link', 'wp-idea-stream' ), wp_idea_stream_users_get_embed_user_profile_display_name() ) . '</span>' )
			);

			$description = wp_idea_stream_create_excerpt( $description, 20, $more, true );
		}

		return apply_filters( 'wp_idea_stream_users_get_embed_user_profile_description', $description );
	}

/**
 * Append displayed user's rating in ideas header when viewing his rates profile
 *
 * @since 2.0.0
 *
 * @param int $id      the idea ID
 * @param int $user_id the user ID
 */
function wp_idea_stream_users_the_user_idea_rating( $id = 0, $user_id = 0 ) {
	echo wp_idea_stream_users_get_user_idea_rating( $id, $user_id );
}

	/**
	 * Gets displayed user's rating for a given idea
	 *
	 * @since 2.0.0
	 *
	 * @param  int $id      the idea ID
	 * @param  int $user_id the user ID
	 * @return string the displayed user's rating for a given idea.
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

/**
 * Display the signup fields
 *
 * @since 2.1.0
 */
function wp_idea_stream_users_the_signup_fields() {
	echo wp_idea_stream_users_get_signup_fields();
}

	/**
	 * Get the signup fields output.
	 *
	 * @since  2.1.0
	 *
	 * @return string HTML output for the signup fields.
	 */
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

/**
 * Display the Signup form submit output.
 *
 * @since  2.1.0
 *
 * @return string HTML Output.
 */
function wp_idea_stream_users_the_signup_submit() {
	$wp_idea_stream = wp_idea_stream();

	wp_nonce_field( 'wp_idea_stream_signup' );

	do_action( 'wp_idea_stream_users_the_signup_submit' ); ?>

	<input type="reset" value="<?php esc_attr_e( 'Reset', 'wp-idea-stream' ) ;?>"/>
	<input type="submit" value="<?php esc_attr_e( 'Sign-up', 'wp-idea-stream' ) ;?>" name="wp_idea_stream_signup[signup]"/>
	<?php
}

/**
 * Displays the Sharing button inside a user's profile
 *
 * So that WordPress can build the sharing dialog and embed codes
 * we need to temporarly set the Utility page as the current post
 * for the displayed user's profile.
 * Then, we intercept the permalink and the title of the utility page
 * using filters and we override them with the ones of the displayed
 * user profile. Tada!
 *
 * @since 2.3.0
 *
 * @global WP_Post $post
 * @return bool False in case the Embed profile is disabled
 */
function wp_idea_stream_users_embed_content_meta() {
	global $post;

	$reset_post = get_post( wp_idea_stream_is_embed_profile() );

	if ( ( ! empty( $post->ID ) && ! empty( $reset_post->ID ) && (int) $post->ID === (int) $reset_post->ID ) || empty( $reset_post->ID ) ) {
		return false;
	}

	if ( ! empty( $reset_post->ID ) ) {
		// Globalize the post
		wp_idea_stream_set_idea_var( 'embed_reset_post', $post );

		// Reset it to our utility page
		$post = $reset_post;

		// Globalize the user
		wp_idea_stream_set_idea_var( 'embed_user_data', wp_idea_stream_users_get_user_data( 'id', wp_idea_stream_users_displayed_user_id() ) );

		// Make sure the post link will be the one of the user's displayed profile
		add_filter( 'post_type_link', 'wp_idea_stream_users_oembed_link',  10, 2 );
		add_filter( 'the_title',      'wp_idea_stream_users_oembed_title', 10, 2 );

		add_action( 'embed_footer', 'wp_idea_stream_users_embed_content_reset_post', 40 );

		// Add WordPress Sharing Button
		print_embed_sharing_button();
	}
}

/**
 * Make sure to play nice with WordPress by resetting the post global the way it was
 * before overriding it with our utility page.
 *
 * @since 2.3.0
 *
 * @global WP_Post $post
 */
function wp_idea_stream_users_embed_content_reset_post() {
	global $post;

	// Reset post the way it was.
	$post = wp_idea_stream_get_idea_var( 'embed_reset_post' );

	// Reset the embed user & post
	wp_idea_stream_set_idea_var( 'embed_user_data', null );
	wp_idea_stream_set_idea_var( 'embed_reset_post', null );

	// Stop filtering...
	remove_filter( 'post_type_link', 'wp_idea_stream_users_oembed_link',  10, 2 );
	remove_filter( 'the_title',      'wp_idea_stream_users_oembed_title', 10, 2 );
}

/**
 * Displays the sharing dialog box on user's profile so
 * that people can easily get the embed code.
 *
 * @since 2.3.0
 */
function wp_idea_stream_users_sharing_button() {
	// No need to carry on.
	if ( ! wp_idea_stream_is_embed_profile() ) {
		return;
	}

	wp_idea_stream_users_embed_content_meta();

	// Print the sharing dialog
	print_embed_sharing_dialog();

	// Reset the post
	wp_idea_stream_users_embed_content_reset_post();
}
add_action( 'wp_idea_stream_user_profile_after_description', 'wp_idea_stream_users_sharing_button', 10 );
