<?php

/**
 * @copyright Bobbing Wide 2019
 * @package oik-bwtrace
 *
 * Tests for Issue 74
 */
class Tests_issue_74 extends BW_UnitTestCase {

	function setUp() : void {
		parent::setUp();
		oik_require( 'includes/bwtrace.php', 'oik-bwtrace' );
	}

	/**
	 * First check that there are no handlers active.
	 */
	function test_bw_trace_print_r() {
		$handlers = ob_list_handlers();
		$this->assertEquals( $handlers[0], 'default output handler' );
	}

	/**
	 * We don't really need to know what print_r returns
	 * just that when there are no handlers we use it
	 */
	function test_bw_trace_print_r_no_handlers() {
		$tests = array( "test" );
		$output = bw_trace_print_r( "test");
		$this->assertEquals( "test", $output );
		$output = bw_trace_print_r( $tests );
		$print_r = print_r( $tests, true );
		$this->assertEquals( $print_r, $output );
	}

	/**
	 * From https://www.php.net/manual/en/function.print-r.php
	 * When the return parameter is used, this function uses internal output buffering
	 * so it cannot be used inside an ob_start() callback function.
	 *
	 * OK. so how come we can call print_r safely against the array.
	 * Is it because we're not inside an ob_start() callback function?
	 */

	function test_bw_trace_print_r_obsafe() {
		ob_start();
		$handlers = ob_list_handlers();
		$expected = [ 'default output handler', 'default output handler'];
		$this->assertEquals( $expected ,$handlers );

		$tests = array( "test" );
		$output = bw_trace_print_r( "test");
		$expected = "default output handler,default output handler\ntest";
		$this->assertEquals( $expected, $output );
		$output = bw_trace_print_r( $tests );

		$expected = "default output handler,default output handler\n";
		$expected .= "Array\n";
		$expected .= "\n";
		$expected .= "    [0] => (string) \"test\"\n";

		//$print_r = print_r( $tests, true );
		$this->assertEquals( $expected, $output );
		ob_end_flush();

	}


}