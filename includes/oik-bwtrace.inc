<?php // (C) Copyright Bobbing Wide 2012-2014
if ( !defined( 'OIK_OIK_BWTRACE_INCLUDES_INCLUDED' ) ) {
define( 'OIK_OIK_BWTRACE_INCLUDES_INCLUDED', true );


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
 This is a poorly named file it should be really have been oik-actions.inc
*/
bw_trace2( __FILE__, "file loaded" );

/** 
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
 * Trace anything left in the output buffer(s)
 * 
 * Artisteer 4.0 saves information in $theme_ob_stack, so we trace that in case it contains Warnings or Fatal messages.
 *
 */
function bw_trace_output_buffer() {  
  //$ob = ob_get_contents();
  $ob = ob_get_flush();
  bw_trace2( $ob, "output buffer" );
  if ( defined( "WP_DEBUG") && WP_DEBUG ) {
    //echo "output buffer";
    //print_r( $ob );
  }  
  global $theme_ob_stack;
  bw_trace2( $theme_ob_stack, "theme_ob_stack" );
  if ( defined( "WP_DEBUG") && WP_DEBUG ) {
    //echo "theme_ob_stack";
    //print_r( $theme_ob_stack );
  }  
}

/** 
 * At shutdown produce a report of the actions performed.
 * 
 * Note: WordPress doesn't count the number of times each filter is invoked. 
 * @TODO Shame... but we could do it couldn't we?
 *
 */
function bw_trace_report_actions() { 
  global $wp_actions;
  bw_trace( $wp_actions, __FUNCTION__, __LINE__, __FILE__, "wp_actions" );
}

/** 
 * At shutdown produce a report of the files loaded
 * 
 */
function bw_trace_included_files() { 
  $included_files = get_included_files();
  global $bw_trace_anonymous;
  $anon = $bw_trace_anonymous;
  $bw_trace_anonymous = true;
  $files = "<h3>Files</h3>";
  $lose = str_replace( "/", "\\", ABSPATH );
  foreach ( $included_files as $file ) {
    $file = str_replace( "/", "\\", $file );
    $file = str_replace( $lose , '', $file );
    $file = str_replace( "\\", "/", $file );
    $file = str_replace( "wp-content/plugins/", "", $file );
    $file = str_replace( "wp-content/themes/", "", $file );
    // If it's wp-content then we need to strip the plugin name as well .... but that means we might not load the right file
    // so oikai_get_file_byname() is wrong... and needs the plugin prefix.
    // where are MU plugins and drop-ins? 
    
    $files .= PHP_EOL . "[file $file]";
  }
  $bw_trace_anonymous = $anon;
  bw_trace( $files, __FUNCTION__, __LINE__, __FILE__, "included files" );
}

/**
 * Set the SAVEQUERIES constant if possible 
 *
 * If we want to trace the queries then the SAVEQUERIES constant needs to be set to true.
 * If it's already set then that MAY be hard lines; trace the value so that we know.
 */
function bw_trace_set_savequeries() {
  if ( !defined( 'SAVEQUERIES' ) ) {
    define( 'SAVEQUERIES', true );
  } else {
    bw_trace2( SAVEQUERIES, "SAVEQUERIES is already defined" );
  }
} 

/**
 * At shutdown produce the SAVEQUERIES report
 * 
 * Only do this if SAVEQUERIES is defined 
 *
 * Note: If SAVEQUERIES was not defined in wp-config.php then we can miss the first query.
 * The value of $wpdb->num_queries will be greater than the number of queries logged in the array.
 * This first query is: 
 *  [0] => Array
 *    (
 *     [0] => SELECT option_name, option_value FROM wp_options WHERE autoload = 'yes'
 *     [1] => 0.028857946395874
 *     [2] => require(wp-blog-header.php'), require_once('wp-load.php'), require_once('wp-config.php'), require_once('wp-settings.php'), wp_not_installed, is_blog_installed, wp_load_alloptions
 *   )
 *     
 */
function bw_trace_saved_queries() { 
  if ( defined( 'SAVEQUERIES' ) && SAVEQUERIES == true ) {
    global $wpdb;
    $elapsed_query_time = 0;
    bw_trace2( $wpdb, "saved queries", false ); 
    if ( count( $wpdb->queries ) ) {
      $count = 0;
      $record = "<h3>Queries</h3>" . PHP_EOL;
      $record .= "[bw_csv]#,Elapsed,Query" . PHP_EOL;
      foreach ( $wpdb->queries as $key => $query ) {
        $count++;
        $execution = $query[1];
        $query_string = $query[0];
        $record .= $count;
        $record .= ',';
        $record .= number_format( $execution, 6 ); 
        $record .= ',';
        $record .= str_replace( ",", "&comma;", $query_string);
        $record .= PHP_EOL;
        $elapsed_query_time += $execution;
      }
      $record .= $count . ',' . $elapsed_query_time . ",Total" . PHP_EOL;
      $record .= "[/bw_csv]";
      bw_trace2( $record, "Queries: {$wpdb->num_queries} in $elapsed_query_time", false );
    }
    $wpdb->elapsed_query_time = $elapsed_query_time;
  }  
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

   

/**
 * Trace the results and echo a comment?
 *
 */
function bw_trace_trace2( $value, $text, $extra=false ) {
  bw_trace2( $value, $text, $extra );
  bw_trace_c3( $value, $text, $extra );
}

/**
 * When tracing is inactive we write the output as a comment
 *
 * But only when it's safe to do so.
 *
 * * When the request is not an AJAX request
 * * When the request is not a aysnc-upload of a new file
 * * and other situations we don't yet know about
 *
 * Uses c()?  
 */ 
function bw_trace_c3( $value, $text, $extra=false ) {
  //  bw_trace2( DOING_AJAX, "doing_ajax?", false );
  //bw_trace2( $_REQUEST, "request", false );  
  if ( defined('DOING_AJAX') && DOING_AJAX ) {
    // Not safe to echo here
  } else {
    $short = bw_array_get( $_REQUEST, "short", null );
    if ( $short ) {
      // Not safe here either!
    } else {
      c( "$text:$value\n");
    } 
     
  }
  bw_trace_vt( $value, $text );
} 

function bw_trace_query_plugin_count( $plugins=null ) {
  if ( !$plugins ) {
    $plugins = bw_trace_query_plugins();
  }   
  $count = count( $plugins );
  return( $count );
}  

function bw_trace_query_plugins() {
  if ( PHP_SAPI == "cli" ) {
    $plugins = array( "oik-batch" );
  } elseif ( bw_is_wordpress() ) {  
    oik_require( "admin/oik-depends.inc" );
    $plugins = bw_get_active_plugins();
  } else {
    $plugins = array( "oik-batch" );
  }
  return( $plugins );
}
 

/** 
 * Show some really basic stuff about the PHP version, and number of functions and classes implemented
 * 
 * This is in addition to other stuff produced by oik-bwtrace
 * Not need to show number of db_queries as this is already (optionally) included in the trace record
 * BUT we could sum the time spent in the DB
 * AND we could sum the time spent tracing
 * which 'could' give us the execution time doing other things
 * 
 */
function bw_trace_status_report() {
  global $bw_trace_on, $bw_trace_count;
   // oik_require( "shortcodes/oik-api-status.php", "oik-shortcodes" );
  $func = "bw_trace_c3";
  $defined_functions = get_defined_functions(); 
  //$count = count( $defined_functions ); 
  $count_internal = count( $defined_functions["internal"] );
  $count_user = count( $defined_functions["user"] );
  $func( phpversion(), "PHP version", false ); 
  $func( $count_internal, "PHP functions", false );
  $func( $count_user, "User functions", false );
  $declared_classes = get_declared_classes(); 
  $count = count( $declared_classes );
  $func( $count, "Classes", false ); 
  // Don't trace $GLOBALS - there's far too much - approx 38K lines
  //$func( $GLOBALS, "Globals", false );
  $func( bw_trace_query_plugin_count(), "Plugins", false );
  $func( count( get_included_files() ), "Files", false );
  
  $func( count( $GLOBALS['wp_registered_widgets'] ), "Registered widgets", false );
  $func( count( $GLOBALS['wp_post_types'] ), "Post types", false );
  $func( count( $GLOBALS['wp_taxonomies'] ), "Taxonomies", false );
  global $wpdb;
  
  $func( $wpdb->num_queries, "Queries", false );
  $func( $wpdb->elapsed_query_time, "Query time", false ); 
  $func( $bw_trace_count, "Trace records", false );
  
  $elapsed = timer_stop( false, 6 );
  // Do this regardless 
  
  if ( $bw_trace_on ) { 
    $func = "bw_trace_trace2";
  } else {
    $func = "bw_trace_c3";
  }
  $func( $elapsed, "Elapsed (secs)", false );
  
  bw_flush();
  bw_record_vt();
}

/**
 * Define actions only available when trace is loaded
 *
 * Oh bugger: add_action() might not be available yet!
 * 
 *
 */
function bw_trace_oik_bwtrace_loaded() {
}

//bw_trace_oik_bwtrace_loaded();

function bw_trace_vt( $value, $text ) {
  global $vt_values, $vt_text;
  $vt_values[] = $value;
  $vt_text[] = $text;
}

/**
 * Determine what to log as the request
 * 
 * $SERVER | DOING_AJAX | What to log 
 * ------- | ---------- | ---------- 
 *  yes    |  No        | REQUEST_URI,,
 *  no     |  n/a       | parms,,
 *  yes    |  Yes       | REQUEST_URI,action 
 * 
 */
function bw_trace_determine_request() {
  $request = $_SERVER['REQUEST_URI' ];
  if ( !$request ) {
    if ( PHP_SAPI == "cli" ) {
      foreach ( $_SERVER['argv'] as $key => $arg ) {
        if ( $key ) {
          $request .= " ";
        }
        $request .= $arg; 
      }
    }
  }        
  $request .= ",";
  if ( defined( 'DOING_AJAX') && DOING_AJAX ) {
    $request .=  bw_array_get( $_REQUEST, 'action', null );
  }
  return( $request );
} 


/**
 * Record the summary values for this transaction
 *
 * Note: The columns are dynamically created from the fields recorded by bw_trace_status_report()
 * 
 * - request
 * - elapsed ( final figure )
 * - PHP version
 * - PHP functions
 * - User functions
 * - Classes
 * - Plugins
 * - Files
 * - Registered Widgets
 * - Post types
 * - Taxonomies
 * - Queries
 * - Trace records
 * - Elapsed
 */
function bw_record_vt( $vnoisy=false ) {
  global $vt_values, $vt_text;
  $line = bw_trace_determine_request();
  $line .= ",";
  $line .= timer_stop( false, 6 );
  foreach ( $vt_values as $key=> $value ) {
    $line .=  ",";
    if ( $vnoisy ) {
      $line .=   $vt_text[$key] . "=";
    }   
    $line .= $value ;
  }
  $line .= ",";
  $line .= date( 'c' );
  $line .= PHP_EOL;
  $file = ABSPATH . "bwtrace.vt." .  date( "md" );
  bw_write( $file, $line );
}  


} /* end of first if defined() */
