<?php 
/**
 * Copyright: (C) Copyright Bobbing Wide 2018
 * Class: OIK_trace_summary
 *
 * Daily trace summary report
 * 
 * This class should implement the methods that are needed to produce the daily trace summary report
 * We need methods:
 *
 * - To display the meta box on admin pages
 * - To know if we need to produce the report
 * - To produce the report
 * - To determine the file name for the report
 * 
 */
class OIK_trace_summary {

	public $bw_summary_options = null;

	/**
	 * Constructor for OIK_trace_summary
	 */
  function __construct() {
	}
	
	/**
	 * Display the summary options form
	 *
	 * @TODO Add support for naming the daily trace summary report file
	 *
	 */
	public function display_summary() {  
		bw_form( "options.php" );
		$options = get_option('bw_summary_options');     
		stag( 'table class="form-table"' );
		bw_flush();
		settings_fields('bw_summary_options_options'); 
		bw_textfield_arr( "bw_summary_options", __( "Daily trace summary file", "oik-bwtrace" ), $options, 'summary_file', 60 );
		bw_checkbox_arr( "bw_summary_options", __( "Log transactions to daily trace summary file", "oik-bwtrace" ), $options, 'trace_status_report' );
		etag( "table" ); 			
		BW_::p( isubmit( "ok", __( "Save changes", "oik-bwtrace" ), null, "button-primary" ) );
		etag( "form" );
		//bw_flush();
	}
	
	/**
	 * We have to decide whether or not we're going to produce a record in the Daily Status report
	 * 
	 * If so, then we have to attach our method to shutdown.
	 * We do this after action trace so we can piggy back on the global $vt stuff
	 *
	 */
	public function initialise() {
		$reporting = $this->is_reporting();
		if ( $reporting ) {
			add_action( "shutdown", array( $this, "record_vt" ), 11 );	
		}
	}
	
	private function is_reporting() {
		$this->get_options();
		$reporting = bw_array_get( $this->bw_summary_options, 'trace_status_report', '0' );
		$reporting = $reporting === "on";
		return $reporting;
	}
	
	/**
	 * Creation of the Daily Trace Summary report is now separated from the trace action. 
	 * The option field name may be changed from `trace_status_report` to `trace_summary`.
	 *
	 */
	function get_options() {
		$this->bw_summary_options = get_option( 'bw_summary_options' );
		bw_trace2( $this, "this", false, BW_TRACE_DEBUG );
	}
	
	/**
	 * Gets an option value
	 *
	 * Doesn't support defaults
	 * 
	 * @param string $name
	 * @return string | null
	 */
	private function get_option_value( $name ) {
		$value = bw_array_get( $this->bw_summary_options, $name, null );
		return $value;
	}
	
	/**
	 * 
	 */
	function get_summary_file_prefix() {
		$file_prefix = $this->get_option_value( 'summary_file' );
		$file_prefix = trim( $file_prefix );
		if ( empty( $file_prefix ) ) {
			$file_prefix = "bwtrace.vt";
			
		}
		
		return $file_prefix;
		
	}
	
	
	/**
	 * Writes a record to the Daily Trace Summary file
	 */
	function record_vt() {
		$this->populate_vt_values();
    $this->bw_record_vt();
	}
	
/**
 * Record the summary values for this transaction
 *
 * Note: The columns are dynamically created from the fields recorded by bw_trace_status_report()
 *
 * Index | Field
 * ----- | ----------- 
 * 0 | request
 * 1 | AJAX action
 * 2 | elapsed ( final figure )
 * 3 | PHP version
 * 4 | PHP functions
 * 5 | User functions
 * 6 | Classes
 * 7 | Plugins
 * 8 | Files
 * 9 | Registered Widgets
 * 10 | Post types
 * 11 | Taxonomies
 * 12 | Queries
 * 13 | Query time
 * 14 | Trace file
 * 15 | Trace records
 * 16 | Trace errors
 * 17 | Remote address ( IP address )
 * 18 | Elapsed
 * 19 | Date - ISO 8601 date
 * 20 | HTTP user agent
 * 21 | REQUEST_METHOD
 */
function bw_record_vt( $vnoisy=false ) {
  global $vt_values, $vt_text;
  $line = bw_trace_determine_request();
  $line .= ",";
  $line .= bw_trace_timer_stop();
  foreach ( $vt_values as $key=> $value ) {
    $line .=  ",";
    if ( $vnoisy ) {
      $line .=   $vt_text[$key] . "=";
    }   
    $line .= $value ;
  }
  $line .= ",";
  $line .= date( 'c' );
	$line .= ",";
	$line .= bw_trace_http_user_agent();
	$line .= ",";
	$line .= bw_array_get( $_SERVER, "REQUEST_METHOD", null );
	
  $line .= PHP_EOL;
	$file = $this->bw_trace_vt_file();
	
	// How do we know that bw_write is loaded? 
  bw_write( $file, $line );
}


/**
 * Returns the 'vt' file name
 * 
 * Format: bwtrace.vt.ccyymmdd
 * 
 * For WPMS this includes the blog ID, but not the site ID
 *
 * Format: bwtrace.vt.mmdd.blog_ID
 *
 * @return string Fully qualified file name
 */
function bw_trace_vt_file() {
	global $bw_trace;    
	$fq_trace_files_directory = $bw_trace->trace_files_directory->get_fq_trace_files_directory();

	$bwtracevt = $this->get_summary_file_prefix();
	
  $file = $fq_trace_files_directory . $bwtracevt . "." .  date( "Ymd" );
	global $blog_id; 
	bw_trace2( $blog_id, "blog_id !$blog_id!", false, BW_TRACE_VERBOSE );
	if ( $blog_id != 1 ) {
		$file .= ".$blog_id";
	} 
	return( $file );
}


	
	/**
	 * Populate's the values if not already set.
	 *
	 * Here we fiddle the setting of $_REQUEST['short'] to prevent HTML comments from being echo'd
	 */
	function populate_vt_values() {
		global $vt_values, $vt_text;
		if ( !isset( $vt_values[0] ) ) {
			 $_REQUEST[ "short" ] = 1;
			$this->call_bw_trace_status_report();
		}
	}
	
	/**
	 * Calls the bw_trace_status_report
	 */
	function call_bw_trace_status_report() {
		if ( !function_exists( "bw_trace_status_report" ) ) {
			oik_require( "includes/oik-actions.php", "oik-bwtrace" );
		}
		if ( function_exists( "bw_trace_status_report" ) ) {
			bw_trace_status_report();
		}
	}
	
	
	
	 
	
} 
