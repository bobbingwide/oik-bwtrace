<?php
if ( !defined( "HEXDUMP_INCLUDED" ) ) {
	define( "HEXDUMP_INCLUDED", "1.0.1" );

	/**
	 * @copyright (C) Bobbing Wide 2013-2023
	 * Hexadecimal dumping functions
	 *
	 * Library: hexdump
	 * Provides: hexdump
	 * Depends:
	 *
	 * Helps you to find those pesky control characters
	 *
	 * I first wrote this code in PHP in Sep 2013. It echoed the output.
	 * Now I need it to be returned as a string, so rewriting as oik_hexdump().
	 * Quite a few people look for something similar. https://stackoverflow.com/questions/1057572/how-can-i-get-a-hex-dump-of-a-string-in-php
	 *
	 */

	/**
	 * Returns the hex dump of the string
	 */
	function oik_hexdump( $string ) {
		$hexdump = null;
		$count   = ( null === $string) ? 0 : strlen( $string );
		$hexdump .= $count;
		$hexdump .= PHP_EOL;
		$lineo   = "";
		$hexo    = "";
		for ( $i = 1; $i <= $count; $i ++ ) {
			$ch = $string[ $i - 1 ];
			if ( ctype_cntrl( $ch ) ) {
				$lineo .= ".";
			} else {
				$lineo .= $ch;
			}
			$hexo .= bin2hex( $ch );
			$hexo .= " ";
			if ( 0 == $i % 20 ) {
				$hexdump .= $lineo . " " . $hexo . PHP_EOL;
				$lineo   = "";
				$hexo    = "";
			}
		}
		$hexdump .= substr( $lineo . str_repeat( ".", 20 ), 0, 20 );
		$hexdump .= " ";
		$hexdump .= $hexo;
		$hexdump .= PHP_EOL;

		return $hexdump;
	}

	/**
	 * Echoes the hex dump of a string
	 * @param $string
	 */
	function hexdump( $string ) {
		echo oik_hexdump( $string );
	}

}





