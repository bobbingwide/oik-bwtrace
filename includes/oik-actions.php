<?php // (C) Copyright Bobbing Wide 2014

/**
 * Turn on action counting 
 */
function bw_trace_count_on() {
  global $bw_count_on;
  global $bw_action_counts; 
  if ( !isset( $bw_action_counts ) ) {
    $bw_action_counts = array();
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
  add_action( "all", "bw_trace_count_all" );
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
}

/**
 * Report trace action count on shutdown
 * 
 * Here we produce the output multiple times.
 * 1. WordPress wp_actions table - which will include some actions invoked before oik-action processing was activated
 * 2. bw_action_counts - the actions and filters invoked since oik-action counting was activated
 * 3. merged - the result of merging wp_actions with bw_actions_counts - where figures from wp_action are more correct
 *    From this output you should be able to work out the main sequence of events... but you have to ignore the
      commonly used filters. 
 
 * 4. merged sorted by most used - to show how much work WordPress is doing
 * 5. merged sorted by name.
 * Finally we produce counts of the number of unique hooks invoked
 * and the the total number of action/filter hooks invoked.
 *   
 */
function bw_trace_count_report() {
  global $wp_actions;
  global $bw_action_counts;
  bw_trace( $wp_actions, __FUNCTION__, __LINE__, __FILE__, "wp_actions" );
  bw_trace( $bw_action_counts, __FUNCTION__, __LINE__, __FILE__, "bw_action_counts" );
  bw_trace_create_hook_links( $wp_actions, "wp_actions" );
  bw_trace_create_hook_links( $bw_action_counts, "bw_action_counts" );
  $merged = array_merge( $bw_action_counts, $wp_actions );
  bw_trace( $merged, __FUNCTION__, __LINE__, __FILE__, "merged" );
  arsort( $merged );
  bw_trace( $merged, __FUNCTION__, __LINE__, __FILE__, "merged - most used" );
  ksort( $merged );
  bw_trace( $merged, __FUNCTION__, __LINE__, __FILE__, "merged - by hook name" );
  bw_trace( count( $merged), __FUNCTION__, __LINE__, __FILE__, "merged - count hooks" );
  bw_trace( array_sum( $merged), __FUNCTION__, __LINE__, __FILE__, "merged - total hooks" );
  
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
  foreach ( $action_counts as $hook => $count ) {
    $hook_links .= PHP_EOL;
    $hook_links .= "[hook $hook],$count";
  }
  $hook_links .= "[/bw_csv]";
  bw_trace2( $hook_links, "hook_links", false ); 

}
