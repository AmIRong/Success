<?php

if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}


class discuz_table extends discuz_base
{

    public function __construct($para = array()) {
        if(!empty($para)) {
            $this->_table = $para['table'];
            $this->_pk = $para['pk'];
        }
        if(isset($this->_pre_cache_key) && (($ttl = getglobal('setting/memory/'.$this->_table)) !== null || ($ttl = $this->_cache_ttl) !== null) && memory('check')) {
            $this->_cache_ttl = $ttl;
            $this->_allowmem = true;
        }
        $this->_init_extend();
        parent::__construct();
    }
    protected function _init_extend() {
    }
}