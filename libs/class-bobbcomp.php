<?php // (C) Copyright BobbingWide 2016
if ( !defined( "CLASS_BOBBCOMP_INCLUDED" ) ) {
define( "CLASS_BOBBCOMP_INCLUDED", "3.1.0" );

/**
 * HTML output library functions
 * 
 * Library: class-bobbcomp
 * Depends: 
 * Provides: bobbfunc class
 *
 * These functions are clones of functions in bobbcomp.inc
 * The original functions may be deprecated in the future.
 * To use the new functions prefix the original function call with bobbcomp:: 
 */

class bobbcomp {


	/**
	 * Get the value of an option field
	 *
	 * @param string $field field name within set
	 * @param string $set option name
	 * @return mixed option value
	 */
	static function bw_get_option( $field, $set="bw_options" ) {
		$bw_options = get_option( $set );
		if ( isset( $bw_options[ $field ] ) ) {
			$option = $bw_options[ $field ] ; 
		} else {
			$option = null;
		}  
		// Note: A value that appears to be blank ( == '') may actually be FALSE ( == FALSE )
		// bw_trace( '!' .$option.'!', __FUNCTION__,  __LINE__, __FILE__, "option" );  
		return( $option ); 
	} 

	/** 
	 * Return the array[index] or build the result by calling $callback, passing the $default as the arg.
	 *
	 *
	 * Notes: dcb = deferred callback
	 * Use this function when applying the default might take some time but would be unnecessary if the $array[$index] is already set.
	 *
	 * You can also use this function when the default value is a string that you want to be translated.
	 *
	 * 2012/10/23 - When the parameter was passed as a null value e.g. "" then it was being treated as NULL
	 * hence the default processing took effect. 
	 * In this new verision we replace the NULLs in the code body with $default
	 * So bw_array_get() can return a given NULL value which will then override the default.
	 * In this case, if the parameter that is passed turns out to be the default value then this will also be translated.
	 * Note: It could could still match a default null value
	 * Also: We don't expect a null value for the default callback function __()
	 * 2012/12/04 - we have to allow for the value being set as 0 which differs from a default value of NULL
	 * so the comparison needs to be identical ( === ) rather than equal ( == )
	 * 
	 * 2014/02/27 - In cases where value found may be the same as the default and the dcb function could mess this up
	 * then it's advisable to NOT use this function.
	 *   
	 * @param array $array array from which to obtain the value
	 * @param string $index - index of value to obtain]
	 * @param mixed $default - parameter to the $callback function 
	 * @param string $callback - function name to invoke - defaults to invoking __()
	 */
	static function bw_array_get_dcb( $array = array(), $index, $default = NULL, $callback='__', $text_domain="oik" ) {
		$value = bw_array_get( $array, $index, $default );
		if ( $value === $default ) {
			if ( is_callable( $callback ) ) {
				$value = call_user_func( $callback, $default, $text_domain ); 
			} else {
				bw_backtrace();
			}
		}  
		return( $value );  
	}

}	/* end class */

} /* end if !defined */
