<?php // (C) Copyright Bobbing Wide 2018

class trace_file_selector {

	public $limit;
	public $file_path;
	public $file_name;
	public $file_extension;
	public $file_generation;
	public $trace_file_name;
	public $trace_files;
	
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
		$this->set_limit();
		$this->set_file_path();
		$this->set_file_name();
		$this->set_file_extension();
	
		//$generation = $this->query_next_generation();

		//$this->set_generation( $generation );
		//$this->set_trace_file_name();
	}
	
	/**
	 * Sets the limit
	 * 
	 * Limit      | Generation logic | Generation
	 * ------     | ---------------- | -----------
	 * blank/null | Not used.        | null
	 * 0          | Unlimited        | .timestamp
	 * >0         | Cycling          | .generation
	 */
	public function set_limit( $limit=100 ) {
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
	 * $file_path fully qualified location including trailing slash
	 */
	public function set_file_path( $file_path=null ) {
		if ( !$file_path ) {
			$file_path = $this->get_abspath();
		}
		$this->file_path = $file_path;
	}
	
	/**
	 * Gets a sanitized version of ABSPATH
	 *
	 * If the constant is not set it determines it based on this file's location.
	 *
	 */
	public function get_abspath() {
		if ( !defined('ABSPATH') ) {
			$abspath = dirname( dirname( dirname ( dirname( dirname( __FILE__ ))))) . '/';
			$abspath = str_replace( "\\", "/", $abspath );
			if ( ':' === substr( $abspath, 1, 1 ) ) {
				$abspath = ucfirst( $abspath );
			}
		} else { 
			$abspath = ABSPATH;
		}
		return $abspath;
	}
	
	public function set_trace_options( $bw_trace_options ) {
		$this->trace_options = $bw_trace_options;
		$this->update_from_options();
		$this->set_limit( bw_array_get( $this->trace_options, 'limit', $this->limit ) );
	}
	
	/**
	 * Use ajax/rest/cli file name and extension if defined
	 * 
	 */
	public function update_from_options() {
		$file = null;
		$request_type = $this->query_request_type();
		if ( $request_type ) {
      $file = bw_array_get( $this->trace_options, 'file_' . $request_type, null ); 
		}
		if ( !$file ) {
			$file = bw_array_get( $this->trace_options, 'file', "bwtrace.loh" );
		}
		$file = trim( $file );
		$file_name = pathinfo( $file, PATHINFO_FILENAME );
		$file_extension = pathinfo( $file, PATHINFO_EXTENSION );
		$this->set_file_name( $file_name );
		$this->set_file_extension( $file_extension ? $file_extension : $request_type );
	}
	
	/**
	 * Determines the request type from the available information
	 */
	public function query_request_type() {
		$type = null;
		if ( php_sapi_name() == "cli" ) {
			$type = "cli";
		}	elseif ( defined( 'DOING_AJAX' ) && DOING_AJAX  ) {
			$type = "ajax";
		} else {
			$type = $this->maybe_rest();
		}
		return $type;
	}
	
	/** 
	 * Determines if this request could be a REST request
	 * 
	 * @TODO Cater for custom REST routes
	 * @return string|null 'rest' if we think or know it's a REST request
	 */
	function maybe_rest() {
		$type = null;
		$request_uri = bw_array_get( $_SERVER, 'REQUEST_URI' );
		//$request_uri = $this->maybe_subdir_install( $request_uri );
		$pos = strpos( $request_uri, "/wp-json/wp/v2/" );
		if ( $pos === false ) {
			$pos = strpos( $request_uri, "/index.php?rest_route=" );
		}
		if ( $pos !== false ) {
			$type = "rest";
		}
	
		if ( defined('REST_REQUEST') && REST_REQUEST ) {
			$type = "rest";
		}
		return $type;
	
	}	
	
	/**
	 * Builds the trace file mask
	 *
	 * Format: path/filename.ext
	 */			
	public function get_trace_file_mask() {
		$file_mask = $this->file_path;
		$file_mask .= $this->file_name;
		$file_mask .= ".";
		$file_mask .= $this->file_extension;
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
		$generation = $this->get_generation();
		if ( $generation ) {
			$file_name .= ".";
			$file_name .= $generation;
		}	
		$this->trace_file_name = $file_name;
	}
	
	public function get_trace_file_name() {
		if ( !$this->trace_file_name ) {
			$this->set_generation_for_limit();
			$this->set_trace_file_name();
		}
		return $this->trace_file_name;
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
				$this->set_generation( $_SERVER['REQUEST_TIME' ] );
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
	
		
	




}
