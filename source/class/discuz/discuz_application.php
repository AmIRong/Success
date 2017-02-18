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
}