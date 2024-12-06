<?php // (C) Copyright Bobbing Wide 2011-2021, 2023, 2024
if ( !defined( 'OIK_BWTRACE_INCLUDES_INCLUDED' ) )  {
define( 'OIK_BWTRACE_INCLUDES_INCLUDED', true );
 
/**
 * Programmatically enable tracing
 * 
 * Note: bw_trace(), bw_trace2() and bw_backtrace() call lazy functions that are not loaded until trace is first turned on.
 * The global variable $bw_trace_on should not be set to true manually.
 * 
 * @param bool $default_options true if default options are to be set
 */
function bw_trace_on( $default_options=false ) {
  global $bw_trace_on, $bw_trace;
	
  $bw_trace_on = TRUE;
	if ( $bw_trace ) {
		$bw_trace->trace_on();
	}
	
  if ( $default_options ) { 
    global $bw_include_trace_count, $bw_include_trace_date, $bw_trace_anonymous, $bw_trace_memory, $bw_trace_post_id, $bw_trace_num_queries;
		global $bw_trace_current_filter;
		global $bw_trace_file_count;
    $bw_include_trace_count = true;
    $bw_include_trace_date = true; 
    $bw_trace_anonymous = false;
    $bw_trace_memory = false; 
    $bw_trace_post_id = false;
    $bw_trace_num_queries = false;
		$bw_trace_current_filter = true;
		$bw_trace_file_count = true;
  }  
}

/** 
 * Programmatically disable tracing
 *
 * Turn tracing off by setting the global $bw_trace_on to false
 */
function bw_trace_off() {
  global $bw_trace_on, $bw_trace;
  $bw_trace_on = FALSE;
	if ( $bw_trace ) {
		$bw_trace->trace_off();
	}
}

/**
 * Initialise hardcoded trace options
 *
 * When trace is invoked during startup processing we can't access the wp_options table so we have to use hardcoded values.
 * 
 * @TODO Use values of trace constants instead?
 * 
 */ 
function bw_trace_inc_init() {   
  global $bw_trace_options;   
  bw_trace_off();
  $bw_trace_options = array( 'file' => "bwtrace.loh", 'trace' => 0, 'reset' => 0,  ); 
  if ( !empty( $_REQUEST['_bw_trace_reset'] ) ) {
    bw_trace_reset();
  }
} 

/**
 * Return the selected locale
 *
 * @param string $category 
 * @return string the locale 
 */ 
function bw_getlocale( $category=LC_ALL ) {
  return setlocale( $category, NULL);
}

/**
 * Return the part of the file to trace
 *
 * $bw_trace_anonymous is true if we're not tracing fully qualified file names.
 * First we try to remove the ABSPATH.. If that fails we assume it's a symlinked file
 *
 * @TODO Decide on whether or not slashes should be backslashes or vice-versa  
 *
 * @param string $file Fully qualified file name
 * @return string selected part of the file name to write  
 */
function bw_trace_file_part( $file ) {
  global $bw_trace_anonymous;
  if ( $bw_trace_anonymous ) {
    $lose = str_replace( "\\", "/", ABSPATH );
		if ( ':' === substr( $file, 1, 1 ) ) { 
			$path = ucfirst( $file ); 
		}	         
    $file = str_replace( "\\", "/", $file );
    $fil = str_replace( $lose , '', $file );
		if ( $fil == $file ) {
		  $fil = bw_trace_anonymize_symlinked_file( $fil );
		}
  } else {
    $fil = $file;
	}		 
  return( $fil );
}

/**
 * Anonymize a symlinked file name
 * 
 * $wp_plugin_paths contains the mapping from the symlinked plugin to the real path for the plugin.
 * We need to convert the real path back to the assumed path so that we can remove the ABSPATH,
 * thereby anonymizing the file name.
 *
 * `
 * $plugin => $real_plugin
 * [C:/apache/htdocs/oikcom/wp-content/plugins/cookie-cat] => c:/apache/htdocs/wordpress/wp-content/plugins/cookie-cat
 * `
 * 
 * @param string $file the real file
 * @return string the anonymized file
 */
function bw_trace_anonymize_symlinked_file( $file ) {
	
  $fil = str_replace( "\\", "/", $file );
	//$fil = strtolower( $fil );
  global $wp_plugin_paths;
	
	if ( is_array( $wp_plugin_paths) && count( $wp_plugin_paths ) ) {
	  foreach ( $wp_plugin_paths as $plugin => $real_plugin ) {
			if ( !$real_plugin ) {
				// That's rather unexpected. But it's not safe to trace here!
				// bw_trace2( $wp_plugin_paths, "Missing real_plugin", true, BW_TRACE_WARNING );
			} else {
				if ( 0 === strpos( $fil, $real_plugin ) ) {
					$fil = str_replace( $real_plugin, $plugin, $fil );
					$lose = str_replace( "\\", "/", ABSPATH );
					$fil = str_replace( $lose , '', $fil );
				 break;
				}
			}
		} 
	}
	return( $fil );			
}			

/**
 * Trace the elapsed time
 * 
 * On the first call the timer_start is set.
 * If timing is not required we return a blank.
 * 
 * @return Elapsed time since timer_start first set. 
 */
function bw_trace_elapsed( ) {
	global $bw_include_trace_date;
	if ( $bw_include_trace_date ) {
		static $timer_start, $timer_latest;
		if ( !isset( $timer_start ) ) {
			$timer_start = $_SERVER['REQUEST_TIME_FLOAT'];//microtime( true );
			$timer_latest = $timer_start;
		} 
		$timer_end = microtime( true );
		$timetotal = $timer_end - $timer_start;
		$elapsed = number_format( $timetotal, 6 ); 
		$latest = number_format( $timer_end - $timer_latest, 6 );
		$timer_latest = $timer_end;
	  return( "$elapsed $latest " );
	} else {
		return( null );
	}	
}

/**
 * Return the date for the trace record
 * 
 * Sometimes, when we want to compare trace output it helps if we eliminate dates from the trace output
 * This function allows that. 
 * $bw_trace_date is supposed to be an option field.
 */
function bw_trace_date( $format=DATE_W3C ) {
  global $bw_include_trace_date;
	$ret = null;
  if ( $bw_include_trace_date ) {
    $ret = date( $format );
    $ret .= ' ';
  }       
  return( $ret ) ;
}

/**
 * Return the trace record count if required
 * 
 * Sometimes, when we want to compare trace output it helps if we eliminate the trace counter from the trace output
 *
 * @return string trace record count, if required
 */
function bw_trace_count( $count ) {
	global $bw_include_trace_count;
	$ret = null;      
	if ( $bw_include_trace_count ) {
		$ret = $count;
		$ret .= " ";
	}
	return( $ret ) ;
}

/**
 * Return the function invocation count
 *
 *
 * @param string $function 
 * @erturn string in format 'function(count)' 
 */
function bw_trace_function( $function ) {
  global $bw_trace_functions;
	//$c = $bw_trace_functions[$function];
  //$bw_trace_functions[$function]++;
	//$d = $bw_trace_functions[$function];
  $ret = $function;
  $ret .= "(".$bw_trace_functions[$function].")";
	$ret .= " ";
  return( $ret );
}

/**
 * Return the current filter summary
 * 
 * Even if current_filter exists the global $wp_current_filter may not be set
 * 
 * @return string current filter array imploded with commas
 */
function bw_current_filter() {
  global $wp_current_filter;
  if ( is_array( $wp_current_filter ) ) { 
	  $filters = implode( ",",  $wp_current_filter );
	} else {
	  $filters = null;
	}		
  return( $filters );  
}

/**
 * Return the number of database queries performed so far
 *
 * @return string Number of queries performed, if required
 */
function bw_get_num_queries() {
	global $bw_trace_num_queries;
	$num_queries = null;
	if ( $bw_trace_num_queries ) {
		global $wpdb;
		if ( $wpdb ) {
			$num_queries = $wpdb->num_queries;
		} else {
		$num_queries = "0";   
		} 
		$num_queries .= " ";
	}  
	return( $num_queries ); 
}

/**
 * Return the global post ID and, if different global id, for tracing
 *
 * @return string $post->ID and, if different "<>"$id
 * @global post post
 * @global post_id id
 */
function bw_get_post_id() {
	$post_id = 0;
	$id = 0;
  if ( isset( $GLOBALS['post'] )) {
	  if ( !is_wp_error( $GLOBALS['post']->ID  ) ){
	    $post_id=$GLOBALS['post']->ID;
      } else {
		  print_r( $GLOBALS['post']);
	  }
  } else {
    $post_id = 0;
  }
  if ( isset( $GLOBALS['id'] ) ) {
	  if ( !is_wp_error( $GLOBALS['id'] ) ) {
		  $id=$GLOBALS['id'];
	  }
  }
  if ( $id <> $post_id ) {
			if ( is_scalar( $id ) ) {
				$post_id .= "<>" . $id; 
			}

  }
  return( $post_id ) ;
}

/**
 * Trace the post id, if required
 *
 * @return string post ID, if required
 */
function bw_trace_post_id() {
	global $bw_trace_post_id;
	$id = null;
	if ( $bw_trace_post_id ) {
		$id .= bw_get_post_id();
		$id .= " ";
	}
	return( $id );
}

/**
 * Trace the current memory/peak usage, if required
 * 
 * Now traces real memory usages, not just that allocated by emalloc()
 * 
 * Optionally, trace the current value of the memory_limit
 * 
 */
function bw_get_memory_usage() {
	global $bw_trace_memory;
	$memory = null;
	if ( $bw_trace_memory ) {
		$memory .= memory_get_usage( true ); 
		$peak = memory_get_peak_usage( true );
		$memory .= "/$peak";
		$memory .= " ";
		$memory .= ini_get( "memory_limit" );
		$memory .= " "; 
	}
	return( $memory );
}  

/**
 * Trace bwechos if required
 *
 * Produces nesting level, number of bw_echos() performed and current strlen
 * followed by the current contents of $bwecho
 *
 * e.g.
 * `
 * @#:0 241 27<!--PHP version:5.5.18 -->
 * `
 */
function bw_trace_bwechos() {
  global $bwechos, $bwecho, $bwecho_array; 
  static $saved = 0;
  if ( $saved != $bwechos ) {
    $ret = "@#:";
		if ( is_array( $bwecho_array ) ) {
			$ret .= count( $bwecho_array); 
		} else {
			$ret .= "0";
		}
    $ret .= " $bwechos ";
	$ret .= (null === $bwecho ) ? 0 : strlen( $bwecho );
    $ret .= $bwecho;  
    $saved = $bwechos; 
  } else {
    $ret = null;
  }   
  return( $ret );
}  

/** 
 * Trace contextual information set using bw_set_context 
 *
 * If the context is "act" then we trace the number of times this has been processed
 *
 * @return string context - including the current filter tree
 */
function bw_trace_context() {	
	global $bw_trace_current_filter;
	$context = null;
	if ( $bw_trace_current_filter ) {
		$context = bw_current_filter(); 
		if ( $context ) {
			$context = "cf=" . $context;
		} else {
			$context = "cf!";
		}
		$context .= " ";
	}
   
	global $bw_context;
	if ( is_array( $bw_context ) ) {
		foreach ( $bw_context as $key => $value ) {
			$context .= ",$key=$value";     
			if ( $key == "act" ) {
				global $wp_actions;
				$context .= "(".$wp_actions[$value].")";
			}  
		}
	} 
	return( $context );
} 

/**
 * Set some contextual information for tracing
 *
 * @param string $key
 * @param mixed $value
 *
 */
function bw_set_context( $key, $value=NULL ) {
	global $bw_context;
	if ( !isset( $bw_context ) ) { 
		$bw_context = array();
	}
	$bw_context[$key] = $value;
} 

/**
 * Trace all contextual information
 *
 * @TODO Check if function still necessary
 *
 */
function bw_trace_context_all( $function=NULL, $line=NULL, $file=NULL ) {	
  global $wp_filter, $wp_actions, $merged_filters, $wp_current_filter;
  // bw_trace( $wp_filter, $function, $line, $file, "wp_filter" );
  //bw_trace( $wp_actions, $function, $line, $file, "wp_actions" );
  //bw_trace( $merged_filters, $function, $line, $file, "merged_filters" );
  bw_trace( $wp_current_filter, $function, $line, $file, "current_filter" );
}

/**
 * Return the files loaded count
 *
 */
function bw_trace_file_count() {
  global $bw_trace_file_count;
	$filecount = null;
	if ( $bw_trace_file_count ) {
   $filecount .= "F=";
	 $filecount .= count( get_included_files() );
   $filecount .= " ";
	} 
	return( $filecount );
}

/**
 * Trace the locale(s).
 *
 * Returns a string showing the current locale and determined_locale.
 * Note: the period helps you know if the locale was set before the determined locale.
 *
 * $locale | $determined_locale | returned value
 * -------- | ------------- | -----------
 * null | null | ' '
 * null | cy_LA | '.cy_LA '
 * cy_LA | null | 'cy_LA '
 * cy_LA | cy_LA | 'cy_LA '
 * cy_LA | di_FF | 'cy_LA.di_FF '
 *
 * @return string
 */
function bw_trace_locale() {
	global $locale;
	$determined_locale = bw_trace_determine_locale();
	if ( $locale != $determined_locale) {
		return $locale . '.' . $determined_locale . ' ' ;
	}
	return $locale . ' ';
}

/**
 *
 * Link https://www.php.net/manual/en/function.ini-get.php
 * @param $val
 *
 * @return int|string
 */
function bw_trace_return_bytes($val) {
	$val = trim($val);
	$last = strtolower($val[strlen($val)-1]);
	$val = (int) $val;
	switch($last) {
		// The 'G' modifier is available since PHP 5.1.0
		case 'g':
			$val *= 1024;
		case 'm':
			$val *= 1024;
		case 'k':
			$val *= 1024;
	}
	return $val;
}

function bw_trace_memory_limit_bytes( $update=false ) {
	static $memory_limit_bytes = null;
	if ( null === $memory_limit_bytes || $update ) {
		$memory_limit = ini_get( "memory_limit" );
		$memory_limit_bytes = bw_trace_return_bytes( $memory_limit );
	}
	return $memory_limit_bytes;
}

/**
 * safe print_r ?
 * 
 * Attempt to protect from a crash in print_r() when output buffering is active
 *
 * Note: ob_get_level() may return 1 when output buffering is not really nested.
 * @param mixed $text
 * @return string formatted output
 */
function bw_trace_print_r( $text ) {
	$handlers = ob_list_handlers();
	$output = null;
	if ( count( $handlers ) > 1 ) {
		$output = implode( ",", $handlers );
		$output .= "\n";
	}
	if ( count( $handlers ) > 1 ) {
		$output .= bw_trace_obsafe_print_r( $text );
	} else {
		// Sometimes we can run out of memory.
		// can we check current memory limit?
		$memusage = memory_get_usage( true );
		$peak_before = memory_get_peak_usage( true );
		$memory_limit = bw_trace_memory_limit_bytes();
		if ( ( $memory_limit - $memusage ) < 50000000 ) {
			bw_log( $memusage, $_SERVER['REQUEST_URI'] . ": Possible memory issues < 50MB free", false );
		}

		//$output .= bw_trace_obsafe_print_r( $text );
		$print_red =null;
		//$print_red =print_r( $text, true );
		$print_red .= bw_trace_obsafe_print_r( $text );
		$peak_after=memory_get_peak_usage( true );
		//$output    .=$memusage;
		$len = strlen( $print_red );
		if (  $len < 2097152 ) {
			$output.=$print_red;
		} else {
			$text = ": Too much data to trace: $len $memusage $peak_before $peak_after $memory_limit";
			bw_log( $len, $_SERVER['REQUEST_URI'] . $text, false  );
			$output.= $text;
			$output .= substr( $print_red, 0, 2097152 );
			//bw_backtrace();
		}

	}
	return $output;
}

/**
 * Output buffering safe print_r() 
 * 
 * Activate this plugin when you get the following message from oik-bwtrace
 * 
 * Fatal error: print_r(): Cannot use output buffering in output buffering display handlers in 
 * plugins\oik-bwtrace\includes\bwtrace.inc on line 253 (or thereabouts )
 *
 * The source of this function was @link http://grokbase.com/t/php/php-notes/1219akmjd7/note-107120-added-to-function-debug-print-backtrace
 * 
 * Explanation of the print_r() function available at http://us.php.net/manual/en/function.print-r.php .
 *
 * @param mixed $var - expression to be printed
 * @param integer $level - the nesting level - used for pretty formatting
 * @param array $visitedVars - to cater for recursive structures
 * @return string print_r() like output IF $return is true 
 */
function bw_trace_obsafe_print_r( $var, $level=0, &$visitedVars = array()) {

	if ( $level > 10 ) {
		//return( 'Max Level Reached');
	}
	$spaces = "";
	$space = " ";
	$newline = "\n";
	for ($i = 1; $i <= 4; $i++) {
		$spaces .= $space;
	}
	$tabs = $spaces;
	for ($i = 1; $i <= $level; $i++) {
		$tabs .= $spaces;
	}
	
	if ( is_array( $var ) ) {
		$title = "Array";
	} elseif ( is_object( $var ) ) {
		$title = get_class( $var )." Object";
		$var = (array) $var;
	} else {
		$title = null;
	}
	if ( $title ) {
		$output = $title . $newline . $newline;
		foreach ($var as $key => $value) {
		
			if (is_array( $value ) || is_object( $value) ) {
				if ( is_array( $value ) && 0 == count( $value ) ) {
						$value = "Array";
				}	elseif ( is_object( $value ) && $value instanceof Closure ) {
					 $value = 'Closure';
				} else {
					try {
						$md5_serialize = md5( serialize( $value ) );
					}
					catch	( Exception $e ) {
						$md5_serialize = "Nested closure? $key";
					}
					if ( isset( $visitedVars[ $md5_serialize ] ) ) {
						$value = "*RECURSION* " . $visitedVars[ $md5_serialize] ;
					} else {
						$visitedVars[ $md5_serialize ] = "$key $level";
						$level++;
						$value = bw_trace_obsafe_print_r( $value, $level, $visitedVars);
						$level--;
					}
				}		
			} else {
				$value = '('.gettype($value).') '.(is_string($value) ? '"' : '').$value.(is_string($value) ? '"' : '');
			}
			$key = str_replace( chr(0), " ", $key );
			$output .= $tabs . "[" . $key . "] => " . $value . $newline;
		}
	} else {
		$output = $var;
	}
	return $output;
}


/**
 * Increment a value in an array
 *
 * @param array $array - the array to change
 * @param string $index - the key of the value to increment
 *
 */
function bw_array_inc( &$array, $index ) {
	if ( !isset($array) ) {
		$array = array();
	}
	if ( !isset( $array[$index] ) ) {
		$array[$index] = 1;
	} else {
		++$array[$index];
	}
}

/**
 * Implement bw_trace() to write a record to the trace log file
 *
 * @param string $text field value
 * @param string $function the current function name
 * @param string $lineno the current line number
 * @param string $file the current file
 * @param string $text_label a label for the string
 */
function bw_lazy_trace( $text, $function=__FUNCTION__, $lineno=__LINE__, $file=__FILE__, $text_label=NULL, $level=BW_TRACE_ALWAYS ) {
  global $bw_trace_on, $bw_trace;
	if ( $bw_trace_on && $bw_trace ) {
		$bw_trace->lazy_trace( $text, $function, $lineno, $file, $text_label, $level );
	}
}

/**
 * Return the defined trace file name
 *
 * @param array $bw_trace_options 
 * @param bool $ajax - true when DOING_AJAX
 * @return string filename - default bwtrace.loh
 */
function bw_trace_file_name( $bw_trace_options, $ajax=false ) {
	$file = null;
	
	//if ( defined('REST_REQUEST') && REST_REQUEST ) {
	//	$file = "bwtrace.rest";
	//	return $file;
	//}
	
	if ( $ajax ) {
		$file = bw_array_get( $bw_trace_options, 'file_ajax', null ); 
		$file = trim( $file );
	}
	if ( !$file ) {
		$file = bw_array_get( $bw_trace_options, 'file', null );
		$file = trim( $file );
	}
	if ( !$file ) {
		$file = 'bwtrace.loh';
	}
	//gob();
	global $bw_trace;
	$file = $bw_trace->get_trace_file_name();
	return( $file );
}	 
  
  

/**
 * Return the name of the trace file
 * 
 * The trace file is expected to be in ABSPATH with a default file name of bwtrace.loh
 *
 * @return string fully qualified trace file name
 */ 
function bw_trace_file() { 
	static $bw_trace_file = null;
	if ( !$bw_trace_file ) {
		global $bw_trace_options;
		
		if ( !defined('ABSPATH') ) {
			$abspath = dirname( dirname( dirname ( dirname( dirname( __FILE__ ))))) . '/';
			$abspath = str_replace( "\\", "/", $abspath );
			if ( ':' === substr( $abspath, 1, 1 ) ) {
				$abspath = ucfirst( $abspath );
			}
			$file = $abspath;
		} else { 
			$file = ABSPATH;
		}
		$ajax = defined( 'DOING_AJAX' ) && DOING_AJAX ;
		$bw_trace_file = bw_trace_file_name( $bw_trace_options, $ajax );
		$bw_trace_file = $file . $bw_trace_file;
		
	}
  return( $bw_trace_file );
}


/** 
 * Returns the trace file name supporting trace file generations 
 * 
 * To better support multiple trace requests during editing, 
 * where the new editor performs a variety of calls: normal, AJAX and REST
 * 
 * @return Name of the trace file to use
 */
function bw_trace_file2() {
	static $bw_trace_file2 = null;
	global $bw_trace;
	if ( $bw_trace ) {
		$bw_trace_file2 = $bw_trace->get_trace_file_name();
	}
	/*
	if ( null === $bw_trace_file2 ) {
		global $bw_trace_options;
		oik_require( "includes/class-trace-file-selector.php", "oik-bwtrace" );
		$trace_file_selector = new trace_file_selector();
		$trace_file_selector->set_trace_options( $bw_trace_options );
		$bw_trace_file2 = $trace_file_selector->get_trace_file_name();
	}
	*/
	return $bw_trace_file2;

}

/**
 * Set options for tracing in batch mode
 *
 * In this context batch mode means we're not connected to a database
 * so cannot obtain the trace options from the options table.
 * 
 */
function bw_trace_batch() {   
  global $bw_trace_options;   
  $bw_trace_options = array( 'file' => "bwtrace.loh",  ); 
  $bw_trace_options['file'] = "bwtrace.log";
  //bw_trace_on();
  //bw_trace_errors( 3 );
  bw_trace( $_GET, __FUNCTION__, __LINE__, __FILE__, "_GET" );
}

/**
 * Log a record to a trace file
 *
 * @param string $line - this can be a very long string
 *
 */
function bw_trace_log( $line ) {
	global $bw_trace;
	if ( $bw_trace->BW_trace_record ) {
		$bw_trace->BW_trace_record->trace_log( $line );
	} else {
		// We can't do anything if BW_trace_record is not set!
	}
}  

/**
 * Writes the trace line to the file
 *
 * if we can't open the file turn tracing off
 * 
 *  This is the sort of message we sometimes get.
 *  Not sure what's causing the error. It could be tailing the file.
   
`  
Warning: fopen(C:\apache\htdocs\wordpress/bwtrace.log): 
failed to open stream: 
Permission denied in C:\apache\htdocs\wordpress\wp-content\plugins\oik\bwtrace.inc on line 148 
Call Stack: 
0.0018 467368 1. {main}() C:\apache\htdocs\wordpress\wp-admin\options-general.php:0 
0.0033 561816 2. require_once('C:\apache\htdocs\wordpress\wp-admin\admin.php') C:\apache\htdocs\wordpress\wp-admin\options-general.php:10 
0.0040 578248 3. require_once('C:\apache\htdocs\wordpress\wp-load.php') C:\apache\htdocs\wordpress\wp-admin\admin.php:30 
0.0047 595656 4. require_once('C:\apache\htdocs\wordpress\wp-config.php') C:\apache\htdocs\wordpress\wp-load.php:29 
0.0059 696120 5. require_once('C:\apache\htdocs\wordpress\wp-settings.php') C:\apache\htdocs\wordpress\wp-config.php:117 
0.2378 16843976 6. include_once('C:\apache\htdocs\wordpress\wp-content\plugins\oik\oik-bwtrace.php') C:\apache\htdocs\wordpress\wp-settings.php:192 
0.2428 17183640 7. bw_trace_plugin_startup() C:\apache\htdocs\wordpress\wp-content\plugins\oik\oik-bwtrace.php:59 
0.2431 17187240 8. bw_trace() C:\apache\htdocs\wordpress\wp-content\plugins\oik\oik-bwtrace.php:102 
0.2433 17187680 9. bw_trace_log() C:\apache\htdocs\wordpress\wp-content\plugins\oik\bwtrace.inc:93 
0.2433 17187760 10. bw_write() C:\apache\htdocs\wordpress\wp-content\plugins\oik\bwtrace.inc:129 
0.2433 17187840 11. fopen() C:\apache\htdocs\wordpress\wp-content\plugins\oik\bwtrace.inc:148 ? 
`

* @TODO The unwritten logic needs to take into account the file we're trying to write to!
* 
* Also.. failed to open stream: Resource temporarily unavailable 

*/
function bw_write( $file, $line ) {
	static $unwritten = array();
	if ( !$file ) {
		return( 0 );
	}
	$handle = fopen( $file, "a" );
	if ( $handle === FALSE ) {
		bw_trace_off();
		// It would be nice to let them know... 
		$ret = "fopen failed";
		if ( isset( $unwritten[ $file ] ) ) {
			$unwritten[ $file ] .= $line;
		} else {  
			$unwritten[$file] = $line;
		}
	} else {
		if ( isset( $unwritten[ $file ] ) ) {
			$bytes = fwrite( $handle, "bw_write unwritten" );
			$bytes = fwrite( $handle, $unwritten[ $file ] );
			unset( $unwritten[ $file ] );
		}
		$bytes = fwrite( $handle, $line );
		$ret = fclose( $handle );
		$ret .= " $bytes $file $line";
	}
	return( $ret );
} 

/**
 * Perform a trace reset
 *
 * If the trace file exists and is writable then we can attempt to unlink it.
 * We precede the call to unlink with an @ to attempt to avoid getting warning messages.
 * 
 * This file may not exist so we have two choices. 1. precede with an @, 2. test for it
 * but if $file is not set then we should test
 
 * Note: We shouldn't be doing this if we're not tracing the specific IP.
 * 
 */
function bw_trace_reset() {
	static $reset_done = false; 
	if ( ! $reset_done ) {
		$file = bw_trace_file2();
		if ( is_file($file) ) {
			if ( is_writable( $file ) ) {
				@unlink( $file );
			} else {
				// We can't unlink the file at the moment - never mind eh?
			} 
		}
	} 
	$reset_done = true;
} 

/**
 * 
 */
function bw_trace_errors( $level ) {
  error_reporting( $level );
  @ini_set('display_errors', 1);
}


/** 
 * Return the array[index] or array->index (for an object) or a default value if not set
 *
 * Note: This code is slightly more efficient with the default being assigned in the else
 * than when there is just one assignment of $value = $default right at the start
 * where slightly more is 2 or 3 microseconds - measured on a laptop.
 */
 
if ( !function_exists( 'bw_array_get' ) ) { 
  function bw_array_get( $array, $index, $default=NULL ) {
    if ( isset( $array ) ) {
      if ( is_array( $array ) ) {
        if ( isset( $array[$index] ) || array_key_exists( $index, $array ) ) {
          $value = $array[$index];
        } else {
          $value = $default;
        }  
      } elseif ( is_object( $array ) ) {
        if ( property_exists( $array, $index ) ) {
          $value = $array->$index;
        } else {
          $value = $default;
        } 
      } else {
        $value = $default;
      }  
    } else {
      $value = $default;
    }  
    return( $value );
  }
}


/** 
 * print a backtrace to help find out where something is called from and how to debug it
 *
 * The output from debug_backtrace() is an array - from 0 to n of the calls
 * 
 * - [file] is the file name
 * - [line] is the line number
 * - [function] is the method used to get the file: include, require_once
 * - [args] are parameters
 * - [class]
 * - [object]
 * - [type] -> = method call,  :: = static method call, nothing for function call
 * 
 * `
 C:\apache\htdocs\wordpress\wp-content\themes\hsoh0922bp\functions.php(12:0) 2011-09-27T16:22:49+00:00   backtrace Array
(
    [0] => Array
        (
            [file] => C:\apache\htdocs\wordpress\wp-settings.php
            [line] => 280
            [function] => include
        )

    [1] => Array
        (
            [file] => C:\apache\htdocs\wordpress\wp-config.php
            [line] => 130
            [args] => Array
                (
                    [0] => C:\apache\htdocs\wordpress\wp-settings.php
                )

            [function] => require_once
        )

    [2] => Array
        (
            [file] => C:\apache\htdocs\wordpress\wp-load.php
            [line] => 29
            [args] => Array
                (
                    [0] => C:\apache\htdocs\wordpress\wp-config.php
                )

            [function] => require_once
        )

    [3] => Array
        (
            [file] => C:\apache\htdocs\wordpress\wp-blog-header.php
            [line] => 12
            [args] => Array
                (
                    [0] => C:\apache\htdocs\wordpress\wp-load.php
                )

            [function] => require_once
        )

    [4] => Array
        (
            [file] => C:\apache\htdocs\wordpress\index.php
            [line] => 17
            [args] => Array
                (
                    [0] => C:\apache\htdocs\wordpress\wp-blog-header.php
                )

            [function] => require
        )

)
 ` 
*/
function bw_lazy_backtrace() {
  global $bw_trace_on;
  if ($bw_trace_on) {
    $backtrace = debug_backtrace();
    //bw_trace( $backtrace, __FUNCTION__, __LINE__, __FILE__, "backtrace" );
    $file = "";
    foreach ( $backtrace as $i => $call ) {
      $function = $call['function'];
      $file = bw_array_get( $call, 'file', $file ) ;
      $file = bw_trace_file_part( $file );
      $line = bw_array_get( $call, 'line', 0 );
      $args = bw_array_get( $call, 'args', array() );
      $cargs = count( $args );
      switch ( $cargs ) {
        case 0: 
          $targs = NULL;
          break;
        //case 1:
          //$targs = $args[0]; 
          //if ( strpos( " require require_once include include_once ", $function ) ) {
            $function .= "(".$targs.")";
          //}  
          //break;
        default:
          $targs = $args;
          $sep = '(';
          foreach ( $targs as $targ ) {
            if ( is_object( $targ ) ) {
              $function .= $sep."object";
            } elseif ( is_array( $targ ) ) {
              $function .= $sep."array";
            } elseif ( is_scalar( $targ ) ) {
	            $function.=$sep . $targ;
            } elseif ( is_null( $targ) ) {
				$function.=$sep.'null';
			} else {
				$function .= $sep."unsupported" ;
            }  
            $sep = ',';
          }
          $function .= ')';  
      }
      // This produces far too much      
      //bw_trace( $targs, $function, $line, $file, $i );
      // this is not much better
      //bw_trace( $i, $function, $line, $file, "backtrace" );
      
      /*
0.0018 467368 1. {main}() C:\apache\htdocs\wordpress\wp-admin\options-general.php:0 
0.0033 561816 2. require_once('C:\apache\htdocs\wordpress\wp-admin\admin.php') C:\apache\htdocs\wordpress\wp-admin\options-general.php:10 
0.0040 578248 3. require_once('C:\apache\htdocs\wordpress\wp-load.php') C:\apache\htdocs\wordpress\wp-admin\admin.php:30 
      */ 
      $line = "$i. $function $file:$line $cargs\n";
      bw_trace_log( $line ); 
    }
  }
} 

 
/**
 * Improved trace function that needs no parameters, but accepts two
 *
 * Using debug_backtrace this function can be used to trace the parameters to a function
 * It's a version of bw_backtrace that doesn't produce the whole call tree
 * It's less efficient than bw_lazy_trace since it first needs to call debug_backtrace()
 * bw_backtrace should also perform the checks.
 *
 * @param mixed $value - an optional field to be traced
 * @param string $text - an optional field identifying text for the field to be traced
 * @param string $show_args - true to display the arguments to the call  
 * @param integer $level - trace level, optional **?**
 * @return mixed $value - to allow this function to be called in return statements 
 * 
 *
 */
function bw_lazy_trace2( $value=null, $text=null, $show_args=true, $level=null ) {
  global $bw_trace_on;
  if ($bw_trace_on) {
		//bw_trace_check_level( $level );
    $backtrace = debug_backtrace();
    //bw_lazy_trace( $backtrace, __FUNCTION__, __LINE__, __FILE__, "backtrace" );
    $call = $backtrace[0];
    $call1 = $backtrace[1];
    $file = bw_array_get( $call1, 'file', NULL) ;
    $file = bw_trace_file_part( $file );
    $line = bw_array_get( $call1, 'line', 0 );
    if ( isset( $backtrace[2] ) ) {
      $call2 = $backtrace[2]; 
      $function = $call2['function'];
			if ( isset( $call2['class'] ) ) {
				$function = $call2['class'] . '::' . $function;
			}
      if ( $show_args ) {      
        $args = $call2['args'];
        $cargs = count( $args );
        switch ( $cargs ) {
          case 0: 
            $targs = NULL;
            break;
          case 1:
            $targs = $args[0]; 
            break;
          default:
            $targs = $args;
        }
        bw_lazy_trace( $targs, $function, $line, $file, $cargs, $level );
      }  
    } else { 
      $function = "";
    }
           
    if ( $value || $text ) {
      bw_lazy_trace( $value, $function, $line, $file, $text, $level );
		}			
    //if ( $show_args )  
    //  bw_trace_context_all( $function, $line, $file );  
  } 
  //bw_bang(); 
  return( $value );
}

// Moved bw_lazy_trace_config_startup() to includes/bwtrace-config.php


/**
 * Return the possible trace levels
 *
 * Recommended level is "Information level"
 * as this will include Notice, Warning and Error level trace records as well
 *
 * @return array Trace levels 
 */
function bw_list_trace_levels( $translate=true ) {
	if ( $translate ) {
	$levels = array( BW_TRACE_DEBUG => __( "Debug level", "oik-bwtrace" )
								 , BW_TRACE_INFO => __( "Information level &ndash; standard", "oik-bwtrace" )
								 , BW_TRACE_NOTICE => __( "Notice level", "oik-bwtrace" )
								 , BW_TRACE_WARNING => __( "Warning level" , "oik-bwtrace" )
								 , BW_TRACE_ERROR => __( "Error level", "oik-bwtrace" )
								 , BW_TRACE_VERBOSE => __( "Verbose level &ndash; noisier than Debug", "oik-bwtrace" )
								 );
	} else {
		$levels = array( BW_TRACE_DEBUG => "Debug level"
		, BW_TRACE_INFO => "Information level &ndash; standard"
		, BW_TRACE_NOTICE => "Notice level"
		, BW_TRACE_WARNING => "Warning level"
		, BW_TRACE_ERROR => "Error level"
		, BW_TRACE_VERBOSE => "Verbose level &ndash; noisier than Debug"
		);
	}
	return( $levels );
}

/**
 * Trace the trace startup
 *
 * Notes: 
 * - The merging of $_GET and $_POST data into $_REQUEST depends on php.ini settings. @link http://php.net/request-order
 * - wp_magic_quotes() is normally called after this function has been run, since it's invoked after plugins have been loaded.
 * - wp_magic_quotes() adds the 'sometimes unwanted' backslashes to $_GET, $_POST, $_COOKIE and $_SERVER.
 * - wp_magic_quotes() also remerges $_GET and $_POST into $_REQUEST.
 * - Other plugins and themes can fiddle with these superglobals.
 */
function bw_trace_trace_startup() {
	global $bw_trace;
	$bw_trace->trace_startup();
}

/**
 * Returns a hexdump version of the string
 *
 * @param string $string
 * @return string
 */
function bw_trace_hexdump( $string ) {
	if ( !function_exists( "oik_hexdump") ) {
		oik_require_lib( 'hexdump' );
	}
	if ( function_exists( "oik_hexdump" ) ) {
		$string = oik_hexdump( $string );
	}
	return $string;

}
   
   
}
