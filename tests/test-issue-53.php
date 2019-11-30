<?php // (C) Copyright Bobbing Wide 2019

/**
 * @package oik-bwtrace
 *
 * Tests for Issue #53
 */
class Tests_issue_53 extends BW_UnitTestCase {

	function setUp() : void {
		parent::setUp();
	}

	/**
	 * Test it returns true when the bw_action['count'] is on and false otherwise.
	 * We need to set the current value of bw_action['count']
	 * It's a public property at present so we can just update it directly
	 */
	function test_bw_trace_controller_is_trace_counting() {
		global $bw_trace;
		if ( $bw_trace ) {
			$saved_action_options_count = $bw_trace->action_options['count'];
			$bw_trace->action_options['count'] = 'on';
			$counting = $bw_trace->is_trace_hook_counting();
			$this->assertTrue( $counting );
			$bw_trace->action_options['count'] = 0;
			$counting = $bw_trace->is_trace_hook_counting();
			$this->assertFalse( $counting );
			$bw_trace->action_options['count'] = $saved_action_options_count;
		}
	}

	function test_bw_trace_controller_set_trace_hook_count() {
		global $bw_trace;
		$count0 = $bw_trace->set_trace_hook_count();
		$count0++;
		$count1 = $bw_trace->set_trace_hook_count( 'test');
		$this->assertEquals( $count1, $count0 );
		$count2 = $bw_trace->set_trace_hook_count();
		$this->assertEquals( $count2, $count1 );
	}

	function test_bw_trace_record_trace_hook_count() {
		global $bw_trace;
		$saved_action_options_count = $bw_trace->action_options['count'];
		$bw_trace->action_options['count'] = 'on';
		$count0 = $bw_trace->set_trace_hook_count();
		$bw_trace_record = new BW_trace_record( $bw_trace );
		$countspace = $bw_trace_record->trace_hook_count();
		$this->assertEquals( $count0 . ' ', $countspace );
		$bw_trace->action_options['count'] = 0;
		$bw_trace_record = new BW_trace_record( $bw_trace );
		$countspace = $bw_trace_record->trace_hook_count();
		$this->assertNull( $countspace );
		$bw_trace->action_options['count'] = $saved_action_options_count;
	}

}


