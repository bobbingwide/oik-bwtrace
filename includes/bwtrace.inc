<?php // (C) Copyright Bobbing Wide 2011-2014

if ( !defined( 'OIK_BWTRACE_INCLUDES_INCLUDED' ) )  {
define( 'OIK_BWTRACE_INCLUDES_INCLUDED', true );
 
/**
 * Programmatically enable tracing
 * Note: bw_trace() and bw_trace2() are lazy functions that are not loaded until trace is first turned on.
 * **?** This code must be in the wrong place! since it tries to include itself. 
 * We'll leave it like this until we determine where this API should go **?**
 */
function bw_trace_on( $default_options=false ) {
  global $bw_trace_on;
  oik_require2( "includes/bwtrace.inc", "oik-bwtrace" );
  $bw_trace_on = TRUE;
  if ( $default_options ) { 
    global $bw_include_trace_count, $bw_include_trace_date, $bw_trace_anonymous, $bw_trace_memory, $bw_trace_post_id, $bw_trace_num_queries;
    $bw_include_trace_count = true;
    $bw_include_trace_date = true; 
    $bw_trace_anonymous = false;
    $bw_trace_memory = false; 
    $bw_trace_post_id = false;
    $bw_trace_num_queries = false;
  }  
}

/** 
 * Programmatically disable tracing
 */
function bw_trace_off() {
  global $bw_trace_on;
  $bw_trace_on = FALSE;
} 

function bw_trace_inc_init() {   
  global $bw_trace_options;   
  bw_trace_off();
  $bw_trace_options = array( 'file' => "bwtrace.loh", 'trace' => 0, 'reset' => 0,  ); 
  if ( !empty( $_REQUEST['_bw_trace_reset'] ) ) {
    bw_trace_reset();
  }  
}  

function bw_getlocale($category=LC_ALL) {
  return setlocale($category, NULL);
}

function bw_trace_file_part( $file ) {
  global $bw_trace_anonymous;
  
  if ( $bw_trace_anonymous ) {
    $lose = str_replace( "/", "\\", ABSPATH );
    $file = str_replace( "/", "\\", $file );
    $fil = str_replace( $lose , '', $file );
  } 
  else
    $fil = $file; 
  return( $fil );
}

/**
 * Trace the elapsed time
 * 
 * On the first call the timer_start is set.
 * 
 * @return Elapsed time since timer_start first set. 
 */
function bw_trace_elapsed( ) {
  static $timer_start, $timer_latest;
  if ( !isset( $timer_start ) ) {
    $timer_start = microtime( true );
    $timer_latest = $timer_start;
  } 
  $timer_end = microtime( true );
  $timetotal = $timer_end - $timer_start;
  $elapsed = number_format( $timetotal, 6 ); 
  $latest = number_format( $timer_end - $timer_latest, 6 );
  $timer_latest = $timer_end;
  return( "$elapsed $latest" );
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
  
  if ( $bw_include_trace_date ) {
    $ret = date( $format );
    //$dt = new DateTime();
    //$ret = $dt->format( "Y-m-d h:m:s.u" );
  } else {
    $ret = '';
  }        
  return( $ret ) ;
}


/**
 * Return the trace record count if required
 * 
 * Sometimes, when we want to compare trace output it helps if we eliminate the trace counter from the trace output
 */
function bw_trace_count( $count ) {
  global $bw_include_trace_count;
  
 if ( $bw_include_trace_count )
    $ret = $count;
  else
    $ret = '';      
  return( $ret ) ;
}

function bw_trace_function( $function ) {
  global $bw_trace_functions;
  $bw_trace_functions[$function]++;
  $ret = $function;
  $ret .= "(".$bw_trace_functions[$function].")";
  return( $ret );
}

/**
 * Even if current_filter exists the global $wp_current_filter may not be set
 * which could cause end() to produce a warning.
 * So this is a workaround for the problem. 
 */
function bw_current_filter() {
  global $wp_current_filter;
  if ( is_array( $wp_current_filter ) ) 
    return end( $wp_current_filter );
  return( null );  
}

/**
 * Return the number of database queries performed so far
 */
function bw_get_num_queries() {
  global $bw_trace_num_queries;
  if ( $bw_trace_num_queries ) {
    global $wpdb;
    if ( $wpdb ) {
      $num_queries = " " . $wpdb->num_queries;
    } else {
      $num_queries = " 0";   
    } 
  } else {
    $num_queries = null;
  }  
  return( $num_queries ); 
}

/**
 * Return the global post_id and, if different global id, for tracing
 *
 * @return string $post->ID and, if different "<>"$id
 * @global post post
 * @global post_id id
 */
function bw_get_post_id() {
  if ( isset( $GLOBALS['post'] )) {
    $post_id = $GLOBALS['post']->ID;
  } else {
    $post_id = 0;
  }
  if ( isset( $GLOBALS['id'] ) ) {
    $id = $GLOBALS['id'];
    if ( $id <> $post_id ) { 
      $post_id .= "<>" . $id; 
    }
  }
  return( $post_id ) ;
}

/**
 * Trace the post id, if required
 */
function bw_trace_post_id() {
  global $bw_trace_post_id;
  if ( $bw_trace_post_id ) {
    $id = " ";
    $id .= bw_get_post_id();
  } else { 
    $id = null;
  }
  return( $id );
}

/**
 * Trace the current memory/peak usage, if require
 * 
 */
function bw_get_memory_usage() {
  global $bw_trace_memory;
  if ( $bw_trace_memory ) {
    $memory = " "; 
    $memory .= memory_get_usage(); 
    $peak = memory_get_peak_usage();
    $memory .= "/$peak";
  } else {
    $memory = null;
  }  
  return( $memory );
}  

/**
 * Trace bwechos if required
 *
 * Produces nesting level, number of bw_echos() performed and current strlen
 */
function bw_trace_bwechos() {
  global $bwechos, $bwecho, $bwecho_array; 
  static $saved = 0;
  if ( $saved != $bwechos ) {
    $ret = "@#:";
    $ret .= count( $bwecho_array); 
    $ret .= " $bwechos ";
    $ret .= strlen( $bwecho ); 
    $ret .= $bwecho;  
    $saved = $bwechos; 
  } else {
    $ret = null;
  }   
  return( $ret );
}  
 

/** 
 * Trace contextal information set using bw_set_context 
 *
 * If the context is "act" then we trace the number of times this has been processed
 */
function bw_trace_context() {	
  global $bw_context;
  $context = bw_current_filter(); 
  if ( $context ) {
    $context = "cf=" . $context;
  } else {
    $context = "cf!";
  }
    
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

function bw_set_context( $key, $value=NULL ) {
  global $bw_context;
  if (!isset( $bw_context )) 
    $bw_context = array();
  $bw_context[$key] = $value;
} 

function bw_trace_context_all( $function=NULL, $line=NULL, $file=NULL ) {	
  global $wp_filter, $wp_actions, $merged_filters, $wp_current_filter;
  // bw_trace( $wp_filter, $function, $line, $file, "wp_filter" );
  //bw_trace( $wp_actions, $function, $line, $file, "wp_actions" );
  //bw_trace( $merged_filters, $function, $line, $file, "merged_filters" );
  bw_trace( $wp_current_filter, $function, $line, $file, "current_filter" );
}


/* Format required for the trace record is something like this
 
  C:\apache\htdocs\wordpress\wp-content\plugins\bwtrace\bwtrace.php(116:0)  1 2011-01-05T22:43:13+00:00

  but this can be altered by the settings for:
  - trace count
  - trace date
  - trace files part ( anonymous ) 

*/
function bw_flf( $function, $lineno, $file, $count,  $text, $text_label = NULL ) {
  $ref = bw_trace_file_part( $file );
  $ref .= '('.$lineno.':0) ';
  $ref .= bw_trace_date( DATE_W3C );
  $ref .= " ";
  $ref .= bw_trace_elapsed();
  $ref .= " ";
  $ref .= bw_trace_count( $count );
  $ref .= " "; 
  $ref .= bw_trace_context();
  $ref .= bw_get_num_queries();
  $ref .= bw_trace_post_id();
  $ref .= bw_get_memory_usage();
  $ref .= " "; 
  $ref .= "F=" . count( get_included_files() );
  $ref .= " ";
  $ref .= bw_trace_function( $function );
  $ref .= " ";
  $ref .= $text_label;
  $ref .= " ";
  if ( is_callable( "obsafe_print_r" ) ) {
    $ref .= obsafe_print_r( $text, TRUE );
  } else {  
    $ref .= print_r( $text, TRUE) ; 
  }
  $ref .= bw_trace_bwechos();
  $ref .= "\n";
  return( $ref );
} 

function bw_array_inc( &$array, $index ) {
  if ( !isset($array) )
    $array = array();
  if ( ! isset($array[$index]) )
    $array[$index] = 1;
  else
    ++$array[$index];
}

  
function bw_lazy_trace( $text, $function=__FUNCTION__, $lineno=__LINE__, $file=__FILE__, $text_label=NULL) {
  global $oktop, $bw_trace_on, $bw_trace_count, $bw_trace_functions;
  $oktop = TRUE;
  
  if ($bw_trace_on)
  {
    if ( empty( $bw_trace_count ) ) {
      $bw_trace_count = 0;
      $bw_trace_functions = array();
    }  
    $bw_trace_count++;
    // Note: This is NOT the actual count of the number of times that the function is called. 
    // It's the number of times that bw_trace is called for the $function
    bw_array_inc( $bw_trace_functions, $function );
    $line = bw_flf( $function, $lineno, $file, $bw_trace_count, $text, $text_label );  
    bw_trace_log( $line );  
      
  }  
  else { 
    // echo "<!--bw_trace_on is off -->" ;   
  }  
}  
  


function bw_trace_file() {   
  global $bw_trace_options;
  // $file = bw_get_docroot_suffix();
  
  if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');
  $file = ABSPATH;
     
  $file .= bw_array_get( $bw_trace_options, 'file', "bwtrace.loh" );
  return( $file );
}
  
function bw_trace_batch() {   
  global $bw_trace_options;   
  $bw_trace_options = array( 'file' => "bwtrace.loh",  ); 
  $bw_trace_options['file'] = "bwtrace.log";
  //bw_trace_on();
  //bw_trace_errors( 3 );
  bw_trace( $_GET, __FUNCTION__, __LINE__, __FILE__, "_GET" );
}  



function bw_trace_log( $line ) {
  // echo '<!--bw_trace_log '.$file.$line.'-->';
  $file = bw_trace_file();
  if ( $file )
    bw_write( $file, $line ); 
  else
    _doing_wrong_thing();
}


/* 
  write the trace line to the file
  if we can't open the file turn tracing off
  
  This is the sort of message we sometimes get.
  Not sure what's causing the error. It could be tailing the file.
   
  
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

*/
function bw_write( $file, $line ) {
  // echo 'in bw_write';
  $handle = fopen( $file, "a" );
  // echo "<!-- $handle $file $line-->";
  if ( $handle === FALSE ) {
     bw_trace_off();
     // It would be nice to let them know... 
     $ret = "fopen failed"; 
  }
  else {
    $bytes = fwrite( $handle, $line );
    $ret = fclose( $handle );
    $ret .= " $bytes $file $line";
    
  }
  return( $ret );
} 


/**
 * Perform a trace reset
 *
 * We shouldn't do this if we're not tracing the specific IP
 *
 * 
 */
function bw_trace_reset() {
  static $reset_done = false; 
  
  //global $bw_trace_options;   
  //$reset_done = bw_array_get( $bw_trace_options, 'reset_done', false );
  if ( ! $reset_done ) {
    $file = bw_trace_file();
    // This file may not exist so we have two choices. 1. precede with an @, 2. test for it
    // but if $file is not set then we should test
    if ( is_file($file) ) {
      unlink( $file ); 
      
    }
    // echo( "<p>Attempted to unlink: $file</p>" );  
    //$bw_trace_options['reset_done'] = true; 
  } 
  $reset_done = true;
      
} 



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
  function bw_array_get( $array = NULL, $index, $default=NULL ) {   
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
 * [file] is the file name
 * [line] is the line number
 * [function] is the method used to get the file: include, require_once
 * [args] are parameters
 * [class]
 * [object]
 * [type] -> = method call,  :: = static method call, nothing for function call
 * 

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
      $args = $call['args'];
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
            } else { 
              $function .= $sep.$targ ;
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
 * @param mixed $value - an optional field to be traced
 * @param string $text - an optional field identifying text for the field to be traced
 * @param string $show_args - true to display the arguments to the call  
 * @return mixed $value - to allow this function to be called in return statements 
 * 
 * Using debug_backtrace this function can be used to trace the parameters to a function
 * It's a version of bw_backtrace that doesn't produce the whole call tree
 * It's less efficient than bw_lazy_trace since it first needs to call debug_backtrace()
 * bw_backtrace should also perform the checks.
 *
 */
function bw_lazy_trace2( $value=NULL, $text=NULL, $show_args=true ) {
  global $bw_trace_on;
  if ($bw_trace_on) {
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
        bw_lazy_trace( $targs, $function, $line, $file, $cargs );
      }  
    } else { 
      $function = "";
    }
           
    if ( $value || $text ) 
      bw_lazy_trace( $value, $function, $line, $file, $text );
    if ( $show_args )  
      bw_trace_context_all( $function, $line, $file );  
  } 
  //bw_bang(); 
  return( $value );
}

/**
 * Implement (lazy) trace startup from wp-config.php
 * We cater for the follow defined constants
 * - define( 'BW_TRACE_ON', true );
 * - define( 'BW_ACTIONS_ON', true );
 * - define( 'BW_TRACE_RESET', true );
 * - define( 'BW_ACTIONS_RESET', true );
 */
function bw_lazy_trace_config_startup() {
     
  if ( defined( 'BW_TRACE_ON' ) )
    $trace_on = BW_TRACE_ON;
  else
    $trace_on = false;
  
  if ( defined( 'BW_ACTIONS_ON' ) )
    $actions_on = BW_ACTIONS_ON;
  else
    $actions_on = false;
    
  if ( defined( 'BW_TRACE_RESET' ) )
    $trace_reset = BW_TRACE_RESET;
  else
    $trace_reset = false;
    
  if ( defined( 'BW_ACTIONS_RESET' ) )
    $actions_reset = BW_ACTIONS_RESET;
  else
    $actions_reset = false;
    
  if ( $trace_on ) {
    bw_trace_inc_init();
    bw_trace_on( true );
    if ( $trace_reset ) {
      bw_trace_reset();
    }   
  }

  if ( $actions_on ) {     
    oik_require2( "includes/oik-bwtrace.inc", "oik-bwtrace" );
    bw_action_inc_init();
    bw_trace_actions_on();     
    if ( $actions_reset ) {
      bw_actions_reset();
    }  
  }
}
  
}    
   
   

