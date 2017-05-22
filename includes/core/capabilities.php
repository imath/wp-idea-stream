<?php
/**
 * WP Idea Stream Capabilities.
 *
 * Manage user capabilities for the plugin
 *
 * @package WP Idea Stream\core
 *
 * @since 2.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Return Ideas post type capabilities
 *
 * @since 2.0.0
 *
 * @return array Ideas capabilities
 */
function wp_idea_stream_get_post_type_caps() {
	return apply_filters( 'wp_idea_stream_get_post_type_caps', array (
		'edit_post'              => 'edit_idea',
		'read_post'              => 'read_idea',
		'delete_post'            => 'delete_idea',
		'edit_posts'             => 'edit_ideas',
		'edit_others_posts'      => 'edit_others_ideas',
		'publish_posts'          => 'publish_ideas',
		'read_private_posts'     => 'read_private_ideas',
		'delete_posts'           => 'delete_ideas',
		'delete_private_posts'   => 'delete_private_ideas',
		'delete_published_posts' => 'delete_published_ideas',
		'delete_others_posts'    => 'delete_others_ideas',
		'edit_private_posts'     => 'edit_private_ideas',
		'edit_published_posts'   => 'edit_published_ideas',
	) );
}

/**
 * Return Ideas tag capabilities
 *
 * @since 2.0.0
 *
 * @return array Ideas tag capabilities
 */
function wp_idea_stream_get_tag_caps() {
	return apply_filters( 'wp_idea_stream_get_tag_caps', array (
		'manage_terms' => 'manage_idea_tags',
		'edit_terms'   => 'edit_idea_tags',
		'delete_terms' => 'delete_idea_tags',
		'assign_terms' => 'assign_idea_tags'
	) );
}

/**
 * Return Ideas category capabilities
 *
 * @since 2.0.0
 *
 * @return array Ideas category capabilities
 */
function wp_idea_stream_get_category_caps() {
	return apply_filters( 'wp_idea_stream_get_category_caps', array (
		'manage_terms' => 'manage_idea_categories',
		'edit_terms'   => 'edit_idea_categories',
		'delete_terms' => 'delete_idea_categories',
		'assign_terms' => 'assign_idea_categories'
	) );
}

/**
 * Maps Ideas capabilities
 *
 * @since 2.0.0
 *
 * @param  array $caps Capabilities for meta capability
 * @param  string $cap Capability name
 * @param  int $user_id User id
 * @param  mixed $args Arguments
 * @return array Actual capabilities for meta capability
 */
function wp_idea_stream_map_meta_caps( $caps = array(), $cap = '', $user_id = 0, $args = array() ) {

	// What capability is being checked?
	switch ( $cap ) {

		case 'publish_ideas'   :
		case 'rate_ideas'      :
		case 'read_idea_rates' :
			if ( ! empty( $user_id ) ) {
				$caps = array( 'exist' );
			} else {
				$caps = array( 'publish_posts' );
			}
			break;

		case 'read_idea' :
		case 'edit_idea' :

			// Get the post
			$_post = get_post( $args[0] );

			if ( ! empty( $_post ) ) {

				$caps = array();

				if ( ( ( ! empty( $_POST['action'] ) && 'parse-embed' === $_POST['action'] ) || ! is_admin() ) && ( (int) $user_id === (int) $_post->post_author ) ) {

					$caps = array( 'exist' );

				// Unknown, so map to manage_options
				} else {
					$caps[] = 'manage_options';
				}
			}

			break;

		case 'edit_ideas'           :
		case 'edit_others_ideas'    :
		case 'edit_private_ideas'   :
		case 'edit_published_ideas' :
		case 'read_private_ideas'   :
			$caps = array( 'manage_options' );
			break;

		case 'edit_comment' :

			if ( ! is_admin() ) {

				// Get the comment
				$_comment = get_comment( $args[0] );

				if ( ! empty( $_comment ) && wp_idea_stream_get_post_type() == get_post_type( $_comment->comment_post_ID ) ) {
					$caps = array( 'manage_options' );
				}
			}

			break;

		case 'delete_idea'            :
		case 'delete_ideas'           :
		case 'delete_others_ideas'    :
		case 'delete_private_ideas'   :
		case 'delete_published_ideas' :
			$caps = array( 'manage_options' );
			break;

		/** Taxonomies ****************************************************************/

		case 'manage_idea_tags'       :
		case 'edit_idea_tags'         :
		case 'delete_idea_tags'       :
		case 'manage_idea_categories' :
		case 'edit_idea_categories'   :
		case 'delete_idea_categories' :
			$caps = array( 'manage_options' );
			break;

		// Open to all users that have an ID
		case 'assign_idea_tags'       :
		case 'assign_idea_categories' :
			if ( ! empty( $user_id ) ) {
				$caps = array( 'exist' );
			} else {
				$caps = array( 'manage_options' );
			}
			break;

		/** Admin *********************************************************************/

		case 'wp_idea_stream_ideas_admin' :
			$caps = array( 'manage_options' );
			break;
	}

	/**
	 * @param  array $caps Capabilities for meta capability
	 * @param  string $cap Capability name
	 * @param  int $user_id User id
	 * @param  mixed $args Arguments
	 */
	return apply_filters( 'wp_idea_stream_map_meta_caps', $caps, $cap, $user_id, $args );
}

/**
 * Check wether a user has the capability to perform an action
 *
 * @since 2.0.0
 *
 * @param  string $capability Capability to check
 * @param  array  $args additional args to help
 * @return bool True|False
 */
function wp_idea_stream_user_can( $capability = '', $args = false ) {
	$can = false;

	if ( ! empty( $args ) ) {
		$can = current_user_can( $capability, $args );
	} else {
		$can = current_user_can( $capability );
	}

	return apply_filters( 'wp_idea_stream_user_can', $can, $capability );
}
