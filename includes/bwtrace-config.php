<?php // (C) Copyright Bobbing Wide 2013-2016
/**
 * Implement (lazy) trace startup from wp-config.php
 * 
 * We cater for the follow defined constants
 * - define( 'BW_TRACE_ON', true );
 * - define( 'BW_COUNT_ON', true );
 * - define( 'BW_TRACE_RESET', true );
 *
 * As of v1.25, these constants are deprecated 
 * - define( 'BW_ACTIONS_ON', true );
 * - define( 'BW_ACTIONS_RESET', true );
 *
 * In the future we may cater for advanced caching if WP_CACHE is defined ( Issue #21 )
 */
function bw_lazy_trace_config_startup() {
     
  if ( defined( 'BW_TRACE_ON' ) ) {
    $trace_on = BW_TRACE_ON;
  } else {
    $trace_on = false;
	}
	
  if ( defined( 'BW_COUNT_ON' ) ) {
    $count_on = BW_COUNT_ON;
  } else {
    $count_on = false;
	}
    
  if ( defined( 'BW_TRACE_RESET' ) ) {
    $trace_reset = BW_TRACE_RESET;
  } else {
    $trace_reset = false;
	}
	
	/**
	 * If advanced-cache.php is being used then we may not get a chance to trace anything on shutdown
	  
	if ( defined( 'BW_TRACE_STATUS_REPORT' ) && defined( 'WP_CACHE' ) ) {
		global $bw_action_options;
		$bw_action_options = array();
		$bw_action_options['trace_status_report'] = true;
		$status_report = true;
		
	} else {
		$status_report = false;
	}
	*/

  if ( $trace_on ) {
	  oik_require( "includes/bwtrace.php", "oik-bwtrace" );
    bw_trace_inc_init();
    bw_trace_on( true );
    if ( $trace_reset ) {
      bw_trace_reset();
    }   
  }
	
	if ( $count_on ) {
		
    oik_require( "includes/oik-action-counts.php", "oik-bwtrace" );
		bw_trace_activate_mu();
    //bw_trace_count_on();
    //bw_lazy_trace_count();
		//bw_trace_count_plugins_loaded( $count_on );
	}
	
	
	
}
