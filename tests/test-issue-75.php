<?php

/**
 * @copyright Bobbing Wide 2018, 2019
 * @package oik-bwtrace
 *
 * Tests for logic in oik-bwtrace.php
 */
class Tests_issue_75 extends BW_UnitTestCase {

	public $bw_trace_on = null;

	function setUp() : void {
		parent::setUp();
		oik_require( "includes/oik-actions.php", "oik-bwtrace" );
	}

	function test_bw_trace_ok_to_echo_wordfence_download() {
		$_REQUEST['downloadBackup'] = '1';
		$ok = bw_trace_ok_to_echo();
		$this->assertFalse( $ok );
	}

}