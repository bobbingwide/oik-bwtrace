<?php // (C) Copyright Bobbing Wide 2012-2015
if ( !defined( 'OIK_OIK_BWTRACE_INCLUDES_INCLUDED' ) ) {
define( 'OIK_OIK_BWTRACE_INCLUDES_INCLUDED', true );


/*  
 * This was a poorly named file. It was oik-bwtrace.inc
 * It should be really have been oik-actions.inc. 
 * Well oik-actions.php! 
 * It's OK now 
 *
 */
bw_trace2( __FILE__, "file loaded" );

/**
 * Trace anything left in the output buffer(s)
 * 
 * We need to test if there is anything in the output buffer before calling ob_get_flush() otherwise
 * it produces a Notice that can get returned to a client.
 * Calling ob_get_status() appears to help in this regard.
 * Note: We don't cater for multiple output buffers.
 * 
 * Artisteer 4.0 saves information in $theme_ob_stack, so we trace that in case it contains Warnings or Fatal messages.
 *
 */
function bw_trace_output_buffer() {  
  //$ob = ob_get_contents();
	$status = ob_get_status();
	
  bw_trace2( $status, "output buffer status" );
	if ( count( $status ) ) {
    $ob = ob_get_flush();
    bw_trace2( $ob, "output buffer" );
    if ( defined( "WP_DEBUG") && WP_DEBUG ) {
      //echo "output buffer";
      //print_r( $ob );
    }
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
	$files = PHP_EOL;
  $files .= "<h3>Files</h3>"; 
	$files .= PHP_EOL;
	$files .= "[bw_csv uo=u]File";
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
	$files .= PHP_EOL . "[/bw_csv]";
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
    global $wpdb;
    $elapsed_query_time = 0;
  if ( defined( 'SAVEQUERIES' ) && SAVEQUERIES == true ) {


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

  }
    $wpdb->elapsed_query_time = $elapsed_query_time;
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
  } elseif ( defined( 'JSON_REQUEST' ) && JSON_REQUEST ) {
    // Nor here
  } elseif ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) {
  } else {
    $short = bw_array_get( $_REQUEST, "short", null );
    if ( !$short ) {
      $short = bw_array_get( $_REQUEST, "sgCacheCheck", null );
    }
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
 * 
 * @TODO Trace action hook and filter counts as well?
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
    if ( !property_exists( $wpdb, "elapsed_query_time" )) {
        $wpdb->elapsed_query_time = "";
    }
   $func($wpdb->elapsed_query_time, "Query time", false);
  $func( bw_trace_file(), "Trace file", false );
  $func( $bw_trace_count, "Trace records", false );
  $remote_addr = bw_array_get( $_SERVER, 'REMOTE_ADDR', false );
  $func( $remote_addr, "Remote addr", false );
	
  global $wp_locale;
  if ( $wp_locale ) {
    $elapsed = timer_stop( false, 6 );
  } else {
    $elapsed = null;
  }  
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
 * Trace the 'wp' action
 *
 * 
 * @param object $WP_Environment_Instance
 *
 */
function bw_trace_wp( $WP_Environment_Instance ) {
  bw_trace2();
  $home = is_home();
  $front = is_front_page();
  $show_on_front = get_option( "show_on_front" );
  $page_on_front = get_option( "page_on_front" );
  $page_for_posts = get_option( "page_for_posts" );
  bw_trace2( "show,page,posts", "$show_on_front,$page_on_front,$page_for_posts", false ); 
  bw_trace2( $home, "home", false );
  bw_trace2( $front, "front", false );
}

/**
 * Trace the global $wp_rewrite
 *
 * This is traced during 'wp' if trace_wp_rewrite is true.
 * 
 * @param object $WP_Environment_Instance We don't need to trace this.
 */
function bw_trace_wp_rewrite( $WP_Environment_Instance ) {
  global $wp_rewrite;
  bw_trace2( $wp_rewrite, "wp_rewrite", false );
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
 * 0 - request
 * 1 - AJAX action
 * 2 - elapsed ( final figure )
 * 3 - PHP version
 * 4 - PHP functions
 * 5 - User functions
 * 6 - Classes
 * 7 - Plugins
 * 8 - Files
 * 9 - Registered Widgets
 * 10 - Post types
 * 11 - Taxonomies
 * 12 - Queries
 * 13 - Query time
 * 14 - Trace file
 * 15 - Trace records
 * 16 - Remote address ( IP address )
 * 17 - Elapsed
 * 18 - Date - ISO 8601 date 
 */
function bw_record_vt( $vnoisy=false ) {
  global $vt_values, $vt_text;
  $line = bw_trace_determine_request();
  $line .= ",";
  
  global $wp_locale;
  if ( $wp_locale ) {
    $line .= timer_stop( false, 6 );
  }  
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
