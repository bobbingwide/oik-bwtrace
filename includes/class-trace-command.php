<?php

/**
 * @copyright (C) Copyright Bobbing Wide 2018
 * @package oik-bwtrace
 */
 
/**
 * Manages tracing of requests
 *
 * supporting a series of methods to configure and enable/disable tracing from the command line
 * 
 * e.g. 
 * wp trace subcommand set 
 * 
 * ## OPTIONS
 *
 * <set>
 * : Transaction type to trace.
 * ---
 * default: default
 * options:
 * - default
 * - ajax
 * - rest
 * - cli
 * 
 * <on/off>
 * 
 *
 * 
 * ## EXAMPLES
 * 		# Display trace status
 *    $wp trace status
 *    
 *    # Enable tracing
 * 		$wp trace on
 * 
 *    # Disable tracing for cli
 *		$wp trace off cli
 * 
 * 
 */
 
class trace_command extends WP_CLI_Command {

	public function on() {
		WP_CLI::line( "Turning trace on" );
		
	}
	
	public function off() {
		WP_CLI::line( "Turning trace off" );
	}

	public function status() {
	
		$tracing = bw_trace_status();
		if ( $tracing ) {
			WP_CLI::line( "Tracing is on." ); 
		} else {
			WP_CLI::line( "Tracing is off." );
		}
	}



}
