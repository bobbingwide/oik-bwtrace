# oik-bwtrace 
![banner](https://raw.githubusercontent.com/bobbingwide/oik-bwtrace/master/assets/oik-bwtrace-banner-772x250.jpg)
* Contributors: bobbingwide, vsgloik
* Donate link: https://www.oik-plugins.com/oik/oik-donate/
* Tags: debug, trace, backtrace, actions, filters, ad hoc tracing, hook tracing, filter tracing, string watch
* Requires at least: 5.0
* Tested up to: 5.7
* Gutenberg compatible: Yes
* Requires PHP: 5.6
* Stable tag: 3.2.1
* License: GPLv2 or later
* License URI: http://www.gnu.org/licenses/gpl-2.0.html

## Description 
Debug trace for WordPress, including ad hoc action hook and filter tracing.

* The primary purpose of debug trace is to help in the development of PHP code: plugins or themes.
* The primary purpose of action trace is to help you understand the sequence of events in the server.
* The primary purpose of ad hoc tracing is to let you see what's happening without changing any code.
* The primary purpose of 'string watch' is to track the source of some output.
* This plugin provides the admin interface to trace functions and methods and action and filter tracing.

Except for HTML comments, oik bwtrace does not alter the output of your web pages.


You can also use the oik trace facilities to assist in problem determination in a live site.
Output is written to files on the server in a user defined trace files directory.
When tracing a live or staging site you should ensure that the trace files directory is not publicly accessible.


Features:

* Traces to a defined Trace files directory
* Traces ALL server functionality, including AJAX, REST and other background requests
* Writes summary trace record for each transaction into Daily Trace summary files
* Traces browser transactions separately, if required
* Traces AJAX transactions separately, if required
* Traces REST requests separately, if required
* Traces CLI requests separately, if required
* Ability to choose the IP address to trace, defaults to ALL requests
* Supports ad hoc tracing of user defined hooks
* Action trace counts help you understand the sequence of actions and filters
* Provides contextual information
* Minimum performance overhead when tracing is not enabled
* Tracing can be enabled programmatically
* Traces and backtraces PHP Error, Warning and Notice messages
* Backtraces deprecated logic messages
* Does not require WP_DEBUG to be defined
* Does not require SAVEQUERIES to be defined
* Dynamically activates trace functions
* Implemented as lazy just-in-time code
* Can be used during regression testing
* Can be activated in wp-config.php and db.php to trace code before WordPress is fully loaded
* Plugin does not need to be activated if started programmatically or from wp-config.php
* Operates as a standalone plugin
* Integrated with oik-lib shared library management
* Easy to code APIs: bw_trace2(), bw_backtrace().
* API supports multiple trace levels

Ad hoc tracing allows you to:

* trace parameters
* trace results
* trace registered functions
* trace the global post
* set the priority for the trace hook
* debug backtrace hook invocation
* perform 'string watch' to watch for a particular string


The trace record output can include:

* Fully qualified source file name
* Trace record count and trace error count
* Time stamp
* Current filter information
* Hook count
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

New in v3.2.0
* Daily trace summary records contains the http response code

New in v3.0.0

* All trace files are written within a user defined Trace files directory.
* Tracing will not be activated if the Trace files directory is not specified or is not valid.
* Supports purging of trace files from the Dashboard using a defined retention period.
* The Daily Trace Summary file base name is now user defined.
* Separated Trace 'shutdown' status report from Daily Trace summary.
* Improvements to the [bwtrace] shortcode; moved from the oik plugin.
* Improved support for tracing REST requests.
* Added support for WP-CLI; initial prototype of 'wp trace' command.
* Supports trace file generation logic for tracing parallel requests.
* Improved support for tracing REST requests, with early detection of REST API calls
* Fixes to logic broken by WordPress 4.7
* Tested: With Gutenberg
* Tested: With PHP 7.2 thru' 7.4
* Tested: With WordPress 5.3 and WordPress Multi Site


See also:

* [bw_trace2()](https://www.oik-plugins.com/oik_api/bw_trace2)
* [bw_backtrace()](https://www.oik-plugins.com/oik_api/bw_backtrace)


## Installation 
1. Upload the contents of the oik-bwtrace plugin to the `/wp-content/plugins/oik-bwtrace' directory
1. Activate the oik-bwtrace plugin through the 'Plugins' menu in WordPress
1. Define your trace options using Settings > oik trace options
1. Define your action trace options using Settings > oik action options
1. Don't forget to disable tracing when you no longer need it
1. Don't forget to purge the trace output when you no longer need it

## Frequently Asked Questions 

# Where is the FAQ? 
[oik-bwtrace FAQ](https://www.oik-plugins.com/oik-plugins/oik-bwtrace-debug-trace-for-wordpress/?oik-tab=faq)

# Where is the documentation? 
[oik-bwtrace - debug trace for WordPress](https://www.oik-plugins.com/wordpress-plugins-from-oik-plugins/free-oik-plugins/oik-trace-plugin/)

## Is there a tutorial? 
See this page and short video
[Introduction to oik-bwtrace](https://www.oik-plugins.com/wordpress-plugins-from-oik-plugins/free-oik-plugins/oik-trace-plugin/an-introduction-to-problem-determination-with-oik-bwtrace-debug-trace-for-wordpress)

# Can I get support? 
Use the contact form on the oik-plugins website.

# How can I contribute? 
https://github.com/bobbingwide/oik-bwtrace

## Screenshots 
1. Trace files directory box
2. Trace options - Requests sections
3. Trace options - Trace records
4. Daily Trace Summary box
5. Action options - Options
6. Action options - Ad hoc tracing
7. Trace information box
8. Raw trace output
9. Daily Trace Summary file

## Upgrade Notice 
# 3.2.1 
Tested with PHP 8.0

# 3.2.0 
Update for http response code in the daily trace summary records.

# 3.1.0 
Update for improved reporting of saved queries.
Tested with WordPress 5.6-beta4 and PHP 7.4.

# 3.0.0 
Major update to support tracing of browser requests, AJAX, REST and CLI to a trace files directory.
Tested with WordPress 5.3, PHP 7.3 and PHP 7.4.

# 3.0.0-RC2 
Many more issues fixed. Tested with WordPress 5.3

# 3.0.0-RC1 
Upgrade for PHP 7.3 support

# 3.0.0-beta-20190404 
One-off package for EBPS - the British Fern Society

# 3.0.0-alpha-20181009 
Upgrade for better reporting of client IP addresses

# 3.0.0-alpha-20180525 
Fixes for problems detected in v3.0.0-alpha-20108054

# 3.0.0-alpha-20108054 
Fixes for problems detected in v3.0.0-alpha-20180523

# 3.0.0-alpha-20180523 
Now uses a Trace files directory for storing trace output files.

# 2.2.0-alpha-20180424 
Update for improved support for tracing REST requests and the WP-CLI trace command.

# 2.1.1 
Upgrade for PHP 7.2 support

# 2.1.1-beta-20171023 
Upgrade to ensure shared library file compatibility with oik v3.2.0-RC1

# 2.1.1-alpha.20170303 
Version used to attempt to detect changes to the PHP_SAPI constant.

# 2.1.1-alpha.1124
Started adding an Information section to help assist problem determination

# 2.1.0 
Tested with WordPress 4.7-RC1 and WPMS. No longer requires PHP 5.3 or higher.

# 2.0.12 
Now includes prototype 'string watch' capability. Tested with WordPress 4.5.2 and WordPress MultiSite

# 2.0.11 
Upgrade for additional fields in the daily summary log.

# 2.0.10 
Fixes problem where tracing was being performed when the specific IP did not match

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

# 3.2.1 
* Changed: Set EOL date for PHP 8.0 to 2023/11/26,https://github.com/bobbingwide/oik-bwtrace/issues/98
* Changed: Support PHP 8.0,https://github.com/bobbingwide/oik-bwtrace/issues/98
* Changed: Update language files
* Fixed: Avoid deprecated messages from PHP 8.0,https://github.com/bobbingwide/oik-bwtrace/issues/98
* Fixed: Don't call attempt_reset when there's no trace file selector instance,https://github.com/bobbingwide/oik-bwtrace/issues/99
* Tested: With PHP 8.0
* Tested: With PHPUnit 9
* Tested: With WordPress 5.7 and WordPress Multi Site

# 3.2.0 
* Added: Add http_response_code output to daily trace summary record,https://github.com/bobbingwide/oik-bwtrace/issues/96
* Changed: Set trace admin block width to 100%
* Changed: Updated some shared library files
* Fixed: Attempt to avoid out of memory tracing large objects,https://github.com/bobbingwide/oik-bwtrace/issues/97
* Fixed: In bw_lazy_log() check if the $value is scalar first; avoid calling bw_trace_print_r(),https://github.com/bobbingwide/oik-bwtrace/issues/97
* Tested: With WordPress 5.6 and WordPress Multi Site
* Tested: With Gutenberg 9.8.2
* Tested: With PHP 7.4

# 3.1.0 
* Added: Add saved queries report ordered by longest query time,https://github.com/bobbingwide/oik-bwtrace/issues/93
* Added: Add saved queries report grouped by calling function,https://github.com/bobbingwide/oik-bwtrace/issues/93
* Changed: Updated saved queries report to make it easier to load into a spreadsheet,https://github.com/bobbingwide/oik-bwtrace/issues/93
* Fixed: Trace 'shutdown' status report causes Gutenberg's Site Editor (beta) to fail,https://github.com/bobbingwide/oik-bwtrace/issues/92
* Fixed: Avoid tracing enormous objects with little value. Use wp_json_encode to see what the $result actually contains,https://github.com/bobbingwide/oik-bwtrace/issues/88
* Changed: Update shared library for plugin updates,https://github.com/bobbingwide/oik-bwtrace/issues/89
* Fixed: Trace fails if you don't complete a file name but check the Enabled checkbox,https://github.com/bobbingwide/oik-bwtrace/issues/85
* Tested: With WordPress 5.6-beta4 and WordPress Multi Site
* Tested: With Gutenberg 9.3.0
* Tested: With PHP 7.4

# 3.0.0 
* Changed: Reintroduced logic to only trace a specific IP,https://github.com/bobbingwide/oik-bwtrace/issues/17
* Changed: Remove test on $_REQUEST['wc-ajax'] now that trace file generation limit logic's available,https://github.com/bobbingwide/oik-bwtrace/issues/49
* Changed: Document Hook count in the Daily Trace Summary file,https://github.com/bobbingwide/oik-bwtrace/issues/53
* Changed: Don't count BW_TRACE_INFO as a Trace error,https://github.com/bobbingwide/oik-bwtrace/issues/73
* Changed: Support PHP 7.4,https://github.com/bobbingwide/oik-bwtrace/issues/81

# 3.0.0-RC2 
* Changed: Tidy up bw_trace_http_user_agent,https://github.com/bobbingwide/oik-bwtrace/issues/22
* Added: Hook count in daily trace summary and trace records,https://github.com/bobbingwide/oik-bwtrace/issues/53
* Fixed: check for edd_action in bw_trace_oik_to_echo,https://github.com/bobbingwide/oik-bwtrace/issues/41
* Changed: Eliminate gob() - gobang - calls,https://github.com/bobbingwide/oik-bwtrace/issues/70
* Changed: Add button to purge daily trace summary files separately from other trace files,https://github.com/bobbingwide/oik-bwtrace/issues/71
* Changed: Remove anything to do with bwtron and bwtroff,https://github.com/bobbingwide/oik-bwtrace/issues/71
* Changed: Further changes to fields traced at startup,https://github.com/bobbingwide/oik-bwtrace/issues/72
* Changed: Add option to purge the trace file if no errors traced,https://github.com/bobbingwide/oik-bwtrace/issues/73
* Changed: Change bw_trace2()'s to make them BW_TRACE_ERROR level,https://github.com/bobbingwide/oik-bwtrace/issues/73
* Changed: Check count( $handlers ) when reporting active output buffering handlers,https://github.com/bobbingwide/oik-bwtrace/issues/74
* Fixed: Don't append Trace Summary when Wordfence is downloading .htaccess,https://github.com/bobbingwide/oik-bwtrace/issues/75
* Fixed: Add hex dump support for trace output wth helper function bw_trace_hexdump,https://github.com/bobbingwide/oik-bwtrace/issues/76
* Fixed: Don't echo trace summary for health check,https://github.com/bobbingwide/oik-bwtrace/issues/77
* Changed: Don't write trace status report when request is edd-api,https://github.com/bobbingwide/oik-bwtrace/issues/78
* Fixed: Add tests for PHP 7.3 end-of-life and zlib.output_compression,https://github.com/bobbingwide/oik-bwtrace/issues/79
* Fixed: Tested under PHPUnit 8,https://github.com/bobbingwide/oik-bwtrace/issues/80
* Tested: Add PHPUnit test for bw_trace_inspect_current,https://github.com/bobbingwide/oik-bwtrace/issues/63
* Tested: With WordPress 5.3 and WordPress Multi Site
* Tested: With PHP 7.3
* Tested: With PHPUnit 8

# 3.0.0-RC1 
* Changed: No longer register [bwtron] and [bwtroff] shortcodes,https://github.com/bobbingwide/oik-bwtrace/issues/71
* Changed: Ignore any files in tests\bwtrace
* Changed: Add end of life date for PHP 7.3 and prepare for 7.4,https://github.com/bobbingwide/oik-bwtrace/issues/79
* Fixed: Don't write trace status report when ...,https://github.com/bobbingwide/oik-bwtrace/issues/78
* Changed: Update language files,https://github.com/bobbingwide/oik-bwtrace/issues/69
* Changed: Simplify [bwtrace] shortcode,https://github.com/bobbingwide/oik-bwtrace/issues/69
* Changed: Adjust tests for bwtrace shortcode,https://github.com/bobbingwide/oik-bwtrace/issues/69
* Changed: Delete deprecated functions from deprecated.php,https://github.com/bobbingwide/oik-bwtrace/issues/69
* Changed: Update oik_trace_notes, don't call oik_trace_reset_notes,https://github.com/bobbingwide/oik-bwtrace/issues/71
* Changed: Start increasing robustness of bw_trace_log
* Changed: Correct class trace_file_selector tests,https://github.com/bobbingwide/oik-bwtrace/issues/16
* Tested: Incomplete object class tracing tested elsewhere,https://github.com/bobbingwide/oik-bwtrace/issues/56
* Tested: Update tests for trace options page,https://github.com/bobbingwide/oik-bwtrace/issues/71

# 3.0.0-beta-20190404 
* Fixed: Don't write trace summary for site health check,https://github.com/bobbingwide/oik-bwtrace/issues/77
* Changed: Update bobbforms library file and adjust tests accordingly
* Fixed: Ensure includes\oik-actions.php loaded for bwtrace_get_remote_addr call,https://github.com/bobbingwide/oik-bwtrace/issues/71

# 3.0.0-alpha-20181009 
* Changed: Improve logic to get the remote IP address https://github.com/bobbingwide/oik-bwtrace/issues/71
* Changed: Check trace levels before tracing $_SERVER https://github.com/bobbingwide/oik-bwtrace/issues/72
* Changed: Include list of output buffer handlers in bw_trace_print_r https://github.com/bobbingwide/oik-bwtrace/issues/74

# 3.0.0-alpha-20180525 
* Fixed: Problems noted with v3.0.0-alpha-20180524 https://github.com/bobbingwide/oik-bwtrace/issues/71
* Changed: Reconciled shared libraries; updating version numbers

# 3.0.0-alpha-20180524 
* Fixed: Problems noted with v3.0.0-alpha-20180523 https://github.com/bobbingwide/oik-bwtrace/issues/71

# 3.0.0-alpha-20180523 
* Added: Trace files Purge capability with defined retention period https://github.com/bobbingwide/oik-bwtrace/issues/71
* Added: Trace files directory required before any tracing can be performed https://github.com/bobbingwide/oik-bwtrace/issues/71
* Changed: Improvements to the Daily Trace Summary report https://github.com/bobbingwide/oik-bwtrace/issues/68
* Changed: Move shortcodes from oik and add bwtrace option=logs https://github.com/bobbingwide/oik-bwtrace/issues/69
* Fixed: Cater for Linux file name case sensitivity on includes/class-BW-trace-controller.php
* Fixed: Fix trace file reset when $this->limit is null https://github.com/bobbingwide/oik-bwtrace/issues/16
* Tested: With Gutenberg 2.9.0
* Tested: With PHP 7.1 and 7.2
* Tested: With WordPress 4.9.6 and 5.0-alpha and WordPress Multisite

# 2.2.0-alpha-20180424 
* Added: WP-CLI support https://github.com/bobbingwide/oik-bwtrace/issues/54
* Added: Support trace limits by request type https://github.com/bobbingwide/oik-bwtrace/issues/16
* Changed: Change trace of SAVEQUERIES to verbose https://github.com/bobbingwide/oik-bwtrace/issues/34
* Changed: Improve support for tracing REST requests https://github.com/bobbingwide/oik-bwtrace/issues/67
* Changed: Improved Daily Trace Summary logic https://github.com/bobbingwide/oik-bwtrace/issues/68
* Changed: Pre-detect REST API calls https://github.com/bobbingwide/oik-bwtrace/issues/52
* Fixed: bw_trace_get_attached_hook_count not working since WordPress 4.7 https://github.com/bobbingwide/oik-bwtrace/issues/66
* Fixed: bw_trace_inspect_current no longer determines the current priority https://github.com/bobbingwide/oik-bwtrace/issues/63
* Tested: With Gutenberg 2.7.0
* Tested: With PHP 7.1 and 7.2
* Tested: With WordPress 4.9.5 and 5.0-alpha and WordPress Multsite

# 2.1.1 
* Added: Display information related to WPMS installations https://github.com/bobbingwide/oik-bwtrace/issues/51
* Changed: 100% translatable and localizable on wordpress.org https://github.com/bobbingwide/oik-bwtrace/issues/60
* Changed: Convert php.net URLs into links https://github.com/bobbingwide/oik-bwtrace/issues/51
* Changed: Display End of Life for PHP 7.2 https://github.com/bobbingwide/oik-bwtrace/issues/55
* Changed: First pass at tracing $_GET and $_POST as well as $_REQUEST https://github.com/bobbingwide/oik-bwtrace/issues/61
* Changed: Fix Warning: Count() messages for PHP 7.2
* Changed: Logic to watch for a constant changing https://github.com/bobbingwide/oik-bwtrace/issues/57
* Changed: Test bw_invoke_shortcode exists even if oik-sc-help loaded https://github.com/bobbingwide/oik-bwtrace/issues/62
* Tested: With WordPress 4.9.1 and WordPress Multisite
* Tested: With PHP 7.1 and 7.2

# 2.1.1-beta-20171023 
* Changed: Synchronized shared libraries with oik v3.2.0-RC1,
* Changed: Regenerate language files for en_GB and bb_BB
* Added: bw_trace_all_attached_hooks() for when problem determination gets serious
* Tested: With WordPress 4.8.2 and 4.9-beta3

# 2.1.1-alpha.20170303 
* Added: Logic to limit tracing to CLI processing https://github.com/bobbingwide/oik-bwtrace/issues/58
* Changed: Logic to detect changes to a constant ( e.g. PHP_SAPI ) https://github.com/bobbingwide/oik-bwtrace/issues/57
* Changed: Further analysis in oik_yourehavingmeon()
* Fixed: Catchable fatal error when tracing __PHP_Incomplete_Class https://github.com/bobbingwide/oik-bwtrace/issues/56
* Changed: Reduce messages produced by bw_trace_reset() https://github.com/bobbingwide/oik-bwtrace/issues/46

# 2.1.1-alpha.1124 
* Changed: Improve handling of temporary (random?) problems with file_exists()
* Added: Add an Information section to improve problem determination https://github.com/bobbingwide/oik-bwtrace/issues/51
* Added: Include output_buffering and implicit_flush, EOL for PHP 7.1 https://github.com/bobbingwide/oik-bwtrace/issues/51

# 2.1.0 
* Changed: Add logging library functions under bw_log https://github.com/bobbingwide/oik-bwtrace/issues/50
* Changed: Cater for REST API v2 https://github.com/bobbingwide/oik-bwtrace/issues/42
* Changed: Do not enqueue jQuery when DOING_AJAX https://github.com/bobbingwide/oik-bwtrace/issues/47
* Changed: Improve bw_trace_error_handler output https://github.com/bobbingwide/oik-bwtrace/issues/44
* Changed: Make bw_trace_reset_status() more context sensitive https://github.com/bobbingwide/oik-bwtrace/issues/49
* Changed: Part 1 - include yyyy in the file name https://github.com/bobbingwide/oik-bwtrace/issues/45
* Changed: Reconcile shared libraries with oik v3.1.0
* Changed: Reduce messages produced by trace reset https://github.com/bobbingwide/oik-bwtrace/issues/46
* Fixed: trace shutdown sometimes can't find all the functions it needs; Wrong type of slash https://github.com/bobbingwide/oik-bwtrace/issues/43
* Tested: With WordPress 4.7-RC1

# 2.0.12 
* Added: 'String watch' capability https://github.com/bobbingwide/oik-bwtrace/issues/36
* Changed: Support tracing of nested Closures in bw_trace_obsafe_print_r() https://github.com/bobbingwide/oik-bwtrace/issues/28
* Changed: Improve output for saved queries https://github.com/bobbingwide/oik-bwtrace/issues/29
*	Changed: Improve formatting of included files https://github.com/bobbingwide/oik-bwtrace/issues/32
*	Changed: Improve formatting of hooks https://github.com/bobbingwide/oik-bwtrace/issues/33
*	Changed: Blessed task - reduce trace output produced https://github.com/bobbingwide/oik-bwtrace/issues/34
*	Changed: Add attached functions to hook shortcode https://github.com/bobbingwide/oik-bwtrace/issues/35
* Changed: Trace real memory usage https://github.com/bobbingwide/oik-bwtrace/issues/40
* Changed: Sync shared libraries with oik and oik-libs
* Fixed: Don't call undefined c()  function from bw_trace_c3() https://github.com/bobbingwide/oik-bwtrace/issues/37
* Fixed: bw_trace_c3() should not produce comments after "load-async-upload.php" action https://github.com/bobbingwide/oik-bwtrace/issues/38
* Fixed: Avoid Notice when $GLOBALS['id'] is not just a post ID https://github.com/bobbingwide/oik-bwtrace/issues/39
* Tested: With WordPress 4.5.2 and WordPress MultiSite

# 2.0.11 
* Added: github issue 21 - Show intentions to work on issue - though not yet implemented
* Fixed: github issue 19 - don't use timer_stop()
* Fixed: github issue 20 - don't write trace summary output to robots.txt
* Fixed: github issue 22 - Record additional information in the daily trace summary report
* Fixed: github issue 23 - Support tracing of Closures and protected fields in bw_trace_obsafe_print_r()
* Fixed: github issue 24 - Update bw_trace_inspect_current() with better solution
* Fixed: github issue 25 - Uncaught Error: Call to undefined function bw_invoke_shortcode()
* Tested: up to WordPress 4.4.2 and WordPress MultiSite

# 2.0.10 
* Fixed: Tracing being performed when the specific IP does not match. github issues 17

# 2.0.9 
* Fixed: Issue github issue 15 - Handle WP_Error from oik_require_lib() in bw_trace_query_plugins
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
[oik plugin](https://www.oik-plugins.com/oik)
**"the oik plugin - for often included key-information"**


