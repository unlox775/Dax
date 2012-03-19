<?php

function dax_check_auth() {

	return Globals::isContentAdmin();

///     ###  Set the session name up here
///     session_name('HACK_DAX_DEMO');
///     session_start();
/// 
///     return( isset($_SESSION['auth_success']) ? $_SESSION['auth_success'] : false );
}

