<?php // (C) Copyright Bobbing Wide 2015
 
/**
 * Load the global bw_action_options
 * 
 * We should only need to do this once
 *
 */ 
function bw_action_options() { 
	global $bw_action_options;
	if ( !isset( $bw_action_options ) ) {
	  $bw_action_options = get_option( 'bw_action_options' );
	}
}

/**
 * Add a selected trace action
 * 
 * @param string $action the action hook e.g. 'wp'
 * @param string $option the option name e.g. 'trace_wp_rewrite'
 * @param string $file the implementing file e.g. 'includes/oik-actions.php'
 * @param string $function the implementing function e.g. 'bw_trace_wp_rewrite'
 * @param integer $count expected parameter count
 */
function bw_trace_add_action( $action, $option, $file, $function, $count=1 ) {
  bw_trace2();
	global $bw_action_options;
	$bw_trace_action = bw_array_get( $bw_action_options, $option, false );
	if ( $bw_trace_action ) {
	  if ( !function_exists( $function ) ) {
		  oik_require( $file, "oik-bwtrace" );
		}
		
		if ( function_exists( $function ) ) {
		  add_action( $action, $function, 10, $count );
		}	else {
			gob();
		}
	}
	return( $bw_trace_action );
}
	
/**
 * Add actions to trace selected actions
 * 
 * For 'wp' we can trace the WordPress instance passed and/or the wp_rewrite structure
 * 
 * 
 * At shutdown create a trace log of the following:
 *
 * - included files
 * - saved queries
 * - general status report
 * 
 * Note: The general status report should also be reportable back to the browser
 * even when trace is not being run but when the trace plugin is activated.
 * So it shouldn't be where it currently is. 
 */
function bw_trace_add_selected_actions() {
	bw_trace_add_action( "wp", "trace_wp_action", "includes/oik-actions.php", "bw_trace_wp" );
	bw_trace_add_action( "wp", "trace_wp_rewrite", "includes/oik-actions.php", "bw_trace_wp_rewrite" );
	bw_trace_add_action( "shutdown", "trace_included_files", "includes/oik-actions.php", "bw_trace_included_files" );
	bw_trace_add_action( "shutdown", "trace_saved_queries", "includes/oik-actions.php", "bw_trace_saved_queries" );
	bw_trace_add_action( "shutdown", "trace_output_buffer", "includes/oik-actions.php", "bw_trace_output_buffer" );
	bw_trace_add_action( "shutdown", "trace_functions", "includes/oik-actions.php", "bw_trace_functions_traced" );
	bw_trace_add_action( "shutdown", "trace_status_report", "includes/oik-actions.php", "bw_trace_status_report" );
	/*
	 * These option names are not defined in the admin interface 
	 * so are not expected to be in the $bw_action_options array 
	 * If you want the hooks to be invoked then you either have to add the entries programmatically 
	 * or cheat by using an existing option name
	 */ 
	bw_trace_add_action( "shutdown", "trace_plugin_paths", "includes/oik-actions.php", "bw_trace_plugin_paths" );
	
	$bw_trace_action = bw_trace_add_action( "deprecated_constructor_run", "trace_deprecated", "includes/bwtrace-actions.php", "bw_trace_deprecated_constructor_run", 2 );
	if ( $bw_trace_action ) {
		add_action( "deprecated_constructor_run", "bw_trace_deprecated_constructor_run", 10, 2 ); // hack to get hook listed in WP-a2z 
		add_action( "deprecated_argument_run", "bw_trace_deprecated_argument_run", 10, 3 );
		add_action( "deprecated_file_included", "bw_trace_deprecated_file_included", 10, 4 );
		add_action( "deprecated_function_run", "bw_trace_deprecated_function_run", 10, 3 );
		add_action( "doing_it_wrong_run", "bw_trace_doing_it_wrong_run", 10, 3 );
		add_filter( "deprecated_argument_trigger_error", "bw_trace_deprecated_argument_trigger_error", 10 ); 
		add_filter( "deprecated_constructor_trigger_error", "bw_trace_deprecated_argument_trigger_error", 10 ); 
		add_filter( "deprecated_file_trigger_error", "bw_trace_deprecated_argument_trigger_error", 10 ); 
		add_filter( "deprecated_function_trigger_error", "bw_trace_deprecated_argument_trigger_error", 10 ); 
		add_filter( "doing_it_wrong_trigger_error", "bw_trace_deprecated_argument_trigger_error", 10 ); 
		
		// Quick tests - these should be commented out
    //_deprecated_argument( __FUNCTION__, "2.0.2", "just a test" );
		//_deprecated_file( __FILE__, "2.0.2", "another file", "just a test" );
		//_deprecated_function( __FUNCTION__, "2.0.3", "anotherfunc" );
		//_doing_it_wrong( __FUNCTION__, "you're doing it wrong", "2.0.3" );
	}
}

/**
 * Perform a debug backtrace before reporting the deprecation
 * 
 * Notice: has_cap was called with an argument that is deprecated since version 2.0! 
 * Usage of user levels by plugins and themes is deprecated. Use roles and capabilities instead. 
 * in /home/cwiccer/public_html/wp-includes/functions.php on line 2712
 *
 *
 * @link http://tumbledesign.com/fix-notice-has_cap-was-called-with-an-argument-that-is-deprecated-since-version-2-0-in-wordpress/
 * @link http://masseltech.com/plugins/underconstruction/
 * @uses bw_backtrace() - from oik
 *
 * @param bool $trigger_error true if we want to trigger the error
 * @return bool the value passed in
 
 */
function bw_trace_deprecated_argument_trigger_error( $trigger_error=true ) {
  bw_backtrace();
  return( $trigger_error ); 
}

/**
 * Implement "deprecated_argument_run" action for oik-bwtrace
 *
 * @param string $function The function that was called
 * @param string $message A message regarding the change
 * @param string $version The version of WordPress that deprecated the argument used
 */
function bw_trace_deprecated_argument_run( $function=null, $message=null, $version=null) {
	bw_trace2();
	bw_backtrace();
}


/**
 * Implement "deprecated_constructor_run" action for oik-bwtrace
 * 
 * @param string $class class name
 * @param string $version version
 */
function bw_trace_deprecated_constructor_run( $class=null, $version=null ) {
	bw_trace2();
	bw_backtrace();
}

/**
 * Implement "deprecated_file_included" action for oik-bwtrace
 */
function bw_trace_deprecated_file_included( $file=null, $replacement=null, $version=null, $message=null ) {
	bw_trace2();
	bw_backtrace();
}

 
/**
 * Implement "deprecated_function_run" action for oik-bwtrace
 *
 * @param string $function The function that was called
 * @param string $replacement The function that should have been called
 * @param string $version The version of WordPress that deprecated the function
 */
function bw_trace_deprecated_function_run( $function=null, $replacement=null, $version=null) {
	bw_trace2();
	bw_backtrace();
}

/**
 * Implement "doing_it_wrong_run" action for oik-bwtrace
 *
 * @param string $function The function that was called
 * @param string $message A message regarding the change
 * @param string $version The version of WordPress that deprecated the function
 */
function bw_trace_doing_it_wrong_run( $function=null, $message=null, $version=null) {
	bw_trace2();
	bw_backtrace();
}





