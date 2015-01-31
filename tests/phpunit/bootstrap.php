<?php

require_once getenv( 'WP_DEVELOP_DIR' ) . '/tests/phpunit/includes/functions.php';

function _bootstrap_wp_idea_stream() {
	if ( defined( 'WP_TESTS_BUDDYPRESS' ) && 1 == WP_TESTS_BUDDYPRESS ) {

		if ( ! defined( 'WP_TESTS_MULTISITE' ) ) {
			echo "Testing with BuddyPress integration..." . PHP_EOL;
			echo "Running without BuddyPress multisite integration... To test BuddyPress multisite integration, use -c tests/phpunit/buddymulti.xml" . PHP_EOL;
		} else {
			echo "Testing with BuddyPress multisite integration..." . PHP_EOL;
		}

		if ( ! defined( 'BP_TESTS_DIR' ) ) {
			define( 'BP_TESTS_DIR', dirname( __FILE__ ) . '/../../../buddypress/tests/phpunit' );
		}

		if ( ! file_exists( BP_TESTS_DIR . '/bootstrap.php' ) )  {
			die( 'The BuddyPress Test suite could not be found' );
		}

		// Make sure BP is installed and loaded first
		require BP_TESTS_DIR . '/includes/loader.php';
	} else {
		echo "Running without BuddyPress integration... To test BuddyPress integration, use -c tests/phpunit/buddypress.xml" . PHP_EOL;
		echo "To test BuddyPress multisite integration, use -c tests/phpunit/buddymulti.xml" . PHP_EOL;
	}

	// load WP Idea Stream
	require dirname( __FILE__ ) . '/../../wp-idea-stream.php';
}
tests_add_filter( 'muplugins_loaded', '_bootstrap_wp_idea_stream' );

require getenv( 'WP_DEVELOP_DIR' ) . '/tests/phpunit/includes/bootstrap.php';

if ( defined( 'WP_TESTS_BUDDYPRESS' ) && 1 == WP_TESTS_BUDDYPRESS ) {
	// Load the BP-specific testing tools
	require BP_TESTS_DIR . '/includes/testcase.php';
}

// include our testcase
require( 'testcase.php' );
