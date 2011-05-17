<?php

function dax_cache_hook_enabled() {
    ###  Disabled by default until you want this...
    return false;
}

function dax_call_user_func_array_cached($func, $params) {
    ###  In case they are sloppy...
    if ( !is_array( $params ) ) $params = array( $params );

    ###  Run function with caching.
    if ( dax_cache_hook_enabled() ) {

        ###  Stub for Cache Call here...

    } else {
        $return = call_user_func_array( $func, $params );
        return $return;
    }
}
