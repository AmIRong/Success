<?php

if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

class discuz_memory extends discuz_base
{
    public function __construct() {
        $this->extension['redis'] = extension_loaded('redis');
        $this->extension['memcache'] = extension_loaded('memcache');
        $this->extension['apc'] = function_exists('apc_cache_info') && @apc_cache_info();
        $this->extension['xcache'] = function_exists('xcache_get');
        $this->extension['eaccelerator'] = function_exists('eaccelerator_get');
        $this->extension['wincache'] = function_exists('wincache_ucache_meminfo') && wincache_ucache_meminfo();
    }
    
}