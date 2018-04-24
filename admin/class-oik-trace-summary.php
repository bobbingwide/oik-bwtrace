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
	 * Not needed while we're still using the original code.
	 */
	function get_summary_file_name() {
		$file = bw_trace_vt_file();
		return $file;
	}
	
	
	/**
	 * Writes a record to the Daily Trace Summary file
	 */
	function record_vt() {
		$this->populate_vt_values();
    bw_record_vt();
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
			bw_trace_status_report();
		}
	}
	
	
	
	 
	
} 
