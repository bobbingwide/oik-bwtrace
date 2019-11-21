<?php

/**
 * @copyright Bobbing Wide 2018, 2019
 * @package oik-bwtrace
 *
 * Tests for logic in oik-bwtrace.php
 */
class Tests_issue_78 extends BW_UnitTestCase {

	public $bw_trace_on = null;

	function setUp() : void {
		parent::setUp();
		oik_require( "includes/oik-actions.php", "oik-bwtrace" );
	}

	function test_bw_trace_ok_to_echo_woocommerce_product_export() {
		$_REQUEST['action'] = 'download_product_csv';
		$ok = bw_trace_ok_to_echo();
		$this->assertFalse( $ok );
	}

	function test_bw_trace_ok_to_echo_block_data_export() {
		$_REQUEST['block_data_export'] = 'Advanced Gutenberg Contact Form sets this';
		$ok = bw_trace_ok_to_echo();
		$this->assertFalse( $ok );
	}

	function test_bw_trace_ok_to_echo_edd_api() {
		$_REQUEST['edd-api'] = 'Easy Digital Downloads API sets this';
		$ok = bw_trace_ok_to_echo();
		$this->assertFalse( $ok );
	}
}
