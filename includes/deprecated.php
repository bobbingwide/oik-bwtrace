<?php // (C) Copyright Bobbing Wide 2015-2019

/**
 * deprecated functions for oik-bwtrace
 *
 * This file contains oik-bwtrace functions that have been deprecated.
 * In order to use these you actually need to include this file yourself.
 * We don't really expect you to want to do this.
 *
 * Note: Some functions that were deprecated some time ago have now been deleted.
 * - oik_bwtrace_version
 * - bw_trace_actions_on
 * - bw_trace_actions_off
 * - bw_trace_add_actions
 * - bw_trace_actions
 * - bw_call_lazy_trace_actions
 * - bw_trace_hook_all
 * - bw_lazy_trace_actions
 * - bw_action_inc_init
 * - bw_actions_reset
 * - bw_action_file
 * - bw_action_line
 * - bw_log_action
 * - bw_trace_action_start
 * - bw_trace_action_end
 * - bw_lazy_trace_action_immediate
 * - bw_trace_action_immediate
 */

/**
 * Creates the Trace reset button for use somewhere in any page
 */
function bw_trace_reset_form() {
	oik_require( "bobbforms.inc" );
	e( '<form method="post" action="" class="bw_inline">' );
	e ( isubmit( "_bw_trace_reset", __( "Trace reset", "oik" ), null ));
	etag( "form" );
}

