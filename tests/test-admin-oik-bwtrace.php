<?php // (C) Copyright Bobbing Wide 2017, 2018

/**
 * @package oik-bwtrace 
 * 
 * Tests for logic in admin/oik-bwtrace.php
 */
class Tests_admin_oik_bwtrace extends BW_UnitTestCase {

	function setUp() {
		parent::setUp(); 
		oik_require( "admin/oik-bwtrace.php", "oik-bwtrace" );
		
	}
	
	/**
	 * Normally invoked by 'admin_init'
	 */
	function test_bw_trace_options_init() {
		bw_trace_options_init();
		$registered = get_registered_settings();
		$this->assertArrayHasKey( 'bw_trace_options', $registered );
	}
	
	/**
	 * Normally invoked by 'admin_init'
	 */
	function test_bw_action_options_init() {
		bw_action_options_init();
		$registered = get_registered_settings();
		$this->assertArrayHasKey( 'bw_action_options', $registered );
	}
	
	function test_bw_trace_options_add_page() {
		wp_set_current_user( 1 );
		$this->unset_submenu();
		$this->switch_to_locale( 'en_GB' );
		bw_trace_options_add_page();
		global $submenu;
		$html = $this->arraytohtml( $submenu );
		//$this->generate_expected_file( $html );
		$this->assertArrayEqualsFile( $html );
	}
	
	function test_bw_trace_options_add_page_bb_BB() {
		wp_set_current_user( 1 );
		$this->unset_submenu();
		$this->switch_to_locale( 'bb_BB' );
		bw_trace_options_add_page();
		global $submenu;
		$html = $this->arraytohtml( $submenu );
		//$this->generate_expected_file( $html );
		$this->assertArrayEqualsFile( $html );
		$this->switch_to_locale( 'en_GB' );
	}
	
	function test_bw_action_options_add_page() {
		wp_set_current_user( 1 );
		$this->unset_submenu();
		$this->switch_to_locale( 'en_GB' );
		bw_action_options_add_page();
		global $submenu;
		$html = $this->arraytohtml( $submenu );
		//$this->generate_expected_file( $html );
		$this->assertArrayEqualsFile( $html );
	}
	
	function test_bw_action_options_add_page_bb_BB() {
		wp_set_current_user( 1 );
		$this->unset_submenu();
		$this->switch_to_locale( 'bb_BB' );
		bw_action_options_add_page();
		global $submenu;
		$html = $this->arraytohtml( $submenu );
		//$this->generate_expected_file( $html );
		$this->assertArrayEqualsFile( $html );
		$this->switch_to_locale( 'en_GB' );
	}
	
	function test_bw_action_options_do_page() {
		//$this->setExpectedDeprecated( "bw_translate" );
		$_SERVER['REQUEST_URI'] = "/";
		$this->update_action_options();
		$this->switch_to_locale( 'en_GB' );
		ob_start(); 
		bw_action_options_do_page();
		$html = ob_get_contents();
		ob_end_clean();
		$this->assertNotNull( $html );
		$html = $this->replace_admin_url( $html );
		$html = $this->replace_home_url( $html );
		$html = $this->replace_php_end_of_life( $html );
		$html_array = $this->tag_break( $html );
		$this->assertNotNull( $html_array );
		$html_array = $this->replace_nonce_with_nonsense( $html_array );
		$html_array = $this->replace_nonce_with_nonsense( $html_array, "closedpostboxesnonce", "closedpostboxesnonce" );
		$html_array = $this->replace_oik_trace_info( $html_array );
		//$this->generate_expected_file( $html_array );
		$this->assertArrayEqualsFile( $html_array );
	}
	
	function test_bw_action_options_do_page_bb_BB() {
		$_SERVER['REQUEST_URI'] = "/";
		$this->update_action_options();
		$this->switch_to_locale( 'bb_BB' );
		ob_start(); 
		bw_action_options_do_page();
		$html = ob_get_contents();
		ob_end_clean();
		$this->assertNotNull( $html );
		$html = $this->replace_admin_url( $html );
		$html = $this->replace_home_url( $html );
		$html = $this->replace_php_end_of_life( $html );
		$html_array = $this->tag_break( $html );
		$this->assertNotNull( $html_array );
		$html_array = $this->replace_nonce_with_nonsense( $html_array );
		$html_array = $this->replace_nonce_with_nonsense( $html_array, "closedpostboxesnonce", "closedpostboxesnonce" );
		$html_array = $this->replace_oik_trace_info( $html_array );
		//$this->generate_expected_file( $html_array );
		$this->assertArrayEqualsFile( $html_array );
		$this->switch_to_locale( 'en_GB' );
	}
	
	function update_action_options() {
		$bw_action_options = get_option( "bw_action_options" );
		$bw_action_options['count'] = 'on';
    $bw_action_options['trace_deprecated'] = 'on';
    $bw_action_options['trace_errors'] = 'on';
    $bw_action_options['trace_wp_action'] = '0';
    $bw_action_options['trace_wp_rewrite'] = '0';
    $bw_action_options['trace_included_files'] = 'on';
    $bw_action_options['trace_saved_queries'] = 'on';
    $bw_action_options['trace_output_buffer'] = 'on';
    $bw_action_options['trace_functions'] = 'on';
    $bw_action_options['trace_status_report'] = 'on';
    $bw_action_options['hooks'] = '';
    $bw_action_options['results'] = '';
    $bw_action_options['post_hooks'] = '';
    $bw_action_options['hook_funcs'] = '';
    $bw_action_options['backtrace'] = '';
    $bw_action_options['stringwatch'] = '';
		update_option( "bw_action_options", $bw_action_options );
	}
	
	/** 
	 * Tests the trace options page
	 */
	function test_bw_trace_options_do_page() {
	
		//$this->setExpectedDeprecated( "bw_translate" );
		$_SERVER['REQUEST_URI'] = "/";
		bw_trace_off();
		
		$this->update_trace_options();
		$this->switch_to_locale( 'en_GB' );
		ob_start(); 
		bw_trace_options_do_page();
		$html = ob_get_contents();
		ob_end_clean();
		$this->assertNotNull( $html );
		$html = $this->replace_trace_url( $html );
		$html = $this->replace_admin_url( $html );
		$html = $this->replace_home_url( $html );
		$html = $this->replace_php_end_of_life( $html );
		$html = $this->replace_memory_limit( $html );
		$html_array = $this->tag_break( $html );
		$this->assertNotNull( $html_array );
		$html_array = $this->replace_nonce_with_nonsense( $html_array );
		$html_array = $this->replace_nonce_with_nonsense( $html_array, "closedpostboxesnonce", "closedpostboxesnonce" );
		$html_array = $this->replace_oik_trace_info( $html_array );
		//$this->generate_expected_file( $html_array );
		$this->assertArrayEqualsFile( $html_array );
	}
	
	/** 
	 * Tests the trace options page
	 */
	function test_bw_trace_options_do_page_bb_BB() {
		$_SERVER['REQUEST_URI'] = "/";
		$this->update_trace_options();
		$this->switch_to_locale( 'bb_BB' );
		ob_start(); 
		bw_trace_options_do_page();
		$html = ob_get_contents();
		ob_end_clean();
		$this->assertNotNull( $html );
		$html = $this->replace_trace_url( $html );
		$html = $this->replace_admin_url( $html );
		$html = $this->replace_home_url( $html );
		$html = $this->replace_php_end_of_life( $html );
		$html = $this->replace_memory_limit( $html );
		
		$html_array = $this->tag_break( $html );
		$this->assertNotNull( $html_array );
		$html_array = $this->replace_nonce_with_nonsense( $html_array );
		$html_array = $this->replace_nonce_with_nonsense( $html_array, "closedpostboxesnonce", "closedpostboxesnonce" );
		$html_array = $this->replace_oik_trace_info( $html_array );
		//$this->generate_expected_file( $html_array );
		$this->assertArrayEqualsFile( $html_array );
		
		$this->switch_to_locale( 'en_GB' );
	}
	
	function update_trace_options() {
		$bw_trace_options = get_option( "bw_trace_options" );
		$bw_trace_options['file'] = 'bwphpunit.loh';
		$bw_trace_options['reset'] = 'on';
		$bw_trace_options['trace'] = '0';
		$bw_trace_options['file_ajax'] = 'bwphpunit.ajax';
		$bw_trace_options['reset_ajax'] = 'on';
		$bw_trace_options['trace_ajax'] = 'on'; 
		$bw_trace_options['file_rest'] = 'bwphpunit.rest';
		$bw_trace_options['reset_reset'] = 'on';
		$bw_trace_options['trace_rest'] = '0'; 
		$bw_trace_options['file_cli'] = 'bwphpunit.cli';
		$bw_trace_options['reset_cli'] = 'on';
		$bw_trace_options['trace_cli'] = '0'; 
		$bw_trace_options['limit'] = ''; 
		$bw_trace_options['level'] = BW_TRACE_INFO; // 16
    $bw_trace_options['qualified'] = 'on';
    $bw_trace_options['count'] = 'on';
    $bw_trace_options['date'] = 'on';
    $bw_trace_options['filters'] = 'on';
    $bw_trace_options['num_queries'] = 'on';
    $bw_trace_options['post_id'] = 'on';
    $bw_trace_options['memory'] = 'on';
    $bw_trace_options['files'] = 'on';
    $bw_trace_options['ip'] = php_sapi_name();
		update_option( "bw_trace_options", $bw_trace_options );
		
	}
	
	/**
	 * Replace the values in the oik_trace_info table with consistent values
	 */
	function replace_oik_trace_info( $html_array ) {
		$found_oik_trace_info = false; 
		$count_td = 0; 				 
		foreach ( $html_array as $index => $line ) {
			
			if ( !$found_oik_trace_info ) {
				$found_oik_trace_info = ( false !== strpos( $line, '<div class="postbox " id="oik_trace_info">' ) );
			}
			if ( $found_oik_trace_info ) {
				if ( 0 === strpos( $line, "<td>" ) ) {
					$count_td++;
					switch ( $count_td ) {
						case 0:
						case 1:
						break;
					
						case 2:
							$html_array[$index] = "<td>$index</td>";
							break;
						
						case 3:
							$count_td = 0;
					}
				}
			}
		}
		return $html_array;
	}
	
	/**
	 * Replaces the PHP end of life message with a non-translatable literal string
	 */
	function replace_php_end_of_life( $html ) {
		$php_end_of_life = $this->php_end_of_life();
		$html = str_replace( $php_end_of_life, "generic PHP end of life", $html ); #
		return $html;
	}
	
	/**
	 * Replaces the memory limit with a fixed value
	 */
	function replace_memory_limit( $html ) {
		$memory_limit = ini_get( "memory_limit" );
		$html = str_replace( $memory_limit, "xyzM", $html );
		return $html;
	}
	
	function replace_trace_url( $html ) {
		$trace_url = bw_trace_url();
		$html = str_replace( $trace_url, "https://qw/src/bwphpunit.loh", $html );
		return $html;
	}
		
	
	/**
	 * Gets the PHP end of life message
	 */
	function php_end_of_life() {
		oik_require( "admin/class-oik-trace-info.php", "oik-bwtrace" );
		$oik_trace_info = new OIK_trace_info;
		$php_end_of_life = $oik_trace_info->php_end_of_life();
		$this->assertNotNull( $php_end_of_life );
		return $php_end_of_life;
	}
	
	function unset_submenu() {	
		unset( $GLOBALS['submenu'] );
		//print_r( $GLOBALS['submenu'] );
	}
	
	/**
	 * Reloads the text domains
	 *
	 * Note: We have to load "oik" for the switch_to_locale to work
	 * @TODO: Change it to only reload what's necessary
	 */
	function reload_domains() {
		$domains = array( "oik", "oik-bwtrace" );
		foreach ( $domains as $domain ) {
			$loaded = bw_load_plugin_textdomain( $domain );
			$this->assertTrue( $loaded, "$domain not loaded" );
		}
		oik_require_lib( "oik-l10n" );
		oik_l10n_enable_jti();
	}
	
	/**
	
oik-bwtrace.php 147 1:function bw_trace_options_do_page() {
oik-bwtrace.php 164 1:function oik_trace_options() {
oik-bwtrace.php 214 1:function oik_trace_notes() {
oik-bwtrace.php 240 1:function oik_trace_reset_notes() {
oik-bwtrace.php 244 8:          if ( function_exists( "bw_invoke_shortcode" ) ) {
oik-bwtrace.php 261 1:function oik_trace_info() {
oik-bwtrace.php 274 1:function bw_trace_options_validate($input) {
oik-bwtrace.php 282 28: * Note: If the validation function does not exist then no value is returned and the options don't get saved.
oik-bwtrace.php 288 1:function bw_action_options_validate( $input ) {
oik-bwtrace.php 304 1:function bw_trace_url( $option='bw_trace_options', $ajax=false ) {
oik-bwtrace.php 324 1:function bw_this_plugin_first( $plugin, $network_wide ) {
 */
	
}	
