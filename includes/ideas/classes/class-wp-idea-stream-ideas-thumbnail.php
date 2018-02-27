<?php
/**
 * WP Idea Stream Ideas Thumbnail Class.
 *
 * @package WP Idea Stream\ideas\classes
 *
 * @since 2.4.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Class to side upload Idea Thumbnails.
 *
 * @since 2.3.0
 */
class WP_Idea_Stream_Ideas_Thumbnail {
	protected static $instance = null;

	/**
	 * Set the class
	 *
	 * @param string $src the link to the image to side upload
	 * @param int    $post_id the ID of the post set the featured image for
	 */
	function __construct( $src, $post_id ) {
		// Set vars
		$this->src          = $src;
		$this->post_id      = $post_id;
		$this->thumbnail_id = 0;

		// Process
		$this->includes();
		$this->upload();
	}

	/**
	 * Get the required files
	 */
	private function includes() {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';
	}

	/**
	 * Side upload the image and set it as the post thumbnail
	 */
	private function upload() {
		// Not an image ?
		if ( ! preg_match( '/[^\?]+\.(?:jpe?g|jpe|gif|png)(?:\?|$)/i', $this->src ) ) {
			$this->result = new WP_Error( 'not_an_image', __( 'This image file type is not supported.', 'wp-idea-stream' ) );

		// We can proceed
		} else {
			// First there can be a chance the src is already saved as an attachment
			$thumbnail_id = self::get_existing_attachment( $this->src );

			if ( ! empty( $thumbnail_id ) ) {
				$this->result = set_post_thumbnail( $this->post_id, $thumbnail_id );

			// Otherwise, we need to save it
			} else {
				// Temporarly filter the attachment url to set the Thumbnail ID
				add_filter( 'wp_get_attachment_url', array( $this, 'intercept_id' ), 10, 2 );

				$this->new_src = media_sideload_image( $this->src, $this->post_id, null, 'src' );

				remove_filter( 'wp_get_attachment_url', array( $this, 'intercept_id' ), 10, 2 );

				if ( ! is_wp_error( $this->new_src ) && isset( $this->thumbnail_id ) ) {
					$this->result = set_post_thumbnail( $this->post_id, $this->thumbnail_id );
					update_post_meta( $this->thumbnail_id, '_ideastream_original_src', esc_url_raw( $this->src ) );
				} else {
					$this->result = new WP_Error( 'sideload_failed' );
				}
			}
		}
	}

	/**
	 * Intercept the Attachment ID.
	 *
	 * @param string $url the       link to the attachment just created
	 * @param int    $attachment_id the ID of attachment
	 */
	public function intercept_id( $url = '', $attachment_id = 0 ) {
		if ( ! empty( $attachment_id ) ) {
			$this->thumbnail_id = $attachment_id;
		}

		return $url;
	}

	/**
	 * Check if a featured image has already been uploaded and use it
	 *
	 * @param  string $src original image src
	 * @return int the attachment id containing the featured image
	 */
	public static function get_existing_attachment( $src = '' ) {
		global $wpdb;

		if ( empty( $src ) ) {
			return false;
		}

		return $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_ideastream_original_src' AND meta_value = %s", esc_url_raw( $src ) ) );
	}

	/**
	 * Temporarly include the meta value to WP_Query post fields
	 *
	 * @param  string $fields comma separated list of db fields
	 * @return string comma separated list of db fields + the meta value one
	 */
	public static function original_src_field( $fields = '' ) {
		global $wpdb;

		$qf   = explode( ',', $fields );
		$qf   = array_map( 'trim', $qf );
		$qf[] = $wpdb->postmeta . '.meta_value';

		return join( ', ', $qf );
	}

	/**
	 * Get all existing attachments having an '_ideastream_original_src' meta key
	 *
	 * @param int $idea_id the ID of idea
	 */
	public static function get_idea_attachments( $idea_id = 0 ) {
		global $wpdb;

		$idea_id = (int) $idea_id;

		if ( empty( $idea_id ) ) {
			return array();
		}

		add_filter( 'posts_fields', array( __CLASS__, 'original_src_field' ), 10, 1 );

		$attachment_ids = new WP_Query( array(
			'post_parent' => $idea_id,
			'post_type'   => 'attachment',
			'post_status' => 'inherit',
			'meta_key'    => '_ideastream_original_src',
			'fields'      => 'id=>parent',
		) );

		remove_filter( 'posts_fields', array( __CLASS__, 'original_src_field' ), 10, 1 );

		return wp_list_pluck( $attachment_ids->posts, 'ID', 'meta_value' );
	}

	/**
	 * Starting point.
	 *
	 * @param string $src the link to the image to side upload
	 * @param int    $post_id the ID of the post set the featured image for
	 */
	public static function start( $src = '', $post_id = 0 ) {
		if ( empty( $src ) || empty( $post_id ) ) {
			return new WP_Error( 'missing_argument' );
		}

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self( $src, $post_id );
		}

		return self::$instance;
	}
}