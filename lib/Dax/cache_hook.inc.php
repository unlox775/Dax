<?php

function dax_cache_hook_enabled() {
	/* HOOK */$__x = array('cache_hook_enabled', 0); foreach(dax_ex()->rhni(get_defined_vars(),$__x) as $__xi) dax_ex()->sv($__xi,$$__xi);dax_ex()->srh();if(dax_ex()->hr()) return dax_ex()->get_return();
    ///  Disabled cache for the Content Admin
    return( dax_check_auth() ? false : true );
}

function dax_call_user_func_array_cached($func, $params) {
    ###  In case they are sloppy...
    if ( !is_array( $params ) ) $params = array( $params );
	/* HOOK */$__x = array('cache_hook', 0); foreach(dax_ex()->rhni(get_defined_vars(),$__x) as $__xi) dax_ex()->sv($__xi,$$__xi);dax_ex()->srh();if(dax_ex()->hr()) return dax_ex()->get_return();

    ###  Run function with caching.
    if ( dax_cache_hook_enabled() ) {
		/* HOOK */$__x = array('cache_hook', 5); foreach(dax_ex()->rhni(get_defined_vars(),$__x) as $__xi) dax_ex()->sv($__xi,$$__xi);dax_ex()->srh();if(dax_ex()->hr()) return dax_ex()->get_return();

        ###  Stub for Cache Call here...

    } else {
        $return = call_user_func_array( $func, $params );
        return $return;
    }
}
