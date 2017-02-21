<?php

if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

class table_common_session extends discuz_table
{
    public function __construct() {
    
        $this->_table = 'common_session';
        $this->_pk    = 'sid';
    
        parent::__construct();
    }
}