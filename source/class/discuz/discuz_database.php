<?php
if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}
class discuz_database {
    
    public static $db;
    public static $driver;
    public static function init($driver, $config) {
        self::$driver = $driver;
        self::$db = new $driver;
        self::$db->set_config($config);
        self::$db->connect();
    }
    
    public static function object() {
        return self::$db;
    }
    
    public static function error() {
        return self::$db->error();
    }
    
    public static function errno() {
        return self::$db->errno();
    }
    
    public static function table($table) {
        return self::$db->table_name($table);
    }
    
    public static function query($sql, $arg = array(), $silent = false, $unbuffered = false) {
        if (!empty($arg)) {
            if (is_array($arg)) {
                $sql = self::format($sql, $arg);
            } elseif ($arg === 'SILENT') {
                $silent = true;
    
            } elseif ($arg === 'UNBUFFERED') {
                $unbuffered = true;
            }
        }
        self::checkquery($sql);
    
        $ret = self::$db->query($sql, $silent, $unbuffered);
        if (!$unbuffered && $ret) {
            $cmd = trim(strtoupper(substr($sql, 0, strpos($sql, ' '))));
            if ($cmd === 'SELECT') {
    
            } elseif ($cmd === 'UPDATE' || $cmd === 'DELETE') {
                $ret = self::$db->affected_rows();
            } elseif ($cmd === 'INSERT') {
                $ret = self::$db->insert_id();
            }
        }
        return $ret;
    }
}