<?php // (C) Copyright Bobbing Wide 2012-2016,2019,2020,2021
if ( !defined( 'OIK_OIK_BWTRACE_INCLUDES_INCLUDED' ) ) {
define( 'OIK_OIK_BWTRACE_INCLUDES_INCLUDED', true );


/**
 * oik-bwtrace action tracing
 *  
 *
 */
//bw_trace2( __FILE__, "file loaded" );

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
 *
 * When zlib.output_compression is on then ob_get_status() may return an array like the following.
 * ```
 * Array
 * [name] => (string) "zlib output compression"
 * [type] => (integer) 0
 * [flags] => (integer) 20512
 * [level] => (integer) 1
 * [chunk_size] => (integer) 16384
 * [buffer_size] => (integer) 20480
 * [buffer_used] => (integer) 3935
 * ```
 *
 * In this case should not try to call ob_get_flush() because we get another Notice, due to output handler incompatibilities.
 * As a workaround we first check if zlib.output_compression is set in the php.ini file
 */
function bw_trace_output_buffer() {
	if ( ini_get( 'zlib.output_compression') ) {
		return;
	}
  //$ob = ob_get_contents();
	$status = ob_get_status();
	
  bw_trace2( $status, "output buffer status", false );
	if ( count( $status ) ) {
    $ob = ob_get_flush();
    bw_trace2( $ob, "output buffer", false );
    if ( defined( "WP_DEBUG") && WP_DEBUG ) {
      //echo "output buffer";
      //print_r( $ob );
    }
	}	  
  global $theme_ob_stack;
  bw_trace2( $theme_ob_stack, "theme_ob_stack", false );
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
 * The report is formatted for inclusion into a WordPress page formatted using shortcodes.
 * 
 * The [file] shortcode will need to take into account the WordPress core files and the plugin or theme name.
 *
 * We use the global $bw_trace_anonymous to force bw_trace_file_part() to do its stuff.
 *
 * @TOOD Perhaps we can just look for 'wp-content/plugins' or 'wp-content/themes' and take the substr from that point.
 *
 * If it's wp-content then we need to strip the plugin name as well .... but that means we might not load the right file
 * so oikai_get_file_byname() is wrong... and needs the plugin prefix.
 *
 * @TODO Cater for 'wp-content/mu-plugins'
 * @TODO Cater for drop-ins
 * 
 */
function bw_trace_included_files() { 
	$files = PHP_EOL;
  $files .= "<h3>Files</h3>"; 
	$files .= bw_trace_get_included_files();
  bw_trace( $files, __FUNCTION__, __LINE__, __FILE__, "included files" );
}

/**
 * Return the shortcode for included files
 *
 * @return string shortcode for included files
 */
function bw_trace_get_included_files() {
  $included_files = get_included_files();
	bw_trace2( $included_files, "included_files", false, BW_TRACE_VERBOSE );
  global $bw_trace_anonymous;
  $anon = $bw_trace_anonymous;
  $bw_trace_anonymous = true;
	//$files = "[bw_csv uo=u]File";
  //$lose = str_replace( "/", "\\", ABSPATH );
	$files = null;
  foreach ( $included_files as $file ) {
		$original = $file;
    $file = str_replace( "\\", "/", $file );
		$file = bw_trace_file_part( $file );
		$pos = strpos( $file, "wp-content/" );
		if ( false !== $pos ) {
			$file = substr( $file, $pos );
		} 
    //$file = str_replace( "wp-content/plugins/", "", $file );
    //$file = str_replace( "wp-content/themes/", "", $file );
    
    $files .= PHP_EOL . "[file $file $original]";
  }
	//$files .= PHP_EOL . "[/bw_csv]";
  $bw_trace_anonymous = $anon;
	return( $files );
}


/**
 * At shutdown produce the SAVEQUERIES report
 * 
 * Only do this if SAVEQUERIES is defined 
 *
 * Note: If SAVEQUERIES was not defined in wp-config.php then we can miss the first query.
 * The value of $wpdb->num_queries will be greater than the number of queries logged in the array.
 * This first query is: 
 * `
 *  [0] => Array
 *    (
 *     [0] => SELECT option_name, option_value FROM wp_options WHERE autoload = 'yes'
 *     [1] => 0.028857946395874
 *     [2] => require(wp-blog-header.php'), require_once('wp-load.php'), require_once('wp-config.php'), require_once('wp-settings.php'), wp_not_installed, is_blog_installed, wp_load_alloptions
 *   )
 * `   
 */
function bw_trace_saved_queries() {
	global $wpdb;
	if ( defined( 'SAVEQUERIES' ) && SAVEQUERIES == true ) {
		bw_trace2( $wpdb, "saved queries", false );
        /*
		$record = PHP_EOL . "<h3>Queries</h3>" . PHP_EOL;
		$record .= bw_trace_get_saved_queries_bwsql();
		bw_trace2( $record, "Queries: {$wpdb->num_queries} in {$wpdb->elapsed_query_time}", false );
        */

        // Trace to CSV format
        $queries = bw_trace_fetch_queries_execution_time();
        bw_trace_saved_queries_to_csv( $queries );
        // Order by execution time, recalculate the accumulated figure and trace again
		$queries = bw_trace_sort_queries_by_execution_time( $queries );
		$queries = bw_trace_reaccumulate_execution_time( $queries );
        bw_trace_saved_queries_to_csv( $queries, "Queries by Time DESC" );

        bw_trace_saved_queries_grouped_by_function( $queries );
	}
}

/**
 * Return the saved queries
 *
 * Create a set of [bw_sql] shortcodes with format as in this example
 * 
 * `
 * [bw_sql 1 1.2 get_post_meta]SELECT option_value FROM wp_options WHERE option_name = 'auto_core_update_failed' LIMIT 1,get_option[/bw_sql]
 * `
 *
 * @return string saved queries 
 */
function bw_trace_get_saved_queries_bwsql() {
	global $wpdb;
	$elapsed_query_time = 0;
	$wpdb->elapsed_query_time = $elapsed_query_time;
	$record = null;
	if ( count( $wpdb->queries ) ) {
		$count = 0;
		//$record .= "[bw_csv]#,Elapsed,Query,Function" . PHP_EOL;
		foreach ( $wpdb->queries as $key => $query ) {
			$count++;
			$execution = $query[1];
			$query_string = $query[0];
			$query_string = str_replace( array( ",", "\n") , array( "&comma; ", " " ), $query_string);
			$record .= "[bw_sql ";
			$record .= $count;
			$record .= ' ';
			$record .= number_format( $execution, 6 ); 
			$record .= ' ';
			$record .= bw_trace_get_last_query_function( $query[2] );
			$record .= ']';
			$record .= $query_string;
			$record .= '[/bw_sql]';
			$record .= PHP_EOL;
			$elapsed_query_time += $execution;
		}
		//$record .= $count . ',' . $elapsed_query_time . ",Total" . PHP_EOL;
		//$record .= "[/bw_csv]";
	}
	
	$wpdb->elapsed_query_time = $elapsed_query_time;
	return( $record );
}

 /**
  * Returns the query string suitable for CSV.
  *
  * Excel has a limit of 32,767 characters per cell.
  *
  * https://support.microsoft.com/en-us/office/excel-specifications-and-limits-1672b34d-7043-467e-8e27-269d656771c3
  *
  * @param $query_string
  * @return string|string[]
  */
function bw_trace_query_string_to_csv( $query_string ) {
    // Limit the length of the SQL query to something sensible.
    // Don't worry about truncating at strange places.
    // Perform the replaces after truncating
    // Assuming the content isn't just commas.
    $query_string = substr( $query_string, 0, 5000);
    $query_string = str_replace( array( ",", "\n", "\r" ) , array( "&comma; ", " " ), $query_string );
    return $query_string;
}

 /**
  * Returns summary array of queries execution times.
  *
  * Creates an array containing:
  * - index of original query
  * - query execution time
  * - accumulated execution time
  * - function invoking the query
  * - enough of the query to be useful in the spreadsheet
  *
  * Calculates the $wpdb->elapsed_query_time
  * 
  * @return array
  */
function bw_trace_fetch_queries_execution_time() {
    global $wpdb;
    $wpdb->elapsed_query_time = 0;
    $queries = [];
    if (count($wpdb->queries)) {
         $count = 0;
         $accum = 0;
         foreach ($wpdb->queries as $key => $query) {
             $execution = $query[1];
             $accum += $execution;
             $query_string = bw_trace_query_string_to_csv( $query[0]);
             $function =  bw_trace_get_last_query_function( $query[2] );
             $queries[] = [ $count, $execution, $accum, $function, $query_string ];
             $count++;
         }
    }
    $wpdb->elapsed_query_time = $accum;
    return $queries;
 }

    /**
     * Sorts the $queries array by execution time - column 1
     * @param $queries
     * @return mixed
     */


 function bw_trace_sort_queries_by_execution_time( $queries ) {
     $execution = array_column( $queries, 1 );
     array_multisort( $execution, SORT_DESC, $queries );
     return $queries;
 }
 function bw_trace_reaccumulate_execution_time( $queries ) {
     $accum = 0;
     $accumed = [];
     foreach ( $queries as $query ) {
         $accum += $query[1];
         $query[2] = $accum;
         $accumed[] = $query;
     }
    return $accumed;
}

function bw_trace_saved_queries_to_csv( $queries, $heading="Queries" ) {
    global $wpdb;
    $record = PHP_EOL . "<h3>$heading</h3>" . PHP_EOL;
    $record .= "#,Time,Accum,Function,SQL" . PHP_EOL;
    foreach ( $queries as $query ) {
        $record .= implode(',', $query);
        $record .= PHP_EOL;
    }
    bw_trace2( $record, "Queries: {$wpdb->num_queries} in {$wpdb->elapsed_query_time}", false );

 }
/**
 * Find the function that performed the query
 *
 * @param string $backtrace the formatted backtrace from $wpdb
 * @return string the function or method with '->' converted to '::'
 */
function bw_trace_get_last_query_function( $backtrace ) {
	$functions = explode( ",", $backtrace );
	$last = end( $functions );
	$last = trim( $last );
	$last = str_replace( "->", "::", $last );
	return( $last );
}

function bw_trace_saved_queries_grouped_by_function( $queries ) {
    global $wpdb;
    $functions = [];
    $counts = [];
    $count = 0;
    foreach ( $queries as $query ) {
        $function = $query[3];
        if ( !isset( $functions[ $function ] ) ) {
            $functions[ $function ] = 0;
            $counts[ $function ] = 0;
        }
        $functions[$function] += $query[1];
        $counts[ $function ] += 1;
        $count++;
    }
    $functions["Total"] = $wpdb->elapsed_query_time;
    $counts['Total'] = $count;
    arsort( $functions);
    //bw_trace2( $functions, "Grouped by functions", false );
    $report = PHP_EOL;
    $report .= "Function | Count | Elapsed" . PHP_EOL;
    $report .= "-------- | ----- | -------" . PHP_EOL;
    foreach ( $functions as $key => $value ) {
        $report .= $key;
        $report .= ' | ';
        $report .= $counts[ $key ];
        $report .= ' | ';
        $report .= $value;
        $report .= PHP_EOL;
    }
    //print_r( $counts );
    //print_r( $functions );
    bw_trace2( $report, "Grouped by function", false );
}


/**
 * Report trace function count on shutdown
 * 
 * Show the number of times each function was traced
 *
 */
function bw_trace_functions_traced() {
	global $bw_trace_functions;
  bw_trace2( $bw_trace_functions, "functions traced", false ); 
}

/**
 * Reports the time to load each plugin.
 *
 * This output also includes values for REQUEST_TIME_FLOAT, WP_START_TIMESTAMP
 * muplugins_loaded and plugins_loaded.
 */
function bw_trace_plugin_loaded_report() {
	global $bw_trace_plugins_loaded;
	//global $bw_trace_plugins_loaded_unkeyed;
	global $bw_trace_anonymous;
	$saved_anon = $bw_trace_anonymous;
	$bw_trace_anonymous = true;
	bw_trace2( $bw_trace_plugins_loaded, 'bw_trace_plugins_loaded', false);
	//bw_trace2( $bw_trace_plugins_loaded_unkeyed, 'bw_trace_plugins_loaded_unkeyed', false);
	$prev = $_SERVER['REQUEST_TIME_FLOAT'];
	$accum = 0;
	$output = "Plugin,Load time (msecs),$accum\n";
	foreach ( $bw_trace_plugins_loaded as $plugin => $time ) {
		$plugin = bw_trace_file_part( $plugin );
		$elapsed = $time - $prev;
		$accum += $elapsed;
		$elapsed6 = number_format( $elapsed, 6 );
		$accum6 = number_format( $accum, 6 );
		$output .= "$plugin,$elapsed6,$accum6\n";
		$prev = $time;
	}
	$bw_trace_anonymous = $saved_anon;
	bw_trace2( $output, "Plugin load times", false );
}

/**
 * Trace the results and echo a comment?
 *
 * @param string $value
 * @param string $text
 * @param bool $extra	- 3rd parm to bw_trace2()
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
 * Uses c()?... which requires libs/bobbfunc.php 
 *
 * @param string $value value to be written
 * @param string $text contextual label 
 * @param string $extra	3rd parm to bw_trace2() - future use
 */ 
function bw_trace_c3( $value, $text, $extra=false ) {
	if ( bw_trace_ok_to_echo() ) {
		if ( function_exists( "c" ) ) {
			c( "$text:$value\n");
		}	
  } 
  bw_trace_vt( $value, $text );
}

/** 
 * Return the number of active plugins
 *
 * @param array $plugins may be null
 * @return integer count of active plugins
 */ 
function bw_trace_query_plugin_count( $plugins=null ) {
  if ( !$plugins ) {
    $plugins = bw_trace_query_plugins();
  }   
  $count = count( $plugins );
  return( $count );
} 

/**
 * Return the array of active plugins
 *
 * This function accounts for tracing in a simulated WordPress environment: oik-batch
 * 
 * @TODO Confirm it also accounts for running under WP-CLI
 *
 * @return array plugin names array
 */ 
function bw_trace_query_plugins() {
	if ( !function_exists( "bw_get_active_plugins" ) ) {
		$oik_depends = oik_require_lib( "oik-depends" );
		bw_trace2( $oik_depends, "oik-depends", false, BW_TRACE_VERBOSE );
	}
	if ( function_exists( "bw_get_active_plugins" ) ) { 
		$plugins = bw_get_active_plugins();
	} else {
		$plugins = array( "oik-batch" );
	}
  //if ( PHP_SAPI == "cli" ) {
    //$plugins = array( "oik-batch" );
	//}
  return( $plugins );
}
 
/** 
 * Produce a transaction summary record
 * 
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
 * @TODO Trace number of Errors, Warnings and Notices detected
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
	
	global $bw_trace;
	if ( $bw_trace ) {
		$bw_trace_count = $bw_trace->get_trace_count( "trace_count" );
		$bw_trace_count_errors = $bw_trace->get_trace_error_count();
	}
	if ( $bw_trace_count ) {
		$func( bw_trace_file2(), "Trace file", false );
		$func( $bw_trace_count, "Trace records", false );
		$func( $bw_trace_count_errors, 'Trace errors', false );
	} else {
		$func( null, "Trace file", false );
		$func( null, "Trace records", false );
		$func( null, 'Trace errors', false );
	}
	$hook_count = null;
	if ( $bw_trace ) {
		if ( $bw_trace->is_trace_hook_counting() ) {
			$hook_count = $bw_trace->set_trace_hook_count();
		}
	}
	$func( $hook_count , 'Hook count', false );
	$remote_addr = bwtrace_get_remote_addr();
	$func( $remote_addr, "Remote addr", false );
	$elapsed = bw_trace_timer_stop( false, 6 );
	// Do this regardless 
	if ( $bw_trace_on ) { 
		$func = "bw_trace_trace2";
	} else {
		$func = "bw_trace_c3";
	}
	$func( $elapsed, "Elapsed (secs)", false );
	if ( function_exists( "bw_flush" ) ) {
		bw_flush();
	}
	//bw_record_vt();
}

/**
 * Gets the remote IP address
 * 
 * Index   | May contain
 * ------  | ----------
 * HTTP_CF_CONNECTING_IP | Real IP set by Cloudflare
 * HTTP_X_REAL_IP | IP extracted from HTTP_X_FORWARDED_FOR - set by Nginx?
 * HTTP_X_FORWARDED_FOR | From X-Forwarded-For header. Series of IP addresses, comma separated. First is the user's IP, rest are proxy IPs
 * REMOTE_ADDR | Server forwarding the request
 *
 * @return string|null Remote IP address
 */
function bwtrace_get_remote_addr() {
	global $bw_trace;
	$ip=null;
	if ( $bw_trace ) {
		$ip = $bw_trace->get_remote_addr();
	}

	return $ip;
}



/**
 * Determine the elapsed time in seconds and microseconds
 */
function bw_trace_timer_stop() {
	global $timestart, $timeend;
	$timeend = microtime( true );
	$timetotal = $timeend - $timestart;
	$elapsed = sprintf( "%.6f", $timetotal );
	return( $elapsed );
}

/**
 * Trace the 'wp' action
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
 * Trace the WordPress plugin paths	$wp_plugin_paths
 *
 */
function bw_trace_plugin_paths() {
	global $wp_plugin_paths;
	bw_trace2( $wp_plugin_paths, "plugin paths", false, BW_TRACE_DEBUG );
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

/**
 * Record a trace value / text pair
 *
 * @param string $value the value 
 * @param string $text the textual label for the value
 */
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
 * If the request contains commas we need to wrap it in quotes or escape them.
 * Otherwise a CSV routine may not deal with it correctly.
 * Note: We expect double quotes to have been encoded as %22.
 *
 * @TODO What if the AJAX action contains commas and/or quotes?
 * 
 * @return string the request string
 */
function bw_trace_determine_request() {
  $request = $_SERVER['REQUEST_URI'];
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
	if ( false !== strpos( $request, "," ) ) {
		$request = '"' . $request . '"';
	}				
  $request .= ",";
  if ( defined( 'DOING_AJAX') && DOING_AJAX ) {
    $request .=  bw_array_get( $_REQUEST, 'action', null );
  }
  return( $request );
} 

/**
 * Extracts the HTTP_USER_AGENT
 *
 * @return string a slightly sanitized value for HTTP_USER_AGENT
 */
function bw_trace_http_user_agent() {
	$http_user_agent = bw_array_get( $_SERVER, "HTTP_USER_AGENT", null );
	$http_user_agent = str_replace( ",", ";", $http_user_agent );
	return $http_user_agent;
}

/** 
 * Return the hook type
 *
 * We can tell it's an 'action' hook if it's listed in $wp_actions
 * If not it's a 'filter'.
 *
 * @param string $hook
 * @return string "action" | "filter" 
 */ 
function bw_trace_get_hook_type( $hook ) {
	global $wp_actions;
	if ( isset( $wp_actions[ $hook ] ) ){
		$type = "action";
	} else {
		$type = "filter";
	}
	return( $type );
}

/**
 * Return true if it's OK to echo HTML comments and such
 *
 * It's not safe to echo when:
 *
 * * the request is an AJAX request
 * * the request is a JSON request
 * * the request is for robots.txt
 * * the request is an async|upload 
 * * the request is an async-upload of a new file ( $_REQUEST contains "short" )
 * * the request is a SiteGround cache check
 * * the request was implemented as a REST API !
 * * the request is WordPress Health Check wp-admin/?health-check-test-wp_version_check=1
 * * the request is an export from Visual-Form-Builder ( VFB )
 * * ... and other situations we don't yet know about
 */
function bw_trace_ok_to_echo() {
	$ok = true;
  if ( defined('DOING_AJAX') && DOING_AJAX ) {
		$ok = false;
  } elseif ( defined( 'JSON_REQUEST' ) && JSON_REQUEST ) {
    $ok = false;
  } elseif ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) {
		$ok = false;
  } elseif ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
		$ok = false;
	} elseif ( did_action( "do_robotstxt" ) ) {
		$ok = false;
	} elseif ( did_action( "load-async-upload.php" ) ) {
		$ok = false;
  } else {
    $short = bw_array_get( $_REQUEST, "short", null );
    if ( !$short ) {
      $short = bw_array_get( $_REQUEST, "sgCacheCheck", null );
    }
    if ( !$short ) {
        $short = bw_array_get( $_REQUEST, "health-check-test-wp_version_check", null );
    }
    if ( !$short ) {
    	$action = bw_array_get( $_REQUEST, 'action', null );
    	$short = $action == 'download_product_csv';
    }
    if ( !$short ) {
    	$short = bw_array_get( $_REQUEST, 'block_data_export', null );
    }
    if ( !$short ) {
    	$short = bw_array_get( $_REQUEST, 'edd-api', null );
    }
	  if ( !$short ) {
		  $short = bw_array_get( $_REQUEST, 'edd_action', null );
	  }

    if ( !$short ) {
        $short = bw_array_get( $_REQUEST, 'downloadBackup', null );
    }

    if ( !$short ) {
    	$short = bw_array_get( $_REQUEST, '_wp-find-template');
    }

    if ( !$short ) {
        $short = bw_array_get( $_REQUEST, 'vfb-content');
    }

    if ( !$short ) {
        $short = bw_array_get( $_REQUEST, 'customize_changeset_uuid');
    }

    if ( $short ) {
      $ok = false;
    } else {
			$ok = true;
		}
	}
	return( $ok );
}

function bw_trace_purge_if_no_errors() {

	global $bw_trace;
	if ( $bw_trace ) {
		$bw_trace->purge_trace_file_if_no_errors();

	}

}


} /* end of first if defined() */
