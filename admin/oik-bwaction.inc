<?php
/*

    Copyright 2012-2014 Bobbing Wide (email : herb@bobbingwide.com )

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License version 2,
    as published by the Free Software Foundation.

    You may NOT assume that you can use any other version of the GPL.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    The license for this software can likely be found here:
    http://www.gnu.org/licenses/gpl-2.0.html

*/

/**
 * Calculate an average
 *
 * If count is not 0 then we can calculate the average
 * 
 * @param number $elapsed
 * @param integer $count
 * @return float - average
 */
function bw_average( $elapsed, $count ) { 
  if ( $count ) {
    $average = $elapsed / $count ;
    $av = sprintf( "%1.6F", $average );
    //c( $average );
    //c( $av );
  } else {
    $av = $elapsed;
  }     
  return( $av );
} 

if ( !function_exists( "bw_array_add" ) ) { 

function bw_array_add( &$array, $index, $amount ) {
  if ( ! isset($array[$index]) ) {
    $value = $amount;
  } else {
    $value = $array[$index] + $amount;
  }
  return( $value );  
}
}

function _bw_time( $ts, $msec ) {
  $time = strtotime( $ts );
  return( "$time $msec" );
}

function _bw_time_diff( $endtime, $starttime ) {

  list( $su, $ss ) = explode(" ", $starttime );
  list( $eu, $es ) = explode(" ", $endtime );
  $elapsedsec  = $es - $ss;
  $elapsedu = $eu - $su;
  
  $elapsed = $elapsedsec + $elapsedu;
  
  //e( $elapsedsec );
  //e( $elapsedu );
  //e( $elapsed );
  return( $elapsed );  
}

function bw_action_start_time( $action, $ts, $msec ) {
  global $actions_start;
  $actions_start[ $action ] = _bw_time( $ts, $msec ); 
}

function bw_action_end_time( $action, $ts, $msec ) {
  $endtime = _bw_time( $ts, $msec );
  global $actions_start, $actions_elapsed;
  $starttime = bw_array_get( $actions_start, $action, $endtime );
  $elapsed = _bw_time_diff( $endtime, $starttime );
  //echo "$endtime $starttime $elapsed \n";
  $actions_elapsed[ $action ] = bw_array_add( $actions_elapsed, $action, $elapsed );
  
}


/** 
 * Handle a start or end action
 * 
 * The input data is expected to be in the following format:
 * $ts - made from data[0] and data[1] - it doesn't matter if there's no space between dd and hh
 * $msec - microsecs 
 * $storend = start '<S' or end 'E>' or "<I" or "I>" - for "Immediate invocation"
 * $count = trace action count ( it may get reset )
 * $action = action name - extracted from cf=action name
 * where cf stands for "current filter"
 * If there is no current filter the value is 
 * $data[6] = function: 'apply_filters' or 'do_action'
 * $data[7] = filter/action: the 'immediate' filter or action being applied
   
 
2012-03-27 11:00:37 0.49518200 <S 607 cf=shutdown
2012-03-27 11:00:37 0.49676500 E> 608 cf=shutdown

The version 1.17 code now includes "immediate" actions which are created by bw_lazy_trace_action_immediate(),
which is called by manually added bw_trace_action_immediate() calls in wp-includes/plugins.php 

Sample output: 

2012-10-04 14:10:20 0.03808400 <I 909 cf! apply_filters pre_option_blog_charset
2012-10-04 14:10:20 0.03973400 <I 911 cf! apply_filters option_blog_charset
2012-10-04 14:10:20 0.04081100 <S 912 cf=option_blog_charset 
2012-10-04 14:10:20 0.04248700 E> 914 cf=option_blog_charset 
2012-10-04 14:10:20 0.04394300 I> 916 cf! apply_filters option_blog_charset
2012-10-04 14:10:20 0.04776700 <I 918 cf! apply_filters gettext
2012-10-04 14:10:20 0.04914900 <I 920 cf! apply_filters gettext

Before After   Means
-----  -----   ---------
<I     <I      The before invocation returned without calling a filter or function
<I     I>      The filter has ended normally **?** - but our interceptor wasn't there 
I>     <I      one filter has ended, another has started
<I     <S      this should indicate that our action filter is working... it means that a user defined function is going to be invoked...
               so why don't we log what these are? 
               
<S     E>      end of our action filter
>E     E>      end of another action filter
etc...

The code to analyze the performance is not yet complete. But it's good enough to enable preliminary performance investigations.
Perhaps we could keep a count of the number of SQL queries that have been performed to date! 



*/ 

function bw_handle_action( $data ) {
  global $actions;
  $ts = $data[0] . $data[1];
  $msec = $data[2];
  $storend = $data[3];
  $count = $data[4];
  $action = substr( $data[5], 3 ) ;
  if ( $storend == "<S" ) {
    bw_array_inc( $actions, $action );
    bw_action_start_time( $action, $ts, $msec );
  } elseif ( $storend == "E>" ) {
    bw_action_end_time( $action, $ts, $msec );
  } else {
    $action = $data[7];
    if ( !$action ) { 
      bw_backtrace(); 
      bw_trace2(); 
      //gobang(); 
    } else {
      bw_array_inc( $actions, $action );
      bw_action_start_time( $action, $ts, $msec );
      bw_action_end_time( $action, $ts, $msec );
    }  
  }
}


function bw_format_action_row( $action, $count, $elapsed, $average ) {
  $elapsed = sprintf( "%.6f", $elapsed );
  $average = sprintf( "%.6f", $average );
  $tr = array( $action, $count, $elapsed, $average );
  bw_tablerow( $tr ); 
  //echo $action;
}

function bw_format_action_csv( $action, $count, $elapsed, $average ) {
  $elapsed = sprintf( "%.6f", $elapsed );
  $average = sprintf( "%.6f", $average );
  $tr = array( $action, $count, $elapsed, $average );
  $line = implode( $tr, "," );
  echo $line;
  echo PHP_EOL;
  //echo $action;
}



function bw_summarise_actions( $file="bwaction.loh", $html=true ) {
  $target = "bwaction.cpy" ;
  //$res = copy( $file, $target ); 
  //echo "copy $res\n";
  
  $handle = fopen( $target, "r" );
  // echo $handle;
  while (($data = fgetcsv( $handle, 1000, " ")) !== FALSE) {
    bw_handle_action( $data );
  }
  fclose($handle);
  
  global $actions, $actions_elapsed;
  if ( count( $actions ) ) {
     
     foreach ( $actions as $action => $count ) {
       $elapsed = bw_array_get( $actions_elapsed, $action, 0 );
       $average = bw_average( $elapsed, $count );
       if ( $html ) {
         bw_format_action_row( $action, $count, $elapsed, $average ); 
       } else {
         bw_format_action_csv( $action, $count, $elapsed, $average );  
       }
     }  
  }
}

/** 
 * Display a summary of actions from the most recent action file 
 *
*/ 
function bw_action_summary() {
  oik_require2( "includes/oik-bwtrace.inc", "oik-bwtrace" );
  $file = bw_action_file();
  if ( $file ) {
    stag( "table", "form-table" );
    stag( "thead" ); 
    stag( "tr" );
    th( "Action" );
    th( "Invocations" );
    th( "Elapsed" );
    th( "Average" );
    etag( "tr" );
    etag( "thead" );
    stag( "tbody" );
    
    bw_summarise_actions( $file );
    etag( "tbody" );
    etag( "table" );
    bw_action_reset_form();
  }
}



/**
 * Create the Action reset button for use somewhere in any page
 */
function bw_action_reset_form() {
  oik_require( "bobbforms.inc" );
  e( '<form method="post" action="" class="bw_inline">' ); 
  e( "<input type=\"submit\" name=\"_bw_action_reset\" value=\"Action reset\" />" ); 
  etag( "form" );
}


