<?php
/*
Plugin Name: oik bwtrace 
Plugin URI: http://www.oik-plugins.com/oik-plugins/oik-bwtrace
Description: Debug trace for WordPress, including action and filter tracing
Version: 2.1.1-alpha.20170303
Author: bobbingwide
Author URI: http://www.oik-plugins.com/author/bobbingwide
Text Domain: oik-bwtrace
Domain Path: /languages/
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

    Copyright 2011-2017 Bobbing Wide (email : herb@bobbingwide.com )

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
 * Determine the tracing status
 * 
 * This logic is performed before testing the trace level 
 * and whether or not a specific IP address is being traced.
 *
 * Additionally, it checks different values when DOING_AJAX is true.
 *
 * @return bool - the determined value of $bw_trace_on 
 */
function bw_trace_status() {
	global $bw_trace_on, $bw_trace_options;
	if ( defined( 'BW_TRACE_ON' ) && BW_TRACE_ON ) {
		// $bw_trace_on should already be true... but can we turn it off?
		// How does that affect reset?	
		// Well, perhaps we can check the BW_TRACE_RESET constant and whether or not we started in wp-config
	} else {
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			$bw_trace_on = bw_torf( $bw_trace_options, 'trace_ajax' );
		} else {
			$bw_trace_on = bw_torf( $bw_trace_options, 'trace' );
		}
	}	
	return $bw_trace_on;
}

/**
 * Determine the trace reset status
 *
 * We can reset the trace file regardless of the value of tracing
 * except when we're only tracing a specific IP
 * when we don't want to reset the trace file if we're not tracing this particular transaction.
 *
 * If the request contains '_bw_trace_reset' then we will force a reset.
 * 
 * @TODO Trace reset only affects the particular file we're dealing with.
 *  We'll need to find some way of resetting the AJAX trace file.
 * 
 * $bw_trace_ip | $tracing | $bw_trace_reset ?
 * ------------ | -------- | ---------------------
 * set          | false    | don't reset
 * set          | true		 | depends on the option 'reset' or 'reset_ajax'
 * not-set      | either   | depends on the option 'reset' or 'reset_ajax'
 *
 * @param string $bw_trace_ip - specific IP to trace
 * @param bool $tracing true if tracing
 * @return bool true if the trace file should be reset
 */
function bw_trace_reset_status( $bw_trace_ip, $tracing ) {
	global $bw_trace_options;
	if ( $bw_trace_ip && !$tracing ) { 
		$bw_trace_reset = false ;
	} else {
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			$bw_trace_reset = bw_torf( $bw_trace_options, 'reset_ajax' );
		} else {
			$bw_trace_reset = bw_torf( $bw_trace_options, 'reset' );
		}
	}
	if ( !empty( $_REQUEST['_bw_trace_reset'] ) ) {
		$bw_trace_reset = true;
	}
	
	if ( isset( $_REQUEST['wc-ajax'] ) ) {
		$bw_trace_reset = false;
	} 
	return( $bw_trace_reset );
} 

/**
 * Determine the required trace level
 *
 * The required trace level is determined by a number of methods
 *
 * - It may already be set as a global value
 * - From the option variable "level"
 * - @TODO From the constant BW_TRACE_LEVEL - which may be set as an integer in wp-config.php
 * - If WP_DEBUG is false then the trace level remains the same 
 * - @TODO If WP_DEBUG is true then it will become BW_TRACE_DEBUG
 * 
 * @return integer trace level. Negative when tracing is off
 */
function bw_trace_level() {
	global $bw_trace_level, $bw_trace_options;
	if ( !isset( $bw_trace_level ) ) {
		$bw_trace_level = bw_array_get( $bw_trace_options, "level", BW_TRACE_INFO );
		$bw_trace_level = (int) $bw_trace_level;
	}
	return( $bw_trace_level );
}

/**
 * Return TRUE if option is '1', FALSE otherwise 
 *
 * @param array $array the option array
 * @param string $option the option field
 * @return bool true if the option field is set 
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
 * Activate action hooks and filter counting or other action tracing if the profile says so
 * 
 */
function bw_trace_plugin_startup() {
	global $bw_trace_options, $bw_action_options;
	$bw_trace_options = get_option( 'bw_trace_options' );
	if ( !isset( $bw_action_options ) ) {
		$bw_action_options = get_option( 'bw_action_options' );
	}
	$tracing = bw_trace_status();
	
	$bw_trace_ip = bw_array_get( $bw_trace_options, "ip", null );
	if ( $bw_trace_ip ) {
	 	$server = bw_array_get( $_SERVER, "REMOTE_ADDR", null );
	 	$tracing = ( $server == $bw_trace_ip );
	}
	$bw_trace_reset = bw_trace_reset_status( $bw_trace_ip, $tracing );
	if ( $bw_trace_reset ) {
		oik_require2( "includes/bwtrace.php", "oik-bwtrace" );
		bw_trace_reset();
	}
  
	if ( $tracing ) {
		$bw_trace_level = bw_trace_level(); 
		bw_trace_on();
		global $bw_include_trace_count
				, $bw_include_trace_date
				, $bw_trace_anonymous
				, $bw_trace_memory
				, $bw_trace_post_id
				, $bw_trace_num_queries;
		global $bw_trace_current_filter, $bw_trace_file_count;
		$bw_include_trace_count = bw_torf( $bw_trace_options, 'count' );
		$bw_include_trace_date = bw_torf( $bw_trace_options, 'date' );
		$bw_trace_anonymous = !bw_torf( $bw_trace_options, 'qualified' );
		$bw_trace_memory = bw_torf( $bw_trace_options, "memory" );
		$bw_trace_post_id = bw_torf( $bw_trace_options, "post_id" );
		$bw_trace_num_queries = bw_torf( $bw_trace_options, "num_queries" );
		bw_trace_set_savequeries();
		
		$bw_trace_current_filter = bw_torf( $bw_trace_options, "filters" );
		$bw_trace_file_count = bw_torf( $bw_trace_options, "files" );
    
    
		oik_require2( "includes/bwtrace.php", "oik-bwtrace" );
		
	} else {
		bw_trace_off();
	}
	
	if ( $tracing ) {
		/*
		* If we want to trace hook counting then we can start quite early
		*/
		if ( defined( "BW_COUNT_ON" ) && true == BW_COUNT_ON ) {
	
			oik_require( "includes/oik-action-counts.php", "oik-bwtrace" );
		 bw_trace_count_plugins_loaded( true );
			$count_hooks = true;
		} else {
			oik_require( "includes/oik-action-counts.php", "oik-bwtrace" );
			$count_hooks = bw_array_get( $bw_action_options, "count", false );
			bw_trace_activate_mu( $count_hooks );
			bw_trace_count_plugins_loaded( $count_hooks );
		}
		if ( $count_hooks ) {
			add_action( "plugins_loaded", "bw_trace_count_plugins_loaded" );
			add_action( "muplugins_loaded", "bw_trace_count_plugins_loaded" );
		}
	} 
	 
	if ( $tracing ) {
		bw_trace_trace_startup();
  } 
	add_action( "wp_loaded", "oik_bwtrace_plugins_loaded", 9 );
	add_filter( "oik_query_libs", "oik_bwtrace_query_libs", 12 );
	
}


/**
 * Implement "wp_loaded" filter for oik-bwtrace 
 */
function oik_bwtrace_plugins_loaded() {
	if ( oik_require_lib( "oik-admin" ) && oik_require_lib( "bobbforms" ) && oik_require_lib( "bobbfunc" ) && oik_require_lib( "class-bobbcomp") ) {
		add_action( 'admin_menu', 'bw_trace_options_add_page');
		add_action( 'admin_menu', 'bw_action_options_add_page');
	} else {
		bw_trace2( "Unable to activate oik-bwtrace admin" );
	}
	
	
	add_action( 'admin_init', 'bw_trace_options_init' );
	add_action( 'admin_init', 'bw_action_options_init' );
	/*
	 * Load admin logic if is_admin() 
	 */
	if ( function_exists( "is_admin" ) ) {
		if ( is_admin() ) {   
			oik_require( "admin/oik-bwtrace.inc", "oik-bwtrace" );
		}
	}
	
	
  add_action( "oik_admin_menu", "oik_bwtrace_admin_menu" );
}

/**
 * Implement "oik_query_libs" filter for oik-bwtrace
 *
 * In order for this function to have been invoked the oik-lib logic must be in place.
 * So we can happily register the libraries in the libs folder using the available functions and methods
 * 
 * Here we're determining the subset of oik functions that are actually used by oik-bwtrace.
 * We may eventually determine that these really do need to be implemented as dependencies.
 * Does this mean that we need to implement the bwtrace admin as a library with dependencies on "oik-admin"
 * ... probably.
 * In which case automatically building the libraries from files that are present is not a good idea 
 *
 */
function oik_bwtrace_query_libs( $libraries ) {
  // $libraries = oik_lib_query_libraries( $libraries, "oik-bwtrace" );
	$lib_args = array();
	$libs = array( "bobbfunc" => null, "bobbforms" => "bobbfunc", "oik-admin" => "bobbforms" );
	$versions = array( "bobbfunc" => "3.0.0" );
	foreach ( $libs as $library => $depends ) {
		$lib_args['library'] = $library;
		$lib_args['src'] = oik_path( "libs/$library.php", "oik-bwtrace" ); 
		//if ( $depends ) {
			$lib_args['deps'] = $depends;
		//}
		
		// Here we should consider deferring the version setting until it's actually time to check compatibility
		$lib_args['version'] = bw_array_get( $versions, $library, null );
		$lib = new OIK_lib( $lib_args );
		$libraries[] = $lib;
	}
	bw_trace2( null, null, true, BW_TRACE_VERBOSE );
	//bw_backtrace();
	return( $libraries );
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
 * From oik-bwtrace v2.0.0 we use shared libraries.
 * 
 */
function oik_bwtrace_admin_menu() {
  //oik_register_plugin_server( __FILE__ );
	bw_load_plugin_textdomain( 'oik-bwtrace' );
}
										 
/**
 * Logic invoked when oik-bwtrace is loaded
 *
 * oik-bwtrace can get loaded as a normal plugin, or from the _oik-bwtrace-mu plugin
 * Since its purpose is to enable tracing of WordPress core, plugins and themes
 * it's coded to be able to start up lazily and not expect the whole of WordPress to be up and running.
 * 
 * Some parts of oik-bwtrace are dependent on functions in the oik base plugin.
 * If these functions are not available then it won't do anything.
 * 
 * For the run-time part we now make used of shared library logic, supported by the oik-lib plugin
 * If this has been loaded before us then we can use its logic.
 * Otherwise we have to operate in a standalone mode.
 */
function oik_bwtrace_loaded() {
	/*
	 * Since this plugin is defined to load first... so that it can perform the trace reset
	 * then we need to load oik_boot ourselves... 
	 * Amongst other things we need bw_array_get() and oik_require()
	 */
	if ( !function_exists( 'oik_require' ) ) {
		// check that oik v2.6 (or higher) is available.
		$oik_boot = dirname( __FILE__ ). "/libs/oik_boot.php";
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
		oik_lib_fallback( dirname( __FILE__ ) . '/libs' );
		oik_require( "libs/bwtrace.php", "oik-bwtrace" );
		oik_require( "libs/bwtrace_boot.php", "oik-bwtrace" );
		oik_require( "libs/bwtrace_log.php", "oik-bwtrace" ); 
		oik_require2( "includes/bwtrace.php", "oik-bwtrace" );
	}
	
	/** 
	 * Constants for bw_trace2's $level parameter
	 *
	 * - The trace record is produced if the $level passed is greater than or equal to the current tracing level ( $bw_trace_on );
	 * - The default value for bw_trace2 is BW_TRACE_ALWAYS
	 * - The higher you set the value the more tracing you get.
	 * - The testing is NOT (yet) implemented as a bit-mask.
	 * - Note: Most of these values are a subset of logging levels in packages such as monolog.
	 * - It's not really necessary to have CRITICAL, ALERT or EMERGENCY; ERROR will suffice
	 * - See also {@link https://en.wikipedia.org/wiki/Syslog#Severity_levels}
	 * 
	 */
	if ( !defined( 'BW_TRACE_VERBOSE' ) ) { define( 'BW_TRACE_VERBOSE', 64 ); }
	if ( !defined( 'BW_TRACE_DEBUG' ) ) { define( 'BW_TRACE_DEBUG', 32 ); }
	if ( !defined( 'BW_TRACE_INFO' ) ) { define( 'BW_TRACE_INFO', 16 ); }							// recommended level
	if ( !defined( 'BW_TRACE_NOTICE' ) ) { define( 'BW_TRACE_NOTICE', 8 ); }
	if ( !defined( 'BW_TRACE_WARNING' ) ) { define( 'BW_TRACE_WARNING', 4 ); }
	if ( !defined( 'BW_TRACE_ERROR' ) ) { define( 'BW_TRACE_ERROR', 2 ); }
	if ( !defined( 'BW_TRACE_ALWAYS' ) ) { define( 'BW_TRACE_ALWAYS', 0 ); }			// bw_trace2() default
	
	/*
	 * Invoke the start up logic if "add_action" is available
	 */ 
	if ( function_exists( "add_action" ) ) {
		bw_trace_plugin_startup();
	}
	
	/*
	 * Selected actions, such as shutdown actions are implemented in includes/oik-actions.php
	 * 
	 */
	oik_require( "includes/bwtrace-actions.php", "oik-bwtrace" );
	bw_trace_add_selected_actions();

}


global $bw_trace_options, $bw_trace_on, $bw_trace_level;

oik_bwtrace_loaded();


