<?php
###  Constants
require_once(dirname(__FILE__) .'/config.inc.php');

###  Load debugging
if ( ! function_exists('bug') ) require_once($_SERVER['DOCUMENT_ROOT'] . $DAX_BASE .'/debug.inc.php');
###  Load Model libs
require_once($_SERVER['DOCUMENT_ROOT'] . $DAX_BASE .'/db.inc.php');
if ( ! class_exists('SimpleORM') ) require_once($_SERVER['DOCUMENT_ROOT'] . $DAX_BASE .'/SimpleORM.class.php');
require_once($_SERVER['DOCUMENT_ROOT'] . $DAX_BASE .'/SimpleORM/Local.class.php');
require_once($_SERVER['DOCUMENT_ROOT'] . $DAX_BASE .'/model/ContentSection.class.php');


#########################
###  DAX Global Functions

$dax_empty_scrub_content = '<i>Click here to add Content</i>';
function dax_get_content($content_id, $with_empty_scrub = false, $prefix = '', $suffix = '') {
    global $dax_empty_scrub_content;
    
    $sect = new ContentSection($content_id);
    if ( ! $sect->exists()
         || empty( $sect->content )
         ) {
        if ( $with_empty_scrub ) return $dax_empty_scrub_content;
        return '';
    }
    return $prefix. $sect->content .$suffix;
}


function dax_has_content($content_id) {
    $sect = new ContentSection($content_id);
    if ( ! $sect->exists()
         || empty( $sect->content )
         ) {
        return false;
    }
    return true;
}


function dax_module( $type, $content_id, $prefix = '', $suffix = '', $style = '' ) {
    global $EDIT_DAX_MODE, $DAX_EDITOR_LAUNCH_MODE;
    if (! $EDIT_DAX_MODE) return dax_get_content($content_id, false, $prefix, $suffix);

    ###  Only certain module types, please
    if ( ! in_array($type, array('input','textarea','richtext')) ) trigger_error("Bad content module type '". $type ."' in " . trace_blame_line(), E_USER_ERROR);

    return ( '  <span id="dax_editable-'. $content_id .'" class="tundra dax_editable dax_editable-'. $type .'"'
             .        ( $DAX_EDITOR_LAUNCH_MODE == 'in_lightbox' && $type == 'richtext'
                        ? ' onClick="dax_triggerLightBox('."'". $content_id ."'".','."'". $type ."'".')"'
                        : ' onClick="dax_editContent('    ."'". $content_id ."'".','."'". $type ."'".')"'
                        )
             .        ( ! empty( $style ) ? ' editable_style="'. htmlentities($style) .'"' : '' )
             .       '>'
             .     '<span class="dax_editable_buttons"><a no-href="javascript:dax_editContent('."'". $content_id ."'".','."'". $type."'".')">Click to Edit</a></span>'
             .     ( ! empty( $prefix ) ? '<span class="dax_editable_prefix">'. $prefix .'</span>' : '' )
             .     '<span id="dax_editable_content-'. $content_id .'" class="dax_editable_content" no-onClick="dax_editContent('."'". $content_id ."'".','."'". $type ."'".')">'
             .          dax_get_content($content_id, true)
             .     '</span>'
             .     ( ! empty( $suffix ) ? '<span class="dax_editable_suffix">'. $suffix .'</span>' : '' )
             . '</span>'
             );
}

function dax_image_upload( $content_id, $extra_attrs = '', $default_img = 'dax_brokem_img.png', $istyle_code = '', $container_extra_attrs = '') {
    global $EDIT_DAX_MODE, $DAX_IMAGE_STYLE_DEFAULT_CODE, $dax_empty_scrub_content, $DAX_BASE;
    
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
require_once($_SERVER['DOCUMENT_ROOT'] . $DAX_BASE .'/auth.inc.php');
$EDIT_DAX_MODE = dax_check_auth();
