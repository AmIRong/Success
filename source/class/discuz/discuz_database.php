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
    
    public static function field($field, $val, $glue = '=') {
    
        $field = self::quote_field($field);
    
        if (is_array($val)) {
            $glue = $glue == 'notin' ? 'notin' : 'in';
        } elseif ($glue == 'in') {
            $glue = '=';
        }
    
        switch ($glue) {
            case '=':
                return $field . $glue . self::quote($val);
                break;
            case '-':
            case '+':
                return $field . '=' . $field . $glue . self::quote((string) $val);
                break;
            case '|':
            case '&':
            case '^':
                return $field . '=' . $field . $glue . self::quote($val);
                break;
            case '>':
            case '<':
            case '<>':
            case '<=':
            case '>=':
                return $field . $glue . self::quote($val);
                break;
    
            case 'like':
                return $field . ' LIKE(' . self::quote($val) . ')';
                break;
    
            case 'in':
            case 'notin':
                $val = $val ? implode(',', self::quote($val)) : '\'\'';
                return $field . ($glue == 'notin' ? ' NOT' : '') . ' IN(' . $val . ')';
                break;
    
            default:
                throw new DbException('Not allow this glue between field and value: "' . $glue . '"');
        }
    }
    
    public static function quote_field($field) {
        if (is_array($field)) {
            foreach ($field as $k => $v) {
                $field[$k] = self::quote_field($v);
            }
        } else {
            if (strpos($field, '`') !== false)
                $field = str_replace('`', '', $field);
                $field = '`' . $field . '`';
        }
        return $field;
    }
    
    public static function quote($str, $noarray = false) {
    
        if (is_string($str))
            return '\'' . mysql_escape_string($str) . '\'';
    
            if (is_int($str) or is_float($str))
                return '\'' . $str . '\'';
    
                if (is_array($str)) {
                    if($noarray === false) {
                        foreach ($str as &$v) {
                            $v = self::quote($v, true);
                        }
                        return $str;
                    } else {
                        return '\'\'';
                    }
                }
    
                if (is_bool($str))
                    return $str ? '1' : '0';
    
                    return '\'\'';
    }
}