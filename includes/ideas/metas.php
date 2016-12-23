<?php
/**
 * Metadatas for ideas.
 *
 * @package WP Idea Stream\ideas
 * @subpackage metas
 *
 * @since 2.4.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Register metas for Ideas.
 *
 * @since  2.4.0
 */
function wp_idea_stream_metas_register_defaults() {
	if ( wp_idea_stream_is_rating_disabled() ) {
		return;
	}

	$default_metas = array(
		'_ideastream_average_rate' => array(
			'sanitize_callback' => 'wp_idea_stream_metas_sanitize_value',
			'type'              => 'string',
			'description'       => 'The average rate an idea received',
			'single'            => true,
			'show_in_rest'      => array(
				'name' => 'idea_average_rate',
			),
		),
		'_ideastream_rates' => array(
			'sanitize_callback' => 'wp_idea_stream_metas_sanitize_value',
			'type'              => 'string',
			'description'       => 'The rates an idea received',
			'single'            => true,
			'show_in_rest'      => array(
				'name'             => 'idea_rates',
				'prepare_callback' => 'wp_idea_stream_metas_prepare_rates',
			),
		),
	);

	foreach ( $default_metas as $key_meta => $meta_args ) {
		register_meta(
			'post',    // We need to use post here.
			$key_meta, // The meta key to register
			$meta_args // The meta args
		);
	}
}
add_action( 'wp_idea_stream_init', 'wp_idea_stream_metas_register_defaults', 15 );

/**
 * Sanitize idea metas.
 *
 * @since  2.4.0
 *
 * @param  string|array $value    The value to save.
 * @param  string       $meta_key The key of the meta to save.
 * @return string|array           The sanitized value.
 */
function wp_idea_stream_metas_sanitize_value( $value = '', $meta_key = '' ) {
	if ( '_ideastream_average_rate' === $meta_key ) {
		$value = esc_html( $value );
	} elseif ( '_ideastream_rates' === $meta_key ) {
		$value = (array) $value;
	}

	return $value;
}

/**
 * Prepare ratings for Rest reponses.
 *
 * @since  2.4.0
 *
 * @param  array           $value   The raw ratings.
 * @param  WP_REST_Request $request Rest request.
 * @param  array           $args    Rest args.
 * @return array           The ratings for the Rest response.
 */
function wp_idea_stream_metas_prepare_rates( $value, $request, $args ) {
	if ( empty( $value ) ) {
		return array();
	}

	foreach ( (array) $value as $rate_level => $users ) {
		$value[ $rate_level ] = array_unique( array_values( $users['user_ids'] ) );
	}

	return $value;
}
