<?php // (C) Copyright Bobbing Wide 2014, 2015

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
	bw_trace2( $target, "target dir" );
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
		bw_trace2( "Not a dir?" );
	}
}

/**
 * Turn on action counting 
 */
function bw_trace_count_on() {
  global $bw_count_on;
  global $bw_action_counts; 
  if ( !isset( $bw_action_counts ) ) {
    $bw_action_counts = array();
		bw_trace2( "reset bw_action_counts" );
  }
  $bw_count_on = true;
}

/**
 * Turn off action counting
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
  bw_trace2();
  add_action( "all", "bw_trace_count_all", 10, 2 );
  add_action( "shutdown", "bw_trace_count_report" ); 
}

/**
 * Count every action and filter hook
 * 
 * WordPress itself counts the number of times an action is performed
 * in order to be able to report true on did_action()
 * We want to know about filters as well.
 * 
 * This new logic ( Aug 2014 ) is significantly faster than the orignal action tracing, which wrote each action to the action log.
 * 
 */
function bw_trace_count_all( $tag, $args2=null ) {
  global $bw_action_counts; 
  if ( !isset( $bw_action_counts[ $tag ] ) ) {
    $bw_action_counts[ $tag ] = 1;
  } else {
    $bw_action_counts[ $tag ] += 1; 
  }
	
	/* 
	 * We also want to record the actions showing the hierarchy of hooks
	 *
	 */
	global $wp_current_filter;
	global $bw_action_counts_tree;
	$filters = implode( ",",  $wp_current_filter );
	
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
 * 3. merged - the result of merging wp_actions with bw_actions_counts - where figures from wp_action are more correct
 *    From this output you should be able to work out the main sequence of events... but you have to ignore the
      commonly used filters. 
 
 * 4. merged sorted by most used - to show how much work WordPress is doing
 * 5. merged sorted by name.
 * 6. Count of the number of unique hooks invoked
 * 7. Count of the total number of action/filter hooks invoked.
 * 8. First view of the counts taking into account nested actions and filters  
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
	
		//bw_trace( $merged, __FUNCTION__, __LINE__, __FILE__, "merged" );
		arsort( $merged );
		bw_trace( $merged, __FUNCTION__, __LINE__, __FILE__, "merged - most used" );
	
		ksort( $merged );
		bw_trace( $merged, __FUNCTION__, __LINE__, __FILE__, "merged - by hook name" );
	
		bw_trace( count( $merged), __FUNCTION__, __LINE__, __FILE__, "merged - count hooks" );
		bw_trace( array_sum( $merged), __FUNCTION__, __LINE__, __FILE__, "merged - total hooks" );
	}
	global $bw_action_counts_tree;
	bw_trace( $bw_action_counts_tree, __FUNCTION__, __LINE__, __FILE__, "action counts tree" );
	
  bw_trace_create_hook_links( $bw_action_counts_tree, "bw_action_counts_tree" );
	
  
}

/**
 * Create the HTML for a hook links section for "Request summary"
 * 
 * @param array $action_counts - array of action counts, which may also contain filter counts
 * @param string $heading - a heading for this sections
 * 
 */
function bw_trace_create_hook_links( $action_counts, $heading ) {
  $hook_links = "<h3>$heading</h3>";
  $hook_links .= "[bw_csv]Hook,Invoked";
	if ( count( $action_counts ) ) {
		foreach ( $action_counts as $hook => $count ) {
			$hook_links .= PHP_EOL;
		 $hook_links .= "[hook $hook],$count";
		}
	}
  $hook_links .= "[/bw_csv]";
  bw_trace2( $hook_links, "hook_links", false ); 

}
 
/**
 * Implement "plugins_loaded" for oik-bwtrace
 * 
 * Start the trace count logic if required
 * 
 * @TODO - it would be a lot nicer if we could start counting actions from the first time
 * one is invoked. To achieve this we probably need to create an MU plugin
 * and make it respond to 'muplugins_loaded'.
 * The MU plugin should be responsible for loading the relevant parts of oik and oik-bwtrace 
 *
 * Functions that can be called when loading oik-bwtrace from wp-config AND you want to count ALL the actions hooks and filters
 *
 * db.php is the WordPress drop-in plugin that gets invoked primarily to alter or control the database access
 * But it's not until this stage in the processing that "add_action" is available.
 * So we have to defer the registration of our action hooks until here at least.
 */
function bw_trace_count_plugins_loaded( $trace_count=false ) {
  //bw_backtrace();
  //bw_trace2();
	//bw_trace_count_report();
  if ( !$trace_count ) {
		$bw_action_options = get_option( 'bw_action_options' );
		$trace_count = bw_array_get( $bw_action_options, "count", false );
	}
  if ( $trace_count ) {
    //oik_require( "includes/oik-actions.php", "oik-bwtrace" );
    bw_trace_count_on();
    bw_lazy_trace_count();
  } else {
    if ( is_callable( "bw_trace_count_off" ) ) {
      bw_trace_count_off();
    }  
  }
}
