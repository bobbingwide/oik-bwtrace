<?php // (C) Copyright Bobbing Wide 2015-2018
 
/**
 * Load the global bw_action_options
 * 
 * We should only need to do this once
 *
 */ 
function bw_action_options() { 
	global $bw_action_options;
	if ( !isset( $bw_action_options ) ) {
	  $bw_action_options = get_option( 'bw_action_options' );
	}
}

/**
 * Add a selected trace action
 * 
 * @param string $action the action hook e.g. 'wp'
 * @param string $option the option name e.g. 'trace_wp_rewrite'
 * @param string $file the implementing file e.g. 'includes/oik-actions.php'
 * @param string $function the implementing function e.g. 'bw_trace_wp_rewrite'
 * @param integer $count expected parameter count
 */
function bw_trace_add_action( $action, $option, $file, $function, $count=1 ) {
  bw_trace2( null, null, true, BW_TRACE_DEBUG );
	global $bw_action_options;
	$bw_trace_action = bw_array_get( $bw_action_options, $option, false );
	if ( $bw_trace_action ) {
	  if ( !function_exists( $function ) ) {
		  oik_require( $file, "oik-bwtrace" );
		}
		
		if ( function_exists( $function ) ) {
		  add_action( $action, $function, 10, $count );
		}	else {
			bw_trace2( $function, "Function does not exist", true, BW_TRACE_ERROR );
		}
	}
	return( $bw_trace_action );
}
	
/**
 * Add actions to trace selected actions
 * 
 * For 'wp' we can trace the WordPress instance passed and/or the wp_rewrite structure
 * 
 * 
 * At shutdown create a trace log of the following:
 *
 * - included files
 * - saved queries
 * - general status report
 * 
 * Note: The general status report should also be reportable back to the browser
 * even when trace is not being run but when the trace plugin is activated.
 * So it shouldn't be where it currently is. 
 */
function bw_trace_add_selected_actions() {
	bw_trace_add_action( "wp", "trace_wp_action", "includes/oik-actions.php", "bw_trace_wp" );
	bw_trace_add_action( "wp", "trace_wp_rewrite", "includes/oik-actions.php", "bw_trace_wp_rewrite" );
	//bw_trace_add_action( 'shutdown', 'trace_plugin_loaded', 'includes/oik-actions.php', 'bw_trace_plugin_loaded_report' );
	bw_trace_add_action( 'shutdown','trace_plugin_load', 'includes/oik-actions.php', 'bw_trace_plugin_loaded_report' );
	bw_trace_add_action( "shutdown", "trace_included_files", "includes/oik-actions.php", "bw_trace_included_files" );
	bw_trace_add_action( "shutdown", "trace_saved_queries", "includes/oik-actions.php", "bw_trace_saved_queries" );
	bw_trace_add_action( "shutdown", "trace_output_buffer", "includes/oik-actions.php", "bw_trace_output_buffer" );
	bw_trace_add_action( "shutdown", "trace_functions", "includes/oik-actions.php", "bw_trace_functions_traced" );
	bw_trace_add_action( "shutdown", "trace_status_report", "includes/oik-actions.php", "bw_trace_status_report" );
	bw_trace_add_action( 'shutdown', 'trace_purge_if_no_errors', 'includes/oik-actions.php', 'bw_trace_purge_if_no_errors' );




	/** 
	 * @TODO - Add option to trace all attached hooks at shutdown. 
	 * This logic is/was being used to investigate which hooks are attached when Gutenberg is running.
	 * add_action( "shutdown", "bw_trace_all_attached_hooks" );
	 */
	 
	/*
	 * These option names are not defined in the admin interface 
	 * so are not expected to be in the $bw_action_options array 
	 * If you want the hooks to be invoked then you either have to add the entries programmatically 
	 * or cheat by using an existing option name
	 */ 
	bw_trace_add_action( "shutdown", "trace_plugin_paths", "includes/oik-actions.php", "bw_trace_plugin_paths" );
	
	$bw_trace_action = bw_trace_add_action( "deprecated_constructor_run", "trace_deprecated", "includes/bwtrace-actions.php", "bw_trace_deprecated_constructor_run", 2 );
	if ( $bw_trace_action ) {
		add_action( "deprecated_constructor_run", "bw_trace_deprecated_constructor_run", 10, 2 ); // hack to get hook listed in WP-a2z 
		add_action( "deprecated_argument_run", "bw_trace_deprecated_argument_run", 10, 3 );
		add_action( "deprecated_file_included", "bw_trace_deprecated_file_included", 10, 4 );
		add_action( "deprecated_function_run", "bw_trace_deprecated_function_run", 10, 3 );
		add_action( "doing_it_wrong_run", "bw_trace_doing_it_wrong_run", 10, 3 );
		add_filter( "deprecated_argument_trigger_error", "bw_trace_deprecated_argument_trigger_error", 10 ); 
		add_filter( "deprecated_constructor_trigger_error", "bw_trace_deprecated_argument_trigger_error", 10 ); 
		add_filter( "deprecated_file_trigger_error", "bw_trace_deprecated_argument_trigger_error", 10 ); 
		add_filter( "deprecated_function_trigger_error", "bw_trace_deprecated_argument_trigger_error", 10 ); 
		add_filter( "doing_it_wrong_trigger_error", "bw_trace_deprecated_argument_trigger_error", 10 ); 
		
		// Quick tests - these should be commented out
    //_deprecated_argument( __FUNCTION__, "2.0.2", "just a test" );
		//_deprecated_file( __FILE__, "2.0.2", "another file", "just a test" );
		//_deprecated_function( __FUNCTION__, "2.0.3", "anotherfunc" );
		//_doing_it_wrong( __FUNCTION__, "you're doing it wrong", "2.0.3" );
	}
	
	bw_trace_add_error_handler();
	//$x.= "oops";
	
	bw_trace_add_trace_selected_hooks();
	bw_trace_add_trace_selected_filters();
	bw_trace_add_trace_selected_hooks_the_post();
	bw_trace_add_trace_selected_hooks_attached_hooks();
	bw_trace_add_trace_selected_hooks_backtrace();
	bw_trace_add_trace_stringwatch();
	//bw_trace_add_trace_anychange();
	bw_trace_add_trace_rest();
	bw_trace_http_raw_post_data();
	bw_trace_plugins_loaded();
}

/**
 * Perform a debug backtrace before reporting the deprecation
 * 
 * Notice: has_cap was called with an argument that is deprecated since version 2.0! 
 * Usage of user levels by plugins and themes is deprecated. Use roles and capabilities instead. 
 * in /home/cwiccer/public_html/wp-includes/functions.php on line 2712
 *
 *
 * @link http://tumbledesign.com/fix-notice-has_cap-was-called-with-an-argument-that-is-deprecated-since-version-2-0-in-wordpress/
 * @link http://masseltech.com/plugins/underconstruction/
 * @uses bw_backtrace() - from oik
 *
 * @param bool $trigger_error true if we want to trigger the error
 * @return bool the value passed in
 
 */
function bw_trace_deprecated_argument_trigger_error( $trigger_error=true ) {
  bw_backtrace();
  return( $trigger_error ); 
}

/**
 * Implement "deprecated_argument_run" action for oik-bwtrace
 *
 * @param string $function The function that was called
 * @param string $message A message regarding the change
 * @param string $version The version of WordPress that deprecated the argument used
 */
function bw_trace_deprecated_argument_run( $function=null, $message=null, $version=null) {
	bw_trace2( null, null, true, BW_TRACE_ERROR );
	bw_backtrace();
}


/**
 * Implement "deprecated_constructor_run" action for oik-bwtrace
 * 
 * @param string $class class name
 * @param string $version version
 */
function bw_trace_deprecated_constructor_run( $class=null, $version=null ) {
	bw_trace2( null, null, true, BW_TRACE_ERROR );
	bw_backtrace();
}

/**
 * Implement "deprecated_file_included" action for oik-bwtrace
 */
function bw_trace_deprecated_file_included( $file=null, $replacement=null, $version=null, $message=null ) {
	bw_trace2( null, null, true, BW_TRACE_ERROR );
	bw_backtrace();
}

 
/**
 * Implement "deprecated_function_run" action for oik-bwtrace
 *
 * @param string $function The function that was called
 * @param string $replacement The function that should have been called
 * @param string $version The version of WordPress that deprecated the function
 */
function bw_trace_deprecated_function_run( $function=null, $replacement=null, $version=null) {
	bw_trace2( null, null, true, BW_TRACE_ERROR );
	bw_backtrace();
}

/**
 * Implement "doing_it_wrong_run" action for oik-bwtrace
 *
 * @param string $function The function that was called
 * @param string $message A message regarding the change
 * @param string $version The version of WordPress that deprecated the function
 */
function bw_trace_doing_it_wrong_run( $function=null, $message=null, $version=null) {
	bw_trace2( null, null, true, BW_TRACE_ERROR );
	bw_backtrace();
}

/**
 * Add our error handler for Notice and Warning messages
 *
 * Note: This function may replace an error handler that has been attached by another plugin.
 * e.g. query-monitor.
 */
function bw_trace_add_error_handler() {
	global $bw_action_options;
	$bw_trace_errors = bw_array_get( $bw_action_options, "trace_errors", false );
	if ( $bw_trace_errors ) {
		$previous_error_handler = set_error_handler( "bw_trace_error_handler" );
		bw_trace2( $previous_error_handler, "Previous error handler", false, BW_TRACE_DEBUG );
  }
}


/** 
 * Return string representation of the $errno 
 * 
 * | $errno | constant | string |
 * | --- | --- | --- |
 * | 1 | E_ERROR | Error |
 * | 2 | E_WARNING | Warning |
 * | 4 | E_PARSE | Parsing Error |
 * | 8 | E_NOTICE | Notice |
 * | 16 | E_CORE_ERROR | Core Error |
 * | 32 | E_CORE_WARNING | Core Warning |
 * | 64 | E_COMPILE_ERROR | Compile Error |
 * | 128 | E_COMPILE_WARNING | Compile Warning |
 * | 256 | E_USER_ERROR | User Error |
 * | 512 | E_USER_WARNING | User Warning |
 * | 1024 | E_USER_NOTICE | User Notice |
 * | 2048 | E_STRICT | Runtime Notice |
 * | 4096 | E_RECOVERABLE_ERROR | Catchable Fatal Error |
 * | 8192 | E_DEPRECATED | Deprecated |
 * | 16384 | E_USER_DEPRECATED | User Deprecated |
 *
 * @param integer $errno
 * @return string the string representation
 */
function bw_trace_errno( $errno ) {
	$strings = array( E_ERROR => "Error"
 , E_WARNING => "Warning"
 , E_PARSE => "Parsing Error"
 , E_NOTICE => "Notice"
 , E_CORE_ERROR => "Core Error"
 , E_CORE_WARNING => "Core Warning"
 , E_COMPILE_ERROR => "Compile Error"
 , E_COMPILE_WARNING => "Compile Warning"
 , E_USER_ERROR => "User Error"
 , E_USER_WARNING => "User Warning"
 , E_USER_NOTICE => "User Notice"
 , E_STRICT => "Runtime Notice"
 , E_RECOVERABLE_ERROR => "Catchable Fatal Error"
 , E_DEPRECATED => "Deprecated"
 , E_USER_DEPRECATED => "User Deprecated"
 );
	$string = bw_array_get( $strings, $errno, $errno );
	return( $string );
}
	

/**
 * Trace catchable errors
 *
 *
 * Extract from PHP manual.
 * `If the function returns FALSE then the normal error handler continues.`
 * 
 * It doesn't tell us what the "normal error handler" is though.
 * 
 * If we return true then we can log some of the errors and processing will continue.
 * But we can't do this for E_ERROR - since it's a Fatal error which we don't get to see.
 * 
 * If we return false then the standard PHP handler will most likely terminate the process
 * for anything more serious than a Warning. 
 * 
 * @return bool Always false for the time being. We want the developer to be aware of the message.
 */
function bw_trace_error_handler( $errno, $errstr, $errfile=null, $errline=null, $errcontext=null ) {
	$err_string = bw_trace_errno( $errno );
	$err_string .= ": ";
	$err_string .= $errstr;
	$err = array( $errno, $err_string, $errfile, $errline );
	bw_trace2( $err, "err", false, BW_TRACE_ERROR );
	bw_trace2( $errcontext, "errcontext", false, BW_TRACE_VERBOSE );
	//bw_trace( "errno", __FUNCTION__, __LINE__, __FILE__, $errno);
	//bw_trace( "errstr", __FUNCTION__, __LINE__, __FILE__, $errstr );
	//bw_trace( "errfile", __FUNCTION__, __LINE__, __FILE__, $errfile );
	//bw_trace( "errline", __FUNCTION__, __LINE__, __FILE__, $errline );
	//echo "<!-- bw_trace_error_handler $errno $errstr, $errfile,$errline -->" . PHP_EOL;
	bw_backtrace();
	return( false );
}

/**
 * Trace selected hooks 
 * 
 * Hooks that you might be interested in are many and varied.
 * You can find which hooks are invoked by enabling 'Count action hooks and filters'
 * then type the hook into the text area to find out more.
 * 
 */
function bw_trace_add_trace_selected_hooks() {
  global $bw_action_options;
	$selected_hooks = bw_array_get( $bw_action_options, "hooks", null ); 
	if ( $selected_hooks ) {
		bw_trace_add_filters( $selected_hooks, "bw_trace_parms", 0, 9 ); 
	}
}

/**
 * Add the filter function for the selected hooks
 */
function bw_trace_add_filters( $selected_hooks, $filter_func, $priority=10, $accepted_args=9 ) {
	oik_require_lib( "bobbfunc" );
	$hooks = bw_as_array( $selected_hooks );
	foreach ( $hooks as $hook ) {
		bw_trace_add_filter( $hook, $filter_func, $priority, "$accepted_args" );
	}
}

/**
 * Add the filter function for the selected hook and priority
 * 
 */
function bw_trace_add_filter( $selected_hook, $filter_func, $priority=10, $accepted_args=9 ) {
	$selected_hook = str_replace( ";", ":", $selected_hook );
	$hook = explode( ":", $selected_hook );
	if ( isset( $hook[1] ) ) {
		$priority	= $hook[1];
	}
	add_filter( $hook[0], $filter_func, $priority, $accepted_args );
}

/**
 * Trace the parameters passed to the hook
 *
 * This function allows for an action hook that's passed 0 args. Is this possible?
 *
 * @param mixed $arg the parameter that must be returned for a filter hook
 * @return mixed the arg that was passed
 */
function bw_trace_parms( $arg=null ) {
	$num = func_num_args();
	$args = func_get_args();
	//if ( 1 === $num ) {
	//	$args = $args[0];
	//}
	bw_trace2( $args, "parameters: $num:", null, BW_TRACE_ALWAYS );
	return( $arg );
}

/**
 * Trace selected results 
 * 
 * Filter hooks that you might be interested in are many and varied.
 *
 * You can find which hooks are invoked by enabling 'Count action hooks and filters'
 * then type the hook into the text area to find out more.
 * 
 * Here we register the filter hook with a high value for priority
 * 
 */
function bw_trace_add_trace_selected_filters() {
  global $bw_action_options;
	$selected_hooks = bw_array_get( $bw_action_options, "results", null ); 
	if ( $selected_hooks ) {
		bw_trace_add_filters( $selected_hooks, "bw_trace_results", 9999, 9 ); 
	}
}

/**
 * Trace the results from filtering
 *
 * Note: We also trace the parameters passed in so you don't necessarily 
 * need to trace parms.
 *
 * @param mixed $arg the parameter that must be returned for a filter hook
 * @return mixed the arg that was passed
 */
function bw_trace_results( $arg=null ) {
	$num = func_num_args();
	$args = func_get_args();
	$priority = bw_trace_inspect_current(); 
	bw_trace2( $args, "results: $priority,$num:", false, BW_TRACE_DEBUG );
	bw_trace2( $arg, "return", false, BW_TRACE_ALWAYS );
	//bw_backtrace( BW_TRACE_DEBUG );
	return( $arg );
}

/**
 * Finds the current hook's priority
 * 
 * - Originally implemented with a super hack that relied on the fact that you can define accepted args as a decimal value
 * - I appended the priority to the accepted args, which is always 9.
 * - @lgedeon suggested an improvement to this as a solution for TRAC #33886
 * - But this stopped working for WordPress 4.7
 * - Now we can just use the method that WordPress provides in the WP_Hook class
 * 
 * @return integer the priority of the current hook
 */
function bw_trace_inspect_current() {
	global $wp_filter;
	$tag = current_filter();
	$current = $wp_filter[ $tag ];
	$priority = $current->current_priority();
	return $priority;
}

/**
 * Add selected hooks to trace the values in the global post
 *
 * Note: Don't trace "the_posts" ... it's too early - global $post won't be set
 */
function bw_trace_add_trace_selected_hooks_the_post() {
	global $bw_action_options;
	$selected_hooks = bw_array_get( $bw_action_options, "post_hooks", null ); 
	if ( $selected_hooks ) {
		bw_trace_add_filters( $selected_hooks, "bw_trace_the_post", 0, 9 ); 
	}
}

/**
 * Add selected hooks to trace the attached hooks
 *
 */
function bw_trace_add_trace_selected_hooks_attached_hooks() {
	global $bw_action_options;
	$selected_hooks = bw_array_get( $bw_action_options, "hook_funcs", null ); 
	if ( $selected_hooks ) {
		bw_trace_add_filters( $selected_hooks, "bw_trace_attached_hooks", 0, 9 ); 
	}
}

/**
 * Add selected hooks to debug backtrace
 */
function bw_trace_add_trace_selected_hooks_backtrace() {
	global $bw_action_options;
	$selected_hooks = bw_array_get( $bw_action_options, "backtrace", null ); 
	if ( $selected_hooks ) {
		bw_trace_add_filters( $selected_hooks, "bw_trace_backtrace", 0, 9 ); 
	}
}

/**
 * Add stringwatch logic if required
 *
 * Since this won't get used often it's in a separate file
 * ... that might get loaded earlier by the MU plugin
 * 
 */
function bw_trace_add_trace_stringwatch() {
	global $bw_action_options;
	$stringwatch = bw_array_get( $bw_action_options, "stringwatch", null ); 
	if ( $stringwatch || defined( "BW_TRACE_STRINGWATCH" ) ) {
		oik_require( "includes/bwtrace-stringwatch.php", "oik-bwtrace" );
	}
}

/**
 * Adds anychange logic if required
 */
function bw_trace_add_trace_anychange() {
	global $bw_action_options;
	$anychange = bw_array_get( $bw_action_options, "anychange", null );
	if ( $anychange || defined( "BW_TRACE_ANYCHANGE" ) ) {
		oik_require( "includes/bwtrace-anychange.php", "oik-bwtrace" );
	}
}

/**
 * Adds logic to trace the REST result
 */
function bw_trace_add_trace_rest() {
	add_filter( "rest_pre_echo_response", "bw_trace_rest_pre_echo_response", 9999, 3 );
}

function bw_trace_plugins_loaded() {
	global $bw_trace_plugins_loaded;
	//global $bw_trace_plugins_loaded_unkeyed;
	$bw_trace_plugins_loaded = [ 'REQUEST_TIME_FLOAT' =>$_SERVER['REQUEST_TIME_FLOAT'], 'WP_START_TIMESTAMP' => WP_START_TIMESTAMP ];
	$bw_trace_plugins_loaded['BW_TRACE_START_TIMESTAMP'] = BW_TRACE_START_TIMESTAMP;
	//$bw_trace_plugins_loaded_unkeyed = [];
	//$bw_trace_plugins_loaded_unkeyed = ['REQUEST_TIME_FLOAT', $_SERVER['REQUEST_TIME_FLOAT'] ];
	//$bw_trace_plugins_loaded_unkeyed = [ 'WP_START_TIMESTAMP', WP_START_TIMESTAMP ];
	add_action( "plugin_loaded", 'bw_trace_time_plugin_loaded',-9999);
	add_action( 'mu_plugin_loaded', 'bw_trace_time_plugin_loaded',-9999);
	add_action( 'network_plugin_loaded', 'bw_trace_time_plugin_loaded',-9999);

	add_action( 'plugins_loaded', 'bw_trace_time_plugin_loaded',-9999);
	// This hook caters for network activated plugins as well.
	add_action( 'muplugins_loaded', 'bw_trace_time_plugin_loaded',-9999);
}

/**
 * Records the time the plugin was loaded.
 *
 * If BW_TRACE_VERBOSE then the trace record is also produced.
 * This can show files loaded and other stuff such as memory usage.
 *
 * @param null $plugin
 */
function  bw_trace_time_plugin_loaded( $plugin=null ) {
	global $bw_trace_plugins_loaded;
	//global $bw_trace_plugins_loaded_unkeyed;
	if ( '' === $plugin ) {
		$plugin = bw_current_filter();
	}
	$microtime = microtime( true );
	$bw_trace_plugins_loaded[ $plugin ] = $microtime;
	//$bw_trace_plugins_loaded_unkeyed[] = [ $plugin, $microtime ];

	bw_trace2( null, null, true, BW_TRACE_VERBOSE );
}



/**
 * Traces the REST result.
 *
 * When plugins such as WordPress SEO are activated the trace output can be huge.
 * I have decided to disable the trace logic for the individual parameters
 * to avoid out of memory situations, which will lead to 500 internal errors,
 * and which may subsequently cause the block editor to fail.
 *
 * The previous comment was:
 * - The server and request objects are very large so are only traced at verbose level.
 * - The result can also be large. Until there's finer control this is only traced at debug level.
 * 
 * @param array            $result  Response data to send to the client.
 * @param WP_REST_Server   $server  Server instance.
 * @param WP_REST_Request  $request Request used to generate the response.
 */
function bw_trace_rest_pre_echo_response( $result, $server, $request ) {
	bw_trace_http_raw_post_data();
	//bw_trace2( $result, "result", false, BW_TRACE_VERBOSE );
	//bw_trace2( $server, "server", false, BW_TRACE_DEBUG );
	//bw_trace2( $request, "request", false, BW_TRACE_VERBOSE );
	if ( BW_TRACE_VERBOSE === bw_trace_level() ) {
		$json_encoded=wp_json_encode( $result, JSON_UNESCAPED_SLASHES );
		bw_trace2( $json_encoded, "json_encoded result", false, BW_TRACE_VERBOSE );
	}
	return $result;
}

function bw_trace_http_raw_post_data() {
	global $HTTP_RAW_POST_DATA;
	bw_trace2( $HTTP_RAW_POST_DATA, "HTTP_RAW_POST_DATA", false );
}

/**
 * Trace the global post object
 *
 * Print the contents of the post object
 * 
 * @param mixed $arg the first parameter to the hook has to be returned
 * @return mixed the value passed in
 */
function bw_trace_the_post( $arg=null ) {
	global $post;
	bw_trace2( $post, "global post", false, BW_TRACE_ALWAYS );
	return( $arg );
}

/**
 * Trace the attached hooks for the given hook
 *
 * Note: The result set is expected to include ourselves
 * 
 * @param mixed $arg the value to return, if it's a filter
 * @return mixed the $arg that was passed
 *
 */
function bw_trace_attached_hooks( $arg ) {
	$cf = current_filter(); 
	bw_trace2( $cf, "current filter", true, BW_TRACE_DEBUG );
	$hooks = bw_trace_get_attached_hooks( $cf );
	bw_trace2( $hooks, $cf, false, BW_TRACE_ALWAYS );
	return( $arg );
}

/**
 * Traces all attached hooks
 */
function bw_trace_all_attached_hooks() {
	global $wp_filter;
	$count = count( $wp_filter );
	$hooks = array();
	foreach ( $wp_filter as $tag => $hook_object ) {
		$hooks[ $tag ] = bw_trace_get_attached_hooks( $tag );
	}
	bw_trace2( $hooks, "All $count hooks" );
}

/**
 * Return the attached hooks 
 *
 * Reduce the $wp_filter[ $tag ] structure
 * to something a little easier to interpret
 * e.g. For the "wp_default_styles" hook it will be
 *
 * `
 * : 0   bw_trace_attached_hooks;1
 * : 10   wp_default_styles;1
 * ` 
 *
 * Note: We can't use foreach against $wp_filter[ $tag ] since this
 * moves the current pointer to the end of the array
 * and messes up further filter functions. We need to work on a copy.
 *
 * See {@link http://php.net/manual/en/control-structures.foreach.php}
 *
 * @param string $tag the action hook or filter
 * @return string the attached hook information
 *
 */
function bw_trace_get_attached_hooks( $tag ) {
	global $wp_filter; 
  if ( isset( $wp_filter[ $tag ] ) ) {
		$current_hooks = $wp_filter[ $tag ];
		bw_trace2( $current_hooks, "current hooks for $tag", false, BW_TRACE_VERBOSE );
		$hooks = null;
		foreach ( $current_hooks as $priority => $functions ) {
			$hooks .= "\n: $priority  ";
			foreach ( $functions as $index => $args ) {
				$hooks .= " ";
				if ( is_object( $args['function' ] ) ) {
					$object_name = get_class( $args['function'] );
					$hooks .= $object_name; 

				} elseif ( is_array( $args['function'] ) ) {
					//bw_trace2( $args, "args" );
					if ( is_object( $args['function'][0] ) ) { 
						$object_name = get_class( $args['function'][0] );
					}	else {
						$object_name = $args['function'][0];
					}
					$hooks .= $object_name . '::' . $args['function'][1];
				} else {
					$hooks .= $args['function'];
				}
				$hooks .= ";" . $args['accepted_args'];
			}
		}
	} else {
		$hooks = null;
	}
	//bw_trace2( $hooks, "hooks", true, BW_TRACE_ALWAYS );
	return( $hooks ); 
}


/**
 * Backtrace the selected hook
 *
 * We call bw_trace() in order to get the context.
 *
 * @param mixed $arg the value to return
 * @return mixed the parameter passed
 */
function bw_trace_backtrace( $arg ) {
	bw_trace2();
	bw_backtrace();
	return( $arg );
}




