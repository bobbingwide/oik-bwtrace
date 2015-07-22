<?php // (C) Copyright Bobbing Wide 2011-2015
if ( !defined( "OIK_ADMIN_INCLUDED" ) ) {
define( "OIK_ADMIN_INCLUDED", "2.6" );

/**
 *
 * Library: oik-admin
 * Provides: oik-admin
 * Depends: bobbfunc, bobbforms
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
 */
function bw_load_plugin( $set="bw_buttons", $option="oik-button-shortcodes", $plugin=NULL ) {
  $checkbox = bw_get_option( $option, $set );
  bw_trace2( $checkbox, "checkbox" );
  if ( $checkbox == "on"  ) {
    if ( $plugin == NULL ) {
      $plugin = $option.".php" ;
    }  
    bw_trace2( $plugin, "plugin" );
    oik_require( $plugin );
  }
}    


/**
 * Create a postbox widget on the admin pages 
 *
 * 
 * Notes: Similar to Yoast's potbox (sic)
 *
 * 
 */
function oik_box( $class=NULL, $id=NULL, $title=NULL, $callback='oik_callback' ) {
  if ( $id == NULL ) {
    $id = $callback;
  }  
  sdiv( "postbox $class", $id );
  sdiv( "handlediv", NULL, kv( 'title', __( "Click to toggle" ) ) );
  br();
  ediv();
  h3( bw_translate( $title ), "hndle" );
  sdiv( "inside" );
  call_user_func( $callback );
  ediv( "inside" );
  ediv( "postbox" );
}

/**
 * Start a column 
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
  c( "start ecolumn" );
  ediv( "meta-box-sortables" );
  ediv( "postbox-container" );
  ediv( "metabox-holder" );
  c( "end ecolumn" );
} 

/**
 * Create an oik menu header
 *
 * Note: Removed the link to oik  
 *
 * @param string $title - title for the box
 * @param string $class - class for the box 
 */
function oik_menu_header( $title="Overview", $class="w70pc" ) {
  //oik_require( "bobbforms.inc" );
  //oik_enqueue_stylesheets();
  oik_enqueue_scripts();
  sdiv( "wrap" ); 
  //oik_require( "shortcodes/oik-bob-bing-wide.php" );
	if ( function_exists( "bw_loik" ) ) {
		$loik = bw_loik();
	} else {
		$loik = null;
	}
  h2( "$loik " . bw_translate( $title ) ); 
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
 * Except when 'SCRIPT_DEBUG' is true; then each script is loaded separately
  
 * 'dashboard' enables postbox toggling
 * It's not necessary to add most of these as they are pre-requisites 
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
