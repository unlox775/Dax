<?php
###  Constants
require_once(dirname(__FILE__) .'/LAUNCH.inc.php');

#########################
###  DAX Global Functions

$dax = Dax::load();
if ( ! isset( $_REQUEST['page_id'] )       ) return trigger_error("You must pass a page_id",           E_USER_ERROR);
if ( ! isset( $_REQUEST['page_template'] ) ) return trigger_error("You must pass a new page_template", E_USER_ERROR);
if ( ! $dax->edit_mode ) return trigger_error("Not Logged In", E_USER_ERROR);




$page_template = $_REQUEST['page_template'];
$template = $dax->templateClassInstance($page_template);

///  Die if they are trying to "change" a key that does not exist
$cust_page_content_id = '||CUSTOM_PAGE_URL||'. $_REQUEST['page_id'];
$existing_cust_page = $dax->get_content($cust_page_content_id);
if ( empty($existing_cust_page) || $existing_cust_page == '||deleted||'  ) { return trigger_error("Page Id passed needs to already exist as a custom page", E_USER_ERROR); }

///  Stuff it with default content
$page_content_id = $_REQUEST['page_id']. '||page_content-'. $page_template;
$prototype = (object) $template->prototype();
$prototype->template = $page_template;

///  Only overwrite if the content didn't exist
$existing_page_content = $dax->get_content($page_content_id);
if ( empty($existing_page_content) ) {
	$dax->set_content($page_content_id, json_encode($prototype));
}

///  Set the page template
$dax->set_content($cust_page_content_id, json_encode(['page_template' => $page_template]));

$return_obj = array('status' => 'ok');

if ( isset( $_REQUEST['callback'] ) ) {
    header('Content-type: text/plain');
    echo $_REQUEST['callback'].'('. json_encode($return_obj) .')';
} else {
    header('Content-type: application/json');
    echo json_encode($return_obj);
}
