<?php
###  Constants
require_once(dirname(__FILE__) .'/config.inc.php');

###  Load debugging
require_once($_SERVER['DOCUMENT_ROOT'] . $DAX_BASE .'/debug.inc.php');
###  Load Model libs
require_once($_SERVER['DOCUMENT_ROOT'] . $DAX_BASE .'/db.inc.php');
require_once($_SERVER['DOCUMENT_ROOT'] . $DAX_BASE .'/SimpleORM.class.php');
require_once($_SERVER['DOCUMENT_ROOT'] . $DAX_BASE .'/SimpleORM/Local.class.php');
require_once($_SERVER['DOCUMENT_ROOT'] . $DAX_BASE .'/model/ContentSection.class.php');


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

header('Content-type: application/json');
echo '{"status":"ok"}';
