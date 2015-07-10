<?php // (C) Copyright Bobbing Wide 2015

/**
 * deprecated functions for oik-bwtrace
 *
 * This file contains oik-bwtrace functions that have been deprecated.
 * In order to use these you actually need to include this file yourself.
 * We don't really expect you to want to do this.
 *
 */
 

/**
 * Return the version of oik-bwtrace
 *
 * @return string - same as oik_version()
 */ 
function oik_bwtrace_version() {
  return oik_version();
}



/**
 * Turn on action tracing
 */
function bw_trace_actions_on() {
  global $bw_actions_on;
  $bw_actions_on = true;
}

/**
 * Turn off action tracing
 */
function bw_trace_actions_off() {
  global $bw_actions_on;
  $bw_actions_on = false;
}

/** 
 * Add trace actions for this hook
 * 
 * Test to see if we've added our action handler/filters
 * $wp_filter[$tag][$priority][$idx]
 * If not, then add them
 * @uses add_filter($tag, $function_to_add, $priority = 10, $accepted_args = 1) 
 */
function bw_trace_add_actions( $tag, $actions=NULL ) {
  $action_0 = bw_array_get( $actions, 0, NULL );
  $btas = bw_array_get( $action_0, "bw_trace_action_start", NULL ); 
  
  //bw_trace( $action_0, __FUNCTION__, __LINE__, __FILE__, "$tag" );
  //bw_trace( $btas, __FUNCTION__, __LINE__, __FILE__, "btas" );
  if ( $btas <> "bw_trace_action_start"  ) {
    //bw_trace( $tag, __FUNCTION__, __LINE__, __FILE__, "tag" );
    add_action( $tag, "bw_trace_action_start", 0, 5 );
    add_action( $tag, "bw_trace_action_end", 1000, 5 );
  } else {
    /* Sometimes we're not the first function to get invoked for the action 
       e.g. create_initial_post_types is set for "init" with highest priority (0)
       We can attempt to overcome this by adding our actions first
    */
    bw_trace( $tag, __FUNCTION__, __LINE__, __FILE__, "action_0" );
  }   
}



/** 
 * Start the trace action logic if required 
 *
 * Load the bw_action options and check to see if "actions" is set. 
 * If so then set action tracing on, else set it off.
 * 
 * @TODO - why load oik-bwtrace.inc when it's off? Maybe we should only call bw_trace_actions_off() when it's available.
 */ 
function bw_trace_actions() {
  global $bw_action_options; 
	
  $bw_action_options = get_option( 'bw_action_options' );
  $trace_actions = bw_array_get( $bw_action_options, "actions", false );
  //bw_trace2( $bw_action_options, "bw_action_options" );
  if ( $trace_actions ) {
    oik_require2( "includes/oik-actions.php", "oik-bwtrace" );
    bw_trace_actions_on();
    bw_lazy_trace_actions();
  } else {
    if ( is_callable( "bw_trace_actions_off" ) ) {
      bw_trace_actions_off();
    }
  }  
}



/**
 * Avoid recursion when bw_lazy_trace_actions is adding its own filters
 * 
 * How to use this function (WP 3.4.1 & WP 3.4.2 )
 * - edit wp-includes/plugin.php
 * - locate the add_filters function
 * - copy and paste the following code before the 'return;'
 
        if ( function_exists( "bw_call_lazy_trace_actions" ) )
          bw_call_lazy_trace_actions( $tag, $wp_filter[$tag] );
 *
*/ 
function bw_call_lazy_trace_actions( $tag, $actions ) {
  static $bw_in_lazy_trace;
  if ( !isset( $bw_in_lazy_trace ) )
    $bw_in_lazy_trace = false;
  if ( !$bw_in_lazy_trace ) {
    $bw_in_lazy_trace = true;
    /* We already know that an action or filter has been added 
       so no need to test the counts again
       what we should do is improve the logic to add actions for the new action/filter
    */
    bw_trace_add_actions( $tag, $actions );
    global $bw_filter_count, $wp_filter; 
    $bw_filter_count = count( $wp_filter );
      
    $bw_in_lazy_trace = false;
  }    
}



/**
 * Implements the 'all' action/filter as part of oik-bwtrace... for action tracing
 * 
 * For filters it would be nice to see the value that is being returned.
 * This is not possible with "all" tracing. We need the more complex version of "Immediate" action tracing to achieve this. 
 *
 * @param string $tag - the action or filter 
 * @param mixed $args - the action or filter parameters 
 */
function bw_trace_hook_all( $tag, $args2=null ) {
  // Determine the invocation method from the call stack:
  // 3 do_action(), do_action_ref_array(), apply_filters(), apply_filters_ref_array()
  // 2  _wp_call_all_hook()
  // 1    call_user_func_array()
  // 0       bw_trace_all()    
  $bt = debug_backtrace(); 
  $action_or_filter_function = $bt[3]['function'] ;
  bw_trace_action_immediate( $action_or_filter_function , "#I", $tag, $args2, count( $bt ) );
}

/**
 * Attach oik-bwtrace to each action that gets invoked by do_action
 *
 * Uses globals:
 *   $wp_filter is the array of filters and actions
 *   $wp_actions counts the number of times an action has been performed
 *   $bw_filter_count
 * Notes: This routine gets invoked whenever the number of filters changes
 * We do this since there's no hook for add_filter/add_action
 */
function bw_lazy_trace_actions() {
  global  $wp_filter, $wp_actions, $bw_filter_count;
  //bw_trace2( $wp_filter, "wp_filter" );
  //bw_trace2( $wp_actions, "wp_actions" );
  $wp_filter_copy = $wp_filter;
  if ( count( $wp_filter_copy )) {
    foreach ( $wp_filter_copy as $tag => $actions ) {
      bw_trace_add_actions( $tag, $actions );
    }
  }
  // bw_trace( $actions, __FUNCTION__, __LINE__, __FILE__, "last action" );
  //add_action( "wp_title", "bw_trace_action", 1000, 5 );
  //add_action( "the_content", "bw_trace_action", 1000, 5 );
  add_action( "all", "bw_trace_hook_all" );
  $bw_filter_count = count( $wp_filter );
  // This produces too much trace output
  //bw_trace( $bw_filter_count, __FUNCTION__, __LINE__, __FILE__, "bw_filter_count" );
}

/**
 * 
 */
function bw_action_inc_init() {   
  global $bw_action_options;   
  $bw_action_options = array( 'file' => "bwaction.loh", 'action' => 'on'  ); 
}

/**
 * Reset the trace actions file if this is the first time for this invocation
 * 
 */
function bw_actions_reset() {
  static $reset_done = false;
  //global $bw_action_options;   
  //$reset_done = bw_array_get( $bw_action_options, 'reset_done', false );
  if ( !$reset_done ) {
    $file = bw_action_file();
    
    
    // This file may not exist so we have two choices. 1. precede with an @, 2. test for it
    // but if $file is not set then we should test
    // In order for bw_summarise_actions to work we need a saved copy of the action log before the reset
    if ( is_file($file) ) {      
      $target = "bwaction.cpy" ;
      $res = copy( $file, $target ); 
      $ret = unlink( $file ); 
      bw_trace2( $ret, "unlink $file" );
    }  
    bw_trace2( $file, "action_reset" );
  } 
  //$bw_action_options['reset_done'] = true; 
  $reset_done = true;

}

/**
 * Determine the name of the action file
 * 
 * @return string $file fully qualified file name for the action file 
 * 
 * Notes: 
 * - Similar to bw_trace_file except it uses the action options
 * - If we don't use ABSPATH then the file can be written to into whatever is the current directory, which may be wp-admin or elsewhere
 * - If bw_action_options are not loaded the default file is "bwaction.loh" 
 *
*/
function bw_action_file() {
  static $file = null;
  if ( !$file ) {
    global $bw_action_options;   
    if ( !defined('ABSPATH') )
  	define('ABSPATH', dirname(__FILE__) . '/');
    $file = ABSPATH;
    $file .= bw_array_get( $bw_action_options, 'file', "bwaction.loh" );
  }  
  return( $file );
}

function bw_action_line( $storend, $immed=null ) {
  global $bw_trace_count;
  $mtime = explode(" ", microtime() );
  $line = date( "Y-m-d H:i:s", $mtime[1]); 
  $line .= " ";
  $line .= $mtime[0]; 
  $line .= " ";
  $line .= $storend;
  $line .= " ";
  $line .= bw_trace_count( $bw_trace_count );
  $line .= " ";
  $line .= bw_trace_context();
  $line .= " ";
  $line .= $immed;
  $line .= "\n";
  return( $line );
}



/**
 * Log an action start or end
 */  
function bw_log_action( $storend="<S", $immed=null ) {
  $file = bw_action_file();
  $line = bw_action_line( $storend, $immed );
  $ret = bw_write( $file, $line );
  if ( substr( $ret,0,1)  <> '1'  ) {
    // Record what happened in trace if it didn't actually work    
    bw_trace2( $ret, "returned from bw_write" ); 
  }  
}


/**
 * Trace the fact that an action has started
 * @param mixed $arg1-5 - up to 5 args - the first has to be returned when the action is a filter
 * @return mixed $arg1
 * @uses bw_lazy_trace_actions
 * Notes: 
 * - Since actions may be nested we can't simply record start and end times for an action or filter
 *   to determine the elapsed time for an action
 * - This could be determined from analysis of the trace output were the timestamp accurate enough
 * 
 */
function bw_trace_action_start( $arg1=null, $arg2=null, $arg3=null, $arg4=null, $arg5=null ) {
  global $bw_actions_on;
  if ( $bw_actions_on ) {
    global $wp_filter, $bw_filter_count;
    bw_trace( $arg1, __FUNCTION__, __LINE__, __FILE__, "arg1" );
    bw_log_action();
    if ( count( $wp_filter ) <> $bw_filter_count ) {
      bw_lazy_trace_actions();
    }
  }
  return( $arg1 );
}

/** 
 * Trace the fact that an action has completed
 * Notes:
 * - We assume that priority of 1000 is the highest anyone will set
 * - When the action is shutdown we call bw_trace_report_actions()
 * - this could just as easily have been added using add_action( "shutdown", "bw_trace_report_actions" ) **?** 
 */
function bw_trace_action_end( $arg1=null, $arg2=null, $arg3=null, $arg4=null, $arg5=null ) { 
  global $bw_actions_on;
  if ( $bw_actions_on ) {
    global $wp_filter, $bw_filter_count;
    bw_trace( $arg1, __FUNCTION__, __LINE__, __FILE__, "arg1" );
    bw_trace( $arg2, __FUNCTION__, __LINE__, __FILE__, "arg2" );
    
    bw_log_action( "E>" );
    if ( count( $wp_filter ) <> $bw_filter_count ) {
      bw_lazy_trace_actions();
    }
    $cf = current_filter();
    if ( $cf == "shutdown" ) {
      bw_trace_output_buffer(); 
      bw_trace_report_actions();
      bw_trace_included_files();
    } elseif ( $cf == "the_content" ) {
      bw_trace2( $wp_filter[ $cf ], "the_content filters" );
    }
  }  
  return( $arg1 );
}

/**
 * Trace the fact that an action has been invoked without our prior knowledge of the possibility
 * @param mixed $arg1-5 - up to 5 args - the first has to be returned when the action is a filter
 * @return mixed $arg1
 * @uses bw_lazy_trace_actions(), bw_trace(), bw_log_action()
 * 
 * Notes: Calls to the bw_trace_action_immediate() function should be coded in appropriate places in wp-includes/plugin.php 
 */
function bw_lazy_trace_action_immediate( $function, $storend="I!", $arg1=null, $arg2=null, $arg3=null, $arg4=null, $arg5=null ) {  
  global $bw_trace_on, $bw_actions_on;
  if ( $bw_actions_on && $bw_trace_on ) {
    global $wp_filter, $bw_filter_count;
    bw_trace( $arg2, $function , __LINE__, __FILE__, $arg1 );
    //bw_trace( $arg2, $function , __LINE__, __FILE__, "arg2" );
    bw_log_action( $storend, "$function $arg1 $arg3" ); 
    if ( count( $wp_filter ) <> $bw_filter_count ) {
      bw_lazy_trace_actions();
    }
  } else {
    // Remove when the startup logic has been debugged! **?**
    bw_trace( $arg2, $function, __LINE__, __FILE__, $arg1 );
  }
  return( $arg1 );
}

/**
 * Trace the fact that an action has been invoked without our prior knowledge of the possibility
 * 
 * @param string $function - pass __FUNCTION__
 * @param string $storend - Start or end action code. Use "<I" for immediate start, and ">I" for immediate end
 * @param mixed $arg1-5 - up to 5 args - the first has to be returned when the action is a filter
 * @return mixed $arg1
 * @uses bw_lazy_trace_action_immediate()
 * Notes: 
 * Code calls to this function in wp-includes/plugin.php inside the following functions:
 * - apply_filters()
 * - apply_filters_ref_array()
 * - do_action
 * - do_action_ref_array
 *
 * Calls should be coded, at the start:
 *     bw_trace_action_immediate( __FUNCTION__, "<I", $tag, $value );
 * AND at the end, before the final return statement and closing brace:
 *     bw_trace_action_immediate( __FUNCTION__, "I>", $tag, $value ); 
 *
 * The calls do not have to be paired. If the function returns early then we know that there was no user defined function for the filter/action
 * 
 */
if ( !function_exists( "bw_trace_action_immediate" ) ) {
function bw_trace_action_immediate( $function, $storend="I!", $arg1=null, $arg2=null, $arg3=null, $arg4=null, $arg5=null ) {
  if ( function_exists( "bw_lazy_trace_action_immediate" ) ) {
    return( bw_lazy_trace_action_immediate( $function, $storend, $arg1, $arg2, $arg3, $arg4, $arg5 ) );
  } else {
    return( $arg1 );
  }
}
} 



