<?php // (C) Copyright Bobbing Wide 2012-2016
if ( !defined( 'OIK_BOOT_INCLUDED' ) ) {
define( 'OIK_BOOT_INCLUDED', "3.0.1" );
define( 'OIK_BOOT_FILE', __FILE__ );
/**
 * Library: oik_boot
 * Provides: oik_boot
 * Type: MU
 *
 * Implements shared library functions that each plugin that uses oik may expect to be loaded
 * so that it doesn't have to load these itself. 
 * 
 */


/**
 * Return the path of the oik base plugin or any particular file
 *
 * Note: You can either use oik_path() to find where oik is installed OR
 * use add_action( "init", "oik_init" ); to let oik initialise itself
 * and then you don't have to worry about including the oik header files 
 * until you need them.
 *
 * New version:
 * use add_action( "oik_loaded", 'your_init_function' );
 * to know when oik has been loaded so you can use the APIs
 * 
 * Note: oik_boot may be loaded before WordPress has done its stuff, so we may need to define some constants ourselves
 * Here we assume the file is in ABSPATH/wp-content/plugins/oik/libs so we need 4 dirnames to get back to ABSPATH
 * and then we need to convert backslashes to forward slashes and the drive letter to uppercase.
 * Currently don't think it's necessary to check the first letter but we're doing it anyway.
 */
if (!function_exists( 'oik_path' )) {
		
  if ( !defined('ABSPATH') ) {
		$abspath = dirname( dirname( dirname ( dirname( dirname( __FILE__ ))))) . '/';
    $abspath = str_replace( "\\", "/", $abspath );
		if ( ':' === substr( $abspath, 1, 1 ) ) {
			$abspath = ucfirst( $abspath );
		}
    define( 'ABSPATH', $abspath );
	}

  if ( !defined('WP_CONTENT_DIR') )
    define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' ); // no trailing slash, full paths only - WP_CONTENT_URL is defined further down
          
  if ( !defined('WP_PLUGIN_DIR') )
    define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' ); // full path, no trailing slash

  function oik_path( $file = NULL, $plugin='oik') {
  
    return( WP_PLUGIN_DIR . '/'. $plugin. '/' . $file );
  }
}
 
/**
 * invoke require_once on an oik include file or other file
 *
 * @uses oik_path()
 
 * @param string $include_file - the include file (or any other file) that you want to load
 * @param string $plugin - the plugin in which the file is located (default="oik")
 */
if (!function_exists( 'oik_require' )) {
  function oik_require( $include_file = "bobbfunc.inc", $plugin="oik" ) {
    $path = oik_path( $include_file, $plugin );
    if ( !file_exists( $path ) ) { 
      echo "<!-- File does not exist:$path! -->" ;
      if ( !is_file( $path ) ) {
        echo "<!-- File is not a real file:$path! -->" ;
      }
			echo "<!-- ";
      print_r( debug_backtrace() );
			echo " -->";
      //gobang();
      
    }  
    require_once( $path ); 
  }  
} 

if( !function_exists( "oik_require2" )) {
/**
 * Load a file which could have been relocated from one plugin to another
 * 
 * @param string $include_file - file name within the chosen $pluging e.g. admin/oik-header.inc
 * @param string $to_plugin - the first plugin to try - this is the "to" plugin to where the file has been relocated
 * @param string $from_plugin - this is the original plugin, defaulting to "oik"
 * 
 * Note: we try to be as efficient as possible loading the "new" file 
 * Note: this code does not allow for files to be renamed during relocation
 * This code does REQUIRE the file to exist somewhere! 
 */
function oik_require2( $include_file="bobbfunc.inc", $to_plugin, $from_plugin="oik" ) {
  $new_path = oik_path( $include_file, $to_plugin );
  if ( file_exists( $new_path ) ) {
    require_once( $new_path );
  } else {
    oik_require( $include_file, $from_plugin );
  }  
}
}
  
/**
 * load up the functions required to allow use of the bw/oik API
 *
 * Notes: a plugin that is dependent upon oik and/or uses the trace facility
 * should either call add_action( "init", "oik_init" ); to let oik load the required API files
 * OR, if add_action() is not yet available, call this function, if it's available.
 * In most cases all that is required initially is to load /libs/bwtrace.php
*/ 
if ( !function_exists( "oik_init" ) ) {
function oik_init( ) {
  oik_require_lib( 'bwtrace' );
}
} 
 
/** 
 * Return the array[index] or a default value if not set
 * 
 * Notes: This routine may produce a Warning message if the $index is not scalar.
 
 * @TODO I can't change it yet since there are other bits of code that may go wrong if I attempt 
 * to deal with an invalid  $index parameter. 
 * 
 * @param mixed $array - an array or object or scalar item from which to find $index
 * @param scalar $index - the array index or object property to obtain
 * @param string $default - the default value to return 
 * @return mixed - the value found at the given index
 *
 */
if ( !function_exists( 'bw_array_get' ) ) {
  function bw_array_get( $array = NULL, $index, $default=NULL ) { 
    //  sometimes we get passed an empty array as the index to the array - what should we do in this case **?** Herb 2013/10/24
    if ( is_array( $index ) ) {
      bw_backtrace( BW_TRACE_WARNING );
      //gobang();
    }
    if ( isset( $array ) ) {
      if ( is_array( $array ) ) {
        if ( isset( $array[$index] ) || array_key_exists( $index, $array ) ) {
          $value = $array[$index];
        } else {
          $value = $default;
        }  
      } elseif ( is_object( $array ) ) {
        if ( property_exists( $array, $index ) ) {
          $value = $array->$index;
        } else {
          $value = $default;
        } 
      } else {
        $value = $default;
      }  
    } else {
      $value = $default;
    }  
    return( $value );
  }
}

/**
 * Require a library, with/without oik-libs
 *
 * Locates and loads (once) a library in order to make functions available to the invoking routine.
 * This replaces oik_require() for simple library files where the plugin provides these files.
 * The library file name is expected to match the library name and to be stored in the same folder as
 * the file containing this function.	
 * Note: We don't expect "oik_boot.php" to appear anywhere in __FILE__ except the end.
 * If the oik_libs() function is not defined then we use the fallback method
 * which simply loads files and doesn't perform any version checking.
 *
 * @param string $library the name of the (registered) library
 * @param string $version the required library version. null means don't care
 * @return object/bool the library loaded or a simple bool if oik_libs is not loaded, so we used the fallback
 */
if ( !function_exists( "oik_require_lib" ) ) { 
	function oik_require_lib( $library, $version=null, $args=null ) {
		$library_file = null;
		if ( function_exists( "oik_libs" ) ) {
			$oik_libs = oik_libs();
			$library_file = $oik_libs->require_lib( $library, $version, $args );
		} else { 
			if ( $spos = strpos( $library, "/" ) ) {
				$library = substr( $library, $spos+1 ); 
			}
			$library_file = oik_require_lib_fallback( $library );
		}
		// We are dependent upon the 'bwtrace' library for these functions
		bw_trace2( $library_file, "library_file: $library", true, BW_TRACE_VERBOSE );
		bw_backtrace( BW_TRACE_VERBOSE );
		return( $library_file );
	}
}

/**
 * Load the library from fallback directories
 *
 * If the library name is in the form vendor/package
 * then we trim the vendor name to use this as the library
 * and expect the fallback dirs to include all the possible repositories
 *
 * @param string $library the name of the shared library to load
 * @return string $library_file the file name of the loaded library
 */
//if ( !function_exists( "oik_require_lib_fallback" ) ) {
	function oik_require_lib_fallback( $library ) {
		if ( false === strpos( $library, ".php" ) ) {
			$library .= ".php";
		}
		$oik_lib_fallback = oik_lib_fallback( __DIR__ );
		foreach ( $oik_lib_fallback as $library_dir ) {
			$library_file = "$library_dir/$library";
			
			//echo "<b>trying: $library_file</b>" . PHP_EOL;
			if ( file_exists( $library_file ) ) {
				require_once( $library_file );
				break;
			} else {
				$library_file = false;
			}
		}
		return( $library_file );
	}
//}

/**
 * Set a fallback directory for shared library processing
 *
 * @param string $lib_dir fully qualified directory for library files with NO trailing slash
 * @return array fallback directories so far
 */
function oik_lib_fallback( $lib_dir ) {
	//echo "adding: $lib_dir " . PHP_EOL; 
	global $oik_lib_fallback;
	//echo count( $oik_lib_fallback );
	if ( empty( $oik_lib_fallback ) ) {
		//if ( __DIR__ == $lib_dir ) {
  	//	$oik_lib_fallback = array();
		//} else {
			$oik_lib_fallback = array( __DIR__ );
		//}
	} //else {
		if ( __DIR__ != $lib_dir ) {
			$oik_lib_fallback[] = $lib_dir;
		}
	//}
	return( $oik_lib_fallback );
}

/**
 * Require a file in a library
 * 
 * Locates and loads a file from a given library in order to make additional functions available to the invoking routine
 * Note: If successful the oik_lib object of the library is returned. It won't show the file name of the file loaded.
 * 
 * @param string $file the relative file name ( relative to the library's "root" file ) e.g. class-oik-autoload.php 
 * @param string $library the library name 
 * @param array $args additional parameters
 * @return bool|WP_Error|oik_lib 
 */
if ( !function_exists( "oik_require_file" ) ) { 
function oik_require_file( $file, $library, $args=null ) {
	//bw_trace2();
	if ( function_exists( "oik_libs" ) ) {
		$oik_libs = oik_libs();
		$library_file = $oik_libs->require_file( $file, $library, $args );
	} else {
		$library_file = oik_require_lib_fallback( $file );
	}
	bw_trace2( $library_file, "library_file", true, BW_TRACE_DEBUG );
	return( $library_file );	
}
} 

} /* end if !defined */
