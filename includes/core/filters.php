<?php
/**
 * WP Idea Stream Filters.
 *
 * List of main Filter hooks used in the plugin
 *
 * @package WP Idea Stream
 * @subpackage core/filters
 *
 * @since 2.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

add_filter( 'template_include',          'wp_idea_stream_set_template',                 10, 1 );
add_filter( 'wp_title_parts',            'wp_idea_stream_title',                        10, 1 );
add_filter( 'wp_title',                  'wp_idea_stream_title_adjust',                 20, 3 );
add_filter( 'body_class',                'wp_idea_stream_body_class',                   10, 2 );
add_filter( 'map_meta_cap',              'wp_idea_stream_map_meta_caps',                10, 4 );
add_filter( 'widget_tag_cloud_args',     'wp_idea_stream_tag_cloud_args',               10, 1 );
add_filter( 'wp_nav_menu_objects',       'wp_idea_stream_wp_nav',                       10, 2 );
add_filter( 'get_edit_post_link',        'wp_idea_stream_edit_post_link',               10, 2 );
add_filter( 'get_edit_comment_link',     'wp_idea_stream_edit_comment_link',            10, 1 );
add_filter( 'comments_open',             'wp_idea_stream_comments_open',                10, 2 );
add_filter( 'heartbeat_received',        'wp_idea_stream_ideas_heartbeat_check_locked', 10, 2 );
add_filter( 'heartbeat_nopriv_received', 'wp_idea_stream_ideas_heartbeat_check_locked', 10, 2 );

// Prefix idea's title in case of private/protected
add_filter( 'private_title_format',   'wp_idea_stream_ideas_private_title_prefix',   10, 2 );
add_filter( 'protected_title_format', 'wp_idea_stream_ideas_protected_title_prefix', 10, 2 );

// Order by rates count
add_filter( 'posts_clauses', 'wp_idea_stream_set_rates_count_orderby', 10, 2 );

// Sticky Ideas
add_filter( 'the_posts', 'wp_idea_stream_ideas_stick_ideas', 10, 2 );

// Filter comment author urls just after BuddyPress
add_filter( 'comments_array', 'wp_idea_stream_comments_append_profile_url',  11, 2 );

// Formating loop tags
add_filter( 'wp_idea_stream_ideas_get_title', 'wptexturize'   );
add_filter( 'wp_idea_stream_ideas_get_title', 'convert_chars' );
add_filter( 'wp_idea_stream_ideas_get_title', 'trim'          );

add_filter( 'wp_idea_stream_ideas_get_title_edit', 'strip_tags', 1 );
add_filter( 'wp_idea_stream_ideas_get_title_edit', 'wp_unslash', 5 );

add_filter( 'wp_idea_stream_create_excerpt_text', 'strip_tags',        1 );
add_filter( 'wp_idea_stream_create_excerpt_text', 'force_balance_tags'   );
add_filter( 'wp_idea_stream_create_excerpt_text', 'wptexturize'          );
add_filter( 'wp_idea_stream_create_excerpt_text', 'convert_smilies'      );
add_filter( 'wp_idea_stream_create_excerpt_text', 'convert_chars'        );
add_filter( 'wp_idea_stream_create_excerpt_text', 'wpautop'              );
add_filter( 'wp_idea_stream_create_excerpt_text', 'wp_unslash',        5 );
add_filter( 'wp_idea_stream_create_excerpt_text', 'make_clickable',    9 );

add_filter( 'wp_idea_stream_ideas_get_content', 'wptexturize'          );
add_filter( 'wp_idea_stream_ideas_get_content', 'convert_smilies'      );
add_filter( 'wp_idea_stream_ideas_get_content', 'convert_chars'        );
add_filter( 'wp_idea_stream_ideas_get_content', 'wpautop'              );
add_filter( 'wp_idea_stream_ideas_get_content', 'wp_unslash',        5 );
add_filter( 'wp_idea_stream_ideas_get_content', 'make_clickable',    9 );
add_filter( 'wp_idea_stream_ideas_get_content', 'force_balance_tags'   );

add_filter( 'wp_idea_stream_ideas_get_editor_content', 'wp_unslash'  , 5                );
add_filter( 'wp_idea_stream_ideas_get_editor_content', 'wp_kses_post'                   );
add_filter( 'wp_idea_stream_ideas_get_editor_content', 'wpautop'                        );
add_filter( 'wp_idea_stream_ideas_get_editor_content', 'wp_idea_stream_format_to_edit'  );

add_filter( 'wp_idea_stream_comments_get_comment_excerpt', 'strip_tags',        1 );
add_filter( 'wp_idea_stream_comments_get_comment_excerpt', 'force_balance_tags'   );
add_filter( 'wp_idea_stream_comments_get_comment_excerpt', 'wptexturize'          );
add_filter( 'wp_idea_stream_comments_get_comment_excerpt', 'convert_smilies'      );
add_filter( 'wp_idea_stream_comments_get_comment_excerpt', 'convert_chars'        );
add_filter( 'wp_idea_stream_comments_get_comment_excerpt', 'wpautop'              );
add_filter( 'wp_idea_stream_comments_get_comment_excerpt', 'wp_unslash',        5 );
add_filter( 'wp_idea_stream_comments_get_comment_excerpt', 'make_clickable',    9 );

add_filter( 'wp_idea_stream_users_get_user_profile_description', 'make_clickable', 9 );

add_filter( 'wp_idea_stream_is_signup_allowed', 'wp_idea_stream_buddypress_is_managing_signup', 10, 1 );
