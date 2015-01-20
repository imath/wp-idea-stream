<?php
/**
 * @group comments
 */
class WP_Idea_Stream_Comment_Functions_Tests extends WP_Idea_Stream_TestCase {

	function setUp() {
		parent::setUp();

		$this->post_id = $this->factory->post->create( array( 'post_type' => $this->post_type ) );
	}

	public function tearDown() {
		parent::tearDown();
	}

	/**
	 * @group wp_idea_stream_comments_get_comments
	 */
	function test_wp_idea_stream_comments_get_comments() {
		$comment_id_approved = $this->factory->comment->create( array( 'comment_post_ID' => $this->post_id ) );
		$comment_id_not_approved = $this->factory->comment->create( array( 'comment_post_ID' => $this->post_id, 'comment_approved' => 0 ) );

		$p = $this->factory->post->create();
		$comment_id_post = $this->factory->comment->create( array( 'comment_post_ID' => $p ) );

		$a_comments = wp_idea_stream_comments_get_comments();

		$this->assertTrue( 1 == count( $a_comments ) );
		$this->assertEquals( array( $comment_id_approved ), wp_list_pluck( $a_comments, 'comment_ID' ) );

		$h_comments = wp_idea_stream_comments_get_comments( array( 'status' => 'hold' ) );
		$this->assertEquals( array( $comment_id_not_approved ), wp_list_pluck( $h_comments, 'comment_ID' ) );
	}
}
