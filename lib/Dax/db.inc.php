<?php
/* HOOK */$__x = array('db', 0); foreach(dax_ex()->rhni(get_defined_vars(),$__x) as $__xi) dax_ex()->sv($__xi,$$__xi);dax_ex()->srh();if(dax_ex()->hr()) return dax_ex()->get_return();

if ( ! function_exists('bug') ) require_once($_SERVER['DOCUMENT_ROOT'] . $DAX_BASE .'/debug.inc.php');

###  Detect DB Type
if      ( substr($DAX_DSN, 0, 6) == 'sqlite' ) { $DAX_DB_TYPE = 'sqlite'; }
else if ( substr($DAX_DSN, 0, 5) == 'pgsql'  ) { $DAX_DB_TYPE = 'pg'; }
else if ( substr($DAX_DSN, 0, 5) == 'mysql'  ) { $DAX_DB_TYPE = 'mysql'; }
else { 
    trigger_error("Incompatible DB Type: ". $DAX_DSN .' in ' . trace_blame_line(), E_USER_ERROR);
}
/* HOOK */$__x = array('db', 1); foreach(dax_ex()->rhni(get_defined_vars(),$__x) as $__xi) dax_ex()->sv($__xi,$$__xi);dax_ex()->srh();if(dax_ex()->hr()) return dax_ex()->get_return();

###  Connect to the Database
START_TIMER('dbh_connect', SQL_PROFILE);
try {
    if ( $DAX_DB_TYPE == 'sqlite' && ! file_exists( substr($DAX_DSN, 7) ) ) {
        $file = substr($DAX_DSN, 7);

        ###  Make the containing dir...
        $dir = dirname($file);
        if ( !is_dir($dir) ) mkdir( $dir, 0777, true);
		/* HOOK */$__x = array('db', 3); foreach(dax_ex()->rhni(get_defined_vars(),$__x) as $__xi) dax_ex()->sv($__xi,$$__xi);dax_ex()->srh();if(dax_ex()->hr()) return dax_ex()->get_return();

        $DAX_INIT_DB_NOW = true;
    }
    $DAX_DBH = new PDO($DAX_DSN);
	/* HOOK */$__x = array('db', 4); foreach(dax_ex()->rhni(get_defined_vars(),$__x) as $__xi) dax_ex()->sv($__xi,$$__xi);dax_ex()->srh();if(dax_ex()->hr()) return dax_ex()->get_return();
    if ( $DAX_DB_TYPE == 'pg' ) {
        if ( $DAX_PG_SCHEMAS != 'public' ) $DAX_DBH->exec('SET SEARCH_PATH='. $DAX_PG_SCHEMAS);
        $DAX_DBH->exec("SET client_encoding TO '". $DAX_DB_ENCODING ."'");
		/* HOOK */$__x = array('db', 5); foreach(dax_ex()->rhni(get_defined_vars(),$__x) as $__xi) dax_ex()->sv($__xi,$$__xi);dax_ex()->srh();if(dax_ex()->hr()) return dax_ex()->get_return();
    }
	/* HOOK */$__x = array('db', 6); foreach(dax_ex()->rhni(get_defined_vars(),$__x) as $__xi) dax_ex()->sv($__xi,$$__xi);dax_ex()->srh();if(dax_ex()->hr()) return dax_ex()->get_return();
#    if ( $DAX_DB_TYPE == 'sqlite' && ! empty($DAX_INIT_DB_NOW) ) {
        $DAX_DBH->exec("CREATE TABLE content_section (
                        content_id character varying(100) NOT NULL,
                        content text
                    )");
#    }
	/* HOOK */$__x = array('db', 7); foreach(dax_ex()->rhni(get_defined_vars(),$__x) as $__xi) dax_ex()->sv($__xi,$$__xi);dax_ex()->srh();if(dax_ex()->hr()) return dax_ex()->get_return();
} catch (PDOException $e) {
	/* HOOK */$__x = array('db', 8); foreach(dax_ex()->rhni(get_defined_vars(),$__x) as $__xi) dax_ex()->sv($__xi,$$__xi);dax_ex()->srh();if(dax_ex()->hr()) return dax_ex()->get_return();
    trigger_error( '"Error Connecting to the database: ' . $e->getMessage() .' in ' . trace_blame_line(), E_USER_ERROR);
}
END_TIMER('dbh_connect', SQL_PROFILE);

/* HOOK */$__x = array('db', 10); foreach(dax_ex()->rhni(get_defined_vars(),$__x) as $__xi) dax_ex()->sv($__xi,$$__xi);dax_ex()->srh();if(dax_ex()->hr()) return dax_ex()->get_return();


