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
 */
function bw_trace_add_action( $action, $option, $file, $function ) {
  bw_trace2();
	global $bw_action_options;
	$bw_trace_action = bw_array_get( $bw_action_options, $option, false );
	if ( $bw_trace_action ) {
	  if ( !function_exists( $function ) ) {
		  oik_require( $file, "oik-bwtrace" );
		}
		
		if ( function_exists( $function ) ) {
		  add_action( $action, $function );
		}	else {
			gob();
		}
	}
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
}
