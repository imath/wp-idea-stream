<?php

require_once getenv( 'WP_DEVELOP_DIR' ) . '/tests/phpunit/includes/functions.php';

function _bootstrap_wp_idea_stream() {
	if ( defined( 'WP_TESTS_BUDDYPRESS' ) && 1 == WP_TESTS_BUDDYPRESS ) {
		echo "Testing with BuddyPress integration..." . PHP_EOL;

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
	}

	// load WP Idea Stream
	require dirname( __FILE__ ) . '/../../wp-idea-stream.php';
}
tests_add_filter( 'muplugins_loaded', '_bootstrap_wp_idea_stream' );

require getenv( 'WP_DEVELOP_DIR' ) . '/tests/phpunit/includes/bootstrap.php';

if ( defined( 'WP_TESTS_BUDDYPRESS' ) && true === WP_TESTS_BUDDYPRESS ) {
	// Load the BP test files
	require BP_TESTS_DIR . '/includes/testcase.php';
}

// include our testcase
require( 'testcase.php' );