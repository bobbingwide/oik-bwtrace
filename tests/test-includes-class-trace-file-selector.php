<?php // (C) Copyright Bobbing Wide 2018

class Tests_includes_class_trace_file_selector extends BW_UnitTestCase {


	function setUp() { 
		oik_require( "includes/class-trace-file-selector.php", "oik-bwtrace" );
	}
	
	
	function test__construct() {
	
		$trace_file_selector = new trace_file_selector();
		$this->assertInstanceOf( "trace_file_selector", $trace_file_selector);
		$filename = $trace_file_selector->get_trace_file_mask();
		$this->assertEquals( ABSPATH . "bwtrace.loh" , $filename );
		$trace_file_name = $trace_file_selector->get_trace_file_name();
		$this->assertEquals( ABSPATH . "bwtrace.loh.3", $trace_file_name );
	}
	
	/** 
	 * We don't want query_newest_file
	 * we want query oldest file
	 * since that's the one we want to re-use
	 *
	 
	
	function test_query_files() {
		$trace_file_selector = new trace_file_selector();
		//$files = $trace_file_selector->query_files();
		
		$file= $trace_file_selector->query_newest_file();
		print_r( $file );
		
	}
	
	 */
	
	
	
	/** 
	 * Given an array of files and their most recent update time
	 * which is the next file to use? Assume limit is 10
	 * so files should be from .1 to .10
	 * 
	 * Filename       Created		    Modified
	 * -------------  ----------    ----------
	 * bwtrace.loh.1  
	 * bwtrace.loh.2 
	 * 
	 * Next generation = 3
	 * 
	 * 
	 * bwtrace.loh.1 
	 * bwtrace.loh.3 
	 * 
	 * Next generation = 4
	 * 
	 * 
	 * bwtrace.loh.9 
	 * 
	 * Next generation = 10
	 * bwtrace.loh.10
	 * Next generation = 
	 * 
	 * Find next unused in range
	 * Find oldest last updated
	 *
	 * 
	 * 
	 */
	 
	function test_find_next_unused() {
		$trace_file_selector = new trace_file_selector();
		$next = $trace_file_selector->query_next_generation();
		
		$this->assertEquals( 3, $next );
	}
	
	function test_query_oldest_file() {
	
		$trace_file_selector = new trace_file_selector();
		$trace_file_selector->set_limit( 2 );
		$next = $trace_file_selector->query_next_generation();
		$this->assertEquals( 1, $next );
	}
	
		
	





}
