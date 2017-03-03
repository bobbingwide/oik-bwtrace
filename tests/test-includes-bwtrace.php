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
	 
}
	
