<?php // (C) Copyright Bobbing Wide 2014-2016

/**
 * Activate / deactivate _oik-bwtrace-mu processing
 *
 * Note: WPMU_PLUGIN_DIR may not have been defined if we're invoked from wp-config.php
 * but ABSPATH should have been.
 *
 * The MU plugin name is prefixed with an underscore so that it's loaded very early.
 * Alternatively, we could have called the file 01K-bwtrace-mu or 0ik-bwtrace-mu
 * and that might have worked too.
 *
 * @param bool $activate true to activate, false to deactivate
 */
function bw_trace_activate_mu( $activate=true ) {
	$source = oik_path( 'includes/_oik-bwtrace-mu.php', "oik-bwtrace" );
	if ( defined( 'WPMU_PLUGIN_DIR' ) ) {
		$target = WPMU_PLUGIN_DIR;
	} else {
		$target = ABSPATH . '/wp-content/mu-plugins';
	}
	bw_trace2( $target, "target dir", true, BW_TRACE_DEBUG );
	//var_dump( debug_backtrace() );
	//echo "Target: $target";
	if ( is_dir( $target ) ) {
		$target .= "/_oik-bwtrace-mu.php";
		if ( $activate ) {
			if ( !file_exists( $target ) ) {
				copy( $source, $target );
			}
		} else {
			if ( file_exists( $target ) ) {
				unlink( $target );
			} 
		}
	} else {
		// Do we need to make this ourselves?
		bw_trace2( $target, "Not a dir?", true, BW_TRACE_ERROR );
    //gobang();
	}
}

/**
 * Turn on action hook and filter counting 
 * 
 * 
 */
function bw_trace_count_on() {
  global $bw_count_on;
  global $bw_action_counts; 
  if ( !isset( $bw_action_counts ) ) {
    $bw_action_counts = array();
		bw_trace2( "reset bw_action_counts", null, false, BW_TRACE_VERBOSE );
  }
  $bw_count_on = true;
}

/**
 * Turn off action hook and filter counting
 */
function bw_trace_count_off() {
  global $bw_count_on;
  $bw_count_on = false;
  remove_action( "all", "bw_trace_count_all" );
}

/**
 * Prepare for counting action hooks and filters 
 *
 */
function bw_lazy_trace_count() {
	bw_trace2( "Initialising action counts", null, false, BW_TRACE_VERBOSE );
	oik_require( "includes/oik-actions.php", "oik-bwtrace" );
	add_action( "all", "bw_trace_count_all", 10, 2 );
	add_action( "shutdown", "bw_trace_count_report" ); 
}

/**
 * Implement 'wp' hook to see if we should be tracing 'wp'
 * 
 * This wasn't necessary! 
 */
function bw_trace_wp_early( $WP_Environment_Instance ) {
	oik_require( "includes/bwtrace-actions.php", "oik-bwtrace" );
	bw_action_options();
	bw_trace_add_selected_actions();
}

/**
 * Count every action and filter hook
 * 
 * WordPress itself counts the number of times an action is performed in order to be able to report true on did_action().
 * We want to know about filters as well.
 * 
 * This new logic ( Aug 2014 ) is significantly faster than the orignal action tracing, which wrote each action to the action log.
 * The additional logic ( May/June 2015 ) helps us determine the nesting.
 * Sep 2015 - added ability to record the number of parameters passed. 
 * We subtract 1 from the number of args passed to this function; it gives the value needed when registering an action hook or filter.
 * 
 * @param string $tag the action or filter hook
 * @param array $args2 - doesn't matter - we use func_num_args()
 */
function bw_trace_count_all( $tag, $args2=null ) {
  global $bw_action_counts; 
	global $bw_action_parms;
  if ( !isset( $bw_action_counts[ $tag ] ) ) {
    $bw_action_counts[ $tag ] = 1;
		$bw_action_parms[ $tag ] = func_num_args() - 1;
  } else {
    $bw_action_counts[ $tag ] += 1; 
  }
	
	/* 
	 * We also want to record the actions showing the hierarchy of hooks
	 * Assuming no one uses semi-colons in hook names!
	 *
	 */
	global $wp_current_filter;
	global $bw_action_counts_tree;
	$filters = implode( ";",  $wp_current_filter );
	
  if ( !isset( $bw_action_counts_tree[ $filters ] ) ) {
    $bw_action_counts_tree[ $filters ] = 1;
  } else {
    $bw_action_counts_tree[ $filters ] += 1; 
  }
	 
}

/**
 * Report trace action count on shutdown
 * 
 * Here we produce the output multiple times.
 * 1. WordPress wp_actions table - which may include some actions invoked before oik-action processing was activated
 * 2. bw_action_counts - the actions and filters invoked since oik-action counting was activated
 * 3. bw_action_counts sorted by most used - to show how much work WordPress is doing
 * 4. bw_action_counts sorted by name.
 * 5. Count of the number of unique hooks invoked
 * 6. Count of the total number of action/filter hooks invoked.
 * 7. First view of the counts taking into account nested actions and filters  
 */
function bw_trace_count_report() {
  global $wp_actions;
  global $bw_action_counts;
  //bw_trace( $wp_actions, __FUNCTION__, __LINE__, __FILE__, "wp_actions" );
  //bw_trace( $bw_action_counts, __FUNCTION__, __LINE__, __FILE__, "bw_action_counts" );
  bw_trace_create_hook_links( $wp_actions, "wp_actions" );
  bw_trace_create_hook_links( $bw_action_counts, "bw_action_counts" );
	
  //$merged = array_merge( $bw_action_counts, $wp_actions );
	if ( count( $bw_action_counts ) ) {
		$merged = $bw_action_counts;
	
		arsort( $merged );
		//bw_trace( $merged, __FUNCTION__, __LINE__, __FILE__, "most used" );
		bw_trace_create_hook_links( $merged, "most used", true );
	
		ksort( $merged );
		//bw_trace( $merged, __FUNCTION__, __LINE__, __FILE__, "by hook name" );
		bw_trace_create_hook_links( $merged, "by hook name" );
	
		bw_trace( count( $merged), __FUNCTION__, __LINE__, __FILE__, "count hooks" );
		bw_trace( array_sum( $merged), __FUNCTION__, __LINE__, __FILE__, "total hooks" );
	}
	global $bw_action_counts_tree;
	//bw_trace( $bw_action_counts_tree, __FUNCTION__, __LINE__, __FILE__, "action counts tree" );
	
  bw_trace_create_hook_links( $bw_action_counts_tree, "bw_action_counts_tree" );
 
}

// Moved bw_trace_get_hook_type to includes\oik-actions.php

/**
 * Return the hook param count
 *
 * It should not be ?
 * 
 * @param $hook	the hook name
 * @return integer
 */
function bw_trace_get_hook_num_args( $hook ) {
	global $bw_action_parms;
	if ( isset( $bw_action_parms[ $hook ] ) ) {
		$num_args = $bw_action_parms[ $hook ];
		//if ( 0 === $num_args ) {
		//	$num_args = '';
		//}
  } else {
		$num_args = '?'; 
	}
	return( $num_args );
}

/**
 * Create the HTML for a hook links section for "Request summary"
 * 
 * @param array $action_counts - array of action counts, which may also contain filter counts
 * @param string $heading - a heading for this section
 * @param bool $implemented - restrict output to hooks which are implemented
 */
function bw_trace_create_hook_links( $action_counts, $heading, $implemented=false ) {
	$hook_links = "<h3>$heading</h3>";
	$hook_links .= bw_trace_get_hook_links( $action_counts, $implemented );
	bw_trace2( $hook_links, "hook_links", false ); 
}

/**
 * Return the number of attached hooks
 *
 * If we know that there are attached hooks then we can probably
 * save some time by removing them. It depends what they do.
 * You can find out more about the attached hooks by using ad hoc trace
 *  
 * @param string $hook
 * @return integer the number of attached hook functions
 */
function bw_trace_get_attached_hook_count( $hook ) {
	$count_hooks = 0;
	global $wp_filter;
	$hooks = bw_array_get( $wp_filter, $hook, null );
	if ( $hooks ) {
		$hooks = $hooks->callbacks;
	} else {
		$hooks = array();
	}
	if ( is_array( $hooks ) ) { 
		$count_hooks = count( $hooks );
	}	
	if ( $count_hooks ) {
		$count_hooks = 0;
		foreach ( $hooks as $priority => $functions ) {
			$count_hooks += count( $functions );
		}
	}
	return( $count_hooks );
}

/**
 * Return the [hook] links shortcodes
 * 
 * The implemented parameter allows us to reduce the output to only those hooks where
 * an action hook is implemeted. This makes it a lot easier to find things that actually do things.
 *
 * @param array $action_counts
 * @param bool $implemented true to restricted output to hooks which are implemented
 * @return string bw_csv shortcode with hook link shortcode
 */
function bw_trace_get_hook_links( $action_counts, $implemented=false ) {
	//$hook_links = "[bw_csv]Hook,Invoked";
	$hook_links = null;
	$type = null;
	$num_args = null;
	if ( count( $action_counts ) ) {
		foreach ( $action_counts as $hook => $count ) {
			$hooks = explode( ";", $hook );
			$end_hook = end( $hooks );
			$attached = bw_trace_get_attached_hook_count( $end_hook ); 
			if ( $attached || ( false === $implemented ) ) {
				$type = bw_trace_get_hook_type( $end_hook );
				$num_args = bw_trace_get_hook_num_args( $end_hook );
				$hook_links .= PHP_EOL;
				$hook_links .= "[hook $hook $type $num_args $count $attached]";
			}	
		}
	}
	//$hook_links .= "[/bw_csv]";
	return( $hook_links );
}	

/**
 * Implement "plugins_loaded" for oik-bwtrace
 * 
 * Start the trace count logic if required
 * 
 * @TODO - Review these comments for validity
 *
 * it would be a lot nicer if we could start counting actions from the first time
 * one is invoked. To achieve this we probably need to create an MU plugin
 * and make it respond to 'muplugins_loaded'.
 * The MU plugin should be responsible for loading the relevant parts of oik and oik-bwtrace 
 *
 * Functions that can be called when loading oik-bwtrace from wp-config AND you want to count ALL the actions hooks and filters
 *
 * db.php is the WordPress drop-in plugin that gets invoked primarily to alter or control the database access.
 * But it's not until this stage in the processing that "add_action" is available.
 * So we have to defer the registration of our action hooks until here at least.
 *
 *
 * @param bool $count_hooks true if counting is required, false otherwise
 */
function bw_trace_count_plugins_loaded( $count_hooks=false ) {
  //bw_backtrace();
	global $bw_action_options;
  bw_trace2( $count_hooks, "count_hooks", false, BW_TRACE_DEBUG );
	//bw_trace_count_report();
  if ( !$count_hooks ) {
		//$bw_action_options = get_option( 'bw_action_options' );
		$count_hooks = bw_array_get( $bw_action_options, "count", false );
	}
  if ( $count_hooks ) {
    //oik_require( "includes/oik-actions.php", "oik-bwtrace" );
    bw_trace_count_on();
    bw_lazy_trace_count();
  } else {
    if ( is_callable( "bw_trace_count_off" ) ) {
      bw_trace_count_off();
    }  
  }
}
