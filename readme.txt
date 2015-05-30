=== oik-bwtrace ===
Contributors: bobbingwide
Donate link: http://www.oik-plugins.com/oik/oik-donate/
Tags: debug, trace, backtrace, actions, filters, immediate trace
Requires at least: 3.9
Tested up to: 4.1.1
Stable tag: 1.22
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==
Debug trace for WordPress. 
* The primary purpose of debug trace is to help in the development of PHP code: plugins or themes.
* The primary purpose of action trace is to help you follow the sequence of events in the server.
* This plugin provides the admin interface to trace functions and action and filter tracing built into plugins that use the oik / bw APIs.

Neither oik bwtrace nor oik action trace alters the output of your web pages.
They write their output to a file on the server which you can browse in a separate window.

You can also use the oik trace facilities to assist in problem determination in a live site.


Features:
* Easy to code APIs: bw_trace2(), bw_backtrace() and bw_trace()
* Tracing can be enabled programmatically
* Does not require WP_DEBUG to be defined
* Dynamically activates trace functions
* Implemented as lazy just-in-time code
* Provides contextual information
* Action trace helps you understand the sequence of actions and filters
* Action trace summary shows invocation counts and execution time
* Can be used during regression testing
* Traces ALL server functionality, including AJAX and other background requests
* Minimum performance overhead when tracing is not enabled
* Can be activated in wp-config.php to trace code before WordPress is fully loaded
* Does not need to be activated if started programmatically or from wp-config.php
* Action tracing can be used to trace known actions or filters
* Immediate action tracing will trace ALL calls to do_action() and apply_filters()
* Immediate action tracing helps you discover ALL the actions and filters 
* Ability to choose the IP address to trace, defaults to ALL requests

The trace record output can include:

* Fully qualified source file name
* Trace record count
* Time stamp
* Current post ID
* Number of database queries that have been performed.
* Current and peak memory usage (in bytes) 


See also:
* bw_trace2() 
* bw_backtrace()
* bw_trace()


== Installation ==
1. Upload the contents of the oik-bwtrace plugin to the `/wp-content/plugins/oik-bwtrace' directory
1. Activate the oik-bwtrace plugin through the 'Plugins' menu in WordPress
1. Define your trace options using Settings > trace options
1. Define your action trace options using Settings > action options

== Frequently Asked Questions ==
= Where is the FAQ? =
[oik FAQ](http://www.oik-plugins.com/oik/oik-faq)

= Is there a support forum? =
Yes - please use the standard WordPress forum - http://wordpress.org/tags/oik?forum_id=10

= Can I get support? = 
Yes - see above 

== Screenshots ==
1. Trace options
2. Action trace summary
3. Raw trace output (fully qualified file names unchecked )
4. Raw action output
 

== Upgrade Notice ==
= 1.22 = 
Now outputs a single summary record for each transaction. Required for oik-plugins.com analysis

= 1.21 =
Now outputs summary information in comments to the page, except during AJAX processing

= 1.20 = 
Contains a couple of minor improvements for better analysis of page loading

= 1.19.0307 = 
This version is tested with WordPress 3.6-alpha-23627

= 1.18.1219 =
This version is a standalone version from www.oik-plugins.com

= 1.18.1218 =
This version is a standalone version from www.oik-plugins.com

= 1.18 =
This version is a standalone version from www.oik-plugins.com
This version matches the child plugin oik-bwtrace in oik v1.17

== Changelog ==
= 1.22 = 
* Added: Automatically sets SAVEQUERIES if recording the total number of queries performed
* Changed: bw_trace_saved_queries() formats the output in the trace log
* Changed: bw_trace_included_files() formats the output in the trace log
* Fixed: bw_trace_c3() now detects "short" parameter - set for async-upload of a new file
* Added: bwtrace.vt file stores summary of all activity
* Added: trace action counting - with "Count immediate actions" checkbox
* Added: Responds to "plugins_loaded" to start trace count logic
* Added: bw_trace_wp()
* Added: bw_trace_add_shutdown_actions() to defer adding actions responding to "shutdown"  

= 1.21 =
* Added: Logic originally developed for oik-shortcodes to display summary information at shutdown
* Added: Logic to trace included files: at startup and shutdown
* Added: Logic to trace saved queries: at startup and shutdown
* Added: Trace elapsed time - displayed after current timestamp
* Added: Trace number of included files as F=nnnn
* Changed: bw_lazy_backtrace() handles args which are arrays

= 1.20 =
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

= 1.19.0307
* Added official support for WordPress 3.5.1
* Added support for  WordPress 3.6-alpha-23627  - there have been some minor comment changes in plugin.php

= 1.18.1219 =
* Added support for WordPress 3.5.1-alpha (as of 2012/12/19)

= 1.18.1218.2100
* Fixed: New optional values no longer included in the action log

= 1.18.1218 =
* Added: Immediate action tracing helps you discover ALL the actions and filters
* Added: Immediate action tracing automatically replaces wp-includes/plugin.php
* Added: Trace record may includes current and peak memory usage (in bytes) 
* Added: Inclusion of post ID and number of queries now optional

= 1.18 =
* Added: Code to relocate from part of oik to a separate plugin
* Added: Updates are provided from oik-plugins

= up to 1.16 = 
* Please see the change log in oik for versions prior to 1.17

== Further reading ==
If you want to read more about the oik plugins then please visit the
[oik plugin](http://www.oik-plugins.com/oik) 
**"the oik plugin - for often included key-information"**

If we want to include bw_trace(), bw_trace2() or bw_backtrace() calls in WordPress core 
then we need to define the functions, so we include bwtrace.inc (which is part of the oik base plugin).

If we want trace and actions to be enabled and reset at WordPress startup then we also need to define these as TRUE
Note: action tracing and resetting will be enabled by default. 

`define( 'BW_TRACE_CONFIG_STARTUP', true );
define( 'BW_TRACE_ON', true );
define( 'BW_ACTIONS_ON', true );
define( 'BW_TRACE_RESET', true );
define( 'BW_ACTIONS_RESET', true );`

require_once( ABSPATH . '/wp-content/plugins/oik/bwtrace.inc' );`

Don't forget to remove this code before deleting the plugin.
