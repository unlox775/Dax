<?php

/**
 * Role - a system role type, which users can assume.  This determines access privileges for various systems and functionality
 * 
 * @see SimpleORM
 * @version $Id: ContentSection.class.php,v 1.2 2010/09/29 16:05:52 daxd Exp $
 * @package TSANet
 * @subpackage Model Objects
 */
class ContentSection extends SimpleORM__DAX__Local {
    protected $table       = 'dax_content_section';
    protected $primary_key = array( 'content_id' );
    protected $schema = array( 'content_id'       => array( 'maxlength' => 25 ),
                               'content'          => array(),
        );
    protected $relations = array( 
                                  
        );
}
