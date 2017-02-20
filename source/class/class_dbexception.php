<?php

if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

class DbException extends Exception{

    public $sql;

    public function __construct($message, $code = 0, $sql = '') {
        $this->sql = $sql;
        parent::__construct($message, $code);
    }
}
?>