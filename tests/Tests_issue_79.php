<?php

/**
 * @copyright Bobbing Wide 2019-2021
 * @package oik-bwtrace
 *
 * Tests for Issue 79, 81 and 98 - different versions of PHP's EOL
 */
class Tests_issue_79 extends BW_UnitTestCase {

	function setUp() : void {
		parent::setUp();
		oik_require( 'admin/class-oik-trace-info.php', 'oik-bwtrace' );
	}

	/**
	 *
	 *
	 */
	function test_php_end_of_life() {
		$phpversion = explode( '.', phpversion() );
		switch ( $phpversion[1] ) {
			case '3':
				if ( '8' === $phpversion[0]) {
					$expected = 'End of life for your version of PHP is: 2026-11-23';
				} else {
					$expected='End of life for your version of PHP was: 2021-12-06';
				}
				break;
			case '4':
				if ( '8' === $phpversion[0]) {
					$expected='End of life for your version of PHP is: 2028-12-31';
				} else {
					$expected='End of life for your version of PHP is: 2022-11-28';
				}
				break;
			case '0':
				$expected = 'End of life for your version of PHP was: 2023-11-26';
				break;
			case '1':
				$expected = 'End of life for your version of PHP is: 2024-11-25';
				break;
			case '2':
				$expected = 'End of life for your version of PHP is: 2025-12-08';
				break;
			default:
				echo 'Not catered for' . PHP_EOL;
		}
		$trace_info = new OIK_trace_info();
		//$expected = 'End of life for your version of PHP is: 2021-12-06';
		$message = $trace_info->php_end_of_life();
		$this->assertEquals( $expected, $message );
	}

	function test_bw_trace_output_buffer_for_zlib_output_compression_on() {
		$zloc = ini_get( 'zlib.output_compression');
		if ( $zloc ) {
			ob_start();
			echo __FUNCTION__;
			bw_trace_output_buffer();
			$buffer = ob_get_clean();
			$this->assertEquals( __FUNCTION__ , $buffer );
			//echo $buffer;

		} else {
			// It's not enabled so we can use ob_get_flush
			// We need to tell PHPUnit that we're expecting output
			$this->expectOutputString( __FUNCTION__ );
			ob_start();
			echo __FUNCTION__;
			bw_trace_output_buffer();

		}
		$this->assertTrue( true );


	}

	/**
	 * Here we try turning zlib.output_compression on if it's off.
	 * Well, we can't do it because we get
	 *
	 * Warning: ini_set(): Cannot change zlib.output_compression -
	 * headers already sent in C:\apache\htdocs\wordpress\wp-content\plugins\oik-bwtrace\tests\test-issue-79.php on line 59
	 *
	 */
	function test_bw_trace_output_buffer_for_zlib_output_compression_off() {
		$zloc = ini_get( 'zlib.output_compression');
		if ( $zloc ) {
			// It's already on
			ob_start();
			echo __FUNCTION__;
			bw_trace_output_buffer();
			$buffer = ob_get_clean();
			$this->assertEquals( __FUNCTION__ , $buffer );

		} else {
			//ini_set( 'zlib.output_compression', 1 );
		}


		$this->assertTrue( true );


	}


}