<?php
/**
 * @group users
 */
class WP_Idea_Stream_User_Functions_Tests extends WP_Idea_Stream_TestCase {
	public function setUp() {
		parent::setUp();

		unset( $GLOBALS['phpmailer']->mock_sent );

		$this->reset_post    = $_POST;
		$this->reset_request = $_REQUEST;
		$this->reset_server  = $_SERVER;
		$this->reset_cookie  = $_COOKIE;

		add_filter( 'wp_redirect', '__return_false' );
	}

	public function tearDown() {
		parent::tearDown();

		$_POST    = $this->reset_post;
		$_REQUEST = $this->reset_request;
		$_SERVER  = $this->reset_server;
		$_COOKIE  = $this->reset_cookie;
		wp_idea_stream()->feedback = array();

		remove_filter( 'wp_redirect', '__return_false' );
	}

	public function post_signup_form( $args = array() ) {
		$r = wp_parse_args( $args, array(
			'user_login' => '',
			'user_email' => '',
		) );

		$_SERVER['REQUEST_METHOD'] = 'POST';
		$_REQUEST['_wpnonce']      = wp_create_nonce( 'wp_idea_stream_signup' );
		$_POST    = array( 'wp_idea_stream_signup' => $r );
	}

	/**
	 * @group signup
	 */
	public function test_wp_idea_stream_users_signup_user_bad_user_login() {
		if ( function_exists( 'buddypress' ) ) {
			$this->markTestSkipped( 'wp_idea_stream_users_signup_user() is not used when BuddyPress is activated.' );
		}

		$this->post_signup_form( array( 'user_login' => 'foo', 'user_email' => 'foo@mail.com' ) );

		wp_idea_stream_users_signup_user( false );

		$this->assertContains( 'error', wp_idea_stream_get_idea_var( 'feedback' ) );
	}

	/**
	 * @group signup
	 */
	public function test_wp_idea_stream_users_signup_user_bad_user_email() {
		if ( function_exists( 'buddypress' ) ) {
			$this->markTestSkipped( 'wp_idea_stream_users_signup_user() is not used when BuddyPress is activated.' );
		}

		$this->post_signup_form( array( 'user_login' => 'foobar', 'user_email' => 'foo.com' ) );

		wp_idea_stream_users_signup_user( false );

		$this->assertContains( 'error', wp_idea_stream_get_idea_var( 'feedback' ) );
	}

	/**
	 * @group signup
	 */
	public function test_wp_idea_stream_users_signup_user_success() {
		if ( function_exists( 'buddypress' ) ) {
			$this->markTestSkipped( 'wp_idea_stream_users_signup_user() is not used when BuddyPress is activated.' );
		}

		$this->post_signup_form( array( 'user_login' => 'foobar', 'user_email' => 'foobar@mail.com' ) );

		wp_idea_stream_users_signup_user( false );

		$this->assertContains( 'success', wp_idea_stream_get_idea_var( 'feedback' ) );
	}

	/**
	 * @group signup
	 */
	public function test_wp_idea_stream_users_signup_child_blog() {
		if ( ! is_multisite() ) {
			$this->markTestSkipped( __METHOD__ . ' is a multisite-only test.' );
		}

		if ( function_exists( 'buddypress' ) ) {
			$this->markTestSkipped( 'wp_idea_stream_users_signup_user() is not used when BuddyPress is activated.' );
		}

		$registration = get_site_option( 'registration' );
		update_site_option( 'registration', 'user' );

		$b = $this->factory->blog->create();

		switch_to_blog( $b );

		add_filter( 'wp_idea_stream_allow_signups', '__return_true' );

		$this->post_signup_form( array( 'user_login' => 'barfoo', 'user_email' => 'barfoo@mail.com' ) );

		wp_idea_stream_users_signup_user( false );

		remove_filter( 'wp_idea_stream_allow_signups', '__return_true' );

		preg_match( '/<(.+?)>/', $GLOBALS['phpmailer']->mock_sent[0]['body'], $match );
		$activate_url  = explode( '?', $match[1] );

		$this->assertSame( wp_login_url(), $activate_url[0], 'The activate url must be the one of the child blog' );
		$user = get_user_by( 'email', 'barfoo@mail.com' );

		$this->assertTrue( ! empty( $user->ID ), 'The user must be created' );

		restore_current_blog();
		update_site_option( 'registration', $registration );

		global $wpdb;

		$signup_data = $wpdb->get_row( "SELECT activated, meta FROM {$wpdb->signups} WHERE user_email = 'barfoo@mail.com' AND active = 1" );

		$this->assertTrue( ! empty( $signup_data->activated ), 'The signup must be activated' );
		$this->assertEquals( array( 'add_to_blog' => $b, 'new_role' => 'subscriber' ), maybe_unserialize( $signup_data->meta ) );
	}
}
