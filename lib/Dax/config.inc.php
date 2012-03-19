<?php
///  leave this to others...
// ini_set('display_errors',true);

###  Constants
$GLOBALS['DAX_BASE'] = "/dax";
$GLOBALS['DAX_JS_LIB'] = 'jquery';
$GLOBALS['DAX_JQUERY_BASE'] = $GLOBALS['DAX_BASE'] ."/js/dax_jquery";
$GLOBALS['DAX_DOJO_BASE'] = $GLOBALS['DAX_BASE'] ."/js/dax_dojo";
$GLOBALS['DAX_HEADERS_INCLUDE'] = realpath( dirname(__FILE__ ) ) . "/headers.inc.php" ;

###  Editor Configurations
$GLOBALS['DAX_EDITOR_LAUNCH_MODE'] = 'in_place';

###  DB Setup
$DAX_DSN = 'sqlite:'. $_SERVER['DOCUMENT_ROOT'] . $DAX_BASE .'/sqlite/dax.sq3';

###  Debugging or Profiling flags
@define('SQL_PROFILE', true);
@define('SQL_DEBUG', false);
@define('SQL_WRITE_DEBUG', false);
@define('SimpleORM_PROFILE', false);
@define('SimpleORM_DEBUG', false);

###  Other Globals
$GLOBALS['EDIT_DAX_MODE'] = false;

###  HTML Tag Scrubber Configuration (JSON)
$GLOBALS['DAX_SCRUB_CONFIG'] = <<<CONFIG

      { allowed_tags : { div : true,
                         hr : true,
                         br : true,
                         p  : true,
                         a  : true,
                         b  : true,
                         img  : true,
                         strong : true,
                         i  : { disallowed_styles : { 'font-style' : true } },
                         em : { disallowed_styles : { 'font-style' : true } },
                         span : true,
                         u  : true,
                         ul : true,
                         ol : true,
                         li : true,
                         style : { allowed_styles : { 'border' : true,
                                                      'float' : true
                                                    }
                                 }
                       },
        allowed_attrs : { 'class' : true },
        allowed_styles : { 'font-weight' : true,
                           'font-style'  : true,
                           'text-decoration' : { 'regexp' : 'underline' }
                         },
        options : { collapse_tables_nicely : true,
                    honor_td_brs_like_rows : true,
                    max_consec_blank_lines : 1,
                    cull_empty_inline_tags : true,
                    transform_urls_into_anchors : true,
                    transform_emails_into_anchors : true,
                    remove_useless_parents : true
                    //                    disable_style_attr : false
                  }
      }

CONFIG;
