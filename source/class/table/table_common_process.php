<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_common_process extends discuz_table
{
    public function __construct() {
    
        $this->_table = 'common_process';
        $this->_pk    = 'processid';
    
        parent::__construct();
    }
}

?>