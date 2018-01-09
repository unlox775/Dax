<?php
// $GLOBALS['st'] = microtime(true);
// print_r(['starting', sprintf("%.3f",microtime(true) - $GLOBALS['st'])]);exit;

###  Constants
require_once(dirname(__FILE__) .'/LAUNCH.inc.php');

#########################
###  DAX Global Functions

if ( ! isset( $_REQUEST['content_id'] ) ) return trigger_error("You must pass a content_id", E_USER_ERROR);
if ( ! isset( $_REQUEST['content'] )    ) return trigger_error("You must pass content", E_USER_ERROR);
if ( ! Dax::load()->edit_mode )        return trigger_error("Not Logged In", E_USER_ERROR);

// print_r(['pre-save', sprintf("%.3f",microtime(true) - $GLOBALS['st'])]);
Dax::load()->set_content($_REQUEST['content_id'], $_REQUEST['content']);
// print_r(['DOnN', sprintf("%.3f",microtime(true) - $GLOBALS['st'])]);
// exit;

if ( isset( $_REQUEST['callback'] ) ) {
    header('Content-type: text/plain');
    echo $_REQUEST['callback'].'('. '{"status":"ok"}' .')';
} else {
    header('Content-type: application/json');
    echo '{"status":"ok"}';
}
exit;
