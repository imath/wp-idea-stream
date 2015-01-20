<?php

class WP_Idea_Stream_TestCase extends WP_UnitTestCase {
	public $post_type;

	function setUp() {
		parent::setUp();

		$this->post_type = wp_idea_stream_get_post_type();
		$this->old_current_user = get_current_user_id();
	}

	public function tearDown() {
		parent::tearDown();

		$this->post_type = '';
		wp_set_current_user( $this->old_current_user );
	}
}
