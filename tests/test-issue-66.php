<?php 

/**
 * @copyright Bobbing Wide 2018, 2019
 * @package oik-bwtrace 
 * 
 * Tests for logic in oik-bwtrace.php
 */
class Tests_issue_66 extends BW_UnitTestCase {

	public $bw_trace_on = null;

	function setUp() : void {
		parent::setUp();
		oik_require( "includes/oik-action-counts.php", "oik-bwtrace" );
	}
	
	function test_bw_trace_get_attached_hook_count() {
		$count = bw_trace_get_attached_hook_count( "oik-bwtrace-issue-66" );
		$this->assertEquals( 0, $count );
		add_action( "oik-bwtrace-issue-66", "__return_true" );
		$count = bw_trace_get_attached_hook_count( "oik-bwtrace-issue-66" );
		$this->assertEquals( 1, $count );
	}
	
}
