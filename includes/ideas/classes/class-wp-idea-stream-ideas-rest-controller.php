<?php
/**
 * WP Idea Stream Ideas Rest Controller Class.
 *
 * @package WP Idea Stream\ideas\classes
 *
 * @since 2.4.0 Ideas are readonly
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( class_exists( 'WP_REST_Posts_Controller' ) ) :

/**
 * The Custom Rest controller for Ideas.
 *
 * @since  2.4.0
 */
class WP_Idea_Stream_Ideas_REST_Controller extends WP_REST_Posts_Controller {

	/**
	 * Registers the routes for ideas.
	 *
	 * @since 2.4.0
	 * @access public
	 */
	public function register_routes() {
		// Register regular routes.
		parent::register_routes();

		// Register the rate route.
		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)/rate', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_rate' ),
				'permission_callback' => array( $this, 'update_rate_permissions_check' ),
				'args'                =>  array(
					'id' => array(
						'validate_callback' => function( $param, $request, $key ) {
							return is_numeric( $param );
						}
					),
				),
			),
		) );
	}

	/**
	 * Returns a feedback to inform the method is not supported yet by WP Idea Stream.
	 *
	 * @since 2.4.0
	 * @access public
	 *
	 * @param  string $method The Rest request.
	 * @return WP_Error       The feedback for the method.
	 */
	public function not_supported( $method = 'CREATE' ) {
		return new WP_Error( 'rest_not_supported', sprintf( __( 'The %s method for ideas is not supported yet.', 'wp-idea-stream' ), strtoupper( $method ) ), array( 'status' => 400 ) );
	}

	/**
	 * Temporarly Adds specific idea metas to the registered post metas.
	 *
	 * @since 2.4.0
	 * @access public
	 */
	public function register_post_type_only_metas() {
		$this->idea_fields = get_registered_meta_keys( $this->post_type );

		foreach( $this->idea_fields as $k_field => $idea_field ) {
			register_meta( 'post', $k_field, $idea_field );
		}
	}

	/**
	 * Removes specific idea metas from the registered post metas.
	 *
	 * @since 2.4.0
	 * @access public
	 */
	public function unregister_post_type_only_metas() {
		if ( empty( $this->idea_fields ) ) {
			$this->idea_fields = get_registered_meta_keys( $this->post_type );
		}

		foreach( array_keys( $this->idea_fields ) as $idea_field ) {
			unregister_meta_key( 'post', $idea_field );
		}
	}

	/**
	 * Retrieves a collection of ideas.
	 *
	 * @since 2.4.0
	 * @access public
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_items( $request ) {
		$this->register_post_type_only_metas();

		$response = parent::get_items( $request );

		$this->unregister_post_type_only_metas();

		return $response;
	}

	/**
	 * Retrieves a single idea.
	 *
	 * @since 2.4.0
	 * @access public
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_item( $request ) {
		$this->register_post_type_only_metas();

		$response = parent::get_item( $request );

		$this->unregister_post_type_only_metas();

		return $response;
	}

	/**
	 * Creates a single idea.
	 *
	 * @since 2.4.0
	 * @access public
	 *
	 * @param  WP_REST_Request   $request Full details about the request.
	 * @return WP_Error Response          WP_Error containing the not supported feedback.
	 */
	public function create_item( $request ) {
		return $this->not_supported( WP_REST_Server::CREATABLE );
	}

	/**
	 * Updates a single idea.
	 *
	 * @since 2.4.0
	 * @access public
	 *
	 * @param  WP_REST_Request   $request Full details about the request.
	 * @return WP_Error Response          WP_Error containing the not supported feedback.
	 */
	public function update_item( $request ) {
		return $this->not_supported( WP_REST_Server::EDITABLE );
	}

	/**
	 * Deletes a single idea.
	 *
	 * @since 2.4.0
	 * @access public
	 *
	 * @param  WP_REST_Request   $request Full details about the request.
	 * @return WP_Error Response          WP_Error containing the not supported feedback.
	 */
	public function delete_item( $request ) {
		return $this->not_supported( WP_REST_Server::DELETABLE );
	}

	/**
	 * Check the user can rate the idea.
	 *
	 * @since 2.4.0
	 * @access public
	 *
	 * @param  WP_REST_Request $request The Rest request.
	 * @return bool                     True if the user can vote. False otherwise.
	 */
	public function update_rate_permissions_check( WP_REST_Request $request ) {
		return current_user_can( 'rate_ideas' );
	}

	/**
	 * Save the user rating for the idea.
	 *
	 * @since 2.4.0
	 * @access public
	 *
	 * @param  WP_REST_Request $request The Rest request.
	 * @return array                    The Rest response.
	 */
	public function update_rate( WP_REST_Request $request ) {
		$idea_id = (int) $request->get_param( 'id' );
		$rating  = (int) $request->get_param( 'rating' );
		$user_id = wp_idea_stream_users_current_user_id();

		$response = array( 'success' => false );
		if ( empty( $idea_id ) || empty( $rating ) || empty( $user_id ) ) {
			return $response;
		}

		$average_rate = wp_idea_stream_add_rate( $idea_id, $user_id, $rating );

		if ( ! $average_rate ) {
			return $response;
		}

		return array(
			'success'           => true,
			'idea_average_rate' => $average_rate,
		);
	}
}

endif;
