<?php

/**
 * Use BuddyPress factory if running BuddyPress tests
 */
if ( class_exists( 'BP_UnitTest_Factory' ) && defined( 'WP_TESTS_BUDDYPRESS' ) && 1 == WP_TESTS_BUDDYPRESS ) :
class WP_Idea_Stream_UnitTest_Factory extends BP_UnitTest_Factory {

	function __construct() {
		parent::__construct();

		$this->idea = new WP_Idea_Stream_UnitTest_Factory_For_Idea( $this );
		$this->idea_comment = new WP_Idea_Stream_UnitTest_Factory_For_Idea_Comment( $this );
	}
}
else :
class WP_Idea_Stream_UnitTest_Factory extends WP_UnitTest_Factory {

	function __construct() {
		parent::__construct();

		$this->idea = new WP_Idea_Stream_UnitTest_Factory_For_Idea( $this );
	}
}
endif;

class WP_Idea_Stream_UnitTest_Factory_For_Idea extends WP_UnitTest_Factory_For_Thing {

	function __construct( $factory = null ) {
		parent::__construct( $factory );

		$this->default_generation_definitions = array(
			'title'        => new WP_UnitTest_Generator_Sequence( 'Idea %s' ),
			'description'  => new WP_UnitTest_Generator_Sequence( 'Idea description %s' ),
			'status'       => 'publish',
		);
	}

	function create_object( $args ) {
		if ( ! isset( $args['author'] ) ) {
			if ( is_user_logged_in() ) {
				$args['author'] = get_current_user_id();

			// Create a user
			} else {
				$args['author'] = $this->factory->user->create( array( 'role' => 'subscriber' ) );
			}
		}

		$idea = new WP_Idea_Stream_Idea();

		foreach ( $args as $key_arg => $value_arg ) {
			$idea->{$key_arg} = $value_arg;
		}

		return $idea->save();
	}

	function update_object( $idea_id, $fields ) {
		$idea = new WP_Idea_Stream_Idea( $idea_id );

		foreach ( $fields as $field_key => $field_value ) {
			if ( isset( $idea->{$field_key} ) ) {
				$idea->{$field_key} = $field_value;
			}
		}

		return $idea->save();
	}

	function get_object_by_id( $idea_id ) {
		return new WP_Idea_Stream_Idea( $idea_id );
	}
}

class WP_Idea_Stream_UnitTest_Factory_For_Idea_Comment extends WP_UnitTest_Factory_For_Thing {

	function __construct( $factory = null ) {
		parent::__construct( $factory );

		$this->default_generation_definitions = array(
			'comment_author'       => new WP_UnitTest_Generator_Sequence( 'Idea Commenter %s' ),
			'comment_author_url'   => new WP_UnitTest_Generator_Sequence( 'http://example.com/%s/' ),
			'comment_author_email' => new WP_UnitTest_Generator_Sequence( 'test%s@comment.url' ),
			'comment_type'         => '',
			'comment_content'      => new WP_UnitTest_Generator_Sequence( 'Idea comment %s' ),
		);
	}

	function create_object( $args ) {
		$reset_server = $_SERVER;
		$_SERVER['REMOTE_ADDR'] = '';

		$comment_id = wp_new_comment( $this->addslashes_deep( $args ) );

		// Reset $_SERVER
		$_SERVER = $reset_server;

		return $comment_id;
	}

	function update_object( $comment_id, $fields ) {
		$reset_server = $_SERVER;
		$_SERVER['REMOTE_ADDR'] = '';

		$fields['comment_ID'] = $comment_id;
		$comment_id = wp_update_comment( $this->addslashes_deep( $fields ) );

		// Reset $_SERVER
		$_SERVER = $reset_server;

		return $comment_id;
	}

	function get_object_by_id( $comment_id ) {
		return wp_idea_stream_comments_get_comment( $comment_id );
	}
}
