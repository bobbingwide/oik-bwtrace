<?php // (C) Copyright Bobbing Wide 2016,2017,2019
/**
 * Class: OIK_trace_info
 *
 * Displays a summary table of lots of information that may be useful for problem determination.
 * 
 */
class OIK_trace_info {

	/**
	 * Constructor for OIK_trace_info
	 */
  function __construct() {
		
	}
	
	/**
	 * Display the trace information
	 * 
	 * Note: We don't really know how to structure this yet... there may be a lot of output
	 */
	function display_info() {
    stag( "table", "widefat" );
    stag( "thead" ); 
		$headings = array( __( "Field", "oik-bwtrace" ), __( "Value", "oik-bwtrace" ), __( "Notes", "oik-bwtrace" ) );
		bw_tablerow( $headings, "tr", "th" );
		etag( "thead" );
		stag( "tbody" );
		$this->display_fields();
		etag( "tbody" );
		etag( "table" );
		bw_flush();
	}
	
	/**
	 * Display a whole load of fields 
	 * 
	 * Each value is obtained by a standard method which may involve a fallback process
	 * 
	 * The format of the displayed table is as in this short example
	 *
	 * Field | Value | Notes
	 * ----- | ----- | ------
	 * $wp_version | 4.7-RC1 | WordPress version
	 * PHP_VERSION | 7.0.7 | End of life for your version of PHP is: 2018-12-03
	 */
	function display_fields() {
	
		//$this->display_method( $field, $type, $extra );
		$this->display_global( "wp_version", "string", __( "WordPress version", "oik-bwtrace" ) );
		$this->display_constant( "PHP_VERSION", "string", $this->php_end_of_life() );
		$this->display_constant( "WP_DEBUG", "bool" );
		$this->display_constant( "WP_DEBUG_LOG", "bool" );
		$this->display_constant( "WP_DEBUG_DISPLAY", "bool" );
		$this->display_ini( "error_reporting", "string", $this->as_url( "http://php.net/manual/en/errorfunc.configuration.php#ini.error-reporting" ) );
		$this->display_ini( "display_errors", "string", $this->as_url( "http://php.net/manual/en/errorfunc.configuration.php#ini.display-errors" ) );
		$this->display_ini( "log_errors", "bool", $this->as_url( "http://php.net/manual/en/errorfunc.configuration.php#ini.log-errors" ) );
		$this->display_ini( "error_log", "string", $this->as_url( "http://php.net/manual/en/errorfunc.configuration.php#ini.error-log" ) );
		$this->display_ini( "output_buffering", "string", __( "Set to off for better detection of Notice: Undefined messages", "oik-bwtrace" ) );
		
		//ini_set( "implicit_flush", true );
		$this->display_ini( "implicit_flush", "bool", $this->as_url( "http://php.net/manual/en/outcontrol.configuration.php#ini.implicit-flush" ) );
		
		$this->display_constant( "SCRIPT_DEBUG", "bool" );
		$this->display_constant( "JETPACK_DEV_DEBUG", "bool" );
		
		$this->display_function( __( "Multisite", "oik-bwtrace" ), "bool", "is_multisite" );
		$this->display_constant( "WP_SITEURL", "url" );
		$this->display_option( "siteurl", "url" );
		
		$this->display_constant( "WP_HOME", "url" );
		$this->display_option( "home", "url" );

		$this->display_constant( "ABSPATH", "string" );
		$this->display_constant( "WP_MEMORY_LIMIT", "string" );
		
		
		$this->display_constant( 'BW_TRACE_CONFIG_STARTUP', "bool" );
		$this->display_constant( 'BW_TRACE_ON', "bool" );
		$this->display_constant( 'BW_COUNT_ON', "bool" );
		$this->display_constant( 'BW_TRACE_LEVEL', "string" ) ;
		$this->display_constant( 'BW_TRACE_RESET', "bool" );
		$this->display_constant( "DB_NAME", "string" );	
		
		// WPMS stuff
		$this->display_constant( "MULTISITE", "bool" );
		$this->display_constant( "SUBDOMAIN_INSTALL", "bool" );
		$this->display_constant( "DOMAIN_CURRENT_SITE", "string" );
		$this->display_constant( "PATH_CURRENT_SITE", "string" );
		$this->display_constant( "SITE_ID_CURRENT_SITE", "string" );
		$this->display_constant( "BLOG_ID_CURRENT_SITE", "string" ); 
		$this->display_constant( "SUNRISE", "string" );
		
		// These globals are objects.
		//$this->display_global( "current_site", "string", "Current site" );
		//$this->display_global( "current_blog", "string", "Current blog" );
		
		
	}
	
	/**
	 * Display a field, value and extra notes
	 * 
	 * @param string $field Name for the field
	 * @param mixed $value Value of the field to be passed through the type formatter
	 * @param string $type bool | string | url | numeric | etc
	 * @param string $extra Some other stuff for the third column
	 */
	function tablerow( $field, $value, $type, $extra=null ) {
		$format_method = "as_$type";
		$displayable_value = $this->$format_method( $value );
		bw_tablerow( array( $field, $displayable_value, $extra ) );
		return( true );
	}
	
	/** 
	 * Display a global field
	 * 
	 * @param string $field name omitting the $
	 * @param string $type how to display the field
	 * @param string $extra additional stuff, as considered relevant
	 */
	function display_global( $field, $type, $extra=null ) {
		$value = bw_array_get( $GLOBALS, $field , null );
		$this->tablerow( "$". $field, $value, $type, $extra );
		$displayed = true;
		return( $displayed );
	}
	
	/**
	 * Display the value of a constant
	 */
	function display_constant( $field, $type, $extra=null ) {
		$displayed = defined( $field );
		if ( $displayed ) {
			$value = constant( $field );
			$this->tablerow( $field, $value, $type, $extra );
		} else {
			$this->tablerow( $field, "undefined", "string", $extra ); 
		}
		return( $displayed );
	}
	
	/**
	 * Display the value of an option field
	 */
	function display_option( $field, $type, $extra=null ) {
		$value = get_option( $field );
		if ( false === $value ) {
			$displayed = false;
		}	else {
			$displayed = $this->tablerow( $field, $value, $type, $extra );
		}
		return( $displayed );
	}
	
	/**
	 * Display the value of an option field
	 */
	
	function display_ini( $field, $type, $extra=null ) {
		$value= ini_get( $field );
		if ( false === $value ) {
			$displayed = false;
		}	else {
			$displayed = $this->tablerow( $field, $value, $type, $extra );
		}
		return( $displayed );
	}

	/**
	 * Display a virtual field determined by executing a function
	 * 
   */
	function display_function( $field, $type, $function ) {
		if ( is_callable( $function ) ) {
			$value = call_user_func( $function );
		} else {
			$value = null;
		}
		$this->tablerow( $field, $value, $type, null );
	}
	
	/**
	 * Display a value as a boolean
	 */ 
	function as_bool( $value ) {
		if ( $value ) {
			return( "true" );
		} else {
			return( "false" );
		}
	}
	
	/**
	 * Display a value as a string
	 */
	function as_string( $value ) {
		return( $value );
	}
	
	/**
	 * Display a value as an URL
	 */
	function as_url( $value ) {
		return( retlink( null, $value ) );
	}
	
	/** 
	 * Tell them the truth about PHP end of life
	 * 
	 * {@link https://php.net/supported-versions.php}
	 * 
	 * @return string 
	 */
	function php_end_of_life() {
		$ok = version_compare( PHP_VERSION, "5.2", "gt" );
		if ( $ok ) { 
			$php_eol = array( "5.2" => "2011-01-06" // This is still supported by WordPress
											, "5.3" => "2014-08-14"
											, "5.4" => "2015-09-03"
											, "5.5" => "2016-07-21"
											, "5.6" => "2018-12-31"
											, "7.0" => "2018-12-03"
											, "7.1" => "2019-12-01"
											, "7.2" => "2020-11-30"
											, '7.3' => '2021-12-06'
											, '7.4' => 'to be determined'
											); 
			$eol = $php_eol[ PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION ]; 
			if ( $eol < date( "Y-m-d" ) ) {
				$message = "End of life for your version of PHP was: $eol";
			} else {
				$message = "End of life for your version of PHP is: $eol";
			}	
		} else {
			$message = "You're out of support by over 6 years!";
		}
		return( $message ); 
	}
} 
