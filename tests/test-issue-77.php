<?php

/**
 * @copyright Bobbing Wide 2018, 2019
 * @package oik-bwtrace
 *
 * Tests for logic in oik-bwtrace.php
 */
class Tests_issue_77 extends BW_UnitTestCase {

	function setUp() : void {
		parent::setUp();
		oik_require( "includes/oik-actions.php", "oik-bwtrace" );
	}

	function test_bw_trace_ok_to_echo() {
		$_REQUEST['health-check-test-wp_version_check'] = '1';
		$ok = bw_trace_ok_to_echo();
		$this->assertFalse( $ok );
	}

}
