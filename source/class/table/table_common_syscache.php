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
    
    public function fetch_all($cachenames) {
    
        $data = array();
        $cachenames = is_array($cachenames) ? $cachenames : array($cachenames);
        if($this->_allowmem) {
            $data = memory('get', $cachenames);
            $newarray = $data !== false ? array_diff($cachenames, array_keys($data)) : $cachenames;
            if(empty($newarray)) {
                return $data;
            } else {
                $cachenames = $newarray;
            }
        }
    
        if($this->_isfilecache) {
            $lostcaches = array();
            foreach($cachenames as $cachename) {
                if(!@include_once(DISCUZ_ROOT.'./data/cache/cache_'.$cachename.'.php')) {
                    $lostcaches[] = $cachename;
                } elseif($this->_allowmem) {
                    memory('set', $cachename, $data[$cachename]);
                }
            }
            if(!$lostcaches) {
                return $data;
            }
            $cachenames = $lostcaches;
            unset($lostcaches);
        }
    
        $query = DB::query('SELECT * FROM '.DB::table($this->_table).' WHERE '.DB::field('cname', $cachenames));
        while($syscache = DB::fetch($query)) {
            $data[$syscache['cname']] = $syscache['ctype'] ? unserialize($syscache['data']) : $syscache['data'];
            $this->_allowmem && (memory('set', $syscache['cname'], $data[$syscache['cname']]));
            if($this->_isfilecache) {
                $cachedata = '$data[\''.$syscache['cname'].'\'] = '.var_export($data[$syscache['cname']], true).";\n\n";
                if(($fp = @fopen(DISCUZ_ROOT.'./data/cache/cache_'.$syscache['cname'].'.php', 'wb'))) {
                    fwrite($fp, "<?php\n//Discuz! cache file, DO NOT modify me!\n//Identify: ".md5($syscache['cname'].$cachedata.getglobal('config/security/authkey'))."\n\n$cachedata?>");
                    fclose($fp);
                }
            }
        }
    
        foreach($cachenames as $name) {
            if($data[$name] === null) {
                $data[$name] = null;
                $this->_allowmem && (memory('set', $name, array()));
            }
        }
    
        return $data;
    }
}