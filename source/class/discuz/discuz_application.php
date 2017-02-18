<?php
if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

class discuz_application extends discuz_base{
    static function &instance() {
        static $object;
        if(empty($object)) {
            $object = new self();
        }
        return $object;
    }
    
    public function __construct() {
        $this->_init_env();
        $this->_init_config();
        $this->_init_input();
        $this->_init_output();
    }
}