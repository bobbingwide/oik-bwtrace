<?php // (C) Copyright Bobbing Wide 2011-2019
if ( !defined( "BWTRACE_INCLUDED" ) ) {
define( "BWTRACE_INCLUDED", "3.0.0" );
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
 * Constants for bw_trace2's $level parameter
 *
 * - The trace record is produced if the $level passed is greater than or equal to the current tracing level ( $bw_trace_on );
 * - The default value for bw_trace2 is BW_TRACE_ALWAYS
 * - The higher you set the value the more tracing you get.
 * - The testing is NOT (yet) implemented as a bit-mask.
 * - Note: These values are a subset of logging levels in packages such as monolog.
 * - It's not really necessary to have CRITICAL, ALERT or EMERGENCY; ERROR will suffice
 * - See also {@link https://en.wikipedia.org/wiki/Syslog#Severity_levels}
 * 
 */
if ( !defined( 'BW_TRACE_VERBOSE' ) ) { define( 'BW_TRACE_VERBOSE', 64 ); }
if ( !defined( 'BW_TRACE_DEBUG' ) ) { define( 'BW_TRACE_DEBUG', 32 ); }
if ( !defined( 'BW_TRACE_INFO' ) ) { define( 'BW_TRACE_INFO', 16 ); }							// recommended level
if ( !defined( 'BW_TRACE_NOTICE' ) ) { define( 'BW_TRACE_NOTICE', 8 ); }
if ( !defined( 'BW_TRACE_WARNING' ) ) { define( 'BW_TRACE_WARNING', 4 ); }
if ( !defined( 'BW_TRACE_ERROR' ) ) { define( 'BW_TRACE_ERROR', 2 ); }
if ( !defined( 'BW_TRACE_ALWAYS' ) ) { define( 'BW_TRACE_ALWAYS', 0 ); }			// bw_trace2() default

/** 
 * Assume tracing is off
 */
if ( !isset( $bw_trace_on )) {
	if ( defined( 'BW_TRACE_ON' ) ) {
		$bw_trace_on = BW_TRACE_ON;
	} else {
		$bw_trace_on = false;
	}
}

/**
 * Only set trace level if BW_TRACE_LEVEL is defined and it's not already set
 */
if ( !isset( $bw_trace_level )) {
	if ( defined( 'BW_TRACE_LEVEL' ) ) {
		$bw_trace_level = BW_TRACE_LEVEL;
	}
}

/**
 * Log a simple trace record to the trace log file if tracing is active
 *
 * Use bw_trace2() in preference to bw_trace() except in special circumstances
 * which prevent bw_trace2() from being invoked.
 * 
 * @param mixed $text value to be traced
 * @param string $function name of function to log in the trace file. In OO code use __METHOD__
 * @param integer $lineno line number of source file to log
 * @param string $file source file name
 * @param string $text_label a label to help you locate the trace record 
 * @param integer $level required level of tracing
 *
 */
if ( !function_exists( "bw_trace" ) ) { 
	function bw_trace( $text, $function=__FUNCTION__, $lineno=__LINE__, $file=__FILE__, $text_label=null, $level=BW_TRACE_ALWAYS ) {
		global $bw_trace_on, $bw_trace_level;
		if ( $bw_trace_on && ( $level <= $bw_trace_level )  ) {
			bw_lazy_trace( $text, $function, $lineno, $file, $text_label, $level );
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
 * @param integer $level - the trace level requested
 * @return mixed $value - the first parameter
 */
if ( !function_exists( "bw_trace2" ) ) { 
	function bw_trace2( $value=null, $text=null, $show_args=true, $level=BW_TRACE_ALWAYS ) { 
		global $bw_trace_on, $bw_trace_level;
		if ( $bw_trace_on && ( $level <= $bw_trace_level ) ) { 
			return( bw_lazy_trace2( $value, $text, $show_args, $level ));
		} else {  
			return( $value );
		}
	} 
}

/**
 * Log a debug_backtrace() to the trace log file if tracing is active
 * 
 * @param integer $level required level of tracing
 */
if ( !function_exists( "bw_backtrace" ) ) { 
	function bw_backtrace( $level=BW_TRACE_ALWAYS ) {
		global $bw_trace_on, $bw_trace_level;
		if ( $bw_trace_on && ( $level <= $bw_trace_level ) ) { 
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
