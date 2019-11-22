<?php

/**
 * @copyright Bobbing Wide 2018, 2019
 * @package oik-bwtrace
 *
 * Tests issue 76 - bw_trace_hexdump capability
 */
class Tests_issue_76 extends BW_UnitTestCase {

	function setUp() : void {
		parent::setUp();
		oik_require( "includes/oik-actions.php", "oik-bwtrace" );
	}

	function test_bw_trace_hexdump() {
		$string = "ABCDEFG\n12345678910\t£";
		//echo $string;
		$hex = bw_trace_hexdump( $string );

		$expected = "22\r\n";
		$expected .= "ABCDEFG.12345678910. 41 42 43 44 45 46 47 0a 31 32 33 34 35 36 37 38 39 31 30 09 \r\n";
		$expected .= "£.................. c2 a3 \r\n";
		$this->assertEquals( $expected, $hex );

	}



}