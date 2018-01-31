<?php // (C) Copyright Bobbing Wide 2018

class Tests_includes_class_BW_trace_controller extends BW_UnitTestCase {


	function setUp() { 
		parent::setUp();
		oik_require( "includes/class-BW-trace-controller.php", "oik-bwtrace" );
	}
	
	
	function test__construct() {
	
		$trace_controller = new BW_trace_controller();
		$this->assertInstanceOf( "BW_trace_controller", $trace_controller );
		$this->assertEquals( "cli", $trace_controller->request_type );
		//$this->assertFalse( $trace_controller->trace_on );
	}
	
	
	function test_reset_status() {
	
		$trace_controller = new BW_trace_controller();
		$trace_options = $this->init_trace_options();
		$trace_controller->set_trace_options( $trace_options );
	
		$reset = $trace_controller->reset_status();
    $this->assertFalse( $reset );
		
		$_REQUEST['_bw_trace_reset'] = 'on';
		$reset = $trace_controller->reset_status();
		$_REQUEST['_bw_trace_reset'] = null;
		$this->assertTrue( $reset );
		
		$_REQUEST['wc-ajax'] = 'on';
		$reset = $trace_controller->reset_status();
		$_REQUEST['wc-ajax'] = null;
		$this->assertFalse( $reset );
	
	}
	
	function init_trace_options() {
		$trace_options = array();
		
		$trace_options['file'] = 'bwphpunit.loh';
		$trace_options['reset'] = 'on';
		$trace_options['trace'] = '0';
		$trace_options['file_ajax'] = 'bwphpunit.ajax';
		$trace_options['reset_ajax'] = 'on';
		$trace_options['trace_ajax'] = 'on'; 
		$trace_options['trace_ajax'] = 'on'; 
		$trace_options['file_rest'] = 'bwphpunit.rest';
		$trace_options['reset_reset'] = 'on';
		$trace_options['trace_rest'] = '0'; 
		$trace_options['file_cli'] = 'bwphpunit.cli';
		$trace_options['reset_cli'] = '0';
		$trace_options['trace_cli'] = '0'; 
		$trace_options['limit'] = ''; 
		$trace_options['level'] = BW_TRACE_INFO; // 16
    $trace_options['qualified'] = 'on';
    $trace_options['count'] = 'on';
    $trace_options['date'] = 'on';
    $trace_options['filters'] = 'on';
    $trace_options['num_queries'] = 'on';
    $trace_options['post_id'] = 'on';
    $trace_options['memory'] = 'on';
    $trace_options['files'] = 'on';
    $trace_options['ip'] = '';
		return $trace_options;
	}
	
	
	/**
	 * Tests BW_trace_controller::torf
	 */
	function test_torf() {
		$trace_controller = new BW_trace_controller();
		$trace_options = $this->init_trace_options();
		$trace_controller->set_trace_options( $trace_options );
		
		$ajax = $trace_controller->torf( 'trace_ajax' );
		$this->assertTrue( $ajax );
		
		$trace = $trace_controller->torf( 'trace' );
		$this->assertFalse( $trace );
	}



}
