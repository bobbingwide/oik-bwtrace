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
	
	/**
	 * Constructor for trace logs
	 */
	public function __construct() {
		$this->get_options();
	}
	
	/**
	 * Returns the daily trace summary file prefix
	 */
	public function get_summary_file_prefix() {
		global $bw_trace_summary;
		$summary_file = $bw_trace_summary->get_summary_file_prefix();
		return $summary_file;
	}
	
	public function get_fq_trace_files_directory() {
		global $bw_trace;
		if ( $bw_trace->trace_files_directory ) {
			$fq_trace_files_directory = $bw_trace->trace_files_directory->get_fq_trace_files_directory();
		} else {
			$fq_trace_files_directory = null;
		}
		return $fq_trace_files_directory;
	}
	
	public function get_retention_period() {
		global $bw_trace;
		if ( $bw_trace->trace_files_directory ) {
			$retain = $bw_trace->trace_files_directory->get_retention_period();
		} else {
			$retain = null;
		}
		return $retain;
	}
		
	
	/**
	 * Displays the trace files form(s)
	 */
	public function display() {
		$this->display_options();
		$valid = $this->display_trace_files_directory_notice();
		if ( $valid ) { 
			$this->perform_purge();
			$this->display_summary();
			//$this->purge();
			$this->display_purge_form();
		}	
	}
	
	/**
	 * Display notices about the Trace files directory
	 *
	 */
	
	public function display_trace_files_directory_notice() {
		$valid = false;
		$fq_trace_files_directory = bw_array_get( $_REQUEST, 'trace_directory', null );
		if ( null === $fq_trace_files_directory ) {
			$fq_trace_files_directory = $this->get_fq_trace_files_directory(); 
		}
		if ( null === $fq_trace_files_directory ) {
			e( "Please specify a Trace files directory." );
			e( '&nbsp;' );
			e( "Preferably use a directory that's not accessible from the browser." );
			e( '&nbsp;' );
			$avoid_folders = array( $_SERVER['DOCUMENT_ROOT'] );
			if ( trailingslashit( $_SERVER['DOCUMENT_ROOT'] ) !== ABSPATH ) {
				$avoid_folders[] = ABSPATH;
			}
			e( "Avoid using these folders or subdirectories of them:" ); 
			e( '&nbsp;' );
			e( str_replace( "\\", "/", implode( ", ", $avoid_folders ) ) );
		} else {
			global $bw_trace;
			$valid = $bw_trace->trace_files_directory->validate_trace_files_directory( $fq_trace_files_directory );
			if ( !$valid ) {
				e( $bw_trace->trace_files_directory->get_message() );
			} 
			
		}
		return $valid;
	}
	
	/**
	 * Displays the trace files options fields
	 */
	public function display_options() {
		bw_form( "options.php" );
		$options = get_option('bw_trace_files_options');     
		stag( 'table class="form-table"' );
		bw_flush();
		settings_fields('bw_trace_files_options'); 
		
		
		BW_::bw_textfield_arr( "bw_trace_files_options", __( "Trace files directory", "oik-bwtrace" ), $options, 'trace_directory', 60 );
		BW_::bw_textfield_arr( "bw_trace_files_options", __( "Retention period ( days )", "oik-bwtrace" ), $options, 'retain', 4 );
	
		etag( "table" ); 			
		BW_::p( isubmit( "ok", __( "Save changes", 'oik-bwtrace' ), null, "button-primary" ) );
		etag( "form" );
		bw_flush();
	}
	
	/**
	 * Purge old trace files if requested
	 */
	public function perform_purge() {
		$purge = bw_array_get( $_REQUEST, "_oik_trace_purge_submit", null );
		if ( $purge ) {
			$purge = bw_verify_nonce( "oik_trace_files_purge", "oik_trace_files_purge" );
			if ( $purge ) {
				p( "Purging old trace files" );
				$this->purge();
			} else {
				p( "Norty" );
				//gob();
			}
		} else {
			// No request to purge trace files!
		}
	}
	
	/**
	 * Displays the Purge trace files button
	 */
	public function display_purge_form() {
		bw_form();
		e( wp_nonce_field( "oik_trace_files_purge", "oik_trace_files_purge", false, false ) );
		e( isubmit( "_oik_trace_purge_submit", "Purge trace files" ) ); 
		etag( "form" );
	}
	

	/**
	 * Displays trace log summary
	 * 
	 * If the file name is not defined for the type then don't include it in the table.
	 *
	 * 
	 * Type | Name | Files | Size | From | To
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
		bw_tablerow( bw_as_array( "Type,Name,Files,Size,From,To" ), "tr", "th" );
		etag( "thead" );
		stag( "tbody" );
		
		
		$this->summarise( "daily", $this->get_summary_file_prefix() );
		$this->summarise( "browser", $this->get_option_value( "file" ) );
		$this->summarise( "ajax", $this->get_option_value( "file_ajax" ) );
		$this->summarise( "rest", $this->get_option_value( "file_rest" ) );
		$this->summarise( "cli", $this->get_option_value( "file_cli" ) );
		etag( "tbody" );
		etag( "table" );
		
		//$this->purge();
 
	}
	
	/**
	 * Purges trace files
	 */
	function purge() {
		$this->purge_files( "daily", $this->get_summary_file_prefix() );
		$this->purge_files( "browser", $this->get_option_value( "file" ) );
		$this->purge_files( "ajax", $this->get_option_value( "file_ajax" ) );
		$this->purge_files( "rest", $this->get_option_value( "file_rest" ) );
		$this->purge_files( "cli", $this->get_option_value( "file_cli" ) );
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
	 * Summarises log files for a tracing type
	 *
	 * @param $type the tracing type
	 * @param $path the file prefix for this tracing type
	 */
	private function summarise( $type, $path ) {
		$record = array();
		$record[] = $type;
		$file_mask = $this->get_fq_trace_files_directory();
		$record[] = $path;
		if ( $file_mask && $path ) {
			$file_mask = $file_mask . $path;
			$files = $this->query_files( $file_mask);
			$record[] = count( $files );
			//$record[] = implode( " ", $files );
			$record[] = number_format( $this->size( $files ) );
			$this->date_range( $files );
			$record[] = $this->min_date;
			$record[] = $this->max_date;
		}
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
		$files = glob( $file_mask . "*", GLOB_NOSORT );
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
	
	/**
	 * Purges files with the given file mask $path
	 *
	 * If older than retention period
	 
	 * @param string $type 
	 * @param string $path
	 *
	 */
	public function purge_files( $type, $path ) {
		if ( $path ) {
			$purge_time = $this->query_purge_time();
			$file_mask = $this->get_fq_trace_files_directory() . $path;
			$files = $this->query_files( $file_mask);
			$count = 0;
			foreach ( $files as $file ) {
				$count++;
				//echo "$count $file ";
				$filemtime = filemtime( $file );
				if ( $filemtime < $purge_time ) {
					//echo "will be deleted";
					unlink( $file );
				} else { 
					//echo "stays";
				}
				//echo PHP_EOL;
			}
		} 
	}
	
	/**
	 * Query the purge time
	 * 
	 * The purge time is based on the retention period.
	 *
	 * 
	 * retain     | Action
	 * -------    | ----------
	 * null/blank | Don't purge	@TODO if really necessary
	 * 0          | Purge all files
	 * n          | Purge files older than time() - ( 86400 * n )
	 * 
	 */ 
	function query_purge_time() {
		$retain = $this->get_retention_period();
		if ( null !== $retain ) {
			$time = time();
			//echo "Time now: $time ";
			$time -= ( 86400 * $retain );
			//echo "Purging files older than: $time";
		} else {
			$time = 0;
		}	
		return $time;
	}

}
