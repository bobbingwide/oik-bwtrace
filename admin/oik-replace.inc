<?php // (C) Copyright Bobbing Wide 2012

/**
 * Simple file compare using the md5 hash function
 *
 * @param string $filea - first file name - fully qualified
 * @param string $fileb - second file name - fully qualified
 * @return book  - TRUE when they match exactly
 */
function bw_file_compare( $filea, $fileb ) {
  $match = false;
  $md5a = md5_file( $filea );
  $md5b = md5_file( $fileb );
  //echo "$filea $md5a" . PHP_EOL ;
  //echo "$fileb $md5b" . PHP_EOL ;
  if  ( $md5a === $md5b ) {
    $match = true;
  }  
  return( $match );
}

/** 
 * Return the current file mode permissions then chmod if set
 * @param string $file 
 * @param integer $chmod - decimal integer for the new file mode
 * @return integer - decimal integer for the original file mode 
 *
 * Note: To print the returned value as octal use decoct() 
 */
function bw_chmod( $file, $chmod=null ) {
  $fileperms = fileperms( $file );
  $fp = $fileperms & 0007777;
  if ( $chmod ) { 
    //echo "changing $file to $chmod " . decoct( $chmod ) . PHP_EOL;
    chmod( $file, decoct($chmod) );
  }
  //echo "was $file " .  decoct( $fp) . PHP_EOL;
  return( octdec($fp) ); 
} 

/**
 * Replace the dest file with the source file retaining the original permissions 
 * @param string $source - source file name
 * @param string $dest - target file name. The file must already exist
 * @return bool - result of the copy
 * 
 */
function bw_file_copy( $source, $dest ) {
  $chmod = bw_chmod( $dest, octdec(0777)  );
  $result = copy( $source, $dest );
  $chmod = bw_chmod( $dest, $chmod );
  return( $result );
}  

/**
 * Replace the current file with a slightly modified version
 * 
 * mod vs tgt vs cpy action   process            new 
 * --- -- --- -- --- ------   ---------------    ---
 * A35 <> P35 == P35  enable  copy mod to tgt    A35
 * A35 <> P35 == P35  disable Already disabled   <--
 * A35 == A35 <> P35  enable  Already enabled    <--
 * A35 == A35 <> P35  disable copy cpy to tgt    P35
 *
 * A35 <> P36 <> P35  either  Not allowed        <--
 * 
 * It should not be possible for A35 to match P35 or P36
 * A35 = modified version of plugin.php including Immediate action tracing
 * P35/P36 = WordPress supplied version of wp-includes/plugin.php
 * This file is delivered in oik-bwtrace - the correct file for the WordPress version
 * 
 */
function bw_action_toggle( $enable, $mod, $tgt, $cpy ) {
  if ( $enable ) {    
    $tgtvscpy = bw_file_compare( $tgt, $cpy );
    if ( $tgtvscpy ) {
      $result = bw_file_copy( $mod, $tgt );
    } else { 
      //echo "Either already enabled or not allowed" . PHP_EOL;
      $result = bw_file_compare( $mod, $tgt );
      if ( !$result  ) 
        $result = false; //bw_wp_error( "Attempt to enable but version mismatch" )
    }  
  } else {
    $modvstgt = bw_file_compare( $mod, $tgt );
    if ( $modvstgt ) {
      $result = bw_file_copy( $cpy, $tgt );   
    } else {
      //echo "Either already disabled or not allowed" . PHP_EOL;
      $result = bw_file_compare( $tgt, $cpy );
      if ( !$result ) 
        $result = false; // bw_wp_error( "Attempt to disable but version mismatch" );
    }
  }
  return( $result );
}

/**
 * Enable or disable immediate action trace by replacing plugin.php 
 *
 * @param bool $enable - whether to enable or disable immediate action tracing
 * @return bool $result - true if successful
 * 
 * When enabled the wp-includes/plugin.php is replaced by plugin.act
 * When disabled the wp-includes/plugin.php is reset to original
 * 
 * The logic in bw_action_toggle() does not replace the file if there is a mismatch
 * between the actual version of plugin.php and our local version.
 * 
 */
function bw_enable_action_trace( $enable ) {
  global $wp_version; 
  $result = false; 
  if ( isset( $wp_version ) ) {
    $action_file = oik_path( "wp-includes/$wp_version/plugin.act", "oik-bwtrace" );
    $copy_file = oik_path( "wp-includes/$wp_version/plugin.php", "oik-bwtrace" );
    $target_file = ABSPATH . WPINC . '/plugin.php';
    if ( file_exists( $action_file ) ) {
      $result = bw_action_toggle( $enable, $action_file, $target_file , $copy_file );
    } else {
      bw_trace2( $action_file, "File missing for WordPress version" );
    }
  } else {
    bw_trace2( "WordPress version not set." );
  }
  return( $result ); 
}
