<?php // (C) Copyright Bobbing Wide 2017-2019

/**
 * @package 
 * 
 * Tests for logic in shortcodes/oik-trace.php
 */
class Tests_shortcodes_oik_trace extends BW_UnitTestCase {

	function setUp() { 
		parent::setUp();
		
		oik_require_lib( "oik-sc-help" );
		oik_require( "shortcodes/oik-trace.php", "oik-bwtrace" );
		oik_require_lib( "oik_plugins" );
	}
	
	function test_bwtrace__syntax() {
		//$this->setExpectedDeprecated( "bw_translate" );
		$this->switch_to_locale( "en_GB" );
		$array = bwtrace__syntax();
		$html = $this->arraytohtml( $array, true );
		//$this->generate_expected_file( $html );
		$this->assertArrayEqualsFile( $html );
	}
	
	function test_bwtrace__syntax_bb_BB() {
		$this->switch_to_locale( "bb_BB" );
		$array = bwtrace__syntax();
		$html = $this->arraytohtml( $array, true );
		//$this->generate_expected_file( $html );
		$this->assertArrayEqualsFile( $html );
		$this->switch_to_locale( "en_GB" );
	}
	
	/**
	 * - Here we force the trace log file to be bwtraces.loh
	 * - We expect trace to be enabled
	 */ 
	function test_bwtrace__example() {
		//bw_update_option( "file", "bwtraces.loh", "bw_trace_options" );
		$this->switch_to_locale( "en_GB" );
		$html = bw_ret( bwtrace__example() );
		$html = $this->replace_home_url( $html );
		//$this->generate_expected_file( $html );
		$this->assertArrayEqualsFile( $html );
		$this->switch_to_locale( "en_GB" );
	}
	
	function test_bwtrace__example_bb_BB() {
		//bw_update_option( "file", "bwtraces.loh", "bw_trace_options" );
		$this->switch_to_locale( "bb_BB" );
		$html = bw_ret( bwtrace__example() );
		$html = $this->replace_home_url( $html );
		//$this->generate_expected_file( $html );
		$this->assertArrayEqualsFile( $html );
		$this->switch_to_locale( "en_GB" );
	}

	/**
	 * Reloads the text domains
	 *
	 * - Loading the 'oik-libs' text domain from the oik-libs plugin invalidates tests where the plugin is delivered from WordPress.org so oik-libs won't exist.
	 * - but we do need to reload (oik's and?) oik-bwtrace's text domains
	 * - and cause the null domain to be rebuilt.
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
	
	
}
	
