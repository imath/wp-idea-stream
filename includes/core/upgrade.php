<?php
/**
 * WP Idea Stream Upgrade functions.
 *
 * Mainly Inspired by bbPress
 *
 * @package WP Idea Stream\core
 *
 * @since 2.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Compares the current plugin version to the DB one to check if it's an upgrade
 *
 * @since 2.0.0
 *
 * @return bool True if update, False if not
 */
function wp_idea_stream_is_upgrade() {
	$db_version     = wp_idea_stream_db_version();
	$plugin_version = wp_idea_stream_get_version();

	return (bool) version_compare( $db_version, $plugin_version, '<' );
}

/**
 * Checks if an upgrade is needed
 *
 * @since 2.0.0
 */
function wp_idea_stream_maybe_upgrade() {
	// Bail if no update needed
	if ( ! wp_idea_stream_is_upgrade() ) {
		return;
	}

	// Let's upgrade!
	wp_idea_stream_upgrade();
}

/**
 * Upgrade routine
 *
 * @since 2.0.0
 */
function wp_idea_stream_upgrade() {
	$db_version = wp_idea_stream_db_version();

	if ( ! empty( $db_version ) ) {
		if ( (int) $db_version < 2 ) {
			// Filter default options to take in account legacy options
			add_filter( 'wp_idea_stream_get_default_options', 'wp_idea_stream_merge_legacy_options', 10, 1 );

			wp_idea_stream_add_options();

		// Upgrade to 2.3
		} elseif ( version_compare( $db_version, '2.3.0', '<' ) ) {
			wp_idea_stream_upgrade_to_2_3();

		// Upgrade to 2.4
		} elseif ( version_compare( $db_version, '2.4.0', '<' ) ) {
			wp_idea_stream_upgrade_to_2_4();

		// Upgrade to 2.5
		} elseif ( version_compare( $db_version, '2.5.0', '<' ) ) {
			wp_idea_stream_upgrade_to_2_5();
		}

		// Make sure the changelog will be displayed at next page load
		set_transient( '_ideastream_upgrade_redirect', wp_idea_stream_get_version(), 30 );

	// It's a new install
	} else {
		wp_idea_stream_install();
	}

	update_option( '_ideastream_version', wp_idea_stream_get_version() );

	// Force a rewrite rules reset
	wp_idea_stream_delete_rewrite_rules();
}

/**
 * Merge legacy options and do some clean up
 *
 * @since 2.0.0
 */
function wp_idea_stream_merge_legacy_options( $default_options = array() ) {
	// First, as previously root slug was "is", let's keep it to avoid 404 in tag & category archive
	if ( ! empty ( $default_options['_ideastream_root_slug'] ) ) {
		$default_options['_ideastream_root_slug'] = 'is';
	}

	$options_to_remove = array(
		'_ideastream_builtin_rating',
		'_ideastream_sharing_options',
		'_ideastream_twitter_account',
		'_ideastream_feature_from_comments',
		'_ideastream_allowed_featuring_members',
		'_ideastream_editor_config',
		'_ideastream_vestion',
		'_ideastream_image_width'
	);

	$editor_settings = get_option( '_ideastream_editor_config' );

	if ( ! empty( $editor_settings ) && is_array( $editor_settings ) ) {

		if ( empty( $editor_settings['image'] ) ) {
			$default_options['_ideastream_editor_image'] = 0;
		}

		if ( empty( $editor_settings['link'] ) ) {
			$default_options['_ideastream_editor_link'] = 0;
		}
	}

	foreach ( $options_to_remove as $to_remove ) {
		delete_option( $to_remove );
	}

	$options_to_keep = array(
		'_ideastream_version', // Will be updated at a later time
		'_ideastream_submit_status',
		'_ideastream_moderation_message',
		'_ideastream_login_message',
		'_ideastream_hint_list'
	);

	foreach ( $options_to_keep as $to_keep ) {
		unset( $default_options[ $to_keep ] );
	}

	$notice = array( 'admin_notices' => array(
		sprintf( esc_html__( 'Please take a few minutes to read the WP Idea Stream %s page: version 2.0.0 introduced some new features and stopped supporting some others.', 'wp-idea-stream' ),
			'<a href="' . esc_url( add_query_arg( array( 'page' => 'about-ideastream' ), admin_url( 'index.php' ) ) ) . '">' . esc_html__( 'About', 'wp-idea-stream' ) . '</a>'
		)
	) );

	// Check if page on front is set to all-ideas
	if ( 'all-ideas' == get_option( 'page_on_front' ) ) {
		update_option( 'page_on_front', false );

		// Bring back posts to front
		update_option( 'show_on_front', 'posts' );

		// Add a notice
		$notice['admin_notices'][] = esc_html__( 'For instance, having the ideas directly on your site&#39;s front page is no more supported in 2.0.0, sorry.', 'wp-idea-stream' );
	}

	wp_idea_stream_set_idea_var( 'feedback', $notice );

	return $default_options;
}

/**
 * First install routine
 *
 * @since 2.3.0
 */
function wp_idea_stream_install() {
	/**
	 * Filter here if you need to init options in DB
	 *
	 * @since 2.3.0
	 *
	 * @param array $value list of options to init on install
	 */
	$init_options = apply_filters( 'wp_idea_stream_install_init_options', array( '_ideastream_embed_profile' => 1 )  );

	foreach ( $init_options as $key => $value ) {
		add_option( $key, $value );
	}

	/**
	 * Hook here if you need to perform actions when plugin
	 * is installed for the first time
	 *
	 * @since 2.3.0
	 */
	do_action( 'wp_idea_stream_installed' );
}

/**
 * Create the utility page for embed profile.
 * Loop through each rating to use non numeric keys for the list of User IDs
 *
 * See https://github.com/imath/wp-idea-stream/issues/35
 *
 * @since 2.3.0
 */
function wp_idea_stream_upgrade_to_2_3() {
	global $wpdb;

	// First create the utility page
	add_option( '_ideastream_embed_profile', 1 );

	/**
	 * Then init featured images setting, if image editor
	 * is disabled, it will also disable featured images
	 */
	add_option( '_ideastream_featured_images', 1 );

	// Then fix the user votes
	$rates = $wpdb->get_results( "SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_ideastream_rates'" );

	// No upgrade needed.
	if ( empty( $rates ) ) {
		return;
	}

	foreach ( $rates as $rate ) {
		$meta = maybe_unserialize( $rate->meta_value );

		// Loop in each vote
		foreach ( $meta as $vote => $users ) {
			$new_user_ids = array();

			// Rebuild the user ids so that non numeric keys are used
			foreach ( $users['user_ids'] as $user_id ) {
				$new_user_ids['u-' . $user_id] = $user_id;
			}

			$meta[ $vote ]['user_ids'] = $new_user_ids;
		}

		update_post_meta( $rate->post_id, '_ideastream_rates', $meta );
	}
}

/**
 * Upgrade routine for 2.4.0
 *
 * @since 2.4.0
 */
function wp_idea_stream_upgrade_to_2_4() {
	delete_option( '_ideastream_buddypress_integration' );
}

/**
 * Upgrade routine for 2.5.0
 *
 * @since 2.5.0
 */
function wp_idea_stream_upgrade_to_2_5() {
	wp_idea_stream_refresh_editor_styles();
}

/**
 * Redirect to the Changelog Screen after upgrade
 *
 * @since 2.4.0
 *
 * @param  string $transient The transient name.
 * @return mixed             The transient value. False if empty.
 */
function wp_idea_stream_needs_changelog_display( $transient = '' ) {
	if ( empty( $transient ) ) {
		return false;
	}

	$needs_changelog_display = get_transient( $transient );

	// Bail if no activation redirect
	if ( empty( $needs_changelog_display ) ) {
		return false;
	}

	// Delete the redirect transient
	delete_transient( $transient );

	// Bail if activating from network, or bulk
	if ( is_network_admin() || isset( $_GET['activate-multi'] ) ) {
		return false;
	}

	// Bail if the current user cannot see the about page
	if ( ! wp_idea_stream_user_can( 'manage_options' ) ) {
		return false;
	}

	return $needs_changelog_display;
}

/**
 * Redirect to the Changelog Screen after activation
 *
 * @since 2.0.0
 */
function wp_idea_stream_activation_redirect() {
	if ( ! wp_idea_stream_needs_changelog_display( '_ideastream_activation_redirect' ) ) {
		return;
	}

	// Redirect to WP Idea Stream changelog page
	wp_safe_redirect( add_query_arg( array( 'page' => 'about-ideastream' ), admin_url( 'index.php' ) ) );
}

/**
 * Redirect to the Changelog Screen after upgrade
 *
 * @since 2.4.0
 */
function wp_idea_stream_upgrade_redirect() {
	if ( ! wp_idea_stream_needs_changelog_display( '_ideastream_upgrade_redirect' ) ) {
		return;
	}

	// Redirect to WP Idea Stream changelog page
	wp_safe_redirect( add_query_arg( array(
		'page'       => 'about-ideastream',
		'is_upgrade' => wp_idea_stream_get_version(),
	), admin_url( 'index.php' ) ) );
}
