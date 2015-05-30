<?php 
/*

    Copyright 2012-2015 Bobbing Wide (email : herb@bobbingwide.com )

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
}

/**
 * Register bw_action_options
 *
 */
function bw_action_options_init(){
  register_setting( 'bw_action_options_options', 'bw_action_options', 'bw_action_options_validate' );
}

/**
 * Register the trace options page
 *
 */
function bw_trace_options_add_page() {
        add_options_page( __('oik trace options', 'oik-bwtrace' ), __( 'oik trace options', 'oik-bwtrace' ), 'manage_options', 'bw_trace_options', 'bw_trace_options_do_page');
}


// Add menu page
function bw_action_options_add_page() {
	add_options_page('oik action options', 'oik action options', 'manage_options', 'bw_action_options', 'bw_action_options_do_page');
}

/** 
 * Settings page for oik actions logging
 */
function bw_action_options_do_page() {
  oik_menu_header( "action options", "w70pc" );
  oik_box( null, null, "Options", "oik_action_options" ); 
  oik_box( null, null, "Notes about oik actions", "oik_action_notes" ); 
  ecolumn();
  scolumn( "w30pc" );
  //oik_box( null, null, "Action reset button", "oik_action_reset_notes" ); 
  oik_box( NULL, NULL, "oik documentation", "oik_documentation" );
  oik_box( NULL, NULL, "support oik", "oik_support" );
  ecolumn();
  scolumn();
  oik_box( null, null, "Action summary", "oik_action_summary" ); 
  oik_menu_footer();
  bw_flush();
}

/**
 * Display the action options form
 */
function oik_action_options() {  
  bw_form( "options.php" );
  $options = get_option('bw_action_options');     
  stag( 'table class="form-table"' );
  bw_flush();
  settings_fields('bw_action_options_options'); 
  
  bw_textfield_arr( "bw_action_options", "Action file", $options, 'file', 60 );
  bw_checkbox_arr( "bw_action_options", "Trace actions", $options, 'actions' );
  bw_checkbox_arr( "bw_action_options", "Trace immediate actions", $options, 'immediate' );
  bw_checkbox_arr( "bw_action_options", "Count immediate actions", $options, 'count' );
  
  bw_tablerow( array( "", "<input type=\"submit\" name=\"ok\" value=\"Save changes\" class=\"button-primary\"/>") ); 

  etag( "table" ); 			
  etag( "form" );
}  
  
function oik_action_notes() {  
  p( "The actions output produced by " .bw_oik(). " actions can be used to find the sequence that actions are performed." );
  p( "It needs to be used in conjunction with the trace facility.");
  p( "The oik actions should <b>not</b> need to be activated on a live site.");
  p( "If you do need to activate it, only do so for a short period of time." );
 
  p( "You will need to specify the action file name (e.g. bwaction.loh )" );
  p( "Set actions to 'on' when you want to trace action processing, 'off' otherwise.");
  p( "Set immediate actions to 'on' when you want to trace ALL actions and filters." );
  p( "Note: Immediate action tracing will replace the current version of wp-includes/plugin.php with one that has been modified to include tracing." );
  p( "When you deselect the check box then the original file is restored." );
  p( "The code should not break your site after a WordPress upgrade." ); 
  
  p( "You may find the most recent trace action output at..." );
  $bw_action_url = bw_trace_url( 'bw_action_options' );
  
  alink( NULL, $bw_action_url, $bw_action_url, "View action output in your browser.");
  p( "The trace action log is reset at the same time as the trace log." );
  
  bw_flush();
}

function oik_action_reset_notes() {
 p(" to be written" );
}

function oik_action_summary() {  
  oik_require2( "admin/oik-bwaction.inc", "oik-bwtrace" ); 
  bw_action_summary();  
}

// Draw the menu page itself
function bw_trace_options_do_page() { 
  oik_menu_header( "trace options" );
  oik_box( null, null, "Options", "oik_trace_options" ); 
  oik_box( null, null, "Notes about oik trace", "oik_trace_notes" ); 
  ecolumn();
  
  scolumn( "w30pc" );
  oik_box( null, null, "Trace options and reset button", "oik_trace_reset_notes" ); 
  oik_box( NULL, NULL, "oik documentation", "oik_documentation" );
  oik_box( NULL, NULL, "support oik", "oik_support" );
  oik_menu_footer();
  bw_flush();
}


function oik_trace_options() {
  bw_form( "options.php" );
  
  $options = get_option('bw_trace_options');     
 
  stag( 'table class="form-table"' );
  bw_flush();
  settings_fields('bw_trace_options_options'); 
  
  bw_textfield_arr( "bw_trace_options", "Trace file", $options, 'file', 60 );
  
  bw_checkbox_arr( "bw_trace_options", "Trace enabled", $options, 'trace' );
  bw_checkbox_arr( "bw_trace_options", "Reset trace file every transaction", $options, 'reset' );
  bw_checkbox_arr( "bw_trace_options", "Include trace record count", $options, 'count' );
  bw_checkbox_arr( "bw_trace_options", "Include timestamp", $options, 'date' );
  bw_checkbox_arr( "bw_trace_options", "Fully qualified file names", $options, 'qualified' );
  bw_checkbox_arr( "bw_trace_options", "Include number of queries", $options, "num_queries" );
  bw_checkbox_arr( "bw_trace_options", "Include post ID", $options, "post_id" );
  bw_checkbox_arr( "bw_trace_options", "Include memory/peak usage", $options, 'memory' );
  $current_ip = "<br />Current IP: ";
  $current_ip .= bw_array_get( $_SERVER, "REMOTE_ADDR", null );
  bw_textfield_arr( "bw_trace_options", "Trace specific IP $current_ip", $options, 'ip', 20 );
  
  // Trace error processing is not yet enabled.
  // textfield( "bw_trace_options[errors]", 1 ,"Trace errors (0=no,-1=all,1=E_ERROR,2=E_WARNING,4=E_PARSE, etc)", $options['errors'] );
  bw_tablerow( array( "", "<input type=\"submit\" name=\"ok\" value=\"Save changes\" class=\"button-primary\"/>") ); 

  etag( "table" ); 			
  etag( "form" );
  
  bw_flush();
}

function oik_trace_notes() {
  p( "The tracing output produced by " .bw_oik(). " trace can be used for problem determination.");
  p( "It's not for the faint hearted.");
  p( "The oik-bwtrace plugin should <b>not</b> need to be activated on a live site.");
  p( "If you do need to activate it, only do so for a short period of time." );
 
  p( "You will need to specify the trace file name (e.g. bwtrace.loh )" );
  p( "When you want to trace processing check 'Trace enabled'" );
  p( "Check 'Reset trace file every transaction' to cause the trace file to be cleared for every request, including AJAX requests." );
    
  
  p("You may find the most recent trace output at..." );
  $bw_trace_url = bw_trace_url();
  
  alink( NULL, $bw_trace_url, $bw_trace_url, "View trace output in your browser.");
  
  p("If you want to trace processing within some content you can use two shortcodes: [bwtron] to turn trace on and [bwtroff] to turn it off" );
  
  bw_flush();

}

/**
 * Display trace reset
 *
 */
function oik_trace_reset_notes() {
  oik_require( "includes/oik-sc-help.inc" );
  bw_invoke_shortcode( "bwtrace", null, "Use the [bwtrace] shortcode in a widget to provide an instant trace reset and page reload." );
}

// Sanitize and validate input. Accepts an array, return a sanitized array.
function bw_trace_options_validate($input) {
	// Our first value is either 0 or 1
	//$input['option1'] = ( $input['option1'] == 1 ? 1 : 0 );
	
	// Say our second option must be safe text with no HTML tags
	//$input['sometext'] =  wp_filter_nohtml_kses($input['sometext']);
        $input['ip'] = trim( $input['ip']);
	
	return $input;
}

/** 
 * Validate the bw_action_options
 * 
 * Note: If this function does not exist then no value is returned and the options don't get saved
 * WordPress does not produce a warning message. 
 */ 
function bw_action_options_validate( $input ) {
  bw_trace2( $input ); 
  oik_require( "admin/oik-replace.inc", "oik-bwtrace" );
  $immediate = bw_array_get( $input, "immediate", false );
  $result = bw_enable_action_trace( $immediate ) ;
  return $input;
}  

function bw_trace_url( $option='bw_trace_options') {
  $options = get_option( $option ); 
  $file = bw_array_get( $options, 'file', "$option.loh" );    
  $bw_trace_url = get_site_url( NULL, $file );
  return( $bw_trace_url );
}

