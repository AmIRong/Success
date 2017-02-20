<?php

if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

class table_common_syscache extends discuz_table
{
    public function __construct() {
    
        $this->_table = 'common_syscache';
        $this->_pk    = 'cname';
        $this->_pre_cache_key = '';
        $this->_isfilecache = getglobal('config/cache/type') == 'file';
        $this->_allowmem = memory('check');
    
        parent::__construct();
    }
}