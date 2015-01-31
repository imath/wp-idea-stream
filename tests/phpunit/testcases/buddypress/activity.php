<?php
if ( defined( 'WP_TESTS_BUDDYPRESS' ) && 1 == WP_TESTS_BUDDYPRESS ) :
/**
 * @group activity
 */
class WP_Idea_Stream_Activity_Tests extends WP_Idea_Stream_TestCase {

	function setUp() {
		parent::setUp();
	}

	public function tearDown() {
		parent::tearDown();
	}

	/**
	 * @group public_activity
	 */
	function test_wp_idea_stream_activity_publish() {
		// Generate a publish idea
		$idea_id = $this->factory->idea->create();

		$component = buddypress()->blogs->id;

		$a = bp_activity_get( array( 'filter' => array(
			'action'       => 'new_ideas',
			'object'       => $component,
			'primary_id'   => get_current_blog_id(),
			'secondary_id' => $idea_id,
		) ) );

		$this->assertTrue( $idea_id   == $a['activities'][0]->secondary_item_id, 'An activity should be created when an idea is published' );
		$this->assertTrue( $component == $a['activities'][0]->component, 'The component should be set to blogs' );
	}

	/**
	 * @group comment_activity
	 */
	function test_wp_idea_stream_new_comment_activity_publish() {
		// Generate a publish idea
		$idea_id = $this->factory->idea->create();
		// Create a user
		$u = $this->factory->user->create();
		$user = $this->factory->user->get_object_by_id( $u );

		add_filter( 'comment_flood_filter', '__return_false' );

		// Create a comment no user
		$c1 = $this->factory->idea_comment->create( array(
			'comment_post_ID' => $idea_id
		) );

		// Create a comment for $u
		$c2 = $this->factory->idea_comment->create( array(
			'user_id'              => $u,
			'comment_author_email' => $user->user_email,
			'comment_post_ID'      => $idea_id
		) );

		remove_filter( 'comment_flood_filter', '__return_false' );

		$component = buddypress()->blogs->id;

		$a = bp_activity_get( array( 'filter' => array(
			'action'       => 'new_ideas_comment',
			'object'       => $component,
			'primary_id'   => get_current_blog_id(),
		) ) );

		$this->assertTrue( empty( $a['activities'] ), 'No activity should be generated for comments not approved' );

		// Approve comments
		$this->factory->idea_comment->update_object( $c1, array( 'comment_approved' => 1 ) );
		$this->factory->idea_comment->update_object( $c2, array( 'comment_approved' => 1 ) );

		$a = bp_activity_get( array( 'filter' => array(
			'action'       => 'new_ideas_comment',
			'object'       => $component,
			'primary_id'   => get_current_blog_id(),
		) ) );

		$this->assertTrue( count( $a['activities'] ) == 1, 'Only comments made by registered user should generate an activity' );
		$this->assertTrue( $c2 == $a['activities'][0]->secondary_item_id, 'An activity should be created when a comment is made by a registered user on a published idea' );
		$this->assertTrue( $component == $a['activities'][0]->component, 'The component should be set to blogs' );
	}

	/**
	 * @group admin_created
	 */
	function test_wp_idea_stream_activity_private() {
		// Generate a private idea
		$idea_id = $this->factory->post->create( array(
			'post_type'   => wp_idea_stream_get_post_type(),
			'post_status' => 'private',
			'post_author' => 1,
		) );

		$component = buddypress()->blogs->id;

		$a = bp_activity_get( array(
			'filter' => array(
				'action'       => 'new_ideas',
				'object'       => $component,
				'primary_id'   => get_current_blog_id(),
				'secondary_id' => $idea_id,
			),
			'show_hidden' => true,
		) );

		$this->assertTrue( $idea_id   == $a['activities'][0]->secondary_item_id, 'An activity should be created when an idea is privately published' );
		$this->assertTrue( $component == $a['activities'][0]->component, 'The component should be set to blogs' );
		$this->assertTrue( ! empty( $a['activities'][0]->hide_sitewide ), 'The visibility should be hidden' );
	}

	/**
	 * @group comment_activity
	 */
	function test_wp_idea_stream_new_comment_activity_private() {
		// Generate a private idea
		$idea_id = $this->factory->post->create( array(
			'post_type'   => wp_idea_stream_get_post_type(),
			'post_status' => 'private',
			'post_author' => 1,
		) );

		// Create an administrator
		$u = $this->factory->user->create( array( 'role' => 'administrator' ) );
		$user = $this->factory->user->get_object_by_id( $u );

		// Create a comment for $u
		$c = $this->factory->idea_comment->create( array(
			'user_id'              => $u,
			'comment_author_email' => $user->user_email,
			'comment_post_ID'      => $idea_id,
			'comment_approved'     => 1,
		) );

		$component = buddypress()->blogs->id;

		$a = bp_activity_get( array(
			'filter' => array(
				'action'       => 'new_ideas_comment',
				'object'       => $component,
				'primary_id'   => get_current_blog_id(),
			),
			'show_hidden'  => true
		) );

		$this->assertTrue( $c == $a['activities'][0]->secondary_item_id, 'An activity should be created when a comment is made by a registered user on a private idea' );
		$this->assertTrue( $component == $a['activities'][0]->component, 'The component should be set to blogs' );
		$this->assertTrue( ! empty( $a['activities'][0]->hide_sitewide ), 'The visibility should be hidden' );
	}

	/**
	 * @group admin_created
	 */
	function test_wp_idea_stream_activity_password() {
		// Generate a password protected idea
		$idea_id = $this->factory->post->create( array(
			'post_type'     => wp_idea_stream_get_post_type(),
			'post_password' => 'password',
			'post_author'   => 1,
		) );

		$component = buddypress()->blogs->id;

		$a = bp_activity_get( array(
			'filter' => array(
				'action'       => 'new_ideas',
				'object'       => $component,
				'primary_id'   => get_current_blog_id(),
				'secondary_id' => $idea_id,
			),
			'show_hidden' => true,
		) );

		$this->assertTrue( empty( $a['activities'] ), 'No activity should be generated for a password protected idea' );
	}
}

endif;
