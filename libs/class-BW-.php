<?php // (C) Copyright BobbingWide 2017-2023
if ( !defined( "CLASS_BW__INCLUDED" ) ) {
define( "CLASS_BW__INCLUDED", "3.3.2" );

/**
 * More HTML output library functions
 * 
 * Library: class-BW-
 * Depends: bobbfunc, oik-admin, oik-l10n
 * Provides: BW_ class
 *
 * These functions are clones of functions in libs\bobbfunc.php
 * The original functions may be deprecated in the future.
 * To use the new functions prefix the original function call with BW_::
 * and change any translatable string parameter to a translated string
 * 
 * e.g. 
 * `p( "This page left intentionally blank" );`
 *
 * becomes 
 *
 * `BW_::p( __( "This page left intentionally blank", "oik" ) );`
 */

class BW_ {

	/**
	 * Outputs a paragraph of translated text
	 *
	 * @param string $text - translated text - expected to be non-null
	 * @param string $class - CSS class(es)
	 * @param string $id - CSS ID
	 */
	static function p( $text=null, $class=null, $id=null ) {
		sp( $class, $id );
		if ( !is_null( $text ) ) {
			e( $text );
		}
		etag( "p" );
	}

	/**
	 * Outputs a link
	 * 
	 * _alink() and alink() both map to BW_::alink()
	 *
	 * @param string|null $class optional CSS class(es)
	 * @param string $url URL
	 * @param string $linktori translated link text or image 
	 * @param string $alt translated alternate text	or null
	 * @param string $id optional CSS id
	 * @param string $extra additional HTML
	 */
	static function alink( $class, $url, $linktori=null, $alt=null, $id=null, $extra=null ) {
		$link = retlink( $class, $url, $linktori, $alt, $id, $extra );
		e( $link );
	}

	/**
	 * Outputs a menu header
	 *
	 * Note: Completely removed the link to oik  
	 *
	 * @TODO Remove the nonce if #41387 is implemented and we don't still don't want the status of the post boxes to persist.
	 *
	 * @param string $title - title for the box
	 * @param string $class - class for the box 
	 */
	static function oik_menu_header( $title="Overview", $class="w70pc" ) {
		oik_enqueue_scripts();
		e( wp_nonce_field( "closedpostboxes", "closedpostboxesnonce", false, false ) );
		sdiv( "wrap" ); 
		h2( $title ); 
		scolumn( $class );
	}

	/**
	 * Outputs a postbox widget on the admin pages 
	 *
	 * @param string $class additional CSS classes for the postbox
	 * @param string $id Unique CSS ID
	 * @param string $title Translated title
	 * @param string $callback Callable function implementing the post box contents
	 */
	static function oik_box( $class=null, $id=null, $title=null, $callback='oik_callback' ) {
		if ( $id == null ) {
			if ( is_array( $callback ) ) {
				$id = $callback[1];
			} else {
				$id = $callback;
			}
		}  
		sdiv( "postbox $class", $id );
		self::oik_handlediv( $title );
		h3( $title, "hndle" );
		sdiv( "inside" );
		call_user_func( $callback );
		ediv( "inside" );
		ediv( "postbox" );
	}

	/**
	 * Displays the toggle button for the postbox
	 * 
	 * @param string $title - translated title
	 */
	static function oik_handlediv( $title ) {
	    /* translators: %s: panel title */
		$title = sprintf( __( 'Toggle panel: %s', null ), $title );
		e( '<button type="button" class="handlediv" aria-expanded="true">' );
		e( '<span class="screen-reader-text">' . $title . '</span>' );
		e( '<span class="toggle-indicator" aria-hidden="true">' );
		e( '</span>' );
		e( '</button>' );
	}

	/**
	 * Appends some non-translatable text to translated text
	 *
	 * This is similar to using sprintf( __( "translatable_text %1$s", $non_translatable_text ) );
	 * BUT it doesn't require the translator to have to worry about the position of the variable
	 * AS this isn't in the text they translate.
	 *
	 * Note: The non-translatable text is expected to begin with a space character.
	 * It is also possible to append two translated strings.
	 * 
	 * @param string $translated_text - text that's been translated
	 * @param string $non_translatable_text - text that's not translated
	 * @return string concatenated strings
	 */
	static function bwtnt( $translated_text, $non_translatable_text ) {
		$tnt = $translated_text;
		$tnt .= $non_translatable_text;
		return( $tnt );
	}

	/**
	 * Create a field label
	 *
	 * Expects the text to have already been translated. 
	 * 
	 * @param string $name - field name
	 * @param string $text - label to display
	 * @return string - the HTML label tag
	 */
	static function label( $name, $text ) {
		$lab = "<label for=\"";
		$lab.= $name;
		$lab.= "\">";
		$lab.= $text;
		$lab.= "</label>";
		return $lab;
	}

	/**
	 * Form a text field
	 *
	 * @param string $name - the name of the field
	 * @param integer $len - the length of the field
	 * @param string $text - the label for the field
	 * @param string $value - the value of the field
	 * @param string $class - CSS class name
	 * @param string $extras - the extras passed to itext() is expected to be a string
	 * @param array $args - even more options
	 * @TODO accept array so that #hint can be applied 2014/11/28  
	 * 
	 */
	static function bw_textfield( $name, $len, $text, $value, $class=null, $extras=null, $args=null ) {
		$lab = self::label( $name, $text );
		if ( $value === null ) {
			$value = bw_array_get( $_REQUEST, $name, null );
		}
		$itext = itext( $name, $len, $value, $class, $extras, $args );
        if ( self::is_table() ) {
            bw_tablerow(array($lab, $itext));
        } else {
            bw_gridrow(array($lab, $itext), $class);
        }
		return;
	}

	/**
	 * Create a textfield for an array options field 
	 *
	 * @param string $name field name
	 * @param string $text field label
	 * @param array $array 
	 * @param integer $index
	 * @param integer $len
	 * @param string $class
	 * @param string $extras
	 */
	static function bw_textfield_arr( $name, $text, $array, $index, $len, $class=null, $extras=null ) {
		$name_index = $name.'['.$index.']';
		$value = bw_array_get( $array, $index, NULL );
		self::bw_textfield( $name_index, $len, $text, $value, $class, $extras );
	}

	/**
	 * Form an "email" field
	 *
	 * @param string $name - the name of the field
	 * @param integer $len - the length of the field
	 * @param string $text - the label for the field
	 * @param string $value - the value of the field
	 * @param string $class - CSS class name
	 * @param string $extras - the extras passed to itext() is expected to be a string
	 */
	static function bw_emailfield( $name, $len, $text, $value, $class=null, $extras=null ) {
		$lab = self::label( $name, $text );
		if ( $value === null ) {
			$value = bw_array_get( $_REQUEST, $name, null );
		}
		$itext = iemail( $name, $len, $value, $class, $extras );
        if ( self::is_table() ) {
            bw_tablerow(array($lab, $itext));
        } else {
            bw_gridrow(array($lab, $itext), $class);
        }
		return;
	}

	/**
	 * Create an emailfield for an array options field 
	 *
	 * @param string $name field name
	 * @param string $text field label
	 * @param array $array 
	 * @param integer $index
	 * @param integer $len
	 * @param string $class
	 * @param string $extras
	 */
	static function bw_emailfield_arr( $name, $text, $array, $index, $len, $class=null, $extras=null ) {
		$name_index = $name.'['.$index.']';
		$value = bw_array_get( $array, $index, NULL );
		self::bw_emailfield( $name_index, $len, $text, $value, $class, $extras );
	}

	/**
	 * Form a "textarea" field 
	 *
	 * @param string $name - the field name
	 * @param numeric $len - the length of the field ( e.g. 40 )
	 * @param string $text - the label of the field (e.g. "Content" )
	 * @param string $value - the value to display. If NULL then the current value is extracted from $_REQUEST[$name] 
	 * @param numeric $rows - the number of rows for the textarea field
	*/
	static function bw_textarea( $name, $len, $text, $value, $rows=10, $args=null ) {
		$lab = self::label( $name, $text );
		if ( $value === null ) {
			$value = bw_array_get( $_REQUEST, $name, '' );
			bw_trace2( $value, "bw_textarea value" );
			$value = wp_strip_all_tags( $value );
			$value = stripslashes( $value );
		}
	
		$spellcheck = bw_array_get( $args, "#spellcheck", null );
		if ( null !== $spellcheck ) {
			$spellcheck = kv( "spellcheck", $spellcheck );
		}
		$itext = iarea( $name, $len, $value, $rows, $spellcheck );
        if ( self::is_table() ) {
            bw_tablerow(array($lab, $itext));
        } else {
            $class = bw_array_get( $args, '#class', null );
            bw_gridrow(array($lab, $itext), $class);
        }
		return;
	}

	/**
	 * Create a textarea for an array options field
	 *
	 * @param string $name field name
	 * @param string $text field label
	 * @param array $array 
	 * @param integer $index
	 * @param integer $len
	 * @param integer $rows 
	 */
	static function bw_textarea_arr( $name, $text, $array, $index, $len, $rows=5 ) {
		$name_index = $name.'['.$index.']';
		$value = bw_array_get( $array, $index, NULL );
		self::bw_textarea( $name_index, $len, $text, $value, $rows );
	}

	/** 
	 * Create an optional textarea  
	 * 
	 * If the _cb field is present we use this value. otherwise we default to "on"   
	 *
	* Similar to this but the checkbox appears in the label for the textarea
	 *   bw_checkbox_arr( $option, "Include?", $options, 'intro_cb' );
	 *   `bw_textarea_arr( $option, "Introduction", $text, 'intro', 60, 5 );`
	 *
	 * @param string $name field name
	 * @param string $text field label
	 * @param array $array 
	 * @param integer $index
	 * @param integer $len
	 * @param integer $rows 
	 */
	static function bw_textarea_cb_arr( $name, $text, $array, $index, $len, $rows=5 ) {
		$name_index = $name.'['.$index.'_cb]';
		$cb_value = bw_array_get( $array, $index.'_cb', "on" );
		$cb_text = $text; 
		$cb_text .= "&nbsp;";
		$cb_text .= icheckbox( $name_index, $cb_value );
		self::bw_textarea_arr( $name, $cb_text, $array, $index, $len, $rows );
	}

	/**
	 * Display a group of radio buttons
	 * 
	 * @param string $name - the name of the group
	 * @param string $text - the title for the radio button group
	 * @param array $values - array of $id => $value - one for each button in the group
	 * @param array $labels - array of of $id => $label - one for each button in the group
	 * @param string $class - CSS class names e.g. "star"
	 * @param array $extras - sparse array of $id -> $extra where $id matches the key of the $value array 
	 * and $extra are any additional key=value parameters. This is where the "selected" radio button is defined
	*/ 
	static function bw_radio( $name, $text, $values, $labels, $class=null, $extras=null ) {
		$iradios = null;
		foreach ( $values as $id => $value ) {
			$label = bw_array_get( $labels, $id, $value );
			$extra = bw_array_get( $extras, $id, null );
			$iradios .= BW_::label( $name, $label );
			$iradios .= iradio( $name, $id, $value, $class, $extra );
		}   
		$lab = BW_::label( $name, $text );
        if ( self::is_table() ) {
            bw_tablerow(array($lab, $iradios));
        } else {
            bw_gridrow(array($lab, $iradios), $class);
        }
}

	/** 
	 * Create a select field for a form-table
	 * 
	 * @param string $name - field name
	 * @param string $text - label for the field
	 * @param int $value - the selected item
	 * @param array $args - array of parameters where the options are keyed by #options
	 */
	static function bw_select( $name, $text, $value, $args ) {
		$lab = BW_::label( $name, $text );
		$iselect = iselect( $name, $value, $args );
        if ( self::is_table() ) {
            bw_tablerow(array($lab, $iselect));
        } else {
            $class = bw_array_get( $args, '#class', null );
            bw_gridrow(array($lab, $iselect), $class);
        }
		return;
	}

	/**
	 * Create a select for an array options field
	 *
	 * @param string $name field name
	 * @param string $text field label
	 * @param array $array 
	 * @param integer $index
	 * @param array $args
	 */ 
	static function bw_select_arr( $name, $text, $array, $index, $args ) {
		$name_index = $name.'['.$index.']';
		$value = bw_array_get( $array, $index, NULL );
		BW_::bw_select( $name_index, $text, $value, $args );
	}
	
	/** 
	 * Return the default, values and notes for a shortcode parameter
	 *
	 *  bw_skv is an abbreviation of bw_sc_key_values  
	 */
	static function bw_skv( $default, $values, $notes ) {
		return( array( "default" => $default
								 , "values" => $values
								 , "notes" => $notes
								 ) );
	}
	
	/**
	 * Create a list item with a specific CSS class and/or ID
	 *
	 * @param string $text - translated text
	 * @param string $class - CSS classes for the list item
	 * @param string $id - CSS ID for the list item
	 */
	static function lit( $text, $class=null, $id=null ) {
		stag( "li", $class, $id );
		e( $text ) ;
		etag( "li" );
	}

	/** 
	 * Produce a break tag with optional text to follow
	 * 
	 * @param string $text - translated text
	 */
	static function br( $text=null ) {
		bw_echo( '<br />' );
		if ( $text ) {
			e( $text ); 
		}
	}

   /*
   * Determines display format.
    *
    * @since 3.3.0
    * @return bool - true for bw_tablerow, false for bw_gridrow
   */
    static function is_table() {
        if ( function_exists( 'bw_is_table'))  {
            $is_table = bw_is_table();
        } else {
            $is_table = true;
        }
        return $is_table;
    }



} /* end class */

} /* end if !defined */

