<?php // (C) Copyright Bobbing Wide 2019

/**
 * @package oik-bwtrace
 *
 * Tests for Issue #63
 */
class Tests_issue_63 extends BW_UnitTestCase {

	function setUp() : void {
		parent::setUp();
		//oik_require( "includes/oik-action-counts.php", "oik-bwtrace" );
	}

	function issue_63_filter( $priority ) {
		$priority = bw_trace_inspect_current();
		return $priority;
	}

	function test_bw_trace_inspect_current() {
		add_filter( 'issue-63', [$this, 'issue_63_filter' ], 63 );
		$priority = apply_filters( 'issue-63', -100 );
		$this->assertEquals( 63, $priority );
	}

}


