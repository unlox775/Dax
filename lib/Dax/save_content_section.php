<?php
###  Get extend Object
if ( ! class_exists('Stark__Extend') ) require_once(realpath( dirname(__FILE__) .'/../Stark' ) .'/Extend.class.php');
$GLOBALS['dax_extend'] = new Stark__Extend();  function dax_ex() { return $GLOBALS['dax_extend']; }
###  Constants
require_once(dirname(__FILE__) .'/config.inc.php');

###  Load debugging
if ( ! function_exists('bug') ) require_once(dirname(__FILE__) .'/debug.inc.php');
###  Load Model libs
require_once(dirname(__FILE__) .'/db.inc.php');
if ( ! class_exists('SimpleORM') ) require_once(dirname(__FILE__) .'/SimpleORM.class.php');
require_once(dirname(__FILE__) .'/SimpleORM/Local.class.php');
require_once(dirname(__FILE__) .'/model/ContentSection.class.php');


#########################
###  DAX Global Functions

if ( ! isset( $_REQUEST['content_id'] ) ) return trigger_error("You must pass a content_id", E_USER_ERROR);
if ( ! isset( $_REQUEST['content'] )    ) return trigger_error("You must pass content", E_USER_ERROR);

$sect = new ContentSection($_REQUEST['content_id']);

###  If it doesn't exist, then create it
if ( ! $sect->exists() ) {
    $sect->create(array( 'content_id' => $_REQUEST['content_id'],
                         'content' => $_REQUEST['content']
                         ));
}
###  Otherwise, update...
else {
    $sect->set_and_save(array( 'content' => $_REQUEST['content'] ));
}

if ( isset( $_REQUEST['callback'] ) ) {
    header('Content-type: text/plain');
    echo $_REQUEST['callback'].'('. '{"status":"ok"}' .')';
} else {
    header('Content-type: application/json');
    echo '{"status":"ok"}';
}
