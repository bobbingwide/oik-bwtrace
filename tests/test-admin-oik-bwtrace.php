<?php // (C) Copyright Bobbing Wide 2017

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
	
oik-bwtrace.php 49 1:function bw_trace_options_add_page() {
oik-bwtrace.php 60 1:function bw_action_options_add_page() {
oik-bwtrace.php 71 1:function bw_action_options_do_page() {
oik-bwtrace.php 86 1:function oik_action_options() {
oik-bwtrace.php 104 65:  bw_checkbox_arr( "bw_action_options", "Trace 'shutdown' trace functions count", $options, 'trace_functions' );
oik-bwtrace.php 104 100:  bw_checkbox_arr( "bw_action_options", "Trace 'shutdown' trace functions count", $options, 'trace_functions' );
oik-bwtrace.php 110 61: bw_textarea_arr( "bw_action_options", "Trace attached hook functions", $options, "hook_funcs", 80 );
oik-bwtrace.php 120 1:function oik_action_notes() {
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
