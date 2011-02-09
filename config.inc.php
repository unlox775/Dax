<?php
ini_set('display_errors',true);

###  Constants
$DAX_BASE = "/dax";
$DAX_JS_LIB = 'dojo';
$DAX_JQUERY_BASE = $DAX_BASE ."/js/dax_jquery";
$DAX_DOJO_BASE = $DAX_BASE ."/js/dax_dojo";
$DAX_HEADERS_INCLUDE = $DAX_BASE ."/headers.inc.php";

###  Editor Configurations
$DAX_EDITOR_LAUNCH_MODE = 'in_place';

###  DB Setup
$DAX_DSN = 'sqlite:'. $_SERVER['DOCUMENT_ROOT'] . $DAX_BASE .'/sqlite/dax.sq3';

###  Debugging or Profiling flags
@define('SQL_PROFILE', true);
@define('SQL_DEBUG', false);
@define('SQL_WRITE_DEBUG', false);
@define('SimpleORM_PROFILE', false);
@define('SimpleORM_DEBUG', false);

###  Other Globals
$EDIT_DAX_MODE = false;

###  HTML Tag Scrubber Configuration (JSON)
$DAX_SCRUB_CONFIG = <<<CONFIG

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
