<?php

/**
 * Role - a system role type, which users can assume.  This determines access privileges for various systems and functionality
 * 
 * @see StarkORM
 * @version $Id: ContentSection.class.php,v 1.2 2010/09/29 16:05:52 daxd Exp $
 * @package TSANet
 * @subpackage Model Objects
 */

if ( ! class_exists('StarkORM__DAX__Local') ){
  // require_once(dirname(dirname(__FILE__)) . '/StarkORM.class.php');
   require_once(dirname(dirname(__FILE__)) . '/StarkORM/Local.class.php');
  // 
}

class ContentSection extends StarkORM__DAX__Local {
    protected $__table       = 'dax_content_section';
    protected $__primary_key = array( 'content_id', 'daxpub_id' );
    public    $__clone_by_ukey = array( 'content_id' );
    protected $__schema = array( 'content_id'       => array( 'maxlength' => 25 ),
								 'daxpub_id'        => array(),
								 'content'          => array(),
        );
    protected $__relations = array( 
                                  
        );
    public static function get_where($where = null, $limit_or_only_one = false, $order_by = null) { return parent::get_where($where, $limit_or_only_one, $order_by); }

    ///  Local var
    public $publish_table = 'dax_content_publish';
    public $custom_page_content_prefix = '||CUSTOM_PAGE_URL||';

	public static function getPublishedContent($content_id, $channel = null, $return_object = false) {
		///  EDIT MODE : prefer the current daxpub_id above all else
		///    Unless we are in preview mode with a content ID that is A/B active right now
		if ( is_null($channel) && Dax::load()->preferCurrentPublish() ) {
			$obj = new ContentSection(array($content_id, Dax::load()->current_daxpub_id ), true);
			if ( $obj->exists() ) { return( $return_object ? $obj : $obj->content ); }
		}

		$class = function_exists('get_called_class') ? get_called_class() : __CLASS__;
		$_tmp_obj = new $class();

		$sql = "SELECT c.*
                  FROM ". $_tmp_obj->get_table() ." c
                  JOIN ". $_tmp_obj->publish_table ." p USING(daxpub_id)
                 WHERE c.content_id = ?
                   AND p.is_published = 1
                   AND p.start_date <= ". ( Dax::load()->sql_now($_tmp_obj) ) ."
                   AND ( p.end_date IS NULL
                   	  OR p.end_date > ". ( Dax::load()->sql_now($_tmp_obj) ) ."
                   	     )
                   ". ( is_null( $channel )
                   	    ? ''
                   	    : "
                   AND EXISTS( SELECT 1
                   	             FROM ". $_tmp_obj->publish_table ." p
                   	            WHERE p.daxpub_id = c.daxpub_id
                   	              AND ( channel = '-'
                   	                 OR channel = ". $_tmp_obj->dbh()->quote($channel) ."
                   	                  )
	                           )
                            "
                        ) ."
              ORDER BY p.start_date DESC
                 ";

		$values = array($content_id);
        $sth = $_tmp_obj->dbh_query_bind($sql, $values);
        $row = $sth->fetch(PDO::FETCH_ASSOC);
		if ( empty( $row ) ) { return null; }

		if ( $return_object ) {
			return new ContentSection(array($row['content_id'], $row['daxpub_id']), $row);
		}
		return $row['content'];
	}

    public static function get_all_content_multi_channel() {
		$class = function_exists('get_called_class') ? get_called_class() : __CLASS__;
		$_tmp_obj = new $class();
        $sql = "SELECT DISTINCT content_id
                  FROM ". $_tmp_obj->get_table() ." c
                  JOIN ". $_tmp_obj->publish_table ." p USING(daxpub_id)
                 WHERE p.channel != '-'
                   AND p.start_date <= ". ( Dax::load()->sql_now($_tmp_obj) ) ."
                   AND ( p.end_date IS NULL
                   	  OR p.end_date > ". ( Dax::load()->sql_now($_tmp_obj) ) ."
                   	     )
                 ";
        $all_data = $_tmp_obj->dbh_query_bind($sql, array())->fetchAll(PDO::FETCH_ASSOC);
        $all = array();
        foreach( $all_data as $row ) {
            $all[$row['content_id']] = true;
        }
        return $all;
    }

    public static function get_all_custom_pages($channel = null, $with_case_insensitive = false) {
		$class = function_exists('get_called_class') ? get_called_class() : __CLASS__;
		$_tmp_obj = new $class();
        $sql = "SELECT DISTINCT content_id,
                       ( CASE
                       	 WHEN content = '||deleted||' THEN 0
                       	 ELSE 1
                       	 END
                       	 ) as active
                  FROM ". $_tmp_obj->get_table() ." c
                  JOIN ". $_tmp_obj->publish_table ." p USING(daxpub_id)
                 WHERE content_id LIKE ". $_tmp_obj->dbh()->quote($_tmp_obj->custom_page_content_prefix .'%') ."
                   AND ( daxpub_id = ". Dax::load()->current_daxpub_id ."
	                    OR ( p.is_published != 0
	                       AND p.start_date <= ". ( Dax::load()->sql_now($_tmp_obj) ) ."
		                   AND ( p.end_date IS NULL
		                   	  OR p.end_date > ". ( Dax::load()->sql_now($_tmp_obj) ) ."
		                   	     )
		                   ". ( is_null( $channel )
		                   	    ? ''
		                   	    : "
		           	              AND ( channel = '-'
		           	                 OR channel = ". $_tmp_obj->dbh()->quote($channel) ."
		           	                  )
		                            "
		                        ) ."
		                   )
		                )
                 ";

        $all_data = $_tmp_obj->dbh_query_bind($sql, array())->fetchAll(PDO::FETCH_ASSOC);
        $all = array();
        foreach( $all_data as $row ) {
        	if ( ! $row['active'] ) continue; // We still want to select inactive ones, but can skip them here
            $all[substr($row['content_id'],strlen($_tmp_obj->custom_page_content_prefix))] = true;            
            $all_ci[strtolower(substr($row['content_id'],strlen($_tmp_obj->custom_page_content_prefix)))] = substr($row['content_id'],strlen($_tmp_obj->custom_page_content_prefix));
        }
        //maintain backwards compatibility
        return $with_case_insensitive ? array($all, $all_ci) : $all;
    }

}
