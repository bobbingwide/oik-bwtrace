# oik-bwtrace 
* Contributors: bobbingwide, vsgloik
* Donate link: http://www.oik-plugins.com/oik/oik-donate/
* Tags: debug, trace, backtrace, actions, filters, ad hoc tracing, hook tracing, filter tracing
* Requires at least: 4.2
* Tested up to: 4.4
* Stable tag: 2.0.9
* License: GPLv2 or later
* License URI: http://www.gnu.org/licenses/gpl-2.0.html

## Description 
Debug trace for WordPress, including ad hoc action hook and filter tracing.

* The primary purpose of debug trace is to help in the development of PHP code: plugins or themes.
* The primary purpose of action trace is to help you understand the sequence of events in the server.
* The primary purpose of ad hoc tracing is to let you see what's happening without changing any code.
* This plugin provides the admin interface to trace functions and methods and action and filter tracing.

Except for HTML comments, oik bwtrace does not alter the output of your web pages.
Output is written to files on the server which you can browse in a separate window.

You can also use the oik trace facilities to assist in problem determination in a live site.


Features:

* Traces ALL server functionality, including AJAX and other background requests
* Ability to choose the IP address to trace, defaults to ALL requests
* Trace AJAX transactions separately, if required
* Supports ad hoc tracing of user defined hooks
* Action trace counts help you understand the sequence of actions and filters
* Provides contextual information
* Minimum performance overhead when tracing is not enabled
* Tracing can be enabled programmatically
* Traces and backtraces PHP Error, Warning and Notice messages
* Backtraces deprecated logic messages
* Writes summary trace record for each transaction into a daily log
* Does not require WP_DEBUG to be defined
* Dynamically activates trace functions
* Implemented as lazy just-in-time code
* Can be used during regression testing
* Can be activated in wp-config.php and db.php to trace code before WordPress is fully loaded
* Plugin does not need to be activated if started programmatically or from wp-config.php
* Operates as a standalone plugin; independent of the oik base plugin
* Integrated with oik-lib shared library management
* Easy to code APIs: bw_trace2(), bw_backtrace() and bw_trace()
* API supports multiple trace levels

Ad hoc tracing allows you to:

* trace parameters
* trace results
* trace registered functions
* trace the global post
* set the priority for the trace hook
* debug backtrace hook invocation


The trace record output can include:

* Fully qualified source file name
* Trace record count
* Time stamp
* Current filter information
* Number of database queries that have been performed.
* Current post ID
* Current and peak memory usage (in bytes)
* Files loaded count
* Contextual information

The output for action tracing can include trace records showing:

* Count of action hooks and filters
* Invocation of the 'wp' actions
* Contents of the global wp_rewrite for the 'wp' action
* Summary reports at 'shutdown'
* Information about deprecated logic
* Information related to Errors, Warnings and Notices

New in v2.0.8

* Ad hoc debug backtrace for selected hooks

New in v2.0.7

* Ability to control tracing of AJAX transactions

New in v2.0.6

* Parameters passed to user selected hooks
* Returned values from user selected filters
* Current status of the global post object for user selected hooks
* Information about attached hook functions for user selected hooks

If you select "Trace 'shutdown' status report and log in summary file"
then you also get a daily summary log, named bwtrace.vt.mmdd

The summary daily log contains information that can be used for performance analysis.
This log is produced even when tracing is not enabled.


See also:

* [bw_trace2()](http://www.oik-plugins.com/oik_api/bw_trace2)
* [bw_backtrace()](http://www.oik-plugins.com/oik_api/bw_backtrace)
* [bw_trace()](http://www.oik-plugins.com/oik_api/bw_trace)


## Installation 
1. Upload the contents of the oik-bwtrace plugin to the `/wp-content/plugins/oik-bwtrace' directory
1. Activate the oik-bwtrace plugin through the 'Plugins' menu in WordPress
1. Define your trace options using Settings > trace options
1. Define your action trace options using Settings > action options
1. Don't forget to disable tracing when you no longer need it

## Frequently Asked Questions 
# Where is the FAQ? 
[oik-bwtrace FAQ](http://www.oik-plugins.com/oik-plugins/oik-bwtrace-debug-trace-for-wordpress/?oik-tab=faq)

# Can I get support? 
Use the contact form on the oik-plugins website.

## Is there a tutorial? 
See this page and short video
[Introduction to oik-bwtrace](http://www.oik-plugins.com/wordpress-plugins-from-oik-plugins/free-oik-plugins/oik-trace-plugin/an-introduction-to-problem-determination-with-oik-bwtrace-debug-trace-for-wordpress)

## How do I trace from startup? 

If we want to include bw_trace(), bw_trace2() or bw_backtrace() calls in WordPress core
then we need to define the functions, so we include /libs/bwtrace.php.

If we want trace and action count to be enabled and reset at WordPress startup then we also need to define these as TRUE

Put the following in your wp-config.php file

`define( 'BW_TRACE_CONFIG_STARTUP', true );
define( 'BW_TRACE_ON', true );
define( 'BW_COUNT_ON', true );
define( 'BW_TRACE_RESET', true );

if ( file_exists( ABSPATH . '/wp-content/plugins/oik-bwtrace/lib/bwtrace.php' ) ) {
  require_once( ABSPATH . '/wp-content/plugins/oik-bwtrace/lib/bwtrace.php' );
}
`

Don't forget to remove or comment out this code when you no longer need it.


## Screenshots 
1. Trace options
2. Action options - part 1
3. Action options - part 2
4. Raw trace output
5. Daily summary log

## Upgrade Notice 
# 2.0.9 
Tested with WordPress 4.4 and WordPress MultiSite

# 2.0.8 
Upgrade for ad hoc debug backtracing

# 2.0.7 
Upgrade for improved support for AJAX requests

# 2.0.6 
Now supports user selected action hook tracing

* "Other hooks to trace",
* "Filter results to trace"
* "Trace the global post object"
* "Trace attached hook functions"

Added BW_TRACE_VERBOSE ( 64 ); an even higher level than BW_TRACE_DEBUG ( 32 ).

# 2.0.5 
Now supports multiple trace levels.

# 2.0.4 
Upgrade to use 'Error, Warning and Notice' detection in output buffered situations.

# 2.0.3 
Upgrade for improved deprecated logic support and information related to Error, Warning and Notice type messages

# 2.0.2 
Use to find the cause of those pesky "Deprecated constructor" messages

# 2.0.1 
Improved response to "oik_query_libs"

# 2.0.0 
Now works as a standalone plugin. Prior to upgrading oik-bwtrace please deactivate it and upgrade the oik base plugin to 2.6-alpha.0724 or higher.

# 1.28 
Now operates as a standalone plugin

# 1.27 
Current filter reporting and other minor improvements

# 1.26 
Improved logic for self implementation as an MU plugin

# 1.25 
Improved support for action hooks and filters.
Now implements itself as a Must Use (MU) plugin if action counting is selected.
Also contains logic that can be activated in db.php.

# 1.24 
Upgrade to get remote IP address in the summary log

# 1.23 
Fixes warning message when tracing not active but action count tracing is.

# 1.22 
Now outputs a single summary record for each transaction. Required for oik-plugins.com analysis

# 1.21 
Now outputs summary information in comments to the page, except during AJAX processing

# 1.20 
Contains a couple of minor improvements for better analysis of page loading

# 1.19.0307 
This version is tested with WordPress 3.6-alpha-23627

# 1.18.1219 
This version is a standalone version from www.oik-plugins.com

# 1.18.1218 
This version is a standalone version from www.oik-plugins.com

# 1.18 
This version is a standalone version from www.oik-plugins.com
This version matches the child plugin oik-bwtrace in oik v1.17

## Changelog 
# 2.0.9 
* Fixed: Issue #15 - Handle WP_Error from oik_require_lib() in bw_trace_query_plugins
* Tested: Tested with WordPress 4.4 and WordPress MultiSite

# 2.0.8 
* Added: Ad hoc debug backtracing of selected hooks ( github issue 14 )
* Changed: No longer calls oik_register_plugin_server() ( github issue 13 )
* Changed: Updated readme to better reflect ad hoc tracing

# 2.0.7 
* Added: Trace AJAX transactions to a separate file, if defined
* Added: Control tracing of AJAX transactions
* Changed: Improve setting of ABSPATH for Windows
* Changed: Removed unused constants
* Changed: Support trace level parameter on bw_trace()
* Changed: Improve output from bw_trace_included_files()
* Changed: Cater for failure to fopen() in bw_write() - store results for later
* Changed: Delete some previously commented out code
* Changed: bw_trace_the_post() trace level should be ALWAYS
* Fixed: Issue #10 - Ad hoc tracing should allow selection of the hook priority
* Fixed: Issue #11 - Re-enable the logic for the Trace enabled checkbox

# 2.0.6 
* Added: Ad hoc tracing of filter functions for selected hooks ( Issue #3 )
* Added: Ad hoc tracing of parameters to selected hooks ( Issue #2 )
* Added: Ad hoc tracing of the global post object on selected hooks ( Issue #2 )
* Added: Ad hoc tracing of filter results ( Issue #6 )
* Added: Ad hoc tracing should allow selection of the hook priority ( Issue #10 )
* Added: BW_TRACE_VERBOSE ( 64 ) level, which is even more detailed than BW_TRACE_DEBUG ( 32 )
* Changed: Enhanced output from Count action hooks and filters ( Issue #7 )
* Changed: Functions traced should allow OO methods invoking trace ( Issue #8 )
* Fixed: Ensure BW_TRACE_level constants are defined ( Issue #4 )
* Fixed: Fix for problem with symlinked file's drive letters ( similar to TRAC #33265 )
* Fixed: Only trace $errcontext when tracing with BW_TRACE_VERBOSE ( Issue #5 )
* Fixed: Strings containing commas in the trace summary file should be wrapped in quotes ( Issue #9 )

# 2.0.5 
* Added: $level parameter (default BW_TRACE_ALWAYS) to the bw_trace2() and bw_backtrace() APIs
* Added: Select box to choose the level of tracing
* Changed: Some bw_trace2() and bw_backtrace() calls to make use of the level capability
* Fixed: Problem when BW_COUNT_ON defined in wp-config.php

# 2.0.4 
* Changed: Now detects when output buffering is in place and uses an alternative function to print_r()

# 2.0.3 
* Added: bw_trace_error_handler() logic for Error, Warning and Notice type messages
* Added: Add logic for each deprecated action or filter
* Changed: Anonymize path in output from bw_trace_included_files()
* Changed: Updated bw_trace_count_report()

# 2.0.2 
* Changed: Now supports deprecated filters and actions
* Changed: Enabled using "Trace deprecated messages" checkbox

# 2.0.1 
* Changed: libs/bwtrace.php now at 2.0.1
* Changed: libs/oik-lib.php now at 0.0.2
* Changed: libs/oik_boot.php now at 3.0.0
* Changed: libs/bobbfunc.php now at 3.0.0
* Changed: libs/oik-admin.php now at 3.0.0 and uses _bw_c() instead of c()
* Changed: Uses "wp_loaded" to test for shared libraries for admin logic
* Changed: Implements "oik_query_libs" with priority 12, was 10

# 2.0.0 
* Changed: Summary trace record doesn't show trace file name if 0 trace records produced
* Changed: Merged shared libraries with oik-libs v0.0.1
* Changed: Reversioned using semantic versioning, to reflect incompatibility with oik v2.5
* Changed: Must Use plugin also version 2.0.0
* Changed: Some docblock comment updates
* Deleted: Some commented out code removed

# 1.28 
* Added: Shared library files in /libs folder to enable standalone operation
* Added: includes/bwtrace_actions.php to register trace action processing
* Added: option to control inclusion of the current filter ( cf=aaa ) in trace records
* Added: option to control the files loaded count ( F=nnn ) in trace records
* Changed: Admin pages no longer display "oik documentation" or "support oik" boxes
* Changed: Sequence of input fields in the oik trace options page
* Changed: Some functions deleted from libs/bobbfunc.php
* Changed: Some functions renamed in libs/bobbfunc.php
* Changed: Trace options and action options admin pages are only available when "oik-admin" library is available
* Changed: Uses bw_trace_anonymize_symlinked_file() to cater for symlinked files
* Changed: admin/oik-bwaction.inc is now redundant; but not yet deleted.
* Changed: oik_trace_reset_notes() checks for "oik-sc-help" library.
* Deleted: oik_action_summary()
* Fixed: br() does not attempt to translate null text
* Tested: With WordPress and WPMS 4.2 and above.
* Tested: With and without: oik-lib v0.1 and oik v2.6

# 1.27 
* Changed: current filter now shows the full tree. No longer necessary to trace the array separately
* Fixed: Trace function count no longer doubly incremented.
* Added: Option to report the $bw_trace_functions array at shutdown. bw_trace_functions_traced()
* Changed: Sequence of action hooks invoked for 'shutdown': included files, saved queries, output buffer, functions traced, status report
* Changed: Improved more docblock comments and some programming style

# 1.26 
* Changed: Improved activating/de-activating of the oik-bwtrace MU plugin when BW_COUNT_ON is not defined true
* Deprecated: Moved more deprecated functions and logic related to the original action tracing and logging to includes/deprecated.php
* Changed: bw_lazy_trace_config_startup() no longer references BW_ACTIONS_ON nor BW_ACTIONS_RESET; deprecated constants
* Changed: bw_trace_plugin_startup() loads global $bw_action_options regardless of the $bw_trace_options settings.
* Changed: bw_trace_count_plugins_loaded() now uses global $bw_action_options
* Changed: bw_trace_output_buffer() checks the output buffer status

# 1.25 
* Added: includes/deprecated.php for deprecated functions, excluding original immediate action tracing
* Changed: Dependent upon oik v2.6-alpha.0525 for .php versions of include files
* Changed: Deprecated some .inc files in favour of .php files.
* Changed: Moved bw_this_plugin_first() to admin/oik-bwtrace.inc
* Changed: Relabelled 'Count immediate actions' to 'Count action hooks and filters'
* Changed: Added options for specific action tracing functionality that was previously hardcoded
* Changed: bw_summarise_actions() - deprecated; no longer called by bw_action_summary()
* Changed: oik action options page rewrite;
* Changed: removed the slow action trace logic.
* Disabled: Disabled immediate action tracing... the last WordPress version for which this logic was supported was 3.6
* Changed: Improved some docblock comments
* Added: includes/_oik-bwtrace-mu.php - implements the MU version of the plugin
* Changed: Moved bw_lazy_trace_config_startup() to includes/bwtrace-config.php
* Changed: bw_lazy_trace_config_startup() now supports BW_COUNT_ON constant. When true it loads the action hook and filter counting logic and 'activates' the MU version of the plugin
* Changed: oik-bwtrace.php now uses oik_bwtrace_loaded() to perform startup processing

# 1.24 
* Added: records the trace file name in the summary log file
* Added: remote IP address included in the summary log file
* Fixed: Doesn't write HTML comments for JSON or XMLRPC requests
* Fixed: Doesn't write HTML comments for SiteGround cache check: sgCacheCheck
* Changed: Commented out tracing of global wp_rewrite

# 1.23 
* Fixed: Warning $wpdb->elapsed_query_time property missing
* Changed: Documented the fields in the bwtrace.vt.mmdd log file; where vt is simply an abbreviation of value and text

# 1.22 
* Added: Automatically sets SAVEQUERIES if recording the total number of queries performed
* Changed: bw_trace_saved_queries() formats the output in the trace log
* Changed: bw_trace_included_files() formats the output in the trace log
* Fixed: bw_trace_c3() now detects "short" parameter - set for async-upload of a new file
* Added: bwtrace.vt file stores summary of all activity
* Added: trace action counting - with "Count immediate actions" checkbox
* Added: Responds to "plugins_loaded" to start trace count logic
* Added: bw_trace_wp()
* Added: bw_trace_add_shutdown_actions() to defer adding actions responding to "shutdown"

# 1.21 
* Added: Logic originally developed for oik-shortcodes to display summary information at shutdown
* Added: Logic to trace included files: at startup and shutdown
* Added: Logic to trace saved queries: at startup and shutdown
* Added: Trace elapsed time - displayed after current timestamp
* Added: Trace number of included files as F=nnnn
* Changed: bw_lazy_backtrace() handles args which are arrays

# 1.20 
* Fixed: oik action tracing is not invoked if oik action settings have not been defined
* Added: Lists loaded files during shutdown processing
* Changed: trace IP is trimmed on input validation
* Added: Support for action trace output to be written in CSV format
* Added: bw_trace_bwechos()
* Added: tracing will attempt to use obsafe_print_r() instead of print_r()
* Changed: Minor improvement in determining the calling function
* Added: bw_trace_hook_all() for performing immediate action tracing. Makes original solution partially redundant.
* Changed: bw_action_file() - minor performance improvements
* Changed: bw_lazy_trace_action_immediate() also logs $arg3

# 1.19.0307
* Added official support for WordPress 3.5.1
* Added support for  WordPress 3.6-alpha-23627  - there have been some minor comment changes in plugin.php

# 1.18.1219 
* Added support for WordPress 3.5.1-alpha (as of 2012/12/19)

# 1.18.1218.2100
* Fixed: New optional values no longer included in the action log

# 1.18.1218 
* Added: Immediate action tracing helps you discover ALL the actions and filters
* Added: Immediate action tracing automatically replaces wp-includes/plugin.php
* Added: Trace record may includes current and peak memory usage (in bytes)
* Added: Inclusion of post ID and number of queries now optional

# 1.18 
* Added: Code to relocate from part of oik to a separate plugin
* Added: Updates are provided from oik-plugins

# up to 1.16 
* Please see the change log in oik for versions prior to 1.17

## Further reading 
If you want to read more about the oik plugins then please visit the
[oik plugin](http://www.oik-plugins.com/oik)
**"the oik plugin - for often included key-information"**
