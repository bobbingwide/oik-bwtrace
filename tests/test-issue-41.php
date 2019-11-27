<?php

/**
 * @copyright Bobbing Wide 2018, 2019
 * @package oik-bwtrace
 *
 * Tests for logic in oik-bwtrace.php
 */
class Tests_issue_41 extends BW_UnitTestCase {

	function setUp() : void {
		parent::setUp();
		oik_require( "includes/oik-actions.php", "oik-bwtrace" );
	}

	function test_bw_trace_ok_to_echo_edd_export() {
		$_REQUEST['edd_action'] = 'export_settings';
		$ok = bw_trace_ok_to_echo();
		$this->assertFalse( $ok );
	}

}