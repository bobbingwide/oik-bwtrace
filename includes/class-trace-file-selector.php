<?php // (C) Copyright Bobbing Wide 2018

/**
 * @package oik-bwtrace
 * 
 * Trace file selector supporting trace file generations and sets
 * 
 */

class trace_file_selector {

	/**
	 * File generation limit - see set_limit
	 */
	public $limit;     
	
	/**
	 * Trace files directory object.
	 */
	public $trace_files_directory; 	
	
	public $file_path;
	public $file_name;
	public $file_extension;
	public $file_generation;
	public $trace_file_name;
	public $trace_files;
	public $request_type; // null | 'cli' | 'ajax' | 'rest' 
	
	/**
	 * oik-bwtrace was developed as procedural code. 
	 * If we now intend to implement OO code for some things
	 * how do we go about introducing it? 
	 * Should bw_trace_options be a separate class?
	 * For the time being we'll just try working with a local copy
	 * so we can set the $file_name and $file_extension for AJAX and normal.
	 * and extend this for CLI and REST
	 *
	 */
	public $trace_options;
	
	public function __construct() {
		$this->set_trace_files_directory();
		$this->set_limit();
		$this->set_file_path();
		$this->set_file_name();
		$this->set_file_extension();
		$this->set_request_type();
	
		//$generation = $this->query_next_generation();

		//$this->set_generation( $generation );
		//$this->set_trace_file_name();
	}
	
	/**
	 * Sets the trace files directory object
	 * 
	 * @param object $trace_files_directory
	 */
	public function set_trace_files_directory( $trace_files_directory=null ) {
		$this->trace_files_directory = $trace_files_directory;
	}
	
	
	/**
	 * Sets the limit
	 * 
	 * Limit      | Generation logic | Generation
	 * ------     | ---------------- | -----------
	 * blank/null | Not used.        | null
	 * 0          | Unlimited        | .timestamp
	 * >0         | Cycling          | .generation
	 * 
	 * @param integer|null $limit
	 */
	public function set_limit( $limit=null ) {
		$limit = trim( $limit );
		$this->limit = $limit;
	}
	
	/**
	 * Sets the trace file name
	 * 
	 * @param string $file_name file name part, should not contain path
	 */
	public function set_file_name( $file_name="bwtrace" ) {
		$this->file_name = $file_name;
	}
	
	/**
	 * Sets the trace file extension
	 * 
	 * @param string $file_extension Expected to reflect the request type
	 */
	public function set_file_extension( $file_extension="loh" ) {
		$this->file_extension = $file_extension;
	}
	
	/**
	 * Sets the file generation
	 * 
	 * @param integer|null $generation
	 */
	public function set_generation( $generation=null ) {
		$this->file_generation = $generation;	
	}
	
	/** 
	 * Returns the current setting of the generation
	 *
	 * @return integer|null  
	 */
	public function get_generation() {
		return $this->file_generation;
	}
	
	/**
	 * Sets the trace file path
	 *
	 * @param string $file_path fully qualified location including trailing slash
	 */
	public function set_file_path( $file_path=null ) {
		//if ( !$file_path ) {
		//	$file_path = $this->get_abspath();
		//}
		$this->file_path = trailingslashit( $file_path );
	}
	
	/**
	 * Sets trace options
	 */
	public function set_trace_options( $bw_trace_options ) {
		$this->trace_options = $bw_trace_options;
		$this->update_from_options();
		
		$request_type = $this->get_request_type();
		if ( $request_type ) {
			$limit = 'limit_' . $request_type;
		} else {
			$limit = 'limit';
		}
		$this->set_limit( bw_array_get( $this->trace_options, $limit, $this->limit ) );
	}
	
	/**
	 * Use ajax/rest/cli file name and extension if defined
	 * 
	 */
	public function update_from_options() {
		$file = null;
		$request_type = $this->get_request_type();
		if ( $request_type ) {
      $file = bw_array_get( $this->trace_options, 'file_' . $request_type, null ); 
		}
		if ( !$file ) {
			$file = bw_array_get( $this->trace_options, 'file', "bwtrace.loh" );
		}
		$file = trim( $file );
		
		$file_path = pathinfo( $file, PATHINFO_DIRNAME );
		$file_name = pathinfo( $file, PATHINFO_FILENAME );
		$file_extension = pathinfo( $file, PATHINFO_EXTENSION );
		if ( $file_path !== '.' ) {
			$this->set_file_path( $file_path );
		}
		$this->set_file_name( $file_name );
		$this->set_file_extension( $file_extension ? $file_extension : $request_type );
	}
	
	/** 
	 * 
	 */
	public function set_request_type( $request_type=null ) {
		$this->request_type = $request_type;
	}
	
	/**
	 * Determines the request type from the available information
	 */
	public function get_request_type() {
		return $this->request_type;
	}
	
	
	/**
	 * Builds the trace file mask
	 *
	 * Format: path/filename.ext
	 */			
	public function get_trace_file_mask() {
		$file_mask = $this->trace_files_directory->get_fq_trace_files_directory();
		if ( $file_mask ) {
			// Ignore the path. @TODO - either ensure it's not set or implement support.
			//$file_mask .= $this->file_path;
			$file_mask .= $this->file_name;
			$file_mask .= ".";
			$file_mask .= $this->file_extension;
		}	
		return $file_mask;
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
		$files = $this->trim_to_limit( $files, $file_mask, $this->limit );
 		natsort( $files );
		$this->trace_files = $files; 		
		return $this->trace_files;
	}
	
	/**
	 * Trims to limit
	 * 
	 * Removes files which are not in the currently defined limit.
	 */
	public function trim_to_limit( $files, $file_mask, $limit ) {
		$limited = array();
		foreach ( $files as $file ) {
			$index = str_replace( $file_mask . "." , "", $file );
			if ( $index <= $limit ) {
				$limited[] = $file;
			}
		}
		return $limited;
	}
	
	/**
	 * Finds the oldest file in the array
	 *
	 * The oldest file is the one to be used next. 
	 * If trace_reset is on then we'll unlink the file first. 
	 * Otherwise we'll append to the file.
	 *
	 * @param array $files files matching the $file_mask
	 * @param string $file_mask - selector for the files
	 * @return integer generation number of the oldest file.
	 */
	public function query_oldest_file( $files, $file_mask) {
		array_multisort( array_map( 'filemtime', $files), SORT_ASC, $files);
		//print_r( $files );
		$generation = str_replace( $file_mask . "." , "", $files[0] );
		//echo "gener:" . $generation . PHP_EOL;
		return $generation;
	}
	
	/**
	 * Sets the full trace file name
	 */
	public function set_trace_file_name() {
		$file_name = $this->get_trace_file_mask();
		if ( $file_name ) {
			$generation = $this->get_generation();
			if ( $generation ) {
				$file_name .= ".";
				$file_name .= $generation;
			}	
		}		
		$this->trace_file_name = $file_name;
	}
	
	/**
	 * Gets the trace file to use
	 */
	public function get_trace_file_name() {
		if ( !$this->trace_file_name ) {
			$this->set_generation_for_limit();
			$this->set_trace_file_name();
			$this->reset_as_required();
		}
		return $this->trace_file_name;
	}
	
	/**
	 * Gets the trace file URL 
	 *
	 * @TODO Support $file parameter
	 * @param string|null $file name to use
	 * @return string trace file URL 
	 */
	public function get_trace_file_url() {
		if ( $this->trace_file_name ) {
			$file_name = str_replace( $this->trace_files_directory->get_abspath(), "", $this->trace_file_name );
			$trace_file_url = get_site_url( null, $file_name );
			
		} else {
			$trace_file_url = null;
		}
		return $trace_file_url;
	}
	
	/** 
	 * Sets the generation for the given limit
	 * 
	 */
	public function set_generation_for_limit() {
		switch ( $this->limit ) {
			case null:
			case '':
				$this->set_generation( null );
				break;
			
			case 0:
				$this->set_generation( $_SERVER['REQUEST_TIME_FLOAT' ] );
				//$this->set_generation( time() );
				break;
			
			default:		
				$generation = $this->query_next_generation();
				$this->set_generation( $generation );
		}
	}
	
	/**
	 * Find the next unused index in the generation
	 * 
	 * @TODO Determine if we can shortcut by comparing the count of $files vs $limit
	 * 
	 * @param array $files - matching the $file_mask
	 * @param string $files - the file mask
	 * @return integer the generation number for the next unused file
	 */
	public function find_next_unused( $files, $file_mask ) {
		$next = 0;
		$unused = 0;
		foreach ( $files as $file ) {
			$index = str_replace( $file_mask . "." , "", $file );
			$next++;
			if ( $index > $next ) {
				$unused = $next;
				break;
			}
		}
		if ( ( 0 === $unused ) && ( $next < $this->limit ) ) {
			$unused = ++$next;
			//echo "Unused: $unused " . PHP_EOL;
		}
		return $unused;
	}
	
	
	/**
	 * Queries the next generation
	 * 
	 * @return integer - the value of the next generation
	 */
	public function query_next_generation() {
		$file_mask = $this->get_trace_file_mask();
		$files = $this->query_files( $file_mask );
		$unused = $this->find_next_unused( $files, $file_mask );
		if ( 0 == $unused ) {
			$generation = $this->query_oldest_file( $files, $file_mask ); 
		}	else {
			$generation = $unused;
		}
		return $generation;
		
	}
	
	/** 
	 * Resets the trace file as required
	 * 
	 * Reset depends on the setting for the request type and the generation
	 * 
	 * trace_reset? | generation | Perform reset
	 * ------------ | ---------- | --------------
	 * false        | null/blank | No
	 * false        | 0          | No - new files are created for every request
	 * false        | >0         | No - we append to the existing file
	 * true         | null/blank | Yes
	 * true         | 0          | No - new files are created for every request
	 * true         | >0         | Yes
	 * 
	 */
	function reset_as_required() {
		if ( $this->limit !== 0 ) {
			$trace_reset = $this->query_reset();
			if ( $trace_reset )  {
				$this->attempt_reset();
			}
		}
	}
	
	/** 
	 * Returns the reset value for the request_type
	 */
	function query_reset() {
		if ( $this->request_type ) {
			$trace_reset = bw_array_get( $this->trace_options, 'reset_' . $this->request_type, null ); 
		} else {
			$trace_reset = bw_array_get( $this->trace_options, 'reset', null );  
		}
		
		if ( !empty( $_REQUEST['_bw_trace_reset'] ) ) {
			$trace_reset = true;
		}
		// @TODO Is this still necessary? 
		if ( isset( $_REQUEST['wc-ajax'] ) ) {
			$trace_reset = false;
		} 
		return $trace_reset;
	}
	
	/**
	 * Attempts to reset the file by unlinking it.
	 * 
	 * - If it doesn't exist that's fine.
	 * - If it's not writable that could be a problem.
	 * - If we can't unlink it we could try another file
	 * - But the problem may continue until it is possible to delete it.
	 * 
 	 * @return bool true if the reset was successful
	 *
	 */
	function attempt_reset() {
		$unlinked = false;
		if ( is_file( $this->trace_file_name ) ) {
			if ( is_writable( $this->trace_file_name ) ) {
				$unlinked = @unlink( $this->trace_file_name );
				if ( $unlinked ) {
					// Good! 
				} else {
					// That's a shame
					$this->ohmy( "unlink failed" );
				}
			} else {
				// We can't unlink the file at the moment - never mind eh?
				// unlinked remains false
				$this->ohmy( "file not writeable" );
			} 
		
		} else {
			$unlinked = true;
		} 
		return $unlinked;
	}
	
	/**
	 * Log a message to the error log
	 *
	 */ 
	
	function ohmy( $message ) {
		$text = __METHOD__;
		$last_error = error_get_last();
    $flat_value = bw_trace_print_r( $last_error ); 
		$logged = error_log( "$text:$flat_value:$message", 0 );
		echo $flat_value;
		//gob();
	}
		


}
