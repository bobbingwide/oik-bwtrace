<?php // (C) Copyright Bobbing Wide 2011-2015
if ( !defined( "BWTRACE_INCLUDED" ) ) {
define( "BWTRACE_INCLUDED", "2.0.1" );
define( "BWTRACE_FILE", __FILE__ );

/**
 * Trace library functions
 *
 * Library: bwtrace
 * Provides: bwtrace
 * Type: MU
 *
 */

/** 
 * Assume tracing is off
 */
if ( !isset( $bw_trace_on )) {
  $bw_trace_on = false;
}

/**
 * Log a simple trace record to the trace log file if tracing is active
 *
 * Use bw_trace2() in preference to bw_trace() except in special circumstances
 * which prevent bw_trace2() from being invoked.
 * 
 * @param mixed $text value to be traced
 * @param string $function name of function to log in the trace file
 * @param integer $lineno line number of source file to log
 * @param string $file source file name
 * @param string $text_label a label to help you locate the trace record 
 *
 */
if ( !function_exists( "bw_trace" ) ) { 
	function bw_trace( $text, $function=__FUNCTION__, $lineno=__LINE__, $file=__FILE__, $text_label=NULL) {
		global $bw_trace_on;
		if ( $bw_trace_on  ) {
			bw_lazy_trace( $text, $function, $lineno, $file, $text_label );
		}  
	}
}

/**
 * Trace $value to the trace log file if tracing is active
 * 
 * @param mixed $value - the value to be traced. 
 * The value can be a simple field, array or complex object such as a post
 * @param string $text - additional information
 * @param bool $show_args - true if the parameter values are to be traced, false otherwise
 * @return mixed $value - the first parameter
 */
if ( !function_exists( "bw_trace2" ) ) { 
	function bw_trace2( $value=NULL, $text=NULL, $show_args=true ) { 
		global $bw_trace_on;
		if ( $bw_trace_on ) { 
			return( bw_lazy_trace2( $value, $text, $show_args ));
		} else {  
			return( $value );
		}
	} 
}

/**
 * Log a debug_backtrace() to the trace log file if tracing is active
 * 
 */
if ( !function_exists( "bw_backtrace" ) ) { 
	function bw_backtrace() {
		global $bw_trace_on;
		if ( $bw_trace_on ) {
			bw_lazy_backtrace();
		}
	}
}    

/**
 * Start up tracing from the wp-config file if required
 * 
 * Only do this if the file is available from the current library
 */
if ( defined( 'BW_TRACE_CONFIG_STARTUP' ) && BW_TRACE_CONFIG_STARTUP == true ) {
	$bwtrace_boot = __DIR__ . '/bwtrace_boot.php';
	if ( file_exists( $bwtrace_boot ) ) {
		require_once( $bwtrace_boot );
	}
}
  
} /* end !defined */
