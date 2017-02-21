<?php

if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

class discuz_session {
    
    public function __construct($sid = '', $ip = '', $uid = 0) {
        $this->old = array('sid' =>  $sid, 'ip' =>  $ip, 'uid' =>  $uid);
        $this->var = $this->newguest;
    
        $this->table = C::t('common_session');
    
        if(!empty($ip)) {
            $this->init($sid, $ip, $uid);
        }
    }
}