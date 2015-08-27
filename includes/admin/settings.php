<?php
/**
 * WP Idea Stream Settings.
 *
 * Administration / Settings
 *
 * @package WP Idea Stream
 * @subpackage admin/settings
 *
 * @since 2.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * The IdeaStream settings sections
 *
 * @package WP Idea Stream
 * @subpackage admin/settings
 *
 * @since 2.0.0
 *
 * @uses   wp_idea_stream_is_pretty_links() to check if the permalink structure is custom
 * @uses   apply_filters() call 'wp_idea_stream_get_settings_sections' to add/edit/remove sections
 * @return array the setting sections
 */
function wp_idea_stream_get_settings_sections() {
	$settings_sections =  array(
		'ideastream_settings_core' => array(
			'title'    => __( 'Main Settings', 'wp-idea-stream' ),
			'callback' => 'wp_idea_stream_settings_core_section_callback',
			'page'     => 'ideastream',
		),
	);

	if ( wp_idea_stream_is_pretty_links() ) {
		$settings_sections['ideastream_settings_rewrite'] = array(
			'title'    => __( 'Pretty Links', 'wp-idea-stream' ),
			'callback' => 'wp_idea_stream_settings_rewrite_section_callback',
			'page'     => 'ideastream',
		);
	}

	if ( is_multisite() ) {
		$settings_sections['ideastream_settings_multisite'] = array(
			'title'    => __( 'Network users settings', 'wp-idea-stream' ),
			'callback' => 'wp_idea_stream_settings_multisite_section_callback',
			'page'     => 'ideastream',
		);
	}

	/**
	 * Used internally to add the BuddyPress settings
	 * @see  buddypress/settings for an example of use.
	 *
	 * @param array $settings_sections the setting sections
	 */
	return (array) apply_filters( 'wp_idea_stream_get_settings_sections', $settings_sections );
}

/**
 * The different fields for setting sections
 *
 * @package WP Idea Stream
 * @subpackage admin/settings
 *
 * @since 2.0.0
 *
 * @uses   wp_idea_stream_is_pretty_links() to check if the permalink structure is custom
 * @uses   apply_filters() call 'wp_idea_stream_get_settings_fields' to add/edit/remove sections
 * @return array the settings fields
 */
function wp_idea_stream_get_settings_fields() {
	$setting_fields = array(
		/** Core Section **************************************************************/

		'ideastream_settings_core' => array(

			// Post Type Archive page title
			'_ideastream_archive_title' => array(
				'title'             => __( 'IdeaStream archive page', 'wp-idea-stream' ),
				'callback'          => 'wp_idea_stream_archive_title_setting_callback',
				'sanitize_callback' => 'sanitize_text_field',
				'args'              => array()
			),

			// Default post type status
			'_ideastream_submit_status' => array(
				'title'             => __( 'New ideas status', 'wp-idea-stream' ),
				'callback'          => 'wp_idea_stream_submit_status_setting_callback',
				'sanitize_callback' => 'wp_idea_stream_sanitize_status',
				'args'              => array()
			),

			// Can we add images to content ?
			'_ideastream_editor_image' => array(
				'title'             => __( 'Images', 'wp-idea-stream' ),
				'callback'          => 'wp_idea_stream_editor_image_setting_callback',
				'sanitize_callback' => 'absint',
				'args'              => array()
			),

			// Can we add links to content ?
			'_ideastream_editor_link' => array(
				'title'             => __( 'Links', 'wp-idea-stream' ),
				'callback'          => 'wp_idea_stream_editor_link_setting_callback',
				'sanitize_callback' => 'absint',
				'args'              => array()
			),

			// Is there a specific message to show if Pending is default status ?
			'_ideastream_moderation_message' => array(
				'title'             => __( 'Moderation message', 'wp-idea-stream' ),
				'callback'          => 'wp_idea_stream_moderation_message_setting_callback',
				'sanitize_callback' => 'sanitize_text_field',
				'args'              => array()
			),

			// Is there a specific message to show to not logged in users ?
			'_ideastream_login_message' => array(
				'title'             => __( 'Not logged in message', 'wp-idea-stream' ),
				'callback'          => 'wp_idea_stream_login_message_setting_callback',
				'sanitize_callback' => 'sanitize_text_field',
				'args'              => array()
			),

			// Customize the hint list
			'_ideastream_hint_list' => array(
				'title'             => __( 'Rating stars hover captions', 'wp-idea-stream' ),
				'callback'          => 'wp_idea_stream_hint_list_setting_callback',
				'sanitize_callback' => 'wp_idea_stream_sanitize_hint_list',
				'args'              => array()
			),

			// Disable stickies ?
			'_ideastream_sticky_ideas' => array(
				'title'             => __( 'Sticky ideas', 'wp-idea-stream' ),
				'callback'          => 'wp_idea_stream_sticky_ideas_setting_callback',
				'sanitize_callback' => 'wp_idea_stream_sticky_sanitize',
				'args'              => array()
			),

			// Disable comments disjoin ?
			'_ideastream_disjoin_comments' => array(
				'title'             => __( 'Idea comments', 'wp-idea-stream' ),
				'callback'          => 'wp_idea_stream_disjoin_comments_setting_callback',
				'sanitize_callback' => 'absint',
				'args'              => array()
			),

			// Disable comments in ideas post type
			'_ideastream_allow_comments' => array(
				'title'             => __( 'Comments', 'wp-idea-stream' ),
				'callback'          => 'wp_idea_stream_allow_comments_setting_callback',
				'sanitize_callback' => 'absint',
				'args'              => array()
			),
		)
	);

	if ( wp_idea_stream_is_pretty_links() ) {
		/** Rewrite Section ***********************************************************/
		$setting_fields['ideastream_settings_rewrite'] = array(

			// Root slug
			'_ideastream_root_slug' => array(
				'title'             => __( 'IdeaStream root slug', 'wp-idea-stream' ),
				'callback'          => 'wp_idea_stream_root_slug_setting_callback',
				'sanitize_callback' => 'wp_idea_stream_sanitize_slug',
				'args'              => array()
			),

			// Single idea slug
			'_ideastream_idea_slug' => array(
				'title'             => __( 'Single idea slug', 'wp-idea-stream' ),
				'callback'          => 'wp_idea_stream_idea_slug_setting_callback',
				'sanitize_callback' => 'wp_idea_stream_sanitize_slug',
				'args'              => array()
			),

			// Idea Category slug
			'_ideastream_category_slug' => array(
				'title'             => __( 'Category slug', 'wp-idea-stream' ),
				'callback'          => 'wp_idea_stream_category_slug_setting_callback',
				'sanitize_callback' => 'wp_idea_stream_sanitize_slug',
				'args'              => array()
			),

			// Idea Tag slug
			'_ideastream_tag_slug' => array(
				'title'             => __( 'Tag slug', 'wp-idea-stream' ),
				'callback'          => 'wp_idea_stream_tag_slug_setting_callback',
				'sanitize_callback' => 'wp_idea_stream_sanitize_slug',
				'args'              => array()
			),

			// User slug
			'_ideastream_user_slug' => array(
				'title'             => __( 'User slug', 'wp-idea-stream' ),
				'callback'          => 'wp_idea_stream_user_slug_setting_callback',
				'sanitize_callback' => 'wp_idea_stream_sanitize_slug',
				'args'              => array()
			),

			// User comments slug
			'_ideastream_user_comments_slug' => array(
				'title'             => __( 'User comments slug', 'wp-idea-stream' ),
				'callback'          => 'wp_idea_stream_user_comments_slug_setting_callback',
				'sanitize_callback' => 'wp_idea_stream_sanitize_slug',
				'args'              => array()
			),

			// Comments page slug
			'_ideastream_cpage_slug' => array(
				'title'             => __( 'User comments paging slug', 'wp-idea-stream' ),
				'callback'          => 'wp_idea_stream_cpage_slug_setting_callback',
				'sanitize_callback' => 'wp_idea_stream_sanitize_comments_page_slug',
				'args'              => array()
			),

			// User rates slug
			'_ideastream_user_rates_slug' => array(
				'title'             => __( 'User ratings slug', 'wp-idea-stream' ),
				'callback'          => 'wp_idea_stream_user_rates_slug_setting_callback',
				'sanitize_callback' => 'wp_idea_stream_sanitize_slug',
				'args'              => array()
			),

			// Signup slug
			'_ideastream_signup_slug' => array(
				'title'             => __( 'Sign-up slug', 'wp-idea-stream' ),
				'callback'          => 'wp_idea_stream_signup_slug_setting_callback',
				'sanitize_callback' => 'wp_idea_stream_sanitize_slug',
				'args'              => array()
			),

			// Action slug (so far 1 action is available > add )
			'_ideastream_action_slug' => array(
				'title'             => __( 'Action slug', 'wp-idea-stream' ),
				'callback'          => 'wp_idea_stream_action_slug_setting_callback',
				'sanitize_callback' => 'wp_idea_stream_sanitize_slug',
				'args'              => array()
			),

			// Add new slug
			'_ideastream_addnew_slug' => array(
				'title'             => __( 'New form slug', 'wp-idea-stream' ),
				'callback'          => 'wp_idea_stream_addnew_slug_setting_callback',
				'sanitize_callback' => 'wp_idea_stream_sanitize_slug',
				'args'              => array()
			),

			// Edit slug
			'_ideastream_edit_slug' => array(
				'title'             => __( 'Edit form slug', 'wp-idea-stream' ),
				'callback'          => 'wp_idea_stream_edit_slug_setting_callback',
				'sanitize_callback' => 'wp_idea_stream_sanitize_slug',
				'args'              => array()
			),
		);
	}

	if ( is_multisite() ) {
		/** Multisite Section *********************************************************/
		$setting_fields['ideastream_settings_multisite'] = array();

		if ( wp_idea_stream_is_signup_allowed() ) {
			$setting_fields['ideastream_settings_multisite']['_ideastream_allow_signups'] = array(
				'title'             => __( 'Sign-ups', 'wp-idea-stream' ),
				'callback'          => 'wp_idea_stream_allow_signups_setting_callback',
				'sanitize_callback' => 'absint',
				'args'              => array()
			);
		}

		$setting_fields['ideastream_settings_multisite']['_ideastream_user_new_idea_set_role'] = array(
			'title'             => __( 'Default role for network users', 'wp-idea-stream' ),
			'callback'          => 'wp_idea_stream_user_new_idea_set_role_setting_callback',
			'sanitize_callback' => 'absint',
			'args'              => array()
		);
	}

	/**
	 * Used internally to add the BuddyPress settings fields
	 * @see  buddypress/settings for an example of use.
	 *
	 * @param array $setting_fields the setting fields
	 */
	return (array) apply_filters( 'wp_idea_stream_get_settings_fields', $setting_fields );
}


/**
 * Gives the setting fields for section
 *
 * @package WP Idea Stream
 * @subpackage admin/settings
 *
 * @since 2.0.0
 *
 * @param  string $section_id
 * @uses   wp_idea_stream_get_settings_fields() to get the setting fields
 * @uses   apply_filters() call 'wp_idea_stream_get_settings_fields' to add/edit/remove the matching fields
 * @return array  the fields for the requested section
 */
function wp_idea_stream_get_settings_fields_for_section( $section_id = '' ) {

	// Bail if section is empty
	if ( empty( $section_id ) )
		return false;

	$fields = wp_idea_stream_get_settings_fields();
	$retval = isset( $fields[ $section_id ] ) ? $fields[ $section_id ] : false;

	/**
	 * @param array $retval      the setting fields
	 * @param string $section_id the section id
	 */
	return (array) apply_filters( 'wp_idea_stream_get_settings_fields_for_section', $retval, $section_id );
}

/**
 * Disable a settings field if its value rely on another setting field value
 *
 * @package WP Idea Stream
 * @subpackage admin/settings
 *
 * @since 2.0.0
 *
 * @param  string $function function to get the option value
 * @param  string $option   the option value
 * @uses   disabled() to add a disabled attribute if needed
 * @return string HTML output
 */
function wp_idea_stream_setting_disabled( $function = '', $option = '' ) {
	if ( empty( $function ) || empty( $option ) || ! function_exists( $function ) ) {
		return;
	}

	$compare = call_user_func( $function );

	disabled( $compare == $option );
}

/**
 * Disable a settings field if another option is set
 *
 * @package WP Idea Stream
 * @subpackage admin/settings
 *
 * @since 2.0.0
 *
 * @param  string $option_key the option key
 * @uses   disabled() to add a disabled attribute if needed
 * @return string HTML output
 */
function wp_idea_stream_setting_disabled_option( $option = '' ) {
	if( ! get_option( $option, false ) ) {
		return;
	}

	disabled( true );
}

/**
 * Checks for rewrite conflicts, displays a warning if any
 *
 * @package WP Idea Stream
 * @subpackage admin/settings
 *
 * @since 2.0.0
 *
 * @param  string $slug the plugin's root slug
 * @uses   wp_idea_stream() to get plugin's main instance
 * @uses   get_posts() to look for a posts or a page having a post name like root slug
 * @uses   esc_url() to sanitize an url
 * @uses   get_edit_post_link() to get the edit link of the found post or page
 * @uses   bbp_get_root_slug() to get bbPress forums root slug
 * @uses   add_query_arg() to add query vars to an url
 * @uses   admin_url() to build a link inside the current blog's Administration
 * @uses   is_multisite() to check the WordPress config
 * @uses   get_id_from_blogname() to check if a blog exists having the same slug than the plugin's root slug
 * @uses   get_current_blog_id() to get the current blog ID
 * @uses   get_home_url() to get the blog's home page
 * @uses   is_super_admin() to check if the current user is a Super Administrator
 * @uses   network_admin_url() to build a link inside the network Administration
 * @uses   apply_filters() call 'wp_idea_stream_root_slug_conflict_check' to let plugins add their own warning messages
 * @return string HTML output
 */
function wp_idea_stream_root_slug_conflict_check( $slug = 'ideastream' ) {
	// Initialize attention
	$attention = array();

	/**
	 * For pages and posts, problem can occur if the permalink setting is set to
	 * '/%postname%/' In that case a post will be listed in post archive pages but the
	 * single post may arrive on the IdeaStream Archive page.
	 */
	if ( '/%postname%/' == wp_idea_stream()->pretty_links ) {
		// Check for posts having a post name == root IdeaStream slug
		$post = get_posts( array( 'name' => $slug, 'post_type' => array( 'post', 'page' ) ) );

		if ( ! empty( $post ) ) {
			$post = $post[0];
			$conflict = sprintf( _x( 'this %s', 'ideastream settings root slug conflict', 'wp-idea-stream' ), $post->post_type );
			$attention[] = '<strong><a href="' . esc_url( get_edit_post_link( $post->ID ) ) . '">' . $conflict . '</strong>';
		}
	}

	/**
	 * We need to check for bbPress forum's root prefix, if called the same way than
	 * the root prefix of ideastream, then forums archive won't be reachable.
	 */
	if ( function_exists( 'bbp_get_root_slug' ) && $slug == bbp_get_root_slug() ) {
		$conflict = _x( 'bbPress forum root slug', 'bbPress possible conflict', 'wp-idea-stream' );
		$attention[] = '<strong><a href="' . esc_url( add_query_arg( array( 'page' => 'bbpress' ), admin_url( 'options-general.php' ) ) ) .'">' . $conflict . '</strong>';
	}

	/**
	 * Finally, in case of a multisite config, we need to check if a child blog is called
	 * the same way than the ideastream root slug
	 */
	if ( is_multisite() ) {
		$blog_id         = (int) get_id_from_blogname( $slug );
		$current_blog_id = (int) get_current_blog_id();
		$current_site    = get_current_site();

		if ( ! empty( $blog_id ) && $blog_id != $current_blog_id && $current_site->blog_id == $current_blog_id ) {
			$conflict = _x( 'child blog slug', 'Child blog possible conflict', 'wp-idea-stream' );

			$blog_url = get_home_url( $blog_id, '/' );

			if ( is_super_admin() ) {
				$blog_url = add_query_arg( array( 'id' => $blog_id ), network_admin_url( 'site-info.php' ) );
			}

			$attention[] = '<strong><a href="' . esc_url( $blog_url ) .'">' . $conflict . '</strong>';
		}
	}
	/**
	 * Other plugins can come in there to draw attention ;)
	 *
	 * @param array  $attention list of slug conflicts
	 * @param string $slug      the plugin's root slug
	 */
	$attention = apply_filters( 'wp_idea_stream_root_slug_conflict_check', $attention, $slug );

	// Display warnings if needed
	if ( ! empty( $attention ) ) {
		?>

		<span class="attention"><?php printf( esc_html__( 'Possible conflict with: %s', 'wp-idea-stream' ), join( ', ', $attention ) ) ;?></span>

		<?php
	}
}

/** Core settings callbacks ***************************************************/

/**
 * Some text to introduce the core settings section
 *
 * @package WP Idea Stream
 * @subpackage admin/settings
 *
 * @since 2.0.0
 *
 * @uses   wp_idea_stream_get_root_url() to get the main IdeaStream archive page
 * @return string HTML output
 */
function wp_idea_stream_settings_core_section_callback() {
	?>

	<p><?php _e( 'Customize IdeaStream features', 'wp-idea-stream' ); ?></p>
	<p class="description"><?php printf( esc_html__( 'Url of IdeaStream&#39;s main page: %s', 'wp-idea-stream' ), '<code>' . wp_idea_stream_get_root_url() .'</code>' ) ;?></p>

	<?php
}

/**
 * Archive page title callback
 *
 * @package WP Idea Stream
 * @subpackage admin/settings
 *
 * @since 2.0.0
 *
 * @uses  esc_attr() to sanitize the attribute
 * @uses  wp_idea_stream_archive_title() To get the archive page title
 * @return string HTML output
 */
function wp_idea_stream_archive_title_setting_callback() {
	?>

	<input name="_ideastream_archive_title" id="_ideastream_archive_title" type="text" class="regular-text code" value="<?php echo esc_attr( wp_idea_stream_archive_title() ); ?>" />

	<?php
}

/**
 * Submit Status callback
 *
 * @package WP Idea Stream
 * @subpackage admin/settings
 *
 * @since 2.0.0
 *
 * @uses   wp_idea_stream_default_idea_status() to get the active status
 * @uses   wp_idea_stream_setting_disabled_option() to disable the field if an option is set
 * @uses   selected() to activate the active option
 * @return string HTML output
 */
function wp_idea_stream_submit_status_setting_callback() {
	$current_status = wp_idea_stream_default_idea_status();
	?>
	<select name="_ideastream_submit_status" id="_ideastream_submit_status" <?php wp_idea_stream_setting_disabled_option( '_ideastream_groups_integration' ); ?>>
		<option value="publish" <?php selected( $current_status, 'publish');?>><?php esc_html_e( 'Publish', 'wp-idea-stream' );?></option>
		<option value="pending" <?php selected( $current_status, 'pending');?>><?php esc_html_e( 'Pending', 'wp-idea-stream' );?></option>
	</select>
	<label for="_ideastream_submit_status"><?php esc_html_e( 'is the default status for all ideas', 'wp-idea-stream' ); ?></label>
	<p class="description"><?php esc_html_e( 'Depending on this setting, the moderation message one will be available', 'wp-idea-stream' ); ?></p>

	<?php
}

/**
 * WP Editor's image button callback
 *
 * @package WP Idea Stream
 * @subpackage admin/settings
 *
 * @since 2.0.0
 *
 * @uses   checked() to add a checked attribute if needed
 * @uses   wp_idea_stream_idea_editor_image() to get the active option
 * @return string HTML output
 */
function wp_idea_stream_editor_image_setting_callback() {
	?>

	<input name="_ideastream_editor_image" id="_ideastream_editor_image" type="checkbox" value="1" <?php checked( wp_idea_stream_idea_editor_image() ); ?> />
	<label for="_ideastream_editor_image"><?php esc_html_e( 'Allow users to add images to their ideas', 'wp-idea-stream' ); ?></label>

	<?php
}

/**
 * WP Editor's link button callback
 *
 * @package WP Idea Stream
 * @subpackage admin/settings
 *
 * @since 2.0.0
 *
 * @uses   checked() to add a checked attribute if needed
 * @uses   wp_idea_stream_idea_editor_link() to get the active option
 * @return string HTML output
 */
function wp_idea_stream_editor_link_setting_callback() {
	?>

	<input name="_ideastream_editor_link" id="_ideastream_editor_link" type="checkbox" value="1" <?php checked( wp_idea_stream_idea_editor_link() ); ?> />
	<label for="_ideastream_editor_link"><?php esc_html_e( 'Allow users to add links to their ideas', 'wp-idea-stream' ); ?></label>

	<?php
}

/**
 * Custom moderation message callback
 *
 * @package WP Idea Stream
 * @subpackage admin/settings
 *
 * @since 2.0.0
 *
 * @uses   wp_idea_stream_setting_disabled() to disable the field if an option has a specific value
 * @uses   esc_textarea() to sanitize a textarea element
 * @uses   wp_idea_stream_moderation_message() to get the active message
 * @return string HTML output
 */
function wp_idea_stream_moderation_message_setting_callback() {
	?>

	<label for="_ideastream_moderation_message"><?php esc_html_e( 'In case Pending is the status for all ideas, you can customize the moderation message', 'wp-idea-stream' ); ?></label>
	<textarea name="_ideastream_moderation_message" id="_ideastream_moderation_message" rows="10" cols="50" class="large-text code" <?php wp_idea_stream_setting_disabled( 'wp_idea_stream_default_idea_status', 'publish' ); ?>><?php echo esc_textarea( wp_idea_stream_moderation_message() );?></textarea>

	<?php
}

/**
 * Custom login message callback
 *
 * @package WP Idea Stream
 * @subpackage admin/settings
 *
 * @since 2.0.0
 *
 * @uses   esc_textarea() to sanitize a textarea element
 * @uses   wp_idea_stream_login_message() to get the active message
 * @return string HTML output
 */
function wp_idea_stream_login_message_setting_callback() {
	?>

	<label for="_ideastream_login_message"><?php esc_html_e( 'You can customize the message shown to not logged in users on the new idea form', 'wp-idea-stream' ); ?></label>
	<textarea name="_ideastream_login_message" id="_ideastream_login_message" rows="10" cols="50" class="large-text code"><?php echo esc_textarea( wp_idea_stream_login_message() );?></textarea>

	<?php
}

/**
 * List of captions for the rating stars
 *
 * @package WP Idea Stream
 * @subpackage admin/settings
 *
 * @since 2.0.0
 *
 * @uses   wp_idea_stream_get_hint_list() to get the active hint list
 * @uses   esc_attr() to sanitize the attribute
 * @return string HTML output
 */
function wp_idea_stream_hint_list_setting_callback() {
	$hintlist = wp_idea_stream_get_hint_list();
	$csv_hinlist = join( ',', $hintlist );
	?>

	<label for="_ideastream_hint_list"><?php esc_html_e( 'You can customize the hover captions used for stars by using a comma separated list of captions', 'wp-idea-stream' ); ?></label>
	<input name="_ideastream_hint_list" id="_ideastream_hint_list" type="text" class="large-text code" value="<?php echo esc_attr( $csv_hinlist ); ?>" />

	<?php
}

/**
 * Sticky ideas callback
 *
 * @package WP Idea Stream
 * @subpackage admin/settings
 *
 * @since 2.0.0
 *
 * @uses   checked() to add a checked attribute if needed
 * @uses   wp_idea_stream_is_sticky_enabled() to get the active option
 * @return string HTML output
 */
function wp_idea_stream_sticky_ideas_setting_callback() {
	?>

	<input name="_ideastream_sticky_ideas" id="_ideastream_sticky_ideas" type="checkbox" value="1" <?php checked( wp_idea_stream_is_sticky_enabled() ); ?> />
	<label for="_ideastream_sticky_ideas"><?php esc_html_e( 'Allow ideas to be sticked to top of IdeaStream first page', 'wp-idea-stream' ); ?></label>

	<?php
}

/**
 * Disjoin idea comments callback
 *
 * @package WP Idea Stream
 * @subpackage admin/settings
 *
 * @since 2.0.0
 *
 * @uses   checked() to add a checked attribute if needed
 * @uses   wp_idea_stream_is_comments_disjoined() to get the active option
 * @return string HTML output
 */
function wp_idea_stream_disjoin_comments_setting_callback() {
	?>

	<input name="_ideastream_disjoin_comments" id="_ideastream_disjoin_comments" type="checkbox" value="1" <?php checked( wp_idea_stream_is_comments_disjoined() ); ?> />
	<label for="_ideastream_disjoin_comments"><?php esc_html_e( 'Separate comments made on ideas from the other post types.', 'wp-idea-stream' ); ?></label>

	<?php
}

/**
 * Global "opened" comments callback
 *
 * @package WP Idea Stream
 * @subpackage admin/settings
 *
 * @since 2.0.0
 *
 * @uses   checked() to add a checked attribute if needed
 * @uses   wp_idea_stream_is_comments_allowed() to get the active option
 * @return string HTML output
 */
function wp_idea_stream_allow_comments_setting_callback() {
	?>

	<input name="_ideastream_allow_comments" id="_ideastream_allow_comments" type="checkbox" value="1" <?php checked( wp_idea_stream_is_comments_allowed() ); ?> />
	<label for="_ideastream_allow_comments"><?php esc_html_e( 'Allow users to add comments on ideas', 'wp-idea-stream' ); ?></label>

	<?php
}

/** Rewrite settings callbacks ************************************************/

/**
 * Some text to introduce the rewrite settings section
 *
 * @package WP Idea Stream
 * @subpackage admin/settings
 *
 * @since 2.0.0
 *
 * @return string HTML output
 */
function wp_idea_stream_settings_rewrite_section_callback() {
	?>

	<p><?php esc_html_e( 'Customize the slugs of IdeaStream urls', 'wp-idea-stream' ); ?></p>

	<?php
}

/**
 * Root slug of the plugin
 *
 * @package WP Idea Stream
 * @subpackage admin/settings
 *
 * @since 2.0.0
 *
 * @uses   esc_attr() to sanitize the attribute
 * @uses   wp_idea_stream_root_slug() to get the active slug
 * @uses   wp_idea_stream_root_slug_conflict_check() to display a warning if a rewrite conflict was found
 * @return string HTML output
 */
function wp_idea_stream_root_slug_setting_callback() {
	?>

	<input name="_ideastream_root_slug" id="_ideastream_root_slug" type="text" class="regular-text code" value="<?php echo esc_attr( wp_idea_stream_root_slug() ); ?>" />

	<?php
	wp_idea_stream_root_slug_conflict_check( wp_idea_stream_root_slug() );
}

/**
 * Idea slug of the plugin
 *
 * @package WP Idea Stream
 * @subpackage admin/settings
 *
 * @since 2.0.0
 *
 * @uses   esc_attr() to sanitize the attribute
 * @uses   wp_idea_stream_idea_get_slug() to get the active slug
 * @return string HTML output
 */
function wp_idea_stream_idea_slug_setting_callback() {
	?>

	<input name="_ideastream_idea_slug" id="_ideastream_idea_slug" type="text" class="regular-text code" value="<?php echo esc_attr( wp_idea_stream_idea_get_slug() ); ?>" />

	<?php
}

/**
 * Category slug of the plugin
 *
 * @package WP Idea Stream
 * @subpackage admin/settings
 *
 * @since 2.0.0
 *
 * @uses   esc_attr() to sanitize the attribute
 * @uses   wp_idea_stream_category_get_slug() to get the active slug
 * @return string HTML output
 */
function wp_idea_stream_category_slug_setting_callback() {
	?>

	<input name="_ideastream_category_slug" id="_ideastream_category_slug" type="text" class="regular-text code" value="<?php echo esc_attr( wp_idea_stream_category_get_slug() ); ?>" />

	<?php
}

/**
 * Tag slug of the plugin
 *
 * @package WP Idea Stream
 * @subpackage admin/settings
 *
 * @since 2.0.0
 *
 * @uses   esc_attr() to sanitize the attribute
 * @uses   wp_idea_stream_tag_get_slug() to get the active slug
 * @return string HTML output
 */
function wp_idea_stream_tag_slug_setting_callback() {
	?>

	<input name="_ideastream_tag_slug" id="_ideastream_tag_slug" type="text" class="regular-text code" value="<?php echo esc_attr( wp_idea_stream_tag_get_slug() ); ?>" />

	<?php
}

/**
 * User slug of the plugin
 *
 * @package WP Idea Stream
 * @subpackage admin/settings
 *
 * @since 2.0.0
 *
 * @uses   esc_attr() to sanitize the attribute
 * @uses   wp_idea_stream_user_get_slug() to get the active slug
 * @return string HTML output
 */
function wp_idea_stream_user_slug_setting_callback() {
	?>

	<input name="_ideastream_user_slug" id="_ideastream_user_slug" type="text" class="regular-text code" value="<?php echo esc_attr( wp_idea_stream_user_get_slug() ); ?>" />

	<?php
}

/**
 * User comments slug of the plugin
 *
 * @package WP Idea Stream
 * @subpackage admin/settings
 *
 * @since 2.0.0
 *
 * @uses   esc_attr() to sanitize the attribute
 * @uses   wp_idea_stream_user_comments_slug() to get the active slug
 * @return string HTML output
 */
function wp_idea_stream_user_comments_slug_setting_callback() {
	?>

	<input name="_ideastream_user_comments_slug" id="_ideastream_user_comments_slug" type="text" class="regular-text code" value="<?php echo esc_attr( wp_idea_stream_user_comments_slug() ); ?>" />

	<?php
}

/**
 * User comments pagination slug of the plugin
 *
 * @package WP Idea Stream
 * @subpackage admin/settings
 *
 * @since 2.0.0
 *
 * @uses   esc_attr() to sanitize the attribute
 * @uses   wp_idea_stream_cpage_slug() to get the active slug
 * @uses   wp_idea_stream_paged_slug() to get the default pagination slug
 * @return string HTML output
 */
function wp_idea_stream_cpage_slug_setting_callback() {
	?>

	<input name="_ideastream_cpage_slug" id="_ideastream_cpage_slug" type="text" class="regular-text code" value="<?php echo esc_attr( wp_idea_stream_cpage_slug() ); ?>" />
	<p class="description"><?php printf( esc_html__( '&#39;%s&#39; slug cannot be used here.', 'wp-idea-stream' ), wp_idea_stream_paged_slug() ); ?></p>

	<?php
}

/**
 * User rates slug of the plugin
 *
 * @package WP Idea Stream
 * @subpackage admin/settings
 *
 * @since 2.0.0
 *
 * @uses   esc_attr() to sanitize the attribute
 * @uses   wp_idea_stream_user_rates_slug() to get the active slug
 * @return string HTML output
 */
function wp_idea_stream_user_rates_slug_setting_callback() {
	?>

	<input name="_ideastream_user_rates_slug" id="_ideastream_user_rates_slug" type="text" class="regular-text code" value="<?php echo esc_attr( wp_idea_stream_user_rates_slug() ); ?>" />

	<?php
}

/**
 * Signup slug of the plugin
 *
 * @package WP Idea Stream
 * @subpackage admin/settings
 *
 * @since 2.1.0
 *
 * @uses   esc_attr() to sanitize the attribute
 * @uses  wp_idea_stream_signup_slug() to get the active slug
 * @return string HTML output
 */
function wp_idea_stream_signup_slug_setting_callback() {
	?>

	<input name="_ideastream_signup_slug" id="_ideastream_signup_slug" type="text" class="regular-text code" value="<?php echo esc_attr( wp_idea_stream_signup_slug() ); ?>" />

	<?php
}

/**
 * Action slug of the plugin
 *
 * @package WP Idea Stream
 * @subpackage admin/settings
 *
 * @since 2.0.0
 *
 * @uses   esc_attr() to sanitize the attribute
 * @uses   wp_idea_stream_action_get_slug() to get the active slug
 * @return string HTML output
 */
function wp_idea_stream_action_slug_setting_callback() {
	?>

	<input name="_ideastream_action_slug" id="_ideastream_action_slug" type="text" class="regular-text code" value="<?php echo esc_attr( wp_idea_stream_action_get_slug() ); ?>" />

	<?php
}

/**
 * New idea slug of the plugin
 *
 * @package WP Idea Stream
 * @subpackage admin/settings
 *
 * @since 2.0.0
 *
 * @uses   esc_attr() to sanitize the attribute
 * @uses   wp_idea_stream_addnew_slug() to get the active slug
 * @return string HTML output
 */
function wp_idea_stream_addnew_slug_setting_callback() {
	?>

	<input name="_ideastream_addnew_slug" id="_ideastream_addnew_slug" type="text" class="regular-text code" value="<?php echo esc_attr( wp_idea_stream_addnew_slug() ); ?>" />

	<?php
}

/**
 * Edit idea slug of the plugin
 *
 * @package WP Idea Stream
 * @subpackage admin/settings
 *
 * @since 2.0.0
 *
 * @uses   esc_attr() to sanitize the attribute
 * @uses   wp_idea_stream_edit_slug() to get the active slug
 * @return string HTML output
 */
function wp_idea_stream_edit_slug_setting_callback() {
	?>

	<input name="_ideastream_edit_slug" id="_ideastream_edit_slug" type="text" class="regular-text code" value="<?php echo esc_attr( wp_idea_stream_edit_slug() ); ?>" />

	<?php
}

/**
 * Some text to introduce the multisite settings section
 *
 * @package WP Idea Stream
 * @subpackage admin/settings
 *
 * @since 2.2.0
 *
 * @return string HTML output
 */
function wp_idea_stream_settings_multisite_section_callback() {
	?>

	<p><?php esc_html_e( 'Define your preferences about network users', 'wp-idea-stream' ); ?></p>

	<?php
}

/**
 * Does the blog is allowing IdeaStream to manage signups
 *
 * @package WP Idea Stream
 * @subpackage admin/settings
 *
 * @since 2.2.0
 *
 * @uses   checked() to add a checked attribute if needed
 * @uses   wp_idea_stream_allow_signups() to get the active option
 * @return string HTML output
 */
function wp_idea_stream_allow_signups_setting_callback() {
	?>

	<input name="_ideastream_allow_signups" id="_ideastream_allow_signups" type="checkbox" value="1" <?php checked( wp_idea_stream_allow_signups() ); ?> />
	<label for="_ideastream_allow_signups"><?php esc_html_e( 'Allow IdeaStream to manage signups for your site', 'wp-idea-stream' ); ?></label>

	<?php
}

/**
 * Default role for users posting an idea on this site callback
 *
 * @package WP Idea Stream
 * @subpackage admin/settings
 *
 * @since 2.2.0
 *
 * @uses   checked() to add a checked attribute if needed
 * @uses   wp_idea_stream_user_new_idea_set_role() to get the active option
 * @return string HTML output
 */
function wp_idea_stream_user_new_idea_set_role_setting_callback() {
	?>

	<input name="_ideastream_user_new_idea_set_role" id="_ideastream_user_new_idea_set_role" type="checkbox" value="1" <?php checked( wp_idea_stream_user_new_idea_set_role() ); ?> />
	<label for="_ideastream_user_new_idea_set_role"><?php esc_html_e( 'Automatically set this site&#39;s default role for users posting a new idea and having no role on this site.', 'wp-idea-stream' ); ?></label>

	<?php
}

/** Custom sanitization *******************************************************/

/**
 * Sanitize the status setting
 *
 * @package WP Idea Stream
 * @subpackage admin/settings
 *
 * @since 2.0.0
 *
 * @param  string $option the value choosed by the admin
 * @uses   sanitize_key() to sanitize the option
 * @uses   apply_filters() call 'wp_idea_stream_sanitize_status' to do extra sanitization
 * @return string         the sanitized value
 */
function wp_idea_stream_sanitize_status( $option = '' ) {
	/**
	 * @param string $option the sanitized option
	 */
	return apply_filters( 'wp_idea_stream_sanitize_status', sanitize_key( $option ) );
}

/**
 * Sanitize the rating stars captions
 *
 * @package WP Idea Stream
 * @subpackage admin/settings
 *
 * @since 2.0.0
 *
 * @param  string $option the comma separated values choosed by the admin
 * @uses   wp_unslash() to strip slashes
 * @uses   apply_filters() call 'wp_idea_stream_sanitize_hint_list' to do extra sanitization
 * @return string         the sanitized value
 */
function wp_idea_stream_sanitize_hint_list( $option = '' ) {
	if ( is_array( $option ) ) {
		$captions = $option;
	} else {
		$captions = explode( ',', wp_unslash( $option ) );
	}

	if ( ! is_array( $captions ) ) {
		return false;
	}

	$captions = array_map( 'sanitize_text_field', $captions );

	/**
	 * @param array $captions the sanitized captions
	 */
	return apply_filters( 'wp_idea_stream_sanitize_hint_list', $captions );
}

/**
 * Make sure sticky ideas are removed if the sticky setting is disabled
 *
 * @package WP Idea Stream
 * @subpackage admin/settings
 *
 * @since 2.0.0
 *
 * @param  int $option the sticky setting
 * @return int         the new sticky setting
 */
function wp_idea_stream_sticky_sanitize( $option = 0 ) {
	if ( empty( $option ) ) {
		delete_option( 'sticky_ideas' );
	}

	return absint( $option );
}

/**
 * Sanitize permalink slugs when saving the settings page.
 *
 * Inspired by bbPress's bbp_sanitize_slug() function
 *
 * @package WP Idea Stream
 * @subpackage admin/settings
 *
 * @since 2.0.0
 *
 * @param  string $slug the slug choosed by the admin
 * @uses   remove_accents() to remove accents from the slug
 * @uses   esc_url_raw() to strip out unsafe or unusable chars
 * @uses   apply_filters() call 'wp_idea_stream_sanitize_slug' to do extra sanitization
 * @return string the sanitized slug
 */
function wp_idea_stream_sanitize_slug( $slug = '' ) {
	// Remove accents
	$value = remove_accents( $slug );

	// Put every character in lowercase
	$value = strtolower( $value );

	// Don't allow multiple slashes in a row
	$value = preg_replace( '#/+#', '/', str_replace( '#', '', $value ) );

	// Strip out unsafe or unusable chars
	$value = esc_url_raw( $value );

	// esc_url_raw() adds a scheme via esc_url(), so let's remove it
	$value = str_replace( 'http://', '', $value );

	// Trim off first and last slashes.
	//
	// We already prevent double slashing elsewhere, but let's prevent
	// accidental poisoning of options values where we can.
	$value = ltrim( $value, '/' );
	$value = rtrim( $value, '/' );

	/**
	 * @param string $value the sanitized slug
	 * @param string $slug  the slug choosed by the admin
	 */
	return apply_filters( 'wp_idea_stream_sanitize_slug', $value, $slug );
}

/**
 * Sanitize the user comments pagination slug.
 *
 * @package WP Idea Stream
 * @subpackage admin/settings
 *
 * @since 2.0.0
 *
 * @param  string $slug the slug choosed by the admin
 * @uses   wp_idea_stream_paged_slug() to check slug is not the same than the paged one
 * @uses   wp_idea_stream_sanitize_slug() to sanitize the slug
 * @return string the sanitized slug
 */
function wp_idea_stream_sanitize_comments_page_slug( $slug = '' ) {
	if ( $slug == wp_idea_stream_paged_slug() ) {
		return 'cpage';
	}

	return wp_idea_stream_sanitize_slug( $slug );
}

/**
 * Displays the settings page
 *
 * @package WP Idea Stream
 * @subpackage admin/settings
 *
 * @since 2.0.0
 *
 * @uses settings_fields()
 * @uses do_settings_sections()
 */
function wp_idea_stream_settings() {
	?>
	<div class="wrap">

		<h2><?php esc_html_e( 'IdeaStream Settings', 'wp-idea-stream' ) ?></h2>

		<form action="options.php" method="post">

			<?php settings_fields( 'ideastream' ); ?>

			<?php do_settings_sections( 'ideastream' ); ?>

			<p class="submit">
				<input type="submit" name="submit" class="button-primary" value="<?php esc_attr_e( 'Save Changes', 'wp-idea-stream' ); ?>" />
			</p>
		</form>
	</div>
	<?php
}
