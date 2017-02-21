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
    
    public function fetch($id, $force_from_db = false){
        $data = array();
        if(!empty($id)) {
            if($force_from_db || ($data = $this->fetch_cache($id)) === false) {
                $data = DB::fetch_first('SELECT * FROM '.DB::table($this->_table).' WHERE '.DB::field($this->_pk, $id));
                if(!empty($data)) $this->store_cache($id, $data);
            }
        }
        return $data;
    }
    
    public function fetch_cache($ids, $pre_cache_key = null) {
        $data = false;
        if($this->_allowmem) {
            if($pre_cache_key === null)	$pre_cache_key = $this->_pre_cache_key;
            $data = memory('get', $ids, $pre_cache_key);
        }
        return $data;
    }
    
    public function insert($data, $return_insert_id = false, $replace = false, $silent = false) {
        return DB::insert($this->_table, $data, $return_insert_id, $replace, $silent);
    }
}