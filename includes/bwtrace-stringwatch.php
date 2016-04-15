<?php // (C) Copyright Bobbing Wide 2016

/**
 * oik-bwtrace's "String watch" capability
 *
 * Monitor the output for a particular string
 * When it's found write it to the trace output.
 * Only report the first instance.
 * 
 * @TODO We may need to start really early - as soon as actions are enabled
 */
bw_trace_stringwatch_loaded();

/**
 * Start string watching if required
 *
 * We allow the string to be defined or set in oik action options
 * with the defined value taking precedence, obviously.
 */ 
function bw_trace_stringwatch_loaded() {
	if ( !defined( "BW_TRACE_STRINGWATCH" ) ) {
		global $bw_action_options;
		$stringwatch = bw_array_get( $bw_action_options, "stringwatch", null ); 
		if ( $stringwatch ) {
			define( "BW_TRACE_STRINGWATCH", $stringwatch );
		}
	}
	if ( defined( "BW_TRACE_STRINGWATCH" ) ) {
		bw_trace_stringwatch_on();
	}
}

/**
 * 'all' hook looking for the defined string BW_TRACE_STRINGWATCH
 *
 * If found in the output buffer or in the trace input
 * then it will produce a trace and backtrace 
 * and either echo or defer the echoing until it's a bit safer.
 *
 *
 * @TODO If it's hidden inside a call to bw_echo() we should be able to cater for this within bw_echo()
 *  
 * @param string $tag - the hook or filter being invoked
 * @param array $args - other parameters to the action hook or filter
 * @return string $tag
 */
function bw_trace_stringwatch( $tag, $args=null ) {
	static $watching = true, $deferred = true;
	if ( $watching || $deferred ) {
		$type = bw_trace_get_hook_type( $tag );
	}
	if ( $watching ) {
		$buffer = ob_get_contents();
		$deferred = $watching;
		$watching = bw_trace_stringwatch_filter( $buffer, $args, $watching, $tag, $type );
		if ( $type == "filter" ) {
			if ( is_string( $tag ) ) {
				//$deferred = $watching;
				$watching = bw_trace_stringwatch_filter( $tag, $args, $watching, $tag, $type );
			} else {
				// Defer watching until it's a scalar
			}
		}
	} elseif ( $deferred ) {
		bw_trace_stringwatch_echo( $type );
	} else {
		$deferred = false;
	}
	return( $tag );	
}

/**
 * Check for the string in a scalar field
 *
 * Note: We can't use esc_html() on the constant since it currently goes recursive 
 *
 * @param string $buffer the haystack that may contain the string
 * @param array $args - may be needed
 * @param string $watching - current value of $watching - last hook
 * @param string $tag - the current hook
 * @param string $type - the type of the current hook 
 */
function bw_trace_stringwatch_filter( $buffer, $args, $watching, $tag, $type ) {
	if ( false !== strpos( $buffer, BW_TRACE_STRINGWATCH ) ) {
		bw_trace2( BW_TRACE_STRINGWATCH, "stringwatch!", true, BW_TRACE_ALWAYS );
		bw_backtrace();
		$hook = bw_trace_get_hook_type( $watching );
		$string = '<div class="stringwatch">';
		$string .= "String watch detected: " .  BW_TRACE_STRINGWATCH . PHP_EOL;
		$string .= "In: $tag $type" . PHP_EOL;
		$string .= "After: $watching $hook" . PHP_EOL;
		$string .= '</div>';
		bw_trace_stringwatch_echo( $type, $string );
		$watching = false;
	} else {
		$watching = $tag;
	}
	return( $watching );
}
 
/**
 * Turn on stringwatching
 *
 * If the BW_TRACE_STRINGWATCH constant is defined then we turn on output buffering
 * and monitor the output buffer for the string on every action or filter hook
 * We might catch the output eventually
 * and that might be enough.
 * 
 */
function bw_trace_stringwatch_on() {
	if ( defined( "BW_TRACE_STRINGWATCH" ) && BW_TRACE_STRINGWATCH !== null ) {
		add_action( "all", "bw_trace_stringwatch", 9999, 2 );
		ob_start( "bw_trace_output_callback" );
		bw_trace2( BW_TRACE_STRINGWATCH, "stringwatch activated", false );
 	}	else {
		bw_trace2( BW_TRACE_STRINGWATCH, "Invalid constant BW_TRACE_STRINGWATCH", false, BW_TRACE_ERROR );
	}
}

/**
 * Turn off string watching
 *
 * Not yet used by oik-bwtrace.
 */
function bw_trace_stringwatch_off() {
  remove_action( "all", "bw_trace_stringwatch", 9999 );
	ob_end_flush();
}

/**
 * output buffer callback routine
 * 
 * We probably can't use bw_trace2() here due to likelihood of getting
 * `
 * Fatal error: print_r(): Cannot use output buffering in output buffering display handlers
 * `
 * 
 * So this function is pretty useless.
 * 
 * @param string $buffer 
 * @param integer $phase
 * @return string the buffer to be output
 */
function bw_trace_output_callback( $buffer, $phase ) {
	//bw_trace2();
	return( $buffer );	
}

/**
 * Defer or echo stringwatch results
 *
 * Processing depends on the value of $type
 * 
 * $type  | processing
 * ------ | -------------
 * action | Append the string to $deferred, echo and null the value
 * filter | Append the string to $deferred
 *
 * @param string $type action|filter
 * @param string $string the HTML to be echo'd
 * @return string current value of $deferred
 */
function bw_trace_stringwatch_echo( $type=null, $string=null ) {
	static $deferred = null;
	if ( $type === "action" ) {
		if ( $string ) {
			$deferred .= $string;
		}
		echo $deferred;
		$deferred = null;
	} else {
		if ( $string ) {
			$deferred .= $string;
		}	
	}
	return( $deferred );
}







 
