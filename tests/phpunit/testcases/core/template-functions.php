<?php
/**
 * @group core
 */
class WP_Idea_Stream_Core_Template_Functions_Tests extends WP_Idea_Stream_TestCase {

	public function test_wp_idea_stream_parse_query() {
		// Generate a publish idea
		$idea_id = $this->factory->idea->create( array( 'author' => 1 ) );

		// Create a user
		$u1 = $this->factory->user->create();
		$u2 = $this->factory->user->create();

		wp_idea_stream_add_rate( $idea_id, $u1, 1 );
		wp_idea_stream_add_rate( $idea_id, $u2, 1 );

		$rates = get_post_meta( $idea_id, '_ideastream_rates', true );

		// Get user 1 rates
		$u1_rated_ideas = wp_idea_stream_ideas_get_ideas( array(
			'per_page' => -1,
			'meta_query' => array( array(
				'key'     => '_ideastream_rates',
				'value'   => ';i:' . $u1 . ';',
				'compare' => 'LIKE'
			) ),
		) );

		$u1_rated_id = wp_list_pluck( $u1_rated_ideas['ideas'], 'ID' );
		$this->assertTrue( $idea_id === (int) reset( $u1_rated_id ) );

		// Get user 2 rates
		$u2_rated_ideas = wp_idea_stream_ideas_get_ideas( array(
			'per_page' => -1,
			'meta_query' => array( array(
				'key'     => '_ideastream_rates',
				'value'   => ';i:' . $u2 . ';',
				'compare' => 'LIKE'
			) ),
		) );

		$u2_rated_id = wp_list_pluck( $u2_rated_ideas['ideas'], 'ID' );
		$this->assertTrue( $idea_id === (int) reset( $u2_rated_id ) );

		// Get user 2 rates
		$author_rated_ideas = wp_idea_stream_ideas_get_ideas( array(
			'per_page' => -1,
			'meta_query' => array( array(
				'key'     => '_ideastream_rates',
				'value'   => ';i:1;',
				'compare' => 'LIKE'
			) ),
		) );

		// Author did not vote on his idea
		$author_rated_id = wp_list_pluck( $author_rated_ideas['ideas'], 'ID' );
		$this->assertFalse( $idea_id === (int) reset( $author_rated_id ), 'Only ideas the user rated should be in the query' );
	}
}
