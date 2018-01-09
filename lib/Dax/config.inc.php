<?php
///  leave this to others...
// ini_set('display_errors',true);

###  Constants
$this->config('base',"/lib/dax");
$this->config('js_lib','jquery');

###  DB Setup  : NOTE we are using the Zend DB Connection!
$this->config('dsn','sqlite:'. $_SERVER['DOCUMENT_ROOT'] . $DAX_BASE .'/sqlite/dax.sq3');

###  Other Globals
$this->edit_mode = false;
$this->env_mode = 'live';

###  HTML Tag Scrubber Configuration (JSON)
$this->config('scrub_config',<<<CONFIG
      { allowed_tags : { div : true,
                         hr : true,
                         h1 : true,
                         h2 : true,
                         h3 : true,
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
        allowed_attrs : { 
            'class' : true,
            'id' : true,
            'name' : true
        },
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

CONFIG
);
