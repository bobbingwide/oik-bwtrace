<?php // (C) Copyright Bobbing Wide 2018

/**
 * Trace record writer
 *
 * Implements the logic to write trace records
 * - Relies on trace_file_selector to tell it where to write the output
 * - What happens in the event of a failure
 * - I suppose the trace controller is responsible for that! 
 * - Separate from trace summary
 * 
 *
 */


class BW_trace_record {

	/** 
	 * Gives us access to some methods and fields
	 */
	public $trace_controller = null;

	public $trace_file_selector = null;

	/**
	 * Implements all the globals as public variables
	 */
	public $trace_options = null;
  public $include_trace_count = true;
  public $include_trace_date = true; 
  public $trace_anonymous = false;	  
  public $trace_memory = false; 
  public $trace_post_id = false;
  public $trace_num_queries = false;
	public $trace_current_filter = true;
	public $trace_file_count = true;
	
	public $trace_count;
	//public $trace_functions;
	
	
	/**
	 * 
	 */
	function __construct( $trace_controller ) {
		$this->trace_controller = $trace_controller;
		$this->trace_count = 0;
		$this->init_trace_functions(); 
		
	}
	
	public function set_trace_options( $bw_trace_options ) {
		$this->trace_options = $bw_trace_options;
		$this->update_from_options();
	}
	
	/**
	 * Set fields from the option values
	 *
	 * These used to be global fields prefixed $bw_
	 *
	 */
	public function update_from_options() {
		$this->include_trace_count = $this->trace_controller->torf( 'count' );
		$this->include_trace_date = $this->trace_controller->torf( 'date' );
		$this->trace_anonymous = !$this->trace_controller->torf( 'qualified' );
		$this->trace_memory = $this->trace_controller->torf( "memory" );
		$this->trace_post_id = $this->trace_controller->torf( "post_id" );
		$this->trace_num_queries = $this->trace_controller->torf( "num_queries" );
		$this->trace_current_filter = $this->trace_controller->torf( "filters" );
		$this->trace_file_count = $this->trace_controller->torf( "files" );
	
	
	}
	
	function trace() {
	
	
	}
	
	public function init_trace_functions() {
		global $bw_trace_functions;
		$bw_trace_functions = array();
	}
	
	
	/**
	 * Implements lazy tracing
	 */
	public function lazy_trace( $text, $function=__FUNCTION__, $lineno=__LINE__, $file=__FILE__, $text_label=null, $level=BW_TRACE_ALWAYS ) {
		$this->trace_count++;
    /*
		 * Note: $bw_trace_functions does not hold the number of times that the function is called. 
     * It's the number of times that bw_trace() is called for the $function
		 */
		global $bw_trace_functions;
    bw_array_inc( $bw_trace_functions, $function );
    $line = $this->flf( $function, $lineno, $file, $this->trace_count, $text, $text_label, $level );  
    $this->trace_log( $line );  
	}
	
	
	/**
	 * Format the trace record
	 *
	 * Note: flf is an abbreviation for function, line, file 
	 * which become file(line) function in the trace record
	 *
	 * This is the minimum output required over and above the value of the field being traced. 
	 * 
	 * If you don't have this information you may as well not have the trace output.
	 * 
	 * 
	 * The format of the trace record is something like this:
	 *
	 * | Part                | Example
	 * |-------------------- | --------------
	 * | Filename(line)      | /wp-content/plugins/oik-bwtrace/oik-bwtrace.php(143:0)
	 * | function(count)     | bw_trace_plugin_startup(1) 
	 * | trace record count  | 7
	 * | timestamp           | 2015-06-04T13:53:35+00:00
	 * | elapsed						 | 0.011437
	 * | interval            | 0.001158 
	 * | context             | cf=admin_menu
	 * | number of queries   | 1
	 * | post ID             | 3667
	 * | memory/peak usage   | 14310144/14383168
	 * | files loaded        | F=80 
	 * | field               | tracelog
	 * | value               | C:\apache\htdocs\wordpress/bwtraces.loh
	 * | bwecho'd content    | see bw_trace_bwechos()
	 *
	 * Most parts are controlled by trace option settings.
	 *
	 * Each of the invoked functions should either return the value followed by a space OR a null string
	 *
	 * @param string $function the invoking function ( e.g. __FUNCTION__ )
	 * @param string $lineno the invoking file line number ( e.g. __LINE__ )
	 * @param string $file the invoking file normally ( e.g. __FILE__ )
	 * @param integer $count the trace record count
	 * @param string $text representing the information to trace
	 * @param string $text_label identifying text label  
	 */
	function flf( $function, $lineno, $file, $count, $text, $text_label = NULL, $level=BW_TRACE_ALWAYS ) {
		$ref = bw_trace_file_part( $file );
		$ref .= '('.$lineno.':'. $level .') ';
		$ref .= bw_trace_function( $function );
		$ref .= bw_trace_count( $count );
		$ref .= bw_trace_date( DATE_W3C );
		$ref .= bw_trace_elapsed();
		$ref .= bw_trace_context();
		$ref .= bw_get_num_queries();
		$ref .= bw_trace_post_id();
		$ref .= bw_get_memory_usage();
		$ref .= bw_trace_file_count();
		$ref .= $text_label;
		$ref .= " ";
		$ref .= bw_trace_print_r( $text );
		$ref .= bw_trace_bwechos();
		$ref .= "\n";
		return $ref ;
	}	
 

	/**
	 * Log a record to a trace file
	 *
	 * @param string $line - this can be a very long string
	 *
	 */
	function trace_log( $line ) {
		//$file = bw_trace_file2();
		$file = $this->trace_controller->get_trace_file_name();
		
		if ( $file ) {
			bw_write( $file, $line ); 
		} else {
			_doing_wrong_thing();
		}
	}


	



}
