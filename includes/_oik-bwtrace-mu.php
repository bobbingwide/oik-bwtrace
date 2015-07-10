<?php // (C) Copyright Bobbing Wide 2015

/*
Plugin Name: _oik-bwtrace-MU 
Plugin URI: http://www.oik-plugins.com/oik-plugins/oik-bwtrace
Description: Debug trace for WordPress - Must Use version
Version: 1.27
Author: bobbingwide
Author URI: http://www.oik-plugins.com/author/bobbingwide
License: GPL2

    Copyright 2015 Bobbing Wide (email : herb@bobbingwide.com )

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
 * oik-bwtrace Must Use version is the file that is used to implement oik-bwtrace logic as a Must Use plugin
 *
 * Using the oik-bwtrace admin menu the user can choose to make this a MU plugin
 * which means that oik-bwtrace will be activated a lot earlier and we'll be able to count more action hooks
 * 
 * For the site administrator this is an easier way of activating trace logic than editing wp-config.php
 * and it means that we can refer to real options values, not values invented from the constants defined in the config file.
 * 
 * To get the most complete list of action hooks and filters it is necessary to create/update wp-content/db.php
 * ensuring it contains the following code
 * 
 * `
 * if ( defined( "BW_COUNT_ON" ) && true == BW_COUNT_ON ) {
 * 	 if ( function_exists( "bw_trace_count_plugins_loaded" ) ) {
 *	   bw_trace_count_plugins_loaded( BW_COUNT_ON );
 *	 }
 * }
 * ` 
 *  
 */
if ( defined( 'WP_PLUGIN_DIR' ) ) {
	$file = WP_PLUGIN_DIR .  '/oik-bwtrace/oik-bwtrace.php';
} else {
	$file = dirname( dirname( __FILE__ ) ) . '/plugins/oik-bwtrace/oik-bwtrace.php';
}

if ( file_exists( $file ) ) {
	require_once( $file );
}

  
 
 
 
