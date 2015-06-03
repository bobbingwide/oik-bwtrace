<?php
/*
Plugin Name: oik bwtrace 
Plugin URI: http://www.oik-plugins.com/oik-plugins/oik-bwtrace
Description: Debug trace for WordPress, including action and filter tracing
Version: 1.25
Author: bobbingwide
Author URI: http://www.oik-plugins.com/author/bobbingwide
License: GPL2

    Copyright 2011-2015 Bobbing Wide (email : herb@bobbingwide.com )

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License version 2,
    as published by the Free Software Foundation.

    You may NOT assume that you can use any other version of the GPL.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    The license for this software can likely be found here:
    http://www.gnu.org/licenses/gpl-2.0.html

*/


/**
 * Return TRUE if option is '1', FALSE otherwise 
 */
function bw_torf( $array, $option ) {
  $opt = bw_array_get( $array, $option );
  $ret = $opt > '0';
  return $ret;
}

/**
 * Startup processing for oik-bwtrace
 * 
 * Activate trace if the profile says so 
 * AND if the chosen IP address is being used
 * 
 */
function bw_trace_plugin_startup() {
  global $bw_trace_options, $bw_action_options;
  $bw_trace_options = get_option( 'bw_trace_options' );
  $bw_trace_level = bw_torf( $bw_trace_options, 'trace' ); 
  $bw_trace_ip = null;
  if ( $bw_trace_level ) { 
    $bw_trace_ip = bw_array_get( $bw_trace_options, "ip", null );
    if ( $bw_trace_ip ) {
      $server = bw_array_get( $_SERVER, "REMOTE_ADDR", null );
      $bw_trace_level = ( $server == $bw_trace_ip );
    }
  } 
	   
  // We can reset the trace file regardless of the value of tracing
  // except when we're only tracing a specific IP
  
  // $bw_trace_level | $bw_trace_ip | reset ?
  // --------------- | ------------ | -----------
  // 0               | set          | NO
  // 0               | null         | YES
  // 1               | either       | depends on option
  if ( $bw_trace_ip && !$bw_trace_level ) { 
    $bw_trace_reset = false ;
  } else {
    $bw_trace_reset = bw_torf( $bw_trace_options, 'reset' );
  }
  if ( !empty( $_REQUEST['_bw_trace_reset'] ) ) {
    $bw_trace_reset = TRUE;
  } 
	
  if ( $bw_trace_reset ) {
    oik_require2( "includes/bwtrace.php", "oik-bwtrace" );
    bw_trace_reset();
    $bw_action_reset = true;
  } 
  
  if ( $bw_trace_level ) {
    bw_trace_on();
    global $bw_include_trace_count, $bw_include_trace_date, $bw_trace_anonymous, $bw_trace_memory, $bw_trace_post_id, $bw_trace_num_queries, $bw_trace_savequeries;
    $bw_include_trace_count = bw_torf( $bw_trace_options, 'count' );
    $bw_include_trace_date = bw_torf( $bw_trace_options, 'date' );
    $bw_trace_anonymous = !bw_torf( $bw_trace_options, 'qualified' );
    $bw_trace_memory = bw_torf( $bw_trace_options, "memory" );
    $bw_trace_post_id = bw_torf( $bw_trace_options, "post_id" );
    $bw_trace_num_queries = bw_torf( $bw_trace_options, "num_queries" );
    //$bw_trace_savequeries = bw_torf( $bw_trace_options, "savequeries" );
    $bw_trace_savequeries = $bw_trace_num_queries;
    
    
    oik_require2( "includes/bwtrace.php", "oik-bwtrace" );
		/*
		 * @TODO Make this optional
		 */
    //bw_trace_included_files();
    //if ( $bw_trace_savequeries ) {
    //   bw_trace_set_savequeries();
    //}
    //bw_trace_saved_queries();
    
    // We should only do this if we want to trace actions
    //add_action( "init", "bw_trace_actions" );
		
		/*
		 * If we want to trace counting then we can start counting quite early
		 */
		if ( defined( "BW_COUNT_ON" ) && true == BW_COUNT_ON ) {
		  bw_trace_count_plugins_loaded( true );
		} else {
		  oik_require( "includes/oik-action-counts.php", "oik-bwtrace" );
		}
    add_action( "plugins_loaded", "bw_trace_count_plugins_loaded" );
		add_action( "muplugins_loaded", "bw_trace_count_plugins_loaded" );
  } else {
    if ( !$bw_trace_ip ) {
      bw_trace_off();
    }    
  } 

 
  // Shouldn't this be moved so that it's only performed if trace actions is enabled?  **?** 
  
  $bw_action_options = get_option( 'bw_action_options' );
  $bw_action_reset = bw_torf( $bw_action_options, 'reset' );
  if ( !empty( $_REQUEST['_bw_action_reset'] ) ) {
    $bw_action_reset = TRUE;
  } 
  
  
  if ( $bw_action_reset ) {
    oik_require( "includes/oik-actions.php", "oik-bwtrace" );
    bw_actions_reset();
  }

  if ( $bw_trace_level > '0' ) {
    bw_lazy_trace( ABSPATH . $bw_trace_options['file'], __FUNCTION__, __LINE__, __FILE__, 'tracelog' );
    bw_lazy_trace( $_SERVER, __FUNCTION__, __LINE__, __FILE__, "_SERVER" ); 
    bw_lazy_trace( bw_getlocale(), __FUNCTION__, __LINE__, __FILE__, "locale" );
    bw_lazy_trace( $_REQUEST, __FUNCTION__, __LINE__, __FILE__, "_REQUEST" );
    //bw_lazy_trace( $_POST, __FUNCTION__, __LINE__, __FILE__, "_POST" );
    //bw_lazy_trace( $_GET, __FUNCTION__, __LINE__, __FILE__, "_GET" );
    bw_lazy_trace( $bw_action_options, __FUNCTION__, __LINE__, __FILE__, "bw_action_options" );
  } 

  add_action( 'admin_init', 'bw_trace_options_init' );
  add_action( 'admin_init', 'bw_action_options_init' );
  add_action( 'admin_menu', 'bw_trace_options_add_page');
  add_action( 'admin_menu', 'bw_action_options_add_page');
	
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
 * Add a selected trace action
 * 
 * @param string $action the action hook e.g. 'wp'
 * @param string $option the option name e.g. 'trace_wp_rewrite'
 * @param string $file the implementing file e.g. 'includes/oik-actions.php'
 * @param string $function the implementing function e.g. 'bw_trace_wp_rewrite'
 */
function bw_trace_add_action( $action, $option, $file, $function ) {
	global $bw_action_options;
	$bw_trace_action = bw_array_get( $bw_action_options, $option, false );
	if ( $bw_trace_action ) {
	  if ( !function_exists( $function ) ) {
		  oik_require( $file, "oik-bwtrace" );
		}
		if ( function_exists( $function ) ) {
		  add_action( $action, $function );
		}
	}
}
	
/**
 * Add actions to trace selected actions
 * 
 * For 'wp' we trace the WordPress instance passed
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
	bw_trace_add_action( "shutdown", "trace_included_files", "includes/oik-actions.php", "bw_trace_included_files" );
	bw_trace_add_action( "shutdown", "trace_saved_queries", "includes/oik-actions.php", "bw_trave_saved_queries" );
	bw_trace_add_action( "shutdown", "trace_status_report", "includes/oik-actions.php", "bw_trace_status_report" );
	bw_trace_add_action( "shutdown", "trace_output_buffer", "includes/oik-actions.php", "bw_trace_output_buffer" );
}

/**
 * 
 * Implement 'oik_admin_menu' action 
 * 
 * Set the plugin server
 * Register the text domain for localization
 * 
 * 
 * Note: Prior to oik v1.18 we used to relocate the oik-bwtrace plugin from the oik plugin to become its own plugin.
 * This is no longer necessary. As of oik v2.6-alpha.0524 the oik base plugin now delivers a different includes/bwtrace.php
 * that says you need "oik-bwtrace" to implement tracing logic.
 * 
 */
function oik_bwtrace_admin_menu() {
  oik_register_plugin_server( __FILE__ );
	bw_load_plugin_textdomain( 'oik-bwtrace' );
  //bw_add_relocation( "oik/oik-bwtrace.php", "oik-bwtrace/oik-bwtrace.php" );
  //bw_add_relocation( "oik/includes/bwtrace.inc", "oik-bwtrace/includes/bwtrace.inc" );
  //bw_add_relocation( "oik/includes/oik-bwtrace.inc", "oik-bwtrace/includes/oik-bwtrace.inc" );
  //bw_add_relocation( "oik/admin/oik-bwtrace.inc", "oik-bwtrace/admin/oik-bwtrace.inc" );
  //bw_add_relocation( "oik/admin/oik-bwaction.inc", "oik-bwtrace/admin/oik-bwaction.inc" );
}

										 
/**
 * Logic invoked when oik-bwtrace is loaded
 *
 * oik-bwtrace can get loaded as a normal plugin, or from the _oik-bwtrace-mu plugin
 * Since its purpose is to enable tracing of WordPress core, plugins and themes
 * it's coded to be able to start up lazily and not expect the whole of WordPress to be up and running.
 * 
 * It is dependent on functions in the oik base plugin.
 * If these functions are not available then it won't do anything.
 *  
 */
function oik_bwtrace_loaded() {

	/*
	 * Since this plugin is defined to load first... so that it can perform the trace reset
	 * then we need to load oik_boot ourselves... 
	 * Amongst other things we need bw_array_get() and oik_require()
	 */
	if ( !function_exists( 'oik_require' ) ) {
		// check that oik v2.6 (or higher) is available.
		$oik_boot = dirname(dirname(__FILE__)) . "/oik/oik_boot.php";
		if ( file_exists( $oik_boot ) ) { 
			require_once( $oik_boot );
		}
	}

	/* 
	 * Only carry on if "oik_require2()" exists - which indicates oik is version 1.17 or higher 
	 *
	 * It's no longer necessary to check for oik_require2 since this is the code from the already split out oik-bwtrace
	 * If oik really is backlevel then we may have a problem.
	*/
	if ( function_exists( "oik_require2" )) {
		oik_init();
		oik_require( "bwtrace_boot.php" ); 
		oik_require2( "includes/bwtrace.php", "oik-bwtrace" );
	}
	
	/*
	 * Invoke the start up logic if "add_action" is available
	 */ 
	if ( function_exists( "add_action" ) ) {
		bw_trace_plugin_startup();
	}
	
	/*
	 * Load admin logic if is_admin() 
	 */
	if ( function_exists( "is_admin" ) ) {
		if ( is_admin() ) {   
			oik_require( "admin/oik-bwtrace.inc", "oik-bwtrace" );
		}
	}
	
  add_action( "oik_admin_menu", "oik_bwtrace_admin_menu" );
	
	
	/*
	 * Selected actions, such as shutdown actions are implemented on includes/oik-actions.php
	 * 
	 */
	bw_trace_add_selected_actions();




}


global $bw_trace_options, $bw_trace_on, $bw_trace_level;

oik_bwtrace_loaded();


