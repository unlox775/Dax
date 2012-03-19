<?php

###  Set the session name up here
session_name('HACK_DAX_DEMO');
session_start();

###  Log out
$_SESSION['auth_success'] = false;
$_SESSION['auth_username'] = '';

$loc = isset($_REQUEST['redirect']) ? $_REQUEST['redirect'] : '/';
header("Location: ". $loc);
exit;