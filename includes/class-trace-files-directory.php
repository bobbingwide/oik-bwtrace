<?php // (C) Copyright Bobbing Wide 2018

/**
 * @package oik-bwtrace
 * 
 * Trace files directory
 
 *
 * - We need to ensure that trace files are stored in a protected directory.
 * - If it's outside of DOCUMENT_ROOT then it's probably OK.
 * - If this path is below ABSPATH then the folder needs to have an .htaccess file that prevents unauthorized users from accessing it. 
 * - Catering for sub-directory installs in WordPress Multisite could be tricky.
 *
 * The .htaccess file would contain something like this. 
 *
 * ```
 * order deny,allow
 * deny from all
 * allow from 192.168.50.1
 * ```
 * 
 * Note: If running locally this logic doesn't matter so much. 
 * We can't easily check we're running locally so we'll defer to the production strength logic.
 * 
 * 
 * We should also ensure that the path is not a WordPress folder.
 *
 *
 * $directory | Processing
 * ---------- | ------------
 * null       | Don't support trace 
 * 0          | Don't support trace. Note: empty() returns true for "0"
 * starts /   | Treat as fully qualified
 * starts C:/ | Treat as fully qualified - Windows only	- where C is a drive letter
 * directory  | Prepend ABSPATH and check directory exists
 * starts .   | ?
 * starts ../ | ? 
 * 
 * 
 * 
 */

class trace_files_directory {

	public $trace_files_directory; 
	public $options;
	public $valid = false;
	
	/** 
	 * Fully qualified trace files directory, with trailing slash
	 */
	public $fq_trace_files_directory;
	
	
	function __construct() {
		$this->valid = false;
		$this->default_options();
		$this->set_trace_files_directory();
		$this->set_fq_trace_files_directory();
	
	}
	
	function default_options() {
		$this->options = array();
		$this->options[ 'trace_directory' ] = null;
	}
	
	function set_trace_files_directory( $directory=null ) {
		$this->trace_files_directory = null;
	}
		
	
	
	function set_options( $options ) {
		$this->trace_files_directory = bw_array_get( $options, 'trace_directory' );
	
	}
	
	function validate_trace_files_directory() {
		$this->valid = false;
	}
	
	function is_valid() {
		return $this->valid;
	}
	
	function set_fq_trace_files_directory( $directory=null ) {
		$this->fq_trace_files_directory = $directory;
	}
	
	/**
	 * Returns the fully qualified trace files directory
	 * 
	 */
	function get_fq_trace_files_directory() {
		return $this->fq_trace_files_directory;
	}
	
	/**
	 * Gets a sanitized version of ABSPATH
	 *
	 * If the constant is not set it determines it based on this file's location.
	 *
	 * @return string fully qualified path with trailing slash
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

}
	
	
