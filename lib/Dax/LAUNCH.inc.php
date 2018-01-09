<?php
$coderoot = dirname(dirname(dirname(__FILE__))) ;
///  Must have.  This loads Dax Class
require_once dirname(__FILE__) .'/dax.inc.php';

///  UNCOMMENT to override with your own Class / Config
// require_once $coderoot. '/application/models/DaxLocal.php';
// Dax::load(new Models\DaxLocal($coderoot. '/application/configs/dax-config.inc.php')); // seeds the singleton that DAX scripts use...
