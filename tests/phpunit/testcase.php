<?php
/**
 * Include WP Idea Stream Factory
 */
require_once dirname( __FILE__ ) . '/factory.php';

class WP_Idea_Stream_TestCase extends WP_UnitTestCase {

	function setUp() {
		parent::setUp();

		$this->factory = new WP_Idea_Stream_UnitTest_Factory;
	}
}
