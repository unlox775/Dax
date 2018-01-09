<?php
###  Constants
require_once(dirname(__FILE__) .'/LAUNCH.inc.php');

#########################
###  DAX Global Functions

$dax = Dax::load();
if ( ! $dax->edit_mode ) return trigger_error("Not Logged In", E_USER_ERROR);

$db = $dax->get_dbh();

$id_where = 'daxpub_id = '. $dax->current_daxpub_id;
list($published) = $db->query('SELECT is_published FROM dax_content_publish WHERE '. $id_where)->fetch();

$change_to = $published ? 0 : 1;

$db->query('UPDATE dax_content_publish SET is_published = '. $change_to .' WHERE '. $id_where);
$return_obj = array('status' => 'ok', 'set_to' => $change_to);

if ( isset( $_REQUEST['callback'] ) ) {
    header('Content-type: text/plain');
    echo $_REQUEST['callback'].'('. json_encode($return_obj) .')';
} else {
    header('Content-type: application/json');
    echo json_encode($return_obj);
}
