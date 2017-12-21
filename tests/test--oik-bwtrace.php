<?php // (C) Copyright Bobbing Wide 2017

/**
 * @package oik-bwtrace 
 * 
 * Tests for logic in oik-bwtrace.php
 */
class Tests_oik_bwtrace extends BW_UnitTestCase {

	public $bw_trace_on = null;

	function setUp() { 
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
	 */
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
	 * Restore the saved trace options
	 */
	function restore_bw_trace_options() {
		global $bw_trace_options;
		$bw_trace_options = $this->bw_trace_options;
	}
	
	/**
	 * Restore the saved action options
	 */
	function restore_bw_action_options() {
		global $bw_action_options;
		$bw_action_options = $this->bw_action_options;
	}
	
	/**
	 * Update bw_trace_options
	 */
	function update_bw_trace_options() {
		global $bw_trace_options;
		update_option( "bw_trace_options", $bw_trace_options );
		//print_r( $bw_trace_options );
	}
	
	
	/**
	 * Set the trace options to defined values
	 * 
	 * Here's an example of the array structure
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
    $bw_action_options['hooks'] = '';
    $bw_action_options['results'] = '';
    $bw_action_options['post_hooks'] = '';
    $bw_action_options['hook_funcs'] = '';
    $bw_action_options['backtrace'] = '';
    $bw_action_options['stringwatch'] = '';
	} 
	
	/**
	 * Tests bw_trace_reset_status
	 */
	function test_bw_trace_reset_status() {
	
		$reset = bw_trace_reset_status( "IP", false );
		$this->assertFalse( $reset );
		
		$this->save_bw_trace_options(); 
		$this->init_bw_trace_options();
		$reset = bw_trace_reset_status( "IP", true );
		$this->assertTrue( $reset );
		$reset = bw_trace_reset_status( null, true );
		$this->restore_bw_trace_options();
		$this->assertTrue( $reset );
		
		$_REQUEST['_bw_trace_reset'] = 'on';
		$reset = bw_trace_reset_status( "IP", false );
		$_REQUEST['_bw_trace_reset'] = null;
		$this->assertTrue( $reset );
		
		$_REQUEST['wc-ajax'] = 'on';
		$reset = bw_trace_reset_status( "IP", false );
		$_REQUEST['wc-ajax'] = null;
		$this->assertFalse( $reset );
	
	}
	
	/** 
	 * Tests bw_trace_level
	 * 
	 */
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
	
	/**
	 * Tests bw_torf
	 */
	function test_bw_torf() {
		global $bw_trace_options;
		$this->save_bw_trace_options(); 
		$this->init_bw_trace_options();
		$ajax = bw_torf( $bw_trace_options, 'trace_ajax' );
		$this->assertTrue( $ajax );
		$trace = bw_torf( $bw_trace_options, 'trace' );
		$this->assertFalse( $trace );
		$this->restore_bw_trace_options();
	}
	
	/**
	 * Test trace plugin startup with tracing off
	 *
	 * Note: There's a lot of faffing to cater for the fact that trace may already have been started.
	 */
	function test_bw_trace_plugin_startup_tracing_off() {
		global $bw_trace_options;
		global $bw_action_options;
	
		$this->save_bw_trace_options(); 
		if ( $bw_trace_options['trace'] == "on" ) {
			$bw_trace_options['trace'] = '0';
			bw_trace_off();
		}
		$this->init_bw_trace_options();
		$this->update_bw_trace_options();
		$this->save_bw_action_options();
		$this->init_bw_action_options();
		
		bw_trace_plugin_startup();
		
		$tracing = bw_trace_status();
		$this->assertFalse( $tracing );
		
		$this->restore_bw_trace_options();
		$this->restore_bw_action_options();
	}
	
	/**
	 * Test trace plugin startup with tracing on
	 * 
	 * - We have to ensure tracing is on and to a file we control
	 * - The trace file should be reset.
	 */
	function test_bw_trace_plugin_startup_tracing_on() {
		global $bw_trace_options;
		global $bw_action_options;
	
		$this->save_bw_trace_options(); 
		$this->init_bw_trace_options();
		$bw_trace_options['trace'] = 'on';
		$this->update_bw_trace_options();
		
		$this->save_bw_action_options();
		$this->init_bw_action_options();
		
		bw_trace_plugin_startup();
		
		$tracing = bw_trace_status();
		$this->assertTrue( $tracing );
		
		
		$this->restore_bw_trace_options();
		$this->restore_bw_action_options();
		
		if ( $bw_trace_options['trace'] == 0 ) {
			bw_trace_off();
		}
	}
	
	/** 
	 * We can probably get away with saying that the following functions are tested implicitely.
	 * - oik_bwtrace_plugins_loaded
	 * - oik_bwtrace_query_libs
	 * - oik_bwtrace_admin_menu
	 * - oik_bwtrace_loaded
	 */
	
	
}
	
