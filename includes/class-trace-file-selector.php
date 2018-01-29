<?php // (C) Copyright Bobbing Wide 2018

class trace_file_selector {

	public $limit;
	public $file_path;
	public $file_name;
	public $file_extension;
	public $file_generation;
	public $trace_file_name;
	public $trace_files;
	
	
	public function __construct() {
		$this->set_limit();
		$this->set_file_path();
		$this->set_file_name();
		$this->set_file_extension();
	
		//$generation = $this->query_next_generation();

		//$this->set_generation( $generation );
		//$this->set_trace_file_name();
	}
	
	public function set_limit( $limit=100 ) {
		$this->limit = $limit;
	}
	
	public function set_file_name( $file_name="bwtrace" ) {
		$this->file_name = $file_name;
	}
	
	public function set_file_extension( $file_extension="loh" ) {
		$this->file_extension = $file_extension;
	}
	
	public function set_generation( $generation=1 ) {
		$this->file_generation = $generation;	
	}
	
	public function get_generation() {
		return $this->file_generation;
	}
	
	
	/**
	 * Sets the trace file path
	 *
	 * $file_path fully qualified location including trailing slash
	 */
	public function set_file_path( $file_path=ABSPATH ) {
		$this->file_path = $file_path;
	}
	
	public function get_trace_file_mask() {
		$file_mask = $this->file_path;
		$file_mask .= $this->file_name;
		$file_mask .= ".";
		$file_mask .= $this->file_extension;
		return $file_mask;
	
	}
	
	/**
	 * Query trace files given the file mask
	 * 
	 * @param string $file_mask
	 * @return array of fully qualified file names
	 */
	public function query_files( $file_mask ) {  
		$this->trace_files = glob( $file_mask . ".*" );
		return $this->trace_files;
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
	 *  
		
	*/
	public function query_oldest_file( $files, $file_mask) {
		array_multisort( array_map( 'filemtime', $files), SORT_ASC, $files);
		//print_r( $files );
		$generation = str_replace( $file_mask . "." , "", $files[0] );
		//echo "gener:" . $generation . PHP_EOL;
		return $generation;
	}
	
	public function set_trace_file_name() {
		$file_name = $this->get_trace_file_mask();
		$file_name .= ".";
		$file_name .= $this->get_generation();
		$this->trace_file_name = $file_name;
	}
	
	public function get_trace_file_name() {
		if ( !$this->trace_file_name ) {
			$generation = $this->query_next_generation();
			$this->set_generation( $generation );
			$this->set_trace_file_name();
		}
		return $this->trace_file_name;
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
