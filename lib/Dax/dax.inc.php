<?php
###  Get extend Object
if ( ! class_exists('Stark__Extend') ) require_once(realpath( dirname(__FILE__) .'/../Stark' ) .'/Extend.class.php');
$GLOBALS['dax_extend'] = new Stark__Extend();  function dax_ex() { return $GLOBALS['dax_extend']; }
###  Constants
require_once(dirname(__FILE__) .'/config.inc.php');

###  Load debugging
if ( ! function_exists('bug') ) require_once(dirname(__FILE__) . '/debug.inc.php');
###  Load Model libs
require_once(dirname(__FILE__) . '/db.inc.php');
if ( ! class_exists('SimpleORM') ) require_once(dirname(__FILE__) . '/SimpleORM.class.php');
require_once(dirname(__FILE__) . '/SimpleORM/Local.class.php');
require_once(dirname(__FILE__) . '/model/ContentSection.class.php');


#########################
###  DAX Global Functions

$GLOBALS['dax_empty_scrub_content'] = '<i>Click here to add Content</i>';
function dax_get_content($content_id, $with_empty_scrub = false, $prefix = '', $suffix = '') {
    global $dax_empty_scrub_content;
	/* HOOK */$__x = array('get_content', 0); foreach(dax_ex()->rhni(get_defined_vars(),$__x) as $__xi) dax_ex()->sv($__xi,$$__xi);dax_ex()->srh();if(dax_ex()->hr()) return dax_ex()->get_return();

    $sect = new ContentSection($content_id);
	/* HOOK */$__x = array('get_content', 5); foreach(dax_ex()->rhni(get_defined_vars(),$__x) as $__xi) dax_ex()->sv($__xi,$$__xi);dax_ex()->srh();
    if ( ! dax_call_user_func_array_cached( array( $sect, 'exists'), array() )
         || ! ( $content = dax_call_user_func_array_cached( array( $sect, 'get'), array('content') ) )
         || empty( $content )
         ) {
        if ( ! empty( $with_empty_scrub ) ) return( ( $with_empty_scrub === true || $with_empty_scrub === 1 ) ? $dax_empty_scrub_content : $with_empty_scrub );
        return '';
    }
    return $prefix. $content .$suffix;
}


function dax_has_content($content_id) {
	/* HOOK */$__x = array('has_content', 0); foreach(dax_ex()->rhni(get_defined_vars(),$__x) as $__xi) dax_ex()->sv($__xi,$$__xi);dax_ex()->srh();if(dax_ex()->hr()) return dax_ex()->get_return();
    $sect = new ContentSection($content_id);
	/* HOOK */$__x = array('has_content', 5); foreach(dax_ex()->rhni(get_defined_vars(),$__x) as $__xi) dax_ex()->sv($__xi,$$__xi);dax_ex()->srh();
    if ( ! dax_call_user_func_array_cached( array( $sect, 'exists'), array() )
         || ! ( $content = dax_call_user_func_array_cached( array( $sect, 'get'), array('content') ) )
         || empty( $content )
         ) {
        return false;
    }
    return true;
}


function dax_module( $type, $content_id, $prefix = '', $suffix = '', $style = '', $no_edit = '', $content_backup = false ) {
    global $EDIT_DAX_MODE, $DAX_EDITOR_LAUNCH_MODE;

	###  Calling Syntax
	$default_value = false;
	if ( is_array( $content_id ) ) {
		list($content_id, $default_value) = $content_id;
	}
	$static_style = '';
	if ( is_array( $style ) ) {
		list($style, $static_style) = $style;
	}

	/* HOOK */$__x = array('module', 0); foreach(dax_ex()->rhni(get_defined_vars(),$__x) as $__xi) dax_ex()->sv($__xi,$$__xi);dax_ex()->srh();if(dax_ex()->hr()) return dax_ex()->get_return();
    if ( !$EDIT_DAX_MODE || $no_edit ) return dax_get_content($content_id, $default_value, $prefix, $suffix);

	###  Now, default to showing the default phrase if not defined
	if ( $default_value === false ) $default_value = true;

    ###  Only certain module types, please
    if ( ! in_array($type, array('input','textarea','richtext')) ) trigger_error("Bad content module type '". $type ."' in " . trace_blame_line(), E_USER_ERROR);

    return ( '  <span id="dax_editable-'. $content_id .'" class="tundra dax_editable dax_editable-'. $type .'"'
             .        ( $DAX_EDITOR_LAUNCH_MODE == 'in_lightbox' && $type == 'richtext'
                        ? ' onClick="dax_triggerLightBox('."'". $content_id ."'".','."'". $type ."'".')"'
                        : ' onClick="dax_editContent('    ."'". $content_id ."'".','."'". $type ."'".')"'
                        )
             .        ( ! empty( $static_style )   ? ' style="'. htmlentities($static_style) .'"' : '' )
             .        ( ! empty( $style ) ? ' editable_style="'. htmlentities($style) .'"' : '' )
             .       '>'
             .     "<span class=\"dax_editable_buttons\"><a href=\"javascript:dax_editContent('$content_id','$type')\">Click to Edit</a></span>"
             .     ( ! empty( $prefix ) ? "<span class=\"dax_editable_prefix\">$prefix</span>" : '' )
             .     "<span id=\"dax_editable_content-$content_id\""
			 .              ' class="dax_editable_content"'
			 .              " onClick=\"dax_editContent('$content_id','$type')\""
			 .              ( $content_backup ? ' content_backup="'. htmlentities(dax_get_content($content_id, $default_value)) .'"' : '')
			 .              '>'
             .          dax_get_content($content_id, $default_value)
             .     '</span>'
             .     ( ! empty( $suffix ) ? '<span class="dax_editable_suffix">'. $suffix .'</span>' : '' )
             . '</span>'
             );
}

function dax_image_upload( $content_id, $extra_attrs = '', $default_img = 'dax_brokem_img.png', $istyle_code = '', $container_extra_attrs = '') {
    global $EDIT_DAX_MODE, $DAX_IMAGE_STYLE_DEFAULT_CODE, $dax_empty_scrub_content, $DAX_BASE;
	/* HOOK */$__x = array('image_upload', 0); foreach(dax_ex()->rhni(get_defined_vars(),$__x) as $__xi) dax_ex()->sv($__xi,$$__xi);dax_ex()->srh();if(dax_ex()->hr()) return dax_ex()->get_return();
    
    if ( empty($istyle_code) ) $istyle_code = $DAX_IMAGE_STYLE_DEFAULT_CODE;
    
    ###  Get the content and override with default either way...
    $img_url = dax_get_content($content_id, true, '','');
    if ( $img_url == $dax_empty_scrub_content ) $img_url = $default_img;
    
    $img_tag = ( '<img id="dax_editable-image-'. $content_id
                 . '" src="'. $img_url
                 . '" '. $extra_attrs
                 . '/>'
                 );
    
    ###  Return here if NOT EDIT mode
    if ( ! $EDIT_DAX_MODE) return $img_tag;

    ###  If EDIT mode, add the upload floater...
    return ( '<div class="dax_editable-image_container"'. $container_extra_attrs .'>'
             .     $img_tag
             .     '<a class="kill-parent-a-links" href="javascript:void(null)">'
             .         '<div id="dax_editable-'. $content_id .'"'
             .             ' class="tundra dax_editable dax_editable-image dojoxEditorUploadNorm"'
             .             ' >'
             .             '<div dojoType="dojox.form.FileUploader"'
             .                  ' hoverClass="dax_editable-image"'
             .                  ' activeClass="dax_editable-image"'
             .                  ' pressClass="dax_editable-image"'
             .                  ' disabledClass="dax_editable-image"'
             .                  ' selectMultipleFiles="false"'
             .                  ' uploadOnChange="true"'
             .                  ' showProgress="true"'
             .                  ' serverTimeout="15000"'
             .                  ' style="width: 16px; height: 16px; background:url('. $DAX_BASE .'/images/upload_image.png) 0 0 no-repeat;"'
             .                  ' uploadUrl="/istyle/upload.php'. (! empty($istyle_code) ? '?istyle_code='. $istyle_code : '') .'"'
             .                  ' id="dax_editable_content-'. $content_id .'"'
             .                  ' class="dax_editable_content dax_editable_content-'. $content_id .'"'
             .                  ' >'
             .                  '.'
             .             '</div>'
             .         '</div>'
             .     '</a>'
             .     '<div class="clear"></div>'
             . '</div>'
             );
}



###  Check Authentication
require_once(dirname(__FILE__) . '/auth.inc.php');
$GLOBALS['EDIT_DAX_MODE'] = dax_check_auth();

###  Load Cache Hook too if
require_once(dirname(__FILE__) . '/cache_hook.inc.php');
