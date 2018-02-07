<?php // (C) Copyright Bobbing Wide 2018

/**
 * @package oik-bwtrace 
 * 
 * Tests for Issue #64 
 */
class Tests_issue_64 extends BW_UnitTestCase {

	function setUp() { 
		parent::setUp();
		oik_require( "includes/oik-action-counts.php", "oik-bwtrace" );
	}
	
	function test_gho() {
	
		global $wp_filter;
		$hook = "the_content";
		$hooks = bw_array_get( $wp_filter, $hook, null );
		//print_r( $hooks );
		$this->assertInstanceOf( "WP_Hook", $hooks ); 
	}
	
	function test_bw_trace_get_attached_hook_count() {
		$count = bw_trace_get_attached_hook_count( "issue-64" );
		$this->assertEquals( 0, $count );
		
		add_action( "issue-64", "__return_false", 10 );
		$count = bw_trace_get_attached_hook_count( "issue-64" );
		
		$this->assertEquals( 1, $count );
	
	}
	
	
}
	
