<?php // (C) Copyright Bobbing Wide 2011-2017
if ( !defined( "OIK_ADMIN_INCLUDED" ) ) {
define( "OIK_ADMIN_INCLUDED", "3.2.0" );

/**
 *
 * Library: oik-admin
 * Provides: oik-admin
 * Depends: bobbfunc, bobbforms, class-bobbcomp
 *
 * Functions that support the display of admin pages
 * Extracted from admin/oik-admin.inc 
 * 
 * These are the shared library functions. 
 */

/**
 * Load 'plugin' file if the options checkbox is set on
 *
 * The file extension of the plugin is ".php"
 * This function implements a simple "load module" until such a time as 
 * either a) modules are implemented, b) the logic is replaced with library logic
 * 
 * @param string $set the option set
 * @param string $option the option within the set
 * @param string $plugin null for the default file extension
 */
function bw_load_plugin( $set="bw_buttons", $option="oik-button-shortcodes", $plugin=NULL ) {
	$checkbox = bobbcomp::bw_get_option( $option, $set );
	bw_trace2( $checkbox, "checkbox", true, BW_TRACE_DEBUG );
	if ( $checkbox == "on"  ) {
		if ( $plugin == NULL ) {
			$plugin = $option.".php" ;
		}  
		bw_trace2( $plugin, "plugin", false, BW_TRACE_DEBUG );
		oik_require( $plugin );
	}
}


	/**
	 * Outputs a postbox widget on the admin pages 
	 *
	 * @param string $class additional CSS classes for the postbox
	 * @param string $id Unique CSS ID
	 * @param string $title Translatable title
	 * @param string $callback Callable function implementing the post box contents
	 */
	function oik_box( $class=null, $id=null, $title=null, $callback='oik_callback' ) {
		$title = bw_translate( $title );
		if ( $id == null ) {
			$id = $callback;
		}  
		sdiv( "postbox $class", $id );
		oik_handlediv( $title );
		h3( $title, "hndle" );
		sdiv( "inside" );
		call_user_func( $callback );
		ediv( "inside" );
		ediv( "postbox" );
	}

	/**
	 * Displays the toggle button for the postbox
	 * 
	 * @param string $title - translated title
	 */
	function oik_handlediv( $title ) {
		$title = sprintf( __( 'Toggle panel: %s' ), $title );
		e( '<button type="button" class="handlediv" aria-expanded="true">' );
		e( '<span class="screen-reader-text">' . $title . '</span>' );
		e( '<span class="toggle-indicator" aria-hidden="true">' );
		e( '</span>' );
		e( '</button>' );
	}

/**
 * Start a column 
 * 
 * Starts a column in the admin page
 *
 * @param string $class additional CSS classes
 * @param string $id Unique CSS ID
 */
function scolumn( $class=NULL, $id=null ) {
	sdiv( "metabox-holder" );
	sdiv( "postbox-container $class", $id  );
	sdiv( "meta-box-sortables ui-sortable" );
}

/**
 * End a column
 */
function ecolumn() {
	_bw_c( "start ecolumn" );
	ediv( "meta-box-sortables" );
	ediv( "postbox-container" );
	ediv( "metabox-holder" );
	_bw_c( "end ecolumn" );
}

/**
 * Create an oik menu header
 *
 * Note: Completely removed the link to oik  
 *
 * @param string $title - title for the box
 * @param string $class - class for the box 
 */
function oik_menu_header( $title="Overview", $class="w70pc" ) {
	oik_enqueue_scripts();
	e( wp_nonce_field( "closedpostboxes", "closedpostboxesnonce", false, false ) );
	sdiv( "wrap" ); 
	h2( bw_translate( $title ) ); 
	scolumn( $class );
}

/**
 * Create an oik menu footer
 */
function oik_menu_footer() {
	ecolumn();
	sediv( "clear" );
	ediv( "wrap");
}

/**
 * Append some non-translatable text to translatable text once it's been translated
 *
 * This is similar to using sprintf( __( "translatable_text %1$s", $non_translatable_text ) );
 * BUT it doesn't require the translator to have to worry about the position of the variable
 * AS this isn't in the text they translate.
 * 
 * @param string $translatable_text - text to be translated
 * @param string $non_translatable_text - text that's not translated
 */
function _bwtnt( $translatable_text, $non_translatable_text ) {
	$tnt = bw_translate( $translatable_text );
	$tnt .= $non_translatable_text;
	return( $tnt );
}

/**
 * Dummy validation function
 *
 * We choose to accept the input from the user without performing any validation.
 * You may think this is insecure. It probably is if there wasn't any other security.
 * It's certainly a lot easier.
 *
 * @param array $input stuff to validate
 * @return array validated stuff
 */
function oik_plugins_validate( $input ) {
	return $input;
}

/**
 * Enqueue jQuery scripts required by oik
 * 
 * What jQuery scripts do we need to make the page work as if they were dashboard widgets?
 * Where do we find out what all the others do?
 * Each of the default scripts that we enqueue gets added to the list of scripts loaded by wp-admin/load-scripts.php
 * Except when 'SCRIPT_DEBUG' is true; then each script is loaded separately.
 * 
 * 'dashboard' enables postbox toggling
 * Currently ( Jul 2017 ) we want the toggling but don't want the AJAX requests when a postbox is toggled.
 * 
 * It's not necessary to add most of the others that are commented out below as they are pre-requisites 
 * e.g. dashboard is dependent upon jquery, admin-comments and postbox
 *
 * @see wp-includes/script-loader.php
 */
function oik_enqueue_scripts() {
	//wp_enqueue_style( 'wp-pointer' ); 
	//wp_enqueue_script( 'jquery-ui' ); 
	//wp_enqueue_script( 'jquery-ui-core' ); 
	//wp_enqueue_script( 'jquery-ui-widget' ); 
	//wp_enqueue_script( 'jquery-ui-mouse' ); 
	//wp_enqueue_script( 'jquery-ui-sortable' );
	//wp_enqueue_script( 'postbox' );
	//wp_enqueue_script( 'wp-ajax-response' );
	//wp_enqueue_script( 'wp-lists' );
	//wp_enqueue_script( 'jquery-query' );
	wp_enqueue_script( 'dashboard' );
	//wp_enqueue_script( 'jquery-ui-draggable' );
	//wp_enqueue_script( 'jquery-ui-droppable' );
	//wp_enqueue_script( 'jquery-ui-tabs' );
	//wp_enqueue_script( 'jquery-ui-selectable' );
	//wp_enqueue_script( 'wp-pointer' ); 
	//wp_enqueue_script( 'utils' );
}

} /* end !defined() */
