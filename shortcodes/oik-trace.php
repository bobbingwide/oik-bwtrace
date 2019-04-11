<?php
if ( defined( 'OIK_TRACE_SHORTCODES_INCLUDED' ) ) return;
define( 'OIK_TRACE_SHORTCODES_INCLUDED', true );
/*

    Copyright 2012-2019 Bobbing Wide (email : herb@bobbingwide.com )

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
 * Syntax for [bwtrace] shortcode 
 * 
 * Note that the function name is based on the shortcode not the implementing function.
 * This enables both the shortcode and the help and examples to be implemented as lazy functions
 * **?** Actually it's an oversight! Herb 2012/03/20
	*/
function bwtrace__syntax( $shortcode='bwtrace' ) {
  $syntax = array( "text" => BW_::bw_skv( " ", __( "text", "oik-bwtrace" ), __( "text for the trace button", "oik-bwtrace" ) )
               //  , "option" => BW_::bw_skv( "", "view|logs", __( "trace control links to display", "oik" ) )
				//				 , "type" => BW_::bw_skv( "browser", "ajax|rest|cli", __( "Trace type" ) )
                 );
  return( $syntax );
}

/**
 * Displays examples for [bwtrace] shortcode
 */
function bwtrace__example( $shortcode='bwtrace' ) { 
  bw_invoke_shortcode( $shortcode, null, __( "Display a link to trace options.", "oik-bwtrace" ) );
  //bw_invoke_shortcode( $shortcode, "option=view", __( "To display a link to the active trace file.", "oik" ) );
  //bw_invoke_shortcode( $shortcode, "option=reset", __( "To display the trace reset only", "oik" ) );
}                   

/**
 * Implements [bwtrace] shortcode
 * 
 * Shortcode for toggling or setting trace options 
 * Provide a button for controlling trace
 *
 * @param array $atts - shortcode options
 *  option=view, reset, other
 *  text=text for the trace options button
 * @return string - the expanded shortcode. If trace is not enabled it returns null. 
 */
function bw_trace_button( $atts=NULL ) {
	global $bw_trace_on;
	if ( $bw_trace_on ) {
        $url = get_site_url( NULL, 'wp-admin/options-general.php?page=bw_trace_options' );
        $text = bw_array_get( $atts, 'text', __( "Trace options", "oik-bwtrace" ) );
        BW_::alink( "button", $url, $text, $text );
  } else {
		
	}
  return( bw_ret());  
}



/**
 * Displays trace log summary
 */
function bw_trace_logs( $atts ) {
	oik_require( "includes/class-trace-logs.php", "oik-bwtrace" );
	$trace_logs = new trace_logs();
	$trace_logs->display_summary();
}



