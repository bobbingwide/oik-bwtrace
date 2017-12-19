<?php // (C) Copyright Bobbing Wide 2017

/**
 * @package 
 * 
 * Tests for logic in includes/bwtrace.php
 */
class Tests_includes_bwtrace extends BW_UnitTestCase {

	function setUp() { 
		bw_trace_on();
	}
	
	/**
	 * Returns an incomplete object.
	 *
	 * That would print_r() like this
	 * 
	 * `__PHP_Incomplete_Class Object
			(
			 [__PHP_Incomplete_Class_Name] => IncompleteObject
				[pi] => 3.142
			) 
	 * `		
	 */
	function incomplete_object() {
		$incomplete_object = unserialize( 'O:16:"IncompleteObject":1:{s:2:"pi";d:3.142;}' );
		return( $incomplete_object );
	}
	
	/**
	 * Debug backtraces the incomplete object
	 */
	function backtrace( $incomplete_object ) {
		bw_backtrace();
	}
	
	/**
	 * Traces the incomplete object
	 */
	function trace2( $incomplete_object ) {
		bw_trace2();
	}
	
	/**
	 * Test we can trace an incomplete object
	 * 
	 * Not that it caused a problem in the first place.
	 */ 
	function test_bw_trace2_issue56() {
		$incomplete_object = $this->incomplete_object();
		$this->trace2( $incomplete_object );
		$this->assertTrue( true );
	}

	/**
	 * Test for Issue #56
	 * 
	 * We should not see a Catchable Fatal Error or Recoverable Fatal Error
	 * 
	 * depends test_bw_trace2_issue56
	 */
	function test_bw_lazy_backtrace_issue56() {
		$incomplete_object = $this->incomplete_object();
    $this->backtrace( $incomplete_object );
		$this->assertTrue( true );
	}
	
	
/*		
O:28:"GoogleSitemapGeneratorStatus":4:{s:39:"?GoogleSitemapGeneratorStatus?startTime";d:1430982865.7435801029205322265625;s:37:"?GoogleSitemapGeneratorStatus?endTime";d:1430982866.094378948211669921875;s:41:"?GoogleSitemapGeneratorStatus?pingResults";a:2:{s:6:"google";a:5:{s:9:"startTime";d:1430982865.784204006195068359375;s:7:"endTime";d:1430982865.904530048370361328125;s:7:"success";b:1;s:3:"url";s:110:"http://www.google.com/webmasters/sitemaps/ping?sitemap=http%3A%2F%2Fwww.bobbingwidewebdesign.com%2Fsitemap.xml";s:4:"name";s:6:"Google";}s:4:"bing";a:5:{s:9:"startTime";d:1430982865.922090053558349609375;s:7:"endTime";d:1430982866.0639240741729736328125;s:7:"success";b:1;s:3:"url";s:103:"http://www.bing.com/webmaster/ping.aspx?siteMap=http%3A%2F%2Fwww.bobbingwidewebdesign.com%2Fsitemap.xml";s:4:"name";s:4:"Bing";}}s:38:"?GoogleSitemapGeneratorStatus?autoSave";b:1;}
		$expected_output = array();
		$atts = null;
		$atts = bw_cast_array( $atts );
		$this->assertEquals( $atts, $expected_output );
		$atts = '';
		$atts = bw_cast_array( $atts );
		$this->assertEquals( $atts, $expected_output );
		$atts = '0';
		$atts = bw_cast_array( $atts );
		$this->assertEquals( $atts, array( '0' ) );
	}
*/

	 
	/**
	 * Test turning trace on and off
	 */
	function test_bw_trace_on_off() {
		global $bw_trace_on;
		$saved = $bw_trace_on;
		bw_trace_on();
		$this->assertTrue( $bw_trace_on );
		bw_trace_off();
		$this->assertFalse( $bw_trace_on );
		$bw_trace_on = $saved;
	}
	
	/**
	 * Tests anonymize logic for symlinked files
	 *
	 * This really needs ABSPATH to be different from C:/apache/htdocs/wordpress
	 * That's true in qw/oikcom but not qw/wordpress
	 */
	function test_bw_trace_anonymize_symlinked_file() {
		global $wp_plugin_paths;
		$saved_paths = $wp_plugin_paths;
		// key = symlinked directory, data = target directory, where the file really is
		$wp_plugin_paths = array( ABSPATH . "/wp-content/plugins/oik-bwtrace" => "C:/not/abspath/wp-content/plugins/oik-bwtrace" );
		$file = bw_trace_anonymize_symlinked_file( "C:/not/abspath/wp-content/plugins/oik-bwtrace/oik-bwtrace.php" );
		$this->assertEquals( "/wp-content/plugins/oik-bwtrace/oik-bwtrace.php", $file );
		$wp_plugin_paths = $saved_paths;
		//print_r( $wp_plugin_paths );
	}
	
	/** 
	 * Test bw_trace_file_part()
	 * 
	 * Note: This test doesn't invoke bw_trace_anonymized_symlinked_file
	 */
	function test_bw_trace_file_part() {
    global $bw_trace_anonymous;
		$saved = $bw_trace_anonymous;
		$bw_trace_anonymous = true;
		$actual = bw_trace_file_part( ABSPATH . "\\wp-content\\plugins\\oik-bwtrace.php" );
		$this->assertEquals( "/wp-content/plugins/oik-bwtrace.php", $actual );
		$bw_trace_anonymous = $saved;
		
	}
	
	
	/**
	 * How do we test a function that uses microtime?
	 */
	
	function test_bw_trace_elapsed() {
		global $bw_include_trace_date;
		$saved = $bw_include_trace_date;
		$bw_include_trace_date = false;
		$actual = bw_trace_elapsed();
		$this->assertNull( $actual );
		
		$bw_include_trace_date = true;
		$actual = bw_trace_elapsed();
		$actual = str_replace( array( "1", "2", "3", "4", "5", "6", "7", "8", "9" ), array( "0", "0", "0", "0", "0", "0", "0", "0", "0" ), $actual );
		$this->assertEquals( "0.000000 0.000000 ", $actual );
		$bw_include_trace_date = $saved;
	}
	
	
	
	
	/**
	 * There are some functions we won't bother to test explicitely
	 * Function | Notes
	 * --------- | -----------------------------------
	 * bw_trace_inc_init | sets some hardcoded values in $bw_trace_options and may perform trace reset. It's called by bw_lazy_trace_config_startup from bw_trace_config_startup.
	 * bw_getlocale | See bw_trace_trace_startup
	 */
	 
	 
		
	 
	 
		

/**

bwtrace.php 143 1:function bw_trace_elapsed( ) {
bwtrace.php 169 1:function bw_trace_date( $format=DATE_W3C ) {
bwtrace.php 186 1:function bw_trace_count( $count ) {
bwtrace.php 203 1:function bw_trace_function( $function ) {
bwtrace.php 221 1:function bw_current_filter() {
bwtrace.php 236 1:function bw_get_num_queries() {
bwtrace.php 259 1:function bw_trace_set_savequeries() {
bwtrace.php 280 1:function bw_get_post_id() {
bwtrace.php 302 1:function bw_trace_post_id() {
bwtrace.php 320 1:function bw_get_memory_usage() {
bwtrace.php 345 1:function bw_trace_bwechos() {
bwtrace.php 372 1:function bw_trace_context() {
bwtrace.php 405 1:function bw_set_context( $key, $value=NULL ) {
bwtrace.php 419 1:function bw_trace_context_all( $function=NULL, $line=NULL, $file=NULL ) {
bwtrace.php 431 1:function bw_trace_file_count() {
bwtrace.php 451 1:function bw_trace_print_r( $text ) {
bwtrace.php 481 1:function bw_trace_obsafe_print_r( $var, $level=0, &$visitedVars = array()) {
bwtrace.php 579 1:function bw_flf( $function, $lineno, $file, $count, $text, $text_label = NULL, $level=BW_TRACE_ALWAYS ) {
bwtrace.php 606 1:function bw_array_inc( &$array, $index ) {
bwtrace.php 626 1:function bw_lazy_trace( $text, $function=__FUNCTION__, $lineno=__LINE__, $file=__FILE__, $text_label=NULL, $level=BW_TRACE_ALWAYS ) {
bwtrace.php 656 1:function bw_trace_file_name( $bw_trace_options, $ajax=false ) {
bwtrace.php 681 1:function bw_trace_file() {
bwtrace.php 710 1:function bw_trace_batch() {
bwtrace.php 725 1:function bw_trace_log( $line ) {
bwtrace.php 764 1:function bw_write( $file, $line ) {
bwtrace.php 804 1:function bw_trace_reset() {
bwtrace.php 822 1:function bw_trace_errors( $level ) {
bwtrace.php 837 3:  function bw_array_get( $array = NULL, $index, $default=NULL ) {
bwtrace.php 936 1:function bw_lazy_backtrace() {
bwtrace.php 1009 1:function bw_lazy_trace2( $value=null, $text=null, $show_args=true, $level=null ) {
bwtrace.php 1066 1:function bw_list_trace_levels() {
bwtrace.php 1087 1:function bw_trace_trace_startup() {

*/
	 
}
	
