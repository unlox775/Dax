<?php

###  Set the session name up here
session_name('HACK_DAX_DEMO');
session_start();

###  Just a REAL simple AUTH routine.
###    Override this with your own auth
if ( $_REQUEST['username'] == 'dax'
     && md5( $_REQUEST['password'] ) == '46f94c8de14fb36680850768ff1b7f2a'
     ) {
    $_SESSION['auth_success'] = true;
    $_SESSION['auth_username'] = $_REQUEST['username'];
}

$loc = isset($_REQUEST['redirect']) ? $_REQUEST['redirect'] : '/';
header("Location: ". $loc);
exit;