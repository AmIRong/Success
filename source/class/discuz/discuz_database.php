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
}