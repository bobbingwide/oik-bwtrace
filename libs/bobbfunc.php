<?php // (C) Copyright Bobbing Wide 2009-2015
if ( !defined( "BOBBFUNC_INCLUDED" ) ) {
define( "BOBBFUNC_INCLUDED", "2.6" );

/**
 * HTML output library functions
 * 
 * Library: bobbfunc
 * Depends: oik_boot, bwtrace, bobbcomp
 * Provides: bobbfunc
 *
 * These functions were part of the oik base plugin in bobbfunc.inc and bobbcomp.inc
 * Some functions may now be unused. This hasn't yet been confirmed.
 * 
 * 
 */

/** 
 * Return the plugin version of the oik base plugin
 * @return string $version e.g. 2.0-alpha, 2.0-RC1, 2.0, 2.0.mmdd 
 */
function bw_oik_version() { 
  oik_require( "admin/oik-plugins.inc" );
  $version = bw_get_plugin_version();
  return( $version );
}

//require_once( "oik_boot.php" );   /* Don't use oik_require here */

/**
 * bw API partial equivalent of PHP's output buffering
 * 
 * Note: This isn't really an output buffer
 * Use bw_push() and bw_pop() if you need to stack buffers during nested processing.
 * @global $bwecho is used by the bw APIs to create the HTML output that is then returned from the shortcode functions  
 * @global $bwechos counts the number of times that bw_echo() has been called
 * 
 * @param $string - the string to be "echo'd"
 * 
*/
function bw_echo( $string ) {
  global $bwecho, $bwechos;
  $bwecho.=$string;
  $bwechos++;
}

/**
 * Use bw_flush() to echo the contents of $bwecho then empty it
 * 
 * You may need to use this before calling a WordPress function that echoes its output 
 * directly rather than through a buffer. e.g. before calling settings_fields()
 */
function bw_flush() {
  global $bwecho;
  echo $bwecho;
  //bw_trace( "inside bw_flush" );
  //bw_trace( $bwecho );
  //bw_trace( "after" );
  $bwecho = NULL;
  // bw_backtrace();
}

/**
 * Use bw_ret() to return the contents of $bwecho, leaving the global value as NULL
 * 
 * @return string - the contents of @global $bwecho prior to it being emptied
 */
function bw_ret() {
  global $bwecho;
  $bwret = $bwecho;
  $bwecho = NULL;
  //bw_trace( __FUNCTION__ );
  //bw_backtrace();
  //bw_trace2( $bwret );
  return( $bwret );
}

/**
 * Push then empty the current $bwecho buffer
 *
 * We need to cater for when nested shortcodes are expanded, especially when processing 
 * excerpts.
 * 
 */ 
function bw_push() {
 global $bwecho, $bwecho_array;
 $bwecho_array[] = $bwecho;
 $bwecho = null;
}

/** 
 * Restore the previous $bwecho buffer
 */
function bw_pop() {
  global $bwecho, $bwecho_array;
  $bwecho = array_pop( $bwecho_array ); 
}   

/**
 * Perform nested shortcode expansion
 *
 * @param string $content - the content to be expanded
 * @return string the content after shortcode expansion
 *
 * Performance question - is there any point testing for a '[' in the string? i.e. How expensive is do_shortcode() ? 
 */
function bw_do_shortcode( $content ) {
  bw_push(); 
  $content = do_shortcode( $content );
  bw_pop(); 
  return( $content );
}

/**
 * Return an image tag
 * 
 * @param string $class  CSS classes for this image
 * @param string $jpg file name of the image (src=)
 * @param string $title value for the title= and alt= attributes
 * @param string $width width of the image
 * @param string $height height of the image
 * @return string HTML of the img tag
 
 * Note: This function does not handle an id= attribute
 */
function retimage( $class, $jpg, $title=NULL, $width=NULL, $height=NULL, $extras=null ) {
  $img = '<img class="' .  $class . '" ';  
  $img .= 'src="' .  bw_expand_link( $jpg ) . '" ';
  if ( !is_null( $width))
    $img .= 'width="' . $width . '" ';
  if ( !is_null( $height))
    $img .= 'height="' . $height . '" ';
  if ( !is_null( $title ) ) {
    $title = strip_tags( $title );
    $img .= 'title="' . $title . '" ';
    $img .= 'alt="' . $title . '" ';
  } 
  if ( $extras ) { 
    $img .= $extras; 
  }
  $img .= " />";
  return( $img );
}
 
/**
 * Create a keyword value pair
 *
 * If the value is not null returns the keyword value pair in format ' $keyword="$value"'
 *  
 * 
 * @param string $keyword - the keyword name e.g. class
 * @param string $value - the value(s) e.g. "bw_class w50pc"
 * @return string $kv
 */   
function kv( $keyword, $value=null ) {
  if ( $value != null ) {
    $kv = ' '.$keyword . '="' . $value .'"';
  } else {
    $kv = '';
  }  
  return( $kv );  
} 

/**
 * Format a sanitized title= parameter 
 * @param $string - the title
 * @return $string
 */  
function atitle( $title=NULL ) {
  $title = wp_strip_all_tags( $title );
  $title = esc_attr( $title );
  return( kv( "title", $title ) );
} 

/**
 * Create an anchor tag for linking within a page
 *
 * @param string $name - the anchor name
 * @param string $text - optional link text
 */ 
function aname( $name, $text=null ) { 
  //stag( 'a' );
  e( '<a name="'. $name . '">' . $text . '</a>' );
  //etag( 'a' );
  // return( bw_ret() );
} 

/**
 * Create a translatable link
 */
function _alink( $class=NULL, $url, $linktori=NULL, $alt=NULL, $id=NULL, $extra=NULL ) {
  $linktoril10n = bw_translate( $linktori );
  if ( is_null( $alt ) || $linktori == $alt ) {
    $altl10n = $linktoril10n;
  } else {
    $altl10n = bw_translate( $alt );
  }     
  $link = retlink( $class, $url, $linktoril10n, $altl10n, $id, $extra );
  e( $link );
}   

/**
 * Create a link
 * 
 * @param string $class - the classes for the anchor tag
 * @param string $url - the fully formed URL e.g. http://www.oik-plugins.com
 * @param string $linktori - is the text or image
 * @param string $alt - if NULL will use $linktori
 * @param string $id - the unique ID for the anchor tag
 * @param string $extra - anything else that needs to go in the <a> tag. e.g. 'onclick=then some javascript' 
 *
 * @uses retlink()
 * 
*/   
function alink( $class=NULL, $url, $linktori=NULL, $alt=NULL, $id=NULL, $extra=NULL ) {
  $link = retlink( $class, $url, $linktori, $alt, $id, $extra );
  e( $link ); 
}

/**
 * Return a well formed link
 *
 * Parameters as for alink()
*/
function retlink( $class=NULL, $url, $linktori=NULL, $alt=NULL, $id=NULL, $extra=NULL  ) {
  if ( is_null( $linktori ) )
    $linktori = $url;
  $link = "<a" ;
  $link .= kv( "class", $class ); // aclass( $class );
  $link .= kv( "id", $id ); // aid( $id );
  $link .= kv( "href", $url ); // ahref( $url );
  if ( is_null( $alt ) )
     $alt = $linktori;
  // Is alt= allowed with XHTML Strict 1.0?    
  // aalt( $alt );
  $link .= atitle( $alt );
  if ( $extra )
    $link .= $extra ;
  $link .= ">";
  if ( $linktori )
    $link .= $linktori;
  $link .= "</a>";
  return( $link );
}  
 
/**
 * Return an HTML opening tag
 * 
 * @param string $tag - the HTML tag. e.g. span
 * @param string $class - CSS classes
 * @param string $id - unique ID
 * @param string $extra - additional fields formatted using kv()
 * @return string an HTML opening tag
 */
function retstag( $tag, $class=NULL, $id=NULL, $extra=NULL ) {   
  $stag = '<' . $tag ;
  if ( $class <> NULL )
     $stag.= ' class="' . $class. '"';
  if ( $id <> NULL )
     $stag.= ' id="' . $id. '"';
  if ( $extra <> NULL )
    $stag .= ' '. $extra;   
  $stag.= '>';
  return( $stag );
}

/**
 * Return a start tag if the class is not null 
 */
function nullretstag( $tag, $class=NULL ) {
   $ret = '';
   if ( $class <> NULL )
      $ret = retstag( $tag, $class );
   return( $ret );     
} 

/**
 * Output an HTML opening tag
 *
 * The tag is written to the internal buffer
 * 
 * @param string $tag - the HTML tag. e.g. span
 * @param string $class - CSS classes
 * @param string $id - unique ID
 * @param string $extra - additional fields formatted using kv()
 * 
 */
function stag( $tag, $class=NULL, $id=NULL, $extra=NULL ) {
  bw_echo( retstag( $tag, $class, $id, $extra ));
}

/**
 * Start an ordered list
 */
function sol( $class=NULL, $id=NULL ) {
   stag( "ol", $class, $id );
}        

/** 
 * Start an unordered list
 */ 
function sul( $class=NULL, $id=NULL, $extra=null ) {
   stag( "ul", $class, $id, $extra );
} 

/**
 * Start a div
 */       
function sdiv( $class=NULL, $id=NULL, $extra=NULL ) {
   stag( "div", $class, $id, $extra );
}

/** 
 * End an ordered list
 */
function eol() {
   bw_echo( '</ol>' );
}
 
/**
 * End an unordered list
 */  
function eul() {
   bw_echo( '</ul>' );
}

/** 
 * End a div
 */
function ediv() {
   bw_echo( '</div>' );
}

/**
 * Create a dummy div which may be used for placing graphics using background images in CSS 
 */
function sediv( $class=NULL, $id=NULL, $extra=NULL ) {
 sdiv( $class, $id, $extra );
 ediv();
}   

/**
 * End a paragraph (p) tag
 */
function ep() {
  bw_echo( '</p>' );
}

/** 
 * Return an end tag if the class is not null 
 * 
 * @see nullretstag()
 */
function nullretetag( $tag, $class=NULL ) {
  $ret = '';
  if ( $class <> NULL )
     $ret = retetag( $tag );
  return( $ret );   
}

/**
 * Return an end tag 
 */
function retetag( $tag ) {
   return( '</'.$tag.'>');
}  

/** 
 * Output an end tag
 */
function etag( $tag ) {
  //  bw_echo( '</'.$tag.'>'."\n";
  bw_echo( '</'.$tag.'>' );
}    

/**
 * Start a paragraph
 * 
 * @see sp()
 */
function sp( $class=NULL, $id=NULL ) {
   stag( "p", $class, $id );
} 

/**
 * Output a paragraph of translatable text
 *
 * i18n note: If the text you are displaying contains variables to be inserted into the message then you should use p_() instead
 * and perform the translation prior to calling that function.
 *   
 */
function p( $text=NULL, $class=NULL, $id=NULL ) {
  sp( $class, $id );
  if ( !is_null( $text ))
    e( bw_translate( $text ) );
  etag( "p" );
}

/**
 * Output a paragraph of translated text 
 */
function p_( $text=null, $class=null, $id=null ) {
  sp( $class, $id );
  if ( !is_null( $text ))
    e( $text );
  etag( "p" );
}

/**
 * Output a heading of translated text
 * 
 * 
 */
function hn( $text, $level, $class, $id ) {
  stag( "h".$level, $class, $id );
  e( $text );
  etag( "h".$level );
}

function h1( $text, $class=NULL, $id=NULL ) {
  hn( $text, "1", $class, $id ); 
}

function h2( $text, $class=NULL, $id=NULL ) {
  hn( $text, "2", $class, $id );  
}

function h3( $text, $class=NULL, $id=NULL ) {
  hn( $text, "3", $class, $id ); 
}

function h4( $text, $class=NULL, $id=NULL ) {
  hn( $text, "4", $class, $id ); 
}

function h5( $text, $class=NULL, $id=NULL ) {
  hn( $text, "5", $class, $id ); 
}

function h6( $text, $class=NULL, $id=NULL ) {
  hn( $text, "6", $class, $id ); 
}

/** 
 * Output some translated text
 * 
 * Function e() replaces the original t() function used in Bobbing Wide custom code
 * since for Drupal t() is already defined for translatable text.
 *
 * Function bwt() does a similar job but also performs some strange filtering if required.
 *
 * When you want to output text that is translatable use: 
 *   bwt( $text ); 
 *
 * When you want to output text that is NOT translatable use:
 *   e( $text );
 *
 * Within functions where the $text parameter is translatable use:
 *   e( bw_translate( $text ) )
 * 
 */  
function bwt( $text=NULL ) {
  global $bbboing;  
  if ( !is_null( $text )) {
    if ( $bbboing )
       bw_echo( $bbboing( $text ));
    else
      bw_echo( bw_translate( $text ) ) ;
  }    
}

/**
 * @func e for bw_echo( if not NULL
 */
function e( $text = NULL ) {
  if ( !is_null( $text ))
    bw_echo( $text );
}

/** 
 * Produce a break tag with optional text to follow
 */
function br( $text=NULL ) {
	bw_echo( '<br />' );
	if ( $text ) {
		e( bw_translate( $text ) ); 
	}
}   

/**
 * Produce a horizontal rule tag
 */
function hr() {
  bw_echo( '<hr />' );
}  
  

/**
 * Create a list item with a specific CSS class and/or ID
 */
function lit( $text, $class=NULL, $id=NULL ) {
  stag( "li", $class, $id );
  e( bw_translate( $text ) );
  etag( "li" );
}

/** 
 * Helper function for list item 
 */
function li( $text ) {
  lit( $text );   
} 

// Note: here we omit the 's' of span to make it easier to type
function span( $class=NULL, $id=NULL ) {
  stag( "span", $class, $id );
}

function epan() {
  etag( "span" );
}  

/* 
 * Create a span with a specific class and content
 *
 * Note: This has different parameters from sediv()
 * If you need to set an id= parameter use span() and epan(); 
 */
function sepan( $class=NULL, $text=NULL ) {
  span( $class );
  e( $text );
  epan();
}

/**
 * Output a table data field
 */
function td( $data, $class=NULL, $id=NULL ) {
  stag( "td", $class, $id );
  e( $data );
  etag( "td" );
} 

/**
 * Output a table heading
 */
function th( $data, $class=NULL, $id=NULL ) {
  stag( "th", $class, $id );
  bwt( $data );
  etag( "th" );
}         

// This routine finds the subdirectory under which this local version of the website is installed
// we need to remove this from index lookups but add it to links! 
// Example: on betterbfar in the twentyte directory it's set:
// $docroot_suffix = "/twentyte/" ;

function bw_get_docroot_suffix() {
  bw_backtrace();
  //gobang();
  $docroot_suffix = "/";
  if ( $_SERVER['SERVER_NAME'] == bw_get_option( "betterbyfar") )
  {
     $exdr = explode( '/', $_SERVER["DOCUMENT_ROOT"] );
     $exsf = explode( '/', $_SERVER['SCRIPT_FILENAME'] );
     
     $docroot_suffix = '/' . $exsf[ count( $exdr) ] . '/';
     
     // bw_debug( "_SERVER[DOCUMENT_ROOT]: " . $_SERVER["DOCUMENT_ROOT"] );
     // bw_debug( "_SERVER[REQUEST_URI]: " .  $_SERVER['REQUEST_URI'] );  
     // bw_debug( "_SERVER[SCRIPT_FILENAME]: " . $_SERVER['SCRIPT_FILENAME'] );
  
     // bw_debug( "docroot_suffix: " . $docroot_suffix );
  }
  return( $docroot_suffix );
}

/* This gets us to the right place when the link is from a sub-directory
   but it doesn't add anthing when the link is of form
     http:
     https:
     ftp:
     mailto: 
*/     
function bw_expand_link( $linkurl ) {
   if ( strpos( $linkurl, ':' ) == 0  )
      $linkurl = bw_get_docroot_suffix() . $linkurl;
   return( $linkurl) ;   
}

function strong( $text, $class=NULL, $id=NULL ) {
   stag( "strong", $class, $id ) ;
   e( $text );
   etag( "strong" );    
}

function em( $text, $class=NULL, $id=NULL ) {
   stag( "em", $class, $id ) ;
   e( $text );
   etag( "em" );    
}

/**
 * Display a blockquote
 */
function _bw_blockquote( $text, $class=NULL, $id=NULL ) {
   stag( "blockquote", $class, $id ) ;
   e( $text );
   etag( "blockquote" );    
}

/**
 * Produce a quotation tag
 */
function bw_quote( $text, $class=NULL, $id=NULL ) {
	stag( "quote", $class, $id ) ;
	e( $text );
	etag( "quote" );    
}

/**
 * Produce a cite tag
 */
function _bw_cite( $text, $class=NULL, $id=NULL ) {
	stag( "cite", $class, $id ) ;
	e( $text );
	etag( "cite" );    
}

/** 
 * Create an abbr tag 
 *
 * Renamed from abbr() which has been deprecated
 */
function _bw_abbr( $title="OIK Information Kit", $abbrev="oik" ) {
	if ( $abbrev ) {
		$abbr = '<abbr title="';
		$abbr .= $title;
		$abbr .= '">';
		$abbr .= $abbrev; 
		$abbr .= '</abbr>';
		e( $abbr );
	}  
}

/** 
 * Create an acronym tag 
 *
 * Renamed from acronym() which has been deprecated.
 * 
 * Note: acronym becomes obsolete in HTML5
 */
function _bw_acronym( $title="OIK Information Kit", $acronym="oik" ) {
	if ( $acronym ) {
		$acro = '<acronym title="';
		$acro .= $title;
		$acro .= '">';
		$acro .= $acronym; 
		$acro .= '</acronym>';
		e( $acro );
	}  
}

/**
 * Create an HTML comment
 *
 * Renamed from c() which will be deprecated
 */ 
function _bw_c( $text ) {
  bw_echo( '<!--' . $text . '-->' );
}

/**
 * Create an HTML comment
 *
 * @TODO Deprecate in favour of _bw_c()
 */
function c( $text ) { _bw_c( $text ); } 

function bw_debug_on() {
  global $bw_debug_on;
  $bw_debug_on = TRUE;
}

function bw_debug_off() {
  global $bw_debug_on;
  $bw_debug_on = FALSE;
}     
     

function bw_debug( $text ) {
  global $oktop, $bw_debug_on;
  if ($bw_debug_on)
  {
    if ( $oktop )
      p( $bw_debug_on . $text );
    else
      _bw_c( $text ); 
  }     
}


// Deleted bw()
// Deleted ebw()
// Moved logic for art_button() to bobbcomp.inc
// Deleted bw_default()
// Deleted bw_gallery()

/**
 * Format a date with the specified format
 *
 * @param string $date - either a number - representing the UNIX date or a recognisable date string or NULL - for the current date
 *  e.g. 1293840000 => 2011-01-01
 *       2011-12-31 => 2011-12-31
 * @param string $format - date formatting string
 * @returns string $date - the date formatted according to the $format string 
 *
 * A value of NULL will be returned as NULL
 *
 * strtotime() works for dates NOT in format yyyy-mm-dd e.g. '5-10-1955' or '30 Jul 1958'
 * Note: Dates before 1970-01-01 are stored as negative values.
 *
 */
function bw_format_date( $date=NULL, $format="Y-m-d" ) {
  if ( $date != NULL ) {
    $date = trim( $date );
    if ( !is_numeric( $date ) ) {
      $date = strtotime( $date );
    } 
    $date = date( $format, $date );
    // bw_trace2( $date, "date" );
  } else {
    $date = date( $format );
  }  
  return( $date );
}

/**
 * Validate as true or false
 * 
 * Simple function to validate a field as TRUE or FALSE given a big list of strings that represent TRUE 
 *
 * @param string $field - value to be validated. If null then the value returned is FALSE
 * @param string $trues - list of TRUE values. Now includes "on" to allow for checkbox fields
 * @param string $falses - list of FALSE values  Note: No need for the $falses parameter at present
 * @return bool true or false
 */
function bw_validate_torf( $field, $trues=",true,yes,1,on", $falses=NULL ) {
  $torf = FALSE;
  if ( $field ) {
    $field = ",".strtolower( $field );
    //bw_trace( $field, __FUNCTION__, __LINE__, __FILE__, "field" );
    //bw_trace( $trues, __FUNCTION__, __LINE__, __FILE__, "trues" );
    $pos = strpos( $trues, $field );
    //bw_trace( $pos, __FUNCTION__, __LINE__, __FILE__, "pos" );
    if ( $pos !== FALSE )
      $torf = TRUE;
  }    
  return( $torf );
}

/** 
 * Return what might be the plugin name with hyphens and lowercase chars
 * 
 * Strip any tags - the WordPress UI likes adding <br />'s where we don't want them
 */
function bw_plugin_namify( $name ) {
  $name = trim( $name );
  $name = str_replace( ' ', '-', $name );
  $name = str_replace( '_', '-', $name );
  $name = strtolower( $name );
  //bw_trace2();
  return( $name ); 
}
  
/**
 * Return the function name of the function to invoke built from parms
 *
 * @param string $prefix - the function name prefix e.g. "_bw_create_content"
 * @param string $key - the multi-word key e.g. About oik-plugins"
 * @return string $funcname - the function name to invoke
 * 
 * Example: Return the function name to create content for an about us page
 * $funcname = bw_funcname( "_bw_create_content", "About oik-plugins" );
 * 
 * Would return the most detailed function that exists
 *  _bw_create_content
 *  _bw_create_content_about
 *  _bw_create_content_about_oik
 *  _bw_create_content_about_oik_plugins
 *
 * Note: In the original version of this code it happily produced
 * _sc_help_caption
 * for wp_caption
 * So check what happens in the setup plugin **?**
*/
function bw_funcname( $prefix, $key, $value=NULL ) {
  $funcname = $prefix; 
  $testname = $funcname;
  $keys = explode( "-", bw_plugin_namify( $key ));
  foreach ( $keys as $keyword ) {
    $testname .= '_'.$keyword;
    //bw_trace2( $testname, "testname", false );
    if ( function_exists( $testname ) ) {
      $funcname = $testname;
    }  
  }
  return( $funcname );
} 

/**
 * Return the URL for a resource file
 * 
 * @param string $file - the name of the file within the plugin - leading '/' omitted
 * @param string $plugin - the plugin's stub
 * @return string fully qualified URL to the resource file
 * 
 * @uses plugin_dir_url() to find the plugin base directory then appends the plugin folder and file name
 */
function oik_url( $file=null, $plugin='oik' ) {
  $url = plugin_dir_url( null );
  $url .= "$plugin/$file" ;
  return( $url ); 
}

/**
 * Display shortcode help
 * 
 * Display a simple line of help to explain the shortcode
 *
 * Need to find out more about other shortcodes and 
 * then find a way of capturing the registration information for a shortcode
 * it should be possible to find the plugin that implements the shortcode
 * but we might not know the location of the function that we can call 
 * if the shortcode is a lazy shortcode.
 * 
 * @param string $shortcode - the shortcode name
 */
function bw_sc_help( $shortcode="oik" ) {
  oik_require( "includes/oik-sc-help.inc" );
  bw_lazy_sc_help( $shortcode );
}

/**
 * Display a shortcode's syntax
 *
 * @param string $shortcode - the shortcode name
 * @param string $callback - the callback function - which may not be passed 
 */
function bw_sc_syntax( $shortcode="oik", $callback=null ) {
  oik_require( "includes/oik-sc-help.inc" );
  bw_lazy_sc_syntax( $shortcode, $callback );
}

/**
 * Display a shortcode example
 */
function bw_sc_example( $shortcode="oik", $atts=null ) {
  oik_require( "includes/oik-sc-help.inc" );
  bw_lazy_sc_example( $shortcode, $atts );
}

/**
 * Display a shortcode snippet
 */
function bw_sc_snippet( $shortcode="oik" ) {
  oik_require( "includes/oik-sc-help.inc" );
  bw_lazy_sc_snippet( $shortcode );
}

/**
  * Dynamic jQuery setting the selector, function and option parameters
  * When should we use?
  * 
  * jQuery(window).load(function() - when you need to wait for images to load?
  * jQuery(function()
  * jQuery(document).ready(function()
  *
*/  
if ( !function_exists( "bw_jquery" ) ) {
function bw_jquery( $selector, $method, $parms=null, $windowload=false ) {
  bw_jq( "<script type=\"text/javascript\">" );
  if ( $windowload ) {
    $jqfn = "jQuery(window).load(function()";
  } else {
    $jqfn = "jQuery(document).ready(function()"; 
  }    
  $function = "$jqfn { jQuery( \"$selector\" ).$method( $parms ); });";
  bw_jq( $function );
  bw_jq( "</script>" );
} 
}

/**
 * Flush the inline jQuery code to the WordPress footer 
 * 
 * @globals bw_jq
 */
function bw_jq_flush() {
  global $bw_jq;
  echo $bw_jq;
  $bw_jq = null;
}  

/**
 * Append some more jQuery code to be output later
 * 
 * @param $text - some well formed jQuery code
 * @global $bw_jq
 */
function bw_jq( $text ) {
  global $bw_jq;
  if ( !isset( $bw_jq ) ) {
    wp_enqueue_script( 'jquery' ); 
    if ( !is_admin() ) {
      add_action( 'wp_footer', "bw_jq_flush", 25 );
    }  
    //bw_trace2( $bw_jq, "bw_jq not set" );  
  }
  $bw_jq .=$text;
  //bw_trace2( $bw_jq, "bw_jq", false );  

  if ( is_admin() ) {
    bw_jq_flush();
  }  
}

/**
 * json_encode without Warnings
 *
 * JSON_NUMERIC_CHECK was added in PHP version 5.3.3
 * Here we may need to perform our own numeric conversion to strip out the damage done by json_encode 
 * if the PHP version is earlier than this. **?** Not implemented yet! 
 *
 */
function bw_json_encode( $parms, $option=null ) { 
  if ( version_compare( PHP_VERSION, "5.3.3", "<" ) ) {
    $json = json_encode( $parms );
  } else {
    $json = json_encode( $parms, $option );
  }
  return( $json ); 
} 

/**
 * Format an array of parms for jQuery 
 * 
 * If it's not an array we treat it as an already formatted string
 * OR a single parameter and value
 *
 * @uses json_encode() which by default encloses numeric values as string, which can cause problems for some jQuery code
 * (e.g. jQuery Nivo slider) SO we pass the JSON_NUMERIC_CHECK option to prevent this.
*/
if ( !function_exists( "bw_jkv" ) ) {

function bw_jkv( $parms, $value=null, $json_options=null ) {
  if ( is_array( $parms ) ) {
    bw_jtorf( $parms );
    if ( $json_options == null && defined( 'JSON_NUMERIC_CHECK' )) {
      $json_options = (JSON_NUMERIC_CHECK | JSON_FORCE_OBJECT);
    } else { 
      // We can't use JSON_NUMERIC_CHECK if it's not defined
    }  
    $jqoption = bw_json_encode( $parms, $json_options );
  } else {
    $jqoption = "{";
    if ( $value ) { 
      $jqoption .= "$parms : $value";
    } else {
      $jqoption .= $parms;
    }  
    $jqoption .= "}";
  }
  //bw_trace2( $jqoption, "jqoption" );
  return( $jqoption );
}
}

/**
 * Pre-processing for json_encode to convert
 * "on" == "true" to 1 == true
 * "off" == "0" to 0 == false
 * torf = true or false
 * This will allow WordPress options which are checkboxes to be passed as jQuery boolean parameters
 * where the default is true so we need to pass false.
 * Used in conjunction with a hidden field for checkboxes.
 *
 * This code also removes any entries where the option value is blank...
 * allowing the jQuery code to use its hardcoded default.
 * 
*/
if ( !function_exists( "bw_jtorf" ) ) {

function bw_jtorf( &$parms ) {
  foreach ( $parms as $parm => $value ) {
    if ( $value == "on" ) {
      $parms[$parm] = 1;
    } elseif ( $value == "0" ) {
      $parms[$parm] = 0;
    } elseif ( $value == "" ) { 
      unset( $parms[$parm] );  
    }    
  }
  //bw_trace2();
}
}

/**
 * Convert a simple array into an associative array keyed on the value
 * @param array $array - a simple array - with at least one entry
 * @return array $assoc_array
*/ 
function bw_assoc( $array ) {
  $assoc_array = array();
  foreach ( $array as $key => $value  ) {
    $assoc_array[$value] = $value;
  }
  return( $assoc_array );
}    

/**
 *  Convert array of file names to array of urls
 *
 * @param array $files - array of file names 
 * @param array $atts - attributes for get_post
 * @return array $urls
 * 
 * We filter the results to take into account SOME of the possible parameters:
 * 
 * 'numberposts' - we accept ALL by default 
 *   'offset' - start from the first by default 
*/ 
function bw_file_to_url( $files, $atts=null ) {
  $offset = bw_array_get( $atts, 'offset', 0 );
  $numberposts = bw_array_get( $atts, "numberposts", count( $files ) );
  $urls = array();
  if ( count( $files ) ) {
    foreach( $files as $file ) {
      if ( ( $offset <= 0 ) && ( $numberposts > 0 ) ) {
        $urls[] = plugin_dir_url( $file ) . basename( $file );
      }  
      $offset--;
      $numberposts--;
    }
  }  
  //bw_trace2( $urls );
  return( $urls );
}    

/** 
 * Return the URL that this image links to
 *
 * If the returned value is numeric ( not 0 ) then we get the permalink for that value.
 * else we return the given link.
 *
 * @param int $postid - ID of the attachment
 * @return string $permalink URL for the link
 */
function bw_get_image_link( $postid ) {
  $permalink = get_post_meta( $postid, "_bw_image_link", true );  
  // bw_trace2( $permalink, "permalink" );
  if ( !$permalink ) {
    $permalink = get_permalink( $postid );
  } elseif ( is_numeric( $permalink ) ) {
    $permalink = get_permalink( $permalink );
  }
  // bw_trace2( $permalink, "permalink after", false);
  return( $permalink);
}

/**
 * Recreate a WordPress option with the defined autoload value 
 * @param string $option name
 * @param string $autoload value: 'yes'|'no'. default='no' 
 * @return $options 
 * 
 * See 978-0-470-91622 p. 168 for the inspiration
 */
function bw_recreate_options( $option, $autoload="no" ) {
  $options = get_option( $option );   
  if ( $options !== FALSE ) {    
    delete_option( $option );
  }
  add_option( $option, $options, null, $autoload );
  //bw_trace2( $options );
  return( $options );
} 

/**
 * Return a non null string following the separator or null 
 * @param string $value - if null we return null
 * @param string $sep - separating string
 * @return null or $sep followed immediately by $value
 */
function bw_append( $value=null, $sep=" " ) {
  if ( $value ) 
    $value = $sep . $value;
  return $value;  
}

/**
 * This is very much like bw_default but it uses trim() to strip blanks
 * Can we change bw_default to do the same?
 */
function bw_pick_one( $preferred, $alternate ) {
  $picked = trim( $preferred );
  //bw_trace2( "!$picked!", "picked preferred" );
  if ( !$picked  ) {
    $picked = trim( $alternate );
    
    //bw_trace2( $picked, "picked alternate" );
  }
  return( $picked );
}

/**
 * Load the language specific stuff for the selected domain/plugin
 *
 * The plugin domain and plugin name are expected to match e.g. "oik", "oik-clone"
 * The language files are expected to be in the languages folder for the plugin
 *
 * @link http://codex.wordpress.org/Function_Reference/load_plugin_textdomain
 * If the path is not given then it will be the root of the plugin directory.
 *
 * @param string $domain the plugin name
 */
function bw_load_plugin_textdomain( $domain="oik" ) {
  $languages_dir =  "$domain/languages";
  //bw_trace2( $languages_dir, "languages dir" );
  load_plugin_textdomain( $domain, false, $languages_dir );
}  

/**
 * Return the current theme name
 * 
 * Note: get_current_theme() was deprecated in WP 3.4 but wp_get_theme() is NEW
 * It didn't exist in WP 3.3.1.
 * So we need a wrapper to test the WordPress version
 * 
 
C:\apache\htdocs\wordpress\wp-content\plugins\oik\bobbfunc.inc(1037:0) 2012-07-18T11:03:12+00:00 617 cf=the_content bw_get_theme(4) current theme WP_Theme Object
(
    [theme_root:WP_Theme:private] => C:\apache\htdocs\wordpress/wp-content/themes
    [headers:WP_Theme:private] => Array
        (
            [Name] => Twenty Eleven
            [ThemeURI] => http://wordpress.org/extend/themes/twentyeleven
            [Description] => The 2011 theme for WordPress is sophisticated, lightweight, and adaptable. Make it yours with a custom menu, header image, and background -- then go further with available theme options for light or dark color scheme, custom link colors, and three layout choices. Twenty Eleven comes equipped with a Showcase page template that transforms your front page into a showcase to show off your best content, widget support galore (sidebar, three footer areas, and a Showcase page widget area), and a custom "Ephemera" widget to display your Aside, Link, Quote, or Status posts. Included are styles for print and for the admin editor, support for featured images (as custom header images on posts and pages and as large images on featured "sticky" posts), and special styles for six different post formats.
            [Author] => the WordPress team
            [AuthorURI] => http://wordpress.org/
            [Version] => 1.4
            [Template] => 
            [Status] => 
            [Tags] => dark, light, white, black, gray, one-column, two-columns, left-sidebar, right-sidebar, fixed-width, flexible-width, custom-background, custom-colors, custom-header, custom-menu, editor-style, featured-image-header, featured-images, full-width-template, microformats, post-formats, rtl-language-support, sticky-post, theme-options, translation-ready
            [TextDomain] => twentyeleven
            [DomainPath] => 
        )

    [headers_sanitized:WP_Theme:private] => 
    [name_translated:WP_Theme:private] => 
    [errors:WP_Theme:private] => 
    [stylesheet:WP_Theme:private] => twentyeleven
    [template:WP_Theme:private] => twentyeleven
    [parent:WP_Theme:private] => 
    [theme_root_uri:WP_Theme:private] => 
    [textdomain_loaded:WP_Theme:private] => 
    [cache_hash:WP_Theme:private] => a3e6a9c1d55ef4070d99b81dc839928b
)
 * 
 */
function bw_get_theme() { 
  global $wp_version;
  if ( version_compare( $wp_version, '3.4', "ge" ) ) {
    $theme = wp_get_theme();
    $current_theme = $theme->stylesheet;  
  } else {
    $current_theme = get_current_theme();
  }  
  bw_trace2( $current_theme, "current theme" );
  return( $current_theme );
}    

function bw_wp_error( $code, $text=null, $data=null ) {
  oik_require( "includes/bw_error.inc" );
  return( bw_lazy_wp_error( $code, $text, $data ) );
}

/** 
 * Return the global post ID
 * @return ID - the global post ID or 0
 */
function bw_global_post_id() {
  if ( isset( $GLOBALS['post'] )) {
    $post_id = $GLOBALS['post']->ID;
  } else {
    $post_id = 0;
  }  
  return( $post_id ) ;
}

/**
 * Set/return the current post ID
 * 
 * When processing nested posts we need to determine the current post_id rather than the global post id
 * So we provide a routine to set/query the current post id
 *
 * @param ID/null $id - ID to set for the current post ID, if this is reset to 0 then we revert to using the bw_global_post_id()
 * @return ID - the value of the current post, if set.
 *
 */
function bw_current_post_id( $id=null ) {
  static $current_post_id = null;
  if ( $id !== null ) {
    $current_post_id = $id;
  }
  if ( !$current_post_id ) { 
    $current_post_id = bw_global_post_id();
  }
  //bw_trace2( $current_post_id, "current_post_id" );
  return( $current_post_id ); 
}

/**
 * Set/return some contextual information
 *
 * Contextual information such as the current post_type, shortcode being expanded, $atts for the shortcode being expanded
 * can be made accessible using this API. It saves passing complex parameters. The data can be accessed within filter and action functions.
 *
 *
 * @param string $field - the name of the field
 * @param mixed/null $value - the value of the field. It could be anything. Don't pass a parameter when querying a value
 * @return mixed - the $value of the contextual field
 */
function bw_context( $field, $value=null ) {
  static $bw_context = null;
  if ( empty( $bw_context ) ) {
    $bw_context = array();
  }
  if ( $value !== null ) {
    $bw_context[$field] = $value;
  } else {
    $value = bw_array_get( $bw_context, $field, null );
  } 
  // bw_trace2( $bw_context );
  return( $value );
} 

/**
 * Wrapper to translate - well it was! Herb 2013/10/31 
 *
 * Similar to __() but with overriding logic to disable translation
 * 
 * @param string $text - text to be translated
 * @return string $text - the translated text
 */
function bw_translate( $text ) {
  $translation = bw_context( "bw_translation" );
  if ( $translation == "off" ) {
    // Text has already been translated? 
  } else {
    $textdomain = bw_context( "textdomain" );
    if ( !$textdomain ) {
      $textdomain = "oik";
    }  
    // $text = translate( $text, $textdomain );
    // get_translations_for_domain() comes from l10n.php
    //if ( is_callable( "get_translations_for_domain" ) ) } 
      $translations = get_translations_for_domain( $textdomain );
      $text = $translations->translate( $text );
    //}  
  }  
  return( $text );
} 

/**
 * Turn off translation performed by bw_translate()
 * 
 * Helper function for bw_translate()
 */
function bw_translation_off() {
  bw_context( "bw_translation", "off" );
}

/**
 * Turn on translation performed by bw_translate()
 */
function bw_translation_on() {
  bw_context( "bw_translation", "on" );
} 

/**
 * Register some text for localization as deferred translatable text
 *
 * Part of the internationalization process is to ensure text is translatable.
 * We use bw_dtt() to register strings of text destined for internationalization.
 * This enables makepot/makeoik to extract the strings into the plugin's .pot file
 * from which localized versions can be created.
 * 
 * Note: This does not support the context 
 * 
 * @param string $key - the lookup key for the text
 * @param string $text - the text to be translated. If null then the $key is used
 * @param string $context - the text domain
 * @return string - the key - which may be assigned to a variable and used elsewhere
 * 
 */
function bw_dtt( $key, $text=null, $context=null ) {
  global $bw_dtt;
  if ( $context == null ) {
    $context = bw_context( "textdomain");
  }
  if ( $text == null ) {
    $text = $key;
  }  
  $bw_dtt[ $key ] = $text;
  if ( $context ) {  
    $bw_dtt[ $key ][$context] = $text;
  }
  //bw_trace2( $bw_dtt );
  return( $key );
}

/**
 * Retrieve the text for localization from the global $bw_dtt array
 * 
 * Note: This function is called if we haven't already got a translated version
 * 
 * @param string $key - the key of the text to be translated
 * @return string - the text to be translated, for the currently selected textdomain
 */ 
function bw_get_dtt( $key ) {
  global $bw_dtt;
  $context = bw_context( "textdomain");
  if ( $context ) {
    $context_array = bw_array_get( $bw_dtt, $context, null );
    if ( $context_array ) {
      $i18n = bw_array_get( $context_array, $key, null );
    } else {
      $i18n = null;
    }     
  } else {
    $i18n = null;
  }
  if ( !$i18n ) {
    $i18n = bw_array_get( $bw_dtt, $key, $key );
  }  
  return( $i18n );   
}

/**
 * Return the localized version of some deferred translatable text (dtt)
 *
 * @param string $key - the key to the translatable text - which may be the actual text
 * @return string $l10n - the translated text - which may end up as the untranslated text or even the key.
 * 
 * **?** Does it matter what textdomain we use for subsequent translations?
 * 
 */
function bw_tt( $key ) {
  global $bw_l10n;
  $l10n = bw_array_get( $bw_l10n, $key, null );
  if ( $l10n ) {
    // It's already been translated, return this
  } else {
    $i18n = bw_get_dtt( $key );
    $l10n = bw_translate( $i18n ); 
    // $textdomain = bw_context( "textdomain" );
    $bw_l10n[ $key ] = $l10n;
  }
  //bw_trace2( $bw_l10n );
  return( $l10n );
}

/**
 * Determine if a particular file is loaded
 * 
 *
 */
function bw_is_loaded( $file, $is_main=true ) { 
  static $bw_included_files, $bw_main;
  if ( !isset( $bw_included_files ) ) {
    $bw_included_files = get_included_files();
    $bw_main = $bw_included_files[0];
  }
  if ( $is_main ) {
    $loaded = false !== strpos( $bw_main, $file );
  } else {
    $bw_included_files = get_included_files();
    foreach ( $bw_included_files as $key => $included_file ) {
      $loaded = false !== strpos( $included_file, $file );
      if ( $loaded ) {
        $loaded = $included_file;
        break;
      }
    }   
  }
  return( $loaded );
}

} /* end !defined */
