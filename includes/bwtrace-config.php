<?php // (C) Copyright Bobbing Wide 2013-2015

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
 */
function bw_lazy_trace_config_startup() {
     
  if ( defined( 'BW_TRACE_ON' ) ) {
    $trace_on = BW_TRACE_ON;
  } else {
    $trace_on = false;
	}
  
	/*
  if ( defined( 'BW_ACTIONS_ON' ) ) {
    $actions_on = BW_ACTIONS_ON;
  } else {
    $actions_on = false;
	}
	*/
	
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
	
	/*
  if ( defined( 'BW_ACTIONS_RESET' ) ) {
    $actions_reset = BW_ACTIONS_RESET;
  } else {
    $actions_reset = false;
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

	/*
  if ( $actions_on ) {     
    oik_require2( "includes/oik-bwtrace.inc", "oik-bwtrace" );
    bw_action_inc_init();
    bw_trace_actions_on();     
    if ( $actions_reset ) {
      bw_actions_reset();
    }  
  }
	*/
	
	if ( $count_on ) {
    oik_require( "includes/oik-action-counts.php", "oik-bwtrace" );
		bw_trace_activate_mu();
    //bw_trace_count_on();
    //bw_lazy_trace_count();
		//bw_trace_count_plugins_loaded( $count_on );
	}
	
	
}
