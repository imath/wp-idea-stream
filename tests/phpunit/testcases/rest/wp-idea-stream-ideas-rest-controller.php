<?php
/**
 * Rest API tests.
 */

/**
 * @group rest
 */
class WP_Idea_Stream_Ideas_Rest_Tests extends WP_Test_REST_Controller_Testcase {
	protected $rb;
	protected $idea_factory;
	protected $admin_id;

	public function setUp() {
		parent::setUp();

		$this->rb           = wp_idea_stream_get_post_type();
		$this->idea_factory = new WP_Idea_Stream_UnitTest_Factory;
		$this->admin_id     = $this->factory->user->create( array(
			'role' => 'administrator',
		) );
		$this->user_id     = $this->factory->user->create( array(
			'role' => 'subscriber',
		) );
	}

	/**
	 * Routes
	 */
	public function test_register_routes() {
		$routes    = $this->server->get_routes();

		$this->assertArrayHasKey( '/wp/v2/' . $this->rb, $routes );
		$this->assertArrayHasKey( '/wp/v2/' . $this->rb . '/(?P<id>[\d]+)/rate', $routes );
	}

	protected function set_idea_data( $args = array() ) {
		$defaults = array(
			'title'   => 'Idea Title',
			'content' => 'Idea content',
			'status'  => 'publish',
			'author'  => get_current_user_id(),
			'type'    => $this->rb,
		);

		return wp_parse_args( $args, $defaults );
	}

	public function test_context_param() {}

	/**
	 * Get ideas
	 */
	public function test_get_items() {
		$p = $this->factory->post->create();
		$i = $this->idea_factory->idea->create();

		$p_request = new WP_REST_Request( 'GET', '/wp/v2/posts' );
		$p_response = $this->server->dispatch( $p_request );
		$p_response = rest_ensure_response( $p_response );

		$p_meta = wp_list_pluck( $p_response->get_data(), 'meta', 'id' );

		// Posts shouldn't have ratings.
		$this->assertEmpty( $p_meta[$p] );

		$i_request = new WP_REST_Request( 'GET', '/wp/v2/' . $this->rb );
		$i_response = $this->server->dispatch( $i_request );
		$i_response = rest_ensure_response( $i_response );

		$i_meta = wp_list_pluck( $i_response->get_data(), 'meta', 'id' );

		// Ideas have ratings.
		$this->assertArrayHasKey( 'idea_average_rate', $i_meta[$i] );
		$this->assertArrayHasKey( 'idea_rates', $i_meta[$i] );
	}

	/**
	 * Get idea
	 */
	public function test_get_item() {
		$i = $this->idea_factory->idea->create();

		$request = new WP_REST_Request( 'GET', '/wp/v2/' . $this->rb . '/' . $i );
		$response = $this->server->dispatch( $request );
		$response = rest_ensure_response( $response );

		$idea = $response->get_data();

		// Idea have ratings.
		$this->assertArrayHasKey( 'idea_average_rate', $idea['meta'] );
		$this->assertArrayHasKey( 'idea_rates', $idea['meta'] );
	}

	/**
	 * New idea - Not supported
	 */
	public function test_create_item() {
		$current_user_id = get_current_user_id();
		wp_set_current_user( $this->admin_id );

		$request = new WP_REST_Request( 'POST', '/wp/v2/' . $this->rb );
		$request->add_header( 'content-type', 'application/x-www-form-urlencoded' );
		$params = $this->set_idea_data();
		$request->set_body_params( $params );
		$response = $this->server->dispatch( $request );
		$response = rest_ensure_response( $response );

		$this->assertEquals( 400, $response->get_status() );

		wp_set_current_user( $current_user_id );
	}

	/**
	 * Edit idea - Not supported
	 */
	public function test_update_item() {
		$current_user_id = get_current_user_id();
		wp_set_current_user( $this->admin_id );
		$i = $this->idea_factory->idea->create();

		$request = new WP_REST_Request( 'PUT', sprintf( '/wp/v2/' . $this->rb . '/%d', $i ) );
		$request->add_header( 'content-type', 'application/x-www-form-urlencoded' );
		$params = $this->set_idea_data();
		$request->set_body_params( $params );
		$response = $this->server->dispatch( $request );
		$response = rest_ensure_response( $response );

		$this->assertEquals( 400, $response->get_status() );

		wp_set_current_user( $current_user_id );
	}

	/**
	 * Delete idea - Not supported
	 */
	public function test_delete_item() {
		$current_user_id = get_current_user_id();
		wp_set_current_user( $this->admin_id );
		$i = $this->idea_factory->idea->create();

		$request = new WP_REST_Request( 'DELETE', sprintf( '/wp/v2/' . $this->rb . '/%d', $i ) );
		$request->set_param( 'force', 'false' );
		$response = $this->server->dispatch( $request );
		$response = rest_ensure_response( $response );

		$this->assertEquals( 400, $response->get_status() );

		wp_set_current_user( $current_user_id );
	}

	/**
	 * @group rest_rate
	 */
	public function test_rate_item() {
		$current_user_id = get_current_user_id();
		wp_set_current_user( $this->user_id );
		$i = $this->idea_factory->idea->create();

		$request = new WP_REST_Request( 'POST', sprintf( '/wp/v2/' . $this->rb . '/%d/rate', $i ) );
		$request->add_header( 'content-type', 'application/x-www-form-urlencoded' );
		$request->set_body_params( array( 'rating'=> '5' ) );
		$response = $this->server->dispatch( $request );
		$response = rest_ensure_response( $response );

		$this->assertEquals( 200, $response->get_status() );
		$this->assertArrayHasKey( 'idea_average_rate', $response->get_data() );

		wp_set_current_user( $current_user_id );
	}

	/**
	 * @group rest_rate
	 */
	public function test_rate_item_not_logged_in() {
		$current_user_id = get_current_user_id();
		wp_set_current_user( 0 );
		$i = $this->idea_factory->idea->create();

		$request = new WP_REST_Request( 'POST', sprintf( '/wp/v2/' . $this->rb . '/%d/rate', $i ) );
		$request->add_header( 'content-type', 'application/x-www-form-urlencoded' );
		$request->set_body_params( array( 'rating'=> '1' ) );
		$response = $this->server->dispatch( $request );
		$response = rest_ensure_response( $response );

		$this->assertEquals( 403, $response->get_status() );

		wp_set_current_user( $current_user_id );
	}

	public function test_prepare_item() {}

	public function test_get_item_schema() {}
}
