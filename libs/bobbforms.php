<?php // (C) Copyright Bobbing Wide 2009-2023
if ( !defined( "BOBBFORMS_INCLUDED" ) ) {
define( "BOBBFORMS_INCLUDED", "3.4.2" );

/**
 * Library: bobbforms
 * Provides: bobbforms
 * Depends: bobbfunc
 * 
 * Note: This file uses functions from the bobbfunc library. You must ensure this is already loaded
 * e.g. use oik_require_lib( "bobbfunc" )
 */

/**
 * Create a form tag
 * 
 * @param string $action - action to perform
 * @param string $method - post/get
 * @param string $class - CSS class name
 * @param string $extras - additional tag parameters
 * @return string - HTML form tag
 */
function form( $action="", $method="post", $class=null, $extras=null ) {
	$form = "<form";
	$form .= kv( "method", $method );
	$form .= kv( "action", $action );
	$form .= kv( "class", $class );
	$form .= $extras;
	$form .= ">";
	return( $form );
}

/**
 * Start a form
 * 
 * @param string $action - defaults to none - so this must be defined on the submit button
 * @param string $method - defaults to "post". other value is "get"
 * @param string $class - CSS class name
 * @param string $extras - additional tag parameters
*/
function bw_form( $action="", $method="post", $class=null, $extras=null ) {
	e( form( $action, $method, $class, $extras ) );
}

/**
 * Create a field label
 * 
 * @Uses bw_translate() from bobbfunc
 * 
 * @param string $name - field name
 * @param string $text - label to display
 * @return string - the HTML label tag
 * 
 */
function label( $name, $text ) {
	$lab = "<label for=\"";
	$lab.= $name;
	$lab.= "\">";
	$lab.= bw_translate( $text );
	$lab.= "</label>";
	return( $lab );
}

/**
 * Return a hidden input field for a form
 * 
 * @param string $name - the field name e.g. my_field or my_field[1]
 * @param string $value - the value of the field
 * @return string an <input type=hidden> tag - returned regardless of the value of the field
 */
function ihidden( $name, $value ) {
  $it = "<input type=\"hidden\" ";
  $it.= "name=\"";
  $it.= $name;
  $it.= "\" value=\"";
  $it.= $value;
  $it.= "\" />"; 
  return $it;
}  

/**
 * Create an input field of type text
 * 
 * @param string $name - the field name 
 * @param integer $len - the field length
 * @param string $value - the field value
 * @param string $class - any class name to apply
 * @param string $extras - additional values for the HTML input tag
 * @param array $args even more options
 * @return string the HTML input tag
 */
function itext( $name, $len, $value, $class=null, $extras=null, $args=null ) {
	$type = bw_array_get( $args, "#type", "text" );
	$it = "<input";
	$it.= kv( "type", $type );
	$it.= kv( "size", $len );
	$it.= " name=\"";
	$it.= $name;
	$it.= "\" id=\"";
	$it.= $name;
	$it.= "\" value=\"";
	$it.= esc_attr( $value );
	$it.= "\" class=\"";
	$it.= $class;
	$it.= "\"";
	$it.= $extras;
	$it.= " />"; 
	return $it;
}

/**
 * Create an HTML textarea field
 *  
 * @param string $name - the field name
 * @param integer $len - the length of each row
 * @param string $value - the textarea content
 * @param integer $rows - the number of rows
 * @param string $extras - any other parameters - a pre-concatenated string of kv()s
 * @return string - the HTML textarea tag
 */
function iarea( $name, $len, $value, $rows=10, $extras=null ) {
	$it = "<textarea";
	$it .= kv( "rows", $rows);
	$it .= kv( "cols", $len );
	$it .= kv( "name", $name );
	$it .= $extras;
	$it .= ">";
	$it .= $value;
	$it .= "</textarea>";
	return( $it );
}

/**
 * Create an email field 
 * 
 * @link http://www.w3.org/TR/html-markup/input.email.html
 * 
 * @param string $name field name
 * @param integer $len the field length
 * @param string $value field value
 * @param string $class CSS classes
 * @param string $extras any additional parameters
 * @return string the HTML
 */
function iemail( $name, $len, $value, $class=null, $extras=null ) {
	$it = "<input";
	$it .= kv( "type", "email" );
	$it .= kv( "name", $name );
	$it .= kv( "value", $value );
	$it .= kv( "size", $len );
	$it .= kv( "class", $class );
	$it .= $extras;
	$it .= " />";
	return( $it );
}

/**
 * Create a submit button
 * 
 * @param string $name field name
 * @param string $value field value
 * @param string $id unique button ID
 * @param string $class CSS classes
 * @param string $extras any additional parameters
 * @return string the HTML for the submit button
 */
function isubmit( $name, $value, $id=null, $class=null, $extras=null ) {
		$it = "<input";
		$it .= kv( "type", "submit" );
		$it .= kv( "name", $name );
		$it .= kv( "value", $value );
		$it .= kv( "id", $id );
		$it .= kv( "class", $class );
		$it .= $extras;
		$it .= " />";
		return( $it );
}

/**
 * Create a radio button
 * 
 * @param string $name field name
 * @param string $id unique button ID
 * @param string $value field value
 * @param string $class CSS classes
 * @param string $extras any additional parameters
 * @return string the HTML for the radio button
 
 */
function iradio( $name, $id, $value, $class, $extras ) {
	$it = "<input";
	$it .= kv( "type", "radio" );
	$it .= kv( "name", $name );
	$it .= kv( "id", $id );
	$it .= kv( "value", $value );
	$it .= kv( "class", $class );
	$it .= $extras;
	$it .= " />";
	return( $it );
}

/**
 * Create a simple table row
 * 
 * @TODO Does this get used at all?
 * 
 * @param string $td1 - contents of first cell
 * @param string $td2 - contents of second cell 
 */  
function tablerow( $td1, $td2 ) {  
	echo '<tr>';
	echo '<td>'.$td1.'</td>';
	echo '<td>'.$td2.'</td>';
	echo '</tr>';
	return 0;
}

/**
 * Create a table data cell
 *
 * In the first version the cell was not created if there was no value.
 * In oik v2.4 the cell is created even when there is no contents. 
 * This may make it easier for [bw_csv] with blanks and zeros.
 * 
 * If the cell is an array we treat it as a simple array.
 * @TODO Since this array may be partially associative we need to unassoc it first
 *
 * @param string $text - cell contents
 * @param string $class - table data class
 * @param string $id - table data ID
 *
 */
function bw_td( $text=NULL, $class=NULL, $id=NULL ) {
	stag( "td", $class, $id );
	if ( is_array( $text ) ) {
		$text = implode( ", ",  $text );
	}
	e( $text );
	etag( "td");
}

/**
 * Create a table heading cell
 *
 * In the first version the cell was not created if there was no value.
 * In oik v2.4 the cell is created even when there is no contents. 
 * This may make it easier for [bw_csv] with blanks and zeros. 
 *
 * @param string $text - cell contents
 * @param string $class - table heading class
 * @param string $id - table heading ID
 */
function bw_th( $text=NULL, $class=NULL, $id=NULL ) {
	stag( "th", $class, $id );
	e( $text );
	etag( "th");
}

/**
 * Display a table row or table head row when $tdtag = "th"
 *
 * If we want it to do something other than create a table then we will need 
 * to redefine bw_td() and bw_th() 
 * 
 * @param array $td_array 
 * @param string $trtag "tr" or ?
 * @param string $tdtag "td" or "th" or ? 
 */
function bw_tablerow( $td_array=array(), $trtag="tr", $tdtag="td" ) {
	if ( count( $td_array ) ) {
		stag( $trtag );
		foreach ( $td_array as $td ) {
			if ( $tdtag == "th" ) {
				bw_th( $td );
			} else {
				bw_td( $td );
			}  
		}
		etag( $trtag ); 
	}
}

/**
 * Create a text input field
 * 
 * @TODO Confirm this is used
 *
 * Create a text field with a label formatted as two columns using tablerow
 * 
 * @param string $name the field name e.g. myfield
 * @param integer $len field length
 * @param string $text the label text
 * @param string $value the field value... it doesn't have to be escaped
 * @param string $class CSS classes to apply
 * @param string $extras extra attributes which aren't supported by $args
 * @param array $args Additional parameters for the field
 */
function textfield( $name, $len, $text, $value, $class=null, $extras=null, $args=null ) {
	$lab = label( $name, $text );
	$itext = itext( $name, $len, $value, $class, $extras, $args ); 
	tablerow( $lab, $itext );
	return;
}

/**
 * Creates a textarea field.
 *
 */
function textarea( $name, $len, $text, $value, $rows=10, $args=null) {
	$lab = label( $name, $text );
	$itext = iarea( $name, $len, $value, $rows ); 
	tablerow( $lab, $itext );
	return;
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
function bw_textfield( $name, $len, $text, $value, $class=null, $extras=null, $args=null ) {
	$lab = label( $name, $text );
	if ( $value === null ) {
		$value = bw_array_get( $_REQUEST, $name, null );
	}
	$itext = itext( $name, $len, $value, $class, $extras, $args );
    if ( bw_is_table() ) {
        bw_tablerow(array($lab, $itext));
    } else {
        bw_gridrow(array($lab, $itext), $class);
    }
	return;
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
function bw_emailfield( $name, $len, $text, $value, $class=null, $extras=null ) {
	$lab = label( $name, $text );
	if ( $value === null ) {
		$value = bw_array_get( $_REQUEST, $name, null );
	}
	$itext = iemail( $name, $len, $value, $class, $extras );
    if ( bw_is_table() ) {
        bw_tablerow(array($lab, $itext));
    } else {
        bw_gridrow(array($lab, $itext), $class );
    }
	return;
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
function bw_textarea( $name, $len, $text, $value, $rows=10, $args=null ) {
	$lab = label( $name, $text );
	if ( $value === null ) {
		$value = bw_array_get( $_REQUEST, $name, null );
		bw_trace2( $value, "bw_textarea value" );
		$value = wp_strip_all_tags( $value );
		$value = stripslashes( $value );
	}
	
	$spellcheck = bw_array_get( $args, "#spellcheck", null );
	if ( null !== $spellcheck ) {
		$spellcheck = kv( "spellcheck", $spellcheck );
	}
	$itext = iarea( $name, $len, $value, $rows, $spellcheck );
    if ( bw_is_table() ) {
        bw_tablerow(array($lab, $itext));
    } else {
        $class = bw_array_get( $args, '#class', null );
        bw_gridrow(array($lab, $itext), $class );
    }
	return;
}

/**
 * Display a group of radio buttons
 * 
 * @param string $name - the name of the group
 * @param string $text - the title for the radio button group
 * @param array $values - array of $id => $value - one for each button in the group
 * @param array $labels - arraof of $id => $label - one for each button in the group
 * @param string $class - CSS class names e.g. "star"
 * @param array $extras - sparse array of $id -> $extra where $id matches the key of the $value array 
 * and $extra are any additional key=value parameters. This is where the "selected" radio button is defined
*/ 
function bw_radio( $name, $text, $values, $labels, $class=null, $extras=null ) {
	$iradios = null;
	foreach ( $values as $id => $value ) {
		$label = bw_array_get( $labels, $id, $value );
		$extra = bw_array_get( $extras, $id, null );
		$iradios .= label( $name, $label );
		$iradios .= iradio( $name, $id, $value, $class, $extra );
	}   
	$lab = label( $name, $text );
    if ( bw_is_table() ) {
        bw_tablerow(array($lab, $iradios));
    } else {
        bw_gridrow(array($lab, $radios), $class );
    }
}

/** 
 * Is this option selected?
 * 
 * Return the selected value if the $option_key or $option_value == $value or the $option_key is IN the $value array
 *
 * `
    [0] => _oik_api_calls
    [1] => Array
        (
            [0] => 6410
            [1] => 6251
            [2] => 6409
        )

    [2] => Array
        (
            [#type] => oik_api
            [#multiple] => 1
            [#options] => Array
                (
                    [6410] => aalt() - aalt
                    [6251] => abbr() - Create an &lt;abbr&gt; tag
                    [6409] => aclass() - aclass
                    
 * `
 * 
 * @param string $option_key
 * @param string $option_value
 * @param mixed $value
 */ 
function is_selected( $option_key, $option_value, $value ) {
	if ( is_array( $value ) ) {
		$vals = bw_assoc( $value );
		$val = bw_array_get( $vals, $option_key, null );
	} else {
		$val = $value;
	}
	$selected = selected( $option_key, $val, false );
	if  ( !$selected ) {
		$selected = selected( $option_value, $val, false );
	}
	return( $selected );
}

/**
 * Query the need to shorten a select field's options
 *
 * Get the value of '#length' if available. If this is set then we truncate the options strings
 *
 *
 * @param array $args -
 * @return integer - the length to shorten to, not including the '...'
 *
 */
function bw_query_shorten( $args=null ) {
	bw_backtrace( BW_TRACE_DEBUG );
	$shorten = 0;
	if ( $args ) {
		$shorten = bw_array_get( $args, "#length", 0 );
	}
	return( $shorten );
}

/**
 * Shorten a string
 *
 * @param string|null $string 
 * @param integer $shorten maximum allowed length
 * @return the shortened string with trailing ellipsis
 */
function bw_shorten( $string, $shorten ) {
	if ( strlen( $string ) > ( $shorten + 3) ) {
		$string = substr( $string, 0, $shorten );
		$string .= "...";
	}
	return( $string );
}

/**
 * Return a select list with the current selection
 *
 * The current value may either be a numeric index OR the actual string 
 * It's slightly more efficient when it's the index
 *
 * @param string $name - the name for the field
 * @param string/mixed $value - the key value for the current selection or an array for #multiple selection
 * @param array $args - containing "#options" which must point to a non empty array
 * @return $iselect HTML for the select list
 */
function iselect( $name, $value, $args ) {
	//bw_trace2();
	$multiple = bw_array_get( $args, "#multiple", false );
	if ( $multiple ) {
		$iselect = "<select name=\"{$name}[]\" multiple size=\"$multiple\">" ;
	} else {
		$iselect = "<select name=\"$name\">" ;
	}
	$options = bw_as_array( $args['#options'] );
	$optional = bw_array_get( $args, "#optional", false );
	if ( $optional ) {
		$options = array( __( "None" ) ) + $options;
	}
	//bw_trace2( $options, "options" );
	$bw_shorten = bw_query_shorten( $args );
	foreach ( $options as $option_key => $option_value ) {
		$selected = is_selected( $option_key, $option_value, $value );
		if ( $bw_shorten ) {
			$option_value = bw_shorten( $option_value, $bw_shorten );
		}
		$option = "<option value=\"$option_key\" $selected>$option_value</option>";
		$iselect .= $option; 
	}
	$iselect .= "</select>" ;
	return( $iselect );
}

/** 
 * Create a select field for a form-table
 * 
 * @param string $name - field name
 * @param string $text - label for the field
 * @param int $value - the selected item
 * @param array $args - array of parameters where the options are keyed by #options
 */
function bw_select( $name, $text, $value, $args ) {
	$lab = label( $name, $text );
	$iselect = iselect( $name, $value, $args );
    if ( bw_is_table() ) {
        bw_tablerow(array($lab, $iselect));
    } else {
        $class = bw_array_get( $args, '#class', null );
        bw_gridrow(array($lab, $iselect), $class);
    }
	return;
}

/** 
 * Create a checkbox input field 
 * 
 * Note: In order to obtain a value when the checkbox is unticked
 * we add a hidden field with a value of off
 * See http://iamcam.wordpress.com/2008/01/15/unchecked-checkbox-values/
 * When the value is set then the checkbox is checked
 * 
 * @param string $name the name for the checkbox input field
 * @param string $value the value of the checkbox - default NOT checked
 * @param bool whether or not the checkbox should be disabled. default: false
 */
function icheckbox( $name, $value=NULL, $disabled=false ) {
	$it = ihidden( $name, "0" );
	$it .= "<input type=\"checkbox\" ";
	$it.= "name=\"";
	$it.= $name;
	$it.= "\" id=\"";
	$it.= $name;
	$it.= '"';
	if ( $value && $value != "0" ) {
		$it.= " checked=\"checked\"";
	} 
	if ( $disabled ) {
		$it .= kv( "disabled", "disabled" );
	}   
	$it.= "/>"; 
	return $it;
}

/**
 * Create a checkbox field given a field name and value
 *
 * @param string $name field name
 * @param string $text field label
 * @param integer $value 1 for checked
 * @param array $args array of arguments
 */
function bw_checkbox( $name, $text, $value=1, $args=NULL ) {
	$lab = BW_::label( $name, $text );
	$icheckbox = icheckbox( $name, $value );
    if ( bw_is_table() ) {
        bw_tablerow(array($lab, $icheckbox));
    } else {
        $class = bw_array_get( $args, '#class', null );
        bw_gridrow(array($lab, $icheckbox), $class );
    }
	return;
}

/**
 * Create a checkbox for an array options field 
 * 
 * @param string $name field name
 * @param string $text field label
 * @param array $array 
 * @param integer $index
 */
function bw_checkbox_arr( $name, $text, $array, $index ) {
	$name_index = $name.'['.$index.']';
	$value = bw_array_get( $array, $index, NULL );
	bw_checkbox( $name_index, $text, $value );
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
function bw_textfield_arr( $name, $text, $array, $index, $len, $class=null, $extras=null ) {
	$name_index = $name.'['.$index.']';
	$value = bw_array_get( $array, $index, NULL );
	bw_textfield( $name_index, $len, $text, $value, $class, $extras );
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
function bw_emailfield_arr( $name, $text, $array, $index, $len, $class=null, $extras=null ) {
	$name_index = $name.'['.$index.']';
	$value = bw_array_get( $array, $index, NULL );
	bw_emailfield( $name_index, $len, $text, $value, $class, $extras );
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
function bw_textarea_arr( $name, $text, $array, $index, $len, $rows=5 ) {
	$name_index = $name.'['.$index.']';
	$value = bw_array_get( $array, $index, NULL );
	bw_textarea( $name_index, $len, $text, $value, $rows );
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
function bw_select_arr( $name, $text, $array, $index, $args ) {
	$name_index = $name.'['.$index.']';
	$value = bw_array_get( $array, $index, NULL );
	bw_select( $name_index, $text, $value, $args );
}

/** 
 * Create an optional textarea  
 * 
 * If the _cb field is present we use this value. otherwise we default to "on"   
 *
 * Similar to this but the checkbox appears in the label for the textarea
 *   bw_checkbox_arr( $option, "Include?", $options, 'intro_cb' );
 *   bw_textarea_arr( $option, "Introduction", $text, 'intro', 60, 5 );
 *
 * @TODO: In this version the checkbox appears next to the label. It might be nicer to have it next to the textarea. 
 * Note: We need to call bw_translate() here since the modified text won't be recognised when it reaches the label() function
 * as it will have had the nbsp and checkbox HTML added by then.
 *
 * @param string $name field name
 * @param string $text field label
 * @param array $array 
 * @param integer $index
 * @param integer $len
 * @param integer $rows 
 */
function bw_textarea_cb_arr( $name, $text, $array, $index, $len, $rows=5 ) {
	$name_index = $name.'['.$index.'_cb]';
	$cb_value = bw_array_get( $array, $index.'_cb', "on" );
	$cb_text = bw_translate( $text ); 
	$cb_text .= "&nbsp;";
	$cb_text .= icheckbox( $name_index, $cb_value );
	bw_textarea_arr( $name, $cb_text, $array, $index, $len, $rows );
}

/**
 * Start of a WordPress form for options fields
 * 
 * @param string $option - name of the options field e.g. "bw_privacy_policy" ( 2nd parm to register_setting())
 * @param string $settings - name of the "settings" e.g. "oik_privacy_policy_options" (1st parm to register_setting())
 * @param string $action for the form - defaults to "options.php" 
 * @return array $options - the stored options settings 
 */
if ( !function_exists( "bw_form_start" ) ) { 
	function bw_form_start( $option, $settings, $action="options.php" ) {
		bw_form( $action );
		$options = get_option( $option );   
		stag( 'table', "form-table" );
		bw_flush();
		settings_fields( $settings );
		return( $options );
	}
}

/**
 * Reset the options to the default fields
 * 
 * Note: There really should be some security/nonce checking here **?** 
 * 
 * @param string $option - option name
 * @param array $options - options 
 * @param string $default_cb - callback function to create the defaults
 * @param string $request_field - the field name that triggers the reset
 * 
 */
function bw_reset_options( $option, $options, $default_cb, $request_field ) {     
	$reset = bw_array_get( $_REQUEST, $request_field, null );
	if ( $reset ) {
		delete_option( $option );
		$options = FALSE;
	}
	if ( $options == FALSE ) {  
		$options = $default_cb();
	} else { 
		$options = array_merge( $default_cb(), $options );
	}
	return( $options );
}
 
/**
 * Return the current URL
 *
 * Fully qualified so that it can be passed as a parameter to another site
 * Code copied from WordPress-SEO and {@link http://webcheatsheet.com/PHP/get_current_page_url.php}
 *
 * @return string URL made up from global fields
 */
function bw_current_url() {
	$pageURL = 'http';
	// Does it matter what it's set to? "on" or 1 **?**
	if ( isset( $_SERVER["HTTPS"] ) ) {  
		$pageURL .= "s";
	}
	$pageURL .= "://";
	$pageURL .= $_SERVER["SERVER_NAME"];
	// We know that SERVER_PORT is set by apache
	//if ($_SERVER["SERVER_PORT"] != "80") {
	//	$pageURL .= $_SERVER["SERVER_PORT"];
	//}
	$pageURL .= $_SERVER["REQUEST_URI"];
	bw_trace2( $pageURL, "pageURL", false );
	return( $pageURL );
}

/**
 * Verify the nonce field
 *
 * @param string $action - the action passed on the call to wp_nonce_field()
 * @param string $name - the name passed on the call to wp_nonce_field() 
 * @return integer - 1 or 2 if verified, false if not
 */
if ( !function_exists( "bw_verify_nonce" ) ) {
	function bw_verify_nonce( $action, $name ) {
		$nonce_field = bw_array_get( $_REQUEST, $name, null );
		$verified = wp_verify_nonce( $nonce_field, $action );
		bw_trace2( $verified, "wp_verify_nonce?" );
		return( $verified );
	}
}


/**
 * Starts a table or grid.
 *
 * @since v3.4.0
 * @param null $table
 */
function bw_table_or_grid_start( $table=null ) {
    bw_is_table( $table );
    if ( $table ) {
        stag( 'table');
    } else {
        sdiv( 'bw_grid');
    }
}

    /**
     * Ends a table or grid.
     * @since v3.4.0
     */
function bw_table_or_grid_end() {
    if ( bw_is_table() ) {
        etag( 'table');
    } else {
        ediv();
    }
}

/**
 * Checks for/sets table or grid.
 *
 * The default format is table.
 * To set the display format to grid use `bw_is_table( false )`.
 *
 * @since v3.4.0
 * @param $table null to retrieve the current value. true for table, false for grid.
 * @return mixed the new value.
 */
function bw_is_table( $table=null ) {
    static $bw_table_or_grid = true;
    if ( null !== $table ) {
        $bw_table_or_grid = $table;
    }
    bw_trace2( $bw_table_or_grid, "table or grid");
    return $bw_table_or_grid;
 }

/**
 * Displays a row as a grid.
 *
 * @since v3.4.0
 * @since v3.4.1 Added $class parameter for block styling
 * @param $array
 * @param string $class - block style
 */
function bw_gridrow( $array, $class=null ) {
    if ( count( $array ) ) { sdiv( $class );
        foreach ($array as $item) {
            e($item);
        }
        ediv();
    }
}

} /* end !defined */
