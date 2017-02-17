<?php

require_once getenv( 'WP_DEVELOP_DIR' ) . '/tests/phpunit/includes/functions.php';

function _bootstrap_wp_idea_stream() {
	// load WP Idea Stream
	require dirname( __FILE__ ) . '/../../wp-idea-stream.php';
}
tests_add_filter( 'muplugins_loaded', '_bootstrap_wp_idea_stream' );

require getenv( 'WP_DEVELOP_DIR' ) . '/tests/phpunit/includes/bootstrap.php';

// include our testcase
require( 'testcase.php' );
