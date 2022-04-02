<?php

/**
 * @copyright (C) Copyright Bobbing Wide 2022
 * @package oik-bwtrace
 *
 * Implements trace options in synchronised JSON files
 * stored in the mu-plugins folder and used when the
 * Trace file > Enable performance trace checkbox option is set.
 * ( bw_trace_files_options.performance_trace )
 *
 *
 */

class trace_json_options {

    function __construct() {

    }

    /**
     * Checks if performance tracing is enabled.
     */
    function is_performance_trace() {
        return true;

    }

    /**
     * Checks if we can use the mu-plugins folder.
     */
    function is_mu_enabled() {
        $enabled = is_dir( WPMU_PLUGIN_DIR );
        return $enabled;
    }

    /**
     *
     * @param $old_value
     * @param $new_value
     * @param $option
     * @return bool
     */
    function maybe_trace_sync_to_json( $old_value, $new_value, $option ) {
        if ( $this->is_mu_enabled() ) {
            $this->sync_to_json($old_value, $new_value, $option);
        }
        return true;
    }

    /**
     * Synchronises (exports) the options settings to a JSON file.
     *
     * This function gets called when there have been updates.
     *
     * @param $old_value
     * @param $new_value
     * @param $option
     */

    function sync_to_json( $old_value, $new_value, $option ) {
        $json = json_encode( $new_value );
        $json_file = $this->get_json_file_name( $option );
        $written = file_put_contents( $json_file, $json );
        bw_trace2( $written, "written to: $json_file" );
    }

    function get_json_file_name( $option ) {
        global $blog_id;
        $json_file = WPMU_PLUGIN_DIR ;
        $json_file .= '/';
        $json_file .= $option;
        $json_file .= '.';
        $json_file .= $blog_id;
        $json_file .= '.json';
        bw_trace2( $json_file, "json file", true);
        return $json_file;
    }



}