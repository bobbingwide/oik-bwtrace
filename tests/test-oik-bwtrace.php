<?php // (C) Copyright Bobbing Wide 2017

/**
 * @package oik-bwtrace 
 * 
 * Tests for logic in oik-bwtrace.php
 */
class Tests_oik_bwtrace extends BW_UnitTestCase {

	function setUp() { 
		parent::setUp();
	}
	
	/**
	 * Tests for bw_trace_status are rather limited by the values of constants
	 * If the constants are already set then we haven't really got much choice.
	 * If not then we can pretend to set tracing off!
	 */
	function test_bw_trace_status() {
		global $bw_trace_on;
		if ( defined( 'BW_TRACE_ON' ) && BW_TRACE_ON ) {
			$bw_trace_on = true;
			$status = bw_trace_status();
			$this->assertTrue( $status );
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
		$this->bw_trace_options = $bw_trace_options;
	}
	
	/**
	 * Restore the saved trace options
	 */
	function restore_bw_trace_options() {
		global $bw_trace_options;
		$bw_trace_options = $this->bw_trace_options;
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
		$bw_trace_options['trace_ajax'] = 'on'; 
		$bw_trace_options['trace'] = '0';
		$bw_trace_options['reset'] = 'on';
		$bw_trace_options['reset_ajax'] = 'on';
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
oik-bwtrace.php 80 1:function bw_trace_reset_status( $bw_trace_ip, $tracing ) {
oik-bwtrace.php 114 1:function bw_trace_level() {
oik-bwtrace.php 130 1:function bw_torf( $array, $option ) {
oik-bwtrace.php 148 1:function bw_trace_plugin_startup() {
oik-bwtrace.php 232 1:function oik_bwtrace_plugins_loaded() {
oik-bwtrace.php 233 7:  if ( function_exists( "is_admin" ) ) {
oik-bwtrace.php 267 22: * In order for this function to have been invoked the oik-lib logic must be in place.
oik-bwtrace.php 268 84: * So we can happily register the libraries in the libs folder using the available functions and methods
oik-bwtrace.php 270 45: * Here we're determining the subset of oik functions that are actually used by oik-bwtrace.
oik-bwtrace.php 277 1:function oik_bwtrace_query_libs( $libraries ) {
oik-bwtrace.php 310 1:function oik_bwtrace_admin_menu() {
oik-bwtrace.php 322 47: * Some parts of oik-bwtrace are dependent on functions in the oik base plugin.
oik-bwtrace.php 323 13: * If these functions are not available then it won't do anything.
oik-bwtrace.php 329 1:function oik_bwtrace_loaded() {
oik-bwtrace.php 335 8:  if ( !function_exists( 'oik_require' ) ) {
oik-bwtrace.php 349 7:  if ( function_exists( "oik_require2" )) {
oik-bwtrace.php 380 7:  if ( function_exists( "add_action" ) ) {
	
	*/
	
	
}
	
