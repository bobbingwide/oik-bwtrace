<?php
/*
Plugin Name: oik bwtrace 
Plugin URI: https://www.oik-plugins.com/oik-plugins/oik-bwtrace
Description: Debug trace for WordPress, including action and filter tracing
Version: 3.0.0
Author: bobbingwide
Author URI: https://www.bobbingwide.com/about-bobbing-wide
Text Domain: oik-bwtrace
Domain Path: /languages/
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

    Copyright 2011-2019 Bobbing Wide (email : herb@bobbingwide.com )

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
	global $bw_trace_on, $bw_trace;
	//, $bw_trace_options;

	$bw_trace_on = $bw_trace->status();
	return $bw_trace_on;
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
	global $bw_trace_level, $bw_trace;
	if ( !isset( $bw_trace_level ) ) {
		$bw_trace_level = $bw_trace->get_trace_level();
	}
	return $bw_trace_level;
}

/**
 * Startup processing for oik-bwtrace
 *
 * @TODO Implementation doesn't match comments. What should happen when "ip" is set in the trace profile?
 * 
 * Activate trace if the profile says so 
 * AND if the chosen IP address is being used
 * OR if there's no REMOTE_ADDR and the value for trace_ip matches the value for PHP CLI processing.
 * 
 * Activate action hooks and filter counting or other action tracing if the profile says so
 * 
 */
function bw_trace_plugin_startup() {
	oik_require( "includes/class-BW-trace-controller.php", "oik-bwtrace" );
	global $bw_trace;
	$bw_trace = new BW_trace_controller();
	global $bw_action_options;
	$bw_action_options = get_option( 'bw_action_options' );
	$tracing = bw_trace_status();
  
	if ( $tracing ) {
		$bw_trace_level = bw_trace_level(); 
		bw_trace_on();
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
	//add_filter( "get_the_generator_xhtml", "oik_bwtrace_get_the_generator", 10, 2 );
}

/**
 * Implement "wp_loaded" filter for oik-bwtrace 
 */
function oik_bwtrace_plugins_loaded() {
	if ( function_exists( "is_admin" ) ) {
		$is_admin = is_admin();
	} else {
		$is_admin = false;
	}
	
	/** 
	 * Load the required library files before registering these hooks
	 */
	if ( oik_require_lib( "oik-admin" ) && oik_require_lib( "bobbforms" ) && oik_require_lib( "bobbfunc" ) && oik_require_lib( "class-bobbcomp")
	&& oik_require_lib( "class-BW-" ) && $is_admin ) {
		add_action( 'admin_menu', 'bw_trace_options_add_page');
		add_action( 'admin_menu', 'bw_action_options_add_page');
	} else {
		bw_trace2( "Unable to activate oik-bwtrace admin" );
	}
	
	
	add_action( 'admin_init', 'bw_trace_options_init' );
	add_action( 'admin_init', 'bw_action_options_init' );
	add_action( 'admin_init', 'bw_summary_options_init' );
	
	/*
	 * Load admin logic if is_admin() 
	 */
	if ( $is_admin ) {   
		oik_require( "admin/oik-bwtrace.php", "oik-bwtrace" );
	}
	
  add_action( "oik_admin_menu", "oik_bwtrace_admin_menu" );
	
  add_action( "oik_add_shortcodes", "oik_bwtrace_add_shortcodes", 11 );
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
	$lib_args = array();
	$libs = array( "bobbfunc" => null, "bobbforms" => "bobbfunc", "oik-admin" => "bobbforms", 'hexdump' => null );
	$versions = array( "bobbfunc" => "3.2.0", "bobbforms" => "3.2.0", "oik-admin" => "3.2.0" );
	foreach ( $libs as $library => $depends ) {
		$lib_args['library'] = $library;
		$lib_args['src'] = oik_path( "libs/$library.php", "oik-bwtrace" ); 
		$lib_args['deps'] = $depends;
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
 * Implements 'oik_admin_menu' action 
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
 * Adds the trace shortcode
 */
function oik_bwtrace_add_shortcodes() {
	bw_add_shortcode( 'bwtrace', 'bw_trace_button', oik_path( 'shortcodes/oik-trace.php', 'oik-bwtrace' ), false );
}

function oik_bwtrace_wp_cli() {
	if ( defined( "WP_CLI" ) && WP_CLI ) {
		WP_CLI::debug( "WP-CLI is active. Loading trace command" );
		oik_require( "includes/class-trace-command.php", "oik-bwtrace" );
		WP_CLI::add_command( "trace", "trace_command" );
		
	}
}

/**
 * Initialises the daily trace summary processing
 *
 * 
 */
function oik_bwtrace_initialise_trace_summary() {	
	global $bw_trace;
	if ( $bw_trace && $bw_trace->trace_files_directory ) {
		global $bw_trace_summary;
		oik_require( "admin/class-oik-trace-summary.php", "oik-bwtrace" );
		$bw_trace_summary = new OIK_trace_summary();
		$bw_trace_summary->initialise();
	}
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
		oik_require( "includes/bwtrace.php", "oik-bwtrace" );  // Don't use require2 as this file's no longer part of oik
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
	 * 2018/01/30 - I can't see why we need this test but it probably does no harm.
	 * 
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
	
	/*
	 * Add trace command for WP-CLI
	 */
	oik_bwtrace_wp_cli();
	
	oik_bwtrace_initialise_trace_summary();

}

function oik_bwtrace_get_the_generator( $gen, $type ) {
	$php_version = '<!--PHP Version:'. phpversion() . ' -->';
	$gen .= $php_version;
	return $gen;
}

global $bw_trace_options, $bw_trace_on, $bw_trace_level;

oik_bwtrace_loaded();
