<?php

/**
 * @copyright (C) Copyright Bobbing Wide 2017,2019,2022,2023
 * @package oik-bwtrace 
 * 
 * Tests for logic in oik-bwtrace.php
 */
class Tests__oik_bwtrace extends BW_UnitTestCase {

	public $bw_trace_on = null;
	public $bw_trace_options;
	public $saved_trace_files_options;
	public $bw_trace_files_options;
	public $bw_action_options;


	function setUp() : void {
		parent::setUp();
	}
	
	function test_constant_bw_trace_on() {
	
		if ( defined( 'BW_TRACE_ON' ) ) { 
			if ( BW_TRACE_ON ) {
				//echo "BW_TRACE_ON is true!";
				$this->bw_trace_on = true;
			} else {
				//echo "BW_TRACE_ON is false";
				$this->bw_trace_on = false;
			}
		} else {
			//echo "BW_TRACE_ON is not set";
			$this->bw_trace_on = null;
		}
		$this->assertTrue( true );
	}
			
	
	/**
	 * Tests for bw_trace_status are rather limited by the values of constants
	 * If the constants are already set then we haven't really got much choice.
	 * If not then we can pretend to set tracing off!
	function test_bw_trace_status() {
		global $bw_trace_on;
		if ( defined( 'BW_TRACE_ON' ) && BW_TRACE_ON ) {
			$saved = $bw_trace_on;
			$bw_trace_on = true;
			$status = bw_trace_status();
			$this->assertTrue( $status );
			$bw_trace_on = $saved;
																		 
			return;
		}
		$this->save_bw_trace_options(); 
		$this->init_bw_trace_options();
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			$status = bw_trace_status();
			$this->assertTrue( $status );
		} else { 
			$status = bw_trace_status();
			$this->assertFalse( $status );
		}
		$this->restore_bw_trace_options();
	}
	 */
	
	/**
	 * Save the current trace options.
	 */
	function save_bw_trace_options() {
		global $bw_trace_options;
		//print_r( $bw_trace_options );
		$this->bw_trace_options = $bw_trace_options;
	}
	
	function save_bw_action_options() {
		global $bw_action_options;
		$this->bw_action_options = $bw_action_options;
	}
	
	/**
	 * Saves the setting for trace_files_directory
	 * which may be null if the options value is not defined or invalid
	 */
	function save_bw_trace_files_options() {
		global $bw_trace;
		$this->saved_trace_files_options = $bw_trace->trace_files_options;
	}
	
	/**
	 * Restore the saved trace options
	 */
	function restore_bw_trace_options() {
		global $bw_trace_options;
		$bw_trace_options = $this->bw_trace_options;
		$this->update_bw_trace_options();
	}
	
	/**
	 * Restore the saved action options
	 */
	function restore_bw_action_options() {
		global $bw_action_options;
		$bw_action_options = $this->bw_action_options;
	}
	
	
	/**
	 * Restore the trace files options 
	 */
	function restore_bw_trace_files_options() {
		global $bw_trace;
		$bw_trace->trace_files_options = $this->saved_trace_files_options;
	}
	
	/**
	 * Update bw_trace_options
	 */
	function update_bw_trace_options() {
		global $bw_trace_options;
		update_option( "bw_trace_options", $bw_trace_options );
		//echo "Update_bw_trace_options";
		//print_r( $bw_trace_options );
	}

	/**
	 * Update bw_action_options
	 */
	function update_bw_action_options() {
		global $bw_action_options;
		update_option( "bw_action_options", $bw_action_options );
	}
	
	/**
	 * Update bw_trace_files_options
	 *
	 */
	function update_bw_trace_files_options() {
		global $bw_trace;
		update_option( "bw_trace_files_options", $bw_trace->trace_files_options );
		//print_r( $this->bw_trace_files_options );
	}
	
	
	/**
	 * Set the trace options to defined values
	 * 
	 * Here's an example of the array structure
	 * @TODO Needs updating for rest_ and cli_ fields and limit
	 * `

    [file] => bwtraces.loh
    [reset] => on
    [trace] => 0
    [file_ajax] => bwtraces.ajax
    [reset_ajax] => on
    [trace_ajax] => on
    [level] => 32
    [qualified] => on
    [count] => on
    [date] => on
    [filters] => on
    [num_queries] => on
    [post_id] => on
    [memory] => on
    [files] => on
    [ip] =>
	* `	
	*/
	function init_bw_trace_options() {
		global $bw_trace_options;
		//print_r( $bw_trace_options );
		$bw_trace_options['file'] = 'bwphpunit.loh';
		$bw_trace_options['reset'] = 'on';
		$bw_trace_options['trace'] = '0';
		$bw_trace_options['file_ajax'] = 'bwphpunit.ajax';
		$bw_trace_options['reset_ajax'] = 'on';
		$bw_trace_options['trace_ajax'] = 'on'; 
		
		$bw_trace_options['file_rest'] = 'bwphpunit.rest';
		$bw_trace_options['reset_rest'] = 'on';
		$bw_trace_options['trace_rest'] = 'on'; 
		
		$bw_trace_options['file_cli'] = 'bwphpunit.cli';
		$bw_trace_options['reset_cli'] = 'on';
		$bw_trace_options['trace_cli'] = '0'; 
		
		$bw_trace_options['limit'] = 10;
		$bw_trace_options['level'] = BW_TRACE_INFO; // 16
		
    $bw_trace_options['qualified'] = 'on';
    $bw_trace_options['count'] = 'on';
    $bw_trace_options['date'] = 'on';
    $bw_trace_options['filters'] = 'on';
    $bw_trace_options['num_queries'] = 'on';
    $bw_trace_options['post_id'] = 'on';
    $bw_trace_options['memory'] = 'on';
    $bw_trace_options['files'] = 'on';
    $bw_trace_options['ip'] = php_sapi_name();
	}
	
	function init_bw_action_options() {
		global $bw_action_options;
		$bw_action_options['count'] = 'on';
    $bw_action_options['trace_deprecated'] = 'on';
    $bw_action_options['trace_errors'] = 'on';
    $bw_action_options['trace_wp_action'] = '0';
    $bw_action_options['trace_wp_rewrite'] = '0';
    $bw_action_options['trace_included_files'] = 'on';
    $bw_action_options['trace_saved_queries'] = 'on';
    $bw_action_options['trace_output_buffer'] = 'on';
    $bw_action_options['trace_functions'] = 'on';
    $bw_action_options['trace_status_report'] = 'on';
    $bw_action_options['trace_url_links'] = 'on';
    $bw_action_options['hooks'] = '';
    $bw_action_options['results'] = '';
    $bw_action_options['post_hooks'] = '';
    $bw_action_options['hook_funcs'] = '';
    $bw_action_options['backtrace'] = '';
    $bw_action_options['stringwatch'] = '';
	}
	
	function init_bw_trace_files_options() {
		global $bw_trace;
		$bw_trace->trace_files_options = array();
		$bw_trace->trace_files_options['trace_directory'] = str_replace( "\\", "/", __DIR__ ) . '/bwtrace';
		$bw_trace->trace_files_options['retain'] = 1;
		$bw_trace->trace_files_options['performance_trace'] = 0;
		
	} 
	
	/**
	 * Tests bw_trace_reset_status
	 * 
	 * Function moved to class BW_trace_controller::reset_status
	 */
	
	/** 
	 * Tests bw_trace_level
	 * 
	 * @TODO Transfer test to BW_trace_controller
	 
	function test_bw_trace_level() {
	 	global $bw_trace_level;
		$this->save_bw_trace_options(); 
		$this->init_bw_trace_options();
		$bw_trace_level = BW_TRACE_VERBOSE;
		$level = bw_trace_level();
		$this->assertEquals( BW_TRACE_VERBOSE, $level );
		
		$bw_trace_level = null;
		$level = bw_trace_level();
		$this->assertEquals( BW_TRACE_INFO, $level );
		
		$this->restore_bw_trace_options();
	}
	 */
	 
	
	/**
	 * Test trace plugin startup with tracing on
	 * 
	 * - We have to ensure tracing is on and to a file we control
	 * - The trace file should be reset.
	 */
	function test_bw_trace_plugin_startup_tracing_on() {
		global $bw_trace_options;
		global $bw_action_options;
        oik_require( 'admin/oik-bwtrace.php', 'oik-bwtrace');
        bw_trace_options_sync();
		$this->save_bw_trace_options();
		$this->save_bw_trace_files_options();
		$this->init_bw_trace_options();
		$bw_trace_options['trace_cli'] = 'on';

		$this->init_bw_trace_files_options();
		$this->update_bw_trace_options();
		$this->update_bw_trace_files_options();
		
		$this->save_bw_action_options();
		$this->init_bw_action_options();
		
		global $bw_trace;
		//print_r( $bw_trace );

		
		bw_trace_plugin_startup();
		//print_r( $bw_trace );


		
		$tracing = bw_trace_status();
		$this->assertTrue( $tracing, "CLI tracing should be on" );

		$this->bw_trace2_issue56();
		$this->bw_lazy_backtrace_issue56();
		
		
		$this->restore_bw_trace_options();
		$this->restore_bw_action_options();
        $this->restore_bw_trace_files_options();
		$this->update_bw_trace_options();
		$this->update_bw_action_options();
		$this->update_bw_trace_files_options();
		
		//if ( $bw_trace_options['trace'] == 0 ) {
		//	bw_trace_off();
		//}
	}
	
	
	/**
	 * Test trace plugin startup with tracing off
	 *
	 * Note: There's a lot of faffing to cater for the fact that trace may already have been started.
	 *
	 * If BW_TRACE_ON is defined then we can't test this.
	 */
	function test_bw_trace_plugin_startup_tracing_off() {
		if ( defined( 'BW_TRACE_ON' ) ) {
			$this->markTestSkipped( "Constant BW_TRACE_ON is defined: " . BW_TRACE_ON ); 
		}
		global $bw_trace_options;
		global $bw_action_options;
        oik_require( 'admin/oik-bwtrace.php', 'oik-bwtrace');
        bw_trace_options_sync();
	
		$this->save_bw_trace_options();
		$this->save_bw_trace_files_options();

		if ( null !== $bw_trace_options) {
			if ( $bw_trace_options['trace_cli'] == "on" ) {
				$bw_trace_options['trace_cli']='0';
				bw_trace_off();
				echo "Turned tracing off!";
			}
		}
		$this->init_bw_trace_options();
		$bw_trace_options['trace_cli'] = '0';
		$this->init_bw_trace_files_options();
		
		$this->update_bw_trace_options();
		$this->update_bw_trace_files_options();
		$this->save_bw_action_options();
		$this->init_bw_action_options();
		global $bw_trace;
		//print_r( $bw_trace );
		
		bw_trace_plugin_startup();
		//print_r( $bw_trace );
		
		$tracing = bw_trace_status();
		
		$this->assertFalse( $tracing, "CLI tracing should be off" );
		
		$this->restore_bw_trace_options();
		$this->restore_bw_action_options();
		$this->restore_bw_trace_files_options();
		$this->update_bw_trace_options();
		$this->update_bw_action_options();
		$this->update_bw_trace_files_options();
	}

	/**
	 * Test we can trace an incomplete object
	 *
	 * Not that it caused a problem in the first place.
	 */
	function bw_trace2_issue56() {
		$incomplete_object = $this->incomplete_object();
		$this->trace2( $incomplete_object );
		$this->assertTrue( true );
	}

	/**
	 * Test for Issue #56
	 *
	 * We should not see a Catchable Fatal Error or Recoverable Fatal Error
	 *
	 * depends test_bw_trace2_issue56
	 */
	function bw_lazy_backtrace_issue56() {
		$incomplete_object = $this->incomplete_object();
		$this->backtrace( $incomplete_object );
		$this->assertTrue( true );
	}
	/**
	 * Returns an incomplete object.
	 *
	 * That would print_r() like this
	 *
	 * `__PHP_Incomplete_Class Object
	(
	[__PHP_Incomplete_Class_Name] => IncompleteObject
	[pi] => 3.142
	)
	 * `
	 */
	function incomplete_object() {
		$incomplete_object = unserialize( 'O:16:"IncompleteObject":1:{s:2:"pi";d:3.142;}' );
		return( $incomplete_object );
	}
	/**
	 * Traces the incomplete object
	 */
	function trace2( $incomplete_object ) {
		bw_trace2();
	}
	/**
	 * Debug backtraces the incomplete object
	 */
	function backtrace( $incomplete_object ) {
		bw_backtrace();
	}
	
	/** 
	 * We can probably get away with saying that the following functions are tested implicitely.
	 * - oik_bwtrace_plugins_loaded
	 * - oik_bwtrace_query_libs
	 * - oik_bwtrace_admin_menu
	 * - oik_bwtrace_loaded
	 */
	
	
}
	
