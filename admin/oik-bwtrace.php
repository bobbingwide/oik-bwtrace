<?php 
/*

    Copyright 2012-2018 Bobbing Wide (email : herb@bobbingwide.com )

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
 * Register bw_trace_options
 *
 * Init plugin options to white list our options
 * 
 */
function bw_trace_options_init(){
  register_setting( 'bw_trace_options_options', 'bw_trace_options', 'bw_trace_options_validate' );
	register_setting( 'bw_trace_files_options', 'bw_trace_files_options', 'bw_trace_files_options_validate' );
  
  add_action( "activated_plugin", "bw_this_plugin_first", 10, 2 );
}

/**
 * Register bw_action_options
 *
 */
function bw_action_options_init(){
  register_setting( 'bw_action_options_options', 'bw_action_options', 'bw_action_options_validate' );
}

/***
 * Registers bw_summary_options
 */
function bw_summary_options_init() {
  register_setting( 'bw_summary_options_options', 'bw_summary_options', 'bw_summary_options_validate' );
}

/**
 * Register the trace options page
 *
 * Only available if the "oik-admin" library is available.
 *
 */
function bw_trace_options_add_page() {
	if ( oik_require_lib( "oik-admin" ) ) {
		add_options_page( __('oik trace options', 'oik-bwtrace' ), __( 'oik trace options', 'oik-bwtrace' ), 'manage_options', 'bw_trace_options', 'bw_trace_options_do_page');
	}
	add_action( "admin_print_styles-settings_page_bw_trace_options", "bw_action_enqueue_styles" );
}


/**
 * Register the action options page
 *
 */
function bw_action_options_add_page() {
	add_options_page( __('oik action options', 'oik-bwtrace') , __( 'oik action options', 'oik-bwtrace' ), 'manage_options', 'bw_action_options', 'bw_action_options_do_page');
	add_action( "admin_print_styles-settings_page_bw_action_options", "bw_action_enqueue_styles" );
}

function bw_action_enqueue_styles() {
	wp_register_style( 'oik-bwtrace', oik_url( 'oik-bwtrace.css', 'oik-bwtrace' ), false );
	wp_enqueue_style( 'oik-bwtrace' ); 
}

/** 
 * Settings page for oik actions logging
 *
 * 
 * Also includes the Information block
 * 
 */
function bw_action_options_do_page() {
  BW_::oik_menu_header( __( "action options", "oik-bwtrace" ), "w70pc" );
  BW_::oik_box( null, null, __( "Options", "oik-bwtrace" ) , "oik_action_options" ); 
	BW_::oik_box( null, null, __( "Daily Trace Summary", "oik-bwtrace" ), "oik_trace_summary" );
  BW_::oik_box( null, null, __( "Information", "oik-bwtrace" ), "oik_trace_info" );
  oik_menu_footer();
  bw_flush();
}

/**
 * Display the action options form
 *
 * 2015/05/23 Disabled 'immediate' tracing
 * Relabelled 'Count immediate actions' to 'Count action hooks and filters'
 * 
 */
function oik_action_options() {  
  bw_form( "options.php" );
  $options = get_option('bw_action_options');     
  stag( 'table class="form-table"' );
  bw_flush();
  settings_fields('bw_action_options_options'); 
  
  bw_checkbox_arr( "bw_action_options", __( "Count action hooks and filters", "oik-bwtrace" ), $options, 'count' );
  bw_checkbox_arr( "bw_action_options", __( "Trace deprecated messages", "oik-bwtrace" ), $options, 'trace_deprecated' );
	bw_checkbox_arr( "bw_action_options", __( "Trace Error, Warning and Notice messages", "oik-bwtrace" ), $options, 'trace_errors' );
  bw_checkbox_arr( "bw_action_options", __( "Trace 'wp' action", "oik-bwtrace" ), $options, 'trace_wp_action' );
  bw_checkbox_arr( "bw_action_options", __( "Trace 'wp' global wp_rewrite ", "oik-bwtrace" ), $options, 'trace_wp_rewrite' );
  bw_checkbox_arr( "bw_action_options", __( "Trace 'shutdown' included files", "oik-bwtrace" ), $options, 'trace_included_files' );
  bw_checkbox_arr( "bw_action_options", __( "Trace 'shutdown' saved queries", "oik-bwtrace" ), $options, 'trace_saved_queries' );
  bw_checkbox_arr( "bw_action_options", __( "Trace 'shutdown' output buffer", "oik-bwtrace" ), $options, 'trace_output_buffer' );
  bw_checkbox_arr( "bw_action_options", __( "Trace 'shutdown' trace functions count", "oik-bwtrace" ), $options, 'trace_functions' );
  bw_checkbox_arr( "bw_action_options", __( "Trace 'shutdown' status report", "oik-bwtrace" ), $options, 'trace_status_report' );
	
	BW_::bw_textarea_arr( "bw_action_options", __( "Other hooks to trace", "oik-bwtrace" ), $options, "hooks", 80 );
	BW_::bw_textarea_arr( "bw_action_options", __( "Filter results to trace", "oik-bwtrace" ), $options, "results", 80 );
	BW_::bw_textarea_arr( "bw_action_options", __( "Trace the global post object", "oik-bwtrace" ), $options, "post_hooks", 80 );
	BW_::bw_textarea_arr( "bw_action_options", __( "Trace attached hook functions", "oik-bwtrace" ), $options, "hook_funcs", 80 );
	BW_::bw_textarea_arr( "bw_action_options", __( "Hooks to debug backtrace", "oik-bwtrace" ), $options, "backtrace", 80 );
	BW_::bw_textarea_arr( "bw_action_options", __( "'String watch' for this string", "oik-bwtrace" ), $options, "stringwatch", 80 );
  etag( "table" ); 			
  BW_::p( isubmit( "ok", __( "Save changes", "oik-bwtrace" ), null, "button-primary" ) );
  etag( "form" );
}  

/**
 * Display the oik trace options page
 * 
 */
function bw_trace_options_do_page() { 
  BW_::oik_menu_header( __( "trace options", "oik-bwtrace" ) );
	BW_::oik_box( null, null, __( "Trace files", "oik-bwtrace" ), "oik_trace_files_options" );
  BW_::oik_box( null, null, __( "Options", "oik-bwtrace" ), "oik_trace_options" );
	BW_::oik_box( null, null, __( "Daily Trace Summary", "oik-bwtrace" ), "oik_trace_summary" );
  BW_::oik_box( null, null, __( "Information", "oik-bwtrace" ), "oik_trace_info" );
  BW_::oik_box( null, null, __( "Notes about oik trace", "oik-bwtrace" ), "oik_trace_notes" ); 
  BW_::oik_box( null, null, __( "Trace options and reset button", "oik-bwtrace" ), "oik_trace_reset_notes" ); 
  oik_menu_footer();
  bw_flush();
}

/**
 * Display the trace files options box
 */
function oik_trace_files_options() {
	oik_require( 	"includes/class-trace-logs.php", "oik-bwtrace" );
	$oik_trace_logs = new trace_logs();
	$oik_trace_logs->display();
}
	

/**
 * Display the trace options box
 *
 * Note: The fields suffixed _ajax apply when the DOING_AJAX constant is true.

 *
 */
function oik_trace_options() {
  bw_form( "options.php" );
  
  $options = get_option('bw_trace_options');     
 
  stag( 'table class="form-table"' );
  bw_flush();
  settings_fields('bw_trace_options_options'); 
	
	bw_tablerow( array( __( "General browser requests", "oik-bwtrace" ) ), "tr", "th" );
  BW_::bw_textfield_arr( "bw_trace_options", __( "Trace file", "oik-bwtrace" ), $options, 'file', 60 );
  bw_checkbox_arr( "bw_trace_options", __( "Trace enabled", "oik-bwtrace" ), $options, 'trace' );
  bw_checkbox_arr( "bw_trace_options", __( "Reset trace file every transaction", "oik-bwtrace" ), $options, 'reset' );
	BW_::bw_textfield_arr( "bw_trace_options", __( "Trace file generation limit", "oik-bwtrace" ), $options, 'limit', 5 ); 
	
	bw_tablerow( array( __( "AJAX requests", "oik-bwtrace" ) ), "tr", "th" );
  BW_::bw_textfield_arr( "bw_trace_options", __( "AJAX trace file", "oik-bwtrace" ), $options, 'file_ajax', 60 );
  bw_checkbox_arr( "bw_trace_options", __( "AJAX trace enabled", "oik-bwtrace" ), $options, 'trace_ajax' );
  bw_checkbox_arr( "bw_trace_options", __( "Reset AJAX trace file every AJAX transaction", "oik-bwtrace" ), $options, 'reset_ajax' );
	BW_::bw_textfield_arr( "bw_trace_options", __( "AJAX trace file generation limit", "oik-bwtrace" ), $options, 'limit_ajax', 5 ); 
	
	bw_tablerow( array( __( "REST requests", "oik-bwtrace" ) ), "tr", "th" );
  BW_::bw_textfield_arr( "bw_trace_options", __( "REST trace file", "oik-bwtrace" ), $options, 'file_rest', 60 );
  bw_checkbox_arr( "bw_trace_options", __( "REST trace enabled", "oik-bwtrace" ), $options, 'trace_rest' );
  bw_checkbox_arr( "bw_trace_options", __( "Reset REST trace file every REST transaction", "oik-bwtrace" ), $options, 'reset_rest' );
	BW_::bw_textfield_arr( "bw_trace_options", __( "REST trace file generation limit", "oik-bwtrace" ), $options, 'limit_rest', 5 ); 
	
	
	bw_tablerow( array( __( "Batch requests", "oik-bwtrace" ) ), "tr", "th" );
  BW_::bw_textfield_arr( "bw_trace_options", __( "Batch trace file", "oik-bwtrace" ), $options, 'file_cli', 60 );
  bw_checkbox_arr( "bw_trace_options", __( "Batch trace enabled", "oik-bwtrace" ), $options, 'trace_cli' );
  bw_checkbox_arr( "bw_trace_options", __( "Reset batch trace file each invocation", "oik-bwtrace" ), $options, 'reset_cli' );
	BW_::bw_textfield_arr( "bw_trace_options", __( "Batch trace file generation limit", "oik-bwtrace" ), $options, 'limit_cli', 5 ); 
	
	// Does this need includes/bwtrace.php?
	bw_tablerow( array( __( "Trace records", "oik-bwtrace" ) ), "tr", "th" );
	$trace_levels = bw_list_trace_levels();
	// Do we need to default this after upgrade?
	//$options['level'] = bw_trace_level();
	BW_::bw_select_arr( "bw_trace_options", __( "Trace level", "oik-bwtrace" ), $options, 'level', array( "#options" => $trace_levels ) );
  bw_checkbox_arr( "bw_trace_options", __( "Fully qualified file names", "oik-bwtrace" ), $options, 'qualified' );
  bw_checkbox_arr( "bw_trace_options", __( "Include trace record count", "oik-bwtrace" ), $options, 'count' );
  bw_checkbox_arr( "bw_trace_options", __( "Include timestamp", "oik-bwtrace" ), $options, 'date' );
  bw_checkbox_arr( "bw_trace_options", __( "Include current filter", "oik-bwtrace" ), $options, 'filters' );
  bw_checkbox_arr( "bw_trace_options", __( "Include number of queries", "oik-bwtrace" ), $options, "num_queries" );
  bw_checkbox_arr( "bw_trace_options", __( "Include post ID", "oik-bwtrace" ), $options, "post_id" );
	$memory_limit = ini_get( "memory_limit" );
	bw_trace( $memory_limit, "memory_limit", false, BW_TRACE_DEBUG );
  bw_checkbox_arr( "bw_trace_options", sprintf( __( 'Include memory/peak usage ( limit %1$s )', "oik-bwtrace" ), $memory_limit ), $options, 'memory' );
  bw_checkbox_arr( "bw_trace_options", __( "Include files loaded count", "oik-bwtrace" ), $options, 'files' );
	
  $current_ip = bw_array_get( $_SERVER, "REMOTE_ADDR", null );
  $current_ip_text = "<br />";
	$current_ip_text .= sprintf( __( 'Current IP: %1$s', "oik-bwtrace" ), $current_ip );
	
  BW_::bw_textfield_arr( "bw_trace_options", __( "Trace specific IP", "oik-bwtrace" ) . $current_ip_text, $options, 'ip', 20 );
  
  // Trace error processing is not yet enabled.
  // textfield( "bw_trace_options[errors]", 1 ,"Trace errors (0=no,-1=all,1=E_ERROR,2=E_WARNING,4=E_PARSE, etc)", $options['errors'] );
  // bw_tablerow( array( "", "<input type=\"submit\" name=\"ok\" value=\"Save changes\" class=\"button-primary\"/>") ); 

  etag( "table" );
  BW_::p( isubmit( "ok", __( "Save changes", 'oik-bwtrace' ), null, "button-primary" ) );
  etag( "form" );
  
  bw_flush();
}

/**
 * Display trace notes
 */
function oik_trace_notes() {
  BW_::p( __( "The tracing output produced by oik-bwtrace can be used for problem determination.", "oik-bwtrace" ) );
  //BW_::p( __( "It's not for the faint hearted.", "oik-bwtrace" ) );
  BW_::p( __( "The oik-bwtrace plugin should <b>not</b> need to be activated on a live site.", "oik-bwtrace" ) );
  BW_::p( __( "If you do need to activate it, only do so for a short period of time.", "oik-bwtrace" )  );
 
  BW_::p( __( "You will need to specify the trace files directory and trace file names.", "oik-bwtrace" )  );
  BW_::p( __( "When you want to trace processing check 'Trace enabled'", "oik-bwtrace" )  );
  //BW_::p( __( "Check 'Reset trace file every transaction' to cause the trace file to be cleared for every request, including AJAX requests.", "oik-bwtrace" )  );
  BW_::p( __( "Specifying a different file name and limit for each transaction can help you to trace multiple concurrent requests." ) );
  
  //BW_::p( __( "You may find the most recent browser trace output at...", "oik-bwtrace" )  );
  //$bw_trace_url = bw_trace_url();
  
  //BW_::alink( NULL, $bw_trace_url, $bw_trace_url, __( "View trace output in your browser.", "oik-bwtrace" ) );
  
  BW_::p( __( "If you want to trace processing within some content you can use two shortcodes: [bwtron] to turn trace on and [bwtroff] to turn it off", "oik-bwtrace" )  );
  
  bw_flush();

}

/**
 * Display trace reset
 *
 */
function oik_trace_reset_notes() {
	$oik_sc_help = oik_require_lib( "oik-sc-help" ); 
	if ( $oik_sc_help && !is_wp_error( $oik_sc_help ) ) {
		bw_trace2( $oik_sc_help, "oik-sc-help", false, BW_TRACE_DEBUG );
		if ( function_exists( "bw_invoke_shortcode" ) ) {
			bw_invoke_shortcode( "bwtrace", null, __( "Use the [bwtrace] shortcode in a widget to provide an instant trace reset and page reload.", "oik-bwtrace" ) );
		} else {
			bw_trace_included_files(); 
		}
	} else {
		bw_trace2( $oik_sc_help, "oik-sc-help" );
		BW_::p( __( "Activate the oik base plugin to enable the [bwtrace] shortcode", "oik-bwtrace" ) );
	}
}

/**
 * Display Trace info
 * 
 * This displays lots of useful information about the site configuration and settings
 * Similar to PHPinfo but more aligned to WordPress and problem determination thereof.
 */
function oik_trace_info() {
	oik_require( "admin/class-oik-trace-info.php", "oik-bwtrace" );
	$oik_trace_info = new OIK_trace_info();
	$oik_trace_info->display_info();
	
}

/**
 * Displays Daily Trace summary box
 * 
 * Displays the daily trace summary settings
 * 
 * Replaces the 'trace_status_report' from bw_action_options
 */
function oik_trace_summary() {
	oik_require( "admin/class-oik-trace-summary.php", "oik-bwtrace" );
	$oik_trace_summary = new OIK_trace_summary();
	$oik_trace_summary->display_summary();
}

/**
 * Sanitize and validate trace options input
 * 
 * @param $input array Accepts an array, 
 * @return array sanitized array.
 */
function bw_trace_options_validate($input) {
  bw_trace2(); 
  $input['ip'] = trim( $input['ip']);
	return $input;
}

/**
 * Sanitize and validate trace files options input
 */
 
function bw_trace_files_options_validate( $input ) {
  bw_trace2(); 
	$valid = bw_trace_validate_directory( $input, 'trace_directory' );
	return $input;
}
 

/** 
 * Validate the bw_action_options
 * 
 * Note: If the validation function does not exist then no value is returned and the options don't get saved.
 * WordPress does not produce a warning message. 
 * 
 * @param array $input the options to be saved
 * @return array validated input
 */ 
function bw_action_options_validate( $input ) {
  bw_trace2( $input ); 
  return $input;
}

/** 
 * Validate the bw_summary_options
 * 
 * Note: If the validation function does not exist then no value is returned and the options don't get saved.
 * WordPress does not produce a warning message. 
 * 
 * @param array $input the options to be saved
 * @return array validated input
 */ 
function bw_summary_options_validate( $input ) {
  bw_trace2( $input ); 
  return $input;
}

/**
 * Validate the trace file directory
 *
 */
function bw_trace_validate_directory( $array, $key ) {
	//print_r( $array );
	$valid = false;
	$directory = bw_array_get( $array, $key );
	$directory = trim( $directory );
	if ( empty( $directory ) ) {
		//p( "Trace file directory must be specified" );
		//gob();
		add_settings_error( $key, $key, "Trace files directory must be specified." );
	} else {
		$valid = false;
		
		oik_require( "includes/class-trace-files-directory.php", "oik-bwtrace" );
		$trace_files_directory = new trace_files_directory();
		$trace_files_directory->validate_trace_files_directory( $directory );
		$valid = $trace_files_directory->is_valid();
		if ( !$valid ) {
			add_settings_error( $key, $key, "Invalid Trace files directory." );
		}
		
	}
	return $valid;
}

/**
 * Return the trace file URL
 *
 * May not return the correct URL for WordPress MultiSite
 *
 * @param string $option the option set.
 * @return string the trace file URL
 */  
function bw_trace_url( $option='bw_trace_options', $ajax=false ) {
  //$options = get_option( $option ); 
	//$file = bw_trace_file_name( $options, $ajax );
  //$bw_trace_url = get_site_url( NULL, $file );
	
	global $bw_trace;
	$bw_trace_url = null;
	if ( $bw_trace ) {
		$bw_trace_url = $bw_trace->get_trace_file_url();
	}
  return( $bw_trace_url );
}

/**
 * 
 * Implement "activated_plugin" action for oik-bwtrace
 *
 * Arrange for the "oik-bwtrace/oik-bwtrace.php" plugin to be loaded first
 * regardless of which plugin has been activated.
 *
 * @TODO If it's network activated this should be first in "active_sitewide_plugins"
 * 
 *
 * @param string $plugin path to main plugin file
 * @param bool $network_wide true if network activated
 */
function bw_this_plugin_first( $plugin, $network_wide ) {
  if ( false == $network_wide ) {
        $this_plugin = "oik-bwtrace/oik-bwtrace.php";        
	$active_plugins = get_option( 'active_plugins' );
	$this_plugin_key = array_search( $this_plugin, $active_plugins );
	if ( $this_plugin_key ) { // if it's 0 it's the first plugin already, no need to continue
		array_splice( $active_plugins, $this_plugin_key, 1 );
		array_unshift( $active_plugins, $this_plugin );
		update_option( 'active_plugins', $active_plugins );
	}
  }                
}


