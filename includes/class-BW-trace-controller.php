<?php // (C) Copyright Bobbing Wide 2018, 2019, 2022

/**
 * @package oik-bwtrace
 * 
 * Controls tracing
 * - Uses trace_file_selector to pick the trace file to write to
 * - This should also be used when resetting the trace file
 * - May still use globals while functions are being refactored. 
 */


class BW_trace_controller {

	/**
	 * Instance of the trace files directory class.
	 * If this is not set then we won't support tracing.
	 */
	public $trace_files_directory;

	/**
	 * Instance of the trace file selector. Only needed when we know we're tracing.
	 */
	public $trace_file_selector;
	
	/**
	 * Instance of the trace record writer
   */
	public $BW_trace_record; 
	
	/** 
	 * These fields represent the globals prefixed with a bw_ 
	 *
	 * During initialisation, if the globals already exist then we could consider using them.
	 */
	public $trace_options;  // array Replacement for global $bw_trace_options
	public $action_options; // array Replacement for global $bw_action_options
	public $trace_on;				// bool Replacement for global $bw_trace_on
	public $trace_level;    // integer 
	public $request_type;  	// null | 'cli' | 'ajax' | 'rest' 
	
	public $trace_files_options; // array

    public $trace_json_options; // Instance of the trace_json_options class

    public $is_performance_trace; // true when tracing to memory. Becomes false on `shutdown`.
	/**
	 * Constructor for tracing
	 */
	function __construct() {
	    $this->trace_json_options = null;
		$this->trace_file_selector = null;
		$this->BW_trace_record = null;

		$this->trace_options = null;
		$this->action_options = null;
		$this->trace_files_options = null;
		$this->trace_on = false;
		$this->trace_level = null;
		$this->load_trace_json_options();
		$this->load_trace_options();
		$this->request_type = $this->query_request_type();
        $this->set_trace_level( $this->query_trace_level() );
        $this->load_trace_files_directory();
		if ( $this->is_performance_trace() ) {
		    //echo "Performance trace";
		    // We assume that we got here because
            // we loaded the options from the JSON file
            // which implies that the trace files directory is correctly set.
            add_action( 'shutdown', [ $this, 'shutdown_performance_trace' ], 9 );
            //$this->trace_files_directory = true; // Fiddle for performance

        } else {

        }

		if ( $this->trace_files_directory ) {


			if ( $this->status() && $this->trace_ip() ) {
			    //echo "Loading trace file selector";
				$this->load_trace_file_selector();
				$this->load_trace_record();
				$this->set_savequeries();
			}
			if ( $this->is_trace_hook_counting() ) {
				add_action( 'all', [$this, 'set_trace_hook_count']);
			}
		} else {
			// Invalid trace files directory so tracing cannot be enabled.
			
		}	
	}

    /**
     * Loads trace JSON options class.
     */
    function load_trace_json_options() {
        oik_require( "includes/class-trace-json-options.php", "oik-bwtrace" );
        $trace_json_options = new trace_json_options();
        $this->trace_json_options = $trace_json_options;
    }
	/**
	 * Loads trace options
	 * 
	 * - bw_trace_options contains settings for controlling tracing of each request type
	 * - bw_action_options contains settings for tracing specific actions
	 * - bw_trace_files_options ( new for v3.0.0 ) contains the Trace files directory
	 * 
	 * If trace options are not defined then tracing should only be performed if controlled programmatically.
	 */
	function load_trace_options() {
	    $this->trace_files_options = $this->get_option( 'bw_trace_files_options' );
		$this->trace_options = $this->get_option( 'bw_trace_options' );
		$this->action_options = $this->get_option( 'bw_action_options' );
	}

    /**
     * Since we don't yet know if performance tracing is enabled we try to get the options from the JSON files first.
     *
     * @param $option_name
     * @return false|mixed|void
     */
	function get_option( $option_name ) {
	    $option = null;
	    //if ( $this->is_performance_trace ) {
	    $option = $this->trace_json_options->get_option( $option_name );
        //}
	    if ( null === $option ) {
	        $option = get_option( $option_name );
			if ( null !== $option ) {
				$this->trace_json_options->maybe_trace_sync_to_json( $option, $option, $option_name );
			}
        }
	    return $option;
    }
	
	/**
	 * Loads the trace files directory object
	 *
	 * Note: If the trace files directory is not valid then we don't support tracing.
	 * 
	 */
	function load_trace_files_directory() {
		oik_require( "includes/class-trace-files-directory.php", "oik-bwtrace" );
		$trace_files_directory = new trace_files_directory();
		$trace_files_directory->set_options( $this->trace_files_options );
		$trace_files_directory->validate_trace_files_directory();
		if ( $trace_files_directory->is_valid() ) {
			$this->trace_files_directory = $trace_files_directory;
		}	else { 
			//echo "Invalid trace_files_directory" . PHP_EOL;
			//print_r( $trace_files_directory );
		}
	}

	/**
	 * Loads the trace file selector.
	 * 
	 * Loading the trace file selector means that $trace_options should already be set.
	 * We also know that tracing is required.
	 */
	function load_trace_file_selector() {
		oik_require( "includes/class-trace-file-selector.php", "oik-bwtrace" );
		$trace_file_selector = new trace_file_selector();
		$trace_file_selector->set_request_type( $this->request_type );
		$trace_file_selector->set_trace_options( $this->trace_options );
		$trace_file_selector->set_trace_files_directory( $this->trace_files_directory );
		$this->trace_file_selector = $trace_file_selector;
	}
	
	/**
	 * Loads the BW_trace_record class
	 * 
	 * This class replaces the procedural logic in includes/bwtrace.php
	 */
	function load_trace_record() {
		oik_require( "includes/class-BW-trace-record.php", "oik-bwtrace" );
		$trace_record = new BW_trace_record( $this );
		$trace_record->set_trace_options( $this->trace_options );
		$this->BW_trace_record = $trace_record;
	}
	
	/**
	 * Determines the request type from the available information
	 * 
	 * @return null|string Request type
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
		$_SERVER['request_type'] = $type;
		return $type;
	}
	
	/** 
	 * Determines if this request could be a REST request
	 * 
	 * @TODO Cater for custom REST routes
	 * 
	 * @return string|null 'rest' if we think or know it's a REST request
	 */
	public function maybe_rest() {
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
	 * Determines trace status
	 * 
	 * We need to determine the request type at some stage in order to find out
	 * what we're going to do with regards trace file generation
	 */
	public function status() {
	
		if ( $this->trace_files_directory ) {
			if ( defined( 'BW_TRACE_ON' ) && BW_TRACE_ON ) {
				$this->trace_on = BW_TRACE_ON;
				// $bw_trace_on should already be true... but can we turn it off?
				// How does that affect reset?	
				// Well, perhaps we can check the BW_TRACE_RESET constant and whether or not we started in wp-config
			} else {
				//$request_type = $this->query_request_type();
				if ( $this->request_type ) {
					$this->trace_on = $this->torf( "trace_" . $this->request_type );
				} else {
					$this->trace_on = $this->torf( "trace" );
				}
			}	
		} else {
			$this->trace_on = false;
		}		
		return $this->trace_on;
	}
	
	/**
	 * Determines if we're tracing a particular IP
	 *
	 * Pre-requisite to this function is that tracing is enabled for the request type.
	 * When the value of the $initial parameter is true then we're testing if
	 * we can continue to trace this request.
	 * When it's false the test finds out if we're actually tracing a specific IP.
	 *
	 * ip       | remote_addr | trace?
	 * -------  | ---------  | ------
	 *  x       |    x       | true
	 *  x       |    y       | false
	 *  not set |    any     | true
	 *
	 * The logic to compare the IP with the server API will be deprecated
	 * @param bool $default Default reply.
	 * @return bool true if we're tracing the IP or we don't care
	 */
	function trace_ip( $initial=true ) {
		$tracing_ip  = $initial;
		$bw_trace_ip = bw_array_get( $this->trace_options, 'ip', null );
		if ( $bw_trace_ip ) {
			$server = $this->get_remote_addr();
			if ( $server ) {
				$tracing_ip = ( $server === $bw_trace_ip );
			}
		}
		return $tracing_ip;
	}

	/**
	 * Returns what we believe to be the remote address
	 *
	 * Index   | May contain
	 * ------  | ----------
	 * HTTP_CF_CONNECTING_IP | Real IP set by Cloudflare
	 * HTTP_X_REAL_IP | IP extracted from HTTP_X_FORWARDED_FOR - set by Nginx?
	 * HTTP_X_FORWARDED_FOR | From X-Forwarded-For header. Series of IP addresses, comma separated. First is the user's IP, rest are proxy IPs
	 * REMOTE_ADDR | Server forwarding the request
	 *
	 * @return string|null Remote IP address
	 */
	function get_remote_addr() {
		$ip = null;
		if ( isset( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ) {
			$ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
		} elseif ( isset( $_SERVER['HTTP_X_REAL_IP'] ) ) {
			$ip = $_SERVER['HTTP_X_REAL_IP'];
		} elseif ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ip = explode( ',', $_SERVER['HTTP_X_FORWARDED_FOR'] );
			$ip = array_shift( $ip );
		} elseif ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		return $ip;
	}

	/**
	 * Determine the trace reset status
	 *
	 * We can reset the trace file regardless of the value of tracing
	 * except when we're only tracing a specific IP
	 * when we don't want to reset the trace file if we're not tracing this particular transaction.
	 *
	 * When we only had one trace file then the logic documented above applied.
	 * With trace file generation it's not so important. But which routine do we trust?
	 * In order to make the tests pass, we need to fiddle trace_ip() passing a parameter for the default reply.
	 *
	 * If the request contains '_bw_trace_reset' then we will force a reset.
	 * 
	 *
	 * $bw_trace_ip | $tracing | $bw_trace_reset ?
	 * ------------ | -------- | ---------------------
	 * set          | false    | don't reset
	 * set          | true		 | depends on the option 'reset' or 'reset_ajax'
	 * not-set      | either   | depends on the option 'reset' or 'reset_ajax'
	 *
	 * @return bool true if the trace file should be reset
	 */
	function reset_status() {
		$tracing_ip = $this->trace_ip( false );
		if ( $tracing_ip && !$this->trace_on ) { 
			$trace_reset = false;
		} else {
			$trace_reset = $this->query_reset();
        }
		return $trace_reset;
	}
	
	/** 
	 * Returns the reset value for the request_type
	 */
	function query_reset() {
		if ( $this->request_type ) {
			$trace_reset = $this->torf( 'reset_' . $this->request_type );
		} else {
			$trace_reset = $this->torf( 'reset' );
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
	 * Returns true if the trace_options field is set
	 * 
	 * @param string $option
	 * @return bool 
	 */
	function torf( $option ) { 
		$opt = bw_array_get( $this->trace_options, $option );
		$ret = $opt > '0';
		return $ret;
	}
	
	/**
	 * Returns true if the trace_actions field is set
	 * 
	 * @param string $option
	 * @return bool 
	 */
	function torf_action( $option ) {
		$opt = bw_array_get( $this->action_options, $option );
		$ret = $opt > '0';
		return $ret;
	}

	/**
	 * Set the SAVEQUERIES constant if possible 
	 *
	 * If we want to trace the queries then the SAVEQUERIES constant needs to be set to true.
	 * If it's already set then that MAY be hard lines; trace the value so that we know.
	 * 
	 * Note: Just because SAVEQUERIES is defined doesn't mean we should be tracing the queries.
	 */
	function set_savequeries() {
		global $bw_trace_savequeries;
		$bw_trace_savequeries = $this->torf_action( "trace_saved_queries" );
		if ( $bw_trace_savequeries ) {
			if ( !defined( 'SAVEQUERIES' ) ) {
				define( 'SAVEQUERIES', true );
			} else {
				bw_trace2( SAVEQUERIES, "SAVEQUERIES is already defined", false, BW_TRACE_VERBOSE );
			}
		}
		return $bw_trace_savequeries;
	} 
	
	public function set_trace_options( $trace_options ) {
		$this->trace_options = $trace_options;
	}
	public function set_action_options( $action_options ) {
		$this->action_options = $action_options;
	}

	/**
	 * Determines the required trace level
	 *
	 * The required trace level is determined by a number of methods
	 *
	 * - It may already be set as a global value
	 * - From the option variable "level"
	 * - @TODO From the constant BW_TRACE_LEVEL - which may be set as an integer in wp-config.php
	 * - If WP_DEBUG is false then the trace level remains the same 
	 * - @TODO If WP_DEBUG is true then it will become BW_TRACE_DEBUG
	 * 
	 * @return integer trace level. Negative when tracing is off
	 */
	function query_trace_level() {
		$trace_level = bw_array_get( $this->trace_options, "level", BW_TRACE_INFO );
		return $trace_level;
	}
	
	function set_trace_level( $level ) {
		$this->trace_level = (int) $level;
	}
	
	function get_trace_level() {
		return $this->trace_level;
	}
	
	function trace_on() {
		$this->trace_on = true;
	}
	
	function trace_off() {
		$this->trace_on = false;
	}
	
	/**
	 * Originally from bw_trace_trace_startup
	 */
	
	function trace_startup() {
		$levels = bw_list_trace_levels(false );
		$bw_trace_level = $this->get_trace_level();
		$trace_level_text = bw_array_get( $levels, $bw_trace_level, "Unknown" );
		bw_trace2( $bw_trace_level, "Trace level: $trace_level_text", false );
		//bw_lazy_backtrace(  );
		
		if ( $bw_trace_level >= BW_TRACE_INFO ) {
		
			bw_lazy_trace( $_SERVER, __FUNCTION__, __LINE__, __FILE__, "_SERVER" );
			bw_lazy_trace( $_REQUEST, __FUNCTION__, __LINE__, __FILE__, "_REQUEST" );
		}
		if ( $bw_trace_level >= BW_TRACE_DEBUG ) {
			bw_lazy_trace( $_GET, __FUNCTION__, __LINE__, __FILE__, "_GET" );
			bw_lazy_trace( $_POST, __FUNCTION__, __LINE__, __FILE__, "_POST" );
			if ( $bw_trace_level >= BW_TRACE_VERBOSE ) {
				bw_lazy_trace( $_COOKIE, __FUNCTION__, __LINE__, __FILE__, "_COOKIE" );
			}
			bw_lazy_trace( bw_getlocale(), __FUNCTION__, __LINE__, __FILE__, "locale" );
			if ( $bw_trace_level >= BW_TRACE_VERBOSE ) {
				bw_lazy_trace( $this->trace_options, __FUNCTION__, __LINE__, __FILE__, "trace_options" );
				bw_lazy_trace( $this->trace_files_options, __FUNCTION__, __LINE__, __FILE__, "trace_files_options" );
				bw_lazy_trace( $this->action_options, __FUNCTION__, __LINE__, __FILE__, "action_options" );
			}
			// Load oik-actions.php ?
			oik_require( "includes/oik-actions.php", "oik-bwtrace" );
			add_action( "plugins_loaded", "bw_trace_plugin_paths" );
		}
	}
	
	/**
	 * Forwards the trace request to the BW_trace_record class
	 *
	 * We can only do this if BW_trace_record has been set. See load_trace_record 
	 */
	public function lazy_trace( $text, $function=__FUNCTION__, $lineno=__LINE__, $file=__FILE__, $text_label=null, $level=BW_TRACE_ALWAYS ) {
		if ( $this->BW_trace_record ) {
			$this->BW_trace_record->lazy_trace( $text, $function, $lineno, $file, $text_label, $level );
		}
	}
	
	/**
	 * Returns the trace file name 
	 */
	public function get_trace_file_name() {
		$bw_trace_file2 = null;
		if ( $this->trace_file_selector ) {
			$bw_trace_file2 = $this->trace_file_selector->get_trace_file_name();
		} else {
			gob();
		}
		return $bw_trace_file2;
	}
	
	public function get_trace_file_url() {
		$bw_trace_file_url = null;
		if ( $this->trace_file_selector ) {	
			$bw_trace_file_url = $this->trace_file_selector->get_trace_file_url();
		} 
		return $bw_trace_file_url;
	}
	
	public function get_trace_count() {
		//$bw_trace_count = $this->trace_file_selector->get_trace_count();
		$bw_trace_count = null;
		if ( $this->BW_trace_record ) {
			$bw_trace_count = $this->BW_trace_record->trace_count;
		}
		return $bw_trace_count;
	}

	public function get_trace_error_count() {
		$bw_trace_error_count = null;
		if ( $this->BW_trace_record ) {
			$bw_trace_error_count = $this->BW_trace_record->trace_error_count();
		}
		return $bw_trace_error_count;
	}

	/**
	 * Purges the trace file if no trace errors encountered.
	 *
	 * If the trace error count is zero then we purge the trace
	 * file if there is one to purge.
	 */
	public function purge_trace_file_if_no_errors() {
		$bw_trace_error_count = $this->get_trace_error_count();
		if ( $bw_trace_error_count ) {
			bw_trace2( $bw_trace_error_count, 'Not purging the trace file' );
		} else {
			bw_trace2( $this->trace_file_selector );
			if ( $this->trace_file_selector ) {
				$this->trace_file_selector->attempt_reset();
			}
		}
	}

	/**
	 * Determines if we're trace action counting
	 *
	 */
	public function is_trace_hook_counting() {
		$counting = $this->torf_action( 'count' );
		return $counting;
	}

	/**
	 * Increments and returns the number of hooks ( actions and filters ) performed
	 * @param $args - if null we want the current value
	 * @return int
	 */
	public function set_trace_hook_count( $args=null ) {
		static $trace_hook_count = 0;
		if ( null !== $args ) {
			$trace_hook_count ++;
		}
		return $trace_hook_count;
	}

    /**
     * Sets is_performance_trace.
     *
     * @return bool
     */
	public function is_performance_trace() {
	    $performance_trace = isset( $this->trace_files_options['performance_trace'] );
	    if ( $performance_trace ) {
            $performance_trace = $this->trace_files_options['performance_trace'] === 'on';
        }
	    $this->is_performance_trace = $performance_trace;
	    return $this->is_performance_trace;

    }

    /**
     * Write performance trace output at shutdown.
     */
    public function shutdown_performance_trace() {
	    $this->load_trace_files_directory();
	    if ( $this->trace_file_selector ) {
            $this->trace_file_selector->set_trace_files_directory($this->trace_files_directory);
            $this->BW_trace_record->trace_file_selector = $this->trace_file_selector;
            $this->BW_trace_record->trace_controller = $this;
            $this->is_performance_trace = false;
            //echo "Performance trace off";
            //$this->BW_trace_record->trace_log('shutdown_performance_trace' );
            bw_trace2();
        }
    }
		
	




}
