<?php

/**
 * Include WP Idea Stream Factory
 */
require_once dirname( __FILE__ ) . '/factory.php';

/**
 * Use BuddyPress unit testcase if running BuddyPress tests
 */
if ( class_exists( 'BP_UnitTestCase' ) && defined( 'WP_TESTS_BUDDYPRESS' ) && 1 == WP_TESTS_BUDDYPRESS ) :
class WP_Idea_Stream_TestCase extends BP_UnitTestCase {

	function setUp() {
		parent::setUp();

		$this->factory = new WP_Idea_Stream_UnitTest_Factory;
	}
}
else :
class WP_Idea_Stream_TestCase extends WP_UnitTestCase {

	function setUp() {
		parent::setUp();

		$this->factory = new WP_Idea_Stream_UnitTest_Factory;
	}
}
endif;
