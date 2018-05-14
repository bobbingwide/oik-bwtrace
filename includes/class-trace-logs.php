<?php 

/**
 * @copyright (C) Copyright Bobbing Wide 2018
 * @package oik-bwtrace
 * 
 * Trace logs: Summary and purging
 * 
 */
class trace_logs {
	
	private $options;
	private $trace_files;
	private $min_date;
	private $max_date;
	
	public function __construct() {
		$this->get_options();
	}

	/**
	 * Displays trace log summary
	 *
	 * 
	 * Type | Path | Files | Size | From | To
	 * -- | -- | -- | -- | -- | --
	 * daily | bwtrace.vt | 161 | 3,956,420 | 2017-05-17 | 2018-04-27
	 * browser | bwtraces.loh | 1095 | 1,908,844,717 | 2018-03-12 | 2018-04-27
	 * ajax | bwtrace.ajax | 969 | 228,329,472 | 2018-03-13 | 2018-04-16
	 * rest | bwtraces.rest | 1141 | 1,544,215,267 | 2018-03-27 | 2018-04-16
	 * cli | bwtraces.cli | 112 | 100,209,273 | 2018-03-11 | 2018-04-03  
	 */
	function display_summary() {
    stag( "table", "widefat" );
    stag( "thead" ); 
		bw_tablerow( bw_as_array( "Type,Path,Files,Size,From,To" ), "tr", "th" );
		etag( "thead" );
		stag( "tbody" );
		$this->summarise( "daily", "bwtrace.vt" );
		$this->summarise( "browser", $this->get_option_value( "file" ) );
		$this->summarise( "ajax", $this->get_option_value( "file_ajax" ) );
		$this->summarise( "rest", $this->get_option_value( "file_rest" ) );
		$this->summarise( "cli", $this->get_option_value( "file_cli" ) );
		etag( "tbody" );
		etag( "table" );
	}
	
	/**
	 * Load the trace options
	 */
	private function get_options() {
		$this->options = get_option( "bw_trace_options" );
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
		$value = bw_array_get( $this->options, $name, null );
		return $value;
	}
	
	/**
	 * Summarise log files for a tracing type
	 *
	 * 
	 */
	private function summarise( $type, $path ) {
		$record = array();
		$record[] = $type;
		$record[] = $path;
		
		$file_mask = ABSPATH . $path;
		
		$files = $this->query_files( $file_mask);
		$record[] = count( $files );
		$record[] = number_format( $this->size( $files ) );
		$this->date_range( $files );
		$record[] = $this->min_date;
		$record[] = $this->max_date;
		bw_tablerow( $record );
	}
	
	/**
	 * Queries trace files given the file mask
	 *
	 * We need them sorted in the natural sort sequence
	 * where .2 is greater than .11
	 * 
	 * @param string $file_mask
	 * @return array of fully qualified file names
	 */
	public function query_files( $file_mask ) { 
		$files = glob( $file_mask . ".*", GLOB_NOSORT );
		//$files = $this->trim_to_limit( $files, $file_mask, $this->limit );
 		natsort( $files );
		$this->trace_files = $files; 		
		return $this->trace_files;
	}
	
	/**
	 * Determine the total size of the files
	 * 
	 * @param array $files array of fully qualified file names
	 * @return integer total file size, in bytes
	 */
	public function size( $files ) {
		$total = 0;
		foreach ( $files as $file ) {
			$filesize = filesize( $file );
			$total += $filesize;
		}
		return $total;
	}
	
	/**
	 * Determines the date range spanned by the files
	 *
	 * Can be used to indicate the level of pruning required
	 * 
	 * Sets $this->min_date and $this->max_date in format Y-m-d
	 *
	 * @param array $file array of fully qualified file names
	 * 
	 */
	public function date_range( $files ) {
		$this->min_date = null;
		$this->max_date = null;
		if ( count( $files ) ) {
			$this->min_date = date( "Y-m-d" ); 
		}
		foreach ( $files as $file ) {
			$filemtime = filemtime( $file ); 	 
			$filedate = date( "Y-m-d", $filemtime ); 
			if ( $filedate < $this->min_date ) {
				$this->min_date = $filedate;
			}
			if ( $filedate > $this->max_date ) {
				$this->max_date = $filedate;
			}

		}
	}
	
	/**
	 * Implement a delete method that can be used to tidy trace log files
	 * 
	 * This would be called by the `wp trace delete <type> --retain=1` subcommand
	 * 
	 */
	
	public function delete( $args, $assoc_args ) {
	
	
	}	
	
	



}
