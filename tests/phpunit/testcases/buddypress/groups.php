<?php
if ( defined( 'WP_TESTS_BUDDYPRESS' ) && 1 == WP_TESTS_BUDDYPRESS ) :
/**
 * @group groups
 */
class WP_Idea_Stream_Groups_Tests extends WP_Idea_Stream_TestCase {
	public $group_admin_id;
	public $group_id;
	public $current_group;
	public $current_component;
	public $current_user;

	public function setUp() {
		parent::setUp();

		$bp = buddypress();
		$this->current_group = $bp->groups->current_group;
		$this->current_component = $bp->current_component;

		$this->current_user = get_current_user_id();
		$this->group_admin_id = $this->factory->user->create();
		$this->set_current_user( $this->group_admin_id );

		$this->group_id = $this->factory->group->create( array( 'creator_id' => $this->group_admin_id ) );

		// Activate WP Idea Stream for the group
		groups_update_groupmeta( $this->group_id, '_group_ideastream_activate', 1 );

		// Set current component
		$bp->current_component = $bp->groups->id;
	}

	public function tearDown() {
		parent::tearDown();

		// Reset current user
		$this->set_current_user( $this->current_user );

		// Reset globals
		$bp = buddypress();
		$bp->groups->current_group = $this->current_group;
		$bp->current_component = $this->current_component;
	}


	/**
	 * @group group_status
	 */
	public function test_wp_idea_stream_groups_public_to_private() {
		$bp = buddypress();

		// Set current group
		$bp->groups->current_group = groups_get_group( array(
			'group_id'        => $this->group_id,
			'populate_extras' => true,
		) );

		$u = $this->factory->user->create();
		groups_join_group( $this->group_id, $u );

		$idea1 = $this->factory->idea->create( array(
			'author' => $u,
			'metas'  => array( 'group_id' => $this->group_id )
		) );

		$idea2 = $this->factory->idea->create( array(
			'author' => $u,
			'metas'  => array( 'group_id' => $this->group_id )
		) );

		$updated = groups_edit_group_settings( $this->group_id, false, 'private' );

		$ideas = wp_idea_stream_ideas_get_ideas( array( 'include' => array( $idea1, $idea2 ) ) );

		$private_ideas = wp_filter_object_list( $ideas['ideas'], array( 'post_status' => 'private' ), 'and', 'ID' );

		$this->assertEqualSets( array( $idea1, $idea2 ), $private_ideas, 'Switching from public to private should update the idea status to private' );
	}

	/**
	 * @group group_status
	 */
	public function test_wp_idea_stream_groups_hidden_to_public() {
		$bp = buddypress();

		$group = new BP_Groups_Group( $this->group_id );
		$group->status = 'hidden';
		$group->save();

		// Set current group
		$bp->groups->current_group = groups_get_group( array(
			'group_id'        => $this->group_id,
			'populate_extras' => true,
		) );

		$u = $this->factory->user->create();
		groups_join_group( $this->group_id, $u );

		$idea1 = $this->factory->idea->create( array(
			'author' => $u,
			'metas'  => array( 'group_id' => $this->group_id ),
			'status' => 'private',
		) );

		$idea2 = $this->factory->idea->create( array(
			'author' => $u,
			'metas'  => array( 'group_id' => $this->group_id ),
			'status' => 'private',
		) );

		$updated = groups_edit_group_settings( $this->group_id, false, 'public' );

		$ideas = wp_idea_stream_ideas_get_ideas( array( 'include' => array( $idea1, $idea2 ) ) );

		$public_ideas = wp_filter_object_list( $ideas['ideas'], array( 'post_status' => 'publish' ), 'and', 'ID' );

		$this->assertEqualSets( array( $idea1, $idea2 ), $public_ideas, 'Switching from hidden to public should update the idea status to publish' );
	}

	/**
	 * @group group_status
	 */
	public function test_wp_idea_stream_groups_hidden_to_private() {
		$bp = buddypress();

		$group = new BP_Groups_Group( $this->group_id );
		$group->status = 'hidden';
		$group->save();

		// Set current group
		$bp->groups->current_group = groups_get_group( array(
			'group_id'        => $this->group_id,
			'populate_extras' => true,
		) );

		$u = $this->factory->user->create();
		groups_join_group( $this->group_id, $u );

		$idea1 = $this->factory->idea->create( array(
			'author' => $u,
			'metas'  => array( 'group_id' => $this->group_id ),
			'status' => 'private',
		) );

		$idea2 = $this->factory->idea->create( array(
			'author' => $u,
			'metas'  => array( 'group_id' => $this->group_id ),
			'status' => 'private',
		) );

		$updated = groups_edit_group_settings( $this->group_id, false, 'private' );

		$ideas = wp_idea_stream_ideas_get_ideas( array( 'include' => array( $idea1, $idea2 ) ) );

		$private_ideas = wp_filter_object_list( $ideas['ideas'], array( 'post_status' => 'private' ), 'and', 'ID' );

		$this->assertEqualSets( array( $idea1, $idea2 ), $private_ideas, 'Switching from hidden to private should not update the idea status' );
	}

	/**
	 * @group remove_from_group
	 */
	public function test_wp_idea_stream_groups_delete_public_group() {
		$bp = buddypress();

		// Set current group
		$bp->groups->current_group = groups_get_group( array(
			'group_id'        => $this->group_id,
			'populate_extras' => true,
		) );

		$u = $this->factory->user->create();
		groups_join_group( $this->group_id, $u );

		$idea1 = $this->factory->idea->create( array(
			'author' => $u,
			'metas'  => array( 'group_id' => $this->group_id )
		) );

		$idea2 = $this->factory->idea->create( array(
			'author' => $u,
			'metas'  => array( 'group_id' => $this->group_id )
		) );

		$deleted = groups_delete_group( $this->group_id );

		// Check metas
		$this->assertEmpty( wp_idea_stream_ideas_get_meta( $idea1, 'group_id' ) );
		$this->assertEmpty( wp_idea_stream_ideas_get_meta( $idea2, 'group_id' ) );

		$ideas = wp_idea_stream_ideas_get_ideas( array( 'include' => array( $idea1, $idea2 ) ) );

		$public_ideas = wp_filter_object_list( $ideas['ideas'], array( 'post_status' => 'publish' ), 'and', 'ID' );

		$this->assertEqualSets( array( $idea1, $idea2 ), $public_ideas, 'Ideas removed from a group should always be public' );
	}

	/**
	 * @group remove_from_group
	 */
	public function test_wp_idea_stream_groups_delete_private_group() {
		$bp = buddypress();

		$group = new BP_Groups_Group( $this->group_id );
		$group->status = 'private';
		$group->save();

		// Set current group
		$bp->groups->current_group = groups_get_group( array(
			'group_id'        => $this->group_id,
			'populate_extras' => true,
		) );

		$u = $this->factory->user->create();
		groups_join_group( $this->group_id, $u );

		$idea1 = $this->factory->idea->create( array(
			'author' => $u,
			'metas'  => array( 'group_id' => $this->group_id ),
			'status' => 'private',
		) );

		$idea2 = $this->factory->idea->create( array(
			'author' => $u,
			'metas'  => array( 'group_id' => $this->group_id ),
			'status' => 'private',
		) );

		$deleted = groups_delete_group( $this->group_id );

		// Check metas
		$this->assertEmpty( wp_idea_stream_ideas_get_meta( $idea1, 'group_id' ) );
		$this->assertEmpty( wp_idea_stream_ideas_get_meta( $idea2, 'group_id' ) );

		$ideas = wp_idea_stream_ideas_get_ideas( array( 'include' => array( $idea1, $idea2 ) ) );

		$public_ideas = wp_filter_object_list( $ideas['ideas'], array( 'post_status' => 'publish' ), 'and', 'ID' );

		$this->assertEqualSets( array( $idea1, $idea2 ), $public_ideas, 'Ideas removed from a group should always be public' );
	}

	/**
	 * @group remove_from_group
	 */
	public function test_wp_idea_stream_groups_member_leave_private_group() {
		$bp = buddypress();

		$group = new BP_Groups_Group( $this->group_id );
		$group->status = 'private';
		$group->save();

		// Set current group
		$bp->groups->current_group = groups_get_group( array(
			'group_id'        => $this->group_id,
			'populate_extras' => true,
		) );

		$u = $this->factory->user->create();
		groups_join_group( $this->group_id, $u );

		$idea1 = $this->factory->idea->create( array(
			'author' => $u,
			'metas'  => array( 'group_id' => $this->group_id ),
			'status' => 'private',
		) );

		$idea2 = $this->factory->idea->create( array(
			'author' => $u,
			'metas'  => array( 'group_id' => $this->group_id ),
			'status' => 'private',
		) );

		$this->set_current_user( $u );
		groups_leave_group( $this->group_id, $u );

		// Check metas
		$this->assertEmpty( wp_idea_stream_ideas_get_meta( $idea1, 'group_id' ) );
		$this->assertEmpty( wp_idea_stream_ideas_get_meta( $idea2, 'group_id' ) );

		$ideas = wp_idea_stream_ideas_get_ideas( array( 'include' => array( $idea1, $idea2 ) ) );

		$public_ideas = wp_filter_object_list( $ideas['ideas'], array( 'post_status' => 'publish' ), 'and', 'ID' );

		$this->assertEqualSets( array( $idea1, $idea2 ), $public_ideas, 'When a member leaves the group, ideas should always be public' );
	}

	/**
	 * @group remove_from_group
	 */
	public function test_wp_idea_stream_groups_member_leave_public_group() {
		$bp = buddypress();

		// Set current group
		$bp->groups->current_group = groups_get_group( array(
			'group_id'        => $this->group_id,
			'populate_extras' => true,
		) );

		$u = $this->factory->user->create();
		groups_join_group( $this->group_id, $u );

		$idea1 = $this->factory->idea->create( array(
			'author' => $u,
			'metas'  => array( 'group_id' => $this->group_id ),
		) );

		$idea2 = $this->factory->idea->create( array(
			'author' => $u,
			'metas'  => array( 'group_id' => $this->group_id ),
		) );

		$this->set_current_user( $u );
		groups_leave_group( $this->group_id, $u );

		// Check metas
		$this->assertEmpty( wp_idea_stream_ideas_get_meta( $idea1, 'group_id' ) );
		$this->assertEmpty( wp_idea_stream_ideas_get_meta( $idea2, 'group_id' ) );

		$ideas = wp_idea_stream_ideas_get_ideas( array( 'include' => array( $idea1, $idea2 ) ) );

		$public_ideas = wp_filter_object_list( $ideas['ideas'], array( 'post_status' => 'publish' ), 'and', 'ID' );

		$this->assertEqualSets( array( $idea1, $idea2 ), $public_ideas, 'When a member leaves the group, ideas should always be public' );
	}

	/**
	 * @group remove_from_group
	 */
	public function test_wp_idea_stream_groups_member_banned_hidden_group() {
		$bp = buddypress();

		$group = new BP_Groups_Group( $this->group_id );
		$group->status = 'hidden';
		$group->save();

		// Set current group
		$bp->groups->current_group = groups_get_group( array(
			'group_id'        => $this->group_id,
			'populate_extras' => true,
		) );

		$u = $this->factory->user->create();
		groups_join_group( $this->group_id, $u );

		$idea1 = $this->factory->idea->create( array(
			'author' => $u,
			'metas'  => array( 'group_id' => $this->group_id ),
			'status' => 'private',
		) );

		$idea2 = $this->factory->idea->create( array(
			'author' => $u,
			'metas'  => array( 'group_id' => $this->group_id ),
			'status' => 'private',
		) );

		$bp->is_item_admin = true;

		groups_ban_member( $u, $this->group_id );

		// Check metas
		$this->assertEmpty( wp_idea_stream_ideas_get_meta( $idea1, 'group_id' ) );
		$this->assertEmpty( wp_idea_stream_ideas_get_meta( $idea2, 'group_id' ) );

		$ideas = wp_idea_stream_ideas_get_ideas( array( 'include' => array( $idea1, $idea2 ) ) );

		$public_ideas = wp_filter_object_list( $ideas['ideas'], array( 'post_status' => 'publish' ), 'and', 'ID' );

		$this->assertEqualSets( array( $idea1, $idea2 ), $public_ideas, 'When a member is banned from the group, ideas should always be public' );

		// Reset item admin
		$bp->is_item_admin = false;
	}

	/**
	 * @group remove_from_group
	 */
	public function test_wp_idea_stream_groups_member_remove_public_group() {
		$bp = buddypress();

		// Set current group
		$bp->groups->current_group = groups_get_group( array(
			'group_id'        => $this->group_id,
			'populate_extras' => true,
		) );

		$u = $this->factory->user->create();
		groups_join_group( $this->group_id, $u );

		$idea1 = $this->factory->idea->create( array(
			'author' => $u,
			'metas'  => array( 'group_id' => $this->group_id ),
		) );

		$idea2 = $this->factory->idea->create( array(
			'author' => $u,
			'metas'  => array( 'group_id' => $this->group_id ),
		) );

		$bp->is_item_admin = true;

		groups_remove_member( $u, $this->group_id );

		// Check metas
		$this->assertEmpty( wp_idea_stream_ideas_get_meta( $idea1, 'group_id' ) );
		$this->assertEmpty( wp_idea_stream_ideas_get_meta( $idea2, 'group_id' ) );

		$ideas = wp_idea_stream_ideas_get_ideas( array( 'include' => array( $idea1, $idea2 ) ) );

		$public_ideas = wp_filter_object_list( $ideas['ideas'], array( 'post_status' => 'publish' ), 'and', 'ID' );

		$this->assertEqualSets( array( $idea1, $idea2 ), $public_ideas, 'When a member is banned from the group, ideas should always be public' );

		// Reset item admin
		$bp->is_item_admin = false;
	}
}

endif;
