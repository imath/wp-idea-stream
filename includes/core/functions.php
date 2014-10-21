<?php
/**
 * WP Idea Stream Functions.
 *
 * Generic functions used at various places in the plugin
 *
 * @package WP Idea Stream
 * @subpackage core/functions
 *
 * @since 2.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/** Globals *******************************************************************/

/**
 * Get the plugin's current version
 *
 * @package WP Idea Stream
 * @subpackage core/functions
 *
 * @since 2.0.0
 *
 * @uses   wp_idea_stream() to get plugin's main instance
 * @return string Plugin's current version
 */
function wp_idea_stream_get_version() {
	return wp_idea_stream()->version;
}

/**
 * Get the DB verion of the plugin
 *
 * Used to check wether to run the upgrade
 * routine of the plugin.
 * @see  core/upgrade > wp_idea_stream_is_upgrade()
 *
 * @package WP Idea Stream
 * @subpackage core/functions
 *
 * @since 2.0.0
 *
 * @uses   get_option()
 * @return string DB version of the plugin
 */
function wp_idea_stream_db_version() {
	$db_version = get_option( '_ideastream_vestion' );

	if ( empty( $db_version ) ) {
		$db_version = get_option( '_ideastream_version', 0 );
	}

	return $db_version;
}

/**
 * Get plugin's basename
 *
 * @package WP Idea Stream
 * @subpackage core/functions
 *
 * @since 2.0.0
 *
 * @uses   wp_idea_stream() to get plugin's main instance
 * @uses   apply_filters() call 'wp_idea_stream_get_basename' to set a different basename
 * @return string Plugin's basename
 */
function wp_idea_stream_get_basename() {
	return apply_filters( 'wp_idea_stream_get_basename', wp_idea_stream()->basename );
}

/**
 * Get plugin's main path
 *
 * @package WP Idea Stream
 * @subpackage core/functions
 *
 * @since 2.0.0
 *
 * @uses   wp_idea_stream() to get plugin's main instance
 * @uses   apply_filters() call 'wp_idea_stream_get_plugin_dir' to set a different plugin dir
 * @return string plugin's main path
 */
function wp_idea_stream_get_plugin_dir() {
	return apply_filters( 'wp_idea_stream_get_plugin_dir', wp_idea_stream()->plugin_dir );
}

/**
 * Get plugin's main url
 *
 * @package WP Idea Stream
 * @subpackage core/functions
 *
 * @since 2.0.0
 *
 * @uses   wp_idea_stream() to get plugin's main instance
 * @uses   apply_filters() call 'wp_idea_stream_get_plugin_url' to set a different plugin url
 * @return string plugin's main url
 */
function wp_idea_stream_get_plugin_url() {
	return apply_filters( 'wp_idea_stream_get_plugin_url', wp_idea_stream()->plugin_url );
}

/**
 * Get plugin's javascript url
 *
 * That's where the plugin's js file are all available
 *
 * @package WP Idea Stream
 * @subpackage core/functions
 *
 * @since 2.0.0
 *
 * @uses   wp_idea_stream() to get plugin's main instance
 * @uses   apply_filters() call 'wp_idea_stream_get_js_url' to set a different js url
 * @return string plugin's javascript url
 */
function wp_idea_stream_get_js_url() {
	return apply_filters( 'wp_idea_stream_get_js_url', wp_idea_stream()->js_url );
}

/**
 * Get a specific javascript file url (minified or not)
 *
 * @package WP Idea Stream
 * @subpackage core/functions
 *
 * @since 2.0.0
 *
 * @param  string $script the name of the script
 * @uses   wp_idea_stream_get_js_url() to plugin's javascript url
 * @return string         url to the minified or regular script
 */
function wp_idea_stream_get_js_script( $script = '' ) {
	$min = '.min';

	if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
		$min = '';
	}

	return wp_idea_stream_get_js_url() . $script . $min . '.js';
}

/**
 * Get plugin's path to includes directory
 *
 * @package WP Idea Stream
 * @subpackage core/functions
 *
 * @since 2.0.0
 *
 * @uses   wp_idea_stream() to get plugin's main instance
 * @uses   apply_filters() call 'wp_idea_stream_get_includes_dir' to set a different include dir
 * @return string includes directory path
 */
function wp_idea_stream_get_includes_dir() {
	return apply_filters( 'wp_idea_stream_get_includes_dir', wp_idea_stream()->includes_dir );
}

/**
 * Get plugin's url to includes directory
 *
 * @package WP Idea Stream
 * @subpackage core/functions
 *
 * @since 2.0.0
 *
 * @uses   wp_idea_stream() to get plugin's main instance
 * @uses   apply_filters() call 'wp_idea_stream_get_includes_url' to set a different include url
 * @return string includes directory url
 */
function wp_idea_stream_get_includes_url() {
	return apply_filters( 'wp_idea_stream_get_includes_url', wp_idea_stream()->includes_url );
}

/**
 * Get plugin's path to templates directory
 *
 * That's where all specific plugin's templates are located
 * You can create a directory called 'wp-idea-stream' in your theme
 * copy the content of this folder in it and customize the templates
 * from your theme's 'wp-idea-stream' directory. Templates in there
 * will override plugin's default ones.
 *
 * @package WP Idea Stream
 * @subpackage core/functions
 *
 * @since 2.0.0
 *
 * @uses   wp_idea_stream() to get plugin's main instance
 * @uses   apply_filters() call 'wp_idea_stream_get_templates_dir' to set a different templates dir
 * @return string path to templates directory
 */
function wp_idea_stream_get_templates_dir() {
	return apply_filters( 'wp_idea_stream_get_templates_dir', wp_idea_stream()->templates_dir );
}

/**
 * Set a global var to be used by the plugin at different times
 * during WordPress loading process.
 *
 * @package WP Idea Stream
 * @subpackage core/functions
 *
 * @since 2.0.0
 *
 * @param  string $var_key   the key to access to the globalized value
 * @uses   wp_idea_stream() to get plugin's main instance
 * @param  mixed  $var_value a value to globalize, can be object, array, int.. whatever
 */
function wp_idea_stream_set_idea_var( $var_key = '', $var_value ='' ) {
	return wp_idea_stream()->set_idea_var( $var_key, $var_value );
}

/**
 * Get a global var set thanks to wp_idea_stream_set_idea_var()
 *
 * @package WP Idea Stream
 * @subpackage core/functions
 *
 * @since 2.0.0
 *
 * @param  string $var_key the key to access to the globalized value
 * @uses   wp_idea_stream() to get plugin's main instance
 * @return mixed           the globalized value for the requested key
 */
function wp_idea_stream_get_idea_var( $var_key = '' ) {
	return wp_idea_stream()->get_idea_var( $var_key );
}

/** Post Type (ideas) *********************************************************/

/**
 * Outputs the post type identifier (ideas) for the plugin
 *
 * @package WP Idea Stream
 * @subpackage core/functions
 *
 * @since 2.0.0
 *
 * @uses   wp_idea_stream_get_post_type()
 * @return string the post type identifier
 */
function wp_idea_stream_post_type() {
	echo wp_idea_stream_get_post_type();
}

	/**
	 * Gets the post type identifier (ideas)
	 *
	 * @package WP Idea Stream
	 * @subpackage core/functions
	 *
	 * @since 2.0.0
	 *
	 * @uses   wp_idea_stream() to get plugin's main instance
	 * @uses   apply_filters() call 'wp_idea_stream_get_post_type' to set a different identifier
	 * @return string the post type identifier
	 */
	function wp_idea_stream_get_post_type() {
		return apply_filters( 'wp_idea_stream_get_post_type', wp_idea_stream()->post_type );
	}

/**
 * Gets plugin's main post type init arguments
 *
 * @package WP Idea Stream
 * @subpackage core/functions
 *
 * @since 2.0.0
 *
 * @uses   wp_idea_stream_get_post_type() to get the post type identifier and set the query var
 * @uses   wp_idea_stream_idea_slug() can be customized through plugin's settings
 * @uses   wp_idea_stream_root_slug() can be customized through plugin's settings
 * @uses   wp_idea_stream_user_can() to check if the user can access to admin bar menu
 * @uses   wp_idea_stream_get_category() to get the hierarchical taxonomy identifier of the post type
 * @uses   wp_idea_stream_get_tag() to get the non-hierarchical taxonomy identifier of the post type
 * @uses   wp_idea_stream_get_post_type_caps() to get the ideas post type capabilities
 * @uses   apply_filters() call 'wp_idea_stream_post_type_register_args' to customize post type init arguments
 * @return array the init arguments for the 'ideas' post type
 */
function wp_idea_stream_post_type_register_args() {
	return apply_filters( 'wp_idea_stream_post_type_register_args', array(
		'public'              => true,
		'query_var'           => wp_idea_stream_get_post_type(),
		'rewrite'             => array(
			'slug'            => wp_idea_stream_idea_slug(),
			'with_front'      => false
		),
		'has_archive'         => wp_idea_stream_root_slug(),
		'exclude_from_search' => true,
		'show_in_nav_menus'   => false,
		'show_in_admin_bar'   => wp_idea_stream_user_can( 'wp_idea_stream_ideas_admin' ),
		'menu_icon'           => 'dashicons-lightbulb',
		'supports'            => array( 'title', 'editor', 'author', 'comments', 'revisions' ),
		'taxonomies'          => array(
			wp_idea_stream_get_category(),
			wp_idea_stream_get_tag()
		),
		'capability_type'     => array( 'idea', 'ideas' ),
		'capabilities'        => wp_idea_stream_get_post_type_caps(),
		'delete_with_user'    => true,
		'can_export'          => true,
	) );
}

/**
 * Gets the labels for the plugin's post type
 *
 * @package WP Idea Stream
 * @subpackage core/functions
 *
 * @since 2.0.0
 *
 * @uses   apply_filters() call 'wp_idea_stream_post_type_register_labels' to customize post type labels
 * @return array post type labels
 */
function wp_idea_stream_post_type_register_labels() {
	return apply_filters( 'wp_idea_stream_post_type_register_labels', array(
		'labels' => array(
			'name'               => __( 'Ideas',                  'wp-idea-stream' ),
			'menu_name'          => __( 'IdeaStream',             'wp-idea-stream' ),
			'all_items'          => __( 'All Ideas',              'wp-idea-stream' ),
			'singular_name'      => __( 'Idea',                   'wp-idea-stream' ),
			'add_new'            => __( 'Add New Idea',           'wp-idea-stream' ),
			'add_new_item'       => __( 'Add New Idea',           'wp-idea-stream' ),
			'edit_item'          => __( 'Edit Idea',              'wp-idea-stream' ),
			'new_item'           => __( 'New Idea',               'wp-idea-stream' ),
			'view_item'          => __( 'View Idea',              'wp-idea-stream' ),
			'search_items'       => __( 'Search Ideas',           'wp-idea-stream' ),
			'not_found'          => __( 'No Ideas Found',         'wp-idea-stream' ),
			'not_found_in_trash' => __( 'No Ideas Found in Trash','wp-idea-stream' )
		)
	) );
}

/**
 * Get plugin's post type "category" identifier (category-ideas)
 *
 * @package WP Idea Stream
 * @subpackage core/functions
 *
 * @since 2.0.0
 *
 * @uses   wp_idea_stream() to get plugin's main instance
 * @uses   apply_filters() call 'wp_idea_stream_get_category' to customize category identifier
 * @return string hierarchical taxonomy identifier
 */
function wp_idea_stream_get_category() {
	return apply_filters( 'wp_idea_stream_get_category', wp_idea_stream()->category );
}

/**
 * Gets the "category" taxonomy init arguments
 *
 * @package WP Idea Stream
 * @subpackage core/functions
 *
 * @since 2.0.0
 *
 * @uses   wp_idea_stream_category_slug() can be customized through plugin's settings
 * @uses   wp_idea_stream_get_category_caps()
 * @uses   wp_idea_stream_get_category() to get the category identifier and set the query var
 * @uses   apply_filters() call 'wp_idea_stream_category_register_args' to customize category arguments
 * @return array taxonomy init arguments
 */
function wp_idea_stream_category_register_args() {
	return apply_filters( 'wp_idea_stream_category_register_args', array(
		'rewrite'               => array(
			'slug'              => wp_idea_stream_category_slug(),
			'with_front'        => false,
			'hierarchical'      => false,
		),
		'capabilities'          => wp_idea_stream_get_category_caps(),
		'update_count_callback' => '_update_post_term_count',
		'query_var'             => wp_idea_stream_get_category(),
		'hierarchical'          => true,
		'show_in_nav_menus'     => false,
		'public'                => true,
		'show_tagcloud'         => false,
	) );
}

/**
 * Get the "category" taxonomy labels
 *
 * @package WP Idea Stream
 * @subpackage core/functions
 *
 * @since 2.0.0
 *
 * @uses   apply_filters() call 'wp_idea_stream_category_register_labels' to customize category labels
 * @return array "category" taxonomy labels
 */
function wp_idea_stream_category_register_labels() {
	return apply_filters( 'wp_idea_stream_category_register_labels', array(
		'labels' => array(
			'name'             => __( 'Idea Categories',   'wp-idea-stream' ),
			'singular_name'    => __( 'Idea Category',     'wp-idea-stream' ),
			'edit_item'        => __( 'Edit Category',     'wp-idea-stream' ),
			'update_item'      => __( 'Update Category',   'wp-idea-stream' ),
			'add_new_item'     => __( 'Add New Category',  'wp-idea-stream' ),
			'new_item_name'    => __( 'New Category Name', 'wp-idea-stream' ),
			'all_items'        => __( 'All Categories',    'wp-idea-stream' ),
			'search_items'     => __( 'Search Categories', 'wp-idea-stream' ),
			'parent_item'      => __( 'Parent Category',   'wp-idea-stream' ),
			'parent_item_colon'=> __( 'Parent Category:',  'wp-idea-stream' ),
		)
	) );
}

/**
 * Get plugin's post type "tag" identifier (tag-ideas)
 *
 * @package WP Idea Stream
 * @subpackage core/functions
 *
 * @since 2.0.0
 *
 * @uses   wp_idea_stream() to get plugin's main instance
 * @uses   apply_filters() call 'wp_idea_stream_get_tag' to customize tag identifier
 * @return string non hierarchical taxonomy identifier
 */
function wp_idea_stream_get_tag() {
	return apply_filters( 'wp_idea_stream_get_tag', wp_idea_stream()->tag );
}

/**
 * Gets the "tag" taxonomy init arguments
 *
 * @package WP Idea Stream
 * @subpackage core/functions
 *
 * @since 2.0.0
 *
 * @uses   wp_idea_stream_tag_slug() can be customized through plugin's settings
 * @uses   wp_idea_stream_get_tag_caps()
 * @uses   wp_idea_stream_get_tag() to get the tag identifier and set the query var
 * @uses   apply_filters() call 'wp_idea_stream_tag_register_args' to customize tag arguments
 * @return array taxonomy init arguments
 */
function wp_idea_stream_tag_register_args() {
	return apply_filters( 'wp_idea_stream_tag_register_args', array(
		'rewrite'               => array(
			'slug'              => wp_idea_stream_tag_slug(),
			'with_front'        => false,
			'hierarchical'      => false,
		),
		'capabilities'          => wp_idea_stream_get_tag_caps(),
		'update_count_callback' => '_update_post_term_count',
		'query_var'             => wp_idea_stream_get_tag(),
		'hierarchical'          => false,
		'show_in_nav_menus'     => false,
		'public'                => true,
		'show_tagcloud'         => true,
	) );
}

/**
 * Get the "tag" taxonomy labels
 *
 * @package WP Idea Stream
 * @subpackage core/functions
 *
 * @since 2.0.0
 *
 * @uses   apply_filters() call 'wp_idea_stream_tag_register_labels' to customize tag labels
 * @return array "tag" taxonomy labels
 */
function wp_idea_stream_tag_register_labels() {
	return apply_filters( 'wp_idea_stream_tag_register_labels', array(
		'labels' => array(
			'name'                       => __( 'Idea Tags',                         'wp-idea-stream' ),
			'singular_name'              => __( 'Idea Tag',                          'wp-idea-stream' ),
			'edit_item'                  => __( 'Edit Tag',                          'wp-idea-stream' ),
			'update_item'                => __( 'Update Tag',                        'wp-idea-stream' ),
			'add_new_item'               => __( 'Add New Tag',                       'wp-idea-stream' ),
			'new_item_name'              => __( 'New Tag Name',                      'wp-idea-stream' ),
			'all_items'                  => __( 'All Tags',                          'wp-idea-stream' ),
			'search_items'               => __( 'Search Tags',                       'wp-idea-stream' ),
			'popular_items'              => __( 'Popular Tags',                      'wp-idea-stream' ),
			'separate_items_with_commas' => __( 'Separate tags with commas',         'wp-idea-stream' ),
			'add_or_remove_items'        => __( 'Add or remove tags',                'wp-idea-stream' ),
			'choose_from_most_used'      => __( 'Choose from the most popular tags', 'wp-idea-stream' )
		)
	) );
}

/** Urls **********************************************************************/

/**
 * Gets plugin's post type main url
 *
 * @package WP Idea Stream
 * @subpackage core/functions
 *
 * @since 2.0.0
 *
 * @uses   get_post_type_archive_link()
 * @uses   wp_idea_stream_get_post_type()
 * @uses   apply_filters() call 'wp_idea_stream_get_root_url' to customize post type archive url
 * @return string root url for the post type
 */
function wp_idea_stream_get_root_url() {
	return apply_filters( 'wp_idea_stream_get_root_url', get_post_type_archive_link( wp_idea_stream_get_post_type() ) );
}

/**
 * Gets a specific "category" term url
 *
 * @package WP Idea Stream
 * @subpackage core/functions
 *
 * @since 2.0.0
 *
 * @param  object $category the term to build the url for
 * @uses   wp_idea_stream_get_current_term() to get the current term thanks to queried object
 * @uses   get_term_link() to build the link
 * @uses   wp_idea_stream_get_category() to get the taxonomy identifier
 * @uses   apply_filters() call 'wp_idea_stream_get_category_url' to customize post type term url
 * @return string          url to reach all ideas categorized with the requested term
 */
function wp_idea_stream_get_category_url( $category = null ) {
	if ( empty( $category ) ) {
		$category = wp_idea_stream_get_current_term();
	}

	$term_link = get_term_link( $category, wp_idea_stream_get_category() );

	/**
	 * @param  string $term_link url to reach the ideas categorized with the term
	 * @param  object $category the term for this taxonomy
	 */
	return apply_filters( 'wp_idea_stream_get_category_url', $term_link, $category );
}

/**
 * Gets a specific "tag" term url
 *
 * @package WP Idea Stream
 * @subpackage core/functions
 *
 * @since 2.0.0
 *
 * @param  object $tag the term to build the url for
 * @uses   wp_idea_stream_get_current_term() to get the current term thanks to queried object
 * @uses   get_term_link() to build the link
 * @uses   wp_idea_stream_get_tag() to get the taxonomy identifier
 * @uses   apply_filters() call 'wp_idea_stream_get_tag_url' to customize post type term url
 * @return string          url to reach all ideas tagged with the requested term
 */
function wp_idea_stream_get_tag_url( $tag = '' ) {
	if ( empty( $tag ) ) {
		$tag = wp_idea_stream_get_current_term();
	}

	$term_link = get_term_link( $tag, wp_idea_stream_get_tag() );

	/**
	 * @param  string $term_link url to reach the ideas tagged with the term
	 * @param  object $tag the term for this taxonomy
	 */
	return apply_filters( 'wp_idea_stream_get_tag_url', $term_link, $tag );
}

/**
 * Gets a global redirect url
 *
 * Used after posting an idea failed
 * Defaults to root url
 *
 * @package WP Idea Stream
 * @subpackage core/functions
 *
 * @since 2.0.0
 *
 * @uses   wp_idea_stream_get_root_url()
 * @uses   apply_filters() call 'wp_idea_stream_get_redirect_url' to customize the redirect url
 * @return string the url to redirect the user to
 */
function wp_idea_stream_get_redirect_url() {
	return apply_filters( 'wp_idea_stream_get_redirect_url', wp_idea_stream_get_root_url() );
}

/**
 * Gets the url to the form to submit new ideas
 *
 * So far only adding new ideas is supported, but
 * there will surely be an edit action to allow users
 * to edit their ideas. Reason of the $type param
 *
 * @package WP Idea Stream
 * @subpackage core/functions
 *
 * @since 2.0.0
 *
 * @global $wp_rewrite
 * @param  string $type action (defaults to new)
 * @param  string $idea_name the post name of the idea to edit
 * @uses   wp_idea_stream_addnew_slug() can be customized through plugin's settings
 * @uses   apply_filters() call 'wp_idea_stream_pre_get_form_url' to customize the form url before it has been built
 * @uses   wp_idea_stream_action_slug()
 * @uses   wp_idea_stream_action_rewrite_id()
 * @uses   home_url()
 * @uses   user_trailingslashit()
 * @uses   wp_idea_stream_edit_slug() to get the edit slug
 * @uses   apply_filters() call 'wp_idea_stream_get_form_url' to customize the form url after it has been built
 * @return string the url of the form to add ideas
 */
function wp_idea_stream_get_form_url( $type = '', $idea_name = '' ) {
	global $wp_rewrite;

	if ( empty( $type ) ) {
		$type = wp_idea_stream_addnew_slug();
	}

	/**
	 * Early filter to override form url before being built
	 *
	 * @param mixed false or url to override
	 * @param string $type (only add new for now)
	 */
	$early_form_url = apply_filters( 'wp_idea_stream_pre_get_form_url', false, $type, $idea_name );

	if ( ! empty( $early_form_url ) ) {
		return $early_form_url;
	}

	// Pretty permalinks
	if ( $wp_rewrite->using_permalinks() ) {
		$url = $wp_rewrite->root . wp_idea_stream_action_slug() . '/%' . wp_idea_stream_action_rewrite_id() . '%';

		$url = str_replace( '%' . wp_idea_stream_action_rewrite_id() . '%', $type, $url );
		$url = home_url( user_trailingslashit( $url ) );

	// Unpretty permalinks
	} else {
		$url = add_query_arg( array( wp_idea_stream_action_rewrite_id() => $type ), home_url( '/' ) );
	}

	if ( $type == wp_idea_stream_edit_slug() && ! empty( $idea_name ) ) {
		$url = add_query_arg( wp_idea_stream_get_post_type(), $idea_name, $url );
	}

	/**
	 * Filter to override form url after being built
	 *
	 * @param string url to override
	 * @param string $type add new or edit
	 * @param string $idea_name the post name of the idea to edit
	 */
	return apply_filters( 'wp_idea_stream_get_form_url', $url, $type, $idea_name );
}

/** Feedbacks *****************************************************************/

/**
 * Add a new message to inform user
 *
 * Inspired by BuddyPress's bp_core_add_message() function
 *
 * package WP Idea Stream
 * @subpackage core/functions
 *
 * @since 2.0.0
 *
 * @param  array  $message_data the type and content of the message
 * @uses   wp_parse_args() to merge args with defaults
 * @uses   wp_idea_stream_set_idea_var() to globalize the feedback
 */
function wp_idea_stream_add_message( $message_data = array() ) {
	// Success is the default
	if ( empty( $type ) ) {
		$type = 'success';
	}

	$r = wp_parse_args( $message_data, array(
		'type'    => 'success',
		'content' => __( 'Saved successfully', 'wp-idea-stream' ),
	) );

	// Send the values to the cookie for page reload display
	@setcookie( 'wp-idea-stream-feedback',      $r['content'], time() + 60 * 60 * 24, COOKIEPATH );
	@setcookie( 'wp-idea-stream-feedback-type', $r['type'],    time() + 60 * 60 * 24, COOKIEPATH );

	wp_idea_stream_set_idea_var( 'feedback', $r );
}

/**
 * Sets a new message to inform user
 *
 * Inspired by BuddyPress's bp_core_setup_message() function
 *
 * @package WP Idea Stream
 * @subpackage core/functions
 *
 * @since 2.0.0
 *
 * @uses   wp_idea_stream_get_idea_var() to get the feedback
 * @uses   wp_idea_stream_set_idea_var() to globalize the feedback
 */
function wp_idea_stream_set_user_feedback() {
	// Check Global if any
	$feedback = wp_idea_stream_get_idea_var( 'feedback' );

	// Check cookies if any
	if ( empty( $feedback ) && ! empty( $_COOKIE['wp-idea-stream-feedback'] ) ) {
		wp_idea_stream_set_idea_var( 'feedback', array(
			'type'    => wp_unslash( $_COOKIE['wp-idea-stream-feedback-type'] ),
			'content' => wp_unslash( $_COOKIE['wp-idea-stream-feedback'] ),
		) );
	}

	// Remove cookies if set.
	if ( isset( $_COOKIE['wp-idea-stream-feedback'] ) ) {
		@setcookie( 'wp-idea-stream-feedback', false, time() - 1000, COOKIEPATH );
	}

	if ( isset( $_COOKIE['wp-idea-stream-feedback-type'] ) ) {
		@setcookie( 'wp-idea-stream-feedback-type', false, time() - 1000, COOKIEPATH );
	}
}

/**
 * Displays the feedback message to user
 *
 * Inspired by BuddyPress's bp_core_render_message() function
 *
 * @package WP Idea Stream
 * @subpackage core/functions
 *
 * @since 2.0.0
 *
 * @uses   wp_idea_stream_get_idea_var() to get the feedback
 * @uses   esc_html() to sanitize the content of the feedback
 */
function wp_idea_stream_user_feedback() {
	$feedback = wp_idea_stream_get_idea_var( 'feedback' );

	if ( empty( $feedback ) || ! empty( $feedback['admin_notices'] ) ) {
		return;
	}

	// Display the message
	?>
	<div class="message <?php echo esc_attr( $feedback['type'] ); ?>">
		<p><?php echo esc_html( $feedback['content'] ); ?></p>
	</div>
	<?php
}

/** Rating Ideas **************************************************************/

/**
 * Checks wether the builtin rating system should be used
 *
 * In previous versions of the plugin this was an option that
 * could be deactivated from plugin settings. This is no more
 * the case, as i think like comments, this is a core functionality
 * when managing ideas. To deactivate the ratings, use the filter.
 *
 * @package WP Idea Stream
 * @subpackage core/functions
 *
 * @since 2.0.0
 *
 * @param  int  $default   by default enabled
 * @uses   apply_filters() call 'wp_idea_stream_is_rating_disabled' to deactivate ratings
 * @return bool            True if disabled, false if enabled
 */
function wp_idea_stream_is_rating_disabled( $default = 0 ) {
	return (bool) apply_filters( 'wp_idea_stream_is_rating_disabled', $default );
}

/**
 * Gets a fallback hintlist for ratings
 *
 * @package WP Idea Stream
 * @subpackage core/functions
 *
 * @since 2.0.0
 *
 * @uses  wp_idea_stream_hint_list() to check for a customized list built in plugin's settings
 * @return array the hintlist
 */
function wp_idea_stream_get_hint_list() {
	$hintlist = wp_idea_stream_hint_list();

	if ( empty( $hintlist ) ) {
		$hintlist = array(
			esc_html__( 'bad',      'wp-idea-stream' ),
			esc_html__( 'poor',     'wp-idea-stream' ),
			esc_html__( 'regular',  'wp-idea-stream' ),
			esc_html__( 'good',     'wp-idea-stream' ),
			esc_html__( 'gorgeous', 'wp-idea-stream' )
		);
	}

	return $hintlist;
}

/**
 * Count rating stats for a specific idea or gets the rating of a specific user for a given idea
 *
 * @package WP Idea Stream
 * @subpackage core/functions
 *
 * @since 2.0.0
 *
 * @param  integer $id      the ID of the idea object
 * @param  integer $user_id the user id
 * @param  boolean $details whether to include detailed stats
 * @uses   wp_idea_stream() to get plugin's main instance
 * @uses   get_post_meta() to get the idea rates
 * @uses   apply_filters() call 'wp_idea_stream_get_user_ratings' to cheat on user's rating
 * @uses   number_format_i18n() to format the average
 * @uses   apply_filters() call 'wp_idea_stream_count_ratings' to cheat on idea's rating stats
 * @return mixed            int|array the rate of the user or the stats
 */
function wp_idea_stream_count_ratings( $id = 0, $user_id = 0, $details = false ) {
	// Init a default array
	$retarray = array(
		'average' => 0,
		'users'   => array()
	);
	// Init a default user rating
	$user_rating = 0;

	// No idea, try to find it in the query loop
	if ( empty( $id ) ) {
		if ( ! wp_idea_stream()->query_loop->idea->ID ) {
			return $retarray;
		} else {
			$id = wp_idea_stream()->query_loop->idea->ID;
		}
	}

	// Get all the rates for the idea
	$rates = get_post_meta( $id, '_ideastream_rates', true );

	// Build the stats
	if ( ! empty( $rates ) && is_array( $rates ) ) {
		foreach ( $rates as $rate => $users ) {
			// We need the user's rating
			if ( ! empty( $user_id ) && in_array( $user_id, (array) $users['user_ids'] ) ) {
				$user_rating = $rate;

			// We need average rating
			} else {
				$retarray['users'] = array_merge( $retarray['users'], (array) $users['user_ids'] );
				$retarray['average'] += $rate * count( (array) $users['user_ids'] );

				if ( ! empty( $details ) ) {
					$retarray['details'][ $rate ] = (array) $users['user_ids'];
				}
			}
		}
	}

	// Return the user rating
	if ( ! empty( $user_id ) ) {
		/**
		 * @param  int $user_rating the rate given by the user to the idea
		 * @param  int $id the ID of the idea
		 * @param  int $user_id the user id who rated the idea
		 */
		return apply_filters( 'wp_idea_stream_get_user_ratings', $user_rating, $id, $user_id );
	}

	if ( ! empty( $retarray['users'] ) ) {
		$retarray['average'] = number_format( $retarray['average'] / count( $retarray['users'] ), 1 );
	} else {
		$retarray['average'] = 0;
	}

	/**
	 * @param  array $retarray the idea rating stats
	 * @param  int $id the ID of the idea
	 * @param  array $rates all idea rates organized in an array
	 */
	return apply_filters( 'wp_idea_stream_count_ratings', $retarray, $id, $rates );
}

/**
 * Delete a specific rate for a given idea
 *
 * This action is only available from the idea edit Administration screen
 * @see  WP_Idea_Stream_Admin->maybe_delete_rate() in admin/admin
 *
 * @package WP Idea Stream
 * @subpackage core/functions
 *
 * @since 2.0.0
 *
 * @param  int $idea    the ID of the idea
 * @param  int $user_id the ID of the user
 * @uses   get_post_meta() to get the rates
 * @uses   update_post_meta() to update the rates and the average rate
 * @uses   wp_idea_stream_count_ratings() to get the average
 * @uses   do_action() call 'wp_idea_stream_deleted_rate' to perform custom actions
 *                     once the rate has been deleted
 * @return mixed       string the new average rating or false if no more rates
 */
function wp_idea_stream_delete_rate( $idea = 0, $user_id = 0 ) {
	if ( empty( $idea ) || empty( $user_id ) ) {
		return false;
	}

	$rates = get_post_meta( $idea, '_ideastream_rates', true );

	if ( empty( $rates ) ) {
		return false;
	} else {
		foreach ( $rates as $rate => $users ) {
			if ( in_array( $user_id, (array) $users['user_ids'] ) ) {
				$rates[ $rate ]['user_ids'] = array_diff( $users['user_ids'], array( $user_id ) );

				// Unset the rate if no more users.
				if ( count( $rates[ $rate ]['user_ids'] ) == 0 ) {
					unset( $rates[ $rate ] );
				}
			}
		}
	}

	if ( update_post_meta( $idea, '_ideastream_rates', $rates ) ) {
		$ratings = wp_idea_stream_count_ratings( $idea );
		update_post_meta( $idea, '_ideastream_average_rate', $ratings['average'] );

		/**
		 * @param  int $idea the ID of the idea
		 * @param  int $user_id the ID of the user
		 * @param  string       the formatted average.
		 */
		do_action( 'wp_idea_stream_deleted_rate', $idea, $user_id, $ratings['average'] );

		return $ratings['average'];
	} else {
		return false;
	}
}

/**
 * Saves a new rate for the idea
 *
 * @package WP Idea Stream
 * @subpackage core/functions
 *
 * @since 2.0.0
 *
 * @param  int $idea    the ID of the idea
 * @param  int $user_id the ID of the user
 * @param  int $rate    the rate of the user
 * @uses   get_post_meta() to get previous rates
 * @uses   update_post_meta() to update the rates
 * @uses   wp_idea_stream_count_ratings() to get the average rate
 * @uses   do_action() call 'wp_idea_stream_added_rate' to perform custom actions
 *                     once the rate has been added
 * @return mixed       string the new average rating or false if no more rates
 */
function wp_idea_stream_add_rate( $idea = 0, $user_id = 0, $rate = 0 ) {
	if ( empty( $idea ) || empty( $user_id ) || empty( $rate ) ) {
		return false;
	}

	$rates = get_post_meta( $idea, '_ideastream_rates', true );

	if ( empty( $rates ) ) {
		$rates = array( $rate => array( 'user_ids' => array( $user_id ) ) );
	} else if ( ! empty( $rates[ $rate ] ) && ! in_array( $user_id, $rates[ $rate ]['user_ids'] ) ) {
		$rates[ $rate ]['user_ids'] = array_merge( $rates[ $rate ]['user_ids'], array( $user_id ) );
	} else if ( empty( $rates[ $rate ] ) ) {
		$rates = $rates + array( $rate => array( 'user_ids' => array( $user_id ) ) );
	} else {
		return false;
	}

	if ( update_post_meta( $idea, '_ideastream_rates', $rates ) ) {
		$ratings = wp_idea_stream_count_ratings( $idea );
		update_post_meta( $idea, '_ideastream_average_rate', $ratings['average'] );

		/**
		 * @param  int $idea the ID of the idea
		 * @param  int $user_id the ID of the user
		 * @param  int $rate the user's rating
		 * @param  string       the formatted average.
		 */
		do_action( 'wp_idea_stream_added_rate', $idea, $user_id, $rate, $ratings['average'] );

		return $ratings['average'];
	} else {
		return false;
	}
}

/**
 * Intercepts the user ajax action to rate the idea
 *
 * @package WP Idea Stream
 * @subpackage core/functions
 *
 * @since 2.0.0
 *
 * @uses   wp_idea_stream_user_can() to check if the user has the capability to rate the idea
 * @uses   wp_idea_stream_users_current_user_id() to get current user id
 * @uses   check_ajax_referer() to be sure the action was performed from the site
 * @uses   wp_idea_stream_add_rate() to save the user rating
 * @return mixed the average rate or 0
 */
function wp_idea_stream_ajax_rate() {
	if ( ! wp_idea_stream_user_can( 'rate_ideas' ) ) {
		exit( '0' );
	}

	$user_id = wp_idea_stream_users_current_user_id();
	$idea = ! empty( $_POST['idea'] ) ? absint( $_POST['idea'] ) : 0;
	$rate = ! empty( $_POST['rate'] ) ? absint( $_POST['rate'] ) : 0;

	check_ajax_referer( 'wp_idea_stream_rate', 'wpnonce' );

	$new_average_rate = wp_idea_stream_add_rate( $idea, $user_id, $rate );

	if ( empty( $new_average_rate ) ) {
		exit( '0' );
	} else {
		exit( $new_average_rate );
	}
}

/**
 * Order the ideas by rates when requested
 *
 * This function is hooking to WordPress 'posts_clauses' filter. As the
 * rating query is first built by using a specific WP_Meta_Query, we need
 * to also make sure the ORDER BY clause of the sql query is customized.
 *
 * @package WP Idea Stream
 * @subpackage core/functions
 *
 * @since 2.0.0
 *
 * @param  array    $clauses  the idea query sql parts
 * @param  WP_Query $wp_query the WordPress query object
 * @uses   wp_idea_stream_is_ideastream() to check it's front end plugin's territory
 * @uses   wp_idea_stream_is_admin() to check it's back end plugin's territory
 * @uses   wp_idea_stream_is_orderby() to check the rates count is the requested order
 * @return array              new order clauses if needed
 */
function wp_idea_stream_set_rates_count_orderby( $clauses = array(), $wp_query = null ) {

	if ( ( wp_idea_stream_is_ideastream() || wp_idea_stream_is_admin() || wp_idea_stream_get_idea_var( 'rating_widget' ) ) && wp_idea_stream_is_orderby( 'rates_count' ) ) {
		preg_match( '/\(?(\S*).meta_key = \'_ideastream_average_rate\'/', $clauses['where'], $matches );
		if ( ! empty( $matches[1] ) ) {
			// default order
			$order = 'DESC';

			// Specific case for IdeaStream administration.
			if ( ! empty( $clauses['orderby'] ) && 'ASC' == strtoupper( substr( $clauses['orderby'], -3 ) ) ) {
				$order = 'ASC';
			}

			$clauses['orderby'] = "{$matches[1]}.meta_value + 0 {$order}";
		}
	}

	return $clauses;
}

/** Utilities *****************************************************************/

/**
 * Creates a specific excerpt for the content of an idea
 *
 * @package WP Idea Stream
 * @subpackage core/functions
 *
 * @since 2.0.0
 *
 * @param  string  $text   the content to truncate
 * @param  integer $length the number of words
 * @param  string  $more   the more string
 * @uses   strip_shortcodes()
 * @uses   apply_filters() call 'wp_idea_stream_create_excerpt_text' to filter the excerpt content
 * @uses   wp_trim_words()
 * @return string          the excerpt of an idea
 */
function wp_idea_stream_create_excerpt( $text = '', $length = 55, $more = ' [&hellip;]' ) {
	if ( empty( $text ) ) {
		return $text;
	}

	$text = strip_shortcodes( $text );

	/**
	 * Used internally to sanitize outputs
	 * @see  core/filters
	 *
	 * @param string $text the content without shortcodes
	 */
	$text = apply_filters( 'wp_idea_stream_create_excerpt_text', $text );

	$text = str_replace( ']]>', ']]&gt;', $text );

	/**
	 * Filter the number of words in an excerpt.
	 */
	$excerpt_length = apply_filters( 'excerpt_length', $length );
	/**
	 * Filter the string in the "more" link displayed after a trimmed excerpt.
	 */
	$excerpt_more = apply_filters( 'excerpt_more', $more );

	return wp_trim_words( $text, $excerpt_length, $excerpt_more );
}

/**
 * Prepare the content to be output in a csv file
 *
 * @package WP Idea Stream
 * @subpackage core/functions
 *
 * @since 2.1.0
 *
 * @param  string $content the content
 * @uses   apply_filters() call 'wp_idea_stream_generate_csv_content' to add extra formatting stuff
 * @return string          the content to be displayed in a csv file
 */
function wp_idea_stream_generate_csv_content( $content = '' ) {
	// Avoid some chars
	$content = str_replace( array( '&#8212;', '"' ), array( 0, "'" ), $content );

	// Strip shortcodes
	$content = strip_shortcodes( $content );

	// Strip slashes
	$content = wp_unslash( $content );

	// Strip all tags
	$content = wp_strip_all_tags( $content, true );

	return apply_filters( 'wp_idea_stream_generate_csv_content', $content );
}

/**
 * Specific tag cloud count text callback
 *
 * By Default, WordPress uses "topic/s", This will
 * make sure "idea/s" will be used instead. Unfortunately
 * it's only possible in front end tag clouds.
 *
 * @package WP Idea Stream
 * @subpackage core/functions
 *
 * @since 2.0.0
 *
 * @param  int $count Number of ideas associated with the tag
 * @uses   number_format_i18n()
 * @return string     the count text for ideas
 */
function wp_idea_stream_tag_cloud_count_callback( $count = 0 ) {
	return sprintf( _nx( '%s idea', '%s ideas', $count, 'ideas tag cloud count text', 'wp-idea-stream' ), number_format_i18n( $count )  );
}

/**
 * Filters the tag cloud args by referencing a specific count text callback
 * if the plugin's "tag" taxonomy is requested.
 *
 * @package WP Idea Stream
 * @subpackage core/functions
 *
 * @since 2.0.0
 *
 * @param  array  $args the tag cloud arguments
 * @uses   wp_idea_stream_get_tag()
 * @return array        the arguments with the new count text callback if needed
 */
function wp_idea_stream_tag_cloud_args( $args = array() ) {
	if( ! empty( $args['taxonomy'] ) && wp_idea_stream_get_tag() == $args['taxonomy'] ) {
		$args['topic_count_text_callback'] = 'wp_idea_stream_tag_cloud_count_callback';
	}

	return $args;
}

/**
 * Generates an ideas tag cloud
 *
 * Used when writing a new idea to allow the author to choose
 * one or more popular idea tags.
 *
 * @package WP Idea Stream
 * @subpackage core/functions
 *
 * @since 2.0.0
 *
 * @param  integer $number number of tag to display
 * @param  array   $args   the tag cloud args
 * @uses   get_terms()
 * @uses   wp_idea_stream_get_tag()
 * @uses   wp_parse_args()
 * @uses   wp_idea_stream_tag_cloud_args()
 * @uses   wp_generate_tag_cloud()
 * @return array           associative array containing the number of tags and the content of the cloud.
 */
function wp_idea_stream_generate_tag_cloud( $number = 10, $args = array() ) {
	$tags = get_terms( wp_idea_stream_get_tag(), apply_filters( 'wp_idea_stream_generate_tag_cloud_args',
		array( 'number' => $number, 'orderby' => 'count', 'order' => 'DESC' )
	) );

	if ( empty( $tags ) ) {
		return;
	}

	foreach ( $tags as $key => $tag ) {
		$tags[ $key ]->link = '#';
		$tags[ $key ]->id = $tag->term_id;
	}

	$args = wp_parse_args( $args,
		wp_idea_stream_tag_cloud_args( array( 'taxonomy' => wp_idea_stream_get_tag() ) )
	);

	$retarray = array(
		'number'   => count( $tags ),
		'tagcloud' => wp_generate_tag_cloud( $tags, $args )
	);

	return apply_filters( 'wp_idea_stream_generate_tag_cloud', $retarray );
}

/**
 * Filters WP Editor Buttons depending on plugin's settings.
 *
 * @package WP Idea Stream
 * @subpackage core/functions
 *
 * @since 2.0.0
 *
 * @param  array  $buttons the list of buttons for the editor
 * @uses   wp_idea_stream_idea_editor_link() should we include the link/unlink button ?
 * @uses   wp_idea_stream_idea_editor_image() should we include the image (url) button ?
 * @return array           the filtered list of buttons to match plugin's needs
 */
function wp_idea_stream_teeny_button_filter( $buttons = array() ) {

	$remove_buttons = array(
		'wp_more',
		'spellchecker',
		'wp_adv',
	);

	if ( ! wp_idea_stream_idea_editor_link() ) {
		$remove_buttons = array_merge( $remove_buttons, array(
			'link',
			'unlink',
		) );
	}

	// Remove unused buttons
	$buttons = array_diff( $buttons, $remove_buttons );

	// Eventually add the image button
	if ( wp_idea_stream_idea_editor_image() ) {
		$buttons = array_diff( $buttons, array( 'fullscreen' ) );
		array_push( $buttons, 'image', 'fullscreen' );
	}

	return $buttons;
}

/**
 * Adds wp_idea_stream to global cache groups
 *
 * Mainly used to cach comments about ideas count
 *
 * @package WP Idea Stream
 * @subpackage core/functions
 *
 * @since 2.0.0
 *
 * @uses wp_cache_add_global_groups()
 */
function wp_idea_stream_cache_global_group() {
	wp_cache_add_global_groups( array( 'wp_idea_stream' ) );
}

/**
 * Adds a shortcut to Idea Stream Backend using the appearence menus
 *
 * While developing the plugin i've found it usefull to be able to easily access
 * to IdeaStream backend from front end, so i've left it. You can disable it by using
 * the filer.
 *
 * @package WP Idea Stream
 * @subpackage core/functions
 *
 * @since 2.0.0
 *
 * @param  WP_Admin_Bar $wp_admin_bar WP_Admin_Bar instance
 * @uses   apply_filters() call 'wp_idea_stream_adminbar_menu' to disable the menu by returning false
 * @uses   wp_idea_stream_user_can() to check for user's capability
 * @uses   add_query_arg()
 * @uses   wp_idea_stream_get_post_type()
 * @uses   admin_url()
 */
function wp_idea_stream_adminbar_menu( $wp_admin_bar = null ){
	$use_admin_bar = apply_filters( 'wp_idea_stream_adminbar_menu', true );

	if ( empty( $use_admin_bar ) ) {
		return;
	}

	if ( ! empty( $wp_admin_bar ) && wp_idea_stream_user_can( 'edit_ideas' ) ) {
		$menu_url = add_query_arg( 'post_type', wp_idea_stream_get_post_type(), admin_url( 'edit.php' ) );
		$wp_admin_bar->add_menu( array(
			'parent' => 'appearance',
			'id'     => 'ideastream',
			'title'  => _x( 'IdeaStream', 'Admin bar menu', 'wp-idea-stream' ),
			'href'   => $menu_url,
		) );
	}
}

/**
 * Checks wether signups are allowed
 *
 * @package WP Idea Stream
 * @subpackage core/functions
 *
 * @since 2.1.0
 *
 * @return bool true if signups are allowed and not on a multisite config, false otherwise
 */
function wp_idea_stream_is_signup_allowed() {
	// First step will not include multisite configs
	if ( is_multisite() ) {
		return false;
	}

	return (bool) get_option( 'users_can_register', false );
}
