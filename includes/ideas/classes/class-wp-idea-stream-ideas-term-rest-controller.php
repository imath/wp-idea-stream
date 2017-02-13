<?php
/**
 * WP Idea Stream Ideas Term Rest Controller Class.
 *
 * @package WP Idea Stream\ideas\classes
 *
 * @since 2.4.0 Terms are readonly
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( class_exists( 'WP_REST_Terms_Controller' ) ) :

/**
 * The Custom Rest controller for Idea terms.
 *
 * @since  2.4.0
 */
class WP_Idea_Stream_Ideas_Term_REST_Controller extends WP_REST_Terms_Controller {

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
		$tax_obj = get_taxonomy( $this->taxonomy );

		return new WP_Error( 'rest_not_supported', sprintf( __( 'The %1$s method for %2$s is not supported yet.', 'wp-idea-stream' ), strtoupper( $method ), strtolower( $tax_obj->name ) ), array( 'status' => 400 ) );
	}

	/**
	 * Creates a term for the taxonomy.
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
	 * Updates a term for the taxonomy.
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
	 * Deletes a term for the taxonomy.
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
}

endif ;
