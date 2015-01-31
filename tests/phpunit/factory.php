<?php

/**
 * Use BuddyPress factory if running BuddyPress tests
 */
if ( class_exists( 'BP_UnitTest_Factory' ) && defined( 'WP_TESTS_BUDDYPRESS' ) && 1 == WP_TESTS_BUDDYPRESS ) :
class WP_Idea_Stream_UnitTest_Factory extends BP_UnitTest_Factory {

	function __construct() {
		parent::__construct();

		$this->idea = new WP_Idea_Stream_UnitTest_Factory_For_Idea( $this );
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
			if ( isset( $idea->field_key ) ) {
				$idea->field_key = $field_value;
			}
		}

		return $idea->save();
	}

	function get_object_by_id( $idea_id ) {
		return new WP_Idea_Stream_Idea( $idea_id );
	}
}
