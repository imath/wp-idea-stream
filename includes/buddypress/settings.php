<?php
/**
 * WP Idea Stream BuddyPress integration : settings.
 *
 * BuddyPress / settings
 *
 * @package WP Idea Stream
 * @subpackage buddypress/settings
 *
 * @since  2.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Adds the BuddyPress Integration setting section to IdeaStream setting sections
 *
 * @package WP Idea Stream
 * @subpackage buddypress/settings
 *
 * @since  2.0.0
 *
 * @param  array  $settings_sections the IdeaStream setting sections
 * @return array                    the settings section with the BuddyPress one
 */
function wp_idea_stream_buddypress_settings_sections( $settings_sections = array() ) {
	$settings_sections['ideastream_settings_buddypress'] = array(
		'title'    => __( 'BuddyPress Integration Settings', 'wp-idea-stream' ),
		'callback' => 'wp_idea_stream_settings_buddypress_section_callback',
		'page'     => 'ideastream',
	);

	return $settings_sections;
}
add_filter( 'wp_idea_stream_get_settings_sections', 'wp_idea_stream_buddypress_settings_sections', 10, 1 );

/**
 * Adds the BuddyPress Integration setting field into the BuddyPress setting sections
 *
 * @package WP Idea Stream
 * @subpackage buddypress/settings
 *
 * @since  2.0.0
 *
 * @param  array  $setting_fields the IdeaStream setting fields
 * @uses   apply_filters() call 'wp_idea_stream_buddypress_settings_field' to add/remove setting fields
 * @return array                  the setting fields the BuddyPress Integration one
 */
function wp_idea_stream_buddypress_settings_field( $setting_fields = array() ) {
	$setting_fields['ideastream_settings_buddypress']['_ideastream_buddypress_integration'] = array(
		'title'             => __( 'BuddyPress', 'wp-idea-stream' ),
		'callback'          => 'wp_idea_stream_buddypress_setting_callback',
		'sanitize_callback' => 'absint',
		'args'              => array()
	);

	/**
	 * Used internally to let the BuddyPress group part of the plugin to add a setting field
	 *
	 * @param  array $setting_fields the IdeaStream setting fields
	 */
	return apply_filters( 'wp_idea_stream_buddypress_settings_field', $setting_fields );
}
add_filter( 'wp_idea_stream_get_settings_fields', 'wp_idea_stream_buddypress_settings_field', 10, 1 );

/**
 * Callback function for the BuddyPress settings section
 *
 * @package WP Idea Stream
 * @subpackage buddypress/settings
 *
 * @since  2.0.0
 */
function wp_idea_stream_settings_buddypress_section_callback() {
	?>

	<p><a name="buddypress"></a><?php esc_html_e( 'Customize the way WP Idea Stream should play with BuddyPress.', 'wp-idea-stream' ); ?></p>

	<?php
}

/**
 * Callback function for the BuddyPress Integration setting field
 *
 * @package WP Idea Stream
 * @subpackage buddypress/settings
 *
 * @since  2.0.0
 *
 * @uses  get_option() to get the BuddyPress integration setting
 * @uses  checked() to add a checked attribute to the checkbox if needed.
 */
function wp_idea_stream_buddypress_setting_callback() {
	$active = get_option( '_ideastream_buddypress_integration', 1 );
	?>

	<input name="_ideastream_buddypress_integration" id="_ideastream_buddypress_integration" type="checkbox" value="1" <?php checked( $active ); ?> />
	<label for="_ideastream_buddypress_integration"><?php esc_html_e( 'Activate BuddyPress integration', 'wp-idea-stream' ); ?></label>

	<?php
}

/**
 * Adds the BuddyPress setting help tab
 *
 * @package WP Idea Stream
 * @subpackage admin/groups
 *
 * @since 2.0.0
 *
 * @param  array  $help_tabs the list of help tabs
 * @return array             the new list of help tabs
 */
function wp_idea_stream_buddypress_settings_help_tab( $help_tabs = array() ) {
	if ( ! empty( $help_tabs['settings_page_ideastream'] ) ) {
		$help_tabs['settings_page_ideastream']['add_help_tab'][] = array(
			'id'      => 'settings-buddypress',
			'title'   => esc_html__( 'BuddyPress Integration Settings', 'wp-idea-stream' ),
			'content' => array(
				esc_html__( 'Sharing Ideas in a BuddyPress powered community will improve user interactions with the ideas posted on your site.', 'wp-idea-stream' ),
				esc_html__( 'If the setting &#34;Activate BuddyPress integration&#34; is activated and you are using at least version 2.1 of BuddyPress, some nice new functionalities will be available depending on the BuddyPress components you have activated:', 'wp-idea-stream' ),
				array(
					esc_html__( 'the plugin&#39;s user profile becomes a new navigation in the BuddyPress member page.', 'wp-idea-stream' ),
					esc_html__( 'Groups component is activated?', 'wp-idea-stream' ) . ' '
					. esc_html__( 'Nice! It’s now possible to share ideas within these micro-communities ensuring their members that the group&#39;s visibility is transposed to the status of their ideas.', 'wp-idea-stream' ) . ' '
					. esc_html__( 'You may prefer to disable WP Idea Stream&#39;s Group integration. This is possible by deactivating the &#34;BuddyPress Groups setting&#34;.', 'wp-idea-stream' ),
					esc_html__( 'Site Tracking and Activity components are activated?', 'wp-idea-stream' ) . ' '
					. esc_html__( 'Great! Each time a new idea or a new comment about an idea is posted, the members of your community will be informed through an activity update.', 'wp-idea-stream' ),
					esc_html__( 'Notifications component is activated?', 'wp-idea-stream' ) . ' '
					. esc_html__( 'Awesome! Your members will receive a screen notification when their ideas has been rated or commented upon.', 'wp-idea-stream' ),
				)
			),
		);
	}

	return $help_tabs;
}
add_filter( 'wp_idea_stream_get_help_tabs', 'wp_idea_stream_buddypress_settings_help_tab', 14, 1 );
