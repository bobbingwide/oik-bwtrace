<?php // (C) Copyright Bobbing Wide 2017

/**
 * oik-bwtrace's "Any change" capability
 * 
 * Monitor a particular variable or constant and report any change.
 * 
 * We're using this to find out where the value of the constant PHP_SAPI changes when running oik-shortcodes in wp-a2z.org
 * 
 *
 */
bw_trace_anychange_loaded();

/**
 * Start watching for any change if required
 *
 * We allow the string to be defined or set in oik action options
 * with the defined value taking precedence, obviously.
 */ 
function bw_trace_anychange_loaded() {
	if ( !defined( "BW_TRACE_ANYCHANGE" ) ) {
		global $bw_action_options;
		$anychange = bw_array_get( $bw_action_options, "anychange", null ); 
		if ( $anychange ) {
			define( "BW_TRACE_ANYCHANGE", $anychange );
		}
	}
	if ( defined( "BW_TRACE_ANYCHANGE" ) ) {
		oik_require( "includes/oik-actions.php", "oik-bwtrace" );
		bw_trace_anychange_on();
	}
}

/**
 * 'all' hook looking for the changes to things listed in string BW_TRACE_ANYCHANGE
 *
 *  
 * @param string $tag - the hook or filter being invoked
 * @param mixed $arg2 - first parameter to the action hook or filter
 */
function bw_trace_anychange( $tag, $arg2=null ) {
	
	static $current_value = null;
	
	$name = BW_TRACE_ANYCHANGE;
	if ( defined( $name ) ) { 
		$value = constant( $name ); 
		if ( null === $value ) {
		 // E_WARNING level error should have been generated
		}
		if ( $value !== $current_value ) {
			bw_trace2( $current_value, "anychange detected: $name", true );
			bw_trace2( $value, "anychange new value for: $name", false );
			bw_backtrace();
			//gob();
		}
		$current_value = $value; 
		
	} else { 
		/// only works for constants at the moment
	}
	
	
	return( $tag );
	
}

 
/**
 * Turn on anychangeing
 *
 * If the BW_TRACE_ANYCHANGE constant is defined then we turn on output buffering
 * and monitor the output buffer for the string on every action or filter hook
 * We might catch the output eventually
 * and that might be enough.
 * 
 */
function bw_trace_anychange_on() {
	if ( defined( "BW_TRACE_ANYCHANGE" ) && BW_TRACE_ANYCHANGE !== null ) {
		add_action( "all", "bw_trace_anychange", 9999, 9 );
		bw_trace2( BW_TRACE_ANYCHANGE, "anychange activated", false );
 	}	else {
		bw_trace2( BW_TRACE_ANYCHANGE, "Invalid constant BW_TRACE_ANYCHANGE", false, BW_TRACE_ERROR );
	}
}

/**
 * Turn off string watching
 *
 * Not yet used by oik-bwtrace.
 */
function bw_trace_anychange_off() {
  remove_action( "all", "bw_trace_anychange", 9999 );
}







 
