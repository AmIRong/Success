<?php

if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

class discuz_process
{
    public static function islocked($process, $ttl = 0) {
        $ttl = $ttl < 1 ? 600 : intval($ttl);
        return discuz_process::_status('get', $process) || discuz_process::_find($process, $ttl);
    }
}

?>