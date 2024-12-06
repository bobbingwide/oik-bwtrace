<?php // (C) Copyright Bobbing Wide 2019

/**
 * @package oik-bwtrace 
 * 
 * Tests for Issue #85
 */
class Tests_issue_85 extends BW_UnitTestCase {

	function setUp() : void {
		parent::setUp();
		//oik_require( "includes/oik-action-counts.php", "oik-bwtrace" );
		require_once ABSPATH . 'wp-admin/includes/template.php';
	}

	function get_settings_errors( $expected ) {
		$errors = get_settings_errors();
		$count_errors = count( $errors );
		if ( is_scalar( $expected ) ) {
			$count_expected = $expected;
			$this->assertEquals( $count_expected, $count_errors );
		}
		else {
			$count_expected = count( $expected );
			$this->assertEquals( $count_expected, $count_errors );
			$this->assertEquals( $expected, $errors );
		}
		global $wp_settings_errors;
		$wp_settings_errors = [];
	}

	function test_bw_trace_validate_filename_unwanted_dir() {
		$filename = bw_trace_validate_filename( 'dirs/not/wanted.log', 'test85' );
		$this->assertEquals( 'wanted.log', $filename );
		$this->get_settings_errors( [ ['setting' => 'test85', 'code' => 'test85', 'message' => 'Trace file name path ( dirs/not ) ignored.', 'type' => 'info'] ] );
	}

	function test_bw_trace_validate_filename_no_extension() {
		$filename = bw_trace_validate_filename( 'noextension', 'test85' );
		$this->assertEquals( 'noextension.log', $filename );
		$this->get_settings_errors( [ [ 'setting' => 'test85', 'code' => 'test85' , 'message' => 'Assuming file extension: .log', 'type' => 'info'] ] );

	}


	function test_bw_trace_validate_filename_dot() {
		$filename = bw_trace_validate_filename( '.', 'test85');
		$this->assertEquals( 'bwtrace.log', $filename );
		$this->get_settings_errors( 2 );
	}

	function test_bw_trace_validate_filename_dot_log() {
		$filename = bw_trace_validate_filename( '.log', 'test85');
		$this->assertEquals( 'bwtrace.log', $filename );
		$this->get_settings_errors( [ ['setting' => 'test85', 'code' => 'test85', 'message' => 'Assuming file name: bwtrace', 'type' => 'info'] ] );
	}

	/**
	 * Tests bw_trace_options_validate
	 * which also tests bw_trace_validate_filename's tests
	 * And we need to check the messages that are output.
	 */
	function test_bw_trace_options_validate() {
		$errors = $this->get_settings_errors( 0 );
		$input   =[
			'file'      =>null,
			'file_ajax' =>'dirs/not/wanted.log',
			'file_rest' =>'noextension',
			'file_cli'  =>'.log',
			'trace'     =>'on',
			'trace_ajax'=>'0',
			'trace_rest'=>'0',
			'trace_cli' =>'0'
		];
		$output  = bw_trace_options_validate( $input );
		$expected= [
			'file'      =>'bwtrace.log',
			'trace'     =>'on',
			'trace_ajax'=>'0',
			'trace_rest'=>'0',
			'trace_cli' =>'0',
			'ip'        =>'',
			'file_ajax' =>'wanted.log',
			'file_rest' =>'noextension.log',
			'file_cli'  =>'bwtrace.log'
		];
		$this->assertEquals( $output, $expected );
		$this->get_settings_errors(4 );
	}


}
