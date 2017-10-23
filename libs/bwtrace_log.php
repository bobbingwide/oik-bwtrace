<?php // (C) Copyright Bobbing Wide 2016, 2017
if ( !defined( "OIK_BWTRACE_LOG_INCLUDED" ) ) {
	define( "OIK_BWTRACE_LOG_INCLUDED", "0.0.2" );
	
/**
 * Logging library functions
 *
 * Library: bw_log
 * Provides: bw_log
 * Type: ?
 *
 * Implements an always available, always active logging function that MAY also perform tracing
 * or invoke callbacks to find other useful information that will help in problem determination.
 * 
 *
 */
 
 
/**
 * Log missing files
 * 
 * In my symlinked environment in Windows I often get reports that a file does not exist
 * when it pretty certainly does, albeit a symlinked file. 
 
 * This code, which used to be in the oik_boot shared library attempts to report what's wrong.
 * But it created serious problems for REST APIs which only expect to see JSON responses.
 * I changed it to check for WP_DEBUG before echoing anything.
 * Then I realised that I needed better information for problem determination
 * so I chose to log the output to the PHP error log using the error_log() function.
 * 
 * The 'you're having me on' function is now a builtin callback function that may eventually be deprecated
 * once the problem determination is complete.
 
 * In most cases, where oik_require() has been invoked correctly we do expect the file to exist.
 * 
 * The second test checks that the file really doesn't exist
 * 
 * @param string $file - the fully qualified file name
 * @return string - some additional information that may help debugging.
 */
function oik_yourehavingmeon( $file ) {
	if ( file_exists( $file ) ) {
		$file = "exists now";
		return $file;
	}
	if ( defined( 'WP_DEBUG') && WP_DEBUG ) {
		echo "<!-- File does not exist:$file! -->" ;
		if ( !is_file( $file ) ) {
			echo "<!-- File is not a real file:$file! -->" ;
		}
		echo "<!-- ";
		echo __FILE__ ; 
		echo PHP_EOL;
		print_r( debug_backtrace() );
		echo " -->";
	}
	
	if ( file_exists( $file ) ) {
	
		//gob(); this is not expected... but perhaps that's part of the problem!
		
		if ( defined( 'WP_DEBUG') && WP_DEBUG ) {
			echo "<!-- Oh. And now it does exist! $file -->";
		}	
		$file = "exists a bit later";
		
	}
	return( $file );	
}
 
/**
 * Implements the logging function
 * 
 * Values for $level may be as defined for [github Seldaek monolog]
 *
 * String | Value | Meaning
 * ------ | ----- | -----------------
 * 'debug' | 100 | 
 * 'info' | 200 |
 * 'notice' |  250 |
 * 'warning' | 300 |
 * 'error' | 400 | 
 * 'critical' | 500
 * 'alert' | 550
 * 'emergency' | 600
 * callablefunction | 
 * BW_TRACE_xxx | standard trace level
 * 
 * @TODO Map the passed trace level to the values expected by trace functions 
 *
 * @param mixed $value - the data to log and trace
 * @param string $text - a label for the output
 * @param bool $show_args - passed to bw_lazy_trace2()
 * @param string|function name $level - the trace level 
 * 
 */ 
function bw_lazy_log( $value=null, $text=null, $show_args=true, $level='error' ) {
	$flat_value = bw_trace_print_r( $value ); 
	if ( is_callable( $level ) ) { 
		$extra = call_user_func( $level, $value, $text );
	} else {
		$extra = $level;
	}
	
	$logged = error_log( "$text:$flat_value:$extra", 0 );
	
	if ( function_exists( "bw_lazy_trace2" ) ) {
		bw_lazy_trace2( $value, $text, $show_args );
	}
	
	if ( function_exists( "bw_lazy_backtrace" ) ) {	
		bw_lazy_backtrace();
	}
	return( $value );
}


 
} else {
	// end if !defined
}

 
 

