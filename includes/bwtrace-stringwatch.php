<?php // (C) Copyright Bobbing Wide 2016

/**
 * String watch
 *
 * Monitor the output for a particular string
 * When it's found write it to the trace output.
 * Only report the first instance.
 * 
 * @TODO We need to start really early - as soon as actions are enabled
 */
bw_trace_stringwatch_loaded();

/**
 * Start stringwatching if required
 *
 * We allow the string to be defined or set in oik action options
 * with the defined value taking precedence, obviously
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
 * Determine the hook type 'action' or 'filter'
 */
if ( !function_exists( "bw_trace_get_hook_type" ) ) {
function bw_trace_get_hook_type( $tag ) {
	global $wp_actions;
  if ( isset( $wp_actions[ $hook ] ) ){
    $type = "action";
  } else {
    $type = "filter";
  }
  return( $type );
}
}

/**
 * 'all' hook looking for the defined string BW_TRACE_STRINGWATCH
 *
 * If found in the output buffer then it will output the buffer,
 * produce a backtrace and ( currently ) crash.
 *
 * If it's a filter then it's a little harder to detect.
 *
 * @TODO If it's hidden inside a call to bw_echo() we should be able to cater for this within bw_echo()
 *  
 * 
 */
function bw_trace_stringwatch( $tag, $args=null ) {
	static $watching = true;
	if ( $watching ) {
		$type = bw_trace_get_hook_type( $tag );
		//if ( $watching ) {
			$buffer = ob_get_contents();
			$watching = bw_trace_stringwatch_filter( $buffer, $args, $watching, $tag, $type );
		//}
		if ( $type == "filter" ) {
			if ( is_string( $tag ) ) {
				$watching = bw_trace_stringwatch_filter( $tag, $args, $watching, $tag, $type );
			} else {
				// Defer watching until it's a scalar
			}
		}
	}
	return( $tag );	
}

/**
 * Check for the string in a scalar field
 *
 * @param string $buffer the haystack that may contain the string
 * @param array $args - may be needed
 * @param string $watching - current value of $watching - last hook
 * @param string $tag - the current hook
 * @param string $type - the type of the current hook 
 */
function bw_trace_stringwatch_filter( $buffer, $args, $watching, $tag, $type ) {
	//if ( is_scalar( $buffer ) ) {
	
	if ( false !== strpos( $buffer, BW_TRACE_STRINGWATCH ) ) {
		bw_trace2( BW_TRACE_STRINGWATCH, "stringwatch!", true, BW_TRACE_ALWAYS );
		bw_backtrace();
			
		$hook = bw_trace_get_hook_type( $watching );
		echo '<div class="stringwatch">';
		echo "Detected: " .BW_TRACE_STRINGWATCH . PHP_EOL;
		echo "In: $tag $type" . PHP_EOL;
		echo "After: $watching $hook" . PHP_EOL;
		echo '</div>';
		$watching = false;
		//gob();
			
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
		gob();
	}
}

/**
 * Turn off stringwatching
 */
function bw_trace_stringwatch_off() {
  remove_action( "all", "bw_trace_stringwatch", 9999 );
	ob_end_flush();
}

/**
 * output buffer callback routine
 * 
 * Probably can't use bw_trace2 here due to likelihood of getting
 * Fatal error: print_r(): Cannot use output buffering in output buffering display handlers
 * 
 */
function bw_trace_output_callback( $buffer, $phase ) {
	//bw_trace2();
	return( $buffer );	
}







 
