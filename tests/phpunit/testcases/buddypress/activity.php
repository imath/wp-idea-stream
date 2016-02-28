<?php
if ( defined( 'WP_TESTS_BUDDYPRESS' ) && 1 == WP_TESTS_BUDDYPRESS ) :
/**
 * @group activity
 */
class WP_Idea_Stream_Activity_Tests extends WP_Idea_Stream_TestCase {

	public function setUp() {
		parent::setUp();

		// As the BuddyPress Activity Actions are reset before each test
		// and we only run the following once at init, we need to make sure
		// the ideas tracking args are set.
		buddypress()->ideastream->activities->register_activity_actions();

		add_filter( 'comment_flood_filter', '__return_false' );
	}

	public function tearDown() {
		parent::tearDown();

		remove_filter( 'comment_flood_filter', '__return_false' );
	}

	/**
	 * @group public_activity
	 */
	public function test_wp_idea_stream_activity_publish() {
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

		/** Comments ****/

		// Create a user
		$u = $this->factory->user->create();
		$user = $this->factory->user->get_object_by_id( $u );

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
	public function test_wp_idea_stream_activity_private() {
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

		/** Comments ****/

		// Create an administrator
		$u = $this->factory->user->create( array( 'role' => 'administrator' ) );
		$user = $this->factory->user->get_object_by_id( $u );

		// Create a comment for $u
		$c = $this->factory->idea_comment->create( array(
			'user_id'              => $u,
			'comment_author_email' => $user->user_email,
			'comment_post_ID'      => $idea_id,
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
	public function test_wp_idea_stream_activity_password() {
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

	/**
	 * @group group_activity
	 */
	public function test_wp_idea_stream_activity_public_group() {
		$bp = buddypress();
		$reset_current_group = $bp->groups->current_group;
		$reset_current_component = $bp->current_component;
		$bp->current_component = $bp->groups->id;

		$ga = $this->factory->user->create();
		$gm1 = $this->factory->user->create();
		$gm2 = $this->factory->user->create();

		$g = $this->factory->group->create( array( 'creator_id' => $ga ) );

		// Allow IdeaStream
		groups_update_groupmeta( $g, '_group_ideastream_activate', 1 );
		groups_update_groupmeta( $g, '_group_ideastream_comments', 1 );

		groups_join_group( $g, $gm1 );
		groups_join_group( $g, $gm2 );

		$bp->groups->current_group = groups_get_group( array(
			'group_id'        => $g,
			'populate_extras' => true,
		) );

		$idea_id = $this->factory->idea->create( array(
			'author' => $gm1,
			'metas'  => array( 'group_id' => $g )
		) );

		$component = $bp->groups->id;

		$a = bp_activity_get( array(
			'filter' => array(
				'action'       => 'new_ideas',
				'object'       => $component,
				'primary_id'   => $g,
				'secondary_id' => $idea_id,
			)
		) );

		$this->assertTrue( $idea_id   == $a['activities'][0]->secondary_item_id, 'An activity should be created when an idea is published in a public group' );
		$this->assertTrue( $component == $a['activities'][0]->component, 'The component should be set to groups' );

		/** comments ****/
		$gmember1 = $this->factory->user->get_object_by_id( $gm1 );
		$gmember2 = $this->factory->user->get_object_by_id( $gm2 );

		// Create comments
		$c1 = $this->factory->idea_comment->create( array(
			'user_id'              => $gm1,
			'comment_author_email' => $gmember1->user_email,
			'comment_post_ID'      => $idea_id,
		) );

		$c2 = $this->factory->idea_comment->create( array(
			'user_id'              => $gm2,
			'comment_author_email' => $gmember2->user_email,
			'comment_post_ID'      => $idea_id,
		) );

		// Approve the comment made by the "not" author
		$this->factory->idea_comment->update_object( $c2, array( 'comment_approved' => 1 ) );

		$a = bp_activity_get( array( 'filter' => array(
			'action'       => 'new_ideas_comment',
			'object'       => $component,
			'primary_id'   => $g,
		) ) );

		$this->assertTrue( $component == $a['activities'][0]->component, 'The component should be set to groups' );
		$this->assertEqualSets( array( $c1, $c2 ), wp_list_pluck( $a['activities'], 'secondary_item_id' ), 'An activity should be created when a comment is made by a group member on a published idea' );

		// clean up!
		$bp->groups->current_group = $reset_current_group;
		$bp->current_component = $reset_current_component;
	}

	/**
	 * @group group_activity
	 */
	public function test_wp_idea_stream_activity_private_group() {
		$bp = buddypress();
		$reset_current_group = $bp->groups->current_group;
		$reset_current_component = $bp->current_component;
		$bp->current_component = $bp->groups->id;

		$ga = $this->factory->user->create();
		$gm1 = $this->factory->user->create();
		$gm2 = $this->factory->user->create();

		$g = $this->factory->group->create( array( 'creator_id' => $ga, 'status' => 'private' ) );

		// Allow IdeaStream
		groups_update_groupmeta( $g, '_group_ideastream_activate', 1 );
		groups_update_groupmeta( $g, '_group_ideastream_comments', 1 );

		groups_join_group( $g, $gm1 );
		groups_join_group( $g, $gm2 );

		$bp->groups->current_group = groups_get_group( array(
			'group_id'        => $g,
			'populate_extras' => true,
		) );

		$idea_id = $this->factory->idea->create( array(
			'author' => $gm1,
			'metas'  => array( 'group_id' => $g ),
			'status' => 'private',
		) );

		$component = $bp->groups->id;

		$a = bp_activity_get( array(
			'filter' => array(
				'action'       => 'new_ideas',
				'object'       => $component,
				'primary_id'   => $g,
				'secondary_id' => $idea_id,
			),
			'show_hidden' => true,
		) );

		$this->assertTrue( $idea_id   == $a['activities'][0]->secondary_item_id, 'An activity should be created when an idea is published in a private group' );
		$this->assertTrue( $component == $a['activities'][0]->component, 'The component should be set to groups' );
		$this->assertTrue( ! empty( $a['activities'][0]->hide_sitewide ), 'The visibility should be hidden' );

		/** comments ****/
		$gadmin = $this->factory->user->get_object_by_id( $ga );
		$gmember2 = $this->factory->user->get_object_by_id( $gm2 );

		// Create comments
		$c1 = $this->factory->idea_comment->create( array(
			'user_id'              => $ga,
			'comment_author_email' => $gadmin->user_email,
			'comment_post_ID'      => $idea_id,
		) );

		$c2 = $this->factory->idea_comment->create( array(
			'user_id'              => $gm2,
			'comment_author_email' => $gmember2->user_email,
			'comment_post_ID'      => $idea_id,
		) );

		// Approve the comment made by the "not" authors
		$this->factory->idea_comment->update_object( $c1, array( 'comment_approved' => 1 ) );
		$this->factory->idea_comment->update_object( $c2, array( 'comment_approved' => 1 ) );

		$a = bp_activity_get( array(
			'filter' => array(
				'action'       => 'new_ideas_comment',
				'object'       => $component,
				'primary_id'   => $g,
			),
			'show_hidden' => true,
		) );

		$this->assertTrue( $component == $a['activities'][0]->component, 'The component should be set to groups' );
		$this->assertTrue( ! empty( $a['activities'][0]->hide_sitewide ), 'The visibility should be hidden' );
		$this->assertEqualSets( array( $c1, $c2 ), wp_list_pluck( $a['activities'], 'secondary_item_id' ), 'An activity should be created when a comment is made by a group member on a private idea' );

		// clean up!
		$bp->groups->current_group = $reset_current_group;
		$bp->current_component = $reset_current_component;
	}

	/**
	 * @group activity_action
	 */
	public function test_wp_idea_stream_idea_activity_actions() {
		add_filter( 'bp_is_current_component', array( $this, 'is_activity_stream' ), 10, 2 );

		$u = $this->factory->user->create();
		$i = $this->factory->idea->create( array(
			'author' => $u,
		) );

		$user = $this->factory->user->get_object_by_id( $u );

		// Create comment
		$c = $this->factory->idea_comment->create( array(
			'user_id'              => $u,
			'comment_author_email' => $user->user_email,
			'comment_post_ID'      => $i,
		) );

		$user_link = bp_core_get_userlink( $u );
		$blog_url = get_home_url();
		$post_url = add_query_arg( 'p', $i, trailingslashit( $blog_url ) );
		$comment_link = wp_idea_stream_comments_get_comment_link( $c );

		$a = $this->factory->activity->create( array(
			'component' => buddypress()->blogs->id,
			'type' => 'new_ideas',
			'user_id' => $u,
			'item_id' => get_current_blog_id(),
			'secondary_item_id' => $i,
			'primary_link' => $post_url,
		) );

		$expected = sprintf(
			'%1$s wrote a new %2$s',
			$user_link,
			'<a href="' . esc_url( $post_url ) . '">idea</a>'
		);

		$a_obj = new BP_Activity_Activity( $a );

		$this->assertSame( $expected, $a_obj->action );

		// Comment

		$a = $this->factory->activity->create( array(
			'component' => buddypress()->blogs->id,
			'type' => 'new_ideas_comment',
			'user_id' => $u,
			'item_id' => get_current_blog_id(),
			'secondary_item_id' => $c,
		) );

		$expected = sprintf(
			'%1$s replied to this %2$s',
			$user_link,
			'<a href="' . esc_url( $comment_link ) . '">idea</a>'
		);

		$a_obj = new BP_Activity_Activity( $a );

		$this->assertSame( $expected, $a_obj->action );

		/** Group */

		$g = $this->factory->group->create();
		$b = $this->factory->activity->create( array(
			'component' => buddypress()->groups->id,
			'type' => 'new_ideas',
			'user_id' => $u,
			'item_id' => $g,
			'secondary_item_id' => $i,
			'primary_link' => $post_url,
		) );

		$group = $this->factory->group->get_object_by_id( $g );
		$group_link = '<a href="' . bp_get_group_permalink( $group ) . '">' . $group->name . '</a>';

		$expected = sprintf(
			'%1$s wrote a new %2$s in the group %3$s',
			$user_link,
			'<a href="' . esc_url( $post_url ) . '">idea</a>',
			$group_link
		);

		$b_obj = new BP_Activity_Activity( $b );

		$this->assertSame( $expected, $b_obj->action );

		// Comment

		$cb = $this->factory->activity->create( array(
			'component' => buddypress()->groups->id,
			'type' => 'new_ideas_comment',
			'user_id' => $u,
			'item_id' => $g,
			'secondary_item_id' => $c,
		) );

		$expected = sprintf(
			'%1$s replied to this %2$s posted in the group %3$s',
			$user_link,
			'<a href="' . esc_url( $comment_link ) . '">idea</a>',
			$group_link
		);

		$cb_obj = new BP_Activity_Activity( $cb );

		$this->assertSame( $expected, $cb_obj->action );

		remove_filter( 'bp_is_current_component', array( $this, 'is_activity_stream' ), 10, 2 );
	}

	/**
	 * @group show_hidden
	 */
	public function test_wp_idea_stream_idea_switch_status_activity_visibility() {
		$u = $this->factory->user->create();

		$idea_id = $this->factory->idea->create( array(
			'author' => $u,
			'status' => 'publish',
		) );

		$idea_object = $this->factory->idea->get_object_by_id( $idea_id );
		$user = $this->factory->user->get_object_by_id( $u );

		// Create a comment for $u
		$c = $this->factory->idea_comment->create( array(
			'user_id'              => $u,
			'comment_author_email' => $user->user_email,
			'comment_post_ID'      => $idea_id,
		) );

		$public_activities = bp_activity_get( array(
			'filter' => array(
				'user_id'      => $u,
			),
			'show_hidden' => true,
		) );

		$public_comment_id = wp_filter_object_list( $public_activities['activities'], array( 'type' => 'new_ideas_comment' ), 'and', 'id' );
		$public_idea_id = wp_filter_object_list( $public_activities['activities'], array( 'type' => 'new_ideas' ), 'and', 'id' );
		$this->assertEquals( array( '0', '0' ), wp_list_pluck( $public_activities['activities'], 'hide_sitewide' ), 'Pubished ideas generate public activities' );

		// publish -> private
		$idea = $idea_object->idea;
		$idea->post_status = 'private';

		wp_update_post( $idea );

		$private_activities = bp_activity_get( array(
			'filter' => array(
				'user_id'      => $u,
			),
			'show_hidden' => true,
		) );

		$private_comment_id = wp_filter_object_list( $private_activities['activities'], array( 'type' => 'new_ideas_comment' ), 'and', 'id' );
		$private_idea_id = wp_filter_object_list( $private_activities['activities'], array( 'type' => 'new_ideas' ), 'and', 'id' );

		$this->assertEquals( array_values( $private_comment_id ), array_values( $public_comment_id ), 'activity about comments are not deleted' );
		$this->assertNotEquals( array_values( $private_idea_id ), array_values( $public_idea_id ), 'Private idea is first deleted, and then recreated with a new id' );
		$this->assertEquals( array( '1', '1' ), wp_list_pluck( $private_activities['activities'], 'hide_sitewide' ), 'Private ideas generate hidden activities' );

		// private -> password protected
		$idea->post_status = 'publish';
		$idea->post_password  = 'foo';

		wp_update_post( $idea );

		$protected_activities = bp_activity_get( array(
			'filter' => array(
				'user_id'      => $u,
			),
			'show_hidden' => true,
		) );

		$this->assertEmpty( $protected_activities['activities'], 'Password protected ideas have no activities' );

		// password protected -> publish
		$idea->post_password = '';

		wp_update_post( $idea );
		$this->factory->idea_comment->update_object( $c, array( 'comment_content' => 'foo bar' ) );

		$public_activities = bp_activity_get( array(
			'filter' => array(
				'user_id' => $u,
			),
			'show_hidden' => true,
		) );

		$this->assertEquals( array( '0', '0' ), wp_list_pluck( $public_activities['activities'], 'hide_sitewide' ), 'Pubished ideas generate public activities' );

		// publish -> pending
		$idea->post_status = 'pending';

		wp_update_post( $idea );

		$pending_activities = bp_activity_get( array(
			'filter' => array(
				'user_id'      => $u,
			),
			'show_hidden' => true,
		) );

		$this->assertEmpty( $pending_activities['activities'], 'Pending ideas have no activities' );

		// pending -> private
		$idea->post_status = 'private';

		wp_update_post( $idea );
		$this->factory->idea_comment->update_object( $c, array( 'comment_content' => 'bar foo' ) );

		$private_activities = bp_activity_get( array(
			'filter' => array(
				'user_id' => $u,
			),
			'show_hidden' => true,
		) );

		$this->assertEquals( array( '1', '1' ), wp_list_pluck( $private_activities['activities'], 'hide_sitewide' ), 'Private ideas generate hidden activities' );

		// private -> publish
		$idea->post_status = 'publish';

		wp_update_post( $idea );

		$public_activities = bp_activity_get( array(
			'filter' => array(
				'user_id' => $u,
			),
			'show_hidden' => true,
		) );

		$this->assertEquals( array( '0', '0' ), wp_list_pluck( $public_activities['activities'], 'hide_sitewide' ), 'Pubished ideas generate public activities' );

		// Publish -> password protected
		$idea->post_password  = 'foo';

		wp_update_post( $idea );

		$protected_activities = bp_activity_get( array(
			'filter' => array(
				'user_id'      => $u,
			),
			'show_hidden' => true,
		) );

		$this->assertEmpty( $protected_activities['activities'], 'Password protected ideas have no activities' );

		// password protected -> private
		$idea->post_status = 'private';
		$idea->post_password = '';

		wp_update_post( $idea );
		$this->factory->idea_comment->update_object( $c, array( 'comment_content' => 'foo bar' ) );

		$private_activities = bp_activity_get( array(
			'filter' => array(
				'user_id' => $u,
			),
			'show_hidden' => true,
		) );

		$this->assertEquals( array( '1', '1' ), wp_list_pluck( $private_activities['activities'], 'hide_sitewide' ), 'Private ideas generate hidden activities' );

		// Trash idea
		wp_trash_post( $idea_id );

		$trashed_activities = bp_activity_get( array(
			'filter' => array(
				'user_id'      => $u,
			),
			'show_hidden' => true,
		) );

		$this->assertEmpty( $trashed_activities['activities'], 'Trashed ideas have no activities' );
	}

	/**
	 * This is to make sure WP_Idea_Stream_Activity->dropdown_filters
	 * will do the job we expect it to do.
	 */
	public function is_activity_stream( $retval, $component ) {
		if ( 'activity' === $component ) {
			$retval = true;
		}

		return $retval;
	}
}

endif;
